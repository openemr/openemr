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
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Services\ContactTelecomService;
use OpenEMR\Services\PersonService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();

// Initialize services
$contactService = new ContactService();
$relationService = new ContactRelationService();
$addressService = new ContactAddressService();
$telecomService = new ContactTelecomService();
$personService = new PersonService();

$relations = [];

try {
	// Get relationships with person details
    $relationRecords = $relationService->getRelationshipsWithPersonDetails($foreign_table_name, $foreign_id, false);
    
    // For each relationship, get addresses and telecoms
    foreach ($relationRecords as $record) {
        $relation = [
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
            'notes' => $record['notes'] ?? '',
            'addresses' => [],
            'telecoms' => []
        ];
        
        

        if ($record['contact_id']) {
        
        	// Get addresses for this person's contact        
            $addressRecords = $addressService->getAddressesForContact($record['contact_id'], false);
            foreach ($addressRecords as $addr) {
                $relation['addresses'][] = [
                    'contact_address_id' => $addr['contact_address_id'] ?? $addr['id'],
                    'use' => $addr['use'] ?? 'home',
                    'type' => $addr['type'] ?? 'both',
                    'line1' => $addr['line1'] ?? '',
                    'line2' => $addr['line2'] ?? '',
                    'city' => $addr['city'] ?? '',
                    'state' => $addr['state'] ?? '',
                    'zip' => $addr['zip'] ?? '',
                    'postalcode' => $addr['postalcode'] ?? $addr['zip'] ?? '',
                    'country' => $addr['country'] ?? '',
                    'district' => $addr['district'] ?? '',
                    'status' => $addr['status'] ?? 'A',
                    'is_primary' => $addr['is_primary'] ?? 'N'
                ];
            }
            
            // Get telecoms for this person's contact
            $telecomRecords = $telecomService->getTelecomsForContact($record['contact_id'], false);
            foreach ($telecomRecords as $tel) {
                $relation['telecoms'][] = [
                    'id' => $tel['id'],
                    'system' => $tel['system'] ?? 'phone',
                    'use' => $tel['use'] ?? 'home',
                    'value' => $tel['value'] ?? '',
                    'rank' => $tel['rank'] ?? 1,
                    'status' => $tel['status'] ?? 'A',
                    'is_primary' => $tel['is_primary'] ?? 'N',
                    'notes' => $tel['notes'] ?? ''
                ];
            }
        }
        
        $relations[] = $relation;
    }
} catch (\Exception $e) {
    $logger->error("Error loading relations for display", [
        'foreign_table' => $foreign_table_name,
        'foreign_id' => $foreign_id,
        'error' => $e->getMessage()
    ]);
}

// Get list options for dropdowns
$list_relationships = generate_list_map("related_person-relationship");
$list_roles = generate_list_map("related_person-role");
$list_address_types = generate_list_map("address-types");
$list_address_uses = generate_list_map("address-uses");
$list_telecom_systems = generate_list_map("telecom-systems");
$list_telecom_uses = generate_list_map("telecom-uses");

// Generate unique table ID
$table_id = uniqid("table_text_relations_");

// Prepare template variables
$templateVars = [
    'table_id' => $table_id,
    'relations' => $relations,
    'list_relationships' => $list_relationships,
    'list_roles' => $list_roles,
    'list_address_types' => $list_address_types,
    'list_address_uses' => $list_address_uses,
    'list_telecom_systems' => $list_telecom_systems,
    'list_telecom_uses' => $list_telecom_uses,
    'has_relations' => !empty($relations)
];

// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/relation_display.html.twig', $templateVars);