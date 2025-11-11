<?php

/**
 * Contact Service - Clean Design with 1:1 Entity Relationship
 * Manages Contact entities with a 1:1 relationship to foreign entities
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\Contact;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Validators\ProcessingResult;

class ContactService extends BaseService
{
    public const TABLE_NAME = 'contact';

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }

    /**
     * Get or create a contact for an entity (ensures 1:1 relationship)
     *
     * @param string $foreignTable The table this contact relates to
     * @param int $foreignId The ID in the foreign table
     */
    public function getOrCreateForEntity(string $foreignTable, int $foreignId): Contact
    {
        // First try to find existing contact
        $sql = "SELECT * FROM contact
                WHERE foreign_table_name = ?
                AND foreign_id = ?
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$foreignTable, $foreignId]);

        if ($result) {
            $contact = new Contact();
            $contact->populate_array($result);
            return $contact;
        }

        // Create new contact if none exists
        $contact = new Contact();
        $contact->setContactRecord($foreignTable, $foreignId);
        $contact->persist();

        $this->getLogger()->debug("Contact created for entity", [
            'id' => $contact->get_id(),
            'foreign_table_name' => $foreignTable,
            'foreign_id' => $foreignId
        ]);

        return $contact;
    }

    /**
     * Get a contact by ID
     */
    public function get(int $contactId): ?Contact
    {
        $contact = new Contact($contactId);

        if (in_array($contact->get_id(), [null, 0], true)) {
            return null;
        }

        return $contact;
    }

    /**
     * Get contact for a specific entity
     */
    public function getForEntity(string $foreignTable, int $foreignId): ?Contact
    {
        $sql = "SELECT * FROM contact
                WHERE foreign_table_name = ?
                AND foreign_id = ?
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$foreignTable, $foreignId]);

        if (!$result) {
            return null;
        }

        $contact = new Contact();
        $contact->populate_array($result);
        return $contact;
    }

    /**
     * Check if an entity has a contact
     */
    public function entityHasContact(string $foreignTable, int $foreignId): bool
    {
        $sql = "SELECT id FROM contact
                WHERE foreign_table_name = ?
                AND foreign_id = ?
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$foreignTable, $foreignId]);

        return !empty($result);
    }

    /**
     * Delete a contact (checks for dependent records first)
     */
    public function delete(int $contactId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Check for dependent records
            $dependents = $this->getDependentRecords($contactId);

            if ($dependents !== []) {
                $processingResult->addProcessingError(
                    "Cannot delete contact with dependent records: " .
                    implode(", ", array_keys($dependents))
                );
                return $processingResult;
            }

            $sql = "DELETE FROM contact WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactId]);

            $this->getLogger()->info("Contact deleted", ['id' => $contactId]);
            $processingResult->addData(['deleted' => true, 'id' => $contactId]);
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting contact", [
                'id' => $contactId,
                'error' => $e->getMessage()
            ]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Delete contact for a specific entity
     */
    public function deleteForEntity(string $foreignTable, int $foreignId): ProcessingResult
    {
        $contact = $this->getForEntity($foreignTable, $foreignId);

        if (!$contact instanceof \OpenEMR\Common\ORDataObject\Contact) {
            $processingResult = new ProcessingResult();
            $processingResult->addProcessingError("No contact found for entity");
            return $processingResult;
        }

        return $this->delete($contact->get_id());
    }

    /**
     * Get dependent records for a contact
     *
     * @return array Array of table names with counts
     */
    public function getDependentRecords(int $contactId): array
    {
        $dependents = [];

        // Check contact_address table
        $sql = "SELECT COUNT(*) as count FROM contact_address WHERE contact_id = ?";
        $result = QueryUtils::querySingleRow($sql, [$contactId]);
        if ($result['count'] > 0) {
            $dependents['contact_address'] = $result['count'];
        }

        // Check relationship table
        $sql = "SELECT COUNT(*) as count FROM relationship WHERE contact_id = ?";
        $result = QueryUtils::querySingleRow($sql, [$contactId]);
        if ($result['count'] > 0) {
            $dependents['relationship'] = $result['count'];
        }

        // Future: Add checks for contact_telephone, contact_email, etc.

        return $dependents;
    }

    /**
     * Validate that a contact exists and belongs to a specific entity
     */
    public function validateOwnership(int $contactId, string $foreignTable, int $foreignId): bool
    {
        $sql = "SELECT id FROM contact
                WHERE id = ?
                AND foreign_table_name = ?
                AND foreign_id = ?";

        $result = QueryUtils::querySingleRow($sql, [$contactId, $foreignTable, $foreignId]);

        return !empty($result);
    }

    /**
     * Transfer a contact from one entity to another
     */
    public function transferContact(int $contactId, string $newForeignTable, int $newForeignId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            // Check if destination already has a contact
            if ($this->entityHasContact($newForeignTable, $newForeignId)) {
                $processingResult->addProcessingError(
                    "Destination entity already has a contact. Delete it first or merge the contacts."
                );
                return $processingResult;
            }

            // Get the contact
            $contact = $this->get($contactId);
            if (!$contact instanceof \OpenEMR\Common\ORDataObject\Contact) {
                $processingResult->addProcessingError("Contact not found");
                return $processingResult;
            }

            // Update the contact
            $contact->setContactRecord($newForeignTable, $newForeignId);
            $contact->persist();

            $this->getLogger()->info("Contact transferred", [
                'contact_id' => $contactId,
                'new_table' => $newForeignTable,
                'new_id' => $newForeignId
            ]);

            $processingResult->addData($contact->toArray());
        } catch (\Exception $e) {
            $this->getLogger()->error("Error transferring contact", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Merge two contacts (useful when consolidating duplicate entities)
     *
     * @param int $sourceContactId Contact to merge from
     * @param int $targetContactId Contact to merge into
     */
    public function mergeContacts(int $sourceContactId, int $targetContactId): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            if ($sourceContactId === $targetContactId) {
                $processingResult->addProcessingError("Cannot merge a contact with itself");
                return $processingResult;
            }

            // Update all dependent records to point to target contact
            $tables = [
                'contact_address',
                'relationship',
                // Add future tables here: contact_telephone, contact_email, etc.
            ];

            foreach ($tables as $table) {
                $sql = "UPDATE $table SET contact_id = ? WHERE contact_id = ?";
                QueryUtils::sqlStatementThrowException($sql, [$targetContactId, $sourceContactId]);
            }

            // Delete the source contact
            $sql = "DELETE FROM contact WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$sourceContactId]);

            $this->getLogger()->info("Contacts merged", [
                'source_id' => $sourceContactId,
                'target_id' => $targetContactId
            ]);

            $processingResult->addData([
                'merged' => true,
                'source_id' => $sourceContactId,
                'target_id' => $targetContactId
            ]);
        } catch (\Exception $e) {
            $this->getLogger()->error("Error merging contacts", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }

    /**
     * Find contacts by foreign table type
     */
    public function findByForeignTable(string $foreignTable, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM contact_address WHERE contact_id = c.id) as address_count,
                (SELECT COUNT(*) FROM relationship WHERE contact_id = c.id) as relationship_count
                FROM contact c
                WHERE c.foreign_table_name = ?
                ORDER BY c.id ASC
                LIMIT ? OFFSET ?";

        return QueryUtils::fetchRecords($sql, [$foreignTable, $limit, $offset]) ?? [];
    }

    /**
     * Get statistics about contacts
     */
    public function getStatistics(): array
    {
        $stats = [];

        // Total contacts
        $sql = "SELECT COUNT(*) as total FROM contact";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['total_contacts'] = (int)$result['total'];

        // Contacts by foreign table
        $sql = "SELECT foreign_table_name, COUNT(*) as count
                FROM contact
                GROUP BY foreign_table_name";
        $results = QueryUtils::fetchRecords($sql, []) ?? [];

        $stats['by_table'] = [];
        foreach ($results as $row) {
            $stats['by_table'][$row['foreign_table_name']] = (int)$row['count'];
        }

        // Contacts with addresses
        $sql = "SELECT COUNT(DISTINCT c.id) as count
                FROM contact c
                JOIN contact_address ca ON ca.contact_id = c.id
                WHERE ca.status = 'A'";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['with_active_addresses'] = (int)$result['count'];

        // Contacts in relationships
        $sql = "SELECT COUNT(DISTINCT contact_id) as count
                FROM relationship
                WHERE active = 1";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['in_active_relationships'] = (int)$result['count'];

        // Orphaned contacts (no addresses, no relationships)
        $sql = "SELECT COUNT(*) as count FROM contact c
                WHERE NOT EXISTS (SELECT 1 FROM contact_address WHERE contact_id = c.id)
                AND NOT EXISTS (SELECT 1 FROM relationship WHERE contact_id = c.id)";
        $result = QueryUtils::querySingleRow($sql, []);
        $stats['orphaned_contacts'] = (int)$result['count'];

        return $stats;
    }

    /**
     * Clean up orphaned contacts (contacts with no dependent records)
     *
     * @param bool $dryRun If true, only returns what would be deleted
     */
    public function cleanupOrphaned(bool $dryRun = true): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        try {
            $sql = "SELECT c.* FROM contact c
                    WHERE NOT EXISTS (SELECT 1 FROM contact_address WHERE contact_id = c.id)
                    AND NOT EXISTS (SELECT 1 FROM relationship WHERE contact_id = c.id)";

            $orphaned = QueryUtils::fetchRecords($sql, []) ?? [];

            if ($dryRun) {
                $processingResult->addData([
                    'dry_run' => true,
                    'orphaned_count' => count($orphaned),
                    'orphaned_contacts' => $orphaned
                ]);
            } else {
                $deletedCount = 0;
                foreach ($orphaned as $contact) {
                    $sql = "DELETE FROM contact WHERE id = ?";
                    QueryUtils::sqlStatementThrowException($sql, [$contact['id']]);
                    $deletedCount++;
                }

                $this->getLogger()->info("Orphaned contacts cleaned up", ['count' => $deletedCount]);

                $processingResult->addData([
                    'dry_run' => false,
                    'deleted_count' => $deletedCount
                ]);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Error cleaning up orphaned contacts", ['error' => $e->getMessage()]);
            $processingResult->addProcessingError($e->getMessage());
        }

        return $processingResult;
    }
}
