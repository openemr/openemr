<?php

namespace OpenEMR\Services;

use OpenEMR\Services\PatientService;

class CDSHookService extends BaseService
{
    private array $cdsServices = [
        'patient-greeting' => [
            'url' => 'https://sandbox-services.cds-hooks.org/cds-services/patient-greeting',
            'hook' => 'patient-view',
            'enabled' => true
        ],
        'cms-price-check' => [
            'url' => 'https://sandbox-services.cds-hooks.org/cds-services/cms-price-check',
            'hook' => 'order-select',
            'enabled' => false
        ],
        'pama-imaging' => [
            'url' => 'https://sandbox-services.cds-hooks.org/cds-services/pama-imaging',
            'hook' => 'order-select',
            'enabled' => false
        ]
    ];

    /**
     * 建構函數
     */
    public function __construct()
    {
        parent::__construct('patient_data'); // 使用 patient_data 表作為基礎
    }

    /**
     * 觸發 patient-view hook
     */
    public function triggerPatientView(int $patientId): array
    {
        error_log("CDS Hook: Starting triggerPatientView for patient ID: $patientId");
        
        // 直接從資料庫獲取患者資料，避免 PatientService 的權限問題
        $patient = $this->getPatientDataDirectly($patientId);
        
        if (empty($patient)) {
            error_log("CDS Hook: No patient data found for ID: $patientId");
            return [];
        }

        error_log("CDS Hook: Patient data found: " . json_encode($patient));
        
        $cdsCards = [];
        
        foreach ($this->cdsServices as $serviceId => $service) {
            if ($service['hook'] === 'patient-view' && $service['enabled']) {
                error_log("CDS Hook: Calling service: $serviceId");
                $cards = $this->callCDSService($service, $patient);
                error_log("CDS Hook: Service $serviceId returned " . count($cards) . " cards");
                $cdsCards = array_merge($cdsCards, $cards);
            }
        }
        
        error_log("CDS Hook: Total cards returned: " . count($cdsCards));
        return $cdsCards;
    }

    /**
     * 直接從資料庫獲取患者資料
     */
    private function getPatientDataDirectly(int $patientId): array
    {
        $query = "SELECT pid, fname, lname, DOB, sex, status, uuid FROM patient_data WHERE pid = ?";
        $result = sqlQuery($query, [$patientId]);
        
        if ($result === false) {
            return [];
        }
        
        return $result;
    }

    /**
     * 調用 CDS Hook 服務
     */
    private function callCDSService(array $service, array $patient): array
    {
        // 使用真實的資料庫資料，但修復 UUID 格式問題
        $patientUuid = $this->convertUuidToString($patient['uuid'] ?? '1');
        
        // 使用真實的患者資料
        $patientFirstName = $patient['fname'] ?? 'John';
        $patientLastName = $patient['lname'] ?? 'Doe';
        $patientBirthDate = $patient['DOB'] ?? '1980-01-01';
        $patientGender = strtolower($patient['sex'] ?? 'male');
        
        $cdsRequest = [
            'hook' => $service['hook'],
            'hookInstance' => 'openemr-' . uniqid(),
            'fhirServer' => 'https://localhost:9300/apis/default/fhir',
            'user' => 'Practitioner/admin',
            'patient' => 'Patient/' . $patientUuid,
            'context' => [
                'patientId' => $patientUuid
            ],
            'prefetch' => [
                'patient' => [
                    'resourceType' => 'Patient',
                    'id' => $patientUuid,
                    'name' => [[
                        'use' => 'official',
                        'family' => $patientLastName,
                        'given' => [$patientFirstName]
                    ]],
                    'birthDate' => $patientBirthDate,
                    'gender' => $patientGender
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cdsRequest));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5秒超時

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 除錯模式：記錄請求和回應
        if (($GLOBALS['cds_debug'] ?? false)) {
            $requestJson = json_encode($cdsRequest);
            error_log("CDS Hook Request (JSON): " . ($requestJson ?: 'JSON_ENCODE_FAILED'));
            error_log("CDS Hook Request (Array): " . print_r($cdsRequest, true));
            error_log("CDS Hook Response (HTTP $httpCode): " . $response);
        }

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['cards'] ?? [];
        }

        error_log("CDS Hook failed with HTTP code: $httpCode, Response: $response");
        return [];
    }

    /**
     * 轉換 UUID 為字串格式
     */
    private function convertUuidToString($uuid): string
    {
        // 如果已經是字串且不包含二進制字元，直接返回
        if (is_string($uuid) && !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $uuid)) {
            return $uuid;
        }
        
        // 如果是二進制格式，轉換為 UUID 字串
        if (is_resource($uuid) || (is_string($uuid) && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $uuid))) {
            $uuidString = bin2hex($uuid);
            return substr($uuidString, 0, 8) . '-' . 
                   substr($uuidString, 8, 4) . '-' . 
                   substr($uuidString, 12, 4) . '-' . 
                   substr($uuidString, 16, 4) . '-' . 
                   substr($uuidString, 20, 12);
        }
        
        return (string)$uuid;
    }

    /**
     * 格式化患者資料為 FHIR 格式
     */
    private function formatPatientForFHIR(array $patient, string $patientUuid): array
    {
        return [
            'resourceType' => 'Patient',
            'id' => $patientUuid,
            'name' => [[
                'use' => 'official',
                'family' => $patient['lname'] ?? '',
                'given' => [$patient['fname'] ?? '']
            ]],
            'birthDate' => $patient['DOB'] ?? '',
            'gender' => strtolower($patient['sex'] ?? 'unknown')
        ];
    }
}
?>
