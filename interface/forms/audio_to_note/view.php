<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This view.php is minimal for MVP as the primary action is creating a new transcription request.
// It could later be enhanced to show details of a past transcription if data is stored in form_audio_to_note table.

require_once("../../globals.php");
require_once("../../../library/api.inc");
require_once("../../../library/forms.inc.php");
require_once("../../../library/formdata.inc.php");

// Include the module's configuration
require_once(__DIR__ . "/../../modules/custom_modules/openemrAudio2Note/config.php");

// Get form ID from request
$form_id = $_GET['id'] ?? null;
$patient_id = $_SESSION['pid'] ?? null; // Get patient ID from session
$encounter_id = $_GET['encounter'] ?? null; // Get encounter ID from request

// Check if we have enough context to potentially load a form
if (!$form_id && !$patient_id && !$encounter_id) {
     die("Insufficient context to view Audio to Note form.");
}

$dbFormData = null;
$status = 'N/A'; // Default status
$transcription_job_id = null;
$error_message = null;
$note_type = 'soap'; // Default
$linked_forms_id = null;
$audio_filename = null;
$form_date = null;
$raw_transcript_from_db = null;


if ($form_id && is_numeric($form_id)) {
    // Use sqlQuery for direct access to form_audio_to_note table
    $sql = "SELECT pid, encounter, date, audio_filename, transcription_job_id, status, note_type, linked_forms_id, transcription_service_response FROM form_audio_to_note WHERE id = ?";
    $dbFormData = sqlQuery($sql, [$form_id]);

    if ($dbFormData) {
        $patient_id = $dbFormData['pid'] ?? $patient_id;
        $status = $dbFormData['status'] ?? 'Unknown';
        $transcription_job_id = $dbFormData['transcription_job_id'];
        $note_type = $dbFormData['note_type'] ?? 'soap';
        $linked_forms_id = $dbFormData['linked_forms_id'];
        $audio_filename = $dbFormData['audio_filename'];
        $form_date = $dbFormData['date'];

        if (!empty($dbFormData['transcription_service_response'])) {
            $response_data = json_decode($dbFormData['transcription_service_response'], true);
            if (isset($response_data['transcript_text'])) { // Based on backend workflow structure
                 $raw_transcript_from_db = $response_data['transcript_text'];
            } elseif (is_string($response_data)) { // Fallback if it's just a string
                 $raw_transcript_from_db = $response_data;
            }
        }
    } else {
        $status = 'Error: Form data not found for ID ' . htmlspecialchars($form_id);
    }
} else {
    $status = 'No specific form instance requested or invalid ID.';
}

if (!$patient_id) {
    $status = 'Error: Patient ID is missing.';
}
if (!$encounter_id) {
    $status = 'Error: Encounter ID is missing.';
}

