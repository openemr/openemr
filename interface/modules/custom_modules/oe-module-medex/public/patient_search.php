<?php
/**
 * MedEx Patient Search API
 *
 * Called by MedEx SaaS (e.g. SmsHub) to search or list patients directly
 * from the OpenEMR patient_data table without requiring a browser session.
 *
 * Auth: POST api_key must match medex_prefs.ME_api_key for the given practice_id.
 *
 * Actions:
 *   list   — return up to 100 patients ordered by recent activity (or alphabetical)
 *   search — return up to 20 patients matching the query string
 */

$ignoreAuth = true;
$sessionAllowWrite = false;

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExHmac.php');

use OpenEMR\Modules\MedEx\MedExHmac;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$rawBody     = file_get_contents('php://input');
$bodyParams  = [];
parse_str($rawBody, $bodyParams);

$practice_id = (int)($bodyParams['practice_id'] ?? 0);
$action      = trim((string)($bodyParams['action'] ?? 'list'));
$q           = trim((string)($bodyParams['q']      ?? ''));

if (!$practice_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing practice_id']);
    exit;
}

// Fetch expected API key
$prefs = sqlQuery(
    "SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ? LIMIT 1",
    [$practice_id]
);

if (empty($prefs['ME_api_key'])) {
    error_log("[MedEx patient_search] No API key configured for practice {$practice_id}");
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'API key not configured']);
    exit;
}

// HMAC validation — replay-safe, timing-safe
[$hmacOk, $hmacErr] = MedExHmac::validate($rawBody, $prefs['ME_api_key']);
if (!$hmacOk) {
    error_log("[MedEx patient_search] HMAC validation failed for practice {$practice_id}: {$hmacErr}");
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Authentication failed']);
    exit;
}

// ------------------------------------------------------------------
// Build patient list
// ------------------------------------------------------------------

if ($action === 'search' && $q !== '') {
    $safe = '%' . $q . '%';
    $result = sqlStatement(
        "SELECT pid, fname, lname, phone_cell, phone_home, email,
                DOB, street, city, state, postal_code
         FROM patient_data
         WHERE fname LIKE ? OR lname LIKE ? OR phone_cell LIKE ? OR CONCAT(fname,' ',lname) LIKE ? OR pid LIKE ?
         ORDER BY lname, fname
         LIMIT 20",
        [$safe, $safe, $safe, $safe, $safe]
    );
} else {
    // list — all active patients, alphabetical
    $result = sqlStatement(
        "SELECT pid, fname, lname, phone_cell, phone_home, email,
                DOB, street, city, state, postal_code
         FROM patient_data
         WHERE pid > 0
         ORDER BY lname ASC, fname ASC
         LIMIT 100"
    );
}

$patients = [];
while ($row = sqlFetchArray($result)) {
    $patients[] = [
        'pid'        => (string)$row['pid'],
        'fname'      => $row['fname'] ?? '',
        'lname'      => $row['lname'] ?? '',
        'phone_cell' => $row['phone_cell'] ?? '',
        'phone_home' => $row['phone_home'] ?? '',
        'email'      => $row['email'] ?? '',
        'dob'        => $row['DOB'] ?? '',
    ];
}

echo json_encode(['success' => true, 'patients' => $patients]);
