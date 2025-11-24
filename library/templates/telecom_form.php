<?php
/**
 * Handles the editing, updating, creating, and deleting of the telecom list datatype in LBF
 * Uses ContactService and ContactTelecomService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactTelecomService;
use OpenEMR\Common\Twig\TwigContainer;

// Initialize services
$contactService = new ContactService();
$telecomService = new ContactTelecomService();

// Get or create contact for entity
/**
 * @global string $foreign_table The foreign table name (e.g., 'patient_data')
 * @global int $foreign_id The foreign ID (e.g., patient ID)
 */
$foreign_table ??= '';
$foreign_id ??= 0;
$contact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);
$telecoms = [];

if ($contact) {
    // Get all telecoms including inactive for editing
    $telecomRecords = $telecomService->getTelecomsForContact($contact->get_id(), true);

    // Transfer records to an array
    foreach ($telecomRecords as $record) {
        $telecoms[] = [
            'id' => $record['id'],
            'contact_id' => $record['contact_id'],
            'system' => $record['system'] ?? 'phone',
            'use' => $record['use'] ?? 'home',
            'value' => $record['value'] ?? '',
            'rank' => $record['rank'] ?? 1,
            'notes' => $record['notes'] ?? '',
            'status' => $record['status'] ?? 'A',
            'is_primary' => $record['is_primary'] ?? 'N',
            'period_start' => $record['period_start'] ?? '',
            'period_end' => $record['period_end'] ?? ''
        ];
    }
}

// Get list options for dropdowns
$list_telecom_systems = generate_list_map("telecom_systems");
$list_telecom_uses = generate_list_map("telecom_uses");

// Generate unique table ID
$table_id = uniqid("table_edit_telecoms_");
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
    'telecoms' => $telecoms,
    'list_telecom_systems' => $list_telecom_systems,
    'list_telecom_uses' => $list_telecom_uses,
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
echo $twig->render('patient/demographics/telecom_form.html.twig', $templateVars);
