<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\OpenemrAudio2Note\Services;

// Autoloading should handle all namespaced classes via composer.
// Ensure the entry point (e.g., cron_runner.php or OpenEMR's task scheduler) includes openemr.bootstrap.php.
// Removed direct require_once for namespaced classes:
// require_once __DIR__ . '/../../Logic/TranscriptionServiceClient.php'; // Incorrect path & not needed with autoload
// require_once __DIR__ . '/../../Logic/Manager/OpenEMRSoapNoteManager.php'; // Incorrect path & not needed with autoload
// require_once __DIR__ . '/../../Logic/Manager/OpenEMRHistoryPhysicalNoteManager.php'; // Incorrect path & not needed with autoload

use OpenEMR\Modules\OpenemrAudio2Note\Logic\TranscriptionServiceClient;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\Manager\OpenEMRSoapNoteManager;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\Manager\OpenEMRHistoryPhysicalNoteManager;
use OpenEMR\Common\Database\Database; // For database access
use OpenEMR\Common\Logging\SystemLogger; // For logging
use OpenEMR\Common\Crypto\CryptoGen; // For decrypting license key

// Need to implement OpenEMR's background service interface or extend a base class
// Example based on common patterns, exact interface/base class might vary by OpenEMR version
// Assuming a simple execute method is called by the scheduler.

class TranscriptionPollingService
{
    private $db; // ADODB connection
    private $transcriptionServiceClient;
    private $systemLog;

