<?php

/**
 * Update calendar event (appointment)
 * Handles drag-and-drop and resize operations
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Check calendar access - need write permission
if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

// Get input - support both JSON and form-encoded data
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    // Form-encoded data (from grid view drag-and-drop)
    $input = $_POST;
}

if (empty($input['eid'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Event ID required']);
    exit;
}

$eventId = $input['eid'];
$newStart = $input['start'] ?? null;
$newEnd = $input['end'] ?? null;
$newProvider = $input['provider'] ?? null;
$newFacility = $input['facility'] ?? null;

if (!$newStart) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Start time required']);
    exit;
}

try {
    // Parse new start/end times from ISO format
    // FullCalendar sends times in browser timezone, need to convert to server timezone
    $startDateTime = new DateTime($newStart);

    // Get the timezone from OpenEMR globals or use system default
    $timezone = $GLOBALS['gbl_time_zone'] ?? date_default_timezone_get();
    if (empty($timezone)) {
        // If no timezone set, try to detect from system
        $timezone = date_default_timezone_get();
    }

    // OpenEMR stores dates/times without timezone info in local time
    // So we just extract the date and time components from the DateTime object
    $newDate = $startDateTime->format('Y-m-d');
    $newStartTime = $startDateTime->format('H:i:s');

    error_log('[MedEx Calendar] Update - Original start: ' . $newStart);
    error_log('[MedEx Calendar] Update - Converted date: ' . $newDate . ' time: ' . $newStartTime);

    $newEndTime = null;
    if ($newEnd) {
        $endDateTime = new DateTime($newEnd);
        $newEndTime = $endDateTime->format('H:i:s');

        // Calculate duration in SECONDS — pc_duration is stored in seconds
        // (OpenEMR's add_edit_event.php saves $minutes*60; reads back with /60)
        $duration = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
    } else {
        // Get existing duration (already in seconds) and recompute endTime from new start
        $existing = sqlQuery("SELECT pc_duration, pc_endTime FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eventId]);
        $duration = $existing['pc_duration'] ?? 1800; // fallback: 30 minutes in seconds
        $newEndTime = date('H:i:s', strtotime($newStartTime) + $duration);
    }

    // Build update query - include provider and facility if provided
    $updateFields = [
        'pc_eventDate' => $newDate,
        'pc_startTime' => $newStartTime,
        'pc_endTime' => $newEndTime,
        'pc_duration' => $duration
    ];

    $params = [];
    if ($newProvider !== null) {
        $updateFields['pc_aid'] = $newProvider;
    }
    if ($newFacility !== null) {
        $updateFields['pc_facility'] = $newFacility;
    }

    $setClauses = [];
    foreach ($updateFields as $field => $value) {
        $setClauses[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $eventId; // For WHERE clause

    $sql = "UPDATE openemr_postcalendar_events SET " . implode(', ', $setClauses) . " WHERE pc_eid = ?";

    error_log('[MedEx Calendar] Update SQL: ' . $sql);
    error_log('[MedEx Calendar] Update params: ' . json_encode($params));

    sqlStatement($sql, $params);

    echo json_encode([
        'success' => true,
        'message' => 'Appointment updated successfully'
    ]);

} catch (\Exception $e) {
    error_log('[MedEx Calendar] Error updating event: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error updating appointment: ' . $e->getMessage()
    ]);
}
