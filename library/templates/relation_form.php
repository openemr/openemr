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
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Services\ContactTelecomService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();

// Initialize services
$contactService = new ContactService();
$relationService = new ContactRelationService();
$personService = new PersonService();
$addressService = new ContactAddressService();
$telecomService = new ContactTelecomService();

$relatedPersons = [];
$ownerContactId = null;
$targetContactId = null;

try {
    // Get the contact for this entity
    /**
     * @global string $foreign_table The foreign table name (e.g., 'patient_data')
     * @global int $foreign_id The foreign ID (e.g., patient ID)
     */
    $foreign_table ??= '';
    $foreign_id ??= 0;
    $ownerContact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);
    $ownerContactId = $ownerContact->get_id();

    if ($ownerContact && $ownerContactId) {
        // Get relationships with details (including inactive for editing)
        $relatedEntityRecords = $relationService->getRelationshipsWithDetails($ownerContactId, true);

        // Filter to only person targets
        $relatedPersonRecords = array_filter($relatedEntityRecords, static fn($rel): bool => isset($rel['target_table']) && $rel['target_table'] === 'person');

        // Transfer records to an array
        foreach ($relatedPersonRecords as $record) {

            // Get target person's contact record
            $targetId = $record['person_id'];
            $targetContact = $contactService->getOrCreateForEntity('person', $targetId);
            $targetContactId = $targetContact->get_id();
            if (empty($targetContactId)) {
                $logger->errorLogCaller("No contact found for related person", [
                    'person_id' => $targetId,
                    'owner_contact_relation_id' => $record['owner_contact_relation_id']
                ]);
                continue;
            }

            $relatedPerson = [
                'owner_contact_relation_id' => $record['owner_contact_relation_id'],
                'owner_contact_id' => $record['owner_contact_id'],
                'target_table' =>  'person',
                'target_id' => $targetId,
                'target_contact_id' => $targetContactId,
                'first_name' => $record['first_name'] ?? '',
                'middle_name' => $record['middle_name'] ?? '',
                'last_name' => $record['last_name'] ?? '',
                'gender' => $record['gender'] ?? '',
                'birth_date' => $record['birth_date'] ?? '',
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
                'notes' => $record['notes'] ?? '',
                'addresses' => [],
                'telecoms' => []
            ];

            if ($record['person_id']) {
                // Get addresses for this person's contact
                $addressRecords = $addressService->getAddressesForContact($targetContactId, true);
                foreach ($addressRecords as $addr) {
                    $relatedPerson['addresses'][] = [
                        'contact_id' => $targetContactId,
                        'contact_address_id' => $addr['contact_address_id'] ?? $addr['id'],
                        'addresses_id' => $addr['addresses_id'],
                        'use' => $addr['use'] ?? 'home',
                        'type' => $addr['type'] ?? 'postal',
                        'line1' => $addr['line1'] ?? '',
                        'line2' => $addr['line2'] ?? '',
                        'city' => $addr['city'] ?? '',
                        'state' => $addr['state'] ?? '',
                        'zip' => $addr['zip'] ?? '',
                        'country' => $addr['country'] ?? '',
                        'district' => $addr['district'] ?? '',
                        'status' => $addr['status'] ?? 'A',
                        'is_primary' => $addr['is_primary'] ?? 'N'
                    ];
                }

                // Get telecoms for this person's contact
                $telecomRecords = $telecomService->getTelecomsForContact($targetContactId, true);
                foreach ($telecomRecords as $telecom) {
                    $relatedPerson['telecoms'][] = [
                        'id' => $telecom['contact_telecom_id'] ?? $telecom['id'],
                        'contact_id' => $targetContactId,
                        'system' => $telecom['system'] ?? 'phone',
                        'use' => $telecom['use'] ?? 'home',
                        'value' => $telecom['value'] ?? '',
                        'rank' => $telecom['rank'] ?? 1,
                        'status' => $telecom['status'] ?? 'A',
                        'is_primary' => $telecom['is_primary'] ?? 'N',
                        'notes' => $telecom['notes'] ?? ''
                    ];
                }
            }

            $relatedPersons[] = $relatedPerson;
        }
    }
} catch (\Exception $e) {
    $logger->error("Error loading relations for form", [
        'foreign_table' => $foreign_table,
        'foreign_id' => $foreign_id,
        'error' => $e->getMessage()
    ]);
}

// Get list options for dropdowns
$list_relationships = generate_list_map("related_person_relationship");
$list_roles = generate_list_map("related_person_role");
$list_telecom_systems = generate_list_map("telecom_systems");
$list_telecom_uses = generate_list_map("telecom_uses");
$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");
$list_states = generate_list_map("state");
$list_countries = generate_list_map("country");


// Generate unique table ID
$table_id = uniqid("table_edit_relations_");
$field_id_esc ??= '0';
$name_field_id = "form_" . $field_id_esc;
$smallform ??= false;

// Prepare template variables
$widgetConstants = [
    'listWithAddButton' => 26,
    'textDate' => 4,
    'textbox' => 2
];

// Prepare template variables
$templateVars = [
    'table_id' => $table_id,
    'relatedPersons' => $relatedPersons,
    'list_relationships' => $list_relationships,
    'list_roles' => $list_roles,
    'list_telecom_systems' => $list_telecom_systems,
    'list_telecom_uses' => $list_telecom_uses,
    'list_address_types' => $list_address_types,
    'list_address_uses' => $list_address_uses,
    'list_states' => $list_states,
    'list_countries' => $list_countries,
    'name_field_id' => $name_field_id,
    'field_id_esc' => $field_id_esc,
    'smallform' => $smallform,
    'widget_constants' => $widgetConstants,
    'edit_options' => $edit_options ?? null,
    'owner_table' => $foreign_table,
    'owner_id' => $foreign_id,
    'owner_contact_id' => $ownerContactId,
    'target_contact_id' => $targetContactId,
    'webroot' => $GLOBALS['webroot'],
    'srcdir' => $GLOBALS['srcdir'],
    'csrfToken' => CsrfUtils::collectCsrfToken()
];

$logger->debug("Sending to TWIG", [
                    'relatedPersons' => $relatedPersons,
                    'name_field_id' => $name_field_id,
                    'field_id_esc' => $field_id_esc,
                    'smallform' => $smallform,
                    'widget_constants' => $widgetConstants,
                    'edit_options' => $edit_options ?? null,
                    'owner_table' => $foreign_table,
                    'owner_id' => $foreign_id,
                    'owner_contact_id' => $ownerContactId,
                    'csrfToken' => CsrfUtils::collectCsrfToken()
                ]);

// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/relation_form.html.twig', $templateVars);