    public function __construct()
    {
        $this->db = $GLOBALS['adodb']['db'];
        $this->systemLog = new SystemLogger();

        if ($this->db) {
            // $dbType = $this->db->databaseType ?? 'N/A';
            // $this->systemLog->info("TranscriptionPollingService Constructor: DB object available. Type: " . $dbType);
            // if (method_exists($this->db, 'IsConnected') && !$this->db->IsConnected()) {
            //     $this->systemLog->error("TranscriptionPollingService Constructor: DB IsConnected() returned false.");
            // }
        } else {
            $this->systemLog->error("TranscriptionPollingService Constructor: DB object is NULL from GLOBALS. Polling cannot proceed.");
        }

        try {
            $this->transcriptionServiceClient = new TranscriptionServiceClient();
            // $this->systemLog->info("TranscriptionPollingService Constructor: TranscriptionServiceClient initialized successfully.");
        } catch (\Throwable $e) {
            $this->systemLog->error("TranscriptionPollingService Constructor: CRITICAL - Failed to initialize TranscriptionServiceClient: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->transcriptionServiceClient = null;
        }
    }

    /**
     * Main method called by the OpenEMR task scheduler.
     */
    public function execute()
    {
        $this->systemLog->info("TranscriptionPollingService: Polling service execution started.");

        if ($this->transcriptionServiceClient === null) {
            $this->systemLog->error("TranscriptionPollingService: Aborting polling, TranscriptionServiceClient failed to initialize.");
            return;
        }

        $pendingJobs = $this->getPendingTranscriptionJobs();

        if (empty($pendingJobs)) {
            $this->systemLog->info("TranscriptionPollingService: No pending transcription jobs found.");
            $this->systemLog->info("TranscriptionPollingService: Polling service execution finished.");
            return;
        }

        $this->systemLog->info("TranscriptionPollingService: Found " . count($pendingJobs) . " pending jobs to process.");

        foreach ($pendingJobs as $job) {
            // $this->systemLog->info("TranscriptionPollingService: Processing job details: " . print_r($job, true)); // Can be very verbose
            $jobId = $job['transcription_job_id'];
            $formId = $job['id'];
            // $currentDbStatus = $job['status']; // Not directly used in this loop's logic flow after fetching

            $this->systemLog->info("TranscriptionPollingService: Polling backend for job_id: " . $jobId . " (form_id: " . $formId . ")");

            try {
                $backendResponse = $this->transcriptionServiceClient->getTranscriptionStatus($jobId);
                $this->systemLog->info("TranscriptionPollingService: Raw backendResponse for job_id {$jobId}: " . print_r($backendResponse, true));

                if ($backendResponse && isset($backendResponse['status'])) {
                    $backendAudioProcessStatus = $backendResponse['status'];
                    $this->systemLog->info("TranscriptionPollingService: Received status '{$backendAudioProcessStatus}' for job_id: " . $jobId);

                    if (strpos($backendAudioProcessStatus, 'error_') === 0) {
                        $errorMessage = $backendResponse['error_message'] ?? 'Unknown client error during polling.';
                        $this->systemLog->error("TranscriptionPollingService: Client error for job_id {$jobId}. Status: {$backendAudioProcessStatus}. Message: {$errorMessage}.");
                        $this->updateJobStatus($formId, 'error_polling_client_issue', null, "Client Error: " . $backendAudioProcessStatus . " - " . $errorMessage);
                        continue;
                    }

                    switch ($backendAudioProcessStatus) {
                        case 'completed':
                            $rawTranscriptPayload = $backendResponse['transcript'] ?? null;
                            $this->systemLog->info("PollingService Debug: Raw transcript payload for job_id {$jobId}: " . print_r($rawTranscriptPayload, true));
                            $transcriptionResults = null;

                            if (is_array($rawTranscriptPayload) || is_object($rawTranscriptPayload)) {
                                $transcriptionResults = (array) $rawTranscriptPayload;
                            } elseif (!empty($rawTranscriptPayload) && is_string($rawTranscriptPayload)) {
                                $transcriptionResults = json_decode($rawTranscriptPayload, true);
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $this->systemLog->error("PollingService: Failed to decode transcript JSON string for job_id: " . $jobId . ". JSON error: " . json_last_error_msg());
                                    $transcriptionResults = null;
                                }
                            }
 
                            // Prepare payload for database storage (must be string or null)
                            $stringifiedPayload = null;
                            if ($rawTranscriptPayload !== null) {
                                if (is_string($rawTranscriptPayload)) {
                                    // If it's a string, use it as is.
                                    // It's assumed to be either valid JSON or a plain string intended for storage.
                                    $stringifiedPayload = $rawTranscriptPayload;
                                } else { // is_array or is_object
                                    $stringifiedPayload = json_encode($rawTranscriptPayload);
                                    if ($stringifiedPayload === false) {
                                        $this->systemLog->error("PollingService: Failed to json_encode rawTranscriptPayload for job_id: " . $jobId . ". Type was: " . gettype($rawTranscriptPayload));
                                        // Store an error JSON if encoding fails
                                        $stringifiedPayload = json_encode(['error' => 'Failed to encode transcript payload for storage.']);
                                    }
                                }
                            }
                            $this->systemLog->info("PollingService Debug: stringifiedPayload for job_id {$jobId}: " . substr($stringifiedPayload, 0, 500) . (strlen($stringifiedPayload) > 500 ? '...' : ''));
                            $this->systemLog->info("PollingService Debug: transcriptionResults (after decoding) for job_id {$jobId}: " . print_r($transcriptionResults, true));
 
                            if (empty($transcriptionResults) || !is_array($transcriptionResults)) {
                                $this->systemLog->error("PollingService: Backend reported completed but no valid/usable results for job_id: " . $jobId);
                                $this->updateJobStatus($formId, 'completed_no_results', $stringifiedPayload, "Backend reported completed but no valid/usable results.");
                                continue; // Use continue instead of break to process other jobs
                            }
 
                            $this->systemLog->info("TranscriptionPollingService: Job completed, processing results for job_id: " . $jobId);
                            $this->updateJobStatus($formId, 'completed', $stringifiedPayload); // Use the stringified payload
 
                            $noteType = $job['note_type'];
                            $pid = (int)$job['pid'];
                            $encounterId = (int)$job['encounter'];
                            $userIdForNote = (int)$job['user'];

                            $noteManager = null;
                            if ($noteType === 'soap') {
                                $noteManager = new OpenEMRSoapNoteManager($pid, $encounterId, $formId, $userIdForNote);
                            } elseif ($noteType === 'history_physical') {
                                $noteManager = new OpenEMRHistoryPhysicalNoteManager($pid, $encounterId, $formId, $userIdForNote);
                            }

                            if ($noteManager) {
                                try {
                                    $this->systemLog->info("PollingService: Calling saveNoteData for job_id {$jobId}. Note Type: {$noteType}.");
                                    $noteManager->saveNoteData($transcriptionResults);
                                    $this->systemLog->info("PollingService: Note updated successfully for job_id: " . $jobId);
                                    $this->updateJobStatus($formId, 'note_updated');
                                } catch (\Throwable $noteUpdateException) {
                                    $this->systemLog->error("TranscriptionPollingService: Error updating note for job_id {$jobId}: " . $noteUpdateException->getMessage());
                                    $this->updateJobStatus($formId, 'completed_error_note_update', null, "Error updating note: " . $noteUpdateException->getMessage());
                                }
                            } else {
                                $this->systemLog->error("TranscriptionPollingService: Unknown note_type '{$noteType}' for form_id {$formId}, job_id {$jobId}. Cannot update note.");
                                $this->updateJobStatus($formId, 'completed_error_note_update', null, "Unknown note type for update.");
                            }
                            break;

                        case 'failed':
                            $errorMessage = $backendResponse['error_message'] ?? 'Transcription failed with an unknown error.';
                            $this->systemLog->error("TranscriptionPollingService: Job failed for job_id: " . $jobId . " - " . $errorMessage);
                            $this->updateJobStatus($formId, 'failed', null, $errorMessage);
                            break;

                        case 'processing':
                            $this->systemLog->info("TranscriptionPollingService: Job still processing for job_id: " . $jobId);
                            break;
                            
                        case 'not_found':
                             $this->systemLog->error("TranscriptionPollingService: Job not found on backend service for job_id: " . $jobId);
                             $this->updateJobStatus($formId, 'error_job_not_found', null, "Job not found on backend service.");
                             break;

                        default:
                            $this->systemLog->error("TranscriptionPollingService: Received unknown status '{$backendAudioProcessStatus}' from backend for job_id: " . $jobId);
                            $this->updateJobStatus($formId, 'error_unknown_backend_audio_process_status', null, "Unknown status from backend: " . $backendAudioProcessStatus);
                            break;
                    }
                } else {
                    $this->systemLog->error("TranscriptionPollingService: Invalid, empty, or unexpected response structure from TranscriptionServiceClient for job_id: " . $jobId . ". Response: " . print_r($backendResponse, true));
                    $this->updateJobStatus($formId, 'error_client_response_unexpected', null, "Unexpected response structure from client for job_id: " . $jobId);
                }
            } catch (\Throwable $e) {
                $this->systemLog->error("TranscriptionPollingService: Exception during polling for job_id {$jobId}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                $this->updateJobStatus($formId, 'error_polling', null, "Polling error: " . $e->getMessage());
            }
        }
        
        $this->systemLog->info("TranscriptionPollingService: Polling service execution finished.");
    }

    /**
     * Retrieves pending transcription jobs from the database.
     * @return array Array of database rows.
     */
    private function getPendingTranscriptionJobs(): array
    {
        $sql = "SELECT id, pid, encounter, user, transcription_job_id, note_type, status
                FROM form_audio_to_note
                WHERE status IN (?, ?)
                AND transcription_job_id IS NOT NULL";
        $bindings = ['processing', 'pending_upload'];
        // $this->systemLog->info("getPendingTranscriptionJobs: SQL: " . $sql . " Bindings: " . json_encode($bindings));
        
        $result = $this->db->Execute($sql, $bindings);

        if ($result === false) {
            $dbError = $this->db->ErrorMsg();
            $logMessage = "TranscriptionPollingService: Database error retrieving pending jobs.";
            if (!empty($dbError)) {
                $logMessage .= " DB Error: " . (string)$dbError;
            }
            $this->systemLog->error($logMessage);
            return [];
        }
        
        if (is_object($result) && method_exists($result, 'RecordCount')) {
            $numRows = $result->RecordCount();
            // $this->systemLog->info("getPendingTranscriptionJobs: Query executed. RecordCount: " . $numRows);
            if ($numRows > 0) {
                $allRows = $result->GetAll();
                $result->Close();
                // $this->systemLog->info("getPendingTranscriptionJobs: GetAll() result: " . print_r($allRows, true)); // Can be very verbose
                return $allRows ?: [];
            } else {
                $result->Close();
                return [];
            }
        } else {
            $this->systemLog->error("getPendingTranscriptionJobs: Query result is not a valid ADODB RecordSet object or RecordCount method missing.");
            return [];
        }
    }

    /**
     * Updates the status and optionally results/error message for a job in the database.
     * @param int $formId The ID of the form_audio_to_note record.
     * @param string $newStatus The new status to set.
     * @param string|null $resultsJson JSON string of results.
     * @param string|null $errorMessage Error message.
     */
    private function updateJobStatus(int $formId, string $newStatus, ?string $resultsJson = null, ?string $errorMessage = null): void
    {
        $sql = "UPDATE form_audio_to_note SET status = ?";
        $bindings = [$newStatus];

        if ($resultsJson !== null) {
            $sql .= ", transcription_service_response = ?";
            $bindings[] = $resultsJson;
        } elseif ($errorMessage !== null) {
             $sql .= ", transcription_service_response = ?"; // Store error in the same field for simplicity
             $bindings[] = json_encode(['error' => $errorMessage]);
        }
        
        $sql .= " WHERE id = ?";
        $bindings[] = $formId;

        if ($this->db->Execute($sql, $bindings) === false) {
            $this->systemLog->error("TranscriptionPollingService: Database error updating status for form_id {$formId} to '{$newStatus}': " . $this->db->ErrorMsg());
        } else {
            // $this->systemLog->info("TranscriptionPollingService: Updated status for form_id {$formId} to '{$newStatus}'."); // Can be verbose
        }
    }

    /**
     * Triggers the note creation/update logic after successful transcription.
     * This method is now effectively integrated into the main execute loop.
     * @param int $formId The ID of the form_audio_to_note record.
     * @param string $noteType The type of note (SOAP/H&P).
     * @param int $pid Patient ID.
     * @param int $encounterId Encounter ID.
     * @param int $userIdForNote User ID to associate with the note.
     * @param array $transcriptionResults The structured transcription results.
     */
    private function processCompletedTranscription(int $formId, string $noteType, int $pid, int $encounterId, int $userIdForNote, array $transcriptionResults): void
    {
        // This method's logic has been moved into the main execute() loop's 'completed' case
        // for better flow and to avoid redundant RestConfig checks if possible.
        // Kept as a placeholder or for future refactoring if needed.
        $this->systemLog->warning("TranscriptionPollingService: processCompletedTranscription was called, but its logic is now in execute(). This indicates a potential refactoring need or old call path.");
    }
}

?>
