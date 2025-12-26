<?php

/**
 * PT Outcome Measures Service with ACL, Audit Logging, and Error Handling
 *
 * AI-GENERATED CODE - Claude Sonnet 4.5 (2025-01-22)
 * Enhanced with ACL integration, audit logging, input sanitization, and error handling
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

class PTOutcomeMeasuresService extends BaseService
{
    private const TABLE = "pt_outcome_measures";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Get patient outcomes
     * AI-GENERATED CODE START
     */
    public function getPatientOutcomes($patientId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE patient_id = ?
                    ORDER BY measurement_date DESC";

            $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);

            while ($row = sqlFetchArray($result)) {
                $processingResult->addData($row);
            }

            // Audit log access
            EventAuditLogger::instance()->newEvent(
                'pt-outcome-access',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Accessed outcome measures for patient: {$patientId}",
                $patientId
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Outcome Measures getPatientOutcomes failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId
            ]);
            $processingResult->addInternalError('Failed to retrieve outcome measures: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Insert outcome measure
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
                    'pt-outcome-create',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Created PT Outcome Measure ID: {$insertId} for patient: {$data['patient_id']}",
                    $data['patient_id']
                );
            } else {
                $processingResult->addInternalError("Error inserting outcome measure");
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Outcome Measures insert failed', [
                'error' => $e->getMessage(),
                'patient_id' => $data['patient_id'] ?? 'unknown'
            ]);
            $processingResult->addInternalError('Failed to create outcome measure: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Get progress tracking data
     * AI-GENERATED CODE START
     */
    public function getProgressTracking($patientId, $measureType): array
    {
        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            SystemLogger::instance()->warning('Access denied to getProgressTracking', [
                'patient_id' => $patientId,
                'user' => $_SESSION['authUser'] ?? 'unknown'
            ]);
            return [];
        }

        try {
            $sql = "SELECT measurement_date, score_value, interpretation_vi
                    FROM " . self::TABLE . "
                    WHERE patient_id = ? AND measure_type = ?
                    ORDER BY measurement_date ASC";

            $result = sqlStatement($sql, [$patientId, $measureType]);
            $tracking = [];

            while ($row = sqlFetchArray($result)) {
                $tracking[] = $row;
            }

            return $tracking;
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Outcome Measures getProgressTracking failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId,
                'measure_type' => $measureType
            ]);
            return [];
        }
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
