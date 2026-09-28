<?php

/**
 * FhirClaimService.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Nilesh Hake <nilesh.hake@nbhhealthsoft.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;

class FhirClaimService extends FhirServiceBase
{
    /**
     * Main Insert Method
     */
    public function insert(FHIRDomainResource $fhirResource): ProcessingResult
    {
        $result = new ProcessingResult();
        $data = $this->parseFhirResource($fhirResource);

        // Versioning Logic to prevent Duplicate Entry errors
        $currentVersion = sqlQuery("SELECT MAX(version) as max_v FROM claims WHERE patient_id = ? AND encounter_id = ?", [
            $data['patient_id'],
            $data['encounter_id']
        ]);
        
        $data['version'] = (!empty($currentVersion['max_v'])) ? (int)$currentVersion['max_v'] + 1 : 1;

        $success = $this->insertOpenEMRRecord($data);

        if ($success) {
            $record = sqlQuery("SELECT * FROM claims WHERE patient_id = ? AND encounter_id = ? AND version = ?", [
                $data['patient_id'],
                $data['encounter_id'],
                $data['version']
            ]);

            $this->createProvenanceResource($record);
            $result->setData($this->parseOpenEMRRecord($record));
        } else {
            $result->addInternalError("Failed to insert claim into database.");
        }

        return $result;
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        return null; 
    }

    /**
     * Safely maps FHIR data to Database Columns
     */
    public function parseFhirResource($fhirResource)
    {
        $data = [];
        
        // 1. Patient ID
        $data['patient_id'] = $this->safeExtractId($fhirResource->getPatient(), 'Patient/');
        
        // 2. Encounter ID (Fix for the Fatal Error)
        $encounterId = 0;
        $items = $fhirResource->getItem();
        if (!empty($items) && is_array($items)) {
            $firstItem = $items[0];
            // Check if it's an object before calling methods
            if (is_object($firstItem) && method_exists($firstItem, 'getEncounter')) {
                $encounterId = $this->safeExtractId($firstItem->getEncounter(), 'Encounter/');
            } elseif (is_array($firstItem) && isset($firstItem['encounter'])) {
                $encounterId = $this->safeExtractId($firstItem['encounter'], 'Encounter/');
            }
        }
        $data['encounter_id'] = $encounterId;

        // 3. Payer ID
        $payerId = 0;
        $insurances = $fhirResource->getInsurance();
        if (!empty($insurances) && is_array($insurances)) {
            $firstIns = $insurances[0];
            if (is_object($firstIns) && method_exists($firstIns, 'getCoverage')) {
                $payerId = $this->safeExtractId($firstIns->getCoverage(), 'Coverage/');
            } elseif (is_array($firstIns) && isset($firstIns['coverage'])) {
                $payerId = $this->safeExtractId($firstIns['coverage'], 'Coverage/');
            }
        }
        $data['payer_id'] = $payerId;

        // 4. Status mapping
        $statusObj = $fhirResource->getStatus();
        $statusStr = is_object($statusObj) ? $statusObj->getValue() : (string)$statusObj;
        $data['status'] = ($statusStr === 'active') ? 0 : 1;

        // 5. Defaults
        $data['payer_type'] = 0;
        $data['bill_process'] = 0;
        $data['bill_time'] = date('Y-m-d H:i:s');
        $data['process_time'] = null;
        $data['process_file'] = null;
        $data['target'] = '0';
        $data['x12_partner_id'] = 0;
        
        // 6. FULL JSON
        $data['submitted_claim'] = json_encode($fhirResource);

        return $data;
    }

    protected function insertOpenEMRRecord($data)
    {
        $sql = "INSERT INTO claims (
                    patient_id, encounter_id, version, payer_id, 
                    status, payer_type, bill_process, bill_time, 
                    process_time, process_file, target, x12_partner_id, 
                    submitted_claim
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return sqlStatement($sql, [
            (int)$data['patient_id'],
            (int)$data['encounter_id'],
            (int)$data['version'],
            (int)$data['payer_id'],
            (int)$data['status'],
            (int)$data['payer_type'],
            (int)$data['bill_process'],
            $data['bill_time'],
            $data['process_time'],
            $data['process_file'],
            (string)$data['target'],
            (int)$data['x12_partner_id'],
            (string)$data['submitted_claim']
        ]);
    }

    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        if (empty($dataRecord) || empty($dataRecord['submitted_claim'])) {
            return null;
        }
        $fullData = json_decode($dataRecord['submitted_claim'], true);
        unset($fullData['id'], $fullData['status']);
        return $encode ? json_encode($fullData) : $fullData;
    }

    /**
     * Refactored helper to prevent "Argument #1 must be of type object|string, array given"
     */
    private function safeExtractId($refSource, $prefix)
    {
        if (empty($refSource)) return 0;

        $refValue = '';

        // Case 1: It's already an array (from json_decode or raw input)
        if (is_array($refSource)) {
            // Handle array of objects
            $item = isset($refSource[0]) ? $refSource[0] : $refSource;
            $refValue = isset($item['reference']) ? $item['reference'] : '';
        } 
        // Case 2: It's an object (FHIR library class)
        elseif (is_object($refSource)) {
            if (method_exists($refSource, 'getReference')) {
                $refValue = $refSource->getReference()->getValue();
            }
        }

        if (empty($refValue)) return 0;

        return (int) str_replace($prefix, '', $refValue);
    }

    /**
     * Required Public Methods for Search functionality.
     */
    public function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField()
        ];
    }

    public function getPatientContextSearchField()
    {
        return new FhirSearchParameterDefinition(
            'patient',
            SearchFieldType::REFERENCE,
            [new ServiceField('patient_id', ServiceField::TYPE_NUMBER)]
        );
    }

    public function searchForOpenEMRRecords($searchParameters): ProcessingResult
    {
        $result = new ProcessingResult();
        $records = sqlStatement("SELECT * FROM claims");

        $data = [];
        while ($row = sqlFetchArray($records)) {
            $data[] = $this->parseOpenEMRRecord($row);
        }

        $result->setData($data);
        return $result;
    }

    protected function updateOpenEMRRecord($data, $id)
    {
        return null;
    }
}