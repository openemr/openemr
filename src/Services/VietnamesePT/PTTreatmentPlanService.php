<?php

/**
 * PT Treatment Plan Service with ACL, Audit Logging, and Error Handling
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

class PTTreatmentPlanService extends BaseService
{
    private const TABLE = "pt_treatment_plans";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Get active treatment plans for a patient
     * AI-GENERATED CODE START
     */
    public function getActivePlans($patientId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE patient_id = ? AND plan_status = 'active'
                    ORDER BY start_date DESC";

            $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);

            while ($row = sqlFetchArray($result)) {
                $processingResult->addData($row);
            }

            // Audit log access
            EventAuditLogger::instance()->newEvent(
                'pt-treatment-plan-access',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Accessed treatment plans for patient: {$patientId}",
                $patientId
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Treatment Plan getActivePlans failed', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId
            ]);
            $processingResult->addInternalError('Failed to retrieve treatment plans: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Insert new treatment plan
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

            if (isset($data['goals_short_term']) && is_array($data['goals_short_term'])) {
                $data['goals_short_term'] = json_encode($data['goals_short_term']);
            }

            if (isset($data['goals_long_term']) && is_array($data['goals_long_term'])) {
                $data['goals_long_term'] = json_encode($data['goals_long_term']);
            }

            $query = $this->buildInsertColumns($data);
            $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
            $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

            if ($insertId) {
                $processingResult->addData(['id' => $insertId]);

                // Audit log
                EventAuditLogger::instance()->newEvent(
                    'pt-treatment-plan-create',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Created PT Treatment Plan ID: {$insertId} for patient: {$data['patient_id']}",
                    $data['patient_id']
                );
            } else {
                $processingResult->addInternalError("Error inserting treatment plan");
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Treatment Plan insert failed', [
                'error' => $e->getMessage(),
                'patient_id' => $data['patient_id'] ?? 'unknown'
            ]);
            $processingResult->addInternalError('Failed to create treatment plan: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Update treatment plan status
     * AI-GENERATED CODE START
     */
    public function updateStatus($id, $status): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        $validStatuses = ['active', 'completed', 'on_hold', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $processingResult->addValidationMessage('status', 'invalid status');
            return $processingResult;
        }

        try {
            $sql = "UPDATE " . self::TABLE . "
                    SET plan_status = ?, updated_at = ?, updated_by = ?
                    WHERE id = ?";

            sqlStatement($sql, [$status, date('Y-m-d H:i:s'), $_SESSION['authUserID'] ?? null, $id]);
            $processingResult->addData(['id' => $id, 'status' => $status]);

            // Audit log
            EventAuditLogger::instance()->newEvent(
                'pt-treatment-plan-update',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Updated PT Treatment Plan ID: {$id} status to: {$status}",
                null
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Treatment Plan updateStatus failed', [
                'error' => $e->getMessage(),
                'plan_id' => $id,
                'status' => $status
            ]);
            $processingResult->addInternalError('Failed to update treatment plan status: ' . $e->getMessage());
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
