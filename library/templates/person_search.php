<?php
/**
 * AJAX endpoint for person search functionality
 * Used by relation_form to search for existing persons
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PersonService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Common\Logging\SystemLogger;

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Check ACL permissions
if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}


$logger = new SystemLogger();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$firstname = $input['firstname'] ?? '';
$lastname = $input['lastname'] ?? '';
$foreignTable = $input['foreign_table'] ?? '';
$foreignId = $input['foreign_id'] ?? 0;

header('Content-Type: application/json');

try {
    $personService = new PersonService();
    $contactService = new ContactService();
    $relationService = new ContactRelationService();
    
    // Build search criteria
    $criteria = [];
    if (!empty($firstname)) {
        $criteria['firstname'] = $firstname;
    }
    if (!empty($lastname)) {
        $criteria['lastname'] = $lastname;
    }
    
    // Search for persons
    $searchResult = $personService->search($criteria, 50, 0);
    
    if (!$searchResult->hasData()) {
        echo json_encode([]);
        exit;
    }
    
    
    $persons = $searchResult->getData();
    $results = [];
    
    // For each person, check if they already have a relationship with this entity
    foreach ($persons as $person) {
        $contact = $contactService->getForEntity('person', $person['id']);
        
        $alreadyRelated = false;
        if ($contact && !empty($foreignTable) && !empty($foreignId)) {
            $alreadyRelated = $relationService->relationshipExists(
                $contact->get_id(),
                $foreignTable,
                $foreignId
            );
        }
        
        $results[] = [
            'id' => $person['id'],
            'contact_id' => $contact ? $contact->get_id() : null,
            'firstname' => $person['firstname'] ?? '',
            'lastname' => $person['lastname'] ?? '',
            'birth_date' => $person['birth_date'] ?? '',
            'email' => $person['email'] ?? '',
            'gender' => $person['gender'] ?? '',
            'already_related' => $alreadyRelated
        ];
    }
    
    // Filter out already related persons (optional - could show with indicator instead)
    $results = array_filter($results, function($person) {
        return !$person['already_related'];
    });
    
    echo json_encode(array_values($results));
    
} catch (\Exception $e) {
    $logger->error("Error in person search", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred during search',
        'message' => $e->getMessage()
    ]);
}