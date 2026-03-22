<?php

/**
 * Get calendar events from OpenEMR
 * Returns appointments in FullCalendar format
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Check calendar access
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    error_log('[MedEx Calendar] ERROR: User not authenticated');
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

error_log('[MedEx Calendar] Starting get_events.php for user ' . $_SESSION['authUserID']);

header('Content-Type: application/json');

// Get date range from query params
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
$end = $_GET['end'] ?? date('Y-m-d', strtotime('+2 months'));

error_log('[MedEx Calendar] Date range: ' . $start . ' to ' . $end);

// Get user's info
$userId = $_SESSION['authUserID'];
$userInfo = sqlQuery("SELECT id, facility_id, authorized FROM users WHERE id = ?", [$userId]);
$userProviderId = $userInfo['id'] ?? null;
$userFacilityId = $userInfo['facility_id'] ?? null;
$isAuthorized = $userInfo['authorized'] ?? 0;

// Get authorized providers/facilities from calendar_full subscription
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();
$subscriptions = $api->getSubscriptions();
$calendarSub = $subscriptions['calendar_full'] ?? null;

$authorizedProviders = [];
$authorizedFacilities = [];

if ($calendarSub) {
    $authorizedProviders = $calendarSub['providers'] ?? [];
    $authorizedFacilities = $calendarSub['facilities'] ?? [];
}

// If user specified providers in URL, use those (but only if they're authorized)
$providerFilter = $_GET['providers'] ?? '';
$requestedProviders = [];

if (!empty($providerFilter)) {
    $requestedProviders = explode(',', $providerFilter);
    // Only allow providers that are authorized
    if (!empty($authorizedProviders)) {
        $requestedProviders = array_intersect($requestedProviders, $authorizedProviders);
    }
} else {
    // Default: show current user's appointments only
    if ($userProviderId) {
        $requestedProviders = [$userProviderId];
    }
    // If current user is not a provider, show all authorized providers
    if (empty($requestedProviders) && !empty($authorizedProviders)) {
        $requestedProviders = $authorizedProviders;
    }
}

// If user specified facilities in URL, use those (but only if they're authorized)
$facilityFilter = $_GET['facilities'] ?? '';
$requestedFacilities = [];

if (!empty($facilityFilter)) {
    $requestedFacilities = explode(',', $facilityFilter);
    // Only allow facilities that are authorized
    if (!empty($authorizedFacilities)) {
        $requestedFacilities = array_intersect($requestedFacilities, $authorizedFacilities);
    }
} else {
    // Default: use all authorized facilities
    if (!empty($authorizedFacilities)) {
        $requestedFacilities = $authorizedFacilities;
    }
}

error_log('[MedEx Calendar] Subscription data: ' . json_encode($calendarSub));
error_log('[MedEx Calendar] Authorized providers from subscription: ' . json_encode($authorizedProviders));
error_log('[MedEx Calendar] Requested providers for SQL: ' . json_encode($requestedProviders));
error_log('[MedEx Calendar] Authorized facilities from subscription: ' . json_encode($authorizedFacilities));
error_log('[MedEx Calendar] Requested facilities for SQL: ' . json_encode($requestedFacilities));

// Build SQL query
$sql = "SELECT
    pc.pc_eid as id,
    pc.pc_title as title,
    pc.pc_eventDate as date,
    pc.pc_startTime as startTime,
    pc.pc_endTime as endTime,
    pc.pc_duration as duration,
    pc.pc_catid as category,
    pc.pc_aid as provider_id,
    pc.pc_pid as patient_id,
    pc.pc_apptstatus as status,
    pc.pc_facility as facility_id,
    p.fname as patient_fname,
    p.lname as patient_lname,
    u.fname as provider_fname,
    u.lname as provider_lname,
    c.pc_catname as category_name,
    c.pc_catcolor as category_color
FROM openemr_postcalendar_events pc
LEFT JOIN patient_data p ON pc.pc_pid = p.pid
LEFT JOIN users u ON pc.pc_aid = u.id
LEFT JOIN openemr_postcalendar_categories c ON pc.pc_catid = c.pc_catid
WHERE pc.pc_eventstatus = 1 AND pc.pc_eventDate >= ? AND pc.pc_eventDate <= ?";

$params = [$start, $end];

// Add provider filter (by username, not ID)
if (!empty($requestedProviders)) {
    $placeholders = implode(',', array_fill(0, count($requestedProviders), '?'));
    $sql .= " AND u.username IN ($placeholders)";
    $params = array_merge($params, $requestedProviders);
}

// Add facility filter
if (!empty($requestedFacilities)) {
    $facilityPlaceholders = implode(',', array_fill(0, count($requestedFacilities), '?'));
    $sql .= " AND pc.pc_facility IN ($facilityPlaceholders)";
    $params = array_merge($params, $requestedFacilities);
}

$sql .= " ORDER BY pc.pc_eventDate, pc.pc_startTime";

error_log('[MedEx Calendar] SQL: ' . $sql);
error_log('[MedEx Calendar] SQL params: ' . json_encode($params));

try {
    $result = sqlStatement($sql, $params);
    error_log('[MedEx Calendar] SQL executed successfully');

    $rows = [];
    while ($row = sqlFetchArray($result)) {
        $rows[] = $row;
    }

    $events = [];
    if (empty($rows)) {
        error_log('[MedEx Calendar] Returning 0 events');
        echo json_encode($events);
        exit;
    }

    // Load MedEx icons map once
    $icons = [];
    try {
        $iconRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords("SELECT * FROM medex_icons");
        foreach ($iconRows as $iconRow) {
            $icons[$iconRow['msg_type']][$iconRow['msg_status']] = $iconRow['i_html'];
        }
    } catch (\Exception $e) {
        // If medex_icons table is missing, just skip icons
        $icons = [];
        error_log('[MedEx Calendar] medex_icons table not found: ' . $e->getMessage());
    }

    // Fetch MedEx outgoing rows for all events in this range
    $eventIds = array_map(static fn($r) => $r['id'], $rows);
    $medexByEid = [];
    if (!empty($eventIds)) {
        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
        $medexRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
            "SELECT msg_pc_eid, msg_type, msg_reply, msg_date, msg_pid, msg_extra_text
             FROM medex_outgoing
             WHERE msg_pc_eid IN ($placeholders)
             ORDER BY msg_pc_eid, msg_date ASC",
            $eventIds
        );
        foreach ($medexRows as $medexRow) {
            $medexByEid[$medexRow['msg_pc_eid']][] = $medexRow;
        }
    }

    $buildMedexIcons = static function (array $medexRows, array $icons): string {
        if (empty($medexRows) || empty($icons)) {
            return '';
        }

        $icon_here = [];
        $icon2_here = '';
        $appointment = [];

        foreach ($medexRows as $row) {
            if ($row['msg_reply'] === 'Other') {
                $icon2_here .= $icons[$row['msg_type']]['Other'] ?? '';
                continue;
            }

            if ($row['msg_reply'] === 'CANCELLED') {
                $appointment[$row['msg_type']]['stage'] = 'CANCELLED';
                $icon_here[$row['msg_type']] = '';
            } elseif ($row['msg_reply'] === 'FAILED') {
                $appointment[$row['msg_type']]['stage'] = 'FAILED';
                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED'] ?? '';
            } elseif ($row['msg_reply'] === 'CONFIRMED' || (($appointment[$row['msg_type']]['stage'] ?? '') === 'CONFIRMED')) {
                $appointment[$row['msg_type']]['stage'] = 'CONFIRMED';
                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['CONFIRMED'] ?? '';
            } elseif ($row['msg_reply'] === 'READ' || (($appointment[$row['msg_type']]['stage'] ?? '') === 'READ')) {
                $appointment[$row['msg_type']]['stage'] = 'READ';
                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ'] ?? '';
            } elseif ($row['msg_reply'] === 'SENT' || (($appointment[$row['msg_type']]['stage'] ?? '') === 'SENT')) {
                $appointment[$row['msg_type']]['stage'] = 'SENT';
                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT'] ?? '';
            } elseif ($row['msg_reply'] === 'To Send' || empty($appointment[$row['msg_type']]['stage'] ?? '')) {
                if (!in_array(($appointment[$row['msg_type']]['stage'] ?? ''), ['CONFIRMED', 'READ', 'SENT', 'FAILED'], true)) {
                    $appointment[$row['msg_type']]['stage'] = 'QUEUED';
                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED'] ?? '';
                }
            }

            if ($row['msg_reply'] === 'CALL') {
                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['CALL'] ?? '';
            } elseif ($row['msg_reply'] === 'STOP') {
                $icon2_here .= $icons[$row['msg_type']]['STOP'] ?? '';
            }
        }

        return implode('', $icon_here) . $icon2_here;
    };

    foreach ($rows as $row) {
        $needsUpdate = false;

        // pc_duration is stored in SECONDS by OpenEMR (add_edit_event.php saves $duration*60,
        // reads it back as round($row['pc_duration']/60)). Treat it as seconds throughout.
        $durationSeconds = (int)$row['duration'];

        // Fallback: compute from pc_startTime + pc_endTime if pc_duration is missing.
        // Only use pc_endTime when pc_duration is absent; pc_endTime is often 00:00:00
        // even for valid appointments, so it is not a reliable primary source.
        if ($durationSeconds <= 0 && !empty($row['startTime']) && !empty($row['endTime'])) {
            $sTs = strtotime($row['startTime']);
            $eTs = strtotime($row['endTime']);
            if ($eTs > $sTs) {
                $durationSeconds = $eTs - $sTs; // already seconds
                $needsUpdate = true;
            }
        }

        // Default to the OpenEMR calendar slot interval (calendar_interval global, in minutes)
        if ($durationSeconds <= 0) {
            $slotMinutes = (int)($GLOBALS['calendar_interval'] ?? 15);
            if ($slotMinutes <= 0) {
                $slotMinutes = 15;
            }
            $durationSeconds = $slotMinutes * 60;
            $needsUpdate = true;
        }

        // Persist corrected duration back to DB if it was missing/zero
        if ($needsUpdate) {
            sqlStatement(
                "UPDATE openemr_postcalendar_events SET pc_duration = ? WHERE pc_eid = ?",
                [$durationSeconds, $row['id']]
            );
        }

        // Keep $calculatedDuration as minutes for backward-compat with any remaining uses below
        $calculatedDuration = (int)round($durationSeconds / 60);

        // Build event start/end datetime
        // NOTE: pc_endTime is unreliable in OpenEMR (frequently stored as 00:00:00).
        // OpenEMR itself uses pc_duration as the canonical source. Always derive end
        // from start + duration so FullCalendar blocks and the duration passed to
        // edit_event_wrapper.php are both correct.
        $startDateTime = $row['date'] . ' ' . $row['startTime'];
        $startTs = strtotime($startDateTime);
        $endTs = $startTs + $durationSeconds; // $durationSeconds already in seconds
        $endDateTime = date('Y-m-d H:i:s', $endTs);

        // Build event title
        $patientName = '';
        if (!empty($row['patient_fname']) || !empty($row['patient_lname'])) {
            $patientName = trim($row['patient_fname'] . ' ' . $row['patient_lname']);
        }

        $providerName = '';
        if (!empty($row['provider_fname']) || !empty($row['provider_lname'])) {
            $providerName = trim($row['provider_fname'] . ' ' . $row['provider_lname']);
        }

        $title = $row['title'];
        if ($patientName) {
            $title = $patientName . ' - ' . $title;
        }

        // Determine event color based on status and category
        $color = $row['category_color'] ?? '#3788d8';

        // Adjust color based on status
        switch ($row['status']) {
            case 'x': // No-show
                $color = '#ff0000';
                break;
            case '@': // Cancelled
                $color = '#999999';
                break;
            case '~': // Completed
                $color = '#00cc00';
                break;
        }

        $medexRowsForEvent = $medexByEid[$row['id']] ?? [];
        $statusIconHtml = $buildMedexIcons($medexRowsForEvent, $icons);
        $medexDebug = null;
        if ($patientName === 'Phil Beldfors' && $row['date'] === '2026-02-16' && str_starts_with((string)$row['startTime'], '08:30')) {
            $medexDebug = [
                'pc_eid' => $row['id'],
                'pc_apptstatus' => $row['status'],
                'medex_rows_count' => count($medexRowsForEvent),
                'medex_rows' => array_map(static function ($r) {
                    return [
                        'msg_type' => $r['msg_type'] ?? null,
                        'msg_reply' => $r['msg_reply'] ?? null,
                        'msg_date' => $r['msg_date'] ?? null,
                    ];
                }, $medexRowsForEvent),
                'status_icon_empty' => $statusIconHtml === ''
            ];
        }

        $events[] = [
            'id' => $row['id'],
            'title' => $title,
            'start' => $startDateTime,
            'end' => $endDateTime,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'patientId' => $row['patient_id'],
                'patientName' => $patientName,
                'providerId' => $row['provider_id'],
                'providerName' => $providerName,
                'category' => $row['category_name'],
                'status' => $row['status'],
                'statusIcon' => $statusIconHtml,
                'medexDebug' => $medexDebug,
                'facilityId' => $row['facility_id']
            ]
        ];
    }

    error_log('[MedEx Calendar] Returning ' . count($events) . ' events');
    echo json_encode($events);

} catch (\Exception $e) {
    error_log('[MedEx Calendar] Error fetching events: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching appointments']);
}
