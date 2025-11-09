<?php
/**
 * Handles the display of the relation list datatype in LBF
 * Updated to use ContactService, ContactRelationService, PersonService
 * Includes addresses and telecoms for each related person
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Services\PersonService;
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Services\ContactTelecomService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();

// Initialize services
$contactService = new ContactService();
$relationService = new ContactRelationService();
$personService = new PersonService();
$addressService = new ContactAddressService();
$telecomService = new ContactTelecomService();

$relatedPersons = [];

try {
    // Get the contact for this entity
    /**
     * @global string $foreign_table The foreign table name (e.g., 'patient_data')
     * @global int $foreign_id The foreign ID (e.g., patient ID)
     */
    $foreign_table ??= '';
    $foreign_id ??= 0;
    $ownerContact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);

    if ($ownerContact) {
        // Get relationships with details (only active for display)
        $relatedEntityRecords = $relationService->getRelationshipsWithDetails($ownerContact->get_id(), false);

        // Filter to only person targets
        $relatedPersonRecords = array_filter($relatedEntityRecords, fn($rel) => isset($rel['target_table']) && $rel['target_table'] === 'person');

        // For each relationship, get addresses and telecoms
        foreach ($relatedPersonRecords as $record) {

            // Get target person's contact record
            $targetId = $record['person_id'];
            $targetContact = $contactService->getOrCreateForEntity('person', $targetId);
            $targetContactId = $targetContact->get_id();

            $relatedPerson = [
                'owner_contact_relation_id' => $record['owner_contact_relation_id'],
                'owner_contact_id' => $record['owner_contact_id'],
                'target_table' => 'person',
                'target_id' => $targetId,
                'target_contact_id' => $targetContactId,
                'first_name' => $record['first_name'] ?? '',
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
                        'contact_address_id' => $addr['contact_address_id'] ?? $addr['id'],
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
                        'contact_id' => $targetContactId,
                        'contact_telecom_id' => $telecom['contact_telecom_id'] ?? $telecom['id'],
                        'system' => $telecom['system'] ?? 'phone',
                        'use' => $telecom['use'] ?? 'home',
                        'value' => $telecom['value'] ?? '',
                        'rank' => $telecom['rank'] ?? 1,
                        'status' => $telecom['status'] ?? 'A',
                        'is_primary' => $tel['is_primary'] ?? 'N',
                        'notes' => $tel['notes'] ?? ''
                    ];
                }
            }

            $relatedPersons[] = $relatedPerson;
        }
    }
} catch (\Exception $e) {
    $logger->error("Error loading relations for display", [
        'foreign_table' => $foreign_table,
        'foreign_id' => $foreign_id,
        'error' => $e->getMessage()
    ]);
}

// Get list options
$list_relationships = generate_list_map("related_person_relationship");
$list_roles = generate_list_map("related_person_role");
$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");
$list_telecom_systems = generate_list_map("telecom_systems");
$list_telecom_uses = generate_list_map("telecom_uses");

// Generate unique table ID
$table_id = uniqid("table_display_relations_");

// Prepare template variables
$templateVars = [
    'table_id' => $table_id,
    'relatedPersons' => $relatedPersons,
    'list_relationships' => $list_relationships,
    'list_roles' => $list_roles,
    'list_address_types' => $list_address_types,
    'list_address_uses' => $list_address_uses,
    'list_telecom_systems' => $list_telecom_systems,
    'list_telecom_uses' => $list_telecom_uses,
    'has_relations' => !empty($relatedPersons)
];

$logger->debug("Error loading relations for display", [
     'foreign_table' => $foreign_table,
]);

// Render the template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/relation_display.html.twig', $templateVars);
