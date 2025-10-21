<?php

/**
 * Person Search AJAX Handler - FULL DEBUG VERSION
 * Use this to diagnose CSRF token issues
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// CRITICAL: Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PersonService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\PersonPatientLinkService;
use OpenEMR\Common\Logging\SystemLogger;

// Initialize logger early
$logger = new SystemLogger();

// Set JSON header
header('Content-Type: application/json');

// ===== STEP 1: CAPTURE ALL POSSIBLE TOKEN SOURCES =====
$debugInfo = [
    'step' => 'token_capture',
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
];

// Get raw input
$rawInput = file_get_contents('php://input');
$debugInfo['raw_input_length'] = strlen($rawInput);
$debugInfo['raw_input_sample'] = substr($rawInput, 0, 200);

// Parse JSON
$jsonInput = json_decode($rawInput, true);
$debugInfo['json_parse_success'] = (json_last_error() === JSON_ERROR_NONE);
$debugInfo['json_error'] = json_last_error_msg();

// Check all possible token locations
$tokenSources = [
    'http_header' => $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null,
    'http_header_alt' => $_SERVER['HTTP_X_XSRF_TOKEN'] ?? null,
    'post_csrf_token' => $_POST['csrf_token'] ?? null,
    'post_csrf_token_form' => $_POST['csrf_token_form'] ?? null,
    'get_csrf_token' => $_GET['csrf_token'] ?? null,
    'json_csrf_token' => $jsonInput['csrf_token'] ?? null,
    'json_csrf_token_form' => $jsonInput['csrf_token_form'] ?? null,
];

$debugInfo['token_sources'] = [];
foreach ($tokenSources as $source => $value) {
    $debugInfo['token_sources'][$source] = [
        'found' => !empty($value),
        'length' => $value ? strlen($value) : 0,
        'first_10' => $value ? substr($value, 0, 10) : 'null',
        'last_10' => $value ? substr($value, -10) : 'null',
    ];
}

// ===== STEP 2: SESSION CHECK =====
$debugInfo['session'] = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'has_auth_user' => isset($_SESSION['authUser']),
    'auth_user' => $_SESSION['authUser'] ?? 'not set',
    'has_csrf_private_key' => isset($_SESSION['csrf_private_key']),
    'csrf_private_key_length' => isset($_SESSION['csrf_private_key']) ? strlen($_SESSION['csrf_private_key']) : 0,
];

// ===== STEP 3: SELECT BEST TOKEN =====
$csrfToken = null;
$tokenSourceUsed = 'none';

// Priority order for token selection
$tokenPriority = [
    'http_header' => $tokenSources['http_header'],
    'json_csrf_token' => $tokenSources['json_csrf_token'],
    'post_csrf_token' => $tokenSources['post_csrf_token'],
    'json_csrf_token_form' => $tokenSources['json_csrf_token_form'],
    'post_csrf_token_form' => $tokenSources['post_csrf_token_form'],
    'http_header_alt' => $tokenSources['http_header_alt'],
];

foreach ($tokenPriority as $source => $token) {
    if (!empty($token)) {
        $csrfToken = $token;
        $tokenSourceUsed = $source;
        break;
    }
}

$debugInfo['token_selection'] = [
    'token_found' => !empty($csrfToken),
    'source_used' => $tokenSourceUsed,
    'token_length' => $csrfToken ? strlen($csrfToken) : 0,
];

// ===== STEP 4: VERIFY TOKEN =====
$verificationResult = false;
$verificationError = null;

try {
    if (empty($csrfToken)) {
        $verificationError = 'No CSRF token found in any source';
    } else {
        $verificationResult = CsrfUtils::verifyCsrfToken($csrfToken);
        if (!$verificationResult) {
            $verificationError = 'Token verification returned false';
        }
    }
} catch (Exception $e) {
    $verificationError = 'Exception during verification: ' . $e->getMessage();
}

$debugInfo['verification'] = [
    'result' => $verificationResult,
    'error' => $verificationError,
];

// Log everything
$logger->debug("CSRF Debug Information", $debugInfo);

// ===== STEP 5: DECIDE WHETHER TO CONTINUE =====

// TEMPORARY: For debugging, you can uncomment this to bypass CSRF check
// WARNING: ONLY USE FOR TESTING, NEVER IN PRODUCTION
// $verificationResult = true;
// $debugInfo['bypass_enabled'] = true;

if (!$verificationResult) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token',
        'debug' => $debugInfo,
        'help' => [
            'issue' => $verificationError,
            'suggestions' => [
                '1. Check that your JavaScript is sending the token in the X-CSRF-TOKEN header or in the JSON body as csrf_token',
                '2. Verify the token is valid by checking the page source or using browser dev tools',
                '3. Make sure the session is active and not expired',
                '4. Check that globals.php is properly initializing the session',
                '5. Look for the token in page HTML - usually in a meta tag like: <meta name="csrf-token" content="...">',
            ],
            'how_to_get_token' => [
                'From meta tag' => 'document.querySelector(\'meta[name="csrf-token"]\')?.content',
                'From OpenEMR global' => 'csrf_token_js (if available)',
                'From data attribute' => 'document.body.dataset.csrfToken',
            ]
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Session check
if (!isset($_SESSION['authUser'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - No session',
        'debug' => [
            'session_id' => session_id(),
            'session_data' => array_keys($_SESSION ?? [])
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// ===== STEP 6: PROCEED WITH NORMAL OPERATION =====

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
            handleSearchPersons($jsonInput, $personService, $contactService, $linkService, $logger);
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
function handleSearchPersons($input, $personService, $contactService, $linkService, $logger)
{
    $firstname = trim($input['firstname'] ?? '');
    $lastname = trim($input['lastname'] ?? '');
    $birthDate = trim($input['birth_date'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');

    if (empty($firstname) && empty($lastname) && empty($birthDate) && empty($phone) && empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => xl('Please provide at least one search criterion')
        ]);
        return;
    }

    $results = [];

    // Search person table
    $personResults = searchPersonTable($firstname, $lastname, $birthDate, $phone, $email, $contactService, $linkService);
    $results = array_merge($results, $personResults);

    // Search patient_data table
    $patientResults = searchPatientDataTable($firstname, $lastname, $birthDate, $phone, $email, $contactService, $linkService);
    $results = array_merge($results, $patientResults);

    // Remove duplicates
    $results = deduplicateResults($results);

    // Sort by lastname, firstname
    usort($results, function($a, $b) {
        $lastCompare = strcasecmp($a['lastname'], $b['lastname']);
        if ($lastCompare !== 0) {
            return $lastCompare;
        }
        return strcasecmp($a['firstname'], $b['firstname']);
    });

    $logger->debug("Person search completed", [
        'criteria' => compact('firstname', 'lastname', 'birthDate', 'phone', 'email'),
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
function searchPersonTable($firstname, $lastname, $birthDate, $phone, $email, $contactService, $linkService)
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

    if (!empty($firstname)) {
        $sql .= " AND p.firstname LIKE ?";
        $params[] = "%$firstname%";
    }

    if (!empty($lastname)) {
        $sql .= " AND p.lastname LIKE ?";
        $params[] = "%$lastname%";
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

    if (!empty($email)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM contact_telecom ct3
            WHERE ct3.contact_id = c.id
            AND ct3.system = 'email'
            AND ct3.value LIKE ?
        )";
        $params[] = "%$email%";
    }

    $sql .= " GROUP BY p.id, c.id, ppl.patient_id, pd.pid, ppl.link_method, ppl.linked_date";
    $sql .= " ORDER BY p.lastname, p.firstname LIMIT 50";

    $result = sqlStatement($sql, $params);

    $persons = [];
    while ($row = sqlFetchArray($result)) {
        $persons[] = [
            'id' => $row['id'],
            'source' => 'person',
            'contact_id' => $row['contact_id'],
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'] ?? '',
            'lastname' => $row['lastname'],
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
function searchPatientDataTable($firstname, $lastname, $birthDate, $phone, $email, $contactService, $linkService)
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

    if (!empty($firstname)) {
        $sql .= " AND pd.fname LIKE ?";
        $params[] = "%$firstname%";
    }

    if (!empty($lastname)) {
        $sql .= " AND pd.lname LIKE ?";
        $params[] = "%$lastname%";
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

    if (!empty($email)) {
        $sql .= " AND pd.email LIKE ?";
        $params[] = "%$email%";
    }

    $sql .= " ORDER BY pd.lname, pd.fname LIMIT 50";

    $result = sqlStatement($sql, $params);

    $patients = [];
    while ($row = sqlFetchArray($result)) {
        $patients[] = [
            'id' => $row['id'],
            'pid' => $row['pid'],
            'source' => 'patient_data',
            'contact_id' => $row['contact_id'],
            'firstname' => $row['fname'],
            'middlename' => $row['mname'] ?? '',
            'lastname' => $row['lname'],
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
function deduplicateResults($results)
{
    $seen = [];
    $unique = [];

    foreach ($results as $result) {
        $key = strtolower($result['firstname'] . '|' . $result['lastname'] . '|' . $result['birth_date']);

        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $result;
        } else {
            if ($result['source'] === 'person') {
                foreach ($unique as $i => $existing) {
                    $existingKey = strtolower($existing['firstname'] . '|' . $existing['lastname'] . '|' . $existing['birth_date']);
                    if ($existingKey === $key && $existing['source'] === 'patient_data') {
                        $unique[$i] = $result;
                        break;
                    }
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
function handleCreatePerson($input, $personService, $contactService, $logger)
{
    // Validate required fields
    if (empty($input['firstname']) || empty($input['lastname'])) {
        $logger->debug("Person creation rejected: missing required fields", [
            'has_firstname' => !empty($input['firstname']),
            'has_lastname' => !empty($input['lastname'])
        ]);
        echo json_encode([
            'success' => false,
            'message' => xl('First name and last name are required')
        ]);
        return;
    }

    // Build person data array - NO EMAIL OR PHONE (those go in contact_telecom)
    $personData = [
        'firstname' => trim($input['firstname']),
        'lastname' => trim($input['lastname']),
        'middlename' => trim($input['middlename'] ?? ''),
        'birth_date' => $input['birth_date'] ?? null,
        'gender' => $input['gender'] ?? '',
        'notes' => trim($input['notes'] ?? '')
    ];

    // Store email/phone separately for contact_telecom
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');

    $logger->debug("Attempting to create person", [
        'personData' => $personData,
        'has_phone' => !empty($phone),
        'has_email' => !empty($email)
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
    }
    // Approach 2: Data is directly the array
    elseif (is_array($allData) && !empty($allData['id'])) {
        $personArray = $allData;
    }
    // Approach 3: Check if it's a nested structure
    elseif (isset($allData['data']) && is_array($allData['data']) && !empty($allData['data']['id'])) {
        $personArray = $allData['data'];
    }

    // Validate we got valid person data
    if (empty($personArray) || empty($personArray['id'])) {
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
        'name' => $personArray['firstname'] . ' ' . $personArray['lastname']
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
    $telecomService = new \OpenEMR\Services\ContactTelecomService();
    $addedTelecoms = [];

    try {
        if (!empty($phone)) {
            $phoneResult = $telecomService->create([
                'contact_id' => $contactId,
                'system' => 'phone',
                'value' => $phone,
                'use' => 'home',
                'rank' => 1
            ]);

            if ($phoneResult->isValid()) {
                $addedTelecoms[] = 'phone';
                $logger->debug("Phone added to contact_telecom", [
                    'contact_id' => $contactId,
                    'phone' => $phone
                ]);
            } else {
                $logger->warning("Failed to add phone to contact_telecom", [
                    'contact_id' => $contactId,
                    'errors' => $phoneResult->getInternalErrors()
                ]);
            }
        }

        if (!empty($email)) {
            $emailResult = $telecomService->create([
                'contact_id' => $contactId,
                'system' => 'email',
                'value' => $email,
                'use' => 'home',
                'rank' => 1
            ]);

            if ($emailResult->isValid()) {
                $addedTelecoms[] = 'email';
                $logger->debug("Email added to contact_telecom", [
                    'contact_id' => $contactId,
                    'email' => $email
                ]);
            } else {
                $logger->warning("Failed to add email to contact_telecom", [
                    'contact_id' => $contactId,
                    'errors' => $emailResult->getInternalErrors()
                ]);
            }
        }
    } catch (\Exception $e) {
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
            'contact_id' => $contactId,
            'firstname' => $personArray['firstname'] ?? '',
            'lastname' => $personArray['lastname'] ?? '',
            'middlename' => $personArray['middlename'] ?? '',
            'birth_date' => $personArray['birth_date'] ?? '',
            'gender' => $personArray['gender'] ?? '',
            'full_name' => ($personArray['firstname'] ?? '') . ' ' . ($personArray['lastname'] ?? ''),
            // Include the telecoms we just added
            'phone' => $phone,  // Return what was submitted
            'email' => $email,  // Return what was submitted
            'notes' => $personArray['notes'] ?? '',
            'telecoms_added' => $addedTelecoms
        ]
    ];

    echo json_encode($response);
}

function handleCheckPersonMatch($input, $linkService, $logger) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
function handleLinkPersonToPatient($input, $linkService, $logger) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
function handleUnlinkPersonFromPatient($input, $linkService, $logger) { echo json_encode(['success' => false, 'message' => 'Not implemented']); }
