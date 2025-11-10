<?php

/**
 * Handles saving address list data from LBF form submissions
 * Updated to use ContactService and ContactAddressService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\ORDataObject\Contact;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\ORDataObject\Address;

/**
 * Save addresses for a patient from form data
 *
 * @param int $pid Patient ID
 * @param array $addressData Address form data
 * @return array Array of saved ContactAddress records
 */
function saveAddressesForPatient($pid, $addressData)
{
    $logger = new SystemLogger();
    $logger->debug("Saving addresses for patient", [
        'pid' => $pid, 
        'addressData' => $addressData
    ]);

    $contactService = new ContactService();
    $addressService = new ContactAddressService();

    $savedAddresses = [];

    try {
        // Get or create contact for patient
        $contact = $contactService->getOrCreateForEntity('patient_data', $pid);

        if (!$contact) {
            $logger->error("Failed to get or create contact for patient", ['pid' => $pid]);
            return [];
        }

        $count = count($addressData['data_action'] ?? []);
        if ($count <= 0) {
            return [];
        }

        // Process each address
        for ($i = 0; $i < $count; $i++) {
            $action = $addressData['data_action'][$i] ?? '';

            // Skip empty entries
            if (empty($action) || ($action != 'ADD' && empty($addressData['id'][$i]))) {
                continue;
            }

            try {
                switch ($action) {
                    case 'ADD':
                        $result = handleAddAddress($contact, $addressData, $i, $addressService);
                        if ($result) {
                            $savedAddresses[] = $result;
                        }
                        break;

                    case 'UPDATE':
                        $result = handleUpdateAddress($addressData, $i, $addressService);
                        if ($result) {
                            $savedAddresses[] = $result;
                        }
                        break;


                    case 'INACTIVATE':
                        $result = handleInactivateAddress($addressData, $i, $addressService);
                        if ($result) {
                            $savedAddresses[] = $result;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $logger->error("Error processing address", [
                    'action' => $action,
                    'index' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $logger->info("Addresses saved successfully", [
            'pid' => $pid,
            'count' => count($savedAddresses)
        ]);

    } catch (\Exception $e) {
        $logger->error("Error in saveAddressesForPatient", [
            'pid' => $pid,
            'error' => $e->getMessage()
        ]);
    }

    return $savedAddresses;
}

/**
 * Handle adding a new address
 */
function handleAddAddress(Contact $contact, array $addressData, int $index, ContactAddressService $addressService)
{
    $logger = new SystemLogger();

    // Build address data array
    $data = extractAddressDataFromForm($addressData, $index);

    // Validate address data
    $validation = $addressService->validateAddress($data);
    if (!$validation->isValid()) {
        $logger->warning("Address validation failed", [
            'errors' => $validation->getValidationMessages()
        ]);
        return null;
    }

    // Create new address
    $contactAddress = new ContactAddress();
    $contactAddress->setContact($contact);

    // Set address data
    $address = $contactAddress->getAddress();
    populateAddressFromData($address, $data);

    // Set contact address metadata
    populateContactAddressFromData($contactAddress, $data);

    // Save
    if ($contactAddress->persist()) {
        return $contactAddress->toArray();
    }

    return null;
}

/**
 * Handle updating an existing address
 */
function handleUpdateAddress(array $addressData, int $index, ContactAddressService $addressService)
{
    $logger = new SystemLogger();
    $contactAddressId = $addressData['contact_address_id'][$index] ?? null;

    if (!$contactAddressId) {
        $logger->warning("No address ID provided for update", ['index' => $index]);
        return null;
    }

    // Load existing address
    $contactAddress = new ContactAddress($contactAddressId);
    if (empty($contactAddress->get_id())) {
        $logger->warning("Address not found for update", ['id' => $contactAddressId]);
        return null;
    }

    // Extract and validate data
    $data = extractAddressDataFromForm($addressData, $index);
    $validation = $addressService->validateAddress($data);
    if (!$validation->isValid()) {
        $logger->warning("Address validation failed", [
            'errors' => $validation->getValidationMessages()
        ]);
        return null;
    }

    // Update address
    $address = $contactAddress->getAddress();
    populateAddressFromData($address, $data);

    // Update contact address metadata
    populateContactAddressFromData($contactAddress, $data);

    // Save
    if ($contactAddress->persist()) {
        return $contactAddress->toArray();
    }

    return null;
}

/**
 * Handle inactivating an address
 */
function handleInactivateAddress(array $addressData, int $index, ContactAddressService $cotactAddressService)
{
    $logger = new SystemLogger();
    $contactAddressId = $addressData['contact_address_id'][$index] ?? null;

    if (!$contactAddressId) {
        return null;
    }

    $reason = $addressData['inactivated_reason'][$index] ?? 'User deleted';

    if ($cotactAddressService->deactivateAddress($contactAddressId, $reason)) {
        return ['contact_address_id' => $contactAddressId, 'status' => 'INACTIVATED'];
    }

    return null;
}

/**
 * Extract address data from form array at given index
 */
function extractAddressDataFromForm(array $formData, int $index): array
{
    return [
        'line1' => $formData['line_1'][$index] ?? '',
        'line2' => $formData['line_2'][$index] ?? '',
        'city' => $formData['city'][$index] ?? '',
        'state' => $formData['state'][$index] ?? '',
        'postalcode' => $formData['postalcode'][$index] ?? '',
        'country' => $formData['country'][$index] ?? 'USA',
        'district' => $formData['district'][$index] ?? '',
        'use' => $formData['use'][$index] ?? 'home',
        'type' => $formData['type'][$index] ?? 'both',
        'period_start' => $formData['period_start'][$index] ?? '',
        'period_end' => $formData['period_end'][$index] ?? '',
        'priority' => $formData['priority'][$index] ?? 0,
        'notes' => $formData['notes'][$index] ?? '',
        'is_primary' => $formData['is_primary'][$index] ?? 'N'
    ];
}

/**
 * Populate Address object from data array
 */
function populateAddressFromData(Address $address, array $data): void
{
    $address->set_line1($data['line1']);
    $address->set_line2($data['line2']);
    $address->set_city($data['city']);
    $address->set_state($data['state']);
    $address->set_district($data['district']);
    $address->set_country($data['country']);
    $address->set_postalcode($data['postalcode']);
}

/**
 * Populate ContactAddress object from data array
 */
function populateContactAddressFromData(ContactAddress $contactAddress, array $data): void
{
    $logger = new SystemLogger();

    // Set type and use
    $contactAddress->set_type($data['type']);
    $contactAddress->set_use($data['use']);

    // Set dates
    if (!empty($data['period_start'])) {
        $periodStart = \DateTime::createFromFormat('Y-m-d', $data['period_start']);
        if ($periodStart !== false) {
            $contactAddress->set_period_start($periodStart);
        } else {
            $logger->warning("Invalid period_start date format", ['date' => $data['period_start']]);
        }
    }

    if (!empty($data['period_end'])) {
        $periodEnd = \DateTime::createFromFormat('Y-m-d', $data['period_end']);
        if ($periodEnd !== false) {
            $contactAddress->set_period_end($periodEnd);
        } else {
            $logger->warning("Invalid period_end date format", ['date' => $data['period_end']]);
        }
    }

    // Set metadata
    $contactAddress->set_notes($data['notes'] ?? '');
    $contactAddress->set_priority((int)($data['priority'] ?? 0));
    $contactAddress->set_is_primary($data['is_primary'] ?? 'N');
    $contactAddress->set_status('A'); // Active by default
}
