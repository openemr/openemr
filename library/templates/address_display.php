<?php

/**
 * Handles the display of the address list datatype in LBF
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
use OpenEMR\Common\Twig\TwigContainer;


// Initialize services
$contactService = new ContactService();
$contactAddressService = new ContactAddressService();

// Get contact for patient
/**
 * @global string $foreign_table The foreign table name (e.g., 'patient_data')
 * @global int $foreign_id The foreign ID (e.g., patient ID)
 */
$foreign_table ??= '';
$foreign_id ??= 0;
$contact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);
$addresses = [];

if ($contact) {
    // Get all addresses for this contact
    $addressRecords = $contactAddressService->getAddressesForContact($contact->get_id(), true);

    // Transform to display format
    foreach ($addressRecords as $record) {
        $addresses[] = [
            'contact_address_id' => $record['contact_address_id'],
            'use' => $record['use'],
            'type' => $record['type'],
            'line1' => $record['line1'] ?? '',
            'line2' => $record['line2'] ?? '',
            'city' => $record['city'] ?? '',
            'state' => $record['state'] ?? '',
            'zip' => $record['zip'] ?? '',
            'postalcode' => $record['postalcode'] ?? '',
            'country' => $record['country'] ?? '',
            'district' => $record['district'] ?? '',
            'period_start' => $record['period_start'] ?? '',
            'period_end' => $record['period_end'] ?? '',
            'status' => $record['status'] ?? 'A',
            'is_primary' => $record['is_primary'] ?? 'N'
        ];
    }
}

// Get list options for dropdowns
$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");

// Generate unique table ID
$table_id = uniqid("table_text_addresses_");

// Prepare template variables
$templateVars = [
    'foreign_table' => $foreign_table,
    'table_id' => $table_id,
    'addresses' => $addresses,
    'list_address_types' => $list_address_types,
    'list_address_uses' => $list_address_uses,
    'has_addresses' => !empty($addresses)
];

// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/address_display.html.twig', $templateVars);
