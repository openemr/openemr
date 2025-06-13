<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure globals.php is loaded first to set up the environment.
require_once("../../globals.php");
// Include auth.inc.php to ensure session and user authentication are fully processed.
require_once(__DIR__ . "/../../../library/auth.inc.php"); // Corrected filename and used __DIR__

// Include the REST configuration file early.
require_once __DIR__ . '/../../../_rest_config.php';

require_once __DIR__ . "/../../../library/api.inc";
require_once __DIR__ . "/../../../library/forms.inc.php";
require_once __DIR__ . "/../../../library/formdata.inc.php";
require_once __DIR__ . "/../../../library/encounter_events.inc.php";
require_once __DIR__ . "/../../../library/patient.inc.php";

// Include the module's configuration and logic classes
require_once __DIR__ . "/../../modules/custom_modules/openemrAudio2Note/config.php";
// The bootstrap handles including vendor/autoload.php for class loading
require_once __DIR__ . "/../../modules/custom_modules/openemrAudio2Note/openemr.bootstrap.php";

use OpenEMR\Modules\OpenemrAudio2Note\Logic\TranscriptionServiceClient;
use OpenEMR\Modules\OpenemrAudio2Note\Logic\LicenseStatusChecker;

// Get RestConfig instance and set non-REST context.
$restConfigInstance = \RestConfig::GetInstance();
if (!$restConfigInstance) {
    error_log("Failed to get RestConfig instance in audio_to_note/save.php");
    throw new \Exception("Failed to initialize RestConfig.");
}
\RestConfig::setNotRestCall();

// Determine which note type to use (SOAP or History and Physical)
$noteTypeSetting = $openemrAudio2NoteConfig['note_type'] ?? 'soap';
if (!in_array($noteTypeSetting, ['soap', 'history_physical'])) {
    // Log invalid configuration and default to 'soap'.
    // error_log("Invalid note_type setting in config.php: " . htmlspecialchars($noteTypeSetting) . ". Defaulting to 'soap'.");
    $noteTypeSetting = 'soap';
}

$transcriptionClient = new TranscriptionServiceClient();

// Basic input validation: Ensure this is a form submission.
if (($_POST['process'] ?? null) !== "true") {
    header("Location: " . $GLOBALS['webroot'] . "/interface/patient_file/encounter/encounter_top.php");
    exit;
}

$patient_id = $_POST['pid'] ?? $_SESSION['pid'] ?? null;
$encounter_id = $_POST['encounter'] ?? null;
$form_id = $_POST['id'] ?? null; // Might be present if editing.

if (!$patient_id || !$encounter_id) {
    $error = "Patient ID or Encounter ID missing in submission.";
    // error_log("audio_to_note/save.php: " . $error); // Log critical missing data.
    // Redirect back with error message.
    $redirectUrl = $GLOBALS['webroot'] . "/interface/patient_file/encounter/encounter_top.php?set_encounter=" . urlencode($_POST['encounter'] ?? '') . "&audio_note_error=" . urlencode($error);
    header("Location: " . $redirectUrl);
    exit;
}

// Ensure required OpenEMR form libraries are loaded.
if (!function_exists('addRecord')) {
    require_once __DIR__ . '/../../../library/formdata.inc.php';
}
if (!function_exists('addForm')) {
    require_once __DIR__ . '/../../../library/forms.inc.php';
}

// Handle file upload
$audioFile = $_FILES['audio_file'] ?? null;
$uploadError = null;
$tempFilePath = null;
$originalFilename = null;

if (!$audioFile || $audioFile['error'] !== UPLOAD_ERR_OK) {
    $uploadError = "Audio file upload failed. Error code: " . ($audioFile['error'] ?? 'Unknown');
} else {
    // Basic validation for audio file type.
    $allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/aac', 'audio/webm'];
    if (!in_array($audioFile['type'], $allowedTypes)) {
        $uploadError = "Invalid audio file type: " . htmlspecialchars($audioFile['type']);
    } else {
        $tempFilePath = $audioFile['tmp_name'];
        $originalFilename = basename($audioFile['name']);
    }
}

// --- Main Processing Logic ---
$successMessage = null;
$errorMessage = $uploadError;
$form_id_to_link = $form_id;

