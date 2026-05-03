<?php

/**
 * MedEx Secure Chat -> OpenEMR Encounter Saver
 *
 * Creates an encounter and stores the chat transcript as a clinical note.
 */

$ignoreAuth = true;
$sessionAllowWrite = false;

require_once(__DIR__ . "/../../../../globals.php");
require_once($GLOBALS['srcdir'] . "/forms.inc.php");

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\ClinicalNotesService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$required = ['practice_id', 'pid', 'api_key', 'transcript'];
foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing field: ' . $field]);
        exit;
    }
}

$practiceId = (int)$data['practice_id'];
$pid = (int)$data['pid'];
$apiKey = (string)$data['api_key'];
$transcript = trim((string)$data['transcript']);
if ($practiceId <= 0 || $pid <= 0 || $transcript === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

// Validate using the configured MedEx credentials for this practice.
$apiRow = sqlQuery("SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ? LIMIT 1", [$practiceId]);
$storedApiKey = (string)($apiRow['ME_api_key'] ?? '');
$apiMatch = false;
if ($storedApiKey !== '' && $apiKey !== '') {
    $apiMatch = hash_equals($storedApiKey, $apiKey)
        || str_starts_with($storedApiKey, $apiKey)
        || str_starts_with($apiKey, $storedApiKey);
}
if (!$apiRow || !$apiMatch) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid API key']);
    exit;
}

$patient = sqlQuery("SELECT pid FROM patient_data WHERE pid = ? LIMIT 1", [$pid]);
if (!$patient) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Patient not found']);
    exit;
}

$facility = sqlQuery("SELECT id, name FROM facility WHERE id = ? LIMIT 1", [$practiceId]);
if (!$facility) {
    $facility = sqlQuery("SELECT id, name FROM facility ORDER BY id ASC LIMIT 1");
}

$facilityId = (int)($facility['id'] ?? 0);
$facilityName = (string)($facility['name'] ?? 'Main Facility');
$encounter = QueryUtils::generateId();
$nowDate = date('Y-m-d H:i:s');
$reason = 'Secure Chat Transcript';

$encounterFormId = sqlInsert(
    "INSERT INTO form_encounter SET date = ?, onset_date = ?, reason = ?, facility = ?, facility_id = ?, pid = ?, encounter = ?, provider_id = 0",
    [$nowDate, $nowDate, $reason, $facilityName, $facilityId, $pid, $encounter]
);

addForm($encounter, 'New Patient Encounter', $encounterFormId, 'newpatient', $pid, '1', 'NOW()', 'medex');

$clinicalNotesService = new ClinicalNotesService();
$clinicalFormId = $clinicalNotesService->createClinicalNotesParentForm($pid, $encounter, 1);

$record = [
    'form_id' => $clinicalFormId,
    'pid' => $pid,
    'encounter' => $encounter,
    'authorized' => 1,
    'activity' => 1,
    'date' => date('Y-m-d'),
    'user' => 'medex',
    'groupname' => 'Default',
    'code' => null,
    'codetext' => 'Secure Chat Transcript',
    'description' => $transcript,
    'external_id' => null,
    'clinical_notes_type' => 'Progress Note',
    'clinical_notes_category' => 'Communication',
    'note_related_to' => 'MedEx Secure Chat',
    'uuid' => (new UuidRegistry(['table_name' => 'form_clinical_notes']))->createUuid(),
];
$saved = $clinicalNotesService->saveArray($record);

http_response_code(200);
echo json_encode([
    'success' => true,
    'encounter' => $encounter,
    'clinical_note_id' => $saved['id'] ?? null,
]);
