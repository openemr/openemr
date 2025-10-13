<?php

/**
 * Person Service
 * Manages Person entities (non-patient individuals)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Validators\ProcessingResult;

class PersonService extends BaseService
{
    public const TABLE_NAME = 'person';
    
    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }
    
    /**
     * Create a new person
     * 
     * @param array $data Person data
     * @return ProcessingResult
     */
    public function create(array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            // Validate required fields
            $validation = $this->validate($data);
            if (!$validation->isValid()) {
                return $validation;
            }
            
            // Prepare data for insertion
            $fields = $this->prepareData($data);
            
            // Build insert query
            $columns = array_keys($fields);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO person (`" . implode('`, `', $columns) . "`) 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $personId = sqlInsert($sql, array_values($fields));
            
            // Fetch and return the created person
            $person = $this->get($personId);
            $processingResult->addData($person);
            
            $this->getLogger()->info("Person created", ['id' => $personId]);
            
        } catch (\Exception $e) {
            $this->getLogger()->error("Error creating person", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }
        
        return $processingResult;
    }
    
    /**
     * Update a person
     * 
     * @param int $personId
     * @param array $data
     * @return ProcessingResult
     */
    public function update(int $personId, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            // Check if person exists
            if (!$this->exists($personId)) {
                $processingResult->addProcessingError("Person not found");
                return $processingResult;
            }
            
            // Validate data
            $validation = $this->validate($data, false); // false = not required for update
            if (!$validation->isValid()) {
                return $validation;
            }
            
            // Prepare data for update
            $fields = $this->prepareData($data);
            
            if (empty($fields)) {
                $processingResult->addProcessingError("No valid fields to update");
                return $processingResult;
            }
            
            // Build update query
            $setClauses = [];
            $values = [];
            foreach ($fields as $field => $value) {
                $setClauses[] = "`$field` = ?";
                $values[] = $value;
            }
            $values[] = $personId;
            
            $sql = "UPDATE person SET " . implode(', ', $setClauses) . " WHERE id = ?";
            sqlStatement($sql, $values);
            
            // Return updated person
            $person = $this->get($personId);
            $processingResult->addData($person);
            
            $this->getLogger()->info("Person updated", ['id' => $personId]);
            
        } catch (\Exception $e) {
            $this->getLogger()->error("Error updating person", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }
        
        return $processingResult;
    }
    
    /**
     * Get a person by ID
     * 
     * @param int $personId
     * @return array|null
     */
    public function get(int $personId): ?array
    {
        $sql = "SELECT * FROM person WHERE id = ?";
        $result = sqlQuery($sql, [$personId]);
        
        return $result ?: null;
    }
    
    /**
     * Delete a person
     * 
     * @param int $personId
     * @return ProcessingResult
     */
    public function delete(int $personId): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            // Check for dependent records
            $dependents = $this->getDependentRecords($personId);
            
            if (!empty($dependents)) {
                $processingResult->addProcessingError(
                    "Cannot delete person with dependent records: " . 
                    implode(", ", array_keys($dependents))
                );
                return $processingResult;
            }
            
            $sql = "DELETE FROM person WHERE id = ?";
            sqlStatement($sql, [$personId]);
            
            $this->getLogger()->info("Person deleted", ['id' => $personId]);
            $processingResult->addData(['deleted' => true, 'id' => $personId]);
            
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting person", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }
        
        return $processingResult;
    }
    
    /**
     * Check if a person exists
     * 
     * @param int $personId
     * @return bool
     */
    public function exists(int $personId): bool
    {
        $sql = "SELECT id FROM person WHERE id = ?";
        $result = sqlQuery($sql, [$personId]);
        
        return !empty($result);
    }
    
    /**
     * Search for persons
     * 
     * @param array $criteria Search criteria
     * @param int $limit
     * @param int $offset
     * @return ProcessingResult
     */
    public function search(array $criteria, int $limit = 100, int $offset = 0): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            $where = [];
            $params = [];
            
            // Build search conditions
            if (!empty($criteria['firstname'])) {
                $where[] = "firstname LIKE ?";
                $params[] = '%' . $criteria['firstname'] . '%';
            }
            
            if (!empty($criteria['lastname'])) {
                $where[] = "lastname LIKE ?";
                $params[] = '%' . $criteria['lastname'] . '%';
            }
            
            if (!empty($criteria['email'])) {
                $where[] = "email = ?";
                $params[] = $criteria['email'];
            }
            
            if (!empty($criteria['phone'])) {
                $where[] = "(phone = ? OR workphone = ?)";
                $params[] = $criteria['phone'];
                $params[] = $criteria['phone'];
            }
            
            if (!empty($criteria['gender'])) {
                $where[] = "gender = ?";
                $params[] = $criteria['gender'];
            }
            
            if (!empty($criteria['birth_date'])) {
                $where[] = "birth_date = ?";
                $params[] = $criteria['birth_date'];
            }
            
            // Build query
            $sql = "SELECT * FROM person";
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY lastname, firstname";
            $sql .= " LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $results = QueryUtils::fetchRecords($sql, $params) ?? [];
            
            foreach ($results as $person) {
                $processingResult->addData($person);
            }
            
        } catch (\Exception $e) {
            $this->getLogger()->error("Error searching persons", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }
        
        return $processingResult;
    }
    
    /**
     * Find persons related to patients
     * 
     * @return array
     */
    public function findRelatedToPatients(): array
    {
        $sql = "SELECT DISTINCT p.*, 
                COUNT(DISTINCT r.id) as relationship_count,
                GROUP_CONCAT(DISTINCT r.relationship) as relationships
                FROM person p
                JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                JOIN relationship r ON r.contact_id = c.id
                WHERE r.related_foreign_table_name = 'patient_data'
                GROUP BY p.id
                ORDER BY p.lastname, p.firstname";
        
        return QueryUtils::fetchRecords($sql) ?? [];
    }
    
    /**
     * Get dependent records for a person
     * 
     * @param int $personId
     * @return array
     */
    private function getDependentRecords(int $personId): array
    {
        $dependents = [];
        
        // Check if person is referenced in contact table
        $sql = "SELECT COUNT(*) as count FROM contact 
                WHERE foreign_table_name = 'person' AND foreign_id = ?";
        $result = sqlQuery($sql, [$personId]);
        if ($result['count'] > 0) {
            $dependents['contact'] = $result['count'];
            
            // Check relationships through contact
            $sql = "SELECT COUNT(*) as count FROM relationship r
                    JOIN contact c ON r.contact_id = c.id
                    WHERE c.foreign_table_name = 'person' AND c.foreign_id = ?";
            $result = sqlQuery($sql, [$personId]);
            if ($result['count'] > 0) {
                $dependents['relationship'] = $result['count'];
            }
        }
        
        return $dependents;
    }
    
    /**
     * Validate person data
     * 
     * @param array $data
     * @param bool $required Whether fields are required (true for create, false for update)
     * @return ProcessingResult
     */
    private function validate(array $data, bool $required = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $errors = [];
        
        if ($required) {
            // Required fields for creation
            if (empty($data['firstname'])) {
                $errors['firstname'] = "First name is required";
            }
            
            if (empty($data['lastname'])) {
                $errors['lastname'] = "Last name is required";
            }
        }
        
        // Validate email format if provided
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format";
        }
        
        // Validate phone format if provided (basic US phone validation)
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) !== 10 && strlen($phone) !== 11) {
                $errors['phone'] = "Invalid phone number format";
            }
        }
        
        if (!empty($data['workphone'])) {
            $workphone = preg_replace('/[^0-9]/', '', $data['workphone']);
            if (strlen($workphone) !== 10 && strlen($workphone) !== 11) {
                $errors['workphone'] = "Invalid work phone number format";
            }
        }
        
        // Validate gender if provided
        if (!empty($data['gender'])) {
            $validGenders = ['Male', 'Female', 'Other', 'Unknown'];
            if (!in_array($data['gender'], $validGenders)) {
                $errors['gender'] = "Invalid gender value";
            }
        }
        
        // Validate birth date format if provided
        if (!empty($data['birth_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['birth_date']);
            if (!$date || $date->format('Y-m-d') !== $data['birth_date']) {
                $errors['birth_date'] = "Invalid date format (use YYYY-MM-DD)";
            }
        }
        
        if (!empty($errors)) {
            $processingResult->setValidationMessages($errors);
        } else {
            $processingResult->addData($data);
        }
        
        return $processingResult;
    }
    
    /**
     * Prepare data for database operations
     * 
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $allowedFields = [
            'firstname', 'lastname', 'gender', 'birth_date',
            'communication', 'phone', 'workphone', 'email'
        ];
        
        $prepared = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $prepared[$field] = $data[$field];
            }
        }
        
        return $prepared;
    }
    
    /**
     * Find or create a person based on demographic data
     * 
     * @param array $data Must include firstname and lastname
     * @return ProcessingResult
     */
    public function findOrCreate(array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            // Try to find existing person (exact match on name and birth date if provided)
            $where = ["firstname = ?", "lastname = ?"];
            $params = [$data['firstname'], $data['lastname']];
            
            if (!empty($data['birth_date'])) {
                $where[] = "birth_date = ?";
                $params[] = $data['birth_date'];
            }
            
            $sql = "SELECT * FROM person WHERE " . implode(" AND ", $where) . " LIMIT 1";
            $existing = sqlQuery($sql, $params);
            
            if ($existing) {
                $processingResult->addData($existing);
                return $processingResult;
            }
            
            // Create new person
            return $this->create($data);
            
        } catch (\Exception $e) {
            $this->getLogger()->error("Error in findOrCreate", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
            return $processingResult;
        }
    }
    
    /**
     * Get statistics about persons
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = [];
        
        // Total persons
        $sql = "SELECT COUNT(*) as total FROM person";
        $result = sqlQuery($sql);
        $stats['total_persons'] = (int)$result['total'];
        
        // Persons with relationships
        $sql = "SELECT COUNT(DISTINCT p.id) as count 
                FROM person p
                JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                JOIN relationship r ON r.contact_id = c.id";
        $result = sqlQuery($sql);
        $stats['with_relationships'] = (int)$result['count'];
        
        // Gender distribution
        $sql = "SELECT gender, COUNT(*) as count 
                FROM person 
                WHERE gender IS NOT NULL 
                GROUP BY gender";
        $results = QueryUtils::fetchRecords($sql) ?? [];
        $stats['by_gender'] = [];
        foreach ($results as $row) {
            $stats['by_gender'][$row['gender']] = (int)$row['count'];
        }
        
        return $stats;
    }
}