// Form rendering
?>
<html>
<head>
    <title><?php echo xlt('View Audio to Note'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    <style>
        #status-container { padding: 15px; border: 1px solid #ccc; margin-top: 15px; background-color: #f9f9f9; border-radius: 4px; }
        .status-processing { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
        .status-completed { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .status-error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
        /* .action-links a { margin-right: 10px; } */ /* Style removed as links are removed */
    </style>
</head>
<body class="body_top">

<h3><?php echo xlt('Audio to Note Transcription Status'); ?></h3>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <p><strong><?php echo xlt('Form ID'); ?>:</strong> <?php echo htmlspecialchars($form_id ?? 'N/A'); ?></p>
            <p><strong><?php echo xlt('Patient ID'); ?>:</strong> <?php echo htmlspecialchars($patient_id ?? 'N/A'); ?></p>
            <p><strong><?php echo xlt('Encounter ID'); ?>:</strong> <?php echo htmlspecialchars($encounter_id ?? 'N/A'); ?></p>
            <?php if ($dbFormData): ?>
                <p><strong><?php echo xlt('Submission Date'); ?>:</strong> <?php echo htmlspecialchars($form_date ?? 'N/A'); ?></p>
                <p><strong><?php echo xlt('Original Audio File'); ?>:</strong> <?php echo htmlspecialchars($audio_filename ?? 'N/A'); ?></p>
                <p><strong><?php echo xlt('Selected Note Type'); ?>:</strong>
                    <?php
                    if ($note_type === 'soap') {
                        echo xlt('SOAP Note');
                    } elseif ($note_type === 'history_physical') {
                        echo xlt('History and Physical Note');
                    } else {
                        echo htmlspecialchars($note_type);
                    }
                    ?>
                </p>
            <?php endif; ?>

            <div id="status-container">
                <p><strong><?php echo xlt('Current Status'); ?>:</strong> <span id="status-text"><?php echo htmlspecialchars($status); ?></span></p>
                <?php if ($transcription_job_id): ?>
                    <p><strong><?php echo xlt('Job ID'); ?>:</strong> <span id="job-id-text"><?php echo htmlspecialchars($transcription_job_id); ?></span></p>
                <?php endif; ?>
            </div>

            <div id="action-links" style="margin-top: 15px;">
                <!-- Links are no longer populated here by JavaScript -->
            </div>

            <?php if ($raw_transcript_from_db): ?>
            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-12">
                    <label for="raw_transcript"><?php echo xlt('Raw Transcript (if available)'); ?>:</label>
                    <textarea id="raw_transcript" class="form-control" rows="10" readonly><?php echo htmlspecialchars($raw_transcript_from_db); ?></textarea>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
    const formId = <?php echo json_encode($form_id); ?>;
    const encounterId = <?php echo json_encode($encounter_id); ?>;
    const statusTextElement = document.getElementById('status-text');
    const jobIdTextElement = document.getElementById('job-id-text');
    const statusContainer = document.getElementById('status-container');
    const actionLinksContainer = document.getElementById('action-links');
    let statusInterval;

    function updateStatusDisplay(data) {
        let displayStatusText = data.status || 'N/A';
        if (data.status === 'note_updated' || data.status === 'completed') {
            displayStatusText = '<?php echo xlt("Note produced. Return to encounter summary page to view it."); ?>';
        }

        if (statusTextElement) statusTextElement.textContent = displayStatusText;
        if (jobIdTextElement) jobIdTextElement.textContent = data.transcription_job_id || '';
        
        statusContainer.className = ''; // Reset classes
        if (data.status === 'processing') {
            statusContainer.classList.add('status-processing');
        } else if (data.status === 'note_updated' || data.status === 'completed') {
            statusContainer.classList.add('status-completed');
        } else if (data.status === 'error' || data.status === 'failed' || data.status === 'initiation_failed' || data.status === 'link_error') {
            statusContainer.classList.add('status-error');
        }

        actionLinksContainer.innerHTML = ''; // Clear previous links

        if ((data.status === 'note_updated' || data.status === 'completed') && data.linked_forms_id && data.note_type) {
            if (statusInterval) clearInterval(statusInterval);
            // Links to specific notes are removed as per user feedback/requirements.
        }
        
        if (['note_updated', 'completed', 'error', 'failed', 'initiation_failed', 'link_error'].includes(data.status)) {
            if (statusInterval) clearInterval(statusInterval);
        }
    }

    function checkStatus() {
        if (!formId) {
            if (statusInterval) clearInterval(statusInterval);
            return;
        }

        fetch(`ajax_get_status.php?form_id=${formId}&encounter_id=${encounterId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data) {
                    updateStatusDisplay(data);
                    const rawTranscriptArea = document.getElementById('raw_transcript');
                    if (rawTranscriptArea && data.raw_transcript && rawTranscriptArea.value !== data.raw_transcript) {
                        rawTranscriptArea.value = data.raw_transcript;
                    }
                }
            })
            .catch(error => {
                if (statusTextElement) statusTextElement.textContent = '<?php echo xlt("Error fetching status."); ?>';
                statusContainer.className = 'status-error';
            });
    }

    // Initial status display from PHP
    updateStatusDisplay({
        status: <?php echo json_encode($status); ?>,
        transcription_job_id: <?php echo json_encode($transcription_job_id); ?>,
        note_type: <?php echo json_encode($note_type); ?>,
        linked_forms_id: <?php echo json_encode($linked_forms_id); ?>,
        raw_transcript: <?php echo json_encode($raw_transcript_from_db); ?>
    });

    // Start polling if status is not terminal (based on initial PHP status)
    const initialStatus = <?php echo json_encode($status); ?>;
    if (formId && !['note_updated', 'completed', 'error', 'failed', 'initiation_failed', 'link_error'].includes(initialStatus)) {
        statusInterval = setInterval(checkStatus, 10000); // Check every 10 seconds
    }
</script>
<div style="font-size: 1.1em; font-weight: bold; margin-top: 20px; text-align: center;">
        <?php echo xlt("No need to stay on this page. The note will be inserted into the patient's chart once the process is complete."); ?>
    </div>

</body>
</html>
