<?php
/**
 * AJAX Handler for MedEx Recall Board
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . "/../src/Services/RecallsBoardService.php");

use OpenEMR\Modules\MedEx\Services\RecallsBoardService;

// Ensure authenticated
if (!isset($_SESSION['authUser'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$service = new RecallsBoardService();
$pid = $_POST['pid'] ?? null;
$action = $_POST['action'] ?? null;

if (!$pid || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing PID or Action']);
    exit;
}

$response = ['success' => false];

if ($action === 'log_phone') {
    // Determine state. If $_POST['active'] is 1, we are LOGGING. If 0, we can't really 'unlog' an action easily in SQL history 
    // without deleting, but the prompt implies simple "Phone call, turns red on click". 
    // The previous logic was "toggle". 
    // However, if we click it again after saving, does it delete the log? 
    // The user said "Phone call, turns red on click, focus to notes... save clicked -> fails".
    // Let's assume the button logs a NEW "Call Logged" event. Toggle OFF usually just means "Oops, I didn't mean to click that" locally.
    // But if we use AJAX, every click is an action? No.
    // Let's stick to: Click -> Logs "Call Logged".
    
    // User logic: "Phone call, turns red on click, focus to notes, note added, save clicked"
    // With AJAX: Click Phone -> Red -> Wait for Note? No, user says "Lose focus ... and submit".
    // Maybe the Phone Click IS the trigger?
    // "Phone call, turns red on click" -> This might just be UI state. 
    // "save clicked -> fails" -> Old behavior.
    
    // Proposed AJAX Behavior:
    // 1. Click Phone Icon -> Toggle Red. If Red -> AJAX Log "Call Logged". (With empty note initially).
    // 2. Type Note -> Blur -> AJAX Log "Note Added" OR Update the previous "Call Logged" entry?
    // The table structure is `recall_board_actions`. It's an INSERT only log usually.
    // If I log "Call Logged" immediately, then type a note, I might have two entries? 
    // "Call Logged" (time X)
    // "Note Added" (time X+10s)
    
    // Better:
    // Click Phone -> Just UI Toggle (Don't save yet?). 
    // User said: "Why not use the ajax calls... specific recall... Lose focus of the field and submit."
    // This implies the NOTE field blur triggers the save.
    // What if I just click Phone and don't add a note? 
    // Maybe the Phone Button click SHOULD trigger an AJAX log immediately?
    
    // Let's support both.
    // 1. toggle_phone: Logs "Call Logged".
    // 2. save_note: Logs "Note Added" (or appends to last call if recent? No, keep it simple).
    
    $note = $_POST['note'] ?? '';
    // If we are un-toggling (active=0), maybe we don't do anything? Or we delete the last log?
    // Let's assume Click = Log.
    $shouldLog = $_POST['active'] === '1' || $_POST['active'] === 'true';
    
    if ($shouldLog) {
        $service->logAction($pid, 'PHONE', 'Call Logged', $note);
        $response['success'] = true;
    }
}
elseif ($action === 'save_note') {
    $note = $_POST['note'] ?? '';
    if (trim($note) !== '') {
        $service->logAction($pid, 'NOTES', 'Note Added', $note);
        $response['success'] = true;
    }
}
elseif ($action === 'get_row_data') {
    // Just refresh data
    $response['success'] = true;
}

// Prepare fresh row data for UI updates (Status Color, History HTML)
if ($response['success']) {
    // Get updated status
    $status = $service->getCampaignStatus($pid);
    $response['status_class'] = $service->getStatusClass($status);
    
    // Get updated history HTML
    $history = $service->getHistory($pid);
    $html = "";
    foreach ($history as $h) {
        $color = '#333';
        if(stripos($h['msg_reply'], 'READ') !== false) $color = 'green';
        if(stripos($h['msg_reply'], 'FAILED') !== false) $color = 'red';
        if(stripos($h['msg_reply'], 'Start') !== false) $color = 'blue';

        // Format dates nicely
        $dateStr = oeFormatShortDate($h['msg_date']);
        
        $html .= '<div style="margin-bottom:2px;">';
        $html .= '<b>' . text($h['msg_type']) . ':</b> <span style="color:'.$color.'">' . text($h['msg_reply']) . '</span>';
        if (!empty($h['msg_extra_text'])) {
            $html .= '<br><span style="font-size:0.9em;color:#555;margin-left:5px;">&rdsh; ' . text($h['msg_extra_text']) . '</span>';
        }
        $html .= ' <span style="color:#888;font-size:0.85em;">(' . text($dateStr) . ')</span>';
        $html .= '</div>';
    }
    $response['history_html'] = $html;
}

header('Content-Type: application/json');
echo json_encode($response);
