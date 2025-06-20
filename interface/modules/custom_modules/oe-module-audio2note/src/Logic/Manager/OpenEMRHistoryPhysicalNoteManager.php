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


class OpenEMRHistoryPhysicalNoteManager
{
    private $pid;
    private $encounterId;
    private $formId; // This is the ID from form_audio_to_note
    private $userId; // User performing the action

    public function __construct(int $pid, int $encounterId, int $formId, int $userId)
    {
        $this->pid = $pid;
        $this->encounterId = $encounterId;
        $this->formId = $formId;
        $this->userId = $userId;
    }

    /**
     * Saves or updates a History and Physical note in OpenEMR based on transcription results.
     *
     * @param array $transcriptionResults An associative array containing structured transcription data.
     *                                    Expected to have an 'output' key with the full H&P text,
     *                                    and potentially 'plan' and 'remarks' at the top level or within 'output'.
     * @return mixed The ID of the saved/updated form_history_physical record on success, false on failure.
     */
    public function saveNoteData(array $transcriptionResults)
    {
        // error_log("OpenEMRHistoryPhysicalNoteManager: saveNoteData called for form_id: " . $this->formId);
        // error_log("OpenEMRHistoryPhysicalNoteManager: Transcription Results Payload: " . json_encode($transcriptionResults));

        // The 'full_note_text' from the external service contains the main H&P content.
        $history_physical_text = $transcriptionResults['full_note_text'] ?? '';
        if (empty($history_physical_text)) {
            // error_log("OpenEMRHistoryPhysicalNoteManager: 'full_note_text' not found or empty for form_id: " . $this->formId . ". Saving empty string.");
            $history_physical_text = '';
        }

        // The 'billing_content' from the external service is mapped to the 'plan' field.
        $plan_text = $transcriptionResults['billing_content'] ?? '';
        if (empty($plan_text)) {
             // error_log("OpenEMRHistoryPhysicalNoteManager: 'billing_content' not found or empty for form_id: " . $this->formId . ". Saving empty string for plan.");
             $plan_text = '';
        }

        $remarks_text = ''; // Remarks are not currently mapped from transcription results.

        $raw_transcript = $transcriptionResults['raw_transcript'] ?? '';
        if (empty($raw_transcript)) {
             // error_log("OpenEMRHistoryPhysicalNoteManager: 'raw_transcript' not found or empty for form_id: " . $this->formId . ". Saving empty string.");
             $raw_transcript = '';
        }

        // Brief logging of extracted content lengths can be useful for production if issues arise.
        // error_log("OpenEMRHistoryPhysicalNoteManager: Extracted history_physical_text length: " . strlen($history_physical_text));
        // error_log("OpenEMRHistoryPhysicalNoteManager: Extracted plan_text length: " . strlen($plan_text));
        // error_log("OpenEMRHistoryPhysicalNoteManager: Extracted raw_transcript length: " . strlen($raw_transcript));

        if (empty($this->pid) || empty($this->encounterId)) {
            error_log("OpenEMRHistoryPhysicalNoteManager CRITICAL: Patient ID (pid) or Encounter ID is missing for form_id: " . $this->formId);
            return false;
        }

        $audioNoteMetaData = sqlQuery("SELECT pid, encounter, user, linked_forms_id FROM form_audio_to_note WHERE id = ?", [$this->formId]);

        if (!$audioNoteMetaData || empty($audioNoteMetaData['linked_forms_id'])) {
            error_log("OpenEMRHistoryPhysicalNoteManager CRITICAL: Could not retrieve metadata or linked_forms_id for form_audio_to_note ID {$this->formId}.");
            return false;
        }

        $targetFormsId = (int)$audioNoteMetaData['linked_forms_id'];
        $this->pid = (int)$audioNoteMetaData['pid']; // Ensure PID from the record is used.
        $this->encounterId = (int)$audioNoteMetaData['encounter']; // Ensure Encounter ID from the record is used.
        // User ID for the note is $this->userId, set during construction.

        // error_log("OpenEMRHistoryPhysicalNoteManager: Target H&P forms.id (from linked_forms_id): " . $targetFormsId);

        $userSql = "SELECT username, facility_id FROM users WHERE id = ?";
        $userData = sqlQuery($userSql, [$this->userId]);
        $userForHp = $userData['username'] ?? 'admin'; // Fallback to 'admin' if user not found.
        $groupnameForHp = $GLOBALS['OE_SITE_ID'] ?? ($userData['facility_id'] ?? 'Default'); // Use site_id or facility_id.

        $existingHpData = sqlQuery("SELECT id FROM form_history_physical WHERE id = ?", [$targetFormsId]);

        if ($existingHpData && isset($existingHpData['id'])) {
            // error_log("OpenEMRHistoryPhysicalNoteManager: Updating existing form_history_physical record ID {$targetFormsId}.");
            $updateSql = "UPDATE `form_history_physical` SET
                            `history_physical` = ?, `plan` = ?, `remarks` = ?, `transcript` = ?,
                            `date` = NOW(), `user` = ?, `groupname` = ?, `authorized` = 1, `activity` = 1,
                            `encounter` = ?, `pid` = ?
                          WHERE `id` = ?";
            $bindings = [
                $history_physical_text, $plan_text, $remarks_text, $raw_transcript,
                $userForHp, $groupnameForHp, $this->encounterId, $this->pid, $targetFormsId
            ];
            $updateResult = sqlStatement($updateSql, $bindings);

            if ($updateResult === false) {
                error_log("OpenEMRHistoryPhysicalNoteManager CRITICAL: Failed to update form_history_physical ID {$targetFormsId}. SQL Error: " . $GLOBALS['adodb']['db']->ErrorMsg());
                return false;
            }
            // error_log("OpenEMRHistoryPhysicalNoteManager: Update form_history_physical ID {$targetFormsId} successful.");
        } else {
            // error_log("OpenEMRHistoryPhysicalNoteManager: Inserting new form_history_physical record with ID {$targetFormsId}.");
            $insertSql = "INSERT INTO `form_history_physical` (
                                `id`, `date`, `pid`, `user`, `groupname`, `authorized`, `activity`,
                                `history_physical`, `plan`, `remarks`, `transcript`, `encounter`
                           ) VALUES (?, NOW(), ?, ?, ?, 1, 1, ?, ?, ?, ?, ?)";
            $bindings = [
                $targetFormsId, $this->pid, $userForHp, $groupnameForHp,
                $history_physical_text, $plan_text, $remarks_text, $raw_transcript, $this->encounterId
            ];
            
            $insertResult = sqlStatement($insertSql, $bindings);
            $affectedRowsInsert = $GLOBALS['adodb']['db']->Affected_Rows();

            if ($insertResult === false || $affectedRowsInsert < 1) {
                error_log("OpenEMRHistoryPhysicalNoteManager CRITICAL: Failed to insert into form_history_physical with ID {$targetFormsId}. SQL Error: " . $GLOBALS['adodb']['db']->ErrorMsg() . " Affected Rows: " . $affectedRowsInsert);
                return false;
            }
            // error_log("OpenEMRHistoryPhysicalNoteManager: Insert into form_history_physical with ID {$targetFormsId} successful.");
        }

        $newFormName = "History and Physical (from Audio)";
        $updateFormsTableSql = "UPDATE forms SET form_name = ?, date = NOW(), deleted = 0, form_id = ? WHERE id = ?";
        if (sqlStatement($updateFormsTableSql, [$newFormName, $targetFormsId, $targetFormsId]) === false) {
            error_log("OpenEMRHistoryPhysicalNoteManager WARNING: Failed to update form_name in 'forms' table for ID {$targetFormsId}.");
        } else {
            // error_log("OpenEMRHistoryPhysicalNoteManager: Updated form_name to '{$newFormName}' in 'forms' table for ID {$targetFormsId}.");
        }

        $updateRawTranscriptSql = "UPDATE form_audio_to_note SET raw_transcript = ? WHERE id = ?";
        sqlStatement($updateRawTranscriptSql, [$raw_transcript, $this->formId]);

        return $targetFormsId;
    }
}
