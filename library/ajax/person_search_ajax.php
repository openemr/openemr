<?php

/**
 * Person Search AJAX Handler - FULL DEBUG VERSION
 * Use this to diagnose CSRF token issues
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");
/**
 * @global string $srcdir defined in globals
 */
$srcdir ??= ''; // should fatally fail but passes phpstan
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PersonService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\PersonPatientLinkService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\ContactTelecomService;

// Initialize logger early
$logger = new SystemLogger();

// Set JSON header
header('Content-Type: application/json');


// Get raw input
$rawInput = file_get_contents('php://input');
// Parse JSON
$jsonInput = json_decode($rawInput, true);

// normal token validation
$csrfToken = $jsonInput['csrf_token'] ?? $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
    CsrfUtils::csrfNotVerified(); // die
}
// Initialize services
$personService = new PersonService();
$contactService = new ContactService();
$linkService = new PersonPatientLinkService();

$action = $jsonInput['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

$logger->debug("Processing action", [
    'action' => $action,
    'user' => $_SESSION['authUser']
]);

try {
    switch ($action) {
        case 'search_persons':
            handleSearchPersons($jsonInput, $contactService, $linkService, $logger);
            break;

        case 'create_person':
            handleCreatePerson($jsonInput, $personService, $contactService, $logger);
            break;

        case 'check_person_match':
            handleCheckPersonMatch($jsonInput, $linkService, $logger);
            break;

        case 'link_person_to_patient':
            handleLinkPersonToPatient($jsonInput, $linkService, $logger);
            break;

        case 'unlink_person_from_patient':
            handleUnlinkPersonFromPatient($jsonInput, $linkService, $logger);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'action_received' => $action,
                'available_actions' => [
                    'search_persons',
                    'create_person',
                    'check_person_match',
                    'link_person_to_patient',
                    'unlink_person_from_patient'
                ]
            ], JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    $logger->error("Person search AJAX error", [
        'action' => $action,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

// ===== HANDLER FUNCTIONS =====

/**
 * Search for persons in both person and patient_data tables
 */
function handleSearchPersons(array $input, $contactService, $linkService, $logger): void
{
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $birthDate = trim($input['birth_date'] ?? '');
    $phone = trim($input['phone'] ?? '');

    if (($first_name === '' || $first_name === '0') && ($last_name === '' || $last_name === '0') && ($birthDate === '' || $birthDate === '0') && ($phone === '' || $phone === '0')) {
        echo json_encode([
            'success' => false,
            'message' => xl('Please provide at least one search criterion')
        ]);
        return;
    }

    $results = [];

    // Search person table
    $personResults = searchPersonTable($first_name, $last_name, $birthDate, $phone);
    $results = array_merge($results, $personResults);

    // Search patient_data table
    $patientResults = searchPatientDataTable($first_name, $last_name, $birthDate, $phone);
    $results = array_merge($results, $patientResults);

    // Remove duplicates
    $results = deduplicateResults($results);

    // Sort by last_name, first_name
    usort($results, function (array $a, array $b): int {
        $lastCompare = strcasecmp((string)$a['last_name'], (string)$b['last_name']);
        if ($lastCompare !== 0) {
            return $lastCompare;
        }
        return strcasecmp((string)$a['first_name'], (string)$b['first_name']);
    });

    $logger->debug("Person search completed", [
        'criteria' => ['first_name' => $first_name, 'last_name' => $last_name, 'birthDate' => $birthDate, 'phone' => $phone],
        'result_count' => count($results)
    ]);

    echo json_encode([
        'success' => true,
        'persons' => $results,
        'count' => count($results)
    ]);
}

/**
 * Search person table with link information and contact telecom data
 */
function searchPersonTable($first_name, $last_name, $birthDate, $phone): array
{
    $sql = "SELECT p.*,
                   c.id as contact_id,
                   ppl.patient_id as linked_patient_id,
                   pd.pid as linked_patient_pid,
                   ppl.link_method,
                   ppl.linked_date,
                   GROUP_CONCAT(DISTINCT CASE WHEN ct.system = 'phone' THEN ct.value END) as phone,
                   GROUP_CONCAT(DISTINCT CASE WHEN ct.system = 'email' THEN ct.value END) as email
            FROM person p
            LEFT JOIN contact c ON c.foreign_table_name = 'person' AND c.foreign_id = p.id
            LEFT JOIN person_patient_link ppl ON ppl.person_id = p.id AND ppl.active = 1
            LEFT JOIN patient_data pd ON pd.id = ppl.patient_id
            LEFT JOIN contact_telecom ct ON ct.contact_id = c.id
            WHERE 1=1";

    $params = [];

    if (!empty($first_name)) {
        $sql .= " AND p.first_name LIKE ?";
        $params[] = "%$first_name%";
    }

    if (!empty($last_name)) {
        $sql .= " AND p.last_name LIKE ?";
        $params[] = "%$last_name%";
    }

    if (!empty($birthDate)) {
        $sql .= " AND p.birth_date = ?";
        $params[] = $birthDate;
    }

    if (!empty($phone)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM contact_telecom ct2
            WHERE ct2.contact_id = c.id
            AND ct2.system = 'phone'
            AND ct2.value LIKE ?
        )";
        $params[] = "%$phone%";
    }

    /*if (!empty($email)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM contact_telecom ct3
            WHERE ct3.contact_id = c.id
            AND ct3.system = 'email'
            AND ct3.value LIKE ?
        )";
        $params[] = "%$email%";
    }*/

    $sql .= " GROUP BY p.id, c.id, ppl.patient_id, pd.pid, ppl.link_method, ppl.linked_date";
    $sql .= " ORDER BY p.last_name, p.first_name LIMIT 50";

    $result = QueryUtils::sqlStatementThrowException($sql, $params);

    $persons = [];
    while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
        $persons[] = [
            'id' => $row['id'],
            'target_table' => 'person',
            'contact_id' => $row['contact_id'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? '',
            'last_name' => $row['last_name'],
            'birth_date' => $row['birth_date'],
            'gender' => $row['sex'] ?? '',
            'phone' => $row['phone'] ?? '',
            'email' => $row['email'] ?? '',
            'notes' => $row['notes'] ?? '',
            'is_also_patient' => !empty($row['linked_patient_id']),
            'linked_patient_id' => $row['linked_patient_id'],
            'linked_patient_pid' => $row['linked_patient_pid'],
            'link_method' => $row['link_method'],
            'linked_date' => $row['linked_date']
        ];
    }

    return $persons;
}

/**
 * Search patient_data table with link information
 */
function searchPatientDataTable($first_name, $last_name, $birthDate, $phone): array
{
    $sql = "SELECT pd.*,
                   c.id as contact_id,
                   ppl.person_id as linked_person_id,
                   ppl.link_method,
                   ppl.linked_date
            FROM patient_data pd
            LEFT JOIN contact c ON c.foreign_table_name = 'patient_data' AND c.foreign_id = pd.id
            LEFT JOIN person_patient_link ppl ON ppl.patient_id = pd.id AND ppl.active = 1
            WHERE 1=1";

    $params = [];

    if (!empty($first_name)) {
        $sql .= " AND pd.fname LIKE ?";
        $params[] = "%$first_name%";
    }

    if (!empty($last_name)) {
        $sql .= " AND pd.lname LIKE ?";
        $params[] = "%$last_name%";
    }

    if (!empty($birthDate)) {
        $sql .= " AND pd.DOB = ?";
        $params[] = $birthDate;
    }

    if (!empty($phone)) {
        $sql .= " AND (pd.phone_cell LIKE ? OR pd.phone_home LIKE ? OR pd.phone_contact LIKE ?)";
        $params[] = "%$phone%";
        $params[] = "%$phone%";
        $params[] = "%$phone%";
    }

    $sql .= " ORDER BY pd.lname, pd.fname LIMIT 50";

    $result = QueryUtils::sqlStatementThrowException($sql, $params);

    $patients = [];
    while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
        $patients[] = [
            'id' => $row['id'],
            'pid' => $row['pid'],
            'target_table' => 'patient_data',
            'contact_id' => $row['contact_id'],
            'first_name' => $row['fname'],
            'middle_name' => $row['mname'] ?? '',
            'last_name' => $row['lname'],
            'birth_date' => $row['DOB'],
            'gender' => $row['sex'] ?? '',
            'phone' => $row['phone_cell'] ?? $row['phone_home'] ?? $row['phone_contact'] ?? '',
            'email' => $row['email'] ?? '',
            'is_also_patient' => true,
            'linked_person_id' => $row['linked_person_id'],
            'link_method' => $row['link_method'],
            'linked_date' => $row['linked_date']
        ];
    }

    return $patients;
}

/**
 * Remove duplicate results
 */
function deduplicateResults($results): array
{
    $seen = [];
    $unique = [];

    foreach ($results as $result) {
        $key = strtolower($result['first_name'] . '|' . $result['last_name'] . '|' . $result['birth_date']);

        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $result;
        } elseif ($result['source'] === 'person') {
            foreach ($unique as $i => $existing) {
                $existingKey = strtolower($existing['first_name'] . '|' . $existing['last_name'] . '|' . $existing['birth_date']);
                if ($existingKey === $key && $existing['source'] === 'patient_data') {
                    $unique[$i] = $result;
                    break;
                }
            }
        }
    }

    return $unique;
}

/**
 * Handle create person request
 */
/**
 * Handle create person request - COMPLETE FIXED VERSION
 * Properly handles email/phone in contact_telecom instead of person table
 */
function handleCreatePerson(array $input, PersonService $personService, $contactService, $logger): void
{
    // Validate required fields
    if (empty($input['first_name']) || empty($input['last_name'])) {
        $logger->debug("Person creation rejected: missing required fields", [
            'has_first_name' => !empty($input['first_name']),
            'has_last_name' => !empty($input['last_name'])
        ]);
        echo json_encode([
            'success' => false,
            'message' => xl('First name and last name are required')
        ]);
        return;
    }

    // Build person data array - NO EMAIL OR PHONE (those go in contact_telecom)
    $personData = [
        'first_name' => trim((string)$input['first_name']),
        'last_name' => trim((string)$input['last_name']),
        'middle_name' => trim($input['middle_name'] ?? ''),
        'birth_date' => $input['birth_date'] ?? null,
        'gender' => $input['gender'] ?? '',
        'notes' => trim($input['notes'] ?? '')
    ];

    // Store email/phone separately for contact_telecom
    $phone = trim($input['phone'] ?? '');
    /*$email = trim($input['email'] ?? '');*/

    $logger->debug("Attempting to create person", [
        'personData' => $personData,
        'has_phone' => $phone !== '' && $phone !== '0'
    ]);

    // Create person via service
    $result = $personService->create($personData);

    // Check for validation errors
    if (!$result->isValid()) {
        $logger->error("Person creation validation failed", [
            'personData' => $personData,
            'validationMessages' => $result->getValidationMessages(),
            'internalErrors' => $result->getInternalErrors()
        ]);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', array_merge(
                $result->getValidationMessages(),
                $result->getInternalErrors()
            ))
        ]);
        return;
    }

    // Extract person data with multiple fallback approaches
    $allData = $result->getData();

    $logger->debug("Person creation result data structure", [
        'hasData' => $result->hasData(),
        'dataCount' => count($allData),
        'dataKeys' => array_keys($allData),
        'firstElementType' => isset($allData[0]) ? gettype($allData[0]) : 'not_set'
    ]);

    // Extract person data - try multiple approaches
    $personArray = null;

    // Approach 1: Data at index 0 (most common)
    if (isset($allData[0]) && is_array($allData[0]) && !empty($allData[0]['id'])) {
        $personArray = $allData[0];
    } // Approach 2: Data is directly the array
    elseif (is_array($allData) && !empty($allData['id'])) {
        $personArray = $allData;
    } // Approach 3: Check if it's a nested structure
    elseif (isset($allData['data']) && is_array($allData['data']) && !empty($allData['data']['id'])) {
        $personArray = $allData['data'];
    }

    // Validate we got valid person data
    if ($personArray === null || $personArray === [] || empty($personArray['id'])) {
        $logger->error("Person creation succeeded but data extraction failed", [
            'hasData' => $result->hasData(),
            'dataStructure' => $allData,
            'personData' => $personData
        ]);
        echo json_encode([
            'success' => false,
            'message' => xl('Failed to create person: no data returned from service'),
            'debug' => [
                'hasData' => $result->hasData(),
                'dataCount' => count($allData)
            ]
        ]);
        return;
    }

    $personId = $personArray['id'];

    $logger->info("Person created successfully via AJAX", [
        'person_id' => $personId,
        'name' => $personArray['first_name'] . ' ' . $personArray['last_name']
    ]);

    // Create or get contact for this person
    // getOrCreateForEntity returns a Contact object directly
    $contact = $contactService->getOrCreateForEntity('person', $personId);

    if (empty($contact) || empty($contact->get_id())) {
        $logger->error("Failed to create contact for new person", [
            'person_id' => $personId
        ]);
        echo json_encode([
            'success' => false,
            'message' => xl('Person created but failed to create contact record')
        ]);
        return;
    }

    $contactId = $contact->get_id();

    $logger->debug("Contact created/retrieved for person", [
        'person_id' => $personId,
        'contact_id' => $contactId
    ]);

    // Now add phone and email to contact_telecom if provided
    $telecomService = new ContactTelecomService();
    $addedTelecoms = [];

    try {
        if ($phone !== '' && $phone !== '0') {
            // if the save fails an exception will be thrown
            $telecomService->saveTelecomsForContact($contactId, [
                'contact_id' => [$contactId],
                'system' => ['phone'],
                'value' => [$phone],
                'use' => ['home'],
                'rank' => [1]
            ]);

            $addedTelecoms[] = 'phone';
            $logger->debug("Phone added to contact_telecom", [
                'contact_id' => $contactId,
                'phone' => $phone
            ]);
        }

        // email was commented out above, so commenting this out too
//        if (!empty($email)) {
//            $emailResult = $telecomService->saveTelecomsForContact($contactId, [
//                'contact_id' => [$contactId],
//                'system' => ['email'],
//                'value' => [$email],
//                'use' => ['home'],
//                'rank' => [1]
//            ]);
//
//            $addedTelecoms[] = 'email';
//            $logger->debug("Email added to contact_telecom", [
//                'contact_id' => $contactId,
//                'email' => $email
//            ]);
//        }
    } catch (Exception $e) {
        $logger->error("Exception adding telecoms", [
            'contact_id' => $contactId,
            'error' => $e->getMessage()
        ]);
        // Continue anyway - person and contact were created successfully
    }

    // Prepare response with person data (email/phone come from contact_telecom now)
    $response = [
        'success' => true,
        'message' => xl('Person created successfully'),
        'person' => [
            'id' => $personId,
            'target_table' => 'person',  // Newly created persons are always in person table
            'contact_id' => $contactId,
            'first_name' => $personArray['first_name'] ?? '',
            'last_name' => $personArray['last_name'] ?? '',
            'middle_name' => $personArray['middle_name'] ?? '',
            'birth_date' => $personArray['birth_date'] ?? '',
            'gender' => $personArray['gender'] ?? '',
            'full_name' => ($personArray['first_name'] ?? '') . ' ' . ($personArray['last_name'] ?? ''),
            // Include the telecoms we just added
            'phone' => $phone,  // Return what was submitted
//            'email' => $email,  // Return what was submitted
            'notes' => $personArray['notes'] ?? '',
            'telecoms_added' => $addedTelecoms
        ]
    ];

    echo json_encode($response);
}

function handleCheckPersonMatch($input, $linkService, $logger): void
{
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
}

function handleLinkPersonToPatient($input, $linkService, $logger): void
{
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
}

function handleUnlinkPersonFromPatient($input, $linkService, $logger): void
{
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
}
