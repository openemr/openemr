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

use OpenEMR\Common\Database\QueryUtils;
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
     */
    public function getAll($search = array(), $isAndCondition = true): ProcessingResult
    {
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

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($this->createResultRecordFromDatabaseResult($row));
        }

        return $processingResult;
    }

    /**
     * Get a single assessment by ID
     */
    public function getOne($id): ProcessingResult
    {
        $search = ['id' => new StringSearchField('id', [$id], SearchModifier::EXACT)];
        return $this->getAll($search);
    }

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
     */
    public function insert($data): ProcessingResult
    {
        $processingResult = $this->validator->validate($data);

        if ($processingResult->isValid()) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

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
            } else {
                $processingResult->addInternalError("Error inserting PT assessment");
            }
        }

        return $processingResult;
    }

    /**
     * Update an existing assessment
     */
    public function update($id, $data): ProcessingResult
    {
        $processingResult = $this->validator->validate($data, true);

        if ($processingResult->isValid()) {
            $data['updated_at'] = date('Y-m-d H:i:s');

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
            } else {
                $processingResult->addInternalError("Error updating PT assessment");
            }
        }

        return $processingResult;
    }

    /**
     * Delete assessment (soft delete by setting status to 'cancelled')
     */
    public function delete($id): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $sql = "UPDATE " . self::ASSESSMENT_TABLE . "
                SET status = 'cancelled', updated_at = ?
                WHERE id = ?";

        $result = sqlStatement($sql, [date('Y-m-d H:i:s'), $id]);

        if ($result !== false) {
            $processingResult->addData(['id' => $id, 'status' => 'cancelled']);
        } else {
            $processingResult->addInternalError("Error deleting PT assessment");
        }

        return $processingResult;
    }

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
}