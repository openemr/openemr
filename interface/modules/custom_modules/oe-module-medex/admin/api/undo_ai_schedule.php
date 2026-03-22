<?php
/**
 * MedEx AI Schedule Undo
 * Removes all calendar events created by a specific AI batch.
 * Only allows undo within 24 hours of batch creation.
 * Events that have been modified (patient booked, status changed) are skipped.
 */

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../../../../../library/sql.inc.php');

header('Content-Type: application/json');

// Security Check
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Restore session for AJAX
if (function_exists('restoreSession')) {
    restoreSession();
}

$input = json_decode(file_get_contents('php://input'), true);
$batchId = $input['batch_id'] ?? '';

if (empty($batchId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing batch_id']);
    exit;
}

// 1. Verify batch exists and is within the 24-hour undo window
$batch = sqlQuery(
    "SELECT batch_id, MIN(created_at) as created_at, undone_at, COUNT(*) as total_events
     FROM medex_ai_batches 
     WHERE batch_id = ? 
     GROUP BY batch_id, undone_at",
    [$batchId]
);

if (empty($batch)) {
    echo json_encode(['error' => 'Batch not found: ' . $batchId]);
    exit;
}

if (!empty($batch['undone_at'])) {
    echo json_encode(['error' => 'Batch was already undone on ' . $batch['undone_at']]);
    exit;
}

// Check 24-hour window
$createdTime = strtotime($batch['created_at']);
$hoursElapsed = (time() - $createdTime) / 3600;
if ($hoursElapsed > 24) {
    echo json_encode([
        'error' => 'Undo window expired. Batch was created ' . round($hoursElapsed, 1) . ' hours ago (limit: 24 hours).'
    ]);
    exit;
}

// 2. Get all event IDs for this batch
$events = sqlStatement(
    "SELECT b.id, b.pc_eid, b.event_date, b.provider_id 
     FROM medex_ai_batches b
     WHERE b.batch_id = ? AND b.undone_at IS NULL",
    [$batchId]
);

$deleted = 0;
$skipped = 0;
$skippedReasons = [];

while ($row = sqlFetchArray($events)) {
    $eid = $row['pc_eid'];
    
    // 3. Safety check: only delete if the event is still an unbooked AI slot
    // If a real patient has been booked into this slot, skip it
    $event = sqlQuery(
        "SELECT pc_eid, pc_pid, pc_apptstatus, pc_title 
         FROM openemr_postcalendar_events 
         WHERE pc_eid = ?",
        [$eid]
    );
    
    if (empty($event)) {
        // Already deleted somehow
        $skipped++;
        $skippedReasons[] = "EID $eid: already removed";
        continue;
    }
    
    // Skip if a real patient has been booked (pc_pid > 0 means a patient is assigned)
    if (!empty($event['pc_pid']) && (int)$event['pc_pid'] > 0) {
        $skipped++;
        $skippedReasons[] = "EID $eid on {$row['event_date']}: patient booked (pid={$event['pc_pid']})";
        continue;
    }
    
    // Skip if status has changed from the default '-' (means someone touched it)
    if ($event['pc_apptstatus'] !== '-' && $event['pc_apptstatus'] !== 'x') {
        $skipped++;
        $skippedReasons[] = "EID $eid on {$row['event_date']}: status changed to '{$event['pc_apptstatus']}'";
        continue;
    }
    
    // 4. Safe to delete
    sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eid]);
    $deleted++;
}

// 5. Mark the batch as undone
sqlStatement(
    "UPDATE medex_ai_batches SET undone_at = NOW(), undone_by = ? WHERE batch_id = ?",
    [$_SESSION['authUserID'], $batchId]
);

echo json_encode([
    'success' => true,
    'batch_id' => $batchId,
    'deleted' => $deleted,
    'skipped' => $skipped,
    'skipped_reasons' => $skippedReasons,
    'message' => "$deleted events removed, $skipped events skipped (already booked or modified)."
]);
exit;
