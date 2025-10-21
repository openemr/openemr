<?php

/**
 * Contact Relation Service - Generic Relationship Management
 * Manages relationships between ANY entities in the system
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\ContactRelation;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Services\Utils\DateFormatterUtils;

class ContactRelationService extends BaseService
{
    public const TABLE_NAME = 'contact_relation';

    private $listService;

    // Default constructor
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        $this->listService = new ListService();
    }


    /**
     * Create a relationship between any two entities
     * Generic design - works with any entity types
     *
     * @param int $contactId The contact ID (from contact table)
     * @param string $relatedForeignTable The target entity table name
     * @param int $relatedForeignId The target entity ID
     * @param array $metadata Additional relationship data
     * @return ProcessingResult
     */
    public function createRelationship(int $contactId, string $relatedForeignTable, int $relatedForeignId, array $metadata = []): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            if ($this->relationshipExists($contactId, $relatedForeignTable, $relatedForeignId)) {
                $processingResult->addInternalError("Relationship already exists");
                return $processingResult;
            }

            $relation = new ContactRelation();
            $relation->set_contact_id($contactId);
            $relation->set_related_foreign_table_name($relatedForeignTable);
            $relation->set_related_foreign_table_id($relatedForeignId);

            // Set metadata
            $this->populateRelationFromArray($relation, $metadata);

            if ($relation->persist()) {
                $processingResult->addData($relation->toArray());
            } else {
                $processingResult->addInternalError("Failed to create relationship");
            }

        } catch (\Exception $e) {
            $this->getLogger()->error("Error creating relationship", ['error' => $e->getMessage()]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Update an existing relationship
     *
     * @param int $relationId
     * @param array $data
     * @return ProcessingResult
     */
    public function updateRelationship(int $relationId, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $relation = new ContactRelation($relationId);
            if (empty($relation->get_id())) {
                $processingResult->addInternalError("Relationship not found");
                return $processingResult;
            }

            $this->populateRelationFromArray($relation, $data);

            if ($relation->persist()) {
                $processingResult->addData($relation->toArray());
            }

        } catch (\Exception $e) {
            $this->getLogger()->error("Error updating relationship", ['error' => $e->getMessage()]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Deactivate a relationship
     *
     * @param int $relationId
     * @param \DateTime|null $endDate
     * @return ProcessingResult
     */
    public function deactivateRelationship(int $relationId, ?\DateTime $endDate = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $relation = new ContactRelation($relationId);
            if (empty($relation->get_id())) {
                $processingResult->addInternalError("Relationship not found");
                return $processingResult;
            }

            $relation->set_active(false);
            $relation->set_end_date($endDate ?? new \DateTime());

            if ($relation->persist()) {
                $this->getLogger()->info("Relationship deactivated", ['id' => $relationId]);
                $processingResult->addData($relation->toArray());
           	} else {
                $processingResult->addInternalError("Failed to deactivate relationship");
            }

        } catch (\Exception $e) {
            $this->getLogger()->error("Error deactivating relationship", [
                'id' => $relationId,
                'error' => $e->getMessage()
            ]);
           	$processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Delete a relationship
     *
     * @param int $relationId
     * @return ProcessingResult
     */
    public function deleteRelationship(int $relationId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "DELETE FROM contact_relation WHERE id = ?";
            sqlStatement($sql, [$relationId]);

            $this->getLogger()->info("Relationship deleted", ['id' => $relationId]);
            $processingResult->addData(['deleted' => true, 'id' => $relationId]);

        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting relationship", [
                'id' => $relationId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Get all relationships for a contact
     * Query FROM the contact's perspective
     *
     * @param int $contactId
     * @param bool $includeInactive
     * @return array
     */
    public function getRelationshipsForContact(int $contactId, bool $includeInactive = false): array
    {
        $sql = "SELECT * FROM contact_relation WHERE contact_id = ?";

        if (!$includeInactive) {
            $sql .= " AND active = 1";
        }

        $sql .= " ORDER BY contact_priority ASC, start_date DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }


    /**
     * Get all relationships TO a specific entity
     * Query TO the target entity's perspective
     *
     * @param string $foreignTable
     * @param int $foreignId
     * @param bool $includeInactive
     * @return array
     */
    public function getRelationshipsToEntity(string $foreignTable, int $foreignId, bool $includeInactive = false): array
    {
        $sql = "SELECT cr.*, c.foreign_table_name as contact_table, c.foreign_id as contact_foreign_id
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.related_foreign_table_name = ? AND cr.related_foreign_table_id = ?";

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        $sql .= " ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$foreignTable, $foreignId]) ?? [];
    }


    /**
     * Get relationships with full contact details (person data)
     *
     * @param string $foreignTable
     * @param int $foreignId
     * @param bool $includeInactive
     * @return array
     */
    public function getRelationshipsWithPersonDetails(
        string $foreignTable,
        int $foreignId,
        bool $includeInactive = false
    ): array {
        $sql = "SELECT cr.*,
                c.id as contact_id, c.foreign_table_name, c.foreign_id,
                p.firstname, p.lastname, p.gender, p.birth_date, p.email
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                LEFT JOIN person p ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                WHERE cr.related_foreign_table_name = ?
                AND cr.related_foreign_table_id = ?";

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        $sql .= " ORDER BY cr.contact_priority ASC, p.lastname, p.firstname";

        return QueryUtils::fetchRecords($sql, [$foreignTable, $foreignId]) ?? [];
    }


    /**
     * Get bidirectional relationships between two entities
     *
     * @param int $contactId1
     * @param string $foreignTable2
     * @param int $foreignId2
     * @param bool $includeInactive
     * @return array
     */
    public function getBidirectionalRelationships(
        int $contactId1,
        string $foreignTable2,
        int $foreignId2,
        bool $includeInactive = false
    ): array {
        // Get relationships in both directions
        $forward = $this->getRelationshipBetween($contactId1, $foreignTable2, $foreignId2, $includeInactive);

        // Get contact for second entity
        $sql = "SELECT id FROM contact WHERE foreign_table_name = ? AND foreign_id = ?";
        $contact2 = sqlQuery($sql, [$foreignTable2, $foreignId2]);

        $backward = [];
        if ($contact2) {
            // Get contact details for first entity
            $sql = "SELECT foreign_table_name, foreign_id FROM contact WHERE id = ?";
            $contact1Details = sqlQuery($sql, [$contactId1]);

            if ($contact1Details) {
                $backward = $this->getRelationshipBetween(
                    $contact2['id'],
                    $contact1Details['foreign_table_name'],
                    $contact1Details['foreign_id'],
                    $includeInactive
                );
            }
        }

        return [
            'forward' => $forward,
            'backward' => $backward
        ];
    }


    /**
     * Get specific relationship between two entities
     *
     * @param int $contactId
     * @param string $foreignTable
     * @param int $foreignId
     * @param bool $includeInactive
     * @return array
     */
    public function getRelationshipBetween(
        int $contactId,
        string $foreignTable,
        int $foreignId,
        bool $includeInactive = false
        ): array
    {
        $sql = "SELECT * FROM contact_relation
                WHERE contact_id = ?
                AND related_foreign_table_name = ?
                AND related_foreign_table_id = ?";

        if (!$includeInactive) {
            $sql .= " AND active = 1";
        }

        return QueryUtils::fetchRecords($sql, [$contactId, $foreignTable, $foreignId]) ?? [];
    }


       /**
     * Get relationships by type (person-to-patient, person-to-person, etc.)
     *
     * @param string $contactEntityType (e.g., 'person', 'patient_data')
     * @param string $relatedEntityType
     * @param array $filters
     * @return array
     */
    public function getRelationshipsByEntityTypes(
        string $contactEntityType,
        string $relatedEntityType,
        array $filters = []
    ): array {
        $sql = "SELECT cr.*,
                c.foreign_table_name as contact_table, c.foreign_id as contact_foreign_id
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE c.foreign_table_name = ?
                AND cr.related_foreign_table_name = ?";

        $params = [$contactEntityType, $relatedEntityType];

        // Apply filters
        if (!empty($filters['active'])) {
            $sql .= " AND cr.active = ?";
            $params[] = $filters['active'];
        }

        if (!empty($filters['relationship'])) {
            $sql .= " AND cr.relationship = ?";
            $params[] = $filters['relationship'];
        }

        if (!empty($filters['role'])) {
            $sql .= " AND cr.role = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['is_emergency_contact'])) {
            $sql .= " AND cr.is_emergency_contact = ?";
            $params[] = $filters['is_emergency_contact'];
        }

        $sql .= " ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, $params) ?? [];
    }


    /**
     * Get emergency contacts for an entity
     *
     * @param string $foreignTable
     * @param int $foreignId
     * @return array
     */
    public function getEmergencyContacts(string $foreignTable, int $foreignId): array
    {
        $sql = "SELECT cr.*, c.foreign_table_name, c.foreign_id,
                CASE
                    WHEN c.foreign_table_name = 'person' THEN
                        (SELECT CONCAT(p.firstname, ' ', p.lastname) FROM person p WHERE p.id = c.foreign_id)
                    ELSE 'Unknown'
                END as contact_name
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.related_foreign_table_name = ?
                AND cr.related_foreign_table_id = ?
                AND cr.is_emergency_contact = 1
                AND cr.active = 1
                ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$foreignTable, $foreignId]) ?? [];
    }


    /**
     * Save relationships for an entity from form data
     *
     * @param string $foreignTable
     * @param int $foreignId
     * @param array $relationData
     * @return array
     */
    public function saveRelationsForEntity(string $foreignTable, int $foreignId, array $relationData): array
    {
    	$this->getLogger()->debug("Saving relations for entity", [
            'foreign_table' => $foreignTable,
            'foreign_id' => $foreignId,
            'relation_count' => count($relationData['data_action'] ?? [])
        ]);

        try {
            $savedRecords = [];
            $count = count($relationData['data_action'] ?? []);

            if ($count <= 0) {
                return $savedRecords;
            }

            for ($i = 0; $i < $count; $i++) {
                $action = $relationData['data_action'][$i] ?? '';

                if (empty($action)) {
                    continue;
                }

                $relationId = $relationData['relation_id'][$i] ?? null;

                // Handle INACTIVATE/DELETE
                if ($action == 'INACTIVATE' || $action == 'DELETE') {
                    if (!empty($relationId)) {
                        $result = $this->deactivateRelationship($relationId);
                        if ($result->hasData()) {
                            $savedRecords[] = $result->getData()[0];
                        }
                    }
                    continue;
                }

                // Handle ADD and UPDATE
                $contactId = $relationData['contact_id'][$i] ?? null;

                if (empty($contactId)) {
                    $this->getLogger()->warning("Missing contact_id for relation", ['index' => $i]);
                    continue;
                }

                $metadata = [
                    'relationship' => $relationData['relationship'][$i] ?? '',
                	'role' => $relationData['role'][$i] ?? '',
                    'contact_priority' => $relationData['contact_priority'][$i] ?? 1,
                    'is_primary_contact' => $relationData['is_primary_contact'][$i] ?? false,
                    'is_emergency_contact' => $relationData['is_emergency_contact'][$i] ?? false,
                    'can_make_medical_decisions' => $relationData['can_make_medical_decisions'][$i] ?? false,
                    'can_receive_medical_info' => $relationData['can_receive_medical_info'][$i] ?? false,
                    'notes' => $relationData['notes'][$i] ?? ''
                ];

                if ($action == 'ADD') {
                    $result = $this->createRelationship($contactId, $foreignTable, $foreignId, $metadata);
                } else {
                    $result = $this->updateRelationship($relationId, $metadata);
                }

                if ($result->hasData()) {
                    $savedRecords[] = $result->getData()[0];
                }
            }

            return $savedRecords;

        } catch (\Exception $e) {
            $this->getLogger()->error("Error saving relations for entity", [
                'foreign_table' => $foreignTable,
                'foreign_id' => $foreignId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Check if a relationship exists
     *
     * @param int $contactId
     * @param string $relatedForeignTable
     * @param int $relatedForeignId
     * @return bool
     */
    public function relationshipExists(int $contactId, string $relatedForeignTable, int $relatedForeignId): bool
    {
        $sql = "SELECT id FROM contact_relation
                WHERE contact_id = ?
                AND related_foreign_table_name = ?
                AND related_foreign_table_id = ?
                AND active = 1
                LIMIT 1";

        $result = sqlQuery($sql, [$contactId, $relatedForeignTable, $relatedForeignId]);
        return !empty($result);
    }


    /**
     * Get valid relationship types
     *
     * @return array
     */
    public function getValidRelationshipTypes(): array
    {
        static $relationships = null;

        if ($relationships === null) {
            $list = $this->listService->getOptionsByListName('related_person-relationship');
            $relationships = array_reduce($list, function ($map, $item) {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $relationships;
    }


    /**
     * Get valid role types
     *
     * @return array
     */
    public function getValidRoleTypes(): array
    {
        static $roles = null;

        if ($roles === null) {
            $list = $this->listService->getOptionsByListName('related_person-role');
            $roles = array_reduce($list, function ($map, $item) {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $roles;
    }


    /**
     * Transfer all relationships from one entity to another
     *
     * @param int $sourceContactId
     * @param int $destinationContactId
     * @return ProcessingResult
     */
    public function transferRelationships(int $sourceContactId, int $destinationContactId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "UPDATE contact_relation SET contact_id = ? WHERE contact_id = ?";
            sqlStatement($sql, [$destinationContactId, $sourceContactId]);

            $this->getLogger()->info("Relationships transferred", [
                'source_contact_id' => $sourceContactId,
                'destination_contact_id' => $destinationContactId
            ]);

            $processingResult->addData([
                'transferred' => true,
                'source_contact_id' => $sourceContactId,
                'destination_contact_id' => $destinationContactId
            ]);

        } catch (\Exception $e) {
            $this->getLogger()->error("Error transferring relationships", ['error' => $e->getMessage()]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


   /**
     * Populate ContactRelation object from array data
     *
     * @param ContactRelation $relation
     * @param array $data
     */
    private function populateRelationFromArray(ContactRelation $relation, array $data): void
    {
        if (isset($data['relationship'])) {
            $relation->set_relationship($data['relationship']);
        }

        if (isset($data['role'])) {
            $relation->set_role($data['role']);
        }

        if (isset($data['contact_priority'])) {
            $relation->set_contact_priority($data['contact_priority']);
        }

        if (isset($data['is_primary_contact'])) {
            $relation->set_is_primary_contact($data['is_primary_contact']);
        }

        if (isset($data['is_emergency_contact'])) {
            $relation->set_is_emergency_contact($data['is_emergency_contact']);
        }

        if (isset($data['can_make_medical_decisions'])) {
            $relation->set_can_make_medical_decisions($data['can_make_medical_decisions']);
        }

        if (isset($data['can_receive_medical_info'])) {
            $relation->set_can_receive_medical_info($data['can_receive_medical_info']);
        }

        if (isset($data['notes'])) {
            $relation->set_notes($data['notes']);
        }

        if (!empty($data['start_date'])) {
            $startDate = DateFormatterUtils::dateStringToDateTime($data['start_date']);
            if ($startDate !== false) {
                $relation->set_start_date($startDate);
            }
        }

        if (!empty($data['end_date'])) {
            $endDate = DateFormatterUtils::dateStringToDateTime($data['end_date']);
            if ($endDate !== false) {
                $relation->set_end_date($endDate);
            }
        }
    }


    /**
     * Get statistics about relationships
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = [];

        // Total relationships
        $sql = "SELECT COUNT(*) as total FROM contact_relation WHERE active = 1";
        $result = sqlQuery($sql);
        $stats['total_active'] = (int)$result['total'];

        // By entity type
        $sql = "SELECT c.foreign_table_name, COUNT(*) as count
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.active = 1
                GROUP BY c.foreign_table_name";
        $results = QueryUtils::fetchRecords($sql) ?? [];

        $stats['by_contact_type'] = [];
        foreach ($results as $row) {
            $stats['by_contact_type'][$row['foreign_table_name']] = (int)$row['count'];
        }

        // Emergency contacts
        $sql = "SELECT COUNT(*) as count FROM contact_relation
                WHERE is_emergency_contact = 1 AND active = 1";
        $result = sqlQuery($sql);
        $stats['emergency_contacts'] = (int)$result['count'];

        return $stats;
    }
}
