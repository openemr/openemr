<?php

/**
 * Vietnamese Insurance Service with Billing Integration
 *
 * AI-GENERATED CODE - Claude Sonnet 4.5 (2025-01-22)
 * Enhanced with ACL integration, audit logging, billing integration, BHYT validation,
 * and comprehensive error handling for Vietnamese health insurance (BHYT) integration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class VietnameseInsuranceService extends BaseService
{
    private const TABLE = "vietnamese_insurance_info";

    // Vietnamese insurance types
    private const BHYT_TYPE = 'BHYT'; // Bảo hiểm y tế (Health Insurance)
    private const BHTN_TYPE = 'BHTN'; // Bảo hiểm tai nạn (Accident Insurance)

    // Coverage percentages by category
    private const COVERAGE_RATES = [
        'full' => 100,      // Full coverage (poor, ethnic minority, etc.)
        'standard' => 80,   // Standard coverage
        'reduced' => 60,    // Reduced coverage
        'basic' => 50       // Basic coverage
    ];

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Get patient insurance information
     * AI-GENERATED CODE START
     */
    public function getPatientInsurance($patientId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE patient_id = ? AND is_active = 1
                    ORDER BY valid_from DESC";

            $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);

            while ($row = sqlFetchArray($result)) {
                $processingResult->addData($row);
            }

            // Audit log
            EventAuditLogger::instance()->newEvent(
                'pt-insurance-access',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Accessed insurance info for patient: {$patientId}",
                $patientId
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('Vietnamese Insurance getPatientInsurance failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId
            ]);
            $processingResult->addInternalError('Failed to retrieve insurance info: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Insert new insurance record
     * AI-GENERATED CODE START
     */
    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        // Validate BHYT card if provided
        if (!empty($data['bhyt_card_number'])) {
            $validationResult = $this->validateBHYTCard($data['bhyt_card_number']);
            if (!$validationResult['valid']) {
                $processingResult->addValidationMessage('bhyt_card_number', $validationResult['message']);
                return $processingResult;
            }
        }

        try {
            // Sanitize input
            $data = $this->sanitizeInput($data);

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $_SESSION['authUserID'] ?? null;

            $query = $this->buildInsertColumns($data);
            $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
            $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

            if ($insertId) {
                $processingResult->addData(['id' => $insertId]);

                // Audit log
                EventAuditLogger::instance()->newEvent(
                    'pt-insurance-create',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Created insurance record ID: {$insertId} for patient: {$data['patient_id']}",
                    $data['patient_id']
                );
            } else {
                $processingResult->addInternalError("Error inserting insurance record");
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('Vietnamese Insurance insert failed', [
                'error' => $e->getMessage(),
                'patient_id' => $data['patient_id'] ?? 'unknown'
            ]);
            $processingResult->addInternalError('Failed to create insurance record: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Check if insurance is valid
     * AI-GENERATED CODE START
     */
    public function isInsuranceValid($patientId): bool
    {
        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            SystemLogger::instance()->warning('Access denied to isInsuranceValid', [
                'patient_id' => $patientId,
                'user' => $_SESSION['authUser'] ?? 'unknown'
            ]);
            return false;
        }

        try {
            $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . "
                    WHERE patient_id = ?
                    AND is_active = 1
                    AND valid_from <= CURDATE()
                    AND (valid_to IS NULL OR valid_to >= CURDATE())";

            $result = sqlQuery($sql, [$patientId]);
            return ($result['count'] ?? 0) > 0;
        } catch (\Exception $e) {
            SystemLogger::instance()->error('Vietnamese Insurance isInsuranceValid failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId
            ]);
            return false;
        }
    }
    // AI-GENERATED CODE END

    /**
     * Validate BHYT (Vietnamese Health Insurance) card number format
     * Format: XXX-XXXX-XXXXX-XXXXX (15 characters + 3 dashes = 18 total)
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     *
     * @param string $cardNumber BHYT card number
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateBHYTCard(string $cardNumber): array
    {
        // Remove spaces and convert to uppercase
        $cardNumber = strtoupper(str_replace(' ', '', $cardNumber));

        // BHYT card format: XXX-XXXX-XXXXX-XXXXX
        // Example: HC1-8421-12345-67890
        $pattern = '/^[A-Z]{2}\d-\d{4}-\d{5}-\d{5}$/';

        if (!preg_match($pattern, $cardNumber)) {
            return [
                'valid' => false,
                'message' => 'Invalid BHYT card format. Expected: XX#-####-#####-##### (e.g., HC1-8421-12345-67890)'
            ];
        }

        // Extract prefix code (first 3 characters)
        $prefixCode = substr($cardNumber, 0, 3);

        // Valid BHYT prefix codes
        $validPrefixes = [
            'HC1', 'HC2', 'HC3', 'HC4', // Full coverage categories
            'DN1', 'DN2', 'DN3',         // Enterprise employees
            'TE1', 'TE2', 'TE3',         // Voluntary insurance
            'CB1', 'CB2',                // Civil servants
            'XK1', 'XK2',                // Export zone workers
            'NN1', 'NN2', 'NN3',         // Agricultural workers
            'TN1', 'TN2',                // Family members
            'TX1', 'TX2',                // Students
        ];

        if (!in_array($prefixCode, $validPrefixes)) {
            return [
                'valid' => false,
                'message' => 'Invalid BHYT card prefix code: ' . $prefixCode
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid BHYT card',
            'prefix_code' => $prefixCode
        ];
    }
    // AI-GENERATED CODE END

    /**
     * Check coverage eligibility for a service date
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     *
     * @param int $patientId Patient ID
     * @param string $serviceDate Service date (Y-m-d format)
     * @return array ['eligible' => bool, 'coverage_percent' => int, 'message' => string]
     */
    public function checkCoverage(int $patientId, string $serviceDate): array
    {
        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            return [
                'eligible' => false,
                'coverage_percent' => 0,
                'message' => 'Access Denied'
            ];
        }

        try {
            $sql = "SELECT *
                    FROM " . self::TABLE . "
                    WHERE patient_id = ?
                    AND is_active = 1
                    AND valid_from <= ?
                    AND (valid_to IS NULL OR valid_to >= ?)
                    ORDER BY coverage_percentage DESC
                    LIMIT 1";

            $result = sqlQuery($sql, [$patientId, $serviceDate, $serviceDate]);

            if (!$result) {
                return [
                    'eligible' => false,
                    'coverage_percent' => 0,
                    'message' => 'No active insurance coverage for this date'
                ];
            }

            return [
                'eligible' => true,
                'coverage_percent' => (int)($result['coverage_percentage'] ?? 80),
                'insurance_type' => $result['insurance_type'] ?? self::BHYT_TYPE,
                'card_number' => $result['bhyt_card_number'] ?? '',
                'message' => 'Coverage active'
            ];
        } catch (\Exception $e) {
            SystemLogger::instance()->error('Vietnamese Insurance checkCoverage failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId,
                'service_date' => $serviceDate
            ]);

            return [
                'eligible' => false,
                'coverage_percent' => 0,
                'message' => 'Error checking coverage: ' . $e->getMessage()
            ];
        }
    }
    // AI-GENERATED CODE END

    /**
     * Calculate patient co-pay amount
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     *
     * @param string $serviceCode Service/procedure code
     * @param int $patientId Patient ID
     * @param float $totalAmount Total service amount
     * @param string $serviceDate Service date
     * @return array ['copay_amount' => float, 'insurance_pays' => float, 'patient_pays' => float]
     */
    public function calculateCopay(string $serviceCode, int $patientId, float $totalAmount, string $serviceDate): array
    {
        $coverage = $this->checkCoverage($patientId, $serviceDate);

        if (!$coverage['eligible']) {
            return [
                'copay_amount' => $totalAmount,
                'insurance_pays' => 0.00,
                'patient_pays' => $totalAmount,
                'coverage_percent' => 0,
                'message' => $coverage['message']
            ];
        }

        $coveragePercent = $coverage['coverage_percent'];
        $insurancePays = $totalAmount * ($coveragePercent / 100);
        $patientPays = $totalAmount - $insurancePays;

        return [
            'copay_amount' => round($patientPays, 2),
            'insurance_pays' => round($insurancePays, 2),
            'patient_pays' => round($patientPays, 2),
            'coverage_percent' => $coveragePercent,
            'message' => 'Coverage calculated successfully'
        ];
    }
    // AI-GENERATED CODE END

    /**
     * Get Vietnamese PT service codes for billing
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     *
     * @return array Service codes with descriptions and prices
     */
    public function getPTServiceCodes(): array
    {
        return [
            'PT_ASSESS_INITIAL' => [
                'code' => 'PT001',
                'name_en' => 'Initial PT Assessment',
                'name_vi' => 'Đánh giá vật lý trị liệu ban đầu',
                'price' => 500000, // VND
                'duration' => 60,  // minutes
                'bhyt_covered' => true
            ],
            'PT_ASSESS_FOLLOWUP' => [
                'code' => 'PT002',
                'name_en' => 'Follow-up PT Assessment',
                'name_vi' => 'Đánh giá vật lý trị liệu tái khám',
                'price' => 300000,
                'duration' => 30,
                'bhyt_covered' => true
            ],
            'PT_EXERCISE_THERAPY' => [
                'code' => 'PT101',
                'name_en' => 'Therapeutic Exercise Session',
                'name_vi' => 'Liệu pháp vận động trị liệu',
                'price' => 250000,
                'duration' => 45,
                'bhyt_covered' => true
            ],
            'PT_MANUAL_THERAPY' => [
                'code' => 'PT102',
                'name_en' => 'Manual Therapy',
                'name_vi' => 'Liệu pháp thủ công',
                'price' => 350000,
                'duration' => 30,
                'bhyt_covered' => true
            ],
            'PT_ELECTROTHERAPY' => [
                'code' => 'PT103',
                'name_en' => 'Electrotherapy',
                'name_vi' => 'Liệu pháp điện',
                'price' => 200000,
                'duration' => 30,
                'bhyt_covered' => true
            ],
            'PT_HEAT_THERAPY' => [
                'code' => 'PT104',
                'name_en' => 'Heat/Cold Therapy',
                'name_vi' => 'Liệu pháp nhiệt/lạnh',
                'price' => 150000,
                'duration' => 20,
                'bhyt_covered' => true
            ],
            'PT_ULTRASOUND' => [
                'code' => 'PT105',
                'name_en' => 'Ultrasound Therapy',
                'name_vi' => 'Liệu pháp siêu âm',
                'price' => 180000,
                'duration' => 15,
                'bhyt_covered' => true
            ],
            'PT_MASSAGE' => [
                'code' => 'PT106',
                'name_en' => 'Therapeutic Massage',
                'name_vi' => 'Massage trị liệu',
                'price' => 300000,
                'duration' => 45,
                'bhyt_covered' => false
            ],
        ];
    }
    // AI-GENERATED CODE END

    /**
     * Create billing entry for PT service
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     *
     * @param int $assessmentId PT assessment ID
     * @param int $encounterId Encounter ID
     * @param string $serviceCode Service code
     * @return ProcessingResult
     */
    public function createBillingEntry(int $assessmentId, int $encounterId, string $serviceCode): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - require billing access
        if (!AclMain::aclCheckCore('acct', 'bill')) {
            $processingResult->setValidationMessages(['Access Denied - Billing privileges required']);
            return $processingResult;
        }

        try {
            // Get service details
            $serviceCodes = $this->getPTServiceCodes();
            if (!isset($serviceCodes[$serviceCode])) {
                $processingResult->addValidationMessage('service_code', 'Invalid PT service code');
                return $processingResult;
            }

            $service = $serviceCodes[$serviceCode];

            // Get patient ID from encounter
            $encounterSql = "SELECT pid, date FROM form_encounter WHERE encounter = ?";
            $encounter = sqlQuery($encounterSql, [$encounterId]);

            if (!$encounter) {
                $processingResult->addInternalError('Encounter not found');
                return $processingResult;
            }

            $patientId = $encounter['pid'];
            $serviceDate = $encounter['date'];

            // Calculate copay if insurance exists
            $copayInfo = $this->calculateCopay($service['code'], $patientId, $service['price'], $serviceDate);

            // Insert into billing table
            $billingSql = "INSERT INTO billing SET
                            encounter = ?,
                            pid = ?,
                            code_type = 'PT',
                            code = ?,
                            code_text = ?,
                            activity = 1,
                            billed = 0,
                            fee = ?,
                            units = 1,
                            modifier = '',
                            authorized = 1,
                            provider_id = ?,
                            bill_date = NOW()";

            $billingParams = [
                $encounterId,
                $patientId,
                $service['code'],
                $service['name_en'] . ' / ' . $service['name_vi'],
                $service['price'],
                $_SESSION['authUserID'] ?? 0
            ];

            $billingId = QueryUtils::sqlInsert($billingSql, $billingParams);

            if ($billingId) {
                $processingResult->addData([
                    'billing_id' => $billingId,
                    'service_code' => $service['code'],
                    'total_amount' => $service['price'],
                    'insurance_pays' => $copayInfo['insurance_pays'],
                    'patient_pays' => $copayInfo['patient_pays']
                ]);

                // Audit log
                EventAuditLogger::instance()->newEvent(
                    'pt-billing-create',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Created billing entry ID: {$billingId} for PT service: {$service['code']}",
                    $patientId
                );
            } else {
                $processingResult->addInternalError('Failed to create billing entry');
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('Vietnamese Insurance createBillingEntry failed', [
                'error' => $e->getMessage(),
                'assessment_id' => $assessmentId,
                'encounter_id' => $encounterId,
                'service_code' => $serviceCode
            ]);
            $processingResult->addInternalError('Failed to create billing entry: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Sanitize input data
     * AI-GENERATED CODE START
     */
    private function sanitizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                $data[$key] = str_replace("\0", '', $data[$key]);
                $data[$key] = trim($data[$key]);
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeInput($value);
            }
        }
        return $data;
    }
    // AI-GENERATED CODE END
}
