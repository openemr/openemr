<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../globals.php";
require_once __DIR__ . "/../../../library/sql.inc.php";

header('Content-Type: application/json');

$form_id = $_GET['form_id'] ?? null;
$encounter_id_from_get = $_GET['encounter_id'] ?? null;

$response = [
    'status' => 'error',
    'message' => 'Form ID not provided or invalid.',
    'transcription_job_id' => null,
    'note_type' => null,
    'linked_forms_id' => null,
    'raw_transcript' => null,
    'encounter_id' => $encounter_id_from_get
];

if ($form_id && is_numeric($form_id)) {
    // Prepare SQL to fetch status and related data for the given form ID.
    $sql = "SELECT status, transcription_job_id, note_type, linked_forms_id, transcription_service_response, encounter FROM form_audio_to_note WHERE id = ?";
    $data = sqlQuery($sql, [$form_id]);

    if ($data) {
        $response['status'] = $data['status'] ?? 'unknown';
        $response['transcription_job_id'] = $data['transcription_job_id'];
        $response['note_type'] = $data['note_type'];
        $response['linked_forms_id'] = $data['linked_forms_id'];
        // Use encounter from DB if available, otherwise fallback to GET param
        $response['encounter_id'] = $data['encounter'] ?? $encounter_id_from_get;

        if (!empty($data['transcription_service_response'])) {
            $transcript_data = json_decode($data['transcription_service_response'], true);
            if (isset($transcript_data['transcript_text'])) {
                $response['raw_transcript'] = $transcript_data['transcript_text'];
            } elseif (is_string($transcript_data)) {
                // Handle cases where the response might be a simple string
                $response['raw_transcript'] = $transcript_data;
            }
        }
        $response['message'] = 'Status retrieved successfully.';
    } else {
        $response['message'] = 'Form data not found for ID: ' . htmlspecialchars($form_id);
    }
}

echo json_encode($response);
exit;

?>