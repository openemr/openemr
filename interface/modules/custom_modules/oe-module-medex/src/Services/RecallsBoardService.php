<?php

namespace OpenEMR\Modules\MedEx\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class RecallsBoardService
{
    /**
     * Get Module Preferences for UI toggles
     */
    public function getPreferences()
    {
        // For now, assuming enabled. In real implementation, check Globals or Module Config.
        return [
            'show_postcards' => $GLOBALS['medex_postcards_enabled'] ?? true,
            'show_labels' => $GLOBALS['medex_labels_enabled'] ?? true
        ];
    }

    /**
     * Get list of facilities for filter dropdown
     */
    public function getFacilities()
    {
        $sql = "SELECT id, name FROM facility ORDER BY name";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * Get list of providers for filter dropdown
     */
    public function getProviders()
    {
        // authorized = 1 usually means they are providers/active users
        $sql = "SELECT id, fname, lname FROM users WHERE authorized = 1 ORDER BY lname, fname";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * Get recalls with MedEx campaign status merged in
     * Replaces the core get_recalls + getRecallsActions logic
     */
    public function getRecallsData($fromDate, $toDate, $facilityId = '', $providerId = '', $patientId = '', $patientName = '')
    {
        error_log("RecallsBoardService::getRecallsData inputs: range($fromDate to $toDate) filters(F:$facilityId P:$providerId PID:$patientId Name:$patientName)");

        // 1. Fetch Basic Recalls (Logic from Core DisplayService)
        $recallsTable = 'patient_recalls';
        $sql = "SELECT r.*, p.fname, p.lname, p.DOB, p.phone_cell, p.phone_home, p.email, p.pid
                FROM {$recallsTable} r
                JOIN patient_data p ON p.pid = r.r_pid
                WHERE r.r_eventDate >= ? AND r.r_eventDate <= ?
                AND IFNULL(p.deceased_date,0) = 0
                ORDER BY r.r_eventDate ASC";
        
        $recalls = QueryUtils::fetchRecords($sql, [$fromDate, $toDate]);
        
        error_log("RecallsBoardService query returned " . count($recalls) . " raw rows.");

        if (empty($recalls)) return [];

        // 2. Fetch MedEx Campaigns & Actions
        $processed = [];
        $modalityService = new ModalityService();

        foreach ($recalls as $recall) {
            // Filter Logic (Server-side is more efficient than the old JS show_this)
            if ($facilityId && $recall['r_facility'] != $facilityId) continue;
            if ($providerId && $recall['r_provider'] != $providerId) continue;
            if ($patientId && stripos($recall['r_pid'], $patientId) === false) continue;
            if ($patientName && (stripos($recall['fname'], $patientName) === false && stripos($recall['lname'], $patientName) === false)) continue;

            // Auto-delete check (Core feature)
            // TODO: Implement the 90-day cleanup logic if desired, or skip for "View Only" first
            
            // Enrich with Modality Permissions
            $recall['modalities'] = $modalityService->getPossibleModalities(['pid' => $recall['pid'], 'phone_cell'=>$recall['phone_cell'], 'phone_home'=>$recall['phone_home'], 'email'=>$recall['email']]);

            // Enrich with Campaign Status (The "Colors")
            $recall['campaign_status'] = $this->getCampaignStatus($recall['pid']);
            $recall['status_class'] = $this->getStatusClass($recall['campaign_status']);

            // Enrich with History/Actions
            $recall['history'] = $this->getHistory($recall['pid']);

            $processed[] = $recall;
        }

        return $processed;
    }

    public function getCampaignStatus($pid)
    {
        // Fetch latest status from EITHER automated campaign OR manual action
        // We prioritize the most recent event to determine row color
        $sql = "SELECT msg_type, msg_reply, msg_date 
                FROM (
                    SELECT msg_type, msg_reply, msg_date FROM medex_outgoing WHERE msg_pc_eid = ?
                    UNION
                    SELECT msg_type, msg_reply, msg_date FROM recall_board_actions WHERE msg_pc_eid = ?
                ) as combined_history 
                ORDER BY msg_date DESC LIMIT 1";
        
        $row = QueryUtils::fetchRecords($sql, ['recall_' . $pid, 'recall_' . $pid]);
        return $row[0] ?? null;
    }

    public function getStatusClass($status)
    {
        if (!$status) return 'whitish';
        
        $reply = strtoupper($status['msg_reply']);

        // Manual Actions
        if ($reply === 'CALL LOGGED') return 'yellowish';

        // Standard Statuses
        if (strpos($reply, 'READ') !== false) return 'greenish';
        if (strpos($reply, 'SENT') !== false) return 'yellowish';
        if (strpos($reply, 'FAILED') !== false) return 'reddish';
        
        return 'whitish';
    }

    public function getHistory($pid)
    {
        // Fetch actions + campaigns (merged)
        $sql = "SELECT msg_type, msg_date, msg_extra_text, msg_reply 
                FROM recall_board_actions 
                WHERE msg_pc_eid = ? 
                UNION 
                SELECT msg_type, msg_date, '' as msg_extra_text, msg_reply 
                FROM medex_outgoing 
                WHERE msg_pc_eid = ?
                ORDER BY msg_date DESC"; // Newest first
        
        return QueryUtils::fetchRecords($sql, ['recall_' . $pid, 'recall_' . $pid]);
    }

    /**
     * Process form submission (Save Changes)
     */
    public function processForm($postData)
    {
        $processed = 0;
        
        // 1. Handle Phone Calls
        if (!empty($postData['msg_phone'])) {
            foreach ($postData['msg_phone'] as $pid => $val) {
                // Checkbox value is usually 'on' or '1'
                if ($val) {
                    // Log Phone Call
                    $note = $postData['msg_notes'][$pid] ?? '';
                    $this->logAction($pid, 'PHONE', 'Call Logged', $note);
                    $processed++;
                }
            }
        }

        // 2. Handle Notes (Only if note exists and wasn't just part of a phone call)
        if (!empty($postData['msg_notes'])) {
            foreach ($postData['msg_notes'] as $pid => $note) {
                if (trim($note) === '') continue;
                
                // If phone was checked, we already saved the note attached to the call
                if (!empty($postData['msg_phone'][$pid])) continue;

                $this->logAction($pid, 'NOTES', 'Note Added', $note);
                $processed++;
            }
        }

        return $processed;
    }

    public function logAction($pid, $type, $reply, $extraText = '')
    {
       QueryUtils::sqlStatement(
           "INSERT INTO recall_board_actions (msg_pc_eid, msg_type, msg_reply, msg_extra_text, msg_date) VALUES (?, ?, ?, ?, NOW())",
           ['recall_' . $pid, $type, $reply, $extraText]
       );
    }

    /**
     * Create a new recall
     * @param array $data Recall data (pid, r_eventDate, r_reason, r_provider, r_facility)
     * @return int The new recall ID
     */
    public function saveRecall($data)
    {
        $recallsTable = 'patient_recalls';
        
        $sql = "INSERT INTO {$recallsTable} 
                (r_pid, r_eventDate, r_reason, r_provider, r_facility, r_created) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        QueryUtils::sqlStatement($sql, [
            $data['pid'],
            $data['r_eventDate'],
            $data['r_reason'] ?? '',
            $data['r_provider'],
            $data['r_facility']
        ]);
        
        // Return the new recall ID
        $result = QueryUtils::fetchRecords("SELECT LAST_INSERT_ID() as id");
        return $result[0]['id'] ?? null;
    }

    /**
     * Get patient data by PID
     * @param int $pid Patient ID
     * @return array|null Patient data
     */
    public function getPatientData($pid)
    {
        $sql = "SELECT pid, fname, lname, DOB, phone_cell, phone_home, email, 
                       TIMESTAMPDIFF(YEAR, DOB, CURDATE()) as age
                FROM patient_data 
                WHERE pid = ?";
        
        $result = QueryUtils::fetchRecords($sql, [$pid]);
        return $result[0] ?? null;
    }

    /**
     * Delete a recall
     * @param int $recallId Recall ID
     * @return bool Success
     */
    public function deleteRecall($recallId)
    {
        $recallsTable = 'patient_recalls';
        $sql = "DELETE FROM {$recallsTable} WHERE r_ID = ?";
        QueryUtils::sqlStatement($sql, [$recallId]);
        return true;
    }
}
