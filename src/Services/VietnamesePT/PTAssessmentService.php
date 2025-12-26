<?php

/**
 * PT Assessment Service
 * Manages bilingual physiotherapy assessments
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\VietnamesePT\PTAssessmentValidator;

class PTAssessmentService extends BaseService
{
    private const ASSESSMENT_TABLE = "pt_assessments_bilingual";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";

    private $validator;

    public function __construct()
    {
        parent::__construct(self::ASSESSMENT_TABLE);
        UuidRegistry::createMissingUuidsForTables([
            self::ASSESSMENT_TABLE,
            self::PATIENT_TABLE,
            self::ENCOUNTER_TABLE
        ]);
        $this->validator = new PTAssessmentValidator();
    }

    /**
     * Get all assessments with optional search criteria
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check and error handling
     */
    public function getAll($search = array(), $isAndCondition = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - require read access to patient medical records
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT
                a.*,
                a.id as assessment_id,
                p.uuid as patient_uuid,
                p.pid,
                p.fname,
                p.lname,
                e.uuid as encounter_uuid,
                e.encounter,
                u.uuid as therapist_uuid,
                u.username as therapist_username,
                CONCAT(u.fname, ' ', u.lname) as therapist_name
            FROM " . self::ASSESSMENT_TABLE . " a
            LEFT JOIN " . self::PATIENT_TABLE . " p ON a.patient_id = p.pid
            LEFT JOIN " . self::ENCOUNTER_TABLE . " e ON a.encounter_id = e.encounter
            LEFT JOIN users u ON a.therapist_id = u.id";

            $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereClause->getFragment();
            $sqlBindArray = $whereClause->getBoundValues();

            $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

            while ($row = sqlFetchArray($statementResults)) {
                $processingResult->addData($this->createResultRecordFromDatabaseResult($row));
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Assessment getAll failed', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);
            $processingResult->addInternalError('Failed to retrieve assessments: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Get a single assessment by ID
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check and audit logging
     */
    public function getOne($id): ProcessingResult
    {
        // ACL check - require read access to patient medical records
        $processingResult = new ProcessingResult();
        if (!AclMain::aclCheckCore('patients', 'med')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        $search = ['id' => new StringSearchField('id', [$id], SearchModifier::EXACT)];
        $result = $this->getAll($search);

        // Audit log access to PT assessment
        if ($result->hasData()) {
            $data = $result->getData();
            if (!empty($data[0])) {
                EventAuditLogger::instance()->newEvent(
                    'pt-assessment-access',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Accessed PT Assessment ID: {$id}",
                    $data[0]['patient_id'] ?? null
                );
            }
        }

        return $result;
    }
    // AI-GENERATED CODE END

    /**
     * Get all assessments for a specific patient
     */
    public function getPatientAssessments($patientId): ProcessingResult
    {
        $search = ['patient_id' => new StringSearchField('a.patient_id', [$patientId], SearchModifier::EXACT)];
        return $this->getAll($search);
    }

    /**
     * Create a new bilingual assessment
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, input sanitization, audit logging, and error handling
     */
    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - require write access to patient medical records
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        // Sanitize input data
        $data = $this->sanitizeInput($data);

        // Validate data
        $processingResult = $this->validator->validate($data);

        if ($processingResult->isValid()) {
            try {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $_SESSION['authUserID'] ?? null;

                $query = $this->buildInsertColumns($data);
                $sql = "INSERT INTO " . self::ASSESSMENT_TABLE . " SET ";
                $sql .= $query['set'];

                $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

                if ($insertId) {
                    $processingResult->addData([
                        'id' => $insertId,
                        'patient_id' => $data['patient_id'],
                        'encounter_id' => $data['encounter_id'] ?? null
                    ]);

                    // Audit log the creation
                    EventAuditLogger::instance()->newEvent(
                        'pt-assessment-create',
                        $_SESSION['authUser'] ?? 'system',
                        $_SESSION['authProvider'] ?? 0,
                        1,
                        "Created PT Assessment ID: {$insertId} for patient: {$data['patient_id']}",
                        $data['patient_id']
                    );
                } else {
                    $processingResult->addInternalError("Error inserting PT assessment");
                }
            } catch (\Exception $e) {
                SystemLogger::instance()->error('PT Assessment insert failed', [
                    'error' => $e->getMessage(),
                    'patient_id' => $data['patient_id'] ?? 'unknown'
                ]);
                $processingResult->addInternalError('Failed to create PT assessment: ' . $e->getMessage());
            }
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Update an existing assessment
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, input sanitization, audit logging, and error handling
     */
    public function update($id, $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - require write access to patient medical records
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        // Sanitize input data
        $data = $this->sanitizeInput($data);

        // Validate data
        $processingResult = $this->validator->validate($data, true);

        if ($processingResult->isValid()) {
            try {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $_SESSION['authUserID'] ?? null;

                // Remove fields that shouldn't be updated
                unset($data['id']);
                unset($data['created_at']);
                unset($data['created_by']);

                $query = $this->buildUpdateColumns($data);
                $sql = "UPDATE " . self::ASSESSMENT_TABLE . " SET ";
                $sql .= $query['set'];
                $sql .= " WHERE id = ?";

                $result = sqlStatement($sql, array_merge($query['bind'], [$id]));

                if ($result !== false) {
                    $processingResult->addData(['id' => $id]);

                    // Audit log the update
                    EventAuditLogger::instance()->newEvent(
                        'pt-assessment-update',
                        $_SESSION['authUser'] ?? 'system',
                        $_SESSION['authProvider'] ?? 0,
                        1,
                        "Updated PT Assessment ID: {$id}",
                        $data['patient_id'] ?? null
                    );
                } else {
                    $processingResult->addInternalError("Error updating PT assessment");
                }
            } catch (\Exception $e) {
                SystemLogger::instance()->error('PT Assessment update failed', [
                    'error' => $e->getMessage(),
                    'assessment_id' => $id
                ]);
                $processingResult->addInternalError('Failed to update PT assessment: ' . $e->getMessage());
            }
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Delete assessment (soft delete by setting status to 'cancelled')
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Added ACL check, audit logging, and error handling
     */
    public function delete($id): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - require write access to patient medical records
        if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            // Get assessment info for audit log
            $assessmentInfo = $this->getOne($id);
            $patientId = null;
            if ($assessmentInfo->hasData()) {
                $data = $assessmentInfo->getData();
                $patientId = $data[0]['patient_id'] ?? null;
            }

            $sql = "UPDATE " . self::ASSESSMENT_TABLE . "
                    SET status = 'cancelled', updated_at = ?, updated_by = ?
                    WHERE id = ?";

            $result = sqlStatement($sql, [
                date('Y-m-d H:i:s'),
                $_SESSION['authUserID'] ?? null,
                $id
            ]);

            if ($result !== false) {
                $processingResult->addData(['id' => $id, 'status' => 'cancelled']);

                // Audit log the deletion
                EventAuditLogger::instance()->newEvent(
                    'pt-assessment-delete',
                    $_SESSION['authUser'] ?? 'system',
                    $_SESSION['authProvider'] ?? 0,
                    1,
                    "Deleted (cancelled) PT Assessment ID: {$id}",
                    $patientId
                );
            } else {
                $processingResult->addInternalError("Error deleting PT assessment");
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Assessment delete failed', [
                'error' => $e->getMessage(),
                'assessment_id' => $id
            ]);
            $processingResult->addInternalError('Failed to delete PT assessment: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Search assessments with Vietnamese text
     */
    public function searchByVietnameseText($searchTerm, $language = 'vi'): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $searchPattern = '%' . $searchTerm . '%';

        if ($language === 'vi') {
            $sql = "SELECT a.*, p.fname, p.lname
                    FROM " . self::ASSESSMENT_TABLE . " a
                    LEFT JOIN " . self::PATIENT_TABLE . " p ON a.patient_id = p.pid
                    WHERE a.chief_complaint_vi LIKE ?
                       OR a.pain_location_vi LIKE ?
                       OR a.pain_description_vi LIKE ?
                       OR a.functional_goals_vi LIKE ?
                       OR a.treatment_plan_vi LIKE ?
                    ORDER BY a.assessment_date DESC";

            $bindArray = array_fill(0, 5, $searchPattern);
        } else {
            $sql = "SELECT a.*, p.fname, p.lname
                    FROM " . self::ASSESSMENT_TABLE . " a
                    LEFT JOIN " . self::PATIENT_TABLE . " p ON a.patient_id = p.pid
                    WHERE a.chief_complaint_en LIKE ?
                       OR a.pain_location_en LIKE ?
                       OR a.pain_description_en LIKE ?
                       OR a.functional_goals_en LIKE ?
                       OR a.treatment_plan_en LIKE ?
                    ORDER BY a.assessment_date DESC";

            $bindArray = array_fill(0, 5, $searchPattern);
        }

        $statementResults = QueryUtils::sqlStatementThrowException($sql, $bindArray);

        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Get assessment statistics for a patient
     */
    public function getPatientAssessmentStats($patientId): array
    {
        $sql = "SELECT
                    COUNT(*) as total_assessments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                    AVG(pain_level) as avg_pain_level,
                    MIN(assessment_date) as first_assessment,
                    MAX(assessment_date) as latest_assessment
                FROM " . self::ASSESSMENT_TABLE . "
                WHERE patient_id = ?";

        $result = sqlQuery($sql, [$patientId]);
        return $result ?: [];
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'patient_uuid', 'encounter_uuid', 'therapist_uuid'];
    }

    /**
     * Sanitize input data for Vietnamese PT assessments
     *
     * AI-GENERATED CODE START - Claude Sonnet 4.5 (2025-01-22)
     * Input sanitization to prevent SQL injection and ensure proper UTF-8 encoding
     *
     * @param array $data Input data to sanitize
     * @return array Sanitized data
     */
    private function sanitizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Ensure proper UTF-8 encoding for Vietnamese text
                $data[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

                // Remove null bytes
                $data[$key] = str_replace("\0", '', $data[$key]);

                // Trim whitespace
                $data[$key] = trim($data[$key]);
            } elseif (is_array($value)) {
                // Recursively sanitize nested arrays
                $data[$key] = $this->sanitizeInput($value);
            }
        }

        return $data;
    }
    // AI-GENERATED CODE END
}