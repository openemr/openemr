<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Include the form-specific includes
require_once __DIR__ . "/audio_to_note.inc.php";

// This is a minimal report.php file as the primary output is to the SOAP note.
// It could be expanded later to show a summary of the transcription process if needed.

function audio_to_note_report($pid, $encounter, $cols, $id)
{
    // Fetch the current record from form_audio_to_note
    $formRecord = null;
    if ($id) {
        // Ensure pid is also used for security/context, though id should be unique.
        $sql = "SELECT id, status, transcription_job_id, transcription_service_response, note_type FROM form_audio_to_note WHERE id = ? AND pid = ?";
        $formRecord = sqlQuery($sql, [$id, $pid]);
    }

    echo "<div class='form-report'>";
    echo "<h4>Audio to Note Transcription Status</h4>";
    // Unique ID for status area
    echo "<div id='transcriptionStatusArea_" . htmlspecialchars($id) . "'>";

    if ($formRecord) {
        $status = $formRecord['status'] ?? 'unknown';
        $jobId = $formRecord['transcription_job_id'] ?? null;
        $resultsJson = $formRecord['transcription_service_response'] ?? null;
        $results = $resultsJson ? json_decode($resultsJson, true) : null;

        echo "<p><strong>Status:</strong> <span id='statusText_" . htmlspecialchars($id) . "'>" . htmlspecialchars(ucfirst($status)) . "</span></p>";

        if ($status === 'processing' && $jobId) {
            echo "<button type='button' class='btn btn-info btn-sm' id='refreshStatusBtn_" . htmlspecialchars($id) . "' onclick='refreshTranscriptionStatus(" . htmlspecialchars($id) . ")'>Refresh Status</button>";
            echo "<span id='loadingIndicator_" . htmlspecialchars($id) . "' style='display:none; margin-left:10px;'><i class='fa fa-spinner fa-spin'></i> Loading...</span>";
        } elseif ($status === 'completed' || $status === 'note_updated') {
            // Assuming 'transcription_results' is the key from ajax_check_status.php
            if (isset($results['transcription_results'])) {
                echo "<h5>Transcription Results:</h5>";
                // Display results - customize as needed. This is a basic example.
                echo "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars(print_r($results['transcription_results'], true)) . "</pre>";
            } elseif (is_array($results)) { // Fallback if structure is different but results exist
                 echo "<h5>Transcription Data:</h5>";
                 echo "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars(print_r($results, true)) . "</pre>";
            } else {
                echo "<p>Transcription completed, but results are not in the expected format or are empty.</p>";
            }
        } elseif ($status === 'failed') {
            $errorMessage = $results['error'] ?? ($results['message'] ?? 'Transcription failed with an unknown error.');
            echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($errorMessage) . "</p>";
        } elseif ($status === 'error_job_not_found' || $status === 'error_unknown_audio_processing_service_status' || $status === 'completed_error_note_update') {
            $errorMessage = $results['message'] ?? 'An error occurred with the transcription job.';
             echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($errorMessage) . "</p>";
        } else {
            echo "<p>Your audio file is being processed. Status will update here.</p>";
             if ($jobId) {
                 echo "<button type='button' class='btn btn-info btn-sm' id='refreshStatusBtn_" . htmlspecialchars($id) . "' onclick='refreshTranscriptionStatus(" . htmlspecialchars($id) . ")'>Refresh Status</button>";
                 echo "<span id='loadingIndicator_" . htmlspecialchars($id) . "' style='display:none; margin-left:10px;'><i class='fa fa-spinner fa-spin'></i> Loading...</span>";
             }
        }
    } else {
        echo "<p>Could not retrieve transcription status for this form entry.</p>";
    }

    echo "</div>"; // end transcriptionStatusArea
    echo "</div>"; // end form-report

    // Add JavaScript for AJAX call
    ?>
    <script type="text/javascript">
        function refreshTranscriptionStatus(formId) {
            var statusArea = $('#transcriptionStatusArea_' + formId);
            var statusText = $('#statusText_' + formId);
            var refreshButton = $('#refreshStatusBtn_' + formId);
            var loadingIndicator = $('#loadingIndicator_' + formId);

            if (!statusArea.length || !statusText.length) {
                // console.error("Status display elements not found for formId: " + formId); // Debugging, remove for production
                return;
            }

            loadingIndicator.show();
            if(refreshButton.length) refreshButton.prop('disabled', true);

            // Construct URL to ajax_check_status.php
            var ajaxUrl = '<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/openemrAudio2Note/ajax_check_status.php';

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: { form_id: formId },
                dataType: 'json',
                success: function(response) {
                    loadingIndicator.hide();
                    if(refreshButton.length) refreshButton.prop('disabled', false);

                    if (response && response.status) {
                        statusText.text(response.status.charAt(0).toUpperCase() + response.status.slice(1).replace(/_/g, ' '));
                        
                        var contentHtml = "<p><strong>Status:</strong> <span id='statusText_" + formId + "'>" + statusText.text() + "</span></p>";

                        if (response.status === 'processing') {
                            if(refreshButton.length) {
                                // Button already exists, ensure it's enabled
                            } else {
                                // Add button if it wasn't there (e.g. initial load was 'pending')
                                contentHtml += "<button type='button' class='btn btn-info btn-sm' id='refreshStatusBtn_" + formId + "' onclick='refreshTranscriptionStatus(" + formId + ")'>Refresh Status</button>";
                                contentHtml += "<span id='loadingIndicator_" + formId + "' style='display:none; margin-left:10px;'><i class='fa fa-spinner fa-spin'></i> Loading...</span>";
                            }
                        } else {
                            if(refreshButton.length) refreshButton.remove(); // Remove button if no longer processing
                        }

                        if (response.status === 'completed' || response.status === 'note_updated') {
                            if (response.transcription_results) {
                                contentHtml += "<h5>Transcription Results:</h5><pre style='white-space: pre-wrap; word-wrap: break-word;'>" + escapeHtml(JSON.stringify(response.transcription_results, null, 2)) + "</pre>";
                            } else {
                                contentHtml += "<p>Transcription completed, but no results data found.</p>";
                            }
                        } else if (response.status === 'failed' || response.status === 'error_job_not_found' || response.status === 'error_unknown_audio_processing_service_status' || response.status === 'completed_error_note_update') {
                            contentHtml += "<p style='color:red;'><strong>Error:</strong> " + escapeHtml(response.message || 'An unknown error occurred.') + "</p>";
                        } else if (response.message) {
                            contentHtml += "<p>" + escapeHtml(response.message) + "</p>";
                        }
                        statusArea.html(contentHtml);

                    } else {
                        statusText.text('Error');
                        statusArea.append("<p style='color:red;'>Error: Invalid response from status check.</p>");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    loadingIndicator.hide();
                    if(refreshButton.length) refreshButton.prop('disabled', false);
                    statusText.text('Error');
                    statusArea.append("<p style='color:red;'>AJAX Error: " + escapeHtml(textStatus) + " - " + escapeHtml(errorThrown) + "</p>");
                    // console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText); // Debugging, remove for production
                }
            });
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            if (typeof unsafe !== 'string') {
                 try { unsafe = JSON.stringify(unsafe); } catch (e) { unsafe = String(unsafe); }
            }
            return unsafe
                 .replace(/&/g, "&")
                 .replace(/</g, "<")
                 .replace(/>/g, ">")
                 .replace(/"/g, """)
                 .replace(/'/g, "&#039;");
        }

        $(document).ready(function() {
            $('button[id^="refreshStatusBtn_"]').each(function() {
                var formId = this.id.split('_')[1];
                var statusTextElement = $('#statusText_' + formId);
                if (statusTextElement.text().toLowerCase().includes('processing') || statusTextElement.text().toLowerCase().includes('pending')) {
                     // console.log("Auto-refresh could be triggered for formId: " + formId); // Debugging, remove for production
                }
            });
        });
    </script>
    <?php
}

?>
