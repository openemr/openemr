<?php

/**
 * Relationship Service
 * Manages generic relationships between any entities through their contact records
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\RelatedPerson;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Validators\ProcessingResult;

class ContactRelationService extends BaseService
{
    public const TABLE_NAME = 'contact_relation';

    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        $this->contactService = new ContactService();
    }

    /**
     * Create a relationship between two entities via their contacts
     *
     * @param int $contactId The contact ID (who is related)
     * @param string $relatedForeignTable The table of the entity being related to
     * @param int $relatedForeignId The ID of the entity being related to
     * @param array $relationshipData The relationship metadata
     * @return ProcessingResult
     */
    public function createContactRelation(
        int $contactId,
        string $relatedForeignTable,
        int $relatedForeignId,
        array $contactRelationData
    ): ProcessingResult {
        $processingResult = new ProcessingResult();

        try {
            // Verify contact exists
            $contact = $this->contactService->get($contactId);
            if (!$contact) {
                $processingResult->addProcessingError("Contact not found");
                return $processingResult;
            }

            // Check if contact relation already exists
            if ($this->contactRelationExists($contactId, $relatedForeignTable, $relatedForeignId)) {
                $processingResult->addProcessingError(
                    "Relation already exists between these entities"
                );
                return $processingResult;
            }

            // Create the contact relation
            $Person = new Person();
            $Person->set_contact_id($contactId);
            $Person->set_related_foreign_table_name($relatedForeignTable);
            $Person->set_related_foreign_table_id($relatedForeignId);

            // Set relationship metadata
            $this->setContactRelationData($Person, $contactRelationData);

            // Save
            if ($Person->persist()) {
                $processingResult->addData($Person->toArray());

                $this->getLogger()->info("Contact relation created", [
                    'contact_id' => $contactId,
                    'related_table' => $relatedForeignTable,
                    'related_id' => $relatedForeignId
                ]);
            } else {
                $processingResult->addProcessingError("Failed to save contact relation");
            }

        } catch (\Exception $e) {
            $this->getLogger()->error("Error creating contact relation", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Create a bidirectional relationship between two contacts
     *
     * @param int $contactId1
     * @param string $relatedTable1
     * @param int $relatedId1
     * @param int $contactId2
     * @param string $relatedTable2
     * @param int $relatedId2
     * @param array $relationshipData1 Relationship data from entity1's perspective
     * @param array $relationshipData2 Relationship data from entity2's perspective
     * @return ProcessingResult
     */
    public function createBidirectionalContactRelation(
        int $contactId1,
        string $relatedTable1,
        int $relatedId1,
        int $contactId2,
        string $relatedTable2,
        int $relatedId2,
        array $contactRelationData1,
        array $contactRelationData2
    ): ProcessingResult {
        $processingResult = new ProcessingResult();

        try {
            // Create first relationship (entity1 -> entity2)
            $result1 = $this->createContactRelation($contactId1, $relatedTable2, $relatedId2, $contactRelationData1);
            if (!$result1->isValid()) {
                return $result1;
            }

            // Create reverse relationship (entity2 -> entity1)
            $result2 = $this->createContactRelation($contactId2, $relatedTable1, $relatedId1, $contactRelationData2);
            if (!$result2->isValid()) {
                // Rollback first relationship
                $firstContactRelation = $result1->getData()[0];
                $this->deleteContactRelation($firstContactRelation['id']);
                return $result2;
            }

            $processingResult->addData([
                'contactRelation1' => $result1->getData()[0],
                'contactRelation2' => $result2->getData()[0]
            ]);

        } catch (\Exception $e) {
            $this->getLogger()->error("Error creating bidirectional contact relation", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Get all relationships for an entity
     *
     * @param string $foreignTable
     * @param int $foreignId
     * @param bool $includeInactive
     * @return array
     */
    public function getContactRelationsForEntity(
        string $foreignTable,
        int $foreignId,
        bool $includeInactive = false
    ): array {
        $sql = "SELECT r.*,
                c.foreign_table_name as contact_table,
                c.foreign_id as contact_foreign_id
                FROM contact_relation r
                JOIN contact c ON r.contact_id = c.id
                WHERE r.related_foreign_table_name = ?
                AND r.related_foreign_table_id = ?";

        if (!$includeInactive) {
            $sql .= " AND r.active = 1";
        }

        $sql .= " ORDER BY r.contact_priority ASC, r.is_primary_contact DESC";

        $results = QueryUtils::fetchRecords($sql, [$foreignTable, $foreignId]) ?? [];

        // Enhance with entity details based on contact type
        $relations = [];
        foreach ($results as $row) {
            $relations[] = $this->enhanceRelationData($row);
        }

        return $relations;
    }

    /**
     * Get relationships where the entity is the contact (reverse lookup)
     *
     * @param string $contactForeignTable
     * @param int $contactForeignId
     * @param bool $includeInactive
     * @return array
     */
    public function getContactRelationAsContact(
        string $contactForeignTable,
        int $contactForeignId,
        bool $includeInactive = false
    ): array {
        // First get the contact for this entity
        $contact = $this->contactService->getForEntity($contactForeignTable, $contactForeignId);

        if (!$contact) {
            return [];
        }

        $sql = "SELECT r.*,
                r.related_foreign_table_name as related_to_table,
                r.related_foreign_table_id as related_to_id
                FROM contact_relation r
                WHERE r.contact_id = ?";

        if (!$includeInactive) {
            $sql .= " AND r.active = 1";
        }

        $sql .= " ORDER BY r.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$contact->get_id()]) ?? [];
    }

    /**wf
     * Get a specific relationship by ID
     *
     * @param int $relationshipId
     * @return array|null
     */
    public function getConactRelation(int $contactRelationId): ?array
    {
        $sql = "SELECT r.*,
                c.foreign_table_name as contact_table,
                c.foreign_id as contact_foreign_id
                FROM contact_relation r
                JOIN contact c ON r.contact_id = c.id
                WHERE r.id = ?";

        $result = sqlQuery($sql, [$contactRelationId]);

        if (!$result) {
            return null;
        }

        return $this->enhanceConactRelationData($result);
    }

    /**
     * Update an existing relationship
     *
     * @param int $relationshipId
     * @param array $relationshipData
     * @return ProcessingResult
     */
    public function updateConactRelation(int $contactRelationId, array $conactRelationData): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $relatedPerson = new RelatedPerson($contactRelationId);
            if (empty($relatedPerson->get_id())) {
                $processingResult->addProcessingError("Contact Relation not found");
                return $processingResult;
            }

            // Update relationship metadata
            $this->setConactRelationData($relatedPerson, $conactRelationData);

            // Save
            if ($relatedPerson->persist()) {
                $processingResult->addData($relatedPerson->toArray());
            } else {
                $processingResult->addProcessingError("Failed to update relationship");
            }

        } catch (\Exception $e) {
            $this->getLogger()->error("Error updating contact relation", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Delete a relationship
     *
     * @param int $relationshipId
     * @return ProcessingResult
     */
    public function deleteContactRelation(int $contactRelationId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "DELETE FROM contact_relation WHERE id = ?";
            sqlStatement($sql, [$relationshipId]);

            $processingResult->addData(['deleted' => true, 'id' => $contactRelationId]);

        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting relationship", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Get relationships between specific entity types
     *
     * @param string $contactTable Type of entity that is the contact
     * @param string $relatedTable Type of entity being related to
     * @return array
     */
    public function getContactRelationsByTypes(string $contactTable, string $relatedTable): array
    {
        $sql = "SELECT r.*,
                c.foreign_id as contact_foreign_id
                FROM contact_relation r
                JOIN contact c ON r.contact_id = c.id
                WHERE c.foreign_table_name = ?
                AND r.related_foreign_table_name = ?
                AND r.active = 1
                ORDER BY r.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$contactTable, $relatedTable]) ?? [];
    }

    /**
     * Get all person-to-patient relationships
     *
     * @return array
     */
    public function getPersonToPatientContactRelations(): array
    {
        return $this->getContactRelationsByTypes('person', 'patient_data');
    }

    /**
     * Get all company-to-patient relationships (for future use)
     *
     * @return array
     */
    public function getCompanyToPatientContactRelations(): array
    {
        return $this->getContactRelationsByTypes('company', 'patient_data');
    }

    /**
     * Get all person-to-person relationships
     *
     * @return array
     */
    public function getPersonToPersonContactRelations(): array
    {
        return $this->getContactRelationsByTypes('person', 'person');
    }

    /**
     * Check if a relationship already exists
     *
     * @param int $contactId
     * @param string $relatedForeignTable
     * @param int $relatedForeignId
     * @return bool
     */
    private function contacteRlationExists(
        int $contactId,
        string $relatedForeignTable,
        int $relatedForeignId
    ): bool {
        $sql = "SELECT id FROM contact_relation
                WHERE contact_id = ?
                AND related_foreign_table_name = ?
                AND related_foreign_table_id = ?
                LIMIT 1";

        $result = sqlQuery($sql, [$contactId, $relatedForeignTable, $relatedForeignId]);

        return !empty($result);
    }

    /**
     * Set relationship data on a RelatedPerson object
     *
     * @param RelatedPerson $relatedPerson
     * @param array $data
     */
    private function setContactRelation(RelatedPerson $relatedPerson, array $data): void
    {
        if (isset($data['relationship'])) {
            $relatedPerson->set_relationship($data['relationship']);
        }
        if (isset($data['relationship_role'])) {
            $relatedPerson->set_relationship_role($data['relationship_role']);
        }
        if (isset($data['contact_priority'])) {
            $relatedPerson->set_contact_priority((int)$data['contact_priority']);
        }
        if (isset($data['is_primary_contact'])) {
            $relatedPerson->set_is_primary_contact((bool)$data['is_primary_contact']);
        }
        if (isset($data['is_emergency_contact'])) {
            $relatedPerson->set_is_emergency_contact((bool)$data['is_emergency_contact']);
        }
        if (isset($data['can_make_medical_decisions'])) {
            $relatedPerson->set_can_make_medical_decisions((bool)$data['can_make_medical_decisions']);
        }
        if (isset($data['can_receive_medical_info'])) {
            $relatedPerson->set_can_receive_medical_info((bool)$data['can_receive_medical_info']);
        }
        if (isset($data['notes'])) {
            $relatedPerson->set_notes($data['notes']);
        }

        // Handle dates
        if (!empty($data['start_date'])) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $data['start_date']);
            if ($startDate) {
                $relatedPerson->set_start_date($startDate);
            }
        }
        if (!empty($data['end_date'])) {
            $endDate = \DateTime::createFromFormat('Y-m-d', $data['end_date']);
            if ($endDate) {
                $relatedPerson->set_end_date($endDate);
            }
        }
    }

    /**
     * Enhance relationship data with entity details
     *
     * @param array $row
     * @return array
     */
    private function enhanceContactRelationData(array $row): array
    {
        $enhanced = $row;

        // Get details about the contact entity
        if ($row['contact_table'] == 'person') {
            $sql = "SELECT * FROM person WHERE id = ?";
            $person = sqlQuery($sql, [$row['contact_foreign_id']]);
            if ($person) {
                $enhanced['contact_entity'] = [
                    'type' => 'person',
                    'data' => $person
                ];
            }
        } elseif ($row['contact_table'] == 'patient_data') {
            // For patient-to-patient relationships
            $sql = "SELECT id, fname, lname, DOB FROM patient_data WHERE id = ?";
            $patient = sqlQuery($sql, [$row['contact_foreign_id']]);
            if ($patient) {
                $enhanced['contact_entity'] = [
                    'type' => 'patient',
                    'data' => $patient
                ];
            }
        }
        // Add more entity types as needed (company, etc.)

        return $enhanced;
    }
}
