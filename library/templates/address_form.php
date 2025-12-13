<?php

/**
 * Handles the editing, updating, creating, and deleting of the address list datatype in LBF
 * Updated to use ContactService and ContactAddressService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Common\Twig\TwigContainer;

// Initialize services
$contactService = new ContactService();
$contactAddressService = new ContactAddressService();

// Get or create contact for patient
/**
 * @global string $foreign_table The foreign table name (e.g., 'patient_data')
 * @global int $foreign_id The foreign ID (e.g., patient ID)
 */
$foreign_table ??= '';
$foreign_id ??= 0;
$contact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);
$addresses = [];

if ($contact) {
    // Get all addresses including inactive for editing
    $addressRecords = $contactAddressService->getAddressesForContact($contact->get_id(), true);

    // Transform to edit format
    foreach ($addressRecords as $record) {
        $addresses[] = [
            'contact_address_id' => $record['contact_address_id'],
            'contact_id' => $record['contact_id'],
            'addresses_id' => $record['addresses_id'],
            'use' => $record['use'] ?? 'home',
            'type' => $record['type'] ?? 'both',
            'line1' => $record['line1'] ?? '',
            'line2' => $record['line2'] ?? '',
            'city' => $record['city'] ?? '',
            'state' => $record['state'] ?? '',
            'zip' => $record['zip'] ?? '',
            'postalcode' => $record['postalcode'] ?? '',
            'country' => $record['country'] ?? 'USA',
            'district' => $record['district'] ?? '',
            'period_start' => $record['period_start'] ?? '',
            'period_end' => $record['period_end'] ?? '',
            'status' => $record['status'] ?? 'A',
            'is_primary' => $record['is_primary'] ?? 'N',
            'priority' => $record['priority'] ?? 0,
            'notes' => $record['notes'] ?? ''
        ];
    }
}

// Get list options for dropdowns
$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");
$list_states = generate_list_map("state");
$list_countries = generate_list_map("country");

// Generate unique table ID
$table_id = uniqid("table_edit_addresses_");
$field_id_esc ??= '0';
$name_field_id = "form_" . $field_id_esc;
$smallform ??= '';

// Widget constants
$widgetConstants = [
    'listWithAddButton' => 26,
    'textDate' => 4,
    'textbox' => 2
];

// Prepare template variables
$templateVars = [
    'foreign_table' => $foreign_table,
    'table_id' => $table_id,
    'addresses' => $addresses,
    'list_address_types' => $list_address_types,
    'list_address_uses' => $list_address_uses,
    'list_states' => $list_states,
    'list_countries' => $list_countries,
    'name_field_id' => $name_field_id,
    'field_id_esc' => $field_id_esc,
    'smallform' => $smallform,
    'widget_constants' => $widgetConstants,
    'edit_options' => $edit_options ?? null,
    'contact_id' => $contact ? $contact->get_id() : null,
    'srcdir' => $GLOBALS['srcdir']
];

// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/address_form.html.twig', $templateVars);
