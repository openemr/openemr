<?php

/**
 * Update calendar event (appointment)
 * Handles drag-and-drop and resize operations
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Check calendar access - need write permission
if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

if (empty($input['eid'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Event ID required']);
    exit;
}

$eventId = $input['eid'];
$newStart = $input['start'] ?? null;
$newEnd   = $input['end']   ?? null;
$newProvider = $input['provider'] ?? null;
$newFacility = $input['facility'] ?? null;

$eventRow = sqlQuery(
    "SELECT pc_pid, pc_title, pc_catid, pc_prefcatid, pc_apptstatus, pc_aid
     FROM openemr_postcalendar_events WHERE pc_eid = ? LIMIT 1",
    [$eventId]
);
if (empty($eventRow)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Event not found']);
    exit;
}

$eventPatientId = (int)($eventRow['pc_pid'] ?? 0);
if ($eventPatientId <= 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'error'   => 'Template/open slots cannot be moved from Full Calendar. Use Dashboard template tools.'
    ]);
    exit;
}

if (!$newStart) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Start time required']);
    exit;
}

// Read the practice scheduling rules so we respect guideline vs strict mode.
$srRow = sqlQuery(
    "SELECT gl_value FROM globals WHERE gl_name = 'medex_scheduling_rules' LIMIT 1"
);
$schedulingRules  = json_decode((string)($srRow['gl_value'] ?? ''), true);
$templateEnforce  = (string)($schedulingRules['template_enforcement'] ?? 'guideline');
$enforceStrictly  = ($templateEnforce === 'strict');

try {
    $startDateTime = new DateTime($newStart);
    $newDate       = $startDateTime->format('Y-m-d');
    $newStartTime  = $startDateTime->format('H:i:s');

    if ($newEnd) {
        $endDateTime = new DateTime($newEnd);
        $newEndTime  = $endDateTime->format('H:i:s');
        $duration    = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
    } else {
        $existing    = sqlQuery("SELECT pc_duration, pc_endTime FROM openemr_postcalendar_events WHERE pc_eid = ?", [$eventId]);
        $duration    = $existing['pc_duration'] ?? 1800;
        $newEndTime  = date('H:i:s', strtotime($newStartTime) + $duration);
    }

    // Category-match guardrail — only enforced in strict mode.
    // In guideline mode the staff can freely move appointments regardless of slot type.
    if ($enforceStrictly) {
        $effectiveProviderId = (int)($newProvider !== null ? $newProvider : ($eventRow['pc_aid'] ?? 0));
        $movingCategoryId    = (int)($eventRow['pc_prefcatid'] ?: $eventRow['pc_catid']);
        $moveStartSec        = strtotime($newStartTime) ?: 0;
        $moveEndSec          = strtotime($newEndTime)   ?: 0;
        if ($moveEndSec <= $moveStartSec) {
            $moveEndSec = $moveStartSec + max(60, (int)$duration);
        }

        if ($effectiveProviderId > 0 && $moveStartSec > 0 && $movingCategoryId > 0) {
            $slotRows = sqlStatement(
                "SELECT pc_eid, pc_prefcatid, pc_startTime, pc_endTime, pc_duration
                 FROM openemr_postcalendar_events
                 WHERE pc_aid = ?
                   AND pc_eventDate = ?
                   AND pc_eid != ?
                   AND (COALESCE(pc_pid, '') = '' OR pc_pid = '0')
                   AND COALESCE(pc_prefcatid, 0) > 0
                   AND (
                       pc_title LIKE 'Open Slot%'
                    OR pc_title LIKE 'In Office%'
                    OR pc_catid IN (2,3)
                    OR COALESCE(pc_location, '') LIKE 'MEDEX_%'
                   )",
                [$effectiveProviderId, $newDate, $eventId]
            );

            while ($slotRow = sqlFetchArray($slotRows)) {
                $slotCategoryId = (int)($slotRow['pc_prefcatid'] ?? 0);
                if ($slotCategoryId <= 0 || $slotCategoryId === $movingCategoryId) {
                    continue;
                }
                $slotStartSec = strtotime((string)$slotRow['pc_startTime']) ?: 0;
                $slotEndSec   = strtotime((string)$slotRow['pc_endTime'])   ?: 0;
                if ($slotEndSec <= $slotStartSec) {
                    $slotDur = (int)($slotRow['pc_duration'] ?? 0);
                    if ($slotDur <= 0) { continue; }
                    $slotEndSec = $slotStartSec + $slotDur;
                }
                if (!($moveStartSec < $slotEndSec && $moveEndSec > $slotStartSec)) {
                    continue;
                }
                $movingCat = sqlQuery("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$movingCategoryId]);
                $slotCat   = sqlQuery("SELECT pc_catname FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$slotCategoryId]);
                $movingName = trim((string)($movingCat['pc_catname'] ?? ('Category #' . $movingCategoryId)));
                $slotName   = trim((string)($slotCat['pc_catname']  ?? ('Category #' . $slotCategoryId)));
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Cannot move this appointment into that slot. '
                               . 'Appointment type ' . $movingName . ' does not match slot type ' . $slotName . '. '
                               . '(Templates are strictly enforced.)'
                ]);
                exit;
            }
        }
    }

    $updateFields = [
        'pc_eventDate' => $newDate,
        'pc_startTime' => $newStartTime,
        'pc_endTime'   => $newEndTime,
        'pc_duration'  => $duration,
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
        $params[]     = $value;
    }
    $params[] = $eventId;

    sqlStatement(
        "UPDATE openemr_postcalendar_events SET " . implode(', ', $setClauses) . " WHERE pc_eid = ?",
        $params
    );

    echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);

} catch (\Exception $e) {
    error_log('[MedEx Calendar] Error updating event: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error updating appointment: ' . $e->getMessage()]);
}
