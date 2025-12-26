<?php

namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\VietnamesePT\PTExercisePrescriptionValidator;

class PTExercisePrescriptionService extends BaseService
{
    private const TABLE = "pt_exercise_prescriptions";
    private $validator;

    public function __construct()
    {
        parent::__construct(self::TABLE);
        $this->validator = new PTExercisePrescriptionValidator();
    }

    /**
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check and error handling
     */
    public function getAll($search = []): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT e.*, p.fname, p.lname, u.username as prescribed_by_name
                    FROM " . self::TABLE . " e
                    LEFT JOIN patient_data p ON e.patient_id = p.pid
                    LEFT JOIN users u ON e.therapist_id = u.id
                    WHERE 1=1";

            $bindArray = [];

            if (!empty($search['patient_id'])) {
                $sql .= " AND e.patient_id = ?";
                $bindArray[] = $search['patient_id'];
            }

            if (!empty($search['is_active'])) {
                $sql .= " AND e.status = ?";
                $bindArray[] = 'active';
            }

            $sql .= " ORDER BY e.start_date DESC";

            $statementResults = QueryUtils::sqlStatementThrowException($sql, $bindArray);

            while ($row = sqlFetchArray($statementResults)) {
                $processingResult->addData($row);
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Exercise Prescription getAll failed', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);
            $processingResult->addInternalError('Failed to retrieve exercise prescriptions: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    public function getOne($id): ProcessingResult
    {
        return $this->getAll(['id' => $id]);
    }

    public function getPatientPrescriptions($patientId): ProcessingResult
    {
        return $this->getAll(['patient_id' => $patientId, 'is_active' => 1]);
    }

    /**
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, sanitization, audit logging, and error handling
     */
    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        // Sanitize input
        $data = $this->sanitizeInput($data);
        $processingResult = $this->validator->validate($data);

        if ($processingResult->isValid()) {
            try {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $_SESSION['authUserID'] ?? null;
                $query = $this->buildInsertColumns($data);
                $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];

                $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

                if ($insertId) {
                    $processingResult->addData(['id' => $insertId]);

                    // Audit log
                    EventAuditLogger::instance()->newEvent(
                        'pt-exercise-create',
                        $_SESSION['authUser'] ?? 'system',
                        $_SESSION['authProvider'] ?? 0,
                        1,
                        "Created PT Exercise Prescription ID: {$insertId} for patient: {$data['patient_id']}",
                        $data['patient_id']
                    );
                } else {
                    $processingResult->addInternalError("Error inserting exercise prescription");
                }
            } catch (\Exception $e) {
                SystemLogger::instance()->error('PT Exercise Prescription insert failed', [
                    'error' => $e->getMessage(),
                    'patient_id' => $data['patient_id'] ?? 'unknown'
                ]);
                $processingResult->addInternalError('Failed to create exercise prescription: ' . $e->getMessage());
            }
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, sanitization, audit logging, and error handling
     */
    public function update($id, $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        // Sanitize input
        $data = $this->sanitizeInput($data);
        $processingResult = $this->validator->validate($data, true);

        if ($processingResult->isValid()) {
            try {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $_SESSION['authUserID'] ?? null;
                unset($data['id'], $data['created_at'], $data['created_by']);

                $query = $this->buildUpdateColumns($data);
                $sql = "UPDATE " . self::TABLE . " SET " . $query['set'] . " WHERE id = ?";

                sqlStatement($sql, array_merge($query['bind'], [$id]));
                $processingResult->addData(['id' => $id]);

                // Audit log
                EventAuditLogger::instance()->newEvent(
                    'pt-exercise-update',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Updated PT Exercise Prescription ID: {$id}",
                    $data['patient_id'] ?? null
                );
            } catch (\Exception $e) {
                SystemLogger::instance()->error('PT Exercise Prescription update failed', [
                    'error' => $e->getMessage(),
                    'prescription_id' => $id
                ]);
                $processingResult->addInternalError('Failed to update exercise prescription: ' . $e->getMessage());
            }
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, audit logging, and error handling
     */
    public function delete($id): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "UPDATE " . self::TABLE . " SET status = 'discontinued', updated_at = ?, updated_by = ? WHERE id = ?";
            sqlStatement($sql, [date('Y-m-d H:i:s'), $_SESSION['authUserID'] ?? null, $id]);
            $processingResult->addData(['id' => $id]);

            // Audit log
            EventAuditLogger::instance()->newEvent(
                'pt-exercise-delete',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Deleted (discontinued) PT Exercise Prescription ID: {$id}",
                null
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Exercise Prescription delete failed', [
                'error' => $e->getMessage(),
                'prescription_id' => $id
            ]);
            $processingResult->addInternalError('Failed to delete exercise prescription: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Sanitize input data
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
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
