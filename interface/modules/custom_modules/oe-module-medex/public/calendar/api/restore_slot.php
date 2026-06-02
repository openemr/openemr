<?php
/**
 * Restore a template Open Slot when its linked patient appointment is deleted.
 *
 * Called fire-and-forget by edit_event_wrapper.php when the booking dialog
 * closes (which happens on both save AND delete). Checks whether the
 * appointment still exists; if it was deleted it recreates the Open Slot
 * from the medex_slot_registry metadata and resets the registry state.
 */

require_once(__DIR__ . "/../../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if (empty($_SESSION['authUserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$appointmentEid = (int)($input['appointment_eid'] ?? 0);

if ($appointmentEid <= 0) {
    echo json_encode(['success' => false, 'error' => 'No appointment_eid']);
    exit;
}

// Check whether the appointment still exists.
$apptRow = sqlQuery(
    "SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_eid = ? LIMIT 1",
    [$appointmentEid]
);

if (!empty($apptRow['pc_eid'])) {
    // Appointment still exists — nothing to restore.
    echo json_encode(['success' => true, 'restored' => false, 'reason' => 'appointment_exists']);
    exit;
}

// Appointment was deleted.  Find registry entries that reference it as the patient_pc_eid.
$tableExists = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
if (empty($tableExists)) {
    echo json_encode(['success' => true, 'restored' => false, 'reason' => 'no_registry_table']);
    exit;
}

$regRow = sqlQuery(
    "SELECT slot_id, open_slot_eid, provider_id, event_date, start_time, end_time,
            category_id, slot_state
     FROM medex_slot_registry
     WHERE patient_pc_eid = ?
     ORDER BY slot_id DESC LIMIT 1",
    [$appointmentEid]
);

// Also check pending_consumption entries that match the session's pending slot
// (for cases where AppointmentSlotListener never linked the patient_pc_eid).
if (empty($regRow['slot_id'])) {
    $pending = $_SESSION['medex_pending_slot_consumption'] ?? null;
    if (!empty($pending['open_slot_eid'])) {
        $regRow = sqlQuery(
            "SELECT slot_id, open_slot_eid, provider_id, event_date, start_time, end_time,
                    category_id, slot_state
             FROM medex_slot_registry
             WHERE open_slot_eid = ?
             ORDER BY slot_id DESC LIMIT 1",
            [(int)$pending['open_slot_eid']]
        );
    }
}

if (empty($regRow['slot_id'])) {
    echo json_encode(['success' => true, 'restored' => false, 'reason' => 'no_registry_entry']);
    exit;
}

$providerId  = (int)($regRow['provider_id'] ?? 0);
$eventDate   = (string)($regRow['event_date']  ?? '');
$startTime   = (string)($regRow['start_time']  ?? '');
$endTime     = (string)($regRow['end_time']    ?? '');
$categoryId  = (int)($regRow['category_id']   ?? 0);

if ($providerId <= 0 || $eventDate === '' || $startTime === '') {
    echo json_encode(['success' => false, 'error' => 'Incomplete registry data']);
    exit;
}

// Verify the Open Slot hasn't already been restored by another process.
$existingSlot = sqlQuery(
    "SELECT pc_eid FROM openemr_postcalendar_events
     WHERE pc_aid = ? AND pc_eventDate = ? AND pc_startTime = ?
       AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')
       AND pc_title LIKE 'Open Slot%'
     LIMIT 1",
    [$providerId, $eventDate, $startTime]
);

if (!empty($existingSlot['pc_eid'])) {
    // Slot already exists (e.g. re-deployed template).  Just reset registry state.
    sqlStatement(
        "UPDATE medex_slot_registry
         SET slot_state = 'available', patient_pc_eid = NULL, consumed_at = NULL
         WHERE slot_id = ?",
        [(int)$regRow['slot_id']]
    );
    echo json_encode(['success' => true, 'restored' => false, 'reason' => 'slot_already_exists']);
    exit;
}

// Resolve category name for the slot title.
$catRow = sqlQuery(
    "SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1",
    [$categoryId]
);
$catName = trim((string)($catRow['pc_catname'] ?? ''));
if ($catName === '') {
    $catName = 'Open Slot';
}

$hasLocation = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_location'"));
$hasFacility = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_facility'"));

$durationSecs = 0;
if ($endTime !== '' && strtotime($endTime) > strtotime($startTime)) {
    $durationSecs = strtotime($endTime) - strtotime($startTime);
}
if ($durationSecs <= 0) {
    $durationSecs = 900; // 15-min fallback
}

// Recreate the Open Slot.
if ($hasLocation && $hasFacility) {
    sqlInsert(
        "INSERT INTO openemr_postcalendar_events
         (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext,
          pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
          pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
         VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, 'MEDEX_STUDIO', 0)",
        [$categoryId, $providerId, 'Open Slot - ' . $catName,
         $eventDate, $eventDate, $durationSecs, $startTime, $endTime, $categoryId]
    );
} elseif ($hasLocation) {
    sqlInsert(
        "INSERT INTO openemr_postcalendar_events
         (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext,
          pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
          pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
         VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, 'MEDEX_STUDIO')",
        [$categoryId, $providerId, 'Open Slot - ' . $catName,
         $eventDate, $eventDate, $durationSecs, $startTime, $endTime, $categoryId]
    );
} else {
    sqlInsert(
        "INSERT INTO openemr_postcalendar_events
         (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext,
          pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
          pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
         VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?)",
        [$categoryId, $providerId, 'Open Slot - ' . $catName,
         $eventDate, $eventDate, $durationSecs, $startTime, $endTime, $categoryId]
    );
}

// Reset registry entry so it's available again.
sqlStatement(
    "UPDATE medex_slot_registry
     SET slot_state = 'available', patient_pc_eid = NULL, consumed_at = NULL
     WHERE slot_id = ?",
    [(int)$regRow['slot_id']]
);

// Clear any pending session data for this slot.
if (!empty($_SESSION['medex_pending_slot_consumption']['open_slot_eid']) &&
    (int)$_SESSION['medex_pending_slot_consumption']['open_slot_eid'] === (int)($regRow['open_slot_eid'] ?? 0)) {
    unset($_SESSION['medex_pending_slot_consumption']);
}

error_log('[MedEx] Restored Open Slot for provider ' . $providerId
    . ' on ' . $eventDate . ' at ' . $startTime . ' (deleted appt eid=' . $appointmentEid . ')');

echo json_encode([
    'success'  => true,
    'restored' => true,
    'provider' => $providerId,
    'date'     => $eventDate,
    'time'     => $startTime,
    'category' => $catName,
]);
