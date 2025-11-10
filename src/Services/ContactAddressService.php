<?php

/**
 * Contact Address Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\ORDataObject\Contact;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Validators\ProcessingResult;

class ContactAddressService extends BaseService
{
    public const TABLE_NAME = 'contact_address';

    /**
     * Save addresses for any contact using form data structure
     * Works with data from get_layout_form_value() and any entity type
     *
     * @param int $contactId The contact ID to save addresses for
     * @param array $addressData Address data from form (with data_action, line_1, city, etc as parallel arrays)
     * @return array Array of saved ContactAddress objects
     */
    public function saveAddressesForContact(int $contactId, array $addressData): array
    {
        $this->getLogger()->debug("Saving addresses for contact", [
            'contact_id' => $contactId,
            'address_count' => count($addressData['data_action'] ?? []),
            'address_data' => $addressData
        ]);

        try {
            // Verify contact exists
            $contact = new Contact($contactId);
            if (in_array($contact->get_id(), [null, 0], true)) {
                throw new \Exception("Contact ID {$contactId} not found");
            }

            $savedRecords = [];

            if ($addressData === []) {
                return $savedRecords;
            }

            $types = $this->getValidAddressTypes();
            $uses = $this->getValidAddressUses();




            foreach ($addressData as $index => $address) {
                $action = $address['data_action'] ?? '';

                // Skip empty actions
                if (empty($action)) {
                    continue;
                }

                $contactAddressId = $address['contact_address_id'] ?? null;

                // Skip if no ID and not ADD
                if ($action != 'ADD' && empty($contactAddressId)) {
                    $this->getLogger()->warning("Skipping non-ADD action without ID", [
                        'action' => $action,
                        'index' => $index
                    ]);
                    continue;
                }

                // Log what we're processing
                $this->getLogger()->debug("Processing address", [
                    'index' => $index,
                    'action' => $action,
                    'id' => $contactAddressId,
                    'line1' => $address['line_1'] ?? 'N/A'
                ]);

                // Handle INACTIVATE/DELETE
                if ($action == 'INACTIVATE' || $action == 'DELETE') {
                    if (!empty($contactAddressId)) {
                        $contactAddress = new ContactAddress($contactAddressId);
                        if (!empty($contactAddress->get_id())) {
                            $contactAddress->deactivate();
                            if ($contactAddress->persist()) {
                                $savedRecords[] = $contactAddress;
                                $this->getLogger()->info("Address inactivated", [
                                    'contact_id' => $contactId,
                                    'contact_address_id' => $contactAddress->get_id()
                                ]);
                            } else {
                                $this->getLogger()->error("Failed to persist inactivated address", [
                                    'contact_address_id' => $contactAddressId
                                ]);
                            }
                        } else {
                            $this->getLogger()->error("Address not found for inactivation", [
                                'contact_address_id' => $contactAddressId
                            ]);
                        }
                    }
                    continue; // Skip the rest for INACTIVATE/DELETE
                }

                // Handle ADD and UPDATE
                // For UPDATE, load existing; for ADD, create new
                $contactAddress = new ContactAddress($contactAddressId);

                // Verify for UPDATE that we loaded an existing record
                if ($action == 'UPDATE' && empty($contactAddress->get_id())) {
                    $this->getLogger()->error("UPDATE action but address not found, treating as ADD", [
                        'contac_address_id' => $contactAddressId,
                        'index' => $index
                    ]);
                    // Create a new one instead
                    $contactAddress = new ContactAddress();
                }

                // Set type
                $type = $address['type'] ?? 'both';
                if (isset($types[$type])) {
                    $contactAddress->set_type($type);
                } else {
                    $this->getLogger()->error("Address type does not exist", ['type' => $type]);
                }

                // Set use
                $use = $address['use'] ?? 'home';
                if (isset($uses[$use])) {
                    $contactAddress->set_use($use);
                } else {
                    $this->getLogger()->error("Address use does not exist", ['use' => $use]);
                }

                // Set dates
                $periodStart = DateFormatterUtils::dateStringToDateTime($address['period_start'] ?? '');
                if ($periodStart !== false) {
                    $contactAddress->set_period_start($periodStart);
                } else {
                    $this->getLogger()->warning("Invalid period_start date format", [
                        'period_start' => $address['period_start'] ?? ''
                    ]);
                }

                $contactAddress->set_period_end(null);
                if (!empty($address['period_end'])) {
                    $date = DateFormatterUtils::dateStringToDateTime($address['period_end']);
                    if ($date !== false) {
                        $contactAddress->set_period_end($date);
                    } else {
                        $this->getLogger()->warning("Invalid period_end date format", [
                            'period_end' => $address['period_end']
                        ]);
                    }
                }

                // Set additional fields
                $contactAddress->set_notes($address['notes'] ?? '');
                $contactAddress->set_priority($address['priority'] ?? 0);
                $contactAddress->set_inactivated_reason($address['inactivated_reason'] ?? '');

                // Set address fields - FIXED: Handle both postalcode and zip
                $addr = $contactAddress->getAddress();
                $addr->set_line1($address['line_1'] ?? '');
                $addr->set_line2($address['line_2'] ?? '');
                $addr->set_city($address['city'] ?? '');
                $addr->set_district($address['district'] ?? '');
                $addr->set_state($address['state'] ?? '');
                $addr->set_country($address['country'] ?? '');

                // Handle postal code - check both field names
                $postalcode = $address['postalcode'] ?? $address['postal_code'] ?? $address['zip'] ?? '';
                if (!empty($postalcode)) {
                    $addr->set_postalcode($postalcode);
                }

                $addr->set_foreign_id(null);

                // Set the contact (use existing contact)
                $contactAddress->set_contact_id($contactId);

                // Save the record
                if ($contactAddress->persist()) {
                    $savedRecords[] = $contactAddress;
                    $this->getLogger()->info("Address saved", [
                        'contact_id' => $contactId,
                        'address_id' => $contactAddress->get_id(),
                        'action' => $action,
                        'is_new' => empty($contactAddressId)
                    ]);
                } else {
                    $this->getLogger()->error("Failed to persist address", [
                        'contact_id' => $contactId,
                        'index' => $index,
                        'action' => $action
                    ]);
                }
            }

            $this->getLogger()->info("Addresses saved for contact", [
                'contact_id' => $contactId,
                'saved_count' => count($savedRecords)
            ]);

            return $savedRecords;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error saving addresses for contact", [
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get addresses for a contact
     *
     * @param int $contactId The contact ID
     * @param bool $includeInactive Include inactive addresses
     * @return array Array of address records with merged contact_address and address data
     */
    public function getAddressesForContactAsMergedArray(int $contactId, bool $includeInactive = false): array
    {
        if ($contactId === 0) {
            return [];
        }

        $addresses = $this->getAddressesForContact($contactId, $includeInactive);

        return $addresses;
    }

    private readonly \OpenEMR\Services\ListService $listService;

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        $this->listService = new ListService();
    }

    /**
     * Save a single contact address
     */
    public function saveContactAddress(ContactAddress $contactAddress): bool
    {
        try {
            return $contactAddress->persist();
        } catch (\Exception $e) {
            $this->getLogger()->error("Error saving contact address", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create or update contact address from array data
     */
    public function createContactAddressFromArray(array $data, Contact $contact): ContactAddress
    {
        return $this->buildContactAddress($data, $contact);
    }

    /**
     * Build a contact address from array data
     */
    private function buildContactAddress(array $data, Contact $contact): ContactAddress
    {
        $contactAddress = new ContactAddress($data['id'] ?? null);

        // Validate and set type
        $types = $this->getValidAddressTypes();
        $type = $data['type'] ?? ContactAddress::DEFAULT_TYPE;
        if (isset($types[$type])) {
            $contactAddress->set_type($type);
        } else {
            $this->getLogger()->warning("Invalid address type, using default", ['type' => $type]);
            $contactAddress->set_type(ContactAddress::DEFAULT_TYPE);
        }

        // Validate and set use
        $uses = $this->getValidAddressUses();
        $use = $data['use'] ?? ContactAddress::DEFAULT_USE;
        if (isset($uses[$use])) {
            $contactAddress->set_use($use);
        } else {
            $this->getLogger()->warning("Invalid address use, using default", ['use' => $use]);
            $contactAddress->set_use(ContactAddress::DEFAULT_USE);
        }

        // Set dates
        $this->setDatesOnContactAddress($contactAddress, $data);

        // Set other fields
        $contactAddress->set_notes($data['notes'] ?? '');
        $contactAddress->set_priority($data['priority'] ?? 0);
        $contactAddress->set_inactivated_reason($data['inactivated_reason'] ?? '');
        $contactAddress->set_is_primary($data['is_primary'] ?? 'N');
        $contactAddress->set_status($data['status'] ?? 'A');

        // Set address data
        $this->setAddressData($contactAddress->getAddress(), $data);

        // Set contact
        $contactAddress->setContact($contact);

        return $contactAddress;
    }

    /**
     * Set address data on Address object
     */
    private function setAddressData(Address $address, array $data): void
    {
        $address->set_line1($data['line1'] ?? $data['line_1'] ?? '');
        $address->set_line2($data['line2'] ?? $data['line_2'] ?? '');
        $address->set_city($data['city'] ?? '');
        $address->set_district($data['district'] ?? '');
        $address->set_state($data['state'] ?? '');
        $address->set_country($data['country'] ?? '');
        $address->set_postalcode($data['postalcode'] ?? $data['postal_code'] ?? $data['zip'] ?? '');
        $address->set_plus_four($data['plus_four'] ?? '');
    }

    /**
     * Set dates on ContactAddress
     */
    private function setDatesOnContactAddress(ContactAddress $contactAddress, array $data): void
    {
        // Period start
        if (!empty($data['period_start'])) {
            $periodStart = DateFormatterUtils::dateStringToDateTime($data['period_start']);
            if ($periodStart !== false) {
                $contactAddress->set_period_start($periodStart);
            } else {
                $this->getLogger()->warning("Invalid period_start date format", ['period_start' => $data['period_start']]);
            }
        }

        // Period end
        if (!empty($data['period_end'])) {
            $periodEnd = DateFormatterUtils::dateStringToDateTime($data['period_end']);
            if ($periodEnd !== false) {
                $contactAddress->set_period_end($periodEnd);
            } else {
                $this->getLogger()->warning("Invalid period_end date format", ['period_end' => $data['period_end']]);
            }
        }
    }

    /**
     * Get all addresses for a contact
     */
    public function getAddressesForContact(int $contactId, bool $includeInactive = false): array
    {
        $sql = "SELECT
                    ca.id AS contact_address_id,
                    ca.*,
                    a.id AS addresses_id,
                    a.*
                FROM contact_address ca
                JOIN addresses a ON ca.address_id = a.id
                WHERE ca.contact_id = ?";

        if (!$includeInactive) {
            $sql .= " AND ca.status = 'A'";
        }

        $sql .= " ORDER BY ca.priority ASC, ca.is_primary DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }

    /**
     * Get primary address for a contact
     */
    public function getPrimaryAddressForContact(int $contactId): ?array
    {
        $sql = "SELECT ca.*, a.* FROM contact_address ca
                JOIN addresses a ON ca.address_id = a.id
                WHERE ca.contact_id = ?
                AND ca.is_primary = 'Y'
                AND ca.status = 'A'
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$contactId]);

        return $result ?: null;
    }

    /**
     * Set primary address for a contact
     */
    public function setPrimaryAddressForContact(int $contactAddressId, int $contactId): bool
    {
        try {
            // Unset all other primary addresses for this contact
            $sql = "UPDATE contact_address SET is_primary = 'N' WHERE contact_id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactId]);

            // Set the specified address as primary
            $sql = "UPDATE contact_address SET is_primary = 'Y' WHERE id = ? AND contact_id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactAddressId, $contactId]);

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error setting primary address", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Deactivate a contact address
     *
     * @param string $reason Optional reason for deactivation
     */
    public function deactivateAddress(int $contactAddressId, string $reason = ''): bool
    {
        try {
            $contactAddress = new ContactAddress($contactAddressId);
            if (empty($contactAddress->get_id())) {
                return false;
            }

            $contactAddress->deactivate();
            if ($reason !== '' && $reason !== '0') {
                $contactAddress->set_inactivated_reason($reason);
            }

            return $contactAddress->persist();
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deactivating address", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete a contact address with orphan cleanup
     */
    public function deleteContactAddress(int $contactAddressId): bool
    {
        try {
            $contactAddress = new ContactAddress($contactAddressId);
            if (empty($contactAddress->get_id())) {
                return false;
            }

            $addressId = $contactAddress->get_address_id();
            $contactId = $contactAddress->get_contact_id();

            // Delete the contact_address record
            $sql = "DELETE FROM contact_address WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactAddressId]);

            // Clean up orphaned address
            $this->cleanupOrphanedAddress($addressId);

            // Clean up orphaned contact
            $this->cleanupOrphanedContact($contactId);

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting contact address", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clean up orphaned address record
     */
    private function cleanupOrphanedAddress(int $addressId): void
    {
        $sql = "SELECT COUNT(*) as count FROM contact_address WHERE address_id = ?";
        $result = QueryUtils::querySingleRow($sql, [$addressId]);

        if ($result['count'] == 0) {
            $sql = "DELETE FROM addresses WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$addressId]);
        }
    }

    /**
     * Clean up orphaned contact record
     */
    private function cleanupOrphanedContact(int $contactId): void
    {
        // Check if contact has any other addresses
        $sql = "SELECT COUNT(*) as count FROM contact_address WHERE contact_id = ?";
        $result = QueryUtils::querySingleRow($sql, [$contactId]);

        if ($result['count'] == 0) {
            // Check if contact is used in relationships
            $sql = "SELECT COUNT(*) as count FROM relationship WHERE contact_id = ?";
            $result = QueryUtils::querySingleRow($sql, [$contactId]);

            if ($result['count'] == 0) {
                // Delete the orphaned contact
                $sql = "DELETE FROM contact WHERE id = ?";
                QueryUtils::sqlStatementThrowException($sql, [$contactId]);
            }
        }
    }

    /**
     * Get addresses by type
     */
    public function getAddressesByType(int $contactId, string $type, bool $includeInactive = false): array
    {
        $sql = "SELECT ca.*, a.* FROM contact_address ca
                JOIN addresses a ON ca.address_id = a.id
                WHERE ca.contact_id = ? AND ca.type = ?";

        if (!$includeInactive) {
            $sql .= " AND ca.status = 'A'";
        }

        $sql .= " ORDER BY ca.priority ASC";

        return QueryUtils::fetchRecords($sql, [$contactId, $type]) ?? [];
    }

    /**
     * Get addresses by use
     */
    public function getAddressesByUse(int $contactId, string $use, bool $includeInactive = false): array
    {
        $sql = "SELECT ca.*, a.* FROM contact_address ca
                JOIN addresses a ON ca.address_id = a.id
                WHERE ca.contact_id = ? AND ca.use = ?";

        if (!$includeInactive) {
            $sql .= " AND ca.status = 'A'";
        }

        $sql .= " ORDER BY ca.priority ASC";

        return QueryUtils::fetchRecords($sql, [$contactId, $use]) ?? [];
    }

    /**
     * Validate address data
     */
    public function validateAddress(array $addressData): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $errors = [];

        // Required fields validation
        if (empty($addressData['line1']) && empty($addressData['line_1'])) {
            $errors['line1'] = "Address line 1 is required";
        }

        if (empty($addressData['city'])) {
            $errors['city'] = "City is required";
        }

        if (empty($addressData['state'])) {
            $errors['state'] = "State is required";
        }

        $postalCode = $addressData['postalcode'] ?? $addressData['postal_code'] ?? $addressData['zip'] ?? '';
        if (empty($postalCode)) {
            $errors['postalcode'] = "Postal code is required";
        }

        // Country-specific validation
        $country = $addressData['country'] ?? 'US';

        // US ZIP code validation
        if ($country == 'US' && !empty($postalCode) && !preg_match('/^\d{5}(-\d{4})?$/', (string) $postalCode)) {
            $errors['postalcode'] = "Invalid US postal code format (must be XXXXX or XXXXX-XXXX)";
        }

        // Canadian postal code validation
        if ($country == 'CA' && !empty($postalCode) && !preg_match('/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i', (string) $postalCode)) {
            $errors['postalcode'] = "Invalid Canadian postal code format";
        }

        // Type validation
        if (!empty($addressData['type'])) {
            $validTypes = $this->getValidAddressTypes();
            if (!isset($validTypes[$addressData['type']])) {
                $errors['type'] = "Invalid address type";
            }
        }

        // Use validation
        if (!empty($addressData['use'])) {
            $validUses = $this->getValidAddressUses();
            if (!isset($validUses[$addressData['use']])) {
                $errors['use'] = "Invalid address use";
            }
        }

        if ($errors !== []) {
            $processingResult->setValidationMessages($errors);
        } else {
            $processingResult->addData($addressData);
        }

        return $processingResult;
    }

    /**
     * Get valid address types
     */
    public function getValidAddressTypes(): array
    {
        static $types = null;

        if ($types === null) {
            $typesList = $this->listService->getOptionsByListName('address-types');
            $types = array_reduce($typesList, function (array $map, array $item): array {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $types;
    }

    /**
     * Get valid address uses
     */
    public function getValidAddressUses(): array
    {
        static $uses = null;

        if ($uses === null) {
            $usesList = $this->listService->getOptionsByListName('address-uses');
            $uses = array_reduce($usesList, function (array $map, array $item): array {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $uses;
    }

    /**
     * Copy address to another contact
     */
    public function copyAddress(int $sourceContactAddressId, int $destinationContactId): ?ContactAddress
    {
        try {
            $sourceAddress = new ContactAddress($sourceContactAddressId);
            if (empty($sourceAddress->get_id())) {
                return null;
            }

            $destContact = new Contact($destinationContactId);
            if (in_array($destContact->get_id(), [null, 0], true)) {
                return null;
            }

            $newContactAddress = new ContactAddress();
            $newContactAddress->set_contact_id($destinationContactId);
            $newContactAddress->set_address_id($sourceAddress->get_address_id());
            $newContactAddress->set_type($sourceAddress->get_type());
            $newContactAddress->set_use($sourceAddress->get_use());
            $newContactAddress->set_priority($sourceAddress->get_priority());
            $newContactAddress->set_status('A'); // Set as active
            $newContactAddress->set_is_primary('N'); // Not primary by default
            $newContactAddress->set_notes($sourceAddress->get_notes());
            $newContactAddress->set_period_start(new \DateTime());

            if ($newContactAddress->persist()) {
                return $newContactAddress;
            }

            return null;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error copying address", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get address history for a contact
     */
    public function getAddressHistory(int $contactId): array
    {
        $sql = "SELECT ca.*, a.*,
                ca.created_date, ca.period_start, ca.period_end, ca.status,
                ca.inactivated_reason
                FROM contact_address ca
                JOIN addresses a ON ca.address_id = a.id
                WHERE ca.contact_id = ?
                ORDER BY ca.period_start DESC, ca.created_date DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }
}
