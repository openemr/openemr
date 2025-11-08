<?php

/**
 * Person Service
 * Manages Person entities with complete CRUD operations and relationship awareness
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\Person;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Services\Utils\DateFormatterUtils;

class PersonService extends BaseService
{
    public const TABLE_NAME = 'person';


    // Default constructor
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }


    /**
     * Create a new person
     *
     * @param array $data Person data
     */
    public function create(array $data): ProcessingResult
    {
        //Future:  if $data has telecom or email, create rows in contactService and contact_telecom

        $processingResult = new ProcessingResult();

        try {
            // Validate data
            $validation = $this->validatePerson($data);
            if (!$validation->isValid()) {
                return $validation;
            }

            // Create person
            $person = new Person();
            $this->populatePersonFromArray($person, $data);

            if (!$person->persist()) {
                $this->getLogger()->error("Person persist() returned false", [
                    'data' => $data
                ]);
                $processingResult->addInternalError("Failed to create person");
                return $processingResult;
            }

            // CRITICAL FIX: Verify the person actually has an ID after persist
            $personId = $person->get_id();
            if ($personId === 0) {
                $this->getLogger()->error("Person persist() succeeded but ID is empty", [
                    'data' => $data
                ]);
                $processingResult->addInternalError("Failed to create person: no ID generated");
                return $processingResult;
            }

            $this->getLogger()->info("Person created successfully", ['id' => $personId]);

            // Get the array representation
            $personArray = $person->toArray();

            // Additional safety check: ensure the array has an id
            if (!isset($personArray['id']) || empty($personArray['id'])) {
                $this->getLogger()->error("Person toArray() returned invalid data", [
                    'person_id' => $personId,
                    'array_has_id_key' => isset($personArray['id']),
                    'array_id_value' => $personArray['id'] ?? 'NOT_SET',
                    'array_keys' => array_keys($personArray)
                ]);
                $processingResult->addInternalError("Failed to create person: invalid data structure");
                return $processingResult;
            }

            $processingResult->addData($personArray);
        } catch (\Exception $e) {
            $this->getLogger()->error("Exception during person creation", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Update an existing person
     */
    public function update(int $personId, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $person = new Person($personId);
            if ($person->get_id() === 0) {
                $processingResult->addInternalError("Person not found");
                return $processingResult;
            }

            // Validate data
            $validation = $this->validatePerson($data, $personId);
            if (!$validation->isValid()) {
                return $validation;
            }

            // Update person
            $this->populatePersonFromArray($person, $data);

            if ($person->persist()) {
                $this->getLogger()->info("Person updated", ['id' => $personId]);
                $processingResult->addData($person->toArray());
            } else {
                $processingResult->addInternalError("Failed to update person");
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error updating person", [
                'id' => $personId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


        /**
     * Get a person by ID
     */
    public function get(int $personId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $person = new Person($personId);
            if ($person->get_id() !== 0) {
                $processingResult->addData($person->toArray());
            } else {
                $processingResult->addInternalError("Person not found");
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error getting person", [
                'id' => $personId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Delete a person
     */
    public function delete(int $personId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Check for dependencies
            $dependencies = $this->checkDependencies($personId);
            if ($dependencies !== []) {
                $processingResult->addInternalError(
                    "Cannot delete person with dependencies: " . implode(", ", array_keys($dependencies))
                );
                return $processingResult;
            }

            $sql = "DELETE FROM person WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$personId]);

            $this->getLogger()->info("Person deleted", ['id' => $personId]);
            $processingResult->addData(['deleted' => true, 'id' => $personId]);
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting person", [
                'id' => $personId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Find or create a person (prevents duplicates)
     *
     * @param array $searchCriteria Criteria to search for existing person
     * @param array $createData Data to create person if not found
     */
    public function findOrCreate(array $searchCriteria, array $createData = []): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $existingResult = $this->search($searchCriteria);

            if ($existingResult->hasData()) {
                $data = $existingResult->getData();
                if (!empty($data)) {
                    $this->getLogger()->debug("Found existing person", [
                        'id' => $data[0]['id']
                    ]);
                    $processingResult->addData($data[0]);
                    return $processingResult;
                }
            }

            // Create new person if not found
            $dataToCreate = array_merge($searchCriteria, $createData);
            return $this->create($dataToCreate);
        } catch (\Exception $e) {
            $this->getLogger()->error("Error in findOrCreate", ['error' => $e->getMessage()]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Search for persons with multiple criteria
     *
     * @param array|string $search Search criteria (array) or search string
     * @param bool $isAndCondition Whether to AND or OR conditions (for compatibility)
     * @param int $limit Maximum results to return
     * @param int $offset Offset for pagination
     */
    public function search($search, $isAndCondition = true, $limit = 100, $offset = 0): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Handle both array and string search formats for compatibility
            $criteria = is_array($search) ? $search : [];

            $sql = "SELECT * FROM person WHERE 1=1";
            $params = [];

            // Build WHERE clause dynamically
            if (!empty($criteria['first_name'])) {
                $sql .= " AND first_name LIKE ?";
                $params[] = $criteria['first_name'] . '%';
            }

            if (!empty($criteria['last_name'])) {
                $sql .= " AND last_name LIKE ?";
                $params[] = $criteria['last_name'] . '%';
            }

            if (!empty($criteria['birth_date'])) {
                $sql .= " AND birth_date = ?";
                $params[] = $criteria['birth_date'];
            }

            if (!empty($criteria['gender'])) {
                $sql .= " AND gender = ?";
                $params[] = $criteria['gender'];
            }

            if (!empty($criteria['full_name'])) {
                $sql .= " AND CONCAT(first_name, ' ', last_name) LIKE ?";
                $params[] = '%' . $criteria['full_name'] . '%';
            }

            $sql .= " ORDER BY last_name, first_name LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $results = QueryUtils::fetchRecords($sql, $params) ?? [];

            foreach ($results as $result) {
                $processingResult->addData($result);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error searching persons", ['error' => $e->getMessage()]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Find persons related to a specific patient
     *
     * @param array $filters Optional filters (relationship type, role, etc.)
     */
    public function findPersonsRelatedToPatient(int $patientId, array $filters = []): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "SELECT p.*,
                    cr.*,
                    cr.id as contact_relation_id,
                    c.id as contact_id
                    FROM person p
                    JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                    JOIN contact_relation cr ON cr.contact_id = c.id
                    WHERE cr.target_table = 'patient_data'
                    AND cr.target_id = ?
                    AND cr.active = 1";

            $params = [$patientId];

            // Apply filters
            if (!empty($filters['relationship'])) {
                $sql .= " AND cr.relationship = ?";
                $params[] = $filters['relationship'];
            }

            if (!empty($filters['role'])) {
                $sql .= " AND cr.role = ?";
                $params[] = $filters['role'];
            }

            if (!empty($filters['is_emergency_contact'])) {
                $sql .= " AND cr.is_emergency_contact = ?";
                $params[] = $filters['is_emergency_contact'];
            }

            $sql .= " ORDER BY cr.contact_priority ASC, p.last_name, p.first_name";

            $results = QueryUtils::fetchRecords($sql, $params) ?? [];

            foreach ($results as $result) {
                $processingResult->addData($result);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error finding related persons", [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


       /**
     * Find persons with specific relationship to any entity
     *
     * @param string $foreignTable
     * @param int $foreignId
     */
    public function findPersonsRelatedToEntity(
        string $targetTable,
        int $targetID,
        array $filters = []
    ): ProcessingResult {
        $processingResult = new ProcessingResult();

        try {
            $sql = "SELECT p.*,
                    cr.*,
                    p.id as person_id,
                    cr.id as contact_relation_id,
                    c.id as contact_id
                    FROM person p
                    JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                    JOIN contact_relation cr ON cr.contact_id = c.id
                    WHERE cr.target_table = ?
                    AND cr.target_id = ?
                    AND cr.active = 1";

            $params = [$targetTable, $targetID];

            if (!empty($filters['relationship'])) {
                $sql .= " AND cr.relationship = ?";
                $params[] = $filters['relationship'];
            }

            $sql .= " ORDER BY cr.contact_priority ASC, p.last_name, p.first_name";

            $results = QueryUtils::fetchRecords($sql, $params) ?? [];

            foreach ($results as $result) {
                $processingResult->addData($result);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error finding related persons", [
                'foreign_table' => $targetTable,
                'foreign_id' => $targetID,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Get all relationships for a person
     */
    public function getPersonRelationships(int $personId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "SELECT cr.*,
                    cr.id as contact_relation_id,
                    c.id as contact_id,
                    c.foreign_table_name as owner_table,
                    c.foreign_id as owner_id
                    FROM contact c
                    JOIN contact_relation cr ON cr.contact_id = c.id
                    WHERE c.foreign_table_name = 'person'
                    AND c.foreign_id = ?
                    AND cr.active = 1
                    ORDER BY cr.contact_priority ASC";


            $results = QueryUtils::fetchRecords($sql, [$personId]) ?? [];

            foreach ($results as $result) {
                $processingResult->addData($result);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error getting person relationships", [
                'person_id' => $personId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError($e->getMessage());
        }

        return $processingResult;
    }


    /**
     * Validate person data
     *
     * @param int|null $personId For updates
     */
    private function validatePerson(array $data, ?int $personId = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $errors = [];

        // Required fields
        if (empty($data['first_name']) && empty($data['last_name'])) {
            $errors['name'] = "Either first name or last name is required";
        }

        // Date validation
        if (!empty($data['birth_date'])) {
            $birthDate = DateFormatterUtils::dateStringToDateTime($data['birth_date']);
            if ($birthDate === false) {
                $errors['birth_date'] = "Invalid birth date format";
            } elseif ($birthDate > new \DateTime()) {
                $errors['birth_date'] = "Birth date cannot be in the future";
            }
        }

        if (!empty($data['death_date'])) {
            $deathDate = DateFormatterUtils::dateStringToDateTime($data['death_date']);
            if ($deathDate === false) {
                $errors['death_date'] = "Invalid death date format";
            }

            // Check if death date is after birth date
            if (!empty($data['birth_date']) && isset($birthDate) && $deathDate !== false && $deathDate < $birthDate) {
                $errors['death_date'] = "Death date cannot be before birth date";
            }
        }

        // Check for duplicates (unless updating)
        if ($personId === null || $personId === 0) {
            $duplicateCheck = $this->checkForDuplicates($data);
            if ($duplicateCheck !== null && $duplicateCheck !== []) {
                $errors['duplicate'] = "Possible duplicate person found: ID " . $duplicateCheck['id'];
            }
        }

        if ($errors !== []) {
            $processingResult->setValidationMessages($errors);
        } else {
            $processingResult->addData($data);
        }

        return $processingResult;
    }


     /**
     * Check for duplicate persons
     */
    private function checkForDuplicates(array $data): ?array
    {
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['birth_date'])) {
            return null;
        }

        $sql = "SELECT id FROM person
                WHERE first_name = ?
                AND last_name = ?
                AND birth_date = ?
                LIMIT 1";

        return QueryUtils::querySingleRow($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['birth_date']
        ]) ?: null;
    }


    /**
     * Check dependencies before deletion
     */
    private function checkDependencies(int $personId): array
    {
        $dependencies = [];

        // Check for contact
        $sql = "SELECT id FROM contact WHERE foreign_table = 'person' AND foreign_id = ?";
        $result = QueryUtils::querySingleRow($sql, [$personId]);
        if ($result) {
            $contactId = $result['id'];

            // Check for relationships
            $sql = "SELECT COUNT(*) as count FROM contact_relation WHERE contact_id = ?";
            $result = QueryUtils::querySingleRow($sql, [$contactId]);
            if ($result['count'] > 0) {
                $dependencies['relationships'] = $result['count'];
            }

            // Check for addresses
            $sql = "SELECT COUNT(*) as count FROM contact_address WHERE contact_id = ?";
            $result = QueryUtils::querySingleRow($sql, [$contactId]);
            if ($result['count'] > 0) {
                $dependencies['addresses'] = $result['count'];
            }

            // Check for telecoms
            $sql = "SELECT COUNT(*) as count FROM contact_telecom WHERE contact_id = ?";
            $result = QueryUtils::querySingleRow($sql, [$contactId]);
            if ($result['count'] > 0) {
                $dependencies['telecoms'] = $result['count'];
            }
        }

        return $dependencies;
    }


    /**
     * Populate Person object from array data
     */
    private function populatePersonFromArray(Person $person, array $data): void
    {
        if (isset($data['first_name'])) {
            $person->set_first_name($data['first_name']);
        }

        if (isset($data['last_name'])) {
            $person->set_last_name($data['last_name']);
        }

        if (isset($data['middlename'])) {
            $person->set_middle_name($data['middle_name']);
        }

        if (isset($data['title'])) {
            $person->set_title($data['title']);
        }

        if (isset($data['preferred_name'])) {
            $person->set_preferred_name($data['preferred_name']);
        }

        if (isset($data['gender'])) {
            $person->set_gender($data['gender']);
        }

        if (isset($data['marital_status'])) {
            $person->set_marital_status($data['marital_status']);
        }

        if (isset($data['race'])) {
            $person->set_race($data['race']);
        }

        if (isset($data['ethnicity'])) {
            $person->set_ethnicity($data['ethnicity']);
        }

        if (isset($data['preferred_language'])) {
            $person->set_preferred_language($data['preferred_language']);
        }

        if (isset($data['communication'])) {
            $person->set_communication($data['communication']);
        }

        if (isset($data['ssn'])) {
            $person->set_ssn($data['ssn']);
        }

        if (isset($data['notes'])) {
            $person->set_notes($data['notes']);
        }

        if (!empty($data['birth_date'])) {
            $birthDate = DateFormatterUtils::dateStringToDateTime($data['birth_date']);
            if ($birthDate !== false) {
                $person->set_birth_date($birthDate);
            }
        }

        if (!empty($data['death_date'])) {
            $deathDate = DateFormatterUtils::dateStringToDateTime($data['death_date']);
            if ($deathDate !== false) {
                $person->set_death_date($deathDate);
            }
        }
    }
}
