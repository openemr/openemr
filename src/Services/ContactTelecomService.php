<?php

/**
 * Contact Telecom Service
 * Manages telecom records (phone, email, fax, etc.) for contacts
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\Contact;
use OpenEMR\Common\ORDataObject\ContactTelecom;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Validators\ProcessingResult;

class ContactTelecomService extends BaseService
{
    public const TABLE_NAME = 'contact_telecom';

    private $listService;


    // Default constructor
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        $this->listService = new ListService();
    }


    /**
     * Save telecoms for any contact using form data structure
     * Works with data from get_layout_form_value() and any entity type
     *
     * @param int $contactId The contact ID to save telecoms for
     * @param array $telecomData Telecom data from form
     * @return array Array of saved ContactTelecom objects
     */
    public function saveTelecomsForContact(int $contactId, array $telecomData): array
    {
        $this->getLogger()->debug("Saving telecoms for contact", [
            'contact_id' => $contactId,
            'telecom_count' => count($telecomData['data_action'] ?? []),
            'telecomData' => $telecomData
        ]);

        try {
            // Verify contact exists
            $contact = new Contact($contactId);
            if (empty($contact->get_id())) {
                throw new \Exception("Contact ID {$contactId} not found");
            }

            $savedRecords = [];

            if (empty($telecomData)) {
                return $savedRecords;
            }

            $systems = $this->getValidTelecomSystems();
            $uses = $this->getValidTelecomUses();

            // Iterate through array of telecom objects
            foreach ($telecomData as $index => $telecom) {
                $this->getLogger()->debug("Output Telecom: ", [
                        'keys' => array_keys($telecom),
                        'telecom' => $telecom
                    ]);

                $action = $telecom['data_action'] ?? '';
                if (empty($action)) {
                    $this->getLogger()->warning("No data_action found in telecom", [
                        'index' => $index,
                        'keys' => array_keys($telecom)
                    ]);
                    continue;
                }

                $contactTelecomId = $telecom['contact_telecom_id'] ?? null;

                if ($action != 'ADD' && empty($contactTelecomId)) {
                    $this->getLogger()->warning("Skipping non-ADD action without ID", [
                        'action' => $action,
                        'index' => $index
                    ]);
                    continue;
                }

                $this->getLogger()->debug("Processing telecom", [
                    'index' => $index,
                    'action' => $action,
                    'id' => $contactTelecomId,
                    'value' => $telecom['value'] ?? 'N/A'
                ]);

                // Handle INACTIVATE/DELETE
                if ($action == 'INACTIVATE' || $action == 'DELETE') {
                    if (!empty($contactTelecomId)) {
                        $contactTelecom = new ContactTelecom($contactTelecomId);
                        if (!empty($contactTelecom->get_id())) {
                            $contactTelecom->deactivate();
                            if ($contactTelecom->persist()) {
                                $savedRecords[] = $contactTelecom;
                                $this->getLogger()->info("Telecom inactivated", [
                                    'contact_id' => $contactId,
                                    'contact_telecom_id' => $contactTelecom->get_id()
                                ]);
                            }
                        }
                    }
                    continue;
                }

                // Handle ADD and UPDATE
                $contactTelecom = new ContactTelecom($contactTelecomId);

                if ($action == 'UPDATE' && empty($contactTelecom->get_id())) {
                    $this->getLogger()->error("UPDATE action but telecom not found, treating as ADD", [
                        'contact_telecom_id' => $contactTelecomId,
                        'index' => $index
                    ]);
                    $contactTelecom = new ContactTelecom();
                }

                // Set system
                $system = $telecom['system'] ?? 'phone';
                if (isset($systems[$system])) {
                    $contactTelecom->set_system($system);
                } else {
                    $this->getLogger()->error("Telecom system does not exist", ['system' => $system]);
                }

                // Set use
                $use = $telecom['use'] ?? 'home';
                if (isset($uses[$use])) {
                    $contactTelecom->set_use($use);
                } else {
                    $this->getLogger()->error("Telecom use does not exist", ['use' => $use]);
                }

                // Set dates
                $periodStart = DateFormatterUtils::dateStringToDateTime($telecom['period_start'] ?? '');
                if ($periodStart !== false) {
                    $contactTelecom->set_period_start($periodStart);
                }

                $contactTelecom->set_period_end(null);
                if (!empty($telecom['period_end'])) {
                    $date = DateFormatterUtils::dateStringToDateTime($telecom['period_end']);
                    if ($date !== false) {
                        $contactTelecom->set_period_end($date);
                    }
                }

                // Set value and metadata
                $contactTelecom->set_status($telecom['status'] ?? '');
                $contactTelecom->set_value($telecom['value'] ?? '');
                $contactTelecom->set_notes($telecom['notes'] ?? '');
                $contactTelecom->set_rank($telecom['rank'] ?? 1);
                $contactTelecom->set_inactivated_reason($telecom['inactivated_reason'] ?? '');
                $contactTelecom->set_contact_id($contactId);

                // Save the record
                if ($contactTelecom->persist()) {
                    $savedRecords[] = $contactTelecom;
                    $this->getLogger()->info("Telecom saved", [
                        'contact_id' => $contactId,
                        'telecom_id' => $contactTelecom->get_id(),
                        'action' => $action,
                        'is_new' => empty($contactTelecomId)
                    ]);
                }
            }

            $this->getLogger()->info("Telecoms saved for contact", [
                'contact_id' => $contactId,
                'saved_count' => count($savedRecords)
            ]);

            return $savedRecords;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error saving telecoms for contact", [
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }


     /**
     * Get all telecoms for a contact
     *
     * @param int $contactId
     * @param bool $includeInactive
     * @return array
     */
    public function getTelecomsForContact(int $contactId, bool $includeInactive = false): array
    {
        $sql = "SELECT * FROM contact_telecom WHERE contact_id = ?";

        if (!$includeInactive) {
            $sql .= " AND status = 'A'";
        }

        $sql .= " ORDER BY rank ASC, is_primary DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }


    /**
     * Get primary telecom for a contact by system
     *
     * @param int $contactId
     * @param string $system
     * @return array|null
     */
    public function getPrimaryTelecomForContact(int $contactId, string $system = 'phone'): ?array
    {
        $sql = "SELECT * FROM contact_telecom
                WHERE contact_id = ?
                AND system = ?
                AND is_primary = 'Y'
                AND status = 'A'
                LIMIT 1";

        $result = QueryUtils::querySingleRow($sql, [$contactId, $system]);
        return $result ?: null;
    }


    /**
     * Set primary telecom for a contact by system
     *
     * @param int $contactTelecomId
     * @param int $contactId
     * @param string $system
     * @return bool
     */
    public function setPrimaryTelecomForContact(int $contactTelecomId, int $contactId, string $system): bool
    {
        try {
            // Unset all other primary telecoms for this contact and system
            $sql = "UPDATE contact_telecom SET is_primary = 'N'
                    WHERE contact_id = ?
                    AND system = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactId, $system]);

            // Set the specified telecom as primary
            $sql = "UPDATE contact_telecom SET is_primary = 'Y'
                    WHERE id = ?
                    AND contact_id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactTelecomId, $contactId]);

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error setting primary telecom", ['error' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Deactivate a contact telecom
     *
     * @param int $contactTelecomId
     * @param string $reason
     * @return bool
     */
    public function deactivateTelecom(int $contactTelecomId, string $reason = ''): bool
    {
        try {
            $contactTelecom = new ContactTelecom($contactTelecomId);
            if (empty($contactTelecom->get_id())) {
                return false;
            }

            $contactTelecom->deactivate();
            if (!empty($reason)) {
                $contactTelecom->set_inactivated_reason($reason);
            }

            return $contactTelecom->persist();
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deactivating telecom", ['error' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Delete a contact telecom
     *
     * @param int $contactTelecomId
     * @return bool
     */
    public function deleteTelecom(int $contactTelecomId): bool
    {
        try {
            $sql = "DELETE FROM contact_telecom WHERE id = ?";
            QueryUtils::sqlStatementThrowException($sql, [$contactTelecomId]);
            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error deleting telecom", ['error' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Get telecoms by system
     *
     * @param int $contactId
     * @param string $system
     * @param bool $includeInactive
     * @return array
     */
    public function getTelecomsBySystem(int $contactId, string $system, bool $includeInactive = false): array
    {
        $sql = "SELECT * FROM contact_telecom
                WHERE contact_id = ? AND system = ?";

        if (!$includeInactive) {
            $sql .= " AND status = 'A'";
        }

        $sql .= " ORDER BY rank ASC";

        return QueryUtils::fetchRecords($sql, [$contactId, $system]) ?? [];
    }

    /**
     * Get telecoms by use
     *
     * @param int $contactId
     * @param string $use
     * @param bool $includeInactive
     * @return array
     */
    public function getTelecomsByUse(int $contactId, string $use, bool $includeInactive = false): array
    {
        $sql = "SELECT * FROM contact_telecom
                WHERE contact_id = ? AND `use` = ?";

        if (!$includeInactive) {
            $sql .= " AND status = 'A'";
        }

        $sql .= " ORDER BY rank ASC";

        return QueryUtils::fetchRecords($sql, [$contactId, $use]) ?? [];
    }


     /**
     * Validate telecom data
     *
     * @param array $telecomData
     * @return ProcessingResult
     */
    public function validateTelecom(array $telecomData): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $errors = [];

        // Required fields
        if (empty($telecomData['value'])) {
            $errors['value'] = "Telecom value is required";
        }

        if (empty($telecomData['system'])) {
            $errors['system'] = "Telecom system is required";
        }

        // System validation
        if (!empty($telecomData['system'])) {
            $validSystems = $this->getValidTelecomSystems();
            if (!isset($validSystems[$telecomData['system']])) {
                $errors['system'] = "Invalid telecom system";
            }
        }

        // Use validation
        if (!empty($telecomData['use'])) {
            $validUses = $this->getValidTelecomUses();
            if (!isset($validUses[$telecomData['use']])) {
                $errors['use'] = "Invalid telecom use";
            }
        }

        // System-specific validation
        $system = $telecomData['system'] ?? '';
        $value = $telecomData['value'] ?? '';

        switch ($system) {
            case 'phone':
            case 'mobile':
                // Basic phone validation (digits, spaces, dashes, parentheses, plus sign)
                if (!preg_match('/^[\d\s\-\(\)\+\.]+$/', $value)) {
                    $errors['value'] = "Invalid phone number format";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors['value'] = "Invalid email address format";
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors['value'] = "Invalid URL format";
                }
                break;
        }

        if (!empty($errors)) {
            $processingResult->setValidationMessages($errors);
        } else {
            $processingResult->addData($telecomData);
        }

        return $processingResult;
    }


    /**
     * Get valid telecom systems
     *
     * @return array
     */
    public function getValidTelecomSystems(): array
    {
        static $systems = null;

        if ($systems === null) {
            $systemsList = $this->listService->getOptionsByListName('telecom_systems');
            $systems = array_reduce($systemsList, function ($map, $item) {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $systems;
    }


    /**
     * Get valid telecom uses
     *
     * @return array
     */
    public function getValidTelecomUses(): array
    {
        static $uses = null;

        if ($uses === null) {
            $usesList = $this->listService->getOptionsByListName('telecom_uses');
            $uses = array_reduce($usesList, function ($map, $item) {
                $map[$item['option_id']] = $item['title'];
                return $map;
            }, []);
        }

        return $uses;
    }


    /**
     * Copy telecom to another contact
     *
     * @param int $sourceTelecomId
     * @param int $destinationContactId
     * @return ContactTelecom|null
     */
    public function copyTelecom(int $sourceTelecomId, int $destinationContactId): ?ContactTelecom
    {
        try {
            $sourceTelecom = new ContactTelecom($sourceTelecomId);
            if (empty($sourceTelecom->get_id())) {
                return null;
            }

            $destContact = new Contact($destinationContactId);
            if (empty($destContact->get_id())) {
                return null;
            }

            $newTelecom = new ContactTelecom();
            $newTelecom->set_contact_id($destinationContactId);
            $newTelecom->set_system($sourceTelecom->get_system());
            $newTelecom->set_use($sourceTelecom->get_use());

            $newTelecom->set_value($sourceTelecom->get_value());
            $newTelecom->set_rank($sourceTelecom->get_rank());
            $newTelecom->set_status('A');
            $newTelecom->set_is_primary('N');
            $newTelecom->set_notes($sourceTelecom->get_notes());
            $newTelecom->set_period_start(new \DateTime());

            if ($newTelecom->persist()) {
                return $newTelecom;
            }

            return null;
        } catch (\Exception $e) {
            $this->getLogger()->error("Error copying telecom", ['error' => $e->getMessage()]);
            return null;
        }
    }


    /**
     * Get telecom history for a contact
     *
     * @param int $contactId
     * @return array
     */
    public function getTelecomHistory(int $contactId): array
    {
        $sql = "SELECT *
				FROM contact_telecom
				WHERE contact_id = ?
				ORDER BY period_start
				DESC, created_date DESC";

        return QueryUtils::fetchRecords($sql, [$contactId]) ?? [];
    }
}
