<?php

/**
 * PersonPatientLinkService
 * Manages links between person records and patient_data records
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

class PersonPatientLinkService extends BaseService
{
    public const TABLE_NAME = 'person_patient_link';
    
    private $logger;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        $this->logger = new SystemLogger();
    }

    /**
     * Link a person to a patient
     *
     * @param int $personId
     * @param int $patientId
     * @param int|null $userId User creating the link
     * @param string $linkMethod How the link was created
     * @param string|null $notes Optional notes
     * @return ProcessingResult
     */
    public function linkPersonToPatient(
        int $personId,
        int $patientId,
        ?int $userId = null,
        string $linkMethod = 'manual',
        ?string $notes = null
    ): ProcessingResult {
        $processingResult = new ProcessingResult();
        
        try {
            // Check if person exists
            $person = sqlQuery("SELECT * FROM person WHERE id = ?", [$personId]);
            if (!$person) {
                $processingResult->addInternalError("Person not found: $personId");
                return $processingResult;
            }
            
            // Check if patient exists
            $patient = sqlQuery("SELECT * FROM patient_data WHERE id = ?", [$patientId]);
            if (!$patient) {
                $processingResult->addInternalError("Patient not found: $patientId");
                return $processingResult;
            }
            
            // Check if already linked
            $existing = $this->getActiveLink($personId, $patientId);
            if ($existing) {
                $processingResult->addData($existing);
                $this->logger->debug("Link already exists", [
                    'person_id' => $personId,
                    'patient_id' => $patientId,
                    'link_id' => $existing['id']
                ]);
                return $processingResult;
            }
            
            // Create the link
            $sql = "INSERT INTO person_patient_link 
                    (person_id, patient_id, linked_by, link_method, notes, active) 
                    VALUES (?, ?, ?, ?, ?, 1)";
            
            $result = sqlInsert($sql, [
                $personId,
                $patientId,
                $userId,
                $linkMethod,
                $notes
            ]);
            
            if ($result) {
                $linkData = $this->getLink($result);
                $processingResult->addData($linkData);
                
                $this->logger->info("Person linked to patient", [
                    'link_id' => $result,
                    'person_id' => $personId,
                    'patient_id' => $patientId,
                    'patient_pid' => $patient['pid'],
                    'method' => $linkMethod,
                    'linked_by' => $userId
                ]);
            } else {
                $processingResult->addInternalError("Failed to create link");
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Error linking person to patient", [
                'error' => $e->getMessage(),
                'person_id' => $personId,
                'patient_id' => $patientId
            ]);
            $processingResult->addInternalError($e->getMessage());
        }
        
        return $processingResult;
    }

    /**
     * Unlink a person from a patient (soft delete)
     *
     * @param int $personId
     * @param int $patientId
     * @return ProcessingResult
     */
    public function unlinkPersonFromPatient(int $personId, int $patientId): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        
        try {
            $sql = "UPDATE person_patient_link 
                    SET active = 0 
                    WHERE person_id = ? AND patient_id = ? AND active = 1";
            
            $result = sqlStatement($sql, [$personId, $patientId]);
            
            if ($result) {
                $this->logger->info("Person unlinked from patient", [
                    'person_id' => $personId,
                    'patient_id' => $patientId
                ]);
                $processingResult->addData(['unlinked' => true]);
            } else {
                $processingResult->addInternalError("Link not found or already inactive");
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Error unlinking person from patient", [
                'error' => $e->getMessage(),
                'person_id' => $personId,
                'patient_id' => $patientId
            ]);
            $processingResult->addInternalError($e->getMessage());
        }
        
        return $processingResult;
    }

    /**
     * Get active link between person and patient
     *
     * @param int $personId
     * @param int $patientId
     * @return array|null
     */
    public function getActiveLink(int $personId, int $patientId): ?array
    {
        $sql = "SELECT ppl.*, 
                       u.username as linked_by_username,
                       CONCAT(u.fname, ' ', u.lname) as linked_by_name
                FROM person_patient_link ppl
                LEFT JOIN users u ON u.id = ppl.linked_by
                WHERE ppl.person_id = ? 
                  AND ppl.patient_id = ? 
                  AND ppl.active = 1";
        
        return sqlQuery($sql, [$personId, $patientId]) ?: null;
    }

    /**
     * Get link by ID
     *
     * @param int $linkId
     * @return array|null
     */
    public function getLink(int $linkId): ?array
    {
        $sql = "SELECT ppl.*, 
                       u.username as linked_by_username,
                       CONCAT(u.fname, ' ', u.lname) as linked_by_name,
                       p.firstname as person_firstname,
                       p.lastname as person_lastname,
                       pd.fname as patient_firstname,
                       pd.lname as patient_lastname,
                       pd.pid as patient_pid
                FROM person_patient_link ppl
                LEFT JOIN users u ON u.id = ppl.linked_by
                LEFT JOIN person p ON p.id = ppl.person_id
                LEFT JOIN patient_data pd ON pd.id = ppl.patient_id
                WHERE ppl.id = ?";
        
        return sqlQuery($sql, [$linkId]) ?: null;
    }

    /**
     * Get patient for a person (if linked)
     *
     * @param int $personId
     * @return array|null
     */
    public function getPatientForPerson(int $personId): ?array
    {
        $sql = "SELECT pd.*, ppl.linked_date, ppl.link_method
                FROM patient_data pd
                JOIN person_patient_link ppl ON ppl.patient_id = pd.id
                WHERE ppl.person_id = ? AND ppl.active = 1
                LIMIT 1";
        
        return sqlQuery($sql, [$personId]) ?: null;
    }

    /**
     * Get person for a patient (if linked)
     *
     * @param int $patientId
     * @return array|null
     */
    public function getPersonForPatient(int $patientId): ?array
    {
        $sql = "SELECT p.*, c.id as contact_id, ppl.linked_date, ppl.link_method
                FROM person p
                JOIN person_patient_link ppl ON ppl.person_id = p.id
                LEFT JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                WHERE ppl.patient_id = ? AND ppl.active = 1
                LIMIT 1";
        
        return sqlQuery($sql, [$patientId]) ?: null;
    }

    /**
     * Check if person exists for patient by matching demographics
     * Used during patient registration to detect potential matches
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $birthDate
     * @return array Array of potential person matches
     */
    public function findPotentialPersonMatches(
        string $firstname,
        string $lastname,
        string $birthDate
    ): array {
        $sql = "SELECT p.*, 
                       c.id as contact_id,
                       COUNT(DISTINCT cr.id) as relationship_count,
                       GROUP_CONCAT(
                           DISTINCT CONCAT(
                               cr.relationship, 
                               ' of ', 
                               pd_related.fname, ' ', pd_related.lname, 
                               ' (', pd_related.pid, ')'
                           ) 
                           SEPARATOR '; '
                       ) as relationships_summary
                FROM person p
                LEFT JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                LEFT JOIN contact_relation cr ON cr.contact_id = c.id AND cr.active = 1
                LEFT JOIN patient_data pd_related ON 
                    cr.related_foreign_table_name = 'patient_data' AND 
                    cr.related_foreign_table_id = pd_related.id
                WHERE p.firstname = ? 
                  AND p.lastname = ? 
                  AND p.birth_date = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM person_patient_link ppl 
                      WHERE ppl.person_id = p.id AND ppl.active = 1
                  )
                GROUP BY p.id
                ORDER BY relationship_count DESC";
        
        $result = sqlStatement($sql, [$firstname, $lastname, $birthDate]);
        
        $matches = [];
        while ($row = sqlFetchArray($result)) {
            $matches[] = $row;
        }
        
        return $matches;
    }

    /**
     * Get all relationships for a patient through their linked person record
     *
     * @param int $patientId
     * @return array
     */
    public function getRelationshipsForPatient(int $patientId): array
    {
        $sql = "SELECT 
                    cr.*,
                    p_related.firstname as related_person_firstname,
                    p_related.lastname as related_person_lastname,
                    p_related.birth_date as related_person_dob,
                    p_related.phone_contact as related_person_phone,
                    pd_related.fname as related_patient_firstname,
                    pd_related.lname as related_patient_lastname,
                    pd_related.pid as related_patient_pid
                FROM person_patient_link ppl
                JOIN person p ON p.id = ppl.person_id
                JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                JOIN contact_relation cr ON cr.contact_id = c.id AND cr.active = 1
                LEFT JOIN contact c_related ON c_related.id = (
                    SELECT c2.id FROM contact c2 
                    WHERE c2.foreign_table_name = cr.related_foreign_table_name 
                      AND c2.foreign_id = cr.related_foreign_table_id
                    LIMIT 1
                )
                LEFT JOIN person p_related ON 
                    cr.related_foreign_table_name = 'person' AND 
                    c_related.foreign_id = p_related.id
                LEFT JOIN patient_data pd_related ON 
                    cr.related_foreign_table_name = 'patient_data' AND 
                    c_related.foreign_id = pd_related.id
                WHERE ppl.patient_id = ? 
                  AND ppl.active = 1
                ORDER BY cr.contact_priority, cr.relationship";
        
        $result = sqlStatement($sql, [$patientId]);
        
        $relationships = [];
        while ($row = sqlFetchArray($result)) {
            $relationships[] = $row;
        }
        
        return $relationships;
    }

    /**
     * Find all unlinked persons who might be patients
     * Useful for migration and cleanup
     *
     * @param int $limit
     * @return array
     */
    public function findUnlinkedPersonsWhoArePatients(int $limit = 100): array
    {
        $sql = "SELECT 
                    p.id as person_id,
                    p.firstname,
                    p.lastname,
                    p.birth_date,
                    pd.id as patient_id,
                    pd.pid,
                    pd.regdate,
                    COUNT(DISTINCT cr.id) as relationship_count
                FROM person p
                JOIN patient_data pd ON 
                    pd.fname = p.firstname AND 
                    pd.lname = p.lastname AND 
                    pd.DOB = p.birth_date
                LEFT JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                LEFT JOIN contact_relation cr ON cr.contact_id = c.id AND cr.active = 1
                WHERE NOT EXISTS (
                    SELECT 1 FROM person_patient_link ppl 
                    WHERE ppl.person_id = p.id AND ppl.patient_id = pd.id AND ppl.active = 1
                )
                GROUP BY p.id, pd.id
                HAVING relationship_count > 0
                ORDER BY relationship_count DESC, pd.regdate DESC
                LIMIT ?";
        
        $result = sqlStatement($sql, [$limit]);
        
        $matches = [];
        while ($row = sqlFetchArray($result)) {
            $matches[] = $row;
        }
        
        return $matches;
    }

    /**
     * Migrate/link existing persons to patients in bulk
     * Returns count of successful links
     *
     * @param int $limit
     * @param int|null $userId
     * @return array ['linked' => int, 'failed' => int, 'errors' => array]
     */
    public function migrateUnlinkedPersons(int $limit = 100, ?int $userId = null): array
    {
        $unlinked = $this->findUnlinkedPersonsWhoArePatients($limit);
        
        $linked = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($unlinked as $match) {
            $result = $this->linkPersonToPatient(
                $match['person_id'],
                $match['patient_id'],
                $userId,
                'migrated',
                "Auto-linked during migration. Has {$match['relationship_count']} relationship(s)."
            );
            
            if ($result->isValid()) {
                $linked++;
            } else {
                $failed++;
                $errors[] = [
                    'person_id' => $match['person_id'],
                    'patient_id' => $match['patient_id'],
                    'error' => implode(', ', $result->getValidationMessages())
                ];
            }
        }
        
        $this->logger->info("Migration completed", [
            'linked' => $linked,
            'failed' => $failed,
            'total_processed' => count($unlinked)
        ]);
        
        return [
            'linked' => $linked,
            'failed' => $failed,
            'errors' => $errors,
            'total_processed' => count($unlinked)
        ];
    }

    /**
     * Get all active links (for admin/reporting)
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllActiveLinks(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    ppl.*,
                    p.firstname as person_firstname,
                    p.lastname as person_lastname,
                    p.birth_date as person_dob,
                    pd.fname as patient_firstname,
                    pd.lname as patient_lastname,
                    pd.pid as patient_pid,
                    u.username as linked_by_username,
                    COUNT(DISTINCT cr.id) as relationship_count
                FROM person_patient_link ppl
                JOIN person p ON p.id = ppl.person_id
                JOIN patient_data pd ON pd.id = ppl.patient_id
                LEFT JOIN users u ON u.id = ppl.linked_by
                LEFT JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
                LEFT JOIN contact_relation cr ON cr.contact_id = c.id AND cr.active = 1
                WHERE ppl.active = 1
                GROUP BY ppl.id
                ORDER BY ppl.linked_date DESC
                LIMIT ? OFFSET ?";
        
        $result = sqlStatement($sql, [$limit, $offset]);
        
        $links = [];
        while ($row = sqlFetchArray($result)) {
            $links[] = $row;
        }
        
        return $links;
    }

    /**
     * Get statistics about links
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = [];
        
        // Total active links
        $stats['total_active_links'] = sqlQuery(
            "SELECT COUNT(*) as cnt FROM person_patient_link WHERE active = 1"
        )['cnt'] ?? 0;
        
        // Total inactive links
        $stats['total_inactive_links'] = sqlQuery(
            "SELECT COUNT(*) as cnt FROM person_patient_link WHERE active = 0"
        )['cnt'] ?? 0;
        
        // Links by method
        $methodCounts = sqlStatement(
            "SELECT link_method, COUNT(*) as cnt 
             FROM person_patient_link 
             WHERE active = 1 
             GROUP BY link_method"
        );
        
        $stats['by_method'] = [];
        while ($row = sqlFetchArray($methodCounts)) {
            $stats['by_method'][$row['link_method']] = $row['cnt'];
        }
        
        // Persons linked to patients
        $stats['persons_linked'] = sqlQuery(
            "SELECT COUNT(DISTINCT person_id) as cnt 
             FROM person_patient_link 
             WHERE active = 1"
        )['cnt'] ?? 0;
        
        // Patients with linked person records
        $stats['patients_linked'] = sqlQuery(
            "SELECT COUNT(DISTINCT patient_id) as cnt 
             FROM person_patient_link 
             WHERE active = 1"
        )['cnt'] ?? 0;
        
        return $stats;
    }
}