if (!$errorMessage && $tempFilePath) {
    try {
        // --- License Check ---
        $licenseChecker = new LicenseStatusChecker();

        if (!$licenseChecker->isLicenseActive()) {
            $errorMessage = xlt("Audio transcription feature is not licensed or license is inactive. Please configure your license in the module settings.");
        } else {
            // Prepare Transcription Parameters
            $transcriptionParams = $openemrAudio2NoteConfig['transcription_params'] ?? [];
            if (isset($_POST['min_speakers']) && is_numeric($_POST['min_speakers'])) {
                $transcriptionParams['min_speakers'] = (int)$_POST['min_speakers'];
            }
            if (isset($_POST['max_speakers']) && is_numeric($_POST['max_speakers'])) {
                $transcriptionParams['max_speakers'] = (int)$_POST['max_speakers'];
            }

            // Save basic form data with 'pending_upload' status.
            $selectedNoteType = $_POST['note_type'] ?? 'soap';
            if (!in_array($selectedNoteType, ['soap', 'history_physical'])) {
                 // error_log("Invalid note_type received from form: " . htmlspecialchars($selectedNoteType) . ". Defaulting to 'soap'.");
                 $selectedNoteType = 'soap';
            }

            $formSaveData = [
                'pid' => $patient_id,
                'encounter' => $encounter_id,
                'user' => $GLOBALS['authUserID'] ?? $_SESSION['authId'] ?? $_SESSION['userauthorized'] ?? null,
                'groupname' => $_SESSION['authProvider'] ?? $GLOBALS['authGroup'] ?? null,
                'authorized' => (isset($GLOBALS['authUserID']) && $GLOBALS['authUserID']) || (isset($_SESSION['authId']) && $_SESSION['authId']) || (isset($_SESSION['userauthorized']) && $_SESSION['userauthorized']) ? 1 : 0,
                'activity' => 1,
                'date' => date('Y-m-d H:i:s'),
                'audio_filename' => $originalFilename,
                'transcription_params' => json_encode($transcriptionParams),
                'transcription_service_response' => null,
                'status' => 'pending_upload',
                'note_type' => $selectedNoteType,
                'transcription_job_id' => null
            ];

            $setClauses = [];
            $sqlBindings = [];
            foreach ($formSaveData as $key => $value) {
                $setClauses[] = "`" . $key . "` = ?";
                $sqlBindings[] = $value;
            }
            $sqlSet = implode(', ', $setClauses);

            $new_form_id = sqlInsert("INSERT INTO `form_audio_to_note` SET " . $sqlSet, $sqlBindings);

            if (!$new_form_id) {
                // error_log("audio_to_note/save.php: Failed to get new_form_id from sqlInsert. ADODB Error: " . ($GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->ErrorMsg() : "ADODB unavailable"));
                throw new \Exception(xlt("Failed to save initial form record."));
            }

            $form_id_to_link = $new_form_id;

            // Link form_audio_to_note to the actual clinical note form (SOAP or H&P).
            $formTitle = ($selectedNoteType === 'history_physical') ? "History and Physical Note" : "SOAP Note";
            $formName = ($selectedNoteType === 'history_physical') ? "history_physical" : "soap";
            $addFormResult = addForm($encounter_id, $formTitle, 0, $formName, $patient_id, ($GLOBALS['authUserID'] ?? $_SESSION['authId'] ?? $_SESSION['userauthorized'] ?? null));

            if (!empty($addFormResult) && is_numeric($addFormResult) && $addFormResult > 0) {
                $target_clinical_note_actual_forms_id = (int)$addFormResult;
                $updateLinkedIdSql = "UPDATE `form_audio_to_note` SET `linked_forms_id` = ? WHERE `id` = ?";
                $updateLinkedIdResult = sqlStatement($updateLinkedIdSql, [$target_clinical_note_actual_forms_id, $new_form_id]);
                $affectedRowsUpdateLink = $GLOBALS['adodb']['db']->Affected_Rows();

                if ($updateLinkedIdResult === false || $affectedRowsUpdateLink < 1) {
                    // error_log("audio_to_note/save.php CRITICAL: Failed to update linked_forms_id for form_audio_to_note ID {$new_form_id} with target forms.id {$target_clinical_note_actual_forms_id}. ADODB Error: " . ($GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->ErrorMsg() : "ADODB unavailable"));
                    sqlStatement("UPDATE `form_audio_to_note` SET `status` = ?, `error_message` = ? WHERE `id` = ?", ['link_error', "Failed to link to clinical note form.", $new_form_id]);
                    throw new \Exception(xlt("Failed to establish link to the clinical note form."));
                }
            } else {
                // error_log("audio_to_note/save.php CRITICAL: addForm for target clinical note did not return a valid forms.id. Returned: " . print_r($addFormResult, true));
                sqlStatement("UPDATE `form_audio_to_note` SET `status` = ?, `error_message` = ? WHERE `id` = ?", ['link_error', "Failed to create/link clinical note form shell.", $new_form_id]);
                throw new \Exception(xlt("Failed to create or link the underlying clinical note form."));
            }

            // Initiate Transcription Job (Async)
            $openemrInstanceId = null;
            $config_row = sqlQuery("SELECT openemr_internal_random_uuid FROM audio2note_config ORDER BY id ASC LIMIT 1");

            if (!empty($config_row) && isset($config_row['openemr_internal_random_uuid'])) {
                $openemrInstanceId = $config_row['openemr_internal_random_uuid'];
            } else {
                // error_log("audio_to_note/save.php WARNING: Could not retrieve 'openemr_internal_random_uuid' from audio2note_config.");
                $openemrInstanceId = 'uuid_not_found'; // Fallback if UUID is missing.
            }

            $job_id = $transcriptionClient->initiateTranscription(
                $tempFilePath,
                $originalFilename,
                $selectedNoteType,
                (int)$patient_id,
                (int)$encounter_id,
                (int)$new_form_id,
                (int)($formSaveData['user'] ?? null),
                (string)$openemrInstanceId,
                $transcriptionParams
            );

            if ($job_id) {
                // Update record with job_id and set status to 'processing'.
                $updateSql = "UPDATE `form_audio_to_note` SET `transcription_job_id` = ?, `status` = ? WHERE `id` = ?";
                sqlStatement($updateSql, [$job_id, 'processing', $new_form_id]);
                $affectedRows = $GLOBALS['adodb']['db']->Affected_Rows();

                if ($affectedRows < 1) {
                    // error_log("audio_to_note/save.php CRITICAL: UPDATE for transcription_job_id affected 0 rows for form_id " . $new_form_id . ". ADODB Error: " . ($GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->ErrorMsg() : "ADODB unavailable"));
                    sqlStatement("UPDATE `form_audio_to_note` SET `error_message` = ? WHERE `id` = ?", ["Failed to link audio processing service job_id " . $job_id . " after initiation.", $new_form_id]);
                    throw new \Exception(xlt("Failed to store transcription Job ID after successful initiation. Please contact support."));
                }
                $successMessage = xlt("Audio transcription request submitted successfully. Job ID: ") . htmlspecialchars($job_id);
            } else {
                // Initial record was inserted, but backend job initiation failed.
                sqlStatement("UPDATE `form_audio_to_note` SET `status` = ?, `error_message` = ? WHERE `id` = ?", ['initiation_failed', 'Failed to get Job ID from transcription service.', $new_form_id]);
                throw new \Exception(xlt("Failed to submit audio for transcription."));
            }
        }

    } catch (\Throwable $e) {
        error_log("Error in audio_to_note/save.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        $errorMessage = xlt("An error occurred during processing:") . " " . htmlspecialchars($e->getMessage());
        if (isset($GLOBALS['adodb']['db']) && is_object($GLOBALS['adodb']['db']) && $GLOBALS['adodb']['db']->transCnt > 0) {
            $GLOBALS['adodb']['db']->RollbackTrans();
        }
    } finally {
        // Clean up temporary file.
        if ($tempFilePath && file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }
    }
}

// --- Redirect or Display Feedback ---
if ($errorMessage) {
    $_SESSION['form_error'] = $errorMessage;
    formJump("view.php?id=" . ($form_id_to_link ?? '') . "&encounter=" . urlencode($encounter_id));
} else {
    if ($successMessage) {
         $_SESSION['form_success'] = $successMessage;
    }
    formJump("view.php?id=" . ($form_id_to_link ?? '') . "&encounter=" . urlencode($encounter_id));
}
exit;

?>
