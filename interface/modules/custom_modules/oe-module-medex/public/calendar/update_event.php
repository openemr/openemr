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

$authUserId = (int)($_SESSION['authUserID'] ?? 0);
$currentUser = $authUserId > 0
    ? sqlQuery("SELECT id, authorized FROM users WHERE id = ?", [$authUserId])
    : null;
$isAdminUser = AclMain::aclCheckCore('admin', 'super');
$isProviderUser = ((int)($_SESSION['userauthorized'] ?? ($currentUser['authorized'] ?? 0)) === 1);
$actorRole = $isAdminUser ? 'admin' : ($isProviderUser ? 'provider' : 'secretary');

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
    $existingEvent = sqlQuery(
        "SELECT
            pc_eid,
            pc_pid,
            pc_catid,
            pc_prefcatid,
            pc_aid,
            pc_facility,
            pc_eventstatus,
            pc_apptstatus
         FROM openemr_postcalendar_events
         WHERE pc_eid = ?",
        [$eventId]
    );

    if (empty($existingEvent)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Appointment not found']);
        exit;
    }

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

    $targetProviderId = $newProvider !== null ? (int)$newProvider : (int)($existingEvent['pc_aid'] ?? 0);
    $targetFacilityId = $newFacility !== null ? (int)$newFacility : (int)($existingEvent['pc_facility'] ?? 0);
    $movingPreferredCategoryId = (int)($existingEvent['pc_prefcatid'] ?? 0);
    $movingCategoryId = $movingPreferredCategoryId > 0 ? $movingPreferredCategoryId : (int)($existingEvent['pc_catid'] ?? 0);
    $isPatientAppointment = ((int)($existingEvent['pc_pid'] ?? 0) > 0);

    if ($actorRole === 'secretary') {
        if (!$isPatientAppointment || $movingCategoryId <= 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Secretary drag/drop is limited to category-typed patient appointments.',
                'errorCode' => 'secretary_drag_not_allowed'
            ]);
            exit;
        }

        // Secretary moves require a matching open slot category at the target location/time.
        $slotMatch = sqlQuery(
            "SELECT pc_eid
             FROM openemr_postcalendar_events
             WHERE pc_eventstatus = 1
               AND pc_pid = 0
               AND pc_aid = ?
               AND pc_facility = ?
               AND pc_eventDate = ?
               AND COALESCE(NULLIF(pc_prefcatid, 0), pc_catid) = ?
               AND TIME_TO_SEC(pc_startTime) < TIME_TO_SEC(?)
               AND (TIME_TO_SEC(pc_startTime) + COALESCE(NULLIF(pc_duration, 0), 900)) > TIME_TO_SEC(?)
             LIMIT 1",
            [$targetProviderId, $targetFacilityId, $newDate, $movingCategoryId, $newEndTime, $newStartTime]
        );

        if (empty($slotMatch)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Secretary drag/drop requires a matching open slot category at the destination.',
                'errorCode' => 'secretary_category_slot_required'
            ]);
            exit;
        }
    }

    $overlapBooked = sqlQuery(
        "SELECT pc_eid
         FROM openemr_postcalendar_events
         WHERE pc_eventstatus = 1
           AND pc_eid != ?
           AND pc_pid > 0
           AND pc_aid = ?
           AND pc_eventDate = ?
           AND UPPER(COALESCE(pc_apptstatus, '')) NOT IN ('X', '%')
           AND TIME_TO_SEC(pc_startTime) < TIME_TO_SEC(?)
           AND (TIME_TO_SEC(pc_startTime) + COALESCE(NULLIF(pc_duration, 0), 900)) > TIME_TO_SEC(?)
         LIMIT 1",
        [$eventId, $targetProviderId, $newDate, $newEndTime, $newStartTime]
    );

    $warnings = [];
    if (!empty($overlapBooked)) {
        if (!$isAdminUser && !$isProviderUser) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'This move would double-book the destination slot and is not allowed for this role.',
                'errorCode' => 'double_booking_not_allowed'
            ]);
            exit;
        }
        $warnings[] = 'Warning: Destination already has a booked appointment. Move saved as a double-booked slot.';
    }

    // Non-blocking advisory: staff may still schedule into a patient-held slot,
    // but should be informed a patient is actively considering it.
    $patientHold = null;
    try {
        $patientHold = sqlQuery(
            "SELECT slot_id, held_by_ref, hold_expires_at
             FROM medex_slot_registry
             WHERE slot_state = 'held_patient'
               AND provider_id = ?
               AND event_date = ?
               AND TIME_TO_SEC(start_time) < TIME_TO_SEC(?)
               AND (TIME_TO_SEC(start_time) + (COALESCE(NULLIF(duration_minutes, 0), 15) * 60)) > TIME_TO_SEC(?)
               AND (hold_expires_at IS NULL OR hold_expires_at > NOW())
             ORDER BY slot_id DESC
             LIMIT 1",
            [$targetProviderId, $newDate, $newEndTime, $newStartTime]
        );
    } catch (\Throwable $ignored) {
        $patientHold = null;
    }

    if (!empty($patientHold['slot_id'])) {
        $warnings[] = 'Warning: This slot is currently held for a patient offer. Booking now will invalidate that pending patient response.';
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
        'message' => 'Appointment updated successfully',
        'actorRole' => $actorRole,
        'warning' => !empty($warnings) ? implode("\n", $warnings) : null
    ]);

} catch (\Exception $e) {
    error_log('[MedEx Calendar] Error updating event: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error updating appointment: ' . $e->getMessage()
    ]);
}
