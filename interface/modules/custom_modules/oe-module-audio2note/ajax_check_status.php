<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// ajax_check_status.php

// Ensure globals.php is loaded first to set up the environment.
// Relative path from this file's location in the module to OpenEMR's root globals.php
require_once(__DIR__ . "/../../../../../globals.php");

// Include necessary OpenEMR libraries
use OpenEMR\Common\Csrf\CsrfUtils; // For CSRF protection if needed for POST, less critical for GET status checks if read-only
use OpenEMR\Modules\OpenemrAudio2Note\Logic\TranscriptionServiceClient;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\Manager\OpenEMRSoapNoteManager;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\Manager\OpenEMRHistoryPhysicalNoteManager;

// Ensure user is authenticated (standard OpenEMR practice for AJAX handlers)
if (!isset($GLOBALS['userauthorized']) || $GLOBALS['userauthorized'] != 1) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get form_id from request (assuming GET for simplicity, or POST if preferred)
$formId = $_REQUEST['form_id'] ?? null; // Use $_REQUEST to handle GET or POST

if (empty($formId) || !is_numeric($formId)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid or missing form_id.']);
    exit;
}
$formId = (int)$formId;

// Initialize response array
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];
header('Content-Type: application/json');

try {
    // 1. Retrieve form_audio_to_note record
    $sql = "SELECT id, pid, encounter, user, transcription_job_id, note_type, status FROM form_audio_to_note WHERE id = ?";
    $formRecord = sqlQuery($sql, [$formId]);

    if (!$formRecord || empty($formRecord['transcription_job_id'])) {
        throw new \Exception("Form record not found or job ID missing for form_id: " . $formId);
    }

    $jobId = $formRecord['transcription_job_id'];
    $currentDbStatus = $formRecord['status'];

    // If already completed or failed in DB, can return that status directly unless forced refresh
    // For now, always poll if job_id exists
    
    // 2. Instantiate TranscriptionServiceClient
    $transcriptionClient = new TranscriptionServiceClient(); // Constructor loads config

    // 3. Call getTranscriptionStatus
    $serviceResponse = $transcriptionClient->getTranscriptionStatus($jobId);

    if (!$serviceResponse || !isset($serviceResponse['status'])) {
        throw new \Exception("Invalid or empty response from transcription status service for job_id: " . $jobId);
    }

    $serviceStatus = $serviceResponse['status'];
    $response['service_status'] = $serviceStatus; // Include service's view of the status

    // 4. Process Service Response
    switch ($serviceStatus) {
        case 'completed':
            $transcriptionResults = $serviceResponse['transcription_results'] ?? null;
            if (empty($transcriptionResults)) {
                throw new \Exception("Transcription completed by the service but no results provided for job_id: " . $jobId);
            }

            // Update DB: status, results
            $updateSql = "UPDATE form_audio_to_note SET status = ?, transcription_service_response = ? WHERE id = ?";
            sqlStatement($updateSql, ['completed', json_encode($transcriptionResults), $formId]);

            // Extract raw_transcript from the results
            $rawTranscript = $transcriptionResults['raw_transcript'] ?? '';

            // Update form_audio_to_note with the raw transcript
            $updateRawTranscriptSql = "UPDATE form_audio_to_note SET raw_transcript = ? WHERE id = ?";
            $updateRawTranscriptResult = sqlStatement($updateRawTranscriptSql, [$rawTranscript, $formId]);
            if ($updateRawTranscriptResult === false) {
                error_log("Error updating raw_transcript in form_audio_to_note for form_id {$formId}. SQL Error: " . $GLOBALS['adodb']['db']->ErrorMsg());
                // Decide if this should cause the overall status to be an error
            }


            // Trigger Note Creation/Update Logic
            $noteType = $formRecord['note_type'];
            $pid = (int)$formRecord['pid'];
            $encounterId = (int)$formRecord['encounter'];
            // User ID for note manager might be the original user who submitted, or system user
            $userIdForNote = (int)$formRecord['user'];

            $noteManager = null;
            if ($noteType === 'soap') {
                $noteManager = new OpenEMRSoapNoteManager($pid, $encounterId, $formId, $userIdForNote);
            } elseif ($noteType === 'history_physical') {
                $noteManager = new OpenEMRHistoryPhysicalNoteManager($pid, $encounterId, $formId, $userIdForNote);
            }

            if ($noteManager) {
                // Assuming saveNoteData can handle the structured results from the service
                // And it knows whether to create a new note or update an existing one
                $noteManager->saveNoteData($transcriptionResults);

                // Optionally update status to 'note_updated'
                $updateStatusSql = "UPDATE form_audio_to_note SET status = ? WHERE id = ?";
                sqlStatement($updateStatusSql, ['note_updated', $formId]);
                $response['status'] = 'note_updated';
            } else {
                error_log("Unknown note_type '{$noteType}' for form_id {$formId}, job_id {$jobId}. Cannot update note.");
                $response['status'] = 'completed_error_note_update'; // Results fetched, but note update failed
            }

            $response['message'] = 'Transcription completed and note updated.';
            $response['transcription_results'] = $transcriptionResults; // Send results back to UI
            $response['raw_transcript'] = $rawTranscript; // Also send raw transcript back to UI if needed
            break;

        case 'processing':
            // Update DB status if it changed (e.g., from 'pending_upload' to 'processing')
            if ($currentDbStatus !== 'processing') {
                $updateSql = "UPDATE form_audio_to_note SET status = ? WHERE id = ?";
                sqlStatement($updateSql, ['processing', $formId]);
            }
            $response['status'] = 'processing';
            $response['message'] = 'Transcription is still processing.';
            break;

        case 'failed':
            $errorMessage = $serviceResponse['error_message'] ?? 'Transcription failed with an unknown error.';
            $updateSql = "UPDATE form_audio_to_note SET status = ?, transcription_service_response = ? WHERE id = ?";
            sqlStatement($updateSql, ['failed', json_encode(['error' => $errorMessage]), $formId]);
            
            $response['status'] = 'failed';
            $response['message'] = $errorMessage;
            break;
        
        case 'not_found':
            $updateSql = "UPDATE form_audio_to_note SET status = ? WHERE id = ?";
            sqlStatement($updateSql, ['error_job_not_found', $formId]);
            $response['status'] = 'error_job_not_found';
            $response['message'] = 'Transcription job not found on the external processing service.';
            break;

        default:
            // Unknown status from the service
            // error_log("Unknown status '{$serviceStatus}' from the service for job_id {$jobId}, form_id {$formId}"); // Potentially too verbose for production
            $response['message'] = 'Received an unknown status from transcription service: ' . htmlspecialchars($serviceStatus);
            // Optionally update DB status to a generic error
            $updateSql = "UPDATE form_audio_to_note SET status = ? WHERE id = ?";
            sqlStatement($updateSql, ['error_unknown_service_status', $formId]);
            break;
    }

} catch (\Throwable $e) {
    error_log("Error in ajax_check_status.php for form_id " . ($formId ?? 'UNKNOWN') . ": " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response['status'] = 'error';
    $response['message'] = 'Server error: ' . htmlspecialchars($e->getMessage());
    // Ensure HTTP status code reflects server error if not already set
    if (http_response_code() === 200) { // Check if headers not already sent with an error code
        header('HTTP/1.1 500 Internal Server Error');
    }
}

echo json_encode($response);
exit;

?>
