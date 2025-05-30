<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\OpenemrAudio2Note\Logic\Manager;

// require_once __DIR__ . '/../../../../../../library/pnotes.inc.php'; // Not needed for direct form_soap interaction
// The require_once statement for forms.inc.php has been removed.
// This core OpenEMR file should be globally available.

class OpenEMRSoapNoteManager
{
    private $pid;
    private $encounterId;
    private $form_audio_to_note_id; // This is the ID from form_audio_to_note table
    private $userId; // User performing the action

    public function __construct(int $pid, int $encounterId, int $form_audio_to_note_id, int $userId)
    {
        $this->pid = $pid;
        $this->encounterId = $encounterId;
        $this->form_audio_to_note_id = $form_audio_to_note_id;
        $this->userId = $userId;
    }

    /**
     * Saves or updates a structured SOAP note in OpenEMR's form_soap table.
     *
     * @param array $transcriptionResults An associative array containing structured transcription data (subjective, objective, assessment, plan).
     * @return mixed The ID of the saved/updated form_soap entry on success, false on failure.
     */
    public function saveNoteData(array $transcriptionResults)
    {
        // error_log("OpenEMRSoapNoteManager: saveNoteData called for form_audio_to_note_id: " . $this->form_audio_to_note_id);
        // error_log("OpenEMRSoapNoteManager: Received Transcription Data: " . json_encode($transcriptionResults));

        // Map fields from transcription results to SOAP note components.
        $full_note_text = $transcriptionResults['full_note_text'] ?? '';
        $subjective = '';
        $objective = '';

        // Attempt to parse Subjective and Objective from full_note_text if markers exist.
        $subjective_marker = "SUBJECTIVE:";
        $objective_marker = "OBJECTIVE:";
        $subjective_pos = stripos($full_note_text, $subjective_marker);
        $objective_pos = stripos($full_note_text, $objective_marker);

        if ($subjective_pos !== false && $objective_pos !== false) {
            if ($subjective_pos < $objective_pos) {
                $subjective_content_start = $subjective_pos + strlen($subjective_marker);
                $subjective = trim(substr($full_note_text, $subjective_content_start, $objective_pos - $subjective_content_start));
                $objective = trim(substr($full_note_text, $objective_pos + strlen($objective_marker)));
            } else { // Objective appears before Subjective
                $objective_content_start = $objective_pos + strlen($objective_marker);
                $objective = trim(substr($full_note_text, $objective_content_start, $subjective_pos - $objective_content_start));
                $subjective = trim(substr($full_note_text, $subjective_pos + strlen($subjective_marker)));
            }
        } elseif ($subjective_pos !== false) { // Only Subjective marker found
            $subjective = trim(substr($full_note_text, $subjective_pos + strlen($subjective_marker)));
        } elseif ($objective_pos !== false) { // Only Objective marker found
            $objective = trim(substr($full_note_text, $objective_pos + strlen($objective_marker)));
            // Content before Objective marker could be considered Subjective if no Subjective marker exists.
            $subjective = trim(substr($full_note_text, 0, $objective_pos));
        } else { // No markers found, assume all is Subjective.
            $subjective = $full_note_text;
        }

        $assessment = $transcriptionResults['billing_content'] ?? '';
        $plan = $transcriptionResults['raw_transcript'] ?? ''; // Raw transcript is mapped to Plan for SOAP.

        // error_log("OpenEMRSoapNoteManager: Mapped SOAP fields: Subjective length=" . strlen($subjective) . ", Objective length=" . strlen($objective) . ", Assessment length=" . strlen($assessment) . ", Plan length=" . strlen($plan));
        if (empty($subjective) && empty($objective) && empty($assessment) && empty($plan)) {
            error_log("OpenEMRSoapNoteManager WARNING: All mapped SOAP fields are empty for form_audio_to_note_id: " . $this->form_audio_to_note_id . ". Check backend output structure.");
        }

        if (empty($this->pid) || empty($this->encounterId) || empty($this->userId)) {
            error_log("OpenEMRSoapNoteManager CRITICAL: PID, EncounterID, or UserID is missing for form_audio_to_note_id: " . $this->form_audio_to_note_id);
            return false;
        }

        $userSql = "SELECT username, facility_id FROM users WHERE id = ?";
        $userData = sqlQuery($userSql, [$this->userId]);
        $user = $userData['username'] ?? 'admin';
        $groupname = $GLOBALS['OE_SITE_ID'] ?? ($userData['facility_id'] ?? 'Default');

        $currentDateTime = date('Y-m-d H:i:s');
        $authorized = 1;
        $activity = 1;

        $audioNoteMetaData = sqlQuery("SELECT pid, encounter, linked_forms_id FROM form_audio_to_note WHERE id = ?", [$this->form_audio_to_note_id]);

        if (!$audioNoteMetaData || empty($audioNoteMetaData['linked_forms_id'])) {
            error_log("OpenEMRSoapNoteManager CRITICAL: Could not retrieve metadata or linked_forms_id for form_audio_to_note ID {$this->form_audio_to_note_id}.");
            return false;
        }

        $targetFormsId = (int)$audioNoteMetaData['linked_forms_id'];
        $this->pid = (int)$audioNoteMetaData['pid']; // Ensure correct PID from the audio note record.
        $this->encounterId = (int)$audioNoteMetaData['encounter']; // Ensure correct Encounter ID.

        // error_log("OpenEMRSoapNoteManager: Target SOAP forms.id (from linked_forms_id): " . $targetFormsId);

        $existingSoapData = sqlQuery("SELECT id FROM form_soap WHERE id = ?", [$targetFormsId]);

        if ($existingSoapData && isset($existingSoapData['id'])) {
            // error_log("OpenEMRSoapNoteManager: Updating existing form_soap record ID {$targetFormsId}.");
            $updateSql = "UPDATE form_soap SET
                            date = ?, user = ?, groupname = ?, authorized = ?, activity = ?,
                            subjective = ?, objective = ?, assessment = ?, plan = ?, pid = ?
                          WHERE id = ?";
            $bindings = [
                $currentDateTime, $user, $groupname, $authorized, $activity,
                $subjective, $objective, $assessment, $plan, $this->pid,
                $targetFormsId
            ];
            if (sqlStatement($updateSql, $bindings) === false) {
                error_log("OpenEMRSoapNoteManager CRITICAL: Failed to update form_soap ID {$targetFormsId}. SQL Error: " . $GLOBALS['adodb']['db']->ErrorMsg());
                return false;
            }
            // error_log("OpenEMRSoapNoteManager: Update form_soap ID {$targetFormsId} successful.");
        } else {
            // error_log("OpenEMRSoapNoteManager: Inserting new form_soap record with ID {$targetFormsId}.");
            $insertSql = "INSERT INTO form_soap (
                                id, date, pid, user, groupname, authorized, activity,
                                subjective, objective, assessment, plan
                           ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $bindings = [
                $targetFormsId, $currentDateTime, $this->pid, $user, $groupname, $authorized, $activity,
                $subjective, $objective, $assessment, $plan
            ];
            $insertResult = sqlStatement($insertSql, $bindings);
            $affectedRowsInsert = $GLOBALS['adodb']['db']->Affected_Rows();

            if ($insertResult === false || $affectedRowsInsert < 1) {
                error_log("OpenEMRSoapNoteManager CRITICAL: Failed to insert into form_soap with ID {$targetFormsId}. SQL Error: " . $GLOBALS['adodb']['db']->ErrorMsg() . " Affected Rows: " . $affectedRowsInsert);
                return false;
            }
            // error_log("OpenEMRSoapNoteManager: Insert into form_soap with ID {$targetFormsId} successful.");
        }

        $newFormName = "SOAP Note (from Audio)";
        $updateFormsTableSql = "UPDATE forms SET form_name = ?, date = NOW(), deleted = 0, form_id = ? WHERE id = ?";
        if (sqlStatement($updateFormsTableSql, [$newFormName, $targetFormsId, $targetFormsId]) === false) {
            error_log("OpenEMRSoapNoteManager WARNING: Failed to update form_name in 'forms' table for ID {$targetFormsId}.");
        } else {
            // error_log("OpenEMRSoapNoteManager: Updated form_name to '{$newFormName}' in 'forms' table for ID {$targetFormsId}.");
        }
        
        // The raw transcript is already part of the 'plan' for SOAP notes as per current mapping.
        // No separate update to form_audio_to_note for raw_transcript is needed here if that's the case.

        return $targetFormsId;
    }
}
