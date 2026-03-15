<?php

/**
 * PatientRelationshipService - handles patient-to-patient relationships.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Entity\PatientRelationship;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\PatientService;
use OpenEMR\Validators\ProcessingResult;

class PatientRelationshipService extends BaseService
{
    private const PATIENT_RELATIONSHIPS_TABLE = "patient_relationships";
    private const PATIENT_DATA_TABLE = "patient_data";

    private readonly PatientService $patientService;

    /**
     * Default constructor.
     */
    public function __construct(PatientService $patientService = null)
    {
        parent::__construct(self::PATIENT_RELATIONSHIPS_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PATIENT_RELATIONSHIPS_TABLE]);
        $this->patientService = $patientService ?? new PatientService();
    }

    /**
     * Create a new patient relationship
     *
     * @param PatientRelationship $relationship The relationship entity
     * @return ProcessingResult
     */
    public function createRelationship(PatientRelationship $relationship): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Validate entity
            $validationErrors = $relationship->validate();
            if (!empty($validationErrors)) {
                $processingResult->setValidationMessages($validationErrors);
                return $processingResult;
            }

            // Verify both patients exist using PatientService
            $patient = $this->patientService->findByPid($relationship->getPatientId());
            $relatedPatient = $this->patientService->findByPid($relationship->getRelatedPatientId());

            if (empty($patient) || empty($relatedPatient)) {
                $processingResult->setValidationMessages(['One or both patients do not exist']);
                return $processingResult;
            }

            // Set UUID and convert to array for database
            $uuid = UuidRegistry::getRegistryForTable(self::PATIENT_RELATIONSHIPS_TABLE)->createUuid();
            $relationship->setUuid(UuidRegistry::uuidToString($uuid));

            $data = $relationship->toArray();
            $data['uuid'] = $uuid; // Keep binary for database

            $query = $this->buildInsertColumns($data);
            $sql = "INSERT INTO " . self::PATIENT_RELATIONSHIPS_TABLE . " SET " . $query['set'];

            $results = QueryUtils::sqlInsert($sql, $query['bind']);
            if ($results) {
                $relationship->setId($results);
                $processingResult->addData([
                    'id' => $results,
                    'uuid' => $relationship->getUuid(),
                    'relationship' => $relationship
                ]);
            } else {
                $processingResult->addInternalError("Failed to create relationship");
            }
        } catch (\Exception $exception) {
            $processingResult->addInternalError("Failed to create relationship: " . $exception->getMessage());
        }

        return $processingResult;
    }

    /**
     * Get all relationships for a patient
     *
     * @param int $patientId The patient ID
     * @return ProcessingResult
     */
    public function getPatientRelationships(int $patientId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "SELECT pr.*,
                           pd1.fname as patient_fname, pd1.lname as patient_lname,
                           pd2.fname as related_fname, pd2.lname as related_lname,
                           lo.title as relationship_title
                    FROM " . self::PATIENT_RELATIONSHIPS_TABLE . " pr
                    LEFT JOIN " . self::PATIENT_DATA_TABLE . " pd1 ON pr.patient_id = pd1.id
                    LEFT JOIN " . self::PATIENT_DATA_TABLE . " pd2 ON pr.related_patient_id = pd2.id
                    LEFT JOIN list_options lo ON pr.relationship_type = lo.option_id AND lo.list_id = 'patient_relationship_types'
                    WHERE (pr.patient_id = ? OR pr.related_patient_id = ?)
                    AND pr.active = 1
                    ORDER BY pr.created_date DESC";

            $results = QueryUtils::fetchRecords($sql, [$patientId, $patientId]);

            // Convert to entities with additional display data
            $relationships = [];
            foreach ($results as $row) {
                $relationships[] = [
                    'entity' => PatientRelationship::fromArray($row),
                    'patient_name' => $row['patient_fname'] . ' ' . $row['patient_lname'],
                    'related_name' => $row['related_fname'] . ' ' . $row['related_lname'],
                    'relationship_title' => $row['relationship_title']
                ];
            }

            $processingResult->setData($relationships);
        } catch (\Exception $exception) {
            $processingResult->addInternalError("Failed to retrieve relationships: " . $exception->getMessage());
        }

        return $processingResult;
    }

    /**
     * Delete a relationship
     *
     * @param int $relationshipId The relationship ID
     * @return ProcessingResult
     */
    public function deleteRelationship(int $relationshipId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "UPDATE " . self::PATIENT_RELATIONSHIPS_TABLE . "
                    SET active = 0
                    WHERE id = ?";

            $result = QueryUtils::sqlStatementThrowException($sql, [$relationshipId]);
            if ($result) {
                $processingResult->addData(['deleted' => true]);
            } else {
                $processingResult->addInternalError("Failed to delete relationship");
            }
        } catch (\Exception $exception) {
            $processingResult->addInternalError("Failed to delete relationship: " . $exception->getMessage());
        }

        return $processingResult;
    }

    /**
     * Get relationship types from list_options
     *
     * @return array
     */
    public function getRelationshipTypes(): array
    {
        $sql = "SELECT option_id, title FROM list_options
                WHERE list_id = 'patient_relationship_types'
                AND activity = 1
                ORDER BY seq";

        return QueryUtils::fetchRecords($sql);
    }
}
