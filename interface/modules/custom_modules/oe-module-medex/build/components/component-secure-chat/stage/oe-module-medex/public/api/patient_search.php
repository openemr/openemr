<?php
/**
 * Patient Search API — used by Communications Center compose modal
 *
 * GET ?q=<search>&csrf_token=<token>
 * Returns JSON { patients: [{pid, fname, lname, dob, phone_cell}] }
 */

require_once __DIR__ . '/../../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('patients', 'demo')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF']);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if (strlen($q) < 2) {
    echo json_encode(['patients' => []]);
    exit;
}

$like = '%' . $q . '%';
$result = sqlStatement(
    "SELECT pid, fname, lname, DOB, phone_cell
     FROM patient_data
     WHERE (fname LIKE ? OR lname LIKE ? OR CONCAT(lname, ', ', fname) LIKE ? OR CAST(pid AS CHAR) LIKE ?)
       AND pid > 0
     ORDER BY lname, fname
     LIMIT 20",
    [$like, $like, $like, $like]
);

$patients = [];
while ($row = sqlFetchArray($result)) {
    $patients[] = [
        'pid'        => (int)$row['pid'],
        'fname'      => (string)($row['fname'] ?? ''),
        'lname'      => (string)($row['lname'] ?? ''),
        'dob'        => (string)($row['DOB'] ?? ''),
        'phone_cell' => (string)($row['phone_cell'] ?? ''),
    ];
}

echo json_encode(['patients' => $patients]);
