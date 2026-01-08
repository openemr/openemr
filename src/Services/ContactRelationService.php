<?php

/**
 * Contact Relation Service - Generic Relationship Management
 * Manages relationships between contacts and other entities
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use League\Csv\Exception;
use OpenEMR\Common\ORDataObject\ContactRelation;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\ListService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Validators\ProcessingResult;

class ContactRelationService extends BaseService
{
    public const TABLE_NAME = 'contact_relation';

    private readonly ListService $listService;

    private readonly ContactService $contactService;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        $this->listService = new ListService();
        $this->contactService = new ContactService();
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'person_uuid'];
    }

    /**
     * Create a new relationship between a contact and another entity
     *
     * @param int $contactId The contact who owns this relationship
     * @param string $targetTable The table of the entity being related to
     * @param int $targetId The ID of the entity being related to
     * @param array $metadata Additional relationship data (relationship, role, etc.)
     */
    public function createRelationship(
        int $contactId,
        string $targetTable,
        int $targetId,
        array $metadata = []
    ): ProcessingResult {
        $processingResult = new ProcessingResult();

        try {
            // Check if relationship already exists
            if ($this->relationshipExists($contactId, $targetTable, $targetId)) {
                $processingResult->addValidationError('relationship', 'Relationship already exists');
                return $processingResult;
            }

            // Create relationship
            $relation = new ContactRelation();
            $relation->set_contact_id($contactId);
            $relation->set_target_table($targetTable);
            $relation->set_target_id($targetId);

            // Populate metadata
            $this->populateRelationFromArray($relation, $metadata);

            if (!$relation->persist()) {
                $processingResult->addInternalError("Failed to create relationship");
                return $processingResult;
            }

            $this->getLogger()->info("Relationship created", [
                'contact_relation_id' => $relation->get_id(),
                'contact_id' => $contactId,
                'target_table' => $targetTable,
                'target_id' => $targetId
            ]);

            $processingResult->addData($relation->toArray());
        } catch (\Exception $e) {
            $this->getLogger()->error("Error creating relationship", [
                'contact_id' => $contactId,
                'target_table' => $targetTable,
                'target_id' => $targetId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Update an existing relationship
     */
    public function updateRelationship(int $relationId, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $relation = new ContactRelation($relationId);

            if (empty($relation->get_id())) {
                $processingResult->addValidationError('id', 'Relationship not found');
                return $processingResult;
            }

            // Populate updated data
            $this->populateRelationFromArray($relation, $data);

            if (!$relation->persist()) {
                $processingResult->addInternalError("Failed to update relationship");
                return $processingResult;
            }

            $this->getLogger()->info("Relationship updated", [
                'contact_relation_id' => $relationId
            ]);

            $processingResult->addData($relation->toArray());
        } catch (\Exception $e) {
            $this->getLogger()->error("Error updating relationship", [
                'contact_relation_id' => $relationId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Deactivate a relationship (soft delete)
     */
    public function deactivateRelationship(int $relationId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $relation = new ContactRelation($relationId);

            if (empty($relation->get_id())) {
                $processingResult->addValidationError('id', 'Relationship not found');
                return $processingResult;
            }

            $relation->set_active(false);
            $relation->set_end_date(new \DateTime());

            if ($relation->persist()) {
                $this->getLogger()->info("Relationship deactivated", [
                    'contact_relation_id' => $relationId
                ]);
                $processingResult->addData($relation->toArray());
            } else {
                $processingResult->addInternalError("Failed to deactivate relationship");
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deactivating relationship", [
                'contact_relation_id' => $relationId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Delete a relationship (hard delete)
     */
    public function deleteRelationship(int $relationId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "DELETE FROM contact_relation WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$relationId]);

            $this->getLogger()->info("Relationship deleted", [
                'contact_relation_id' => $relationId
            ]);
            $processingResult->addData([
                'deleted' => true,
                'contact_relation_id' => $relationId
            ]);
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting relationship", [
                'contact_relation_id' => $relationId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Get all relationships for a contact (FROM the owner's perspective)
     */
    public function getRelationshipsForContact(int $contactId, bool $includeInactive = false): array
    {
        $sql = "SELECT cr.*,
                cr.id as contact_relation_id
                FROM contact_relation cr
                WHERE cr.contact_id = ?";

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        $sql .= " ORDER BY cr.contact_priority ASC, cr.start_date DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }


    /**
     * Get all relationships TO a specific entity (TO the target's perspective)
     * Returns contacts who have relationships pointing TO this entity
     */
    public function getRelationshipsToEntity(
        string $targetTable,
        int $targetId,
        bool $includeInactive = false
    ): array {
        $sql = "SELECT cr.*,
                cr.id as contact_relation_id,
                c.id as contact_id,
                c.foreign_table_name as owner_table,
                c.foreign_id as owner_id,
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.target_table = ? AND cr.target_id = ?";

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        $sql .= " ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$targetTable, $targetId]) ?? [];
    }

    /**
     * TODO: @adunsulag should this be moved to its own class? Need to balance cohesion vs single responsibility principles
     * @param array<string, ISearchField> $searchParameters
     * @param bool $isAndCondition
     * @return ProcessingResult
     */
    public function searchPatientRelationships(array $searchParameters, bool $isAndCondition = true): ProcessingResult {
        $processingResult = new ProcessingResult();
        $sql = "SELECT
            puuid
            ,pers.person_uuid
            ,cr.active
            ,lo_relationship.relationship_code
            ,lo_relationship.relationship_code_title
            ,pers.fname
            ,pers.lname
            ,cr.updated_date
            ,ct.telecom_id
            ,ct.telecom_status
            ,ct.telecom_use
            ,ct.telecom_value
            ,ct.telecom_period_start
            ,ct.telecom_period_end
            ,ct.telecom_system
            ,addr.address_line1
            ,addr.address_line2
            ,addr.address_city
            ,addr.address_state
            ,addr.address_country
            ,addr.address_postal_code
            ,addr.address_postal_code_plus_four
            ,ca.address_period_start
            ,ca.address_period_end
            ,ca.address_type
            ,ca.address_priority
            ,ca.address_use
            ,ca.address_status
        FROM
            contact_relation cr
            JOIN contact owner_contact ON cr.contact_id = owner_contact.id
            JOIN contact target_contact ON cr.target_id = target_contact.foreign_id
                                                   AND cr.target_table = target_contact.foreign_table_name
            JOIN (
                SELECT
                    uuid AS person_uuid
                    ,id AS person_id
                    ,first_name AS fname
                    ,last_name AS lname
                FROM person
            ) pers ON target_contact.foreign_table_name='person' AND target_contact.foreign_id = pers.person_id
            JOIN (SELECT
                 uuid AS puuid,
                 pid AS patient_id
                 FROM patient_data
             ) pd ON owner_contact.foreign_table_name='patient_data' AND owner_contact.foreign_id = pd.patient_id
            JOIN (
                SELECT
                    option_id AS relationship_code
                    , title AS relationship_code_title
                FROM list_options WHERE list_id = 'related_person_relationship'
            ) lo_relationship ON cr.relationship = lo_relationship.relationship_code
            JOIN (
                SELECT
                    contact_id
                    ,address_id
                    ,priority AS address_priority
                    ,type AS address_type
                    ,`use` AS address_use
                    ,`status` AS address_status
                    ,period_start AS address_period_start
                    ,period_end AS address_period_end
                FROM contact_address
            ) ca ON target_contact.id = ca.contact_id
            JOIN (
                SELECT
                    id AS address_id
                    ,line1 AS address_line1
                    ,line2 AS address_line2
                    ,city AS address_city
                    ,state AS address_state
                    ,zip AS address_postal_code
                    ,plus_four AS address_postal_code_plus_four
                     ,country AS address_country
                 FROM
                    addresses
            ) addr ON addr.address_id = ca.address_id
            JOIN (
                SELECT
                    id AS telecom_id,
                    contact_id,
                    `use` AS telecom_use,
                    `system` AS telecom_system,
                    `value` AS telecom_value,
                    `status` AS telecom_status,
                    period_start AS telecom_period_start,
                    period_end AS telecom_period_end
                FROM
                    contact_telecom
            ) ct ON target_contact.id = ct.contact_id
        ";

        $fhirWhereClause = FhirSearchWhereClauseBuilder::build($searchParameters, $isAndCondition);
        $sql .= $fhirWhereClause->getFragment();
        $params = $fhirWhereClause->getBoundValues();
        try {
            $records = QueryUtils::fetchRecords($sql, $params) ?? [];
            $indexedResults = [];
            $personByUuids = [];
            foreach ($records as $record) {
                $record = $this->createResultRecordFromDatabaseResult($record);
                $uuid = $record['person_uuid'];
                if (!isset($personByUuids[$uuid])) {
                    $personByUuids[$record['person_uuid']] = $this->getPersonFromRecord($record);
                    $indexedResults[] = $uuid;
                }
                $telecom_id = $record['telecom_id'];
                if (!isset($personByUuids[$uuid]['telecom'][$telecom_id])) {
                    $personByUuids[$uuid]['telecom'][$telecom_id] = $this->getTelecomFromRecord($record);
                }
                $address_id = $record['address_id'];
                if (!isset($personByUuids[$uuid]['addresses'][$address_id])) {
                    $personByUuids[$uuid]['addresses'][$address_id] = $this->getAddressFromRecord($record);
                }
            }
            foreach ($indexedResults as $recordUuid) {
                $processingResult->addData($personByUuids[$recordUuid]);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error searching patient relationships", [
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }
        return $processingResult;
    }


    /**
     * Get relationships with full details from the target entity (generic for any entity type)
     * Automatically detects the entity table from the contact_relation record and joins to get all details
     *
     * @param int $contactId The contact whose relationships we're querying
     * @param bool $includeInactive Whether to include inactive relationships
     * @return array Array of relationships with entity details from all target entity types
     */
    public function getRelationshipsWithDetails(
        int $ownerContactId,
        bool $includeInactive = false
    ): array {
        // Get all relationships with owner entity info from contact table
        $sql = "SELECT cr.*,
                cr.id as owner_contact_relation_id,
                c.id as owner_contact_id,
                c.foreign_table_name as owner_table,
                c.foreign_id as owner_id,
                target_contact.id AS target_contact_id
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                LEFT JOIN contact target_contact ON cr.target_id = target_contact.foreign_id
                                               AND cr.target_table = target_contact.foreign_table_name
                WHERE cr.contact_id = ?";

        $params = [$ownerContactId];

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        $sql .= " ORDER BY cr.contact_priority ASC";

        $relationships = QueryUtils::fetchRecords($sql, $params) ?? [];

        // Now for each relationship, fetch the target entity details
        $results = [];
        foreach ($relationships as $relationship) {
            $targetTable = $relationship['target_table'];
            $targetId = $relationship['target_id'];

            // Validate table name for security
            $validTables = ['person', 'patient_data', 'company', 'users'];
            if (!in_array($targetTable, $validTables)) {
                $this->getLogger()->warning("Skipping invalid target table", [
                    'table' => $targetTable,
                    'contact_id' => $ownerContactId
                ]);
                $results[] = $relationship; // Include without entity details
                continue;
            }

            // Fetch entity details with aliased ID column
            $entitySql = "SELECT *, id as {$targetTable}_id FROM {$targetTable} WHERE id = ?";
            $entityDetails = QueryUtils::querySingleRow($entitySql, [$targetId]);

            // Merge relationship data with entity details
            if ($entityDetails) {
                unset($entityDetails['active']);
                $results[] = array_merge($relationship, $entityDetails);
            } else {
                $results[] = $relationship; // Include without entity details if entity not found
            }
        }

        return $results;
    }


    /**
     * Get bidirectional relationships between two entities
     *
     * @param string $targetTable2
     * @param int $targetId2
     */
    public function getBidirectionalRelationships(
        int $contactId1,
        int $contactId2,
        bool $includeInactive = false
    ): array {
        // Get relationships in both directions

        $forward = $this->getRelationshipBetween($contactId1, $contactId2, $includeInactive);

        $backward = $this->getRelationshipBetween($contactId2, $contactId1, $includeInactive);

        return [
            'forward' => $forward,
            'backward' => $backward
        ];
    }


    /**
     * Get specific relationship between two entities
     *
     * @param string $targetTable
     */
    public function getRelationshipBetween(
        int $contactId,
        int $targetId,
        bool $includeInactive = false
    ): array {
        $sql = "SELECT cr.*,
                cr.id as contact_relation_id
                FROM contact_relation cr
                INNER JOIN contact c ON c.foreign_table_name = cr.target_table
                                    AND c.foreign_id = cr.target_id
                WHERE cr.contact_id = ?
                AND c.id = ?";

        if (!$includeInactive) {
            $sql .= " AND cr.active = 1";
        }

        return QueryUtils::fetchRecords($sql, [$contactId, $targetId]) ?? [];
    }


    /**
     * Get relationships by type (person-to-patient, person-to-person, etc.)
     *
     * @param string $ownerTable (e.g., 'person', 'patient_data')
     */
    public function getRelationshipsByEntityTypes(
        string $ownerTable,
        string $targetTable,
        array $filters = []
    ): array {
        $sql = "SELECT cr.*,
                cr.id as contact_relation_id,
                c.id as contact_id,
                c.foreign_table_name as owner_table,
                c.foreign_id as owner_id
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE c.foreign_table_name = ?
                AND cr.target_table = ?";

        $params = [$ownerTable, $targetTable];

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

        if (isset($filters['is_primary_contact'])) {
            $sql .= " AND cr.is_primary_contact = ?";
            $params[] = $filters['is_primary_contact'];
        }

        $sql .= " ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, $params) ?? [];
    }


    /**
     * Check if a relationship already exists
     *
     * @param int $contactId - Owner contact ID
     * @param string $targetTable - Target entity table
     * @param int $targetId - Target entity ID
     */
    public function relationshipExists(int $ownerContactId, string $targetTable, int $targetId): bool
    {
        $sql = "SELECT id
                FROM contact_relation
                WHERE contact_id = ?
                AND target_table = ?
                AND target_id = ?
                AND active = 1
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$ownerContactId, $targetTable, $targetId]);
        return !empty($result);
    }


    /**
     * Get valid relationship types from list options
     */
    public function getValidRelationshipTypes(): array
    {
        static $relationships = null;

        if ($relationships === null) {
            $list = $this->listService->getOptionsByListName('related_person_relationship');
            $relationships = array_reduce($list, function (array $map, array $item): array {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $relationships;
    }


    /**
     * Get valid role types from list options
     */
    public function getValidRoleTypes(): array
    {
        static $roles = null;

        if ($roles === null) {
            $list = $this->listService->getOptionsByListName('related_person_role');
            $roles = array_reduce($list, function (array $map, array $item): array {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $roles;
    }


    /**
     * Transfer all relationships from one contact to another
     */
    public function transferRelationships(
        int $sourceContactId,
        int $destinationContactId
    ): ProcessingResult {
        $processingResult = new ProcessingResult();

        try {
            $sql = "UPDATE contact_relation SET contact_id = ? WHERE contact_id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$destinationContactId, $sourceContactId]);

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
            $this->getLogger()->error("Error transferring relationships", [
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Get emergency contacts for an entity
     */
    public function getEmergencyContacts(string $foreignTable, int $foreignId): array
    {
        $sql = "SELECT cr.*, c.foreign_table_name, c.foreign_id,
                CASE
                    WHEN c.foreign_table_name = 'person' THEN
                        (SELECT CONCAT(p.first_name, ' ', p.last_name) FROM person p WHERE p.id = c.foreign_id)
                    ELSE 'Unknown'
                END as contact_name
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.target_table = ?
                AND cr.target_id = ?
                AND cr.is_emergency_contact = 1
                AND cr.active = 1
                ORDER BY cr.contact_priority ASC";

        return QueryUtils::fetchRecords($sql, [$foreignTable, $foreignId]) ?? [];
    }


    /**
     * Get or create a person record for a patient
     * If patient already has a linked person, return that person's ID
     * If not, create a new person from patient demographics and link them
     *
     * @param int $patientId The patient_data.id
     * @return int The person.id
     */
    private function getOrCreatePersonForPatient(int $patientId): int
    {
        $linkService = new PersonPatientLinkService();
        $personService = new PersonService();

        // Check if patient already has a linked person
        $existingPerson = $linkService->getPersonForPatient($patientId);

        if ($existingPerson) {
            $this->getLogger()->debug("Patient already has linked person", [
                'patient_id' => $patientId,
                'person_id' => $existingPerson['id']
            ]);
            return (int)$existingPerson['id'];
        }

        // Get patient demographics
        $patient = QueryUtils::querySingleRow("SELECT * FROM patient_data WHERE id = ?", [$patientId]);

        if (!$patient) {
            throw new \Exception("Patient not found: $patientId");
        }

        // Create person from patient demographics
        $personData = [
            'first_name' => $patient['fname'] ?? '',
            'last_name' => $patient['lname'] ?? '',
            'middle_name' => $patient['mname'] ?? '',
            'title' => $patient['title'] ?? '',
            'suffix' => $patient['suffix'] ?? '',
            'birth_date' => $patient['DOB'] ?? '',
            'gender' => $patient['sex'] ?? '',
            'ssn' => $patient['ss'] ?? '',
            'email_direct' => $patient['email_direct'] ?? '',
            'phone_contact' => $patient['phone_contact'] ?? '',
            'notes' => "Auto-created from patient PID: " . ($patient['pid'] ?? $patientId)
        ];

        $result = $personService->create($personData);

        if (!$result->hasData()) {
            throw new \Exception("Failed to create person for patient: $patientId");
        }

        // getData() returns an array of items, get the first one
        $personData = $result->getData()[0] ?? $result->getData();
        if (empty($personData['id'])) {
            throw new \Exception("Failed to create person for patient: no ID returned");
        }

        $personId = $personData['id'];

        // Link the person to the patient
        $linkResult = $linkService->linkPersonToPatient(
            $personId,
            $patientId,
            $_SESSION['authUserID'] ?? null,
            'auto_created_for_relationship',
            'Auto-created when adding relationship to existing patient'
        );

        if (!$linkResult->hasData()) {
            throw new \Exception("Failed to link person to patient: person=$personId, patient=$patientId");
        }

        $this->getLogger()->info("Created and linked person for patient", [
            'patient_id' => $patientId,
            'patient_pid' => $patient['pid'] ?? 'unknown',
            'person_id' => $personId
        ]);

        return $personId;
    }


    /**
     * Batch save relationships from form data (for LBF relation datatype)
     *
     * @param string $ownerTable The table that owns these relationships
     * @param int $ownerId The ID in that table
     * @param array $relatedPersonData Array of relationship data from form
     * @return array Array of saved relationship records
     */
    public function saveRelatedPersons(
        string $ownerTable,
        int $ownerId,
        array $relatedPersonData
    ): array {
        $committed = false;
        try {
            QueryUtils::startTransaction();
            $this->getLogger()->debug("Batch saving relationships", [
                'owner_table' => $ownerTable,
                'owner_id' => $ownerId,
                'related_person_data' => $relatedPersonData
            ]);

            $savedRecords = [];

            if ($relatedPersonData === []) {
                return $savedRecords;
            }

            // Iterate through array of RelatedPerson objects
            foreach ($relatedPersonData as $index => $relatedPerson) {
                $action = $relatedPerson['data_action'] ?? '';

                if (empty($action)) {
                    continue;
                }

                $owner_contact_relation_id = $relatedPerson['owner_contact_relation_id'] ?? null;

                // Handle INACTIVATE/DELETE
                if ($action == 'INACTIVATE' || $action == 'DELETE') {
                    if (!empty($owner_contact_relation_id)) {
                        $result = $this->deactivateRelationship($owner_contact_relation_id);
                        if ($result->hasData()) {
                            $savedRecords[] = $result->getData()[0];
                        }
                    }
                    continue;
                }

                // Handle ADD and UPDATE
                $ownerContactId = $relatedPerson['owner_contact_id'] ?? null;

                if (empty($ownerContactId)) {
                    $this->getLogger()->warning("Missing contact_id for relation", [
                        'index' => $index
                    ]);
                    continue;
                }

                // Get target table and ID
                $targetTable = $relatedPerson['target_table'] ?? 'person';
                $targetId = $relatedPerson['target_id'] ?? null;

                if (empty($targetId)) {
                    $this->getLogger()->warning("Missing target ID for relation", [
                        'index' => $index,
                        'target_table' => $targetTable
                    ]);
                    continue;
                }
                $targetId = (int)$targetId;

                // If target is a patient, get or create their person record
                if ($targetTable === 'patient_data') {
                    try {
                        $targetPersonId = $this->getOrCreatePersonForPatient($targetId);
                        $targetTable = 'person';
                        $targetId = $targetPersonId;

                        $this->getLogger()->debug("Resolved patient to person", [
                            'patient_id' => $targetId,
                            'person_id' => $targetPersonId
                        ]);
                    } catch (\Exception $e) {
                        $this->getLogger()->error("Failed to get/create person for patient", [
                            'patient_id' => $targetId,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                } else {
                    // Target is already a person
                    $targetTable = 'person';
                }

                $person_metadata = [
                    'first_name' => $relatedPerson['first_name'] ?? '',
                    'middle_name' => $relatedPerson['middle_name'] ?? '',
                    'last_name' => $relatedPerson['last_name'] ?? '',
                    'gender' => $relatedPerson['gender'] ?? '',
                    'birth_date' => $relatedPerson['birth_date'] ?? ''
                ];

                if ($action == 'UPDATE') {
                    if (empty($targetId)) {
                        $this->getLogger()->warning("Missing person_id for UPDATE", [
                            'index' => $index
                        ]);
                        continue;
                    } else {  // UPDATE
                        $personService = new PersonService();
                        $personService->update($targetId, $person_metadata);
                    }
                }

                $contact_relation_metadata = [
                    'relationship' => $relatedPerson['relationship'] ?? '',
                    'role' => $relatedPerson['role'] ?? '',
                    'contact_priority' => $relatedPerson['contact_priority'] ?? 1,
                    'is_primary_contact' => $relatedPerson['is_primary_contact'] ?? false,
                    'is_emergency_contact' => $relatedPerson['is_emergency_contact'] ?? false,
                    'can_make_medical_decisions' => $relatedPerson['can_make_medical_decisions'] ?? false,
                    'can_receive_medical_info' => $relatedPerson['can_receive_medical_info'] ?? false,
                    'start_date' => $relatedPerson['start_date'] ?? '',
                    'end_date' => $relatedPerson['end_date'] ?? '',
                    'notes' => $relatedPerson['notes'] ?? '',
                    'active' => $relatedPerson['active'] ?? true
                ];

                if ($action == 'ADD') {
                    // For ADD, we need to create relationship to target person
                    // createRelationship expects: contactId, targetTable, targetId
                    $result = $this->createRelationship($ownerContactId, $targetTable, $targetId, $contact_relation_metadata);
                } else { // UPDATE
                    if (empty($owner_contact_relation_id)) {
                        $this->getLogger()->warning("Missing relation_id for UPDATE", [
                            'index' => $index
                        ]);
                        continue;
                    }
                    $result = $this->updateRelationship($owner_contact_relation_id, $contact_relation_metadata);
                }

                if ($result->hasData()) {
                    $savedRecords[] = $result->getData()[0];
                }

                // now we need to go through and save the phone number and address information for the connected
                // relationship
                if (!empty($relatedPerson['telecoms'])) {
                    $contact = $this->contactService->getOrCreateForEntity('person', $targetId);
                    $telecomService = new ContactTelecomService();
                    $telecoms = $relatedPerson['telecoms'] ?? [];
                    $telecomService->saveTelecomsForContact($contact->get_id(), $telecoms);
                }

                if (!empty($relatedPerson['addresses'])) {
                    $contact = $this->contactService->getOrCreateForEntity('person', $targetId);
                    $addressService = new ContactAddressService();
                    $addresses = $relatedPerson['addresses'] ?? [];
                    $addressService->saveAddressesForContact($contact->get_id(), $addresses);
                }
            }

            QueryUtils::commitTransaction();
            $committed = true;
        } catch (Exception $exception) {
            $this->getLogger()->error("Error batch saving relationships", [
                'error' => $exception->getMessage()
            ]);
            throw $exception;
        } finally {
            if (!$committed) {
                QueryUtils::rollbackTransaction();
            }
        }

        return $savedRecords;
    }


    /**
     * Populate ContactRelation object from array data
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

        // Convert start_date using DateFormatterUtils to handle different date formats
        // Handle empty strings by setting to null
        if (isset($data['start_date'])) {
            if (!empty($data['start_date'])) {
                $startDate = DateFormatterUtils::dateStringToDateTime($data['start_date']);
                if ($startDate !== false) {
                    $relation->set_start_date($startDate);
                } else {
                    $this->getLogger()->warning("Invalid start_date format", [
                        'start_date' => $data['start_date']
                    ]);
                }
            } else {
                // Empty string means clear the date
                $relation->set_start_date(null);
            }
        }

        // Convert end_date using DateFormatterUtils to handle different date formats
        // Handle empty strings by setting to null
        if (isset($data['end_date'])) {
            if (!empty($data['end_date'])) {
                $endDate = DateFormatterUtils::dateStringToDateTime($data['end_date']);
                if ($endDate !== false) {
                    $relation->set_end_date($endDate);
                } else {
                    $this->getLogger()->warning("Invalid end_date format", [
                        'end_date' => $data['end_date']
                    ]);
                }
            } else {
                // Empty string means clear the date
                $relation->set_end_date(null);
            }
        }

        if (isset($data['notes'])) {
            $relation->set_notes($data['notes']);
        }
        if (isset($data['active'])) {
            $relation->set_active($data['active']);
        }
    }


    /**
     * Get statistics about relationships
     */
    public function getStatistics(): array
    {
        $stats = [];

        // Total relationships
        $sql = "SELECT COUNT(*) as total FROM contact_relation WHERE active = 1";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['total_active'] = (int)$result['total'];

        // By entity type
        $sql = "SELECT c.foreign_table_name, COUNT(*) as count
                FROM contact_relation cr
                JOIN contact c ON c.id = cr.contact_id
                WHERE cr.active = 1
                GROUP BY c.foreign_table_name";
        $results = QueryUtils::fetchRecords($sql, []) ?? [];

        $stats['by_contact_type'] = [];
        foreach ($results as $row) {
            $stats['by_contact_type'][$row['foreign_table_name']] = (int)$row['count'];
        }

        // Emergency contacts
        $sql = "SELECT COUNT(*) as count FROM contact_relation
                WHERE is_emergency_contact = 1 AND active = 1";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['emergency_contacts'] = (int)$result['count'];

        return $stats;
    }

    protected function getPersonFromRecord(array $record): array
    {
        /**
         * puuid
         * ,pers.person_uuid
         * ,cr.active
         * ,lo_relationship.relationship_code
         * ,pers.fname
         * ,pers.lname
         * ,cr.updated_date
         */
        return [
            'uuid' => $record['person_uuid'],
            'puuid' => $record['puuid'],
            'active' => $record['active'],
            'relationship_code' => $record['relationship_code'],
            'relationship_code_title' => $record['relationship_code_title'],
            'fname' => $record['fname'],
            'lname' => $record['lname']
        ];
    }

    protected function getTelecomFromRecord(array $record): array
    {
        return [
            'telecom_id' => $record['telecom_id'],
            'use' => $record['telecom_use'],
            'system' => $record['telecom_system'],
            'value' => $record['telecom_value'],
            'status' => $record['telecom_active']
        ];
    }

    protected function getAddressFromRecord(array $record): array
    {
        return [
            'address_id' => $record['address_id'],
            'line1' => $record['address_line1'],
            'line2' => $record['address_line2'],
            'city' => $record['address_city'],
            'state' => $record['address_state'],
            'postal_code' => $record['address_postalcode'],
            'postal_code_plus_four' => $record['address_postalcode_plus_four'],
            'country' => $record['address_country'],
            'priority' => $record['address_priority'],
            'type' => $record['address_type'],
            'period_start' => $record['address_period_start'],
            'period_end' => $record['address_period_end'],
            'use' => $record['address_use'],
            'status' => $record['address_status']
        ];
    }
}
