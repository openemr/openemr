<?php

/**
 * Get calendar events from OpenEMR
 * Returns appointments in FullCalendar format
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . '/../../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;

// Check calendar access
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Verify user is authenticated (support both native session and wrapper-backed sessions).
$sessionWrapper = null;
try {
    $sessionWrapper = SessionWrapperFactory::getInstance()->getActiveSession();
} catch (\Throwable $e) {
    $sessionWrapper = null;
}

$authUserId = $_SESSION['authUserID'] ?? null;
if (empty($authUserId) && $sessionWrapper) {
    $authUserId = $sessionWrapper->get('authUserID') ?: $sessionWrapper->get('authUser');
}

if (empty($authUserId)) {
    error_log('[MedEx Calendar] WARN: User id missing in session; continuing with subscription filters');
}

error_log('[MedEx Calendar] Starting get_events.php for user ' . ($authUserId ?? 'unknown'));

$getGlobalPref = static function (string $name, string $default = ''): string {
    $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT gl_value FROM globals WHERE gl_name = ? ORDER BY gl_index DESC LIMIT 1",
        [$name]
    );
    return isset($row['gl_value']) ? trim((string)$row['gl_value']) : $default;
};

$reschedDefaultEnabled = $getGlobalPref('medex_resched_defaults_enabled', '1') !== '0';
$reschedKeywordCsv = $getGlobalPref('medex_resched_default_categories', 'new,est,established');
$reschedKeywords = array_values(array_filter(array_map(
    static fn($v) => strtolower(trim((string)$v)),
    preg_split('/[,|]/', $reschedKeywordCsv) ?: []
), static fn($v) => $v !== ''));
if (empty($reschedKeywords)) {
    $reschedKeywords = ['new', 'est', 'established'];
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$api = new \OpenEMR\Modules\MedEx\MedExAPI();
if (!$api->hasServiceEntitlement('calendar_full')) {
    http_response_code(403);
    echo json_encode(['error' => 'calendar_full subscription required']);
    exit;
}

// Get date range from query params
$rawStart = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
$rawEnd = $_GET['end'] ?? date('Y-m-d', strtotime('+2 months'));
$startTs = strtotime((string)$rawStart);
$endTs = strtotime((string)$rawEnd);
$start = $startTs !== false ? date('Y-m-d', $startTs) : date('Y-m-d', strtotime('-1 month'));
$end = $endTs !== false ? date('Y-m-d', $endTs) : date('Y-m-d', strtotime('+2 months'));

error_log('[MedEx Calendar] Date range: ' . $start . ' to ' . $end);

// Get user's info
$userId = $authUserId;
$userInfo = !empty($userId)
    ? sqlQuery("SELECT id, username, facility_id, authorized FROM users WHERE id = ?", [$userId])
    : null;
$userProviderId = $userInfo['id'] ?? null;
$userProviderUsername = (string)($userInfo['username'] ?? '');
$userFacilityId = $userInfo['facility_id'] ?? null;
$isAuthorized = $userInfo['authorized'] ?? 0;

// Get authorized providers/facilities from calendar_full subscription
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
    if ($userProviderUsername !== '') {
        $requestedProviders = [$userProviderUsername];
    } elseif ($userProviderId) {
        $requestedProviders = [(string)$userProviderId];
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
    pc.pc_hometext as comments,
    pc.pc_eventDate as date,
    pc.pc_startTime as startTime,
    pc.pc_endTime as endTime,
    pc.pc_duration as duration,
    pc.pc_catid as category,
    pc.pc_prefcatid as preferred_category_id,
    pc.pc_aid as provider_id,
    pc.pc_pid as patient_id,
    pc.pc_apptstatus as status,
    pc.pc_location as location_tag,
    pc.pc_facility as facility_id,
    p.fname as patient_fname,
    p.lname as patient_lname,
    u.fname as provider_fname,
    u.lname as provider_lname,
    c.pc_catname as category_name,
    c.pc_catcolor as category_color,
    pref.pc_catname as preferred_category_name,
    pref.pc_catcolor as preferred_category_color
FROM openemr_postcalendar_events pc
LEFT JOIN patient_data p ON pc.pc_pid = p.pid
LEFT JOIN users u ON pc.pc_aid = u.id
LEFT JOIN openemr_postcalendar_categories c ON pc.pc_catid = c.pc_catid
LEFT JOIN openemr_postcalendar_categories pref ON pc.pc_prefcatid = pref.pc_catid
WHERE pc.pc_eventstatus = 1 AND pc.pc_eventDate >= ? AND pc.pc_eventDate <= ?";

$params = [$start, $end];

// Add provider filter. Accept both OpenEMR usernames and numeric user IDs
// so module-generated provider IDs and UI provider usernames both work.
if (!empty($requestedProviders)) {
    $providerIds = [];
    $providerUsernames = [];
    foreach ($requestedProviders as $providerToken) {
        $token = trim((string)$providerToken);
        if ($token === '') {
            continue;
        }
        if (ctype_digit($token)) {
            $providerIds[] = (int)$token;
        } else {
            $providerUsernames[] = $token;
        }
    }

    $providerClauses = [];
    if (!empty($providerUsernames)) {
        $namePlaceholders = implode(',', array_fill(0, count($providerUsernames), '?'));
        $providerClauses[] = "u.username IN ($namePlaceholders)";
        $params = array_merge($params, $providerUsernames);
    }
    if (!empty($providerIds)) {
        $idPlaceholders = implode(',', array_fill(0, count($providerIds), '?'));
        $providerClauses[] = "pc.pc_aid IN ($idPlaceholders)";
        $params = array_merge($params, $providerIds);
    }
    if (!empty($providerClauses)) {
        $sql .= " AND (" . implode(' OR ', $providerClauses) . ")";
    }
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

    // Build occupied intervals first so stale generated open slots are not shown
    // when a real patient appointment already exists at the same time.
    $occupiedByProviderDate = [];
    foreach ($rows as $occupiedRow) {
        $patientId = (int)($occupiedRow['patient_id'] ?? 0);
        if ($patientId <= 0) {
            continue;
        }

        $statusCode = strtoupper(trim((string)($occupiedRow['status'] ?? '')));
        if ($statusCode === 'X' || $statusCode === '%') {
            continue;
        }

        $providerId = (int)($occupiedRow['provider_id'] ?? 0);
        $eventDate = trim((string)($occupiedRow['date'] ?? ''));
        $startTime = trim((string)($occupiedRow['startTime'] ?? ''));
        if ($providerId <= 0 || $eventDate === '' || $startTime === '') {
            continue;
        }

        $durationSeconds = (int)($occupiedRow['duration'] ?? 0);
        if ($durationSeconds <= 0 && !empty($occupiedRow['endTime'])) {
            $startTsRaw = strtotime($startTime);
            $endTsRaw = strtotime((string)$occupiedRow['endTime']);
            if ($startTsRaw !== false && $endTsRaw !== false && $endTsRaw > $startTsRaw) {
                $durationSeconds = $endTsRaw - $startTsRaw;
            }
        }
        if ($durationSeconds <= 0) {
            $slotMinutes = (int)($GLOBALS['calendar_interval'] ?? 15);
            if ($slotMinutes <= 0) {
                $slotMinutes = 15;
            }
            $durationSeconds = $slotMinutes * 60;
        }

        $startTs = strtotime($eventDate . ' ' . $startTime);
        if ($startTs === false) {
            continue;
        }
        $endTs = $startTs + $durationSeconds;
        if ($endTs <= $startTs) {
            continue;
        }

        $bucketKey = $providerId . '|' . $eventDate;
        if (!isset($occupiedByProviderDate[$bucketKey])) {
            $occupiedByProviderDate[$bucketKey] = [];
        }
        $occupiedByProviderDate[$bucketKey][] = [$startTs, $endTs];
    }

    // Guard optional slot-registry access so missing table never pollutes JSON output.
    $hasSlotRegistry = false;
    try {
        $registryProbe = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
        $hasSlotRegistry = !empty($registryProbe);
    } catch (\Throwable $ignored) {
        $hasSlotRegistry = false;
    }

    // Expire elapsed temporary slot holds so feed reflects actionable state.
    if ($hasSlotRegistry) {
        try {
            sqlStatement(
                "UPDATE medex_slot_registry
                 SET slot_state = 'available', hold_expires_at = NULL, held_by_role = NULL, held_by_ref = NULL
                 WHERE slot_state IN ('held_staff', 'held_patient')
                   AND hold_expires_at IS NOT NULL
                   AND hold_expires_at <= NOW()"
            );
        } catch (\Throwable $ignored) {
            // Optional registry support; ignore failures so event feed remains usable.
        }
    }

    $slotStateByOpenEid = [];
    try {
        if (!$hasSlotRegistry) {
            throw new \RuntimeException('slot registry unavailable');
        }
        $eventIds = array_map(static fn($r) => (int)($r['id'] ?? 0), $rows);
        $eventIds = array_values(array_filter($eventIds, static fn($v) => $v > 0));
        if (!empty($eventIds)) {
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            $slotRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
                "SELECT open_slot_eid, slot_state, hold_expires_at, held_by_role, held_by_ref, slot_id
                 FROM medex_slot_registry
                 WHERE open_slot_eid IN ($placeholders)
                 ORDER BY slot_id DESC",
                $eventIds
            );
            foreach ($slotRows as $slotRow) {
                $openEid = (int)($slotRow['open_slot_eid'] ?? 0);
                if ($openEid <= 0 || isset($slotStateByOpenEid[$openEid])) {
                    continue;
                }
                $slotStateByOpenEid[$openEid] = [
                    'slot_state' => (string)($slotRow['slot_state'] ?? 'available'),
                    'hold_expires_at' => $slotRow['hold_expires_at'] ?? null,
                    'held_by_role' => $slotRow['held_by_role'] ?? null,
                    'held_by_ref' => $slotRow['held_by_ref'] ?? null,
                ];
            }
        }
    } catch (\Throwable $ignored) {
        $slotStateByOpenEid = [];
    }

    // Synthesize Chip 1 events for slots that were deleted from openemr_postcalendar_events
    // when a patient booked (edit_event_wrapper.php deletes the slot row).
    // The registry preserves the slot metadata; we rebuild the chip from it so Chip 1
    // stays visible alongside the patient appointment.
    $existingEventIds = array_flip(array_map(static fn($r) => (int)($r['id'] ?? 0), $rows));
    if ($hasSlotRegistry) {
        try {
            // Build provider IN clause matching the current filter
            $registryProviderIds = [];
            foreach ($requestedProviders as $tok) {
                $tok = trim((string)$tok);
                if ($tok === '') { continue; }
                if (ctype_digit($tok)) {
                    $registryProviderIds[] = (int)$tok;
                } else {
                    $uidRow = sqlQuery("SELECT id FROM users WHERE username = ? LIMIT 1", [$tok]);
                    if (!empty($uidRow['id'])) {
                        $registryProviderIds[] = (int)$uidRow['id'];
                    }
                }
            }

            $regWhere = "event_date >= ? AND event_date <= ?";
            $regParams = [$start, $end];
            if (!empty($registryProviderIds)) {
                $rp = implode(',', array_fill(0, count($registryProviderIds), '?'));
                $regWhere .= " AND provider_id IN ($rp)";
                $regParams = array_merge($regParams, $registryProviderIds);
            }

            $deletedSlotRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
                "SELECT slot_id, open_slot_eid, patient_pc_eid, provider_id,
                        event_date, start_time, end_time, category_id, slot_state
                 FROM medex_slot_registry
                 WHERE $regWhere
                   AND slot_state IN ('consumed','pending_consumption')
                 ORDER BY slot_id DESC",
                $regParams
            );

            // Load category lookup once (name + color)
            $catLookup = [];
            $catStmt = sqlStatement("SELECT pc_catid, pc_catname, pc_catcolor FROM openemr_postcalendar_categories");
            while ($catRow = sqlFetchArray($catStmt)) {
                $catLookup[(int)$catRow['pc_catid']] = [
                    'name'  => (string)($catRow['pc_catname'] ?? ''),
                    'color' => (string)($catRow['pc_catcolor'] ?? ''),
                ];
            }

            // Deduplicate: one synthesized Chip 1 per (provider, date, start_time)
            $seenSynthKeys = [];
            foreach ($deletedSlotRows as $drRow) {
                $openEid = (int)($drRow['open_slot_eid'] ?? 0);
                // Skip if the original slot still exists in the event feed
                if ($openEid > 0 && isset($existingEventIds[$openEid])) {
                    continue;
                }

                $drProvider = (int)($drRow['provider_id'] ?? 0);
                $drDate     = trim((string)($drRow['event_date'] ?? ''));
                $drStart    = trim((string)($drRow['start_time'] ?? ''));
                $drEnd      = trim((string)($drRow['end_time']   ?? ''));
                $drCatId    = (int)($drRow['category_id'] ?? 0);
                if ($drProvider <= 0 || $drDate === '' || $drStart === '') {
                    continue;
                }

                $synthKey = $drProvider . '|' . $drDate . '|' . $drStart;
                if (isset($seenSynthKeys[$synthKey])) {
                    continue;
                }
                $seenSynthKeys[$synthKey] = true;

                $catInfo   = $catLookup[$drCatId] ?? ['name' => 'Open Slot', 'color' => ''];
                $catName   = $catInfo['name'] !== '' ? $catInfo['name'] : 'Open Slot';
                $rawCatCol = $catInfo['color'];
                if ($rawCatCol !== '' && $rawCatCol[0] !== '#') {
                    $rawCatCol = '#' . $rawCatCol;
                }
                $synthColor = preg_match('/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $rawCatCol)
                    ? $rawCatCol : '#3788d8';

                $startDt = $drDate . ' ' . $drStart;
                $startTs2 = strtotime($startDt) ?: 0;
                if ($drEnd === '' || strtotime($drEnd) <= strtotime($drStart)) {
                    $drEnd = date('H:i:s', $startTs2 + 900); // fallback 15 min
                }
                $endDt = $drDate . ' ' . $drEnd;

                $rr = hexdec(substr($synthColor, 1, 2));
                $gg = hexdec(substr($synthColor, 3, 2));
                $bb = hexdec(substr($synthColor, 5, 2));
                $lum2 = (($rr * 299) + ($gg * 587) + ($bb * 114)) / 1000;

                $slotState2 = (string)($drRow['slot_state'] ?? 'consumed');
                $rows[] = [
                    'id'                    => 'reg_' . (int)$drRow['slot_id'],
                    'title'                 => 'Open Slot - ' . $catName,
                    'date'                  => $drDate,
                    'startTime'             => $drStart,
                    'endTime'               => $drEnd,
                    'duration'              => max(0, strtotime($drEnd) - strtotime($drStart)),
                    'category'              => $drCatId,
                    'preferred_category_id' => $drCatId,
                    'provider_id'           => $drProvider,
                    'patient_id'            => null,
                    'status'                => '-',
                    'location_tag'          => 'MEDEX_REGISTRY_SYNTH',
                    'facility_id'           => 0,
                    'comments'              => '',
                    'category_name'         => $catName,
                    'category_color'        => $rawCatCol,
                    'preferred_category_name'  => $catName,
                    'preferred_category_color' => $rawCatCol,
                    'provider_fname'        => '',
                    'provider_lname'        => '',
                    'patient_fname'         => '',
                    'patient_lname'         => '',
                    // Pre-fill computed fields used later in the loop
                    '_synth_start_dt'  => $startDt,
                    '_synth_end_dt'    => $endDt,
                    '_synth_color'     => $synthColor,
                    '_synth_text_col'  => $lum2 >= 145 ? '#111111' : '#ffffff',
                    '_synth_state'     => $slotState2,
                    '_is_synth'        => true,
                ];
            }
        } catch (\Throwable $ignored) {
            // Registry synthesis is best-effort; never block the main feed.
        }
    }

    // Load appointment status labels + colors from list_options (apptstat list).
    // pc_apptstatus is the source of truth — staff and MedEx write directly to it.
    // Multi-char codes like SMS/EMAIL/AVM are set by MedEx when a patient confirms
    // via that channel. Single-char codes like '<' (In exam room) are set by staff.
    $apptStatMap = [];
    try {
        $apptStatRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
            "SELECT option_id, title, notes FROM list_options WHERE list_id = 'apptstat' AND activity = 1",
            []
        );
        foreach ($apptStatRows as $statRow) {
            $code  = (string)($statRow['option_id'] ?? '');
            $title = trim((string)($statRow['title'] ?? ''));
            $notes = trim((string)($statRow['notes'] ?? ''));
            if ($code === '') {
                continue;
            }
            // Strip leading "code " prefix from title (e.g. "< In exam room" → "In exam room")
            $label = preg_replace('/^' . preg_quote($code, '/') . '\s+/u', '', $title);
            if ($label === '' || $label === $code) {
                $label = $title;
            }
            // notes format: "RRGGBB|weight"
            $rawColor = strtok($notes, '|') ?: '';
            $color = '';
            if (preg_match('/^[0-9a-fA-F]{6}$/', $rawColor)) {
                $color = '#' . strtoupper($rawColor);
            }
            $apptStatMap[$code] = ['label' => $label, 'color' => $color];
        }
    } catch (\Exception $e) {
        $apptStatMap = [];
    }

    // Load MedEx icons map once (kept for backward compat; statusIcon still populated)
    $icons = [];
    try {
        $iconRows = \OpenEMR\Common\Database\QueryUtils::fetchRecords("SELECT * FROM medex_icons");
        foreach ($iconRows as $iconRow) {
            $icons[$iconRow['msg_type']][$iconRow['msg_status']] = $iconRow['i_html'];
        }
    } catch (\Exception $e) {
        $icons = [];
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

    // Multiple MedEx generation paths can produce the same availability slot.
    // Keep one generated slot per provider/date/start/end to avoid duplicate open rows.
    $bestGeneratedSlotByKey = [];
    foreach ($rows as $candidateRow) {
        $candidatePid = (int)($candidateRow['patient_id'] ?? 0);
        $candidateLocation = trim((string)($candidateRow['location_tag'] ?? ''));
        $candidateIsGenerated = ($candidatePid <= 0) && str_starts_with($candidateLocation, 'MEDEX_');
        if (!$candidateIsGenerated) {
            continue;
        }
        $slotKey = (int)($candidateRow['provider_id'] ?? 0)
            . '|' . (string)($candidateRow['date'] ?? '')
            . '|' . (string)($candidateRow['startTime'] ?? '')
            . '|' . (string)($candidateRow['endTime'] ?? '');
        if ($slotKey === '') {
            continue;
        }

        $score = 0;
        if ((int)($candidateRow['preferred_category_id'] ?? 0) > 0) {
            $score += 10;
        }
        if (str_contains($candidateLocation, 'INTERVIEW_GENERATED')) {
            $score += 5;
        }

        $existing = $bestGeneratedSlotByKey[$slotKey] ?? null;
        if ($existing === null || $score > (int)($existing['score'] ?? -1)) {
            $bestGeneratedSlotByKey[$slotKey] = [
                'id' => (string)($candidateRow['id'] ?? ''),
                'score' => $score,
            ];
        }
    }

    foreach ($rows as $row) {
        // Synthesized registry rows have pre-computed values — emit them directly.
        if (!empty($row['_is_synth'])) {
            $synthColor   = (string)($row['_synth_color']    ?? '#3788d8');
            $synthTextCol = (string)($row['_synth_text_col'] ?? '#ffffff');
            $synthState   = (string)($row['_synth_state']    ?? 'consumed');
            $catName      = (string)($row['category_name']   ?? 'Open Slot');
            $events[] = [
                'id'              => $row['id'],
                'title'           => $catName,
                'start'           => (string)($row['_synth_start_dt'] ?? ''),
                'end'             => (string)($row['_synth_end_dt']   ?? ''),
                'backgroundColor' => $synthColor,
                'borderColor'     => $synthColor,
                'textColor'       => $synthTextCol,
                'extendedProps'   => [
                    'patientId'          => null,
                    'patientName'        => '',
                    'providerId'         => $row['provider_id'],
                    'providerName'       => '',
                    'category'           => $catName,
                    'categoryId'         => (int)($row['category'] ?? 0),
                    'preferredCategoryId'=> (int)($row['preferred_category_id'] ?? 0),
                    'isGeneratedSlot'    => true,
                    'isProviderAvailability' => false,
                    'isOpenSlotLike'     => true,
                    'isReschedulable'    => false,
                    'locationTag'        => 'MEDEX_REGISTRY_SYNTH',
                    'comments'           => '',
                    'status'             => '-',
                    'apptStatusLabel'    => '',
                    'apptStatusColor'    => '',
                    'statusIcon'         => '',
                    'reminderHistory'    => [],
                    'facilityId'         => 0,
                    'slotTypeColor'      => $synthColor,
                    'slotState'          => $synthState,
                ],
            ];
            continue;
        }

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

        $rawTitle = trim((string)($row['title'] ?? ''));
        if ($patientName) {
            // Patient appointments: leave event.title empty so FullCalendar renders nothing.
            // eventDidMount builds the full chip content: name link + modality icon + category + comments.
            // Any non-empty title renders in .fc-event-title AND our injected content, causing duplication.
            $title = '';
        } else {
            $title = $rawTitle;
        }

        $locationTag = trim((string)($row['location_tag'] ?? ''));
        $isProviderAvailability = ((int)($row['category'] ?? 0) === 2) && ((int)($row['patient_id'] ?? 0) <= 0);
        $isGeneratedSlot = ((int)($row['patient_id'] ?? 0) <= 0) && str_starts_with($locationTag, 'MEDEX_');
        $isOpenSlotLike = ((int)($row['patient_id'] ?? 0) <= 0)
            && (
                stripos($rawTitle, 'Open Slot - ') === 0
                || stripos($rawTitle, 'In Office - ') === 0
                || strcasecmp($rawTitle, 'Open Slot') === 0
            );
        $locationTagUpper = strtoupper($locationTag);
        $isExplicitReschedYes = str_contains($locationTagUpper, 'RESCHEDULABLE=1') || str_contains($locationTagUpper, 'RESCHED=1');
        $isExplicitReschedNo = str_contains($locationTagUpper, 'RESCHEDULABLE=0') || str_contains($locationTagUpper, 'RESCHED=0');
        $isReschedulable = $isExplicitReschedYes;

        // Default policy: template-style open slots for New/Established visits are
        // treated as reschedulable unless a slot explicitly opts out via location tag.
        if ($reschedDefaultEnabled && !$isReschedulable && !$isExplicitReschedNo && ($isProviderAvailability || $isGeneratedSlot || $isOpenSlotLike)) {
            $preferredCategoryNameForPolicy = trim((string)($row['preferred_category_name'] ?? ''));
            if ($preferredCategoryNameForPolicy === '') {
                $preferredCategoryNameForPolicy = trim((string)($row['category_name'] ?? ''));
            }
            if ($preferredCategoryNameForPolicy === '') {
                $preferredCategoryNameForPolicy = $rawTitle;
            }

            $policyName = strtolower($preferredCategoryNameForPolicy);
            $isNewEstablishedTemplate = false;
            foreach ($reschedKeywords as $needle) {
                if ($needle !== '' && str_contains($policyName, $needle)) {
                    $isNewEstablishedTemplate = true;
                    break;
                }
            }

            if ($isNewEstablishedTemplate) {
                $isReschedulable = true;
            }
        }

        // Generated In/Out boundary markers are internal state toggles for OpenEMR
        // availability logic and should not be rendered as patient-facing slots.
        $normalizedRawTitle = strtolower(trim((string)($row['title'] ?? '')));
        $isGeneratedBoundaryMarker = $isGeneratedSlot
            && ((int)($row['preferred_category_id'] ?? 0) <= 0)
            && in_array((int)($row['category'] ?? 0), [2, 3], true)
            && ($normalizedRawTitle === 'in office' || $normalizedRawTitle === 'out of office');
        if ($isGeneratedBoundaryMarker) {
            continue;
        }

        if ($isGeneratedSlot) {
            $slotKey = (int)($row['provider_id'] ?? 0)
                . '|' . (string)($row['date'] ?? '')
                . '|' . (string)($row['startTime'] ?? '')
                . '|' . (string)($row['endTime'] ?? '');
            $best = $bestGeneratedSlotByKey[$slotKey] ?? null;
            if ($best && (string)($best['id'] ?? '') !== (string)($row['id'] ?? '')) {
                continue;
            }
        }

        // Preserve original slot-type color separately from event/status color.
        // Prefer preferred-category color (pc_prefcatid), then base category color.
        $slotTypeRaw = trim((string)($row['preferred_category_color'] ?? ''));
        if ($slotTypeRaw === '') {
            $slotTypeRaw = trim((string)($row['category_color'] ?? ''));
        }
        if ($slotTypeRaw !== '' && $slotTypeRaw[0] !== '#') {
            $slotTypeRaw = '#' . $slotTypeRaw;
        }
        $slotTypeColor = preg_match('/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $slotTypeRaw) ? $slotTypeRaw : '';

        // Use preferred category color for provider-availability rows (pc_catid=2)
        // so generated open slots visually match appointment category color.
        $rawColor = trim((string)($row['category_color'] ?? ''));
        if ($isProviderAvailability || $isGeneratedSlot) {
            $preferredRaw = trim((string)($row['preferred_category_color'] ?? ''));
            if ($preferredRaw !== '') {
                $rawColor = $preferredRaw;
            }
        }
        if ($rawColor !== '' && $rawColor[0] !== '#') {
            $rawColor = '#' . $rawColor;
        }
        $color = preg_match('/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $rawColor) ? $rawColor : '#3788d8';

        // Compute contrasting text color for readability against category color.
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        $luminance = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        $eventTextColor = $luminance >= 145 ? '#111111' : '#ffffff';

        // pc_apptstatus is the source of truth for reminder confirmation status.
        // MedEx writes multi-char codes (SMS/EMAIL/AVM/CALL) when a patient confirms
        // via that channel. Map them directly to the appropriate icon from medex_icons.
        $apptStatus = (string)($row['status'] ?? '');
        $statusIconHtml = '';
        if (in_array($apptStatus, ['SMS', 'EMAIL', 'AVM', 'CALL'], true)) {
            // For CALL use the SMS CALL icon; for channel confirmations use CONFIRMED icon.
            $iconType   = ($apptStatus === 'CALL') ? 'SMS' : $apptStatus;
            $iconStatus = ($apptStatus === 'CALL') ? 'CALL' : 'CONFIRMED';
            $statusIconHtml = $icons[$iconType][$iconStatus] ?? '';
        }
        // Fallback: legacy medex_outgoing icon (covers older data paths).
        if ($statusIconHtml === '') {
            $medexRowsForEvent = $medexByEid[$row['id']] ?? [];
            $statusIconHtml = $buildMedexIcons($medexRowsForEvent, $icons);
        }
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

        $displayCategory = (string)($row['category_name'] ?? '');
        if (($isProviderAvailability || $isGeneratedSlot || $isOpenSlotLike) && !empty($row['preferred_category_name'])) {
            $displayCategory = (string)$row['preferred_category_name'];
        }

        // Generated/provider-availability slots should show the slot's appointment
        // type (preferred category) instead of generic In Office text.
        if ($isProviderAvailability || $isGeneratedSlot || $isOpenSlotLike) {
            $preferredCategoryName = trim((string)($row['preferred_category_name'] ?? ''));
            $titleText = trim((string)($row['title'] ?? ''));

            if ($preferredCategoryName === '' && $titleText !== '') {
                if (stripos($titleText, 'Open Slot - ') === 0) {
                    $preferredCategoryName = trim(substr($titleText, strlen('Open Slot - ')));
                } elseif (stripos($titleText, 'In Office - ') === 0) {
                    $preferredCategoryName = trim(substr($titleText, strlen('In Office - ')));
                } elseif (strcasecmp($titleText, 'In Office') !== 0 && strcasecmp($titleText, 'Out Of Office') !== 0) {
                    $preferredCategoryName = $titleText;
                }
            }

            if ($preferredCategoryName !== '') {
                $displayCategory = $preferredCategoryName;
                $title = $preferredCategoryName;
            } elseif ($isGeneratedSlot || $isOpenSlotLike) {
                $title = 'Open Slot';
            }
        }

        if (($isGeneratedSlot || $isProviderAvailability) && $isReschedulable) {
            $title .= ' (Reschedulable)';
        }

        // Template slot chips (Chip 1) always remain visible in MedEx FullCalendar
        // regardless of whether a patient appointment occupies the same time window.
        // The two-lane layout shows the slot type on the left and the appointment on the right.
        // (Note: templates do NOT belong in the native OpenEMR calendar — that is a separate concern.)

        $events[] = [
            'id' => $row['id'],
            'title' => $title,
            'start' => $startDateTime,
            'end' => $endDateTime,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => $eventTextColor,
            'extendedProps' => [
                'patientId' => $row['patient_id'],
                'patientName' => $patientName,
                'providerId' => $row['provider_id'],
                'providerName' => $providerName,
                'category' => $displayCategory,
                'categoryId' => (int)($row['category'] ?? 0),
                'preferredCategoryId' => (int)($row['preferred_category_id'] ?? 0),
                'isGeneratedSlot' => $isGeneratedSlot,
                'isProviderAvailability' => $isProviderAvailability,
                'isOpenSlotLike' => $isOpenSlotLike,
                'isReschedulable' => $isReschedulable,
                'locationTag' => $locationTag,
                'comments' => trim((string)($row['comments'] ?? '')),
                'status' => $row['status'],
                'apptStatusLabel' => $apptStatMap[(string)($row['status'] ?? '')] ['label'] ?? '',
                'apptStatusColor' => $apptStatMap[(string)($row['status'] ?? '')]['color'] ?? '',
                'statusIcon' => $statusIconHtml,
                'reminderHistory' => array_values(array_map(static function($r) {
                    return [
                        'type'     => (string)($r['msg_type']     ?? ''),
                        'reply'    => (string)($r['msg_reply']    ?? ''),
                        'date'     => (string)($r['msg_date']     ?? ''),
                        'progress' => (string)($r['msg_progress'] ?? ''),
                    ];
                }, $medexByEid[$row['id']] ?? [])),
                'medexDebug' => $medexDebug,
                'facilityId' => $row['facility_id'],
                'slotTypeColor' => $slotTypeColor !== '' ? $slotTypeColor : $color
            ]
        ];

        $eventIndex = count($events) - 1;
        $openEid = (int)($row['id'] ?? 0);
        if ($openEid > 0 && isset($slotStateByOpenEid[$openEid])) {
            $events[$eventIndex]['extendedProps']['slotState'] = $slotStateByOpenEid[$openEid]['slot_state'];
            $events[$eventIndex]['extendedProps']['holdExpiresAt'] = $slotStateByOpenEid[$openEid]['hold_expires_at'];
            $events[$eventIndex]['extendedProps']['heldByRole'] = $slotStateByOpenEid[$openEid]['held_by_role'];
            $events[$eventIndex]['extendedProps']['heldByRef'] = $slotStateByOpenEid[$openEid]['held_by_ref'];
        } elseif ($isGeneratedSlot || $isProviderAvailability || $isOpenSlotLike) {
            $events[$eventIndex]['extendedProps']['slotState'] = 'available';
        }
    }

    error_log('[MedEx Calendar] Returning ' . count($events) . ' events');
    echo json_encode($events);

} catch (\Exception $e) {
    error_log('[MedEx Calendar] Error fetching events: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching appointments']);
}
