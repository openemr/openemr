<?php
/**
 * MedEx AI Batch History
 * Returns a list of AI schedule batches with their status (active/undone/expired).
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

// Get batch summaries
$sql = "SELECT 
            b.batch_id,
            COUNT(b.pc_eid) as total_events,
            MIN(b.event_date) as first_date,
            MAX(b.event_date) as last_date,
            MIN(b.created_at) as created_at,
            b.created_by,
            b.undone_at,
            b.undone_by,
            GROUP_CONCAT(DISTINCT b.provider_id) as provider_ids,
            GROUP_CONCAT(DISTINCT b.facility_id) as facility_ids,
            u.fname as creator_fname,
            u.lname as creator_lname
        FROM medex_ai_batches b
        LEFT JOIN users u ON u.id = b.created_by
        GROUP BY b.batch_id, b.created_by, b.undone_at, b.undone_by, u.fname, u.lname
        ORDER BY MIN(b.created_at) DESC
        LIMIT 50";

$result = sqlStatement($sql);
$batches = [];

while ($row = sqlFetchArray($result)) {
    $createdTime = strtotime($row['created_at']);
    $hoursElapsed = (time() - $createdTime) / 3600;
    
    // Determine status
    if (!empty($row['undone_at'])) {
        $status = 'undone';
    } elseif ($hoursElapsed > 24) {
        $status = 'locked'; // Past undo window
    } else {
        $status = 'active'; // Can still be undone
    }
    
    // Count how many events are still on the calendar (not deleted externally)
    $liveCount = sqlQuery(
        "SELECT COUNT(*) as cnt FROM medex_ai_batches ab 
         INNER JOIN openemr_postcalendar_events e ON e.pc_eid = ab.pc_eid
         WHERE ab.batch_id = ? AND ab.undone_at IS NULL",
        [$row['batch_id']]
    );
    
    $batches[] = [
        'batch_id' => $row['batch_id'],
        'total_events' => (int)$row['total_events'],
        'live_events' => (int)($liveCount['cnt'] ?? 0),
        'first_date' => $row['first_date'],
        'last_date' => $row['last_date'],
        'created_at' => $row['created_at'],
        'created_by' => trim(($row['creator_fname'] ?? '') . ' ' . ($row['creator_lname'] ?? '')),
        'undone_at' => $row['undone_at'],
        'status' => $status,
        'hours_remaining' => $status === 'active' ? round(24 - $hoursElapsed, 1) : 0,
        'provider_ids' => $row['provider_ids'],
        'facility_ids' => $row['facility_ids']
    ];
}

echo json_encode([
    'success' => true,
    'batches' => $batches
]);
exit;
