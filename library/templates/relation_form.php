<?php

/**
 * Handles the editing, updating, creating, and deleting of the relation list datatype in LBF
 * Updated to use ContactService, ContactRelationService, PersonService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * Portions of this code were developed with assistance from Claude (Anthropic)
 *
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Services\PersonService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();

// Initialize services
$contactService = new ContactService();
$relationService = new ContactRelationService();
$personService = new PersonService();

$relations = [];

try {
	// Get relationships with person details for editing (including inactive)
    $relationRecords = $relationService->getRelationshipsWithPersonDetails($foreign_table_name, $foreign_id, true);

    // Transfer records to an array
    foreach ($relationRecords as $record) {
        $relations[] = [
            'relation_id' => $record['id'],
            'contact_id' => $record['contact_id'],
            'person_id' => $record['foreign_id'],
            'firstname' => $record['firstname'] ?? '',
            'lastname' => $record['lastname'] ?? '',
            'gender' => $record['gender'] ?? '',
            'birth_date' => $record['birth_date'] ?? '',
            'email' => $record['email'] ?? '',
            'relationship' => $record['relationship'] ?? '',
            'role' => $record['role'] ?? '',
            'contact_priority' => $record['contact_priority'] ?? 1,
            'is_primary_contact' => $record['is_primary_contact'] ?? false,
            'is_emergency_contact' => $record['is_emergency_contact'] ?? false,
            'can_make_medical_decisions' => $record['can_make_medical_decisions'] ?? false,
            'can_receive_medical_info' => $record['can_receive_medical_info'] ?? false,
            'active' => $record['active'] ?? true,
            'start_date' => $record['start_date'] ?? '',
            'end_date' => $record['end_date'] ?? '',
            'notes' => $record['notes'] ?? ''
        ];
    }
} catch (\Exception $e) {
    $logger->error("Error loading relations for form", [
        'foreign_table' => $foreign_table_name,
        'foreign_id' => $foreign_id,
        'error' => $e->getMessage()
    ]);
}

// Get list options for dropdowns
$list_relationships = generate_list_map("related_person-relationship");
$list_roles = generate_list_map("related_person-role");

// Generate unique table ID
$table_id = uniqid("table_edit_relations_");
$field_id_esc = $field_id_esc ?? '0';
$name_field_id = "form_" . $field_id_esc;
$smallform = $smallform ?? '';

// Prepare template variables
$widgetConstants = [
    'listWithAddButton' => 26,
    'textDate' => 4,
    'textbox' => 2
];

// Prepare template variables
$templateVars = [
    'table_id' => $table_id,
    'relations' => $relations,
    'list_relationships' => $list_relationships,
    'list_roles' => $list_roles,
    'name_field_id' => $name_field_id,
    'field_id_esc' => $field_id_esc,
    'smallform' => $smallform,
    'widget_constants' => $widgetConstants,
    'edit_options' => $edit_options ?? null,
    'foreign_table_name' => $foreign_table_name,
    'foreign_id' => $foreign_id,
    'webroot' => $GLOBALS['webroot'],
    'srcdir' => $GLOBALS['srcdir'],
    'csrfToken' => CsrfUtils::collectCsrfToken()
];

// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/relation_form.html.twig', $templateVars);
