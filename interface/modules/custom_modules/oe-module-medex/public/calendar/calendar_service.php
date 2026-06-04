<?php
/**
 * MedEx Calendar Service Studio Prototype
 * Unified template and scheduling rules interface.
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . '/../../src/MedExAPI.php');
require_once(__DIR__ . '/../../src/MedExConfig.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\MedExConfig;

if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
    die('Access denied. You do not have permission to access Calendar Service Studio.');
}

function cs_json_exit(array $payload): void
{
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function cs_normalize_hex_color(string $raw): string
{
    $color = trim($raw);
    if ($color !== '' && $color[0] !== '#') {
        $color = '#' . $color;
    }
    // Accept 6-char (#rrggbb) or 8-char with alpha (#rrggbbaa).
    if (!preg_match('/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', $color)) {
        $color = '#1d4ed8';
    }
    return strtoupper($color);
}

function cs_normalize_duration_minutes($rawDuration, int $defaultMinutes = 30): int
{
    $duration = (int)$rawDuration;
    if ($duration <= 0) {
        $duration = $defaultMinutes;
    }

    // Some installs store durations in seconds (or multiplied seconds); repeatedly reduce to minutes.
    while ($duration > 240 && $duration % 60 === 0) {
        $duration = (int)($duration / 60);
    }

    return max(5, min(240, $duration));
}

function cs_category_duration_uses_seconds(): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    try {
        $row = sqlQuery(
            "SELECT COALESCE(MAX(pc_duration), 0) AS max_duration
             FROM openemr_postcalendar_categories
             WHERE COALESCE(pc_active, 1) = 1"
        );
        $maxDuration = (int)($row['max_duration'] ?? 0);
        $cached = $maxDuration > 240;
    } catch (\Throwable $ignored) {
        $cached = false;
    }

    return $cached;
}

function cs_category_duration_to_db_value(int $minutes): int
{
    $cleanMinutes = max(5, min(240, $minutes));
    if (cs_category_duration_uses_seconds()) {
        return $cleanMinutes * 60;
    }
    return $cleanMinutes;
}

function cs_infer_cadence_from_dates(array $dates): array
{
    $unique = [];
    foreach ($dates as $rawDate) {
        $date = trim((string)$rawDate);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            continue;
        }
        $ts = strtotime($date . ' 00:00:00');
        if ($ts === false) {
            continue;
        }
        $unique[$date] = $ts;
    }

    if (count($unique) < 2) {
        return [
            'cadence' => 'weekly',
            'label' => 'Weekly',
            'confidence' => 0.35,
        ];
    }

    asort($unique);
    $timestamps = array_values($unique);
    $diffs = [];
    for ($i = 1; $i < count($timestamps); $i++) {
        $deltaDays = (int)round(($timestamps[$i] - $timestamps[$i - 1]) / 86400);
        if ($deltaDays > 0) {
            $diffs[] = $deltaDays;
        }
    }

    if (empty($diffs)) {
        return [
            'cadence' => 'weekly',
            'label' => 'Weekly',
            'confidence' => 0.35,
        ];
    }

    $weeklyHits = 0;
    $fourWeekHits = 0;
    $monthlyHits = 0;
    foreach ($diffs as $diff) {
        if ($diff >= 6 && $diff <= 8) {
            $weeklyHits++;
        }
        if ($diff >= 26 && $diff <= 30) {
            $fourWeekHits++;
        }
        if ($diff >= 27 && $diff <= 34) {
            $monthlyHits++;
        }
    }

    $diffCount = max(1, count($diffs));
    $weeklyScore = $weeklyHits / $diffCount;
    $fourWeekScore = $fourWeekHits / $diffCount;
    $monthlyScore = $monthlyHits / $diffCount;

    $dayOfMonthCounts = [];
    foreach (array_keys($unique) as $date) {
        $dom = (int)substr($date, 8, 2);
        $dayOfMonthCounts[$dom] = ($dayOfMonthCounts[$dom] ?? 0) + 1;
    }
    $sameDomRatio = max($dayOfMonthCounts) / max(1, count($unique));
    if ($sameDomRatio < 0.75) {
        $monthlyScore *= 0.6;
    }

    if ($monthlyScore >= 0.65 && $monthlyHits >= 2 && count($unique) >= 3) {
        return [
            'cadence' => 'monthly',
            'label' => 'Monthly',
            'confidence' => round($monthlyScore, 2),
        ];
    }
    if ($fourWeekScore >= 0.65 && $fourWeekHits >= 2) {
        return [
            'cadence' => 'four_week',
            'label' => '4-Week Rotation',
            'confidence' => round($fourWeekScore, 2),
        ];
    }
    if ($weeklyScore >= 0.65) {
        return [
            'cadence' => 'weekly',
            'label' => 'Weekly',
            'confidence' => round($weeklyScore, 2),
        ];
    }

    return [
        'cadence' => 'custom',
        'label' => 'Custom cadence',
        'confidence' => max(round($weeklyScore, 2), round($fourWeekScore, 2), round($monthlyScore, 2)),
    ];
}

function cs_weekday_index_from_text(string $text): ?int
{
    $map = [
        'monday' => 0,
        'tuesday' => 1,
        'wednesday' => 2,
        'thursday' => 3,
        'friday' => 4,
        'saturday' => 5,
        'sunday' => 6,
    ];
    $lower = strtolower($text);
    foreach ($map as $name => $idx) {
        if (strpos($lower, $name) !== false) {
            return $idx;
        }
    }
    return null;
}

function cs_weekday_indices_from_text(string $text): array
{
    $lower = strtolower($text);

    if (preg_match('/\b(7\s*days?|every\s+day|daily|all\s+week|seven\s+days)\b/i', $lower)) {
        return [0, 1, 2, 3, 4, 5, 6];
    }
    if (preg_match('/\b(weekdays?|workdays?)\b/i', $lower)) {
        return [0, 1, 2, 3, 4];
    }
    if (preg_match('/\b(weekends?)\b/i', $lower)) {
        return [5, 6];
    }

    $orderedMap = [
        'monday' => 0,
        'tuesday' => 1,
        'wednesday' => 2,
        'thursday' => 3,
        'friday' => 4,
        'saturday' => 5,
        'sunday' => 6,
    ];
    $indices = [];
    foreach ($orderedMap as $name => $idx) {
        if (strpos($lower, $name) !== false) {
            $indices[] = $idx;
        }
    }
    if (!empty($indices)) {
        return array_values(array_unique($indices));
    }

    $single = cs_weekday_index_from_text($text);
    return $single === null ? [] : [$single];
}

function cs_time_minutes_from_text(string $text): ?int
{
    $patterns = [
        '/(?:starting\s+at|start\s+at|from|at)\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/i',
        '/\b(\d{1,2}):(\d{2})\s*(am|pm)\b/i',
        '/\b(\d{1,2})\s*(am|pm)\b/i',
        '/(?:starting\s+at|start\s+at|from|at)\s*(\d{1,2})(?::(\d{2}))?\b/i',
        '/\b(\d{1,2}):(\d{2})\b/',
    ];

    foreach ($patterns as $pattern) {
        if (!preg_match($pattern, $text, $m)) {
            continue;
        }
        $hour = (int)($m[1] ?? 0);
        $min = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
        $ampm = strtolower((string)($m[3] ?? ''));
        $minutes = cs_time_minutes_from_parts($hour, $min, $ampm);
        if ($minutes !== null) {
            return $minutes;
        }
    }

    return null;
}

function cs_time_minutes_from_parts(int $hour, int $minute, string $ampm = ''): ?int
{
    $h = $hour;
    $m = $minute;
    $a = strtolower(trim($ampm));
    if ($a === 'pm' && $h < 12) {
        $h += 12;
    }
    if ($a === 'am' && $h === 12) {
        $h = 0;
    }
    if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
        return null;
    }
    return ($h * 60) + $m;
}

function cs_time_range_from_text(string $text): ?array
{
    if (!preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*(?:to|-|\x{2013}|\x{2014})\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/iu', $text, $m)) {
        return null;
    }
    $startHour = (int)$m[1];
    $startMin = isset($m[2]) ? (int)$m[2] : 0;
    $startAmPm = (string)($m[3] ?? '');
    $endHour = (int)$m[4];
    $endMin = isset($m[5]) ? (int)$m[5] : 0;
    $endAmPm = (string)($m[6] ?? '');

    $start = cs_time_minutes_from_parts($startHour, $startMin, $startAmPm);
    $end = cs_time_minutes_from_parts($endHour, $endMin, $endAmPm);
    if ($start === null || $end === null) {
        return null;
    }

    // Interpret shorthand like "8-4" as 8:00 AM to 4:00 PM.
    if ($end <= $start && $end + (12 * 60) > $start) {
        $end += 12 * 60;
    }

    if ($end <= $start) {
        return null;
    }

    return ['start' => $start, 'end' => $end];
}

function cs_guess_type_from_prompt(string $prompt): ?array
{
    $types = cs_load_appointment_types();
    $lower = strtolower($prompt);
    $best = null;
    $bestScore = 0;
    foreach ($types as $type) {
        $name = strtolower((string)($type['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $score = 0;
        if (strpos($lower, $name) !== false) {
            $score += 100 + strlen($name);
        }

        $tokens = preg_split('/[^a-z0-9]+/', $name) ?: [];
        foreach ($tokens as $token) {
            $token = trim((string)$token);
            if ($token === '' || strlen($token) < 3) {
                continue;
            }
            if (strpos($lower, $token) !== false) {
                $score += 10;
            }
        }

        if ($score > $bestScore) {
            $best = $type;
            $bestScore = $score;
        }
    }
    return $bestScore > 0 ? $best : null;
}

function cs_guess_provider_id_from_prompt(string $prompt): int
{
    $lower = strtolower($prompt);
    $res = sqlStatement("SELECT id, fname, lname, username FROM users WHERE authorized = 1 AND active = 1 AND calendar = 1");
    while ($row = sqlFetchArray($res)) {
        $fname = strtolower(trim((string)($row['fname'] ?? '')));
        $lname = strtolower(trim((string)($row['lname'] ?? '')));
        $username = strtolower(trim((string)($row['username'] ?? '')));
        if (($lname !== '' && strpos($lower, $lname) !== false) || ($fname !== '' && strpos($lower, $fname) !== false) || ($username !== '' && strpos($lower, $username) !== false)) {
            return (int)($row['id'] ?? 0);
        }
    }
    return 0;
}

function cs_guess_facility_id_from_prompt(string $prompt): int
{
    $lower = strtolower($prompt);
    $res = sqlStatement("SELECT id, name FROM facility");
    while ($row = sqlFetchArray($res)) {
        $name = strtolower(trim((string)($row['name'] ?? '')));
        if ($name !== '' && strpos($lower, $name) !== false) {
            return (int)($row['id'] ?? 0);
        }
    }
    return 0;
}

function cs_load_appointment_types(): array
{
    $types = [];
    $res = sqlStatement(
        "SELECT c.pc_catid, c.pc_catname, c.pc_catcolor, COALESCE(c.pc_duration, 30) AS pc_duration,
                COALESCE(p.facility_id, 0) AS facility_id
         FROM openemr_postcalendar_categories c
         LEFT JOIN medex_category_prefs p ON p.pc_catid = c.pc_catid
         WHERE COALESCE(c.pc_active, 1) = 1
           AND COALESCE(c.pc_cattype, 0) = 0
           AND TRIM(COALESCE(c.pc_catname, '')) <> ''
           AND LOWER(REPLACE(TRIM(COALESCE(c.pc_catname, '')), ' ', '')) <> 'noshow'
         ORDER BY COALESCE(c.pc_seq, 9999), c.pc_catname"
    );
    while ($row = sqlFetchArray($res)) {
        $types[] = [
            'id'         => (int)($row['pc_catid'] ?? 0),
            'name'       => (string)($row['pc_catname'] ?? 'Appointment'),
            'color'      => cs_normalize_hex_color((string)($row['pc_catcolor'] ?? '')),
            'duration'   => cs_normalize_duration_minutes($row['pc_duration'] ?? 30),
            'facilityId' => (int)($row['facility_id'] ?? 0),
        ];
    }
    return $types;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'get_rescheduler_status') {
        $paused = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_rescheduler_paused' LIMIT 1");
        cs_json_exit(['success' => true, 'paused' => (($paused['gl_value'] ?? '0') === '1')]);
    }

    if ($action === 'set_rescheduler_paused') {
        $val = (string)($_POST['paused'] ?? '0') === '1' ? '1' : '0';

        // Guard: activating requires at least one future open template slot.
        // Primary check: slot registry has a future available reschedulable slot.
        // Fallback: openemr_postcalendar_events has future open template slots (pc_pid=0).
        // This handles the case where the registry is empty/missing but slots exist in the calendar.
        if ($val === '0') {
            $hasCapacity = false;

            $tableExists = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
            if ($tableExists) {
                $registrySlot = sqlQuery(
                    "SELECT slot_id FROM medex_slot_registry
                     WHERE reschedulable = 1
                       AND slot_state = 'available'
                       AND event_date >= CURDATE()
                     LIMIT 1"
                );
                if ($registrySlot) {
                    $hasCapacity = true;
                }
            }

            // Fallback: any future open template slot in the calendar is sufficient capacity.
            if (!$hasCapacity) {
                $openSlot = sqlQuery(
                    "SELECT pc_eid FROM openemr_postcalendar_events
                     WHERE (pc_pid IS NULL OR pc_pid <= 0)
                       AND pc_recurrtype = 0
                       AND pc_eventDate >= CURDATE()
                     LIMIT 1"
                );
                if ($openSlot) {
                    $hasCapacity = true;
                }
            }

            if (!$hasCapacity) {
                cs_json_exit([
                    'success' => false,
                    'blocked' => true,
                    'reason'  => 'no_reschedulable_slots',
                    'message' => "The Patient Rescheduler cannot be activated because there are no upcoming open template slots in the calendar. Use the Slot Builder under Calendar Services to generate template slots first.",
                ]);
            }
        }

        sqlStatement("DELETE FROM globals WHERE gl_name = 'medex_rescheduler_paused'");
        sqlStatement("INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_rescheduler_paused', 0, ?)", [$val]);

        // Sync paused state + slot availability to MedEx SaaS so campaigns controller reflects reality.
        $hasSlots = '0';
        $syncTableExists = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
        if ($syncTableExists) {
            $hasSlotRow = sqlQuery(
                "SELECT 1 FROM medex_slot_registry
                 WHERE reschedulable = 1 AND slot_state = 'available' AND event_date >= CURDATE() LIMIT 1"
            );
            $hasSlots = $hasSlotRow ? '1' : '0';
        } else {
            $openSlot = sqlQuery(
                "SELECT pc_eid FROM openemr_postcalendar_events
                 WHERE (pc_pid IS NULL OR pc_pid <= 0)
                   AND pc_recurrtype = 0
                   AND pc_eventDate >= CURDATE()
                 LIMIT 1"
            );
            $hasSlots = $openSlot ? '1' : '0';
        }
        try {
            $medexApi = new MedExAPI();
            $medexApi->makeRequest('index.php?route=api/oemr/update_rescheduler_state', [
                'rescheduler_paused'      => $val,
                'has_reschedulable_slots' => $hasSlots,
            ], 'POST');
        } catch (\Throwable $syncErr) {
            error_log('[MedEx] update_rescheduler_state sync failed: ' . $syncErr->getMessage());
        }

        cs_json_exit(['success' => true, 'paused' => ($val === '1')]);
    }

    if ($action === 'get_scheduling_rules') {
        $raw = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_scheduling_rules' LIMIT 1");
        $rules = json_decode((string)($raw['gl_value'] ?? ''), true);
        if (!is_array($rules)) {
            $rules = ['template_enforcement' => 'guideline', 'allow_double_booking' => true];
        }
        cs_json_exit(['success' => true, 'rules' => $rules]);
    }

    if ($action === 'save_scheduling_rules') {
        $enforcement = (string)($_POST['template_enforcement'] ?? 'guideline');
        if (!in_array($enforcement, ['guideline', 'strict'], true)) {
            $enforcement = 'guideline';
        }
        $allowDoubleBook = ((string)($_POST['allow_double_booking'] ?? '1') === '1');
        $payload = json_encode([
            'template_enforcement' => $enforcement,
            'allow_double_booking' => $allowDoubleBook,
        ]);
        sqlStatement("DELETE FROM globals WHERE gl_name = 'medex_scheduling_rules'");
        sqlStatement("INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_scheduling_rules', 0, ?)", [$payload]);
        cs_json_exit(['success' => true, 'rules' => ['template_enforcement' => $enforcement, 'allow_double_booking' => $allowDoubleBook]]);
    }

    if ($action === 'get_rescheduler_rules') {
        $raw = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_rescheduler_rules' LIMIT 1");
        $rules = json_decode((string)($raw['gl_value'] ?? ''), true);
        if (!is_array($rules)) {
            $rules = ['defaults' => [], 'providers' => [], 'updated_at' => ''];
        }
        cs_json_exit(['success' => true, 'rules' => $rules]);
    }

    if ($action === 'save_rescheduler_rules') {
        $defaults = [
            'allow_same_day'   => !empty($_POST['pd_allow_same_day']),
            'max_offers'       => max(1, min(12,  (int)($_POST['pd_max_offers'] ?? 3))),
            'min_hours_before' => max(0, min(168, (int)($_POST['pd_min_hours_before'] ?? 1))),
            'max_days_before'  => max(0, min(365, (int)($_POST['pd_max_days_before'] ?? 30))),
            'max_days_after'   => max(0, min(365, (int)($_POST['pd_max_days_after'] ?? 60))),
            'slot_hold_minutes'=> max(5, min(60,  (int)($_POST['pd_slot_hold_minutes'] ?? 15))),
        ];
        $rawProviders = json_decode((string)($_POST['provider_rules'] ?? '{}'), true);
        $normalized = [];
        if (is_array($rawProviders)) {
            foreach ($rawProviders as $pid => $rule) {
                $pk = trim((string)$pid);
                if ($pk === '' || !ctype_digit($pk) || !is_array($rule)) {
                    continue;
                }
                $normalized[$pk] = [
                    'enabled'          => !empty($rule['enabled']),
                    'allow_same_day'   => !empty($rule['allow_same_day']),
                    'max_offers'       => max(1, min(12,  (int)($rule['max_offers'] ?? 3))),
                    'min_hours_before' => max(0, min(168, (int)($rule['min_hours_before'] ?? 1))),
                    'max_days_before'  => max(0, min(365, (int)($rule['max_days_before'] ?? 30))),
                    'max_days_after'   => max(0, min(365, (int)($rule['max_days_after'] ?? 60))),
                    'slot_hold_minutes'=> max(5, min(60,  (int)($rule['slot_hold_minutes'] ?? 15))),
                ];
            }
        }
        $payload = json_encode([
            'updated_at' => date('c'),
            'defaults'   => $defaults,
            'providers'  => $normalized,
        ]);
        sqlStatement("DELETE FROM globals WHERE gl_name = 'medex_rescheduler_rules'");
        sqlStatement("INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_rescheduler_rules', 0, ?)", [$payload]);
        cs_json_exit([
            'success'  => true,
            'message'  => 'Rescheduler rules saved.',
            'rules'    => ['defaults' => $defaults, 'providers' => $normalized, 'updated_at' => date('c')],
        ]);
    }

    if (in_array($action, ['list_categories', 'upsert_category', 'deactivate_category', 'reorder_categories', 'ask_template', 'help_chat'], true)) {
        try {
            if ($action === 'list_categories') {
                cs_json_exit(['success' => true, 'types' => cs_load_appointment_types()]);
            }

            if ($action === 'upsert_category') {
                $categoryId  = (int)($_POST['category_id'] ?? 0);
                $name        = trim((string)($_POST['category_name'] ?? ''));
                $duration    = cs_normalize_duration_minutes($_POST['duration'] ?? 30);
                $durationDb  = cs_category_duration_to_db_value($duration);
                $color       = cs_normalize_hex_color((string)($_POST['color'] ?? '#1d4ed8'));
                $facilityPref = (int)($_POST['facility_id'] ?? 0);
                if ($name === '') {
                    cs_json_exit(['success' => false, 'error' => 'Category name is required.']);
                }

                $existing = null;
                if ($categoryId > 0) {
                    $existing = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$categoryId]);
                }
                if (empty($existing['pc_catid'])) {
                    $existing = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE LOWER(pc_catname)=LOWER(?) LIMIT 1", [$name]);
                }

                if (!empty($existing['pc_catid'])) {
                    sqlStatement(
                        "UPDATE openemr_postcalendar_categories
                         SET pc_catname = ?, pc_catdesc = ?, pc_catcolor = ?, pc_duration = ?, pc_cattype = 0, pc_active = 1
                         WHERE pc_catid = ?",
                        [$name, 'Managed in Calendar Service Studio', $color, $durationDb, (int)$existing['pc_catid']]
                    );
                } else {
                    $seqRow = sqlQuery("SELECT COALESCE(MAX(pc_seq), 0) AS max_seq FROM openemr_postcalendar_categories");
                    $nextSeq = ((int)($seqRow['max_seq'] ?? 0)) + 1;
                    $constantId = 'medex_' . strtolower((string)preg_replace('/[^a-z0-9]+/', '_', $name));
                    $constantId = trim($constantId, '_');
                    if ($constantId === '' || $constantId === 'medex') {
                        $constantId = 'medex_category_' . time();
                    }
                    if (strlen($constantId) > 120) {
                        $constantId = substr($constantId, 0, 120);
                    }
                    $baseId = $constantId;
                    $suffix = 1;
                    while (true) {
                        $dup = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_constant_id = ? LIMIT 1", [$constantId]);
                        if (empty($dup['pc_catid'])) {
                            break;
                        }
                        $constantId = substr($baseId, 0, 110) . '_' . $suffix;
                        $suffix++;
                    }

                    sqlInsert(
                        "INSERT INTO openemr_postcalendar_categories
                            (pc_catname, pc_constant_id, pc_catdesc, pc_catcolor, pc_recurrtype, pc_recurrspec, pc_recurrfreq, pc_duration,
                             pc_dailylimit, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_cattype, pc_active, pc_seq, aco_spec)
                         VALUES (?, ?, ?, ?, 0, ?, 0, ?, 0, 0, 0, 0, 0, 0, 1, ?, 'encounters|notes')",
                        [$name, $constantId, 'Managed in Calendar Service Studio', $color, '', $durationDb, $nextSeq]
                    );
                }

                // Save/update facility preference for this type.
                $savedCatId = (int)sqlQuery(
                    "SELECT pc_catid FROM openemr_postcalendar_categories WHERE LOWER(pc_catname)=LOWER(?) LIMIT 1",
                    [$name]
                )['pc_catid'] ?? 0;
                if ($savedCatId <= 0 && !empty($existing['pc_catid'])) {
                    $savedCatId = (int)$existing['pc_catid'];
                }
                if ($savedCatId > 0 && $facilityPref >= 0) {
                    try {
                        sqlStatement(
                            "INSERT INTO medex_category_prefs (pc_catid, facility_id) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE facility_id = VALUES(facility_id)",
                            [$savedCatId, $facilityPref]
                        );
                    } catch (\Throwable $ignored) {}
                }

                cs_json_exit(['success' => true, 'types' => cs_load_appointment_types()]);
            }

            if ($action === 'deactivate_category') {
                $categoryId = (int)($_POST['category_id'] ?? 0);
                if ($categoryId <= 0) {
                    cs_json_exit(['success' => false, 'error' => 'Missing category id.']);
                }
                sqlStatement("UPDATE openemr_postcalendar_categories SET pc_active = 0 WHERE pc_catid = ?", [$categoryId]);

                // Sweep template slots that still carry this now-deactivated category.
                // Template slots are identified by having no patient (pc_pid empty/0).
                // Clear pc_prefcatid and strip the old category name from the title
                // so the slot falls back to generic "Open Slot".
                $affectedSlots = 0;
                $sweepRows = sqlStatement(
                    "SELECT pc_eid, pc_title FROM openemr_postcalendar_events
                     WHERE (COALESCE(pc_pid, '') = '' OR pc_pid = '0')
                       AND pc_prefcatid = ?",
                    [$categoryId]
                );
                while ($sweepRow = sqlFetchArray($sweepRows)) {
                    $eid   = (int)$sweepRow['pc_eid'];
                    $title = (string)($sweepRow['pc_title'] ?? '');
                    // Strip " - CategoryName" suffix if present; normalize to "Open Slot"
                    $newTitle = preg_replace('/\s*-\s*.+$/', '', $title);
                    if ($newTitle === '') {
                        $newTitle = 'Open Slot';
                    }
                    sqlStatement(
                        "UPDATE openemr_postcalendar_events SET pc_prefcatid = 0, pc_title = ? WHERE pc_eid = ?",
                        [$newTitle, $eid]
                    );
                    $affectedSlots++;
                }

                cs_json_exit(['success' => true, 'types' => cs_load_appointment_types(), 'affected_slots' => $affectedSlots]);
            }

            if ($action === 'reorder_categories') {
                $ids = json_decode((string)($_POST['ids'] ?? '[]'), true);
                if (!is_array($ids)) {
                    cs_json_exit(['success' => false, 'error' => 'Invalid ids.']);
                }
                foreach ($ids as $seq => $catId) {
                    $catId = (int)$catId;
                    if ($catId <= 0) { continue; }
                    sqlStatement("UPDATE openemr_postcalendar_categories SET pc_seq = ? WHERE pc_catid = ?", [$seq + 1, $catId]);
                }
                cs_json_exit(['success' => true, 'types' => cs_load_appointment_types()]);
            }

            if ($action === 'help_chat') {
                $message      = trim((string)($_POST['message'] ?? ''));
                $conversation = json_decode((string)($_POST['conversation'] ?? '[]'), true) ?: [];
                $slotSummary  = json_decode((string)($_POST['slot_summary'] ?? '[]'), true) ?: [];
                $providerIds  = json_decode((string)($_POST['provider_ids'] ?? '[]'), true) ?: [];

                if ($message === '') {
                    cs_json_exit(['success' => false, 'error' => 'Empty message.']);
                }

                // Build context from OpenEMR data
                $allProviders = [];
                $provRes = sqlStatement("SELECT id, fname, lname FROM users WHERE active = 1 AND authorized = 1 ORDER BY lname, fname");
                while ($pRow = sqlFetchArray($provRes)) {
                    $allProviders[] = ['id' => (int)$pRow['id'], 'name' => trim((string)$pRow['fname'] . ' ' . (string)$pRow['lname'])];
                }

                $allFacilities = [];
                $facRes = sqlStatement("SELECT id, name FROM facility WHERE service_location = 1 OR service_location IS NULL ORDER BY name");
                while ($fRow = sqlFetchArray($facRes)) {
                    $allFacilities[] = ['id' => (int)$fRow['id'], 'name' => (string)$fRow['name']];
                }

                $allCategories = cs_load_appointment_types();

                $selProviders = array_values(array_filter($allProviders, static function ($p) use ($providerIds) {
                    return in_array((int)$p['id'], array_map('intval', $providerIds), true);
                }));

                // Get MedEx practice ID and token
                $prefs = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
                $practiceId = (int)($prefs['MedEx_id'] ?? 0);

                $aiContext = [
                    'practice_id'              => $practiceId,
                    'available_providers'      => $allProviders,
                    'available_facilities'     => $allFacilities,
                    'available_categories'     => $allCategories,
                    'selected_provider_ids'    => array_column($selProviders, 'id'),
                    'selected_provider_names'  => array_column($selProviders, 'name'),
                    'current_slots_summary'    => $slotSummary,
                ];

                // Get session token via MedExAPI (handles login/refresh)
                try {
                    $medexApi = new MedExAPI();
                    $token    = $medexApi->getSessionToken();
                } catch (\Throwable $authEx) {
                    cs_json_exit(['success' => false, 'error' => 'MedEx authentication failed: ' . $authEx->getMessage()]);
                }

                $baseUrl = MedExConfig::baseUrl();
                $url     = $baseUrl . '/index.php?route=api/ai_planner&token=' . urlencode($token);

                $body = json_encode([
                    'mode'         => 'schedule_interview',
                    'message'      => $message,
                    'conversation' => $conversation,
                    'context'      => $aiContext,
                ]);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                $raw = curl_exec($ch);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($raw === false || $raw === '') {
                    cs_json_exit(['success' => false, 'error' => 'Could not reach AI agent: ' . $curlErr]);
                }

                $aiData = json_decode($raw, true);
                if (!is_array($aiData)) {
                    cs_json_exit(['success' => false, 'error' => 'Unexpected AI response format.']);
                }

                cs_json_exit([
                    'success'       => true,
                    'text'          => (string)($aiData['text'] ?? ''),
                    'schedule_data' => $aiData['schedule_data'] ?? null,
                    'context'       => $aiData['context'] ?? null,
                ]);
            }

            if ($action === 'ask_template') {
                $prompt = trim((string)($_POST['prompt'] ?? ''));
                $clarification = trim((string)($_POST['clarification'] ?? ''));
                $text = trim($prompt . ' ' . $clarification);
                if ($text === '') {
                    cs_json_exit(['success' => false, 'needs_clarification' => true, 'message' => 'Tell me what template to create.']);
                }

                $weekdays = cs_weekday_indices_from_text($text);
                if (empty($weekdays)) {
                    cs_json_exit(['success' => false, 'needs_clarification' => true, 'message' => 'Which day(s) should I schedule this template on?']);
                }

                $type = cs_guess_type_from_prompt($text);
                if (empty($type['id'])) {
                    cs_json_exit(['success' => false, 'needs_clarification' => true, 'message' => 'Which appointment type should I use?']);
                }

                // Count: "add 30", "30 POD#1 visits", "sees 30 patients", etc.
                $count = 0;
                if (preg_match('/\badd\s+(\d+)\b/i', $text, $mAdd)) {
                    $count = (int)$mAdd[1];
                }
                if ($count <= 0 && preg_match('/\b(?:sees?|books?|has)\s+(\d+)\b/i', $text, $mVerbCount)
                    && preg_match('/\b(?:patients?|slots?|appointments?|visits?)\b/i', $text)
                ) {
                    $count = (int)$mVerbCount[1];
                }
                if ($count <= 0 && preg_match('/\b(\d+)\s+(?:(?:[a-z0-9#_\/-]+\s+){0,3})?(?:slots?|appointments?|patients?|visits?)\b/i', $text, $mCount)) {
                    $count = (int)$mCount[1];
                }

                // Duration: explicit minutes > category's own pc_duration > 30-min fallback
                $duration = 0;
                if (preg_match('/(\d+)\s*minute/i', $text, $mDur)) {
                    $duration = cs_normalize_duration_minutes((int)$mDur[1]);
                }
                if ($duration <= 0) {
                    $catRow = sqlQuery(
                        "SELECT pc_duration FROM openemr_postcalendar_categories WHERE pc_catid = ?",
                        [(int)$type['id']]
                    );
                    $catDur = cs_normalize_duration_minutes((int)($catRow['pc_duration'] ?? 0));
                    $duration = $catDur > 0 ? $catDur : 30;
                }

                $startMinute = cs_time_minutes_from_text($text);
                if ($startMinute === null) {
                    $startMinute = 8 * 60;
                }

                $range = cs_time_range_from_text($text);
                if ($range === null) {
                    if (preg_match('/\bmornings?\b/i', $text)) {
                        $range = ['start' => 8 * 60, 'end' => 12 * 60];
                    } elseif (preg_match('/\bafternoons?\b/i', $text)) {
                        $range = ['start' => 12 * 60, 'end' => 17 * 60];
                    } elseif (preg_match('/\bevenings?\b/i', $text)) {
                        $range = ['start' => 17 * 60, 'end' => 21 * 60];
                    }
                }
                if ($range !== null) {
                    $startMinute = (int)$range['start'];
                    if ($count <= 0) {
                        $window = (int)$range['end'] - (int)$range['start'];
                        $count = (int)floor($window / max(5, $duration));
                    }
                }

                $count = max(1, min(120, $count > 0 ? $count : 1));

                cs_json_exit([
                    'success' => true,
                    'template' => [
                        'dayIdx' => (int)$weekdays[0],
                        'dayIndices' => array_values($weekdays),
                        'count' => $count,
                        'durationMinutes' => $duration,
                        'startMinute' => $startMinute,
                        'typeId' => (int)$type['id'],
                        'typeName' => (string)$type['name'],
                        'color' => (string)$type['color'],
                        'providerId' => cs_guess_provider_id_from_prompt($text),
                        'facilityId' => cs_guess_facility_id_from_prompt($text),
                    ],
                ]);
            }
        } catch (\Throwable $ex) {
            cs_json_exit(['success' => false, 'error' => $ex->getMessage()]);
        }
    }
}

// ── Template snapshot / rollback / copy ─────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' &&
    in_array((string)($_POST['action'] ?? ''), ['save_snapshot','list_snapshots','restore_snapshot','copy_snapshot'], true)) {
    try {
        $snapAction = (string)($_POST['action'] ?? '');

        if ($snapAction === 'save_snapshot') {
            $providerId    = (int)($_POST['provider_id'] ?? 0);
            $snapName      = trim((string)($_POST['snapshot_name'] ?? ''));
            $templateJson  = (string)($_POST['template_json'] ?? '');
            $notes         = trim((string)($_POST['notes'] ?? ''));
            if ($providerId <= 0 || $templateJson === '' || json_decode($templateJson) === null) {
                cs_json_exit(['success' => false, 'error' => 'Invalid snapshot data.']);
            }
            if ($snapName === '') {
                $snapName = 'Snapshot ' . date('Y-m-d H:i');
            }
            $userId = (int)($_SESSION['authUserID'] ?? 0);
            sqlInsert(
                "INSERT INTO medex_studio_snapshots (provider_id, snapshot_name, created_by, template_json, notes)
                 VALUES (?, ?, ?, ?, ?)",
                [$providerId, $snapName, $userId, $templateJson, $notes]
            );
            $sid = sqlQuery("SELECT LAST_INSERT_ID() AS sid")['sid'] ?? 0;
            cs_json_exit(['success' => true, 'snapshot_id' => (int)$sid, 'message' => 'Snapshot saved.']);
        }

        if ($snapAction === 'list_snapshots') {
            // provider_id=0 → return all practice snapshots (practice-wide)
            $filterPid = (int)($_POST['provider_id'] ?? 0);
            $rows = [];
            $res = $filterPid > 0
                ? sqlStatement(
                    "SELECT s.snapshot_id, s.snapshot_name, s.created_at, s.notes, s.provider_id,
                            TRIM(CONCAT(COALESCE(u.lname,''), ', ', COALESCE(u.fname,''))) AS provider_name
                     FROM medex_studio_snapshots s
                     LEFT JOIN users u ON u.id = s.provider_id
                     WHERE s.provider_id = ?
                     ORDER BY s.snapshot_id DESC LIMIT 100",
                    [$filterPid]
                )
                : sqlStatement(
                    "SELECT s.snapshot_id, s.snapshot_name, s.created_at, s.notes, s.provider_id,
                            TRIM(CONCAT(COALESCE(u.lname,''), ', ', COALESCE(u.fname,''))) AS provider_name
                     FROM medex_studio_snapshots s
                     LEFT JOIN users u ON u.id = s.provider_id
                     ORDER BY s.provider_id, s.snapshot_id DESC LIMIT 200"
                );
            while ($r = sqlFetchArray($res)) {
                $pName = trim((string)($r['provider_name'] ?? ''));
                if ($pName === ',' || $pName === '') {
                    $pName = 'Provider #' . (int)($r['provider_id'] ?? 0);
                }
                $rows[] = [
                    'id'           => (int)$r['snapshot_id'],
                    'name'         => (string)$r['snapshot_name'],
                    'date'         => (string)$r['created_at'],
                    'notes'        => (string)$r['notes'],
                    'providerId'   => (int)$r['provider_id'],
                    'providerName' => $pName,
                ];
            }
            cs_json_exit(['success' => true, 'snapshots' => $rows]);
        }

        if ($snapAction === 'restore_snapshot') {
            $snapId = (int)($_POST['snapshot_id'] ?? 0);
            if ($snapId <= 0) { cs_json_exit(['success' => false, 'error' => 'snapshot_id required']); }
            $snap = sqlQuery("SELECT template_json, snapshot_name FROM medex_studio_snapshots WHERE snapshot_id = ? LIMIT 1", [$snapId]);
            if (empty($snap['template_json'])) { cs_json_exit(['success' => false, 'error' => 'Snapshot not found.']); }
            cs_json_exit(['success' => true, 'template_json' => $snap['template_json'], 'name' => $snap['snapshot_name']]);
        }

        if ($snapAction === 'copy_snapshot') {
            $snapId       = (int)($_POST['snapshot_id'] ?? 0);
            $targetProv   = (int)($_POST['target_provider_id'] ?? 0);
            $newName      = trim((string)($_POST['new_name'] ?? ''));
            if ($snapId <= 0 || $targetProv <= 0) { cs_json_exit(['success' => false, 'error' => 'snapshot_id and target_provider_id required']); }
            $snap = sqlQuery("SELECT template_json, snapshot_name FROM medex_studio_snapshots WHERE snapshot_id = ? LIMIT 1", [$snapId]);
            if (empty($snap['template_json'])) { cs_json_exit(['success' => false, 'error' => 'Snapshot not found.']); }
            $newSnapName = $newName !== '' ? $newName : ('Copy of ' . $snap['snapshot_name']);
            $userId = (int)($_SESSION['authUserID'] ?? 0);
            sqlInsert(
                "INSERT INTO medex_studio_snapshots (provider_id, snapshot_name, created_by, template_json, notes)
                 VALUES (?, ?, ?, ?, ?)",
                [$targetProv, $newSnapName, $userId, $snap['template_json'], 'Copied from snapshot #' . $snapId]
            );
            cs_json_exit(['success' => true, 'message' => 'Template copied to provider #' . $targetProv]);
        }
    } catch (\Throwable $ex) {
        cs_json_exit(['success' => false, 'error' => $ex->getMessage()]);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string)($_POST['action'] ?? '') === 'deploy_template') {
    try {
        $payloadRaw = (string)($_POST['payload'] ?? '');
        $payload = json_decode($payloadRaw, true);
        if (!is_array($payload)) {
            cs_json_exit(['success' => false, 'error' => 'Invalid deploy payload.']);
        }

        $slots = is_array($payload['slots'] ?? null) ? $payload['slots'] : [];
        if (empty($slots)) {
            cs_json_exit(['success' => false, 'error' => 'No slots to deploy.']);
        }

        $providerIds = is_array($payload['provider_ids'] ?? null) ? array_values(array_unique(array_map('intval', $payload['provider_ids']))) : [];
        $providerIds = array_values(array_filter($providerIds, static function ($id) {
            return $id > 0;
        }));
        if (empty($providerIds)) {
            cs_json_exit(['success' => false, 'error' => 'Select at least one provider before deploy.']);
        }

        $facilityId   = (int)($payload['facility_id'] ?? 0);
        $horizonDays  = max(30, min(730, (int)($payload['horizon_days'] ?? 30)));
        $rawStartDate = trim((string)($payload['start_date'] ?? ''));
        $cadence      = trim((string)($payload['cadence'] ?? 'weekly'));
        $weekAStart   = trim((string)($payload['week_a_start'] ?? ''));   // four_week only
        $monthlyMode  = trim((string)($payload['monthly_mode'] ?? 'nth_weekday')); // monthly
        // Monthly Ns: support multiple occurrences (e.g. [2,4] = 2nd AND 4th Thursday)
        $rawNs = $payload['monthly_ns'] ?? null;
        if (is_string($rawNs)) { $rawNs = json_decode($rawNs, true); }
        $monthlyNs = [];
        if (is_array($rawNs) && !empty($rawNs)) {
            foreach ($rawNs as $nv) {
                $nv = max(1, min(5, (int)$nv));
                $monthlyNs[] = $nv;
            }
            $monthlyNs = array_values(array_unique($monthlyNs));
        }
        if (empty($monthlyNs)) {
            $monthlyNs = [max(1, min(5, (int)($payload['monthly_n'] ?? 1)))];
        }
        $monthlyWeekday = max(0, min(6, (int)($payload['monthly_weekday'] ?? 0))); // 0=Mon
        $monthlyDate  = max(1, min(31, (int)($payload['monthly_date'] ?? 1)));     // date-of-month
        $sourceTag = 'MEDEX_STUDIO';

        $hasLocation = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_location'"));
        $hasFacility = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_facility'"));

        $facilityIds = [];
        $facilityStmt = sqlStatement("SELECT id FROM facility");
        while ($facilityRow = sqlFetchArray($facilityStmt)) {
            $facilityIds[(int)($facilityRow['id'] ?? 0)] = true;
        }
        if (empty($facilityIds[$facilityId])) {
            $facilityId = 0;
        }

        $slotTemplates = [];
        $weekdaySet = [];
        foreach ($slots as $slot) {
            $dayIdx = (int)($slot['dayIdx'] ?? -1);
            $minute = (int)($slot['minute'] ?? -1);
            $typeId = (int)($slot['typeId'] ?? 0);
            $typeName = trim((string)($slot['typeName'] ?? 'Open Slot'));
            if ($dayIdx < 0 || $dayIdx > 6 || $minute < 0 || $minute > 1439 || $typeId <= 0) {
                continue;
            }
            $durationMinutes = (int)($slot['durationMinutes'] ?? 30);
            if ($durationMinutes <= 0) {
                $durationMinutes = 30;
            }
            $durationMinutes = max(5, min(240, $durationMinutes));
            $slotFacilityId = (int)($slot['facilityId'] ?? 0);
            if (empty($facilityIds[$slotFacilityId])) {
                $slotFacilityId = $facilityId;
            }
            $weekIndex = max(0, min(3, (int)($slot['weekIndex'] ?? 0))); // 0=A,1=B,2=C,3=D
            $key = $dayIdx . '|' . $minute . '|' . $weekIndex;
            $slotTemplates[$key] = [
                'dayIdx'          => $dayIdx,
                'minute'          => $minute,
                'weekIndex'       => $weekIndex,
                'typeId'          => $typeId,
                'typeName'        => $typeName !== '' ? $typeName : 'Open Slot',
                'durationMinutes' => $durationMinutes,
                'facilityId'      => $slotFacilityId,
            ];
            $weekdaySet[$dayIdx] = true;
        }
        if (empty($slotTemplates)) {
            cs_json_exit(['success' => false, 'error' => 'No valid slots to deploy.']);
        }

        $today   = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawStartDate))
            ? new DateTimeImmutable($rawStartDate)
            : new DateTimeImmutable('today');
        $lastDay = $today->modify('+' . ($horizonDays - 1) . ' days');
        $rangeStart = $today->format('Y-m-d');
        $rangeEnd   = $lastDay->format('Y-m-d');

        // Build date → slot mapping based on cadence.
        // $dateSlots[date][] = slotTemplate
        $dateSlots = [];

        if ($cadence === 'four_week') {
            // 4-week rotation. weekAStart must be a valid date; default to deploy start.
            $weekARef = preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekAStart)
                ? new DateTimeImmutable($weekAStart)
                : $today;
            // Normalise to Monday of that week.
            $weekAMon = $weekARef->modify('monday this week');
            if ($weekAMon > $weekARef) {
                $weekAMon = $weekARef->modify('last monday');
            }
            $weekATs = $weekAMon->getTimestamp();

            for ($d = $today; $d <= $lastDay; $d = $d->modify('+1 day')) {
                $weekday   = (int)$d->format('N') - 1; // 0=Mon..6=Sun
                // Start of the ISO week containing $d (Monday)
                $monOfWeek = $d->modify('monday this week');
                if ($monOfWeek > $d) {
                    $monOfWeek = $d->modify('last monday');
                }
                $diffWeeks  = (int)round(($monOfWeek->getTimestamp() - $weekATs) / 604800);
                $weekIdx    = (($diffWeeks % 4) + 4) % 4; // 0=A,1=B,2=C,3=D
                $dateStr    = $d->format('Y-m-d');
                foreach ($slotTemplates as $tpl) {
                    if ($tpl['dayIdx'] === $weekday && $tpl['weekIndex'] === $weekIdx) {
                        $dateSlots[$dateStr][] = $tpl;
                    }
                }
            }
        } elseif ($cadence === 'monthly') {
            // Monthly: find matching dates in each month of the range.
            for ($d = $today; $d <= $lastDay; ) {
                $year  = (int)$d->format('Y');
                $month = (int)$d->format('m');
                $targetDate = null;

                if ($monthlyMode === 'nth_weekday') {
                    // Find each checked occurrence of $monthlyWeekday in this month.
                    $isoDay = $monthlyWeekday + 1; // 0=Mon→ISO 1
                    $firstOfMonth = new DateTimeImmutable("$year-$month-01");
                    $firstIso = (int)$firstOfMonth->format('N');
                    $offset   = ($isoDay - $firstIso + 7) % 7;
                    $firstOccurrence = $firstOfMonth->modify("+{$offset} days");

                    $candidateDates = [];
                    foreach ($monthlyNs as $nVal) {
                        $occ = $firstOccurrence->modify('+' . ($nVal - 1) . ' weeks');
                        if ((int)$occ->format('m') === $month && $occ >= $today && $occ <= $lastDay) {
                            $candidateDates[] = $occ->format('Y-m-d');
                        }
                    }
                    // Add all candidate dates to dateSlots
                    foreach ($candidateDates as $cDate) {
                        foreach ($slotTemplates as $tpl) {
                            $dateSlots[$cDate][] = $tpl;
                        }
                    }
                    $targetDate = null; // handled above, skip the generic block below
                } elseif ($monthlyMode === 'date_of_month') {
                    $daysInMonth = (int)(new DateTimeImmutable("$year-$month-01"))->format('t');
                    $day = min($monthlyDate, $daysInMonth);
                    $targetDate = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
                }

                if ($targetDate !== null && $targetDate >= $today && $targetDate <= $lastDay) {
                    $dateStr = $targetDate->format('Y-m-d');
                    foreach ($slotTemplates as $tpl) {
                        $dateSlots[$dateStr][] = $tpl;
                    }
                }

                // Advance to next month
                $d = $d->modify('first day of next month');
            }
        } else {
            // Weekly / workdays / one_day — original behaviour.
            for ($d = $today; $d <= $lastDay; $d = $d->modify('+1 day')) {
                $weekday = (int)$d->format('N') - 1;
                if (!isset($weekdaySet[$weekday])) { continue; }
                $dateStr = $d->format('Y-m-d');
                foreach ($slotTemplates as $tpl) {
                    if ($tpl['dayIdx'] === $weekday && $tpl['weekIndex'] === 0) {
                        $dateSlots[$dateStr][] = $tpl;
                    }
                }
            }
        }

        // Full future wipe: delete ALL Open Slot events for these providers from the deploy
        // start date forward. This removes both the deploy range AND any old long-horizon
        // slots (e.g. from a previous 2-year deploy) that would otherwise linger and
        // pollute the Calendar Studio inference with stale high-count patterns.
        // New slots are then inserted only for the configured horizon.
        foreach ($providerIds as $providerId) {
            sqlStatement(
                "DELETE FROM openemr_postcalendar_events
                 WHERE pc_aid = ?
                   AND pc_eventDate >= ?
                   AND COALESCE(pc_pid,'') = ''
                   AND pc_title LIKE 'Open Slot%'",
                [$providerId, $rangeStart]
            );
        }

        $inserted = 0;
        foreach ($providerIds as $providerId) {
            foreach ($dateSlots as $date => $tplList) {
              foreach ($tplList as $tpl) {
                $minute = (int)$tpl['minute'];
                $typeId = (int)$tpl['typeId'];
                $typeName = (string)$tpl['typeName'];
                $durationMinutes = (int)$tpl['durationMinutes'];
                $tplFacilityId = (int)($tpl['facilityId'] ?? $facilityId);
                $startHour = intdiv($minute, 60);
                $startMin = $minute % 60;
                $start = sprintf('%02d:%02d:00', $startHour, $startMin);

                {
                    $startTs = strtotime($date . ' ' . $start);
                    if ($startTs === false) {
                        continue;
                    }
                    $endTs = $startTs + ($durationMinutes * 60);
                    $end = date('H:i:s', $endTs);

                    // Skip inserting an open slot if a real patient appointment already
                    // occupies this provider+date+time window. This prevents the deploy
                    // from creating phantom open slots on top of booked appointments.
                    $conflictRow = sqlQuery(
                        "SELECT pc_eid FROM openemr_postcalendar_events
                         WHERE pc_aid = ? AND pc_eventDate = ?
                           AND pc_pid > 0 AND pc_pid != ''
                           AND pc_startTime < ? AND pc_endTime > ?
                         LIMIT 1",
                        [$providerId, $date, $end, $start]
                    );
                    if (!empty($conflictRow['pc_eid'])) {
                        continue;
                    }

                    if ($hasLocation && $hasFacility) {
                        sqlInsert(
                            "INSERT INTO openemr_postcalendar_events
                             (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                              pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
                             VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, ?, ?)",
                            [$typeId, $providerId, 'Open Slot - ' . $typeName, $date, $date, $durationMinutes * 60, $start, $end, $typeId, $sourceTag, $tplFacilityId]
                        );
                    } elseif ($hasLocation) {
                        sqlInsert(
                            "INSERT INTO openemr_postcalendar_events
                             (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                              pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                             VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, ?)",
                            [$typeId, $providerId, 'Open Slot - ' . $typeName, $date, $date, $durationMinutes * 60, $start, $end, $typeId, $sourceTag]
                        );
                    } else {
                        sqlInsert(
                            "INSERT INTO openemr_postcalendar_events
                             (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                              pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
                             VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?)",
                            [$typeId, $providerId, 'Open Slot - ' . $typeName, $date, $date, $durationMinutes * 60, $start, $end, $typeId]
                        );
                    }
                    $inserted++;
                } // end date block
              } // end tpl loop
            } // end dateSlots loop
        } // end providers loop

        cs_json_exit([
            'success'   => true,
            'inserted'  => $inserted,
            'providers' => count($providerIds),
            'days'      => $horizonDays,
            'cadence'   => $cadence,
        ]);
    } catch (\Throwable $ex) {
        cs_json_exit(['success' => false, 'error' => $ex->getMessage()]);
    }
}

$providers = [];
$providerRes = sqlStatement(
    "SELECT id, username, fname, lname
     FROM users
     WHERE authorized = 1 AND active = 1 AND calendar = 1
     ORDER BY lname, fname"
);
while ($row = sqlFetchArray($providerRes)) {
    $providers[] = [
        'id' => (int)($row['id'] ?? 0),
        'username' => (string)($row['username'] ?? ''),
        'name' => trim((string)($row['lname'] ?? '') . ', ' . (string)($row['fname'] ?? '')),
    ];
}

$facilities = [];
$defaultFacilityId = 0;
try {
    $facilityRes = sqlStatement("SELECT id, name FROM facility ORDER BY name");
    while ($frow = sqlFetchArray($facilityRes)) {
        $facilities[] = [
            'id' => (int)($frow['id'] ?? 0),
            'name' => (string)($frow['name'] ?? ''),
        ];
        if ($defaultFacilityId === 0) {
            $defaultFacilityId = (int)($frow['id'] ?? 0); // first facility = default
        }
    }
} catch (\Throwable $ignored) {
    $facilities = [];
}

// Auto-create medex_category_prefs (per-type facility preference)
try {
    sqlStatement("CREATE TABLE IF NOT EXISTS medex_category_prefs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pc_catid INT NOT NULL,
        facility_id INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_catid (pc_catid)
    )");
} catch (\Throwable $ignored) {}

// Auto-create medex_studio_snapshots (template version history)
try {
    sqlStatement("CREATE TABLE IF NOT EXISTS medex_studio_snapshots (
        snapshot_id INT AUTO_INCREMENT PRIMARY KEY,
        provider_id INT NOT NULL,
        snapshot_name VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_by INT,
        template_json MEDIUMTEXT,
        notes TEXT
    )");
} catch (\Throwable $ignored) {}

$appointmentTypes = cs_load_appointment_types();

$reschedulerPausedRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_rescheduler_paused' LIMIT 1");
$reschedulerPaused = (($reschedulerPausedRow['gl_value'] ?? '0') === '1');

$schedulingRulesRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_scheduling_rules' LIMIT 1");
$schedulingRules = json_decode((string)($schedulingRulesRow['gl_value'] ?? ''), true);
if (!is_array($schedulingRules)) {
    $schedulingRules = ['template_enforcement' => 'guideline', 'allow_double_booking' => true];
}
$srEnforcement    = (string)($schedulingRules['template_enforcement'] ?? 'guideline');
$srAllowDblBook   = (bool)($schedulingRules['allow_double_booking'] ?? true);

$foundPatterns = [];
try {
    $patternRes = sqlStatement(
        "SELECT
            WEEKDAY(pc_eventDate) AS weekday_idx,
            TIME_FORMAT(pc_startTime, '%H:%i') AS slot_time,
            COUNT(*) AS slot_count
         FROM openemr_postcalendar_events
         WHERE (COALESCE(pc_pid, '') = '' OR pc_pid = '0')
           AND (pc_title LIKE 'Open Slot%' OR pc_title LIKE 'In Office%' OR COALESCE(pc_location, '') LIKE 'MEDEX_%')
           AND pc_eventDate >= CURDATE()
         GROUP BY WEEKDAY(pc_eventDate), TIME_FORMAT(pc_startTime, '%H:%i')
         HAVING COUNT(*) >= 2
         ORDER BY slot_count DESC
         LIMIT 12"
    );

    while ($prow = sqlFetchArray($patternRes)) {
        $dow = (int)($prow['weekday_idx'] ?? -1);
        $time = (string)($prow['slot_time'] ?? '');
        $count = (int)($prow['slot_count'] ?? 0);
        if ($dow >= 0 && $dow <= 6 && $time !== '' && $count > 0) {
            $foundPatterns[] = [
                'weekday' => $dow,
                'time' => $time,
                'count' => $count,
            ];
        }
    }
} catch (\Throwable $ignored) {
    $foundPatterns = [];
}

$detectedTemplates = [];
$hasSavedDetectedTemplates = false;
try {
    if (!empty(sqlQuery("SHOW TABLES LIKE 'medex_schedule_templates'"))) {
        $templateRes = sqlStatement(
            "SELECT
                t.template_id,
                t.provider_id,
                COALESCE(t.template_name, '') AS template_name,
                t.day_of_week,
                TIME_FORMAT(t.start_time, '%H:%i') AS start_time,
                TIME_FORMAT(t.end_time, '%H:%i') AS end_time,
                COALESCE(t.slot_duration, 0) AS slot_duration,
                COALESCE(t.preferred_category_id, 0) AS preferred_category_id,
                COALESCE(cat.pc_catname, '') AS preferred_category_name,
                COALESCE(cat.pc_catcolor, '') AS preferred_category_color,
                COALESCE(u.fname, '') AS provider_fname,
                COALESCE(u.lname, '') AS provider_lname,
                COALESCE(u.username, '') AS provider_username
             FROM medex_schedule_templates t
             LEFT JOIN users u ON u.id = t.provider_id
             LEFT JOIN openemr_postcalendar_categories cat ON cat.pc_catid = t.preferred_category_id
             ORDER BY t.provider_id, t.template_id, t.day_of_week, t.start_time"
        );

        $groupedTemplates = [];
        while ($row = sqlFetchArray($templateRes)) {
            $rawDow = (int)($row['day_of_week'] ?? -1);
            $dayIdx = -1;
            if ($rawDow >= 0 && $rawDow <= 6) {
                $dayIdx = $rawDow;
            } elseif ($rawDow >= 1 && $rawDow <= 7) {
                // Map MySQL DAYOFWEEK (Sun=1..Sat=7) to Monday=0..Sunday=6.
                $dayIdx = ($rawDow + 5) % 7;
            }
            if ($dayIdx < 0 || $dayIdx > 6) {
                continue;
            }

            $start = (string)($row['start_time'] ?? '');
            $end = (string)($row['end_time'] ?? '');
            if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                continue;
            }

            $providerName = trim((string)($row['provider_lname'] ?? '') . ', ' . (string)($row['provider_fname'] ?? ''));
            if ($providerName === ',' || $providerName === '') {
                $providerName = trim((string)($row['provider_username'] ?? ''));
            }
            if ($providerName === '') {
                $providerName = 'Provider #' . (int)($row['provider_id'] ?? 0);
            }

            $templateName = trim((string)($row['template_name'] ?? ''));
            if ($templateName === '') {
                $templateName = 'Template ' . (int)($row['template_id'] ?? 0);
            }

            $templateKey = (int)($row['provider_id'] ?? 0) . '|' . $templateName;
            if (!isset($groupedTemplates[$templateKey])) {
                $groupedTemplates[$templateKey] = [
                    'providerId' => (int)($row['provider_id'] ?? 0),
                    'providerName' => $providerName,
                    'templateName' => $templateName,
                    'blocks' => [],
                    'slotCount' => 0,
                ];
            }

            $duration = cs_normalize_duration_minutes($row['slot_duration'] ?? 30);
            $startParts = explode(':', $start);
            $endParts = explode(':', $end);
            $startMinute = ((int)$startParts[0] * 60) + (int)$startParts[1];
            $endMinute = ((int)$endParts[0] * 60) + (int)$endParts[1];
            if ($endMinute <= $startMinute) {
                continue;
            }

            $rawColor = trim((string)($row['preferred_category_color'] ?? ''));
            if ($rawColor !== '' && $rawColor[0] !== '#') {
                $rawColor = '#' . $rawColor;
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) {
                $rawColor = '#1d4ed8';
            }

            $blockSlotCount = (int)floor(($endMinute - $startMinute) / max(1, $duration));
            if ($blockSlotCount < 1) {
                $blockSlotCount = 1;
            }
            $groupedTemplates[$templateKey]['slotCount'] += $blockSlotCount;
            $typeId = (int)($row['preferred_category_id'] ?? 0);
            $typeName = trim((string)($row['preferred_category_name'] ?? ''));
            if ($typeName === '' && $typeId > 0) {
                $typeName = 'Type #' . $typeId;
            }
            if ($typeName === '') {
                $typeName = 'Appointment';
            }
            $groupedTemplates[$templateKey]['blocks'][] = [
                'weekday' => $dayIdx,
                'startMinute' => $startMinute,
                'endMinute' => $endMinute,
                'slotDuration' => $duration,
                'typeId' => $typeId,
                'typeName' => $typeName,
                'color' => $rawColor,
            ];
        }
        $detectedTemplates = array_values($groupedTemplates);
        $hasSavedDetectedTemplates = !empty($detectedTemplates);
    }

    // Pass 1: read MEDEX_STUDIO slots (forward-looking — the active deployed template).
    // Pass 2: if a provider has NO studio slots, look back historically for a sample pattern
    //         using any MEDEX_ source (MEDEX_INTERVIEW_GENERATED, etc.) to suggest what the
    //         schedule looked like. Mark these as 'sample' so the UI can label them distinctly.
    //         Providers with no pattern at all get a blank grid — no template is invented.
    if (empty($detectedTemplates)) {
        // Pass 1: active Studio-deployed template (forward from today)
        $inferRes = sqlStatement(
            "SELECT
                pc.pc_aid AS provider_id,
                DAYOFWEEK(pc.pc_eventDate) AS mysql_day_of_week,
                TIME_FORMAT(pc.pc_startTime, '%H:%i') AS start_time,
                TIME_FORMAT(pc.pc_endTime, '%H:%i') AS end_time,
                COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid, 0) AS preferred_category_id,
                COALESCE(cat.pc_catname, '') AS preferred_category_name,
                COALESCE(cat.pc_catcolor, '') AS preferred_category_color,
                COALESCE(u.fname, '') AS provider_fname,
                COALESCE(u.lname, '') AS provider_lname,
                COALESCE(u.username, '') AS provider_username,
                GROUP_CONCAT(DISTINCT DATE_FORMAT(pc.pc_eventDate, '%Y-%m-%d') ORDER BY pc.pc_eventDate SEPARATOR ',') AS event_dates,
                COUNT(*) AS slot_count,
                'studio' AS template_source
             FROM openemr_postcalendar_events pc
             LEFT JOIN users u ON u.id = pc.pc_aid
             LEFT JOIN openemr_postcalendar_categories cat ON cat.pc_catid = COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid)
             WHERE (COALESCE(pc.pc_pid, '') = '' OR pc.pc_pid = '0')
               AND pc.pc_eventDate >= CURDATE()
               AND pc.pc_title LIKE 'Open Slot%'
               AND COALESCE(pc.pc_location,'') = 'MEDEX_STUDIO'
             GROUP BY pc.pc_aid, DAYOFWEEK(pc.pc_eventDate), TIME_FORMAT(pc.pc_startTime, '%H:%i'), TIME_FORMAT(pc.pc_endTime, '%H:%i'), COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid, 0)
             HAVING COUNT(*) >= 2
             ORDER BY slot_count DESC, pc.pc_aid, start_time"
        );

        $dayNames = [
            0 => 'Monday',
            1 => 'Tuesday',
            2 => 'Wednesday',
            3 => 'Thursday',
            4 => 'Friday',
            5 => 'Saturday',
            6 => 'Sunday',
        ];
        $inferred = [];
        while ($row = sqlFetchArray($inferRes)) {
            $mysqlDow = (int)($row['mysql_day_of_week'] ?? 0);
            if ($mysqlDow < 1 || $mysqlDow > 7) {
                continue;
            }
            $dayIdx = ($mysqlDow + 5) % 7; // Mon=0..Sun=6
            if ($dayIdx < 0 || $dayIdx > 6) {
                continue;
            }

            $start = (string)($row['start_time'] ?? '');
            $end = (string)($row['end_time'] ?? '');
            if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                continue;
            }
            $startParts = explode(':', $start);
            $endParts = explode(':', $end);
            $startMinute = ((int)$startParts[0] * 60) + (int)$startParts[1];
            $endMinute = ((int)$endParts[0] * 60) + (int)$endParts[1];
            if ($endMinute <= $startMinute) {
                continue;
            }

            $providerId = (int)($row['provider_id'] ?? 0);
            if ($providerId <= 0) {
                continue;
            }

            $providerName = trim((string)($row['provider_lname'] ?? '') . ', ' . (string)($row['provider_fname'] ?? ''));
            if ($providerName === ',' || $providerName === '') {
                $providerName = trim((string)($row['provider_username'] ?? ''));
            }
            if ($providerName === '') {
                $providerName = 'Provider #' . $providerId;
            }

            $rawColor = trim((string)($row['preferred_category_color'] ?? ''));
            if ($rawColor !== '' && $rawColor[0] !== '#') {
                $rawColor = '#' . $rawColor;
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) {
                $rawColor = '#1d4ed8';
            }

            $templateSource = (string)($row['template_source'] ?? 'studio');
            $templateKey = $providerId . '|' . $templateSource;
            if (!isset($inferred[$templateKey])) {
                $inferred[$templateKey] = [
                    'providerId'    => $providerId,
                    'providerName'  => $providerName,
                    'templateName'  => $templateSource === 'sample' ? 'Sample Template (Historical)' : 'Deployed Template',
                    'templateSource' => $templateSource,
                    'isSample'      => $templateSource === 'sample',
                    'blocks'        => [],
                    'slotCount'     => 0,
                    'confidenceScore' => 0,
                    '_eventDates'   => [],
                ];
            }

            $slotCount = (int)($row['slot_count'] ?? 0);
            $durationGuess = max(5, min(240, $endMinute - $startMinute));
            $eventDateCsv = trim((string)($row['event_dates'] ?? ''));
            if ($eventDateCsv !== '') {
                foreach (explode(',', $eventDateCsv) as $eventDate) {
                    $eventDate = trim($eventDate);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
                        $inferred[$templateKey]['_eventDates'][$eventDate] = true;
                    }
                }
            }
            $inferred[$templateKey]['slotCount'] += max(1, $slotCount);
            $inferred[$templateKey]['confidenceScore'] += max(1, $slotCount);
            $inferred[$templateKey]['blocks'][] = [
                'weekday' => $dayIdx,
                'startMinute' => $startMinute,
                'endMinute' => $endMinute,
                'slotDuration' => $durationGuess,
                'typeId' => (int)($row['preferred_category_id'] ?? 0),
                'typeName' => trim((string)($row['preferred_category_name'] ?? '')),
                'color' => $rawColor,
            ];
        }

        foreach ($inferred as &$template) {
            usort($template['blocks'], static function (array $left, array $right): int {
                $cmp = ((int)($left['startMinute'] ?? 0)) <=> ((int)($right['startMinute'] ?? 0));
                if ($cmp !== 0) {
                    return $cmp;
                }
                return ((int)($left['endMinute'] ?? 0)) <=> ((int)($right['endMinute'] ?? 0));
            });

            $cadenceInfo = cs_infer_cadence_from_dates(array_keys((array)($template['_eventDates'] ?? [])));
            $template['cadence'] = (string)($cadenceInfo['cadence'] ?? 'weekly');
            $template['cadenceLabel'] = (string)($cadenceInfo['label'] ?? 'Weekly');
            $template['cadenceConfidence'] = (float)($cadenceInfo['confidence'] ?? 0.0);
            unset($template['_eventDates']);
        }
        unset($template);

        // Pass 2: for providers with NO studio-deployed template, look back historically
        // using any MEDEX_ source as a "Sample Template" starting point.
        // Only runs for providers that have no entry in $inferred from Pass 1.
        $studioProviders = [];
        foreach ($inferred as $tplKey => $tpl) {
            if (($tpl['templateSource'] ?? '') === 'studio') {
                $studioProviders[$tpl['providerId']] = true;
            }
        }

        $sampleRes = sqlStatement(
            "SELECT
                pc.pc_aid AS provider_id,
                DAYOFWEEK(pc.pc_eventDate) AS mysql_day_of_week,
                TIME_FORMAT(pc.pc_startTime, '%H:%i') AS start_time,
                TIME_FORMAT(pc.pc_endTime, '%H:%i') AS end_time,
                COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid, 0) AS preferred_category_id,
                COALESCE(cat.pc_catname, '') AS preferred_category_name,
                COALESCE(cat.pc_catcolor, '') AS preferred_category_color,
                COALESCE(u.fname, '') AS provider_fname,
                COALESCE(u.lname, '') AS provider_lname,
                COALESCE(u.username, '') AS provider_username,
                GROUP_CONCAT(DISTINCT DATE_FORMAT(pc.pc_eventDate, '%Y-%m-%d') ORDER BY pc.pc_eventDate SEPARATOR ',') AS event_dates,
                COUNT(*) AS slot_count,
                'sample' AS template_source
             FROM openemr_postcalendar_events pc
             LEFT JOIN users u ON u.id = pc.pc_aid
             LEFT JOIN openemr_postcalendar_categories cat ON cat.pc_catid = COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid)
             WHERE (COALESCE(pc.pc_pid, '') = '' OR pc.pc_pid = '0')
               AND pc.pc_eventDate BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE()
               AND (pc.pc_title LIKE 'Open Slot%' OR COALESCE(pc.pc_location,'') LIKE 'MEDEX_%')
             GROUP BY pc.pc_aid, DAYOFWEEK(pc.pc_eventDate), TIME_FORMAT(pc.pc_startTime, '%H:%i'), TIME_FORMAT(pc.pc_endTime, '%H:%i'), COALESCE(NULLIF(pc.pc_prefcatid, 0), pc.pc_catid, 0)
             HAVING COUNT(*) >= 3
             ORDER BY slot_count DESC, pc.pc_aid, start_time"
        );

        while ($row = sqlFetchArray($sampleRes)) {
            $providerId = (int)($row['provider_id'] ?? 0);
            // Skip providers that already have a Studio template
            if (isset($studioProviders[$providerId])) { continue; }

            $mysqlDow = (int)($row['mysql_day_of_week'] ?? 0);
            if ($mysqlDow < 1 || $mysqlDow > 7) { continue; }
            $dayIdx = ($mysqlDow + 5) % 7;

            $start = (string)($row['start_time'] ?? '');
            $end   = (string)($row['end_time'] ?? '');
            if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) { continue; }

            $startParts = explode(':', $start);
            $endParts   = explode(':', $end);
            $startMinute = ((int)$startParts[0] * 60) + (int)$startParts[1];
            $endMinute   = ((int)$endParts[0] * 60) + (int)$endParts[1];
            if ($endMinute <= $startMinute) { continue; }
            if ($providerId <= 0) { continue; }

            $providerName = trim((string)($row['provider_lname'] ?? '') . ', ' . (string)($row['provider_fname'] ?? ''));
            if ($providerName === ',' || $providerName === '') {
                $providerName = trim((string)($row['provider_username'] ?? ''));
            }
            if ($providerName === '') { $providerName = 'Provider #' . $providerId; }

            $rawColor = trim((string)($row['preferred_category_color'] ?? ''));
            if ($rawColor !== '' && $rawColor[0] !== '#') { $rawColor = '#' . $rawColor; }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) { $rawColor = '#1d4ed8'; }

            $templateKey = $providerId . '|sample';
            if (!isset($inferred[$templateKey])) {
                $inferred[$templateKey] = [
                    'providerId'     => $providerId,
                    'providerName'   => $providerName,
                    'templateName'   => 'Sample Template (Historical)',
                    'templateSource' => 'sample',
                    'isSample'       => true,
                    'blocks'         => [],
                    'slotCount'      => 0,
                    'confidenceScore' => 0,
                    '_eventDates'    => [],
                ];
            }

            $slotCount = (int)($row['slot_count'] ?? 0);
            $durationGuess = max(5, min(240, $endMinute - $startMinute));
            $eventDateCsv = trim((string)($row['event_dates'] ?? ''));
            if ($eventDateCsv !== '') {
                foreach (explode(',', $eventDateCsv) as $eventDate) {
                    $eventDate = trim($eventDate);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
                        $inferred[$templateKey]['_eventDates'][$eventDate] = true;
                    }
                }
            }
            $inferred[$templateKey]['slotCount'] += max(1, $slotCount);
            $inferred[$templateKey]['confidenceScore'] += max(1, $slotCount);
            $inferred[$templateKey]['blocks'][] = [
                'weekday'      => $dayIdx,
                'startMinute'  => $startMinute,
                'endMinute'    => $endMinute,
                'slotDuration' => $durationGuess,
                'typeId'       => (int)($row['preferred_category_id'] ?? 0),
                'typeName'     => trim((string)($row['preferred_category_name'] ?? '')),
                'color'        => $rawColor,
            ];
        }

        $detectedTemplates = array_values($inferred);
        usort($detectedTemplates, static function (array $left, array $right): int {
            $scoreCmp = ((int)($right['confidenceScore'] ?? 0)) <=> ((int)($left['confidenceScore'] ?? 0));
            if ($scoreCmp !== 0) { return $scoreCmp; }
            return ((int)($right['slotCount'] ?? 0)) <=> ((int)($left['slotCount'] ?? 0));
        });
    }
} catch (\Throwable $ignored) {
    $detectedTemplates = [];
}

$patternCount = count($foundPatterns);
$templateCount = count($detectedTemplates);
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@melloware/coloris/dist/coloris.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@melloware/coloris/dist/coloris.min.js"></script>
    <title><?php echo xlt('Calendar Service Studio'); ?></title>
    <style>
        :root {
            --cs-bg: #f5f8fa;
            --cs-surface: #ffffff;
            --cs-ink: #0f1f2d;
            --cs-subtle: #4a617a;
            --cs-border: #cfd9e3;
            --cs-accent: #1c4568;
            --cs-accent-soft: #dbeafe;
            --cs-danger: #b91c1c;
            --cs-grid-head: #e8eef5;
            --cs-grid-hover: #eff6ff;
            --cs-pattern: #fef3c7;
            --cs-shadow: 0 10px 24px rgba(0, 28, 56, 0.08);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {
            font-family: "Avenir Next", "Segoe UI", sans-serif;
            background: radial-gradient(circle at 8% 0%, #eef4ff, var(--cs-bg) 56%);
            color: var(--cs-ink);
        }

        .cs-shell {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 6px 14px 24px;
        }

        .cs-topbar {
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            padding: 4px 2px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            border-bottom: 1px solid var(--cs-border);
            margin-bottom: 2px;
        }

        .cs-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.2px;
        }

        .cs-subtitle {
            margin: 4px 0 0;
            color: var(--cs-subtle);
            font-size: 13px;
        }

        .cs-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .cs-title-breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: var(--cs-subtle);
            flex-wrap: wrap;
        }
        .cs-title-breadcrumb a { color: var(--cs-accent); text-decoration: none; font-weight: 600; }
        .cs-title-breadcrumb a:hover { text-decoration: underline; }
        .cs-title-breadcrumb .bc-sep { color: var(--cs-subtle); user-select: none; }
        .cs-title-breadcrumb strong { color: var(--cs-ink); font-weight: 700; font-size: 14px; }

        .cs-provider-roster {
            background: var(--cs-surface);
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: var(--cs-shadow);
            padding: 14px 16px;
        }
        .provider-roster-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .provider-card {
            width: 190px;
            min-height: 110px;
            flex-shrink: 0;
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: 12px;
            background: #fff;
            display: flex;
            flex-direction: column;
            transition: box-shadow .12s ease, border-color .12s ease;
        }
        .provider-card.has-template {
            border-color: #93c5fd;
            background: #eff6ff;
        }
        .provider-card.active {
            border-color: var(--cs-accent);
            background: var(--cs-accent-soft);
            box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.2);
        }
        .provider-card-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--cs-ink);
            margin-bottom: 3px;
        }
        .provider-card-status {
            font-size: 11px;
            color: var(--cs-subtle);
            margin-bottom: 8px;
        }
        .provider-card-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; padding-top: 8px; justify-content: center; }

        .appt-types-hint-wrap { position: relative; }
        .appt-types-hint {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            width: 230px;
            background: #1c2d3a;
            color: #f0f8ff;
            border-radius: 8px;
            padding: 8px 11px;
            font-size: 11px;
            line-height: 1.5;
            box-shadow: 0 4px 16px rgba(0,0,0,0.28);
            z-index: 200;
            pointer-events: none;
        }
        .appt-types-hint-wrap:hover .appt-types-hint { display: block; }

        .cs-editor-header {
            background: var(--cs-surface);
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Help drawer */
        .ai-assist-drawer {
            display: none;
            background: var(--cs-surface);
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: var(--cs-shadow);
            padding: 14px 16px;
        }
        .ai-assist-drawer.open { display: flex; flex-direction: column; gap: 0; }
        .help-chat-history {
            max-height: 220px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 8px 0 4px;
            margin-top: 6px;
        }
        .help-bubble {
            border-radius: 10px;
            padding: 7px 11px;
            font-size: 12px;
            max-width: 88%;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .help-bubble.user { background: #1c4568; color: white; align-self: flex-end; }
        .help-bubble.ai { background: #f0f4f8; color: var(--cs-ink); align-self: flex-start; }
        .help-apply-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            background: var(--cs-accent);
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 11px;
            cursor: pointer;
            font-weight: 600;
        }
        .help-apply-btn:hover { background: #163953; }
        .ai-assist-input-row { display: flex; gap: 8px; margin-top: 8px; }
        .ai-assist-input-row input {
            flex: 1;
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 13px;
        }

        .btn {
            border: 1px solid var(--cs-border);
            background: var(--cs-surface);
            color: var(--cs-ink);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn.primary {
            border-color: var(--cs-accent);
            background: var(--cs-accent);
            color: #ffffff;
        }

        .btn.ghost {
            background: transparent;
        }

        .cs-config {
            background: var(--cs-surface);
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: var(--cs-shadow);
            padding: 10px 12px;
            display: grid;
            grid-template-columns: repeat(6, minmax(130px, 1fr));
            gap: 10px;
            align-items: start;
        }

        .field {
            display: grid;
            gap: 6px;
            align-content: start;
        }

        .field label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--cs-subtle);
            font-weight: 700;
        }

        .field select,
        .field input,
        .field .inline-group {
            border: 1px solid var(--cs-border);
            background: #fff;
            border-radius: 9px;
            padding: 7px 9px;
            font-size: 12px;
        }

        .field .inline-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .field .inline-group label {
            font-size: 12px;
            letter-spacing: normal;
            text-transform: none;
            margin: 0;
            color: var(--cs-ink);
            font-weight: 600;
        }

        .cs-main {
            display: grid;
            grid-template-columns: 260px 1fr 280px;
            gap: 12px;
            align-items: stretch;   /* all panels same height */
        }

        .panel {
            background: var(--cs-surface);
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: var(--cs-shadow);
            padding: 12px;
            overflow: auto;
            /* Side panels: fill remaining viewport, scroll internally */
            max-height: calc(100vh - 130px);
            min-height: 300px;
        }

        /* Grid panel scrolls through the time slots internally */
        .panel.calendar-wrap {
            overflow: auto;
            height: calc(100vh - 130px);
            min-height: 300px;
        }

        .panel h3 {
            margin: 0 0 10px;
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: var(--cs-subtle);
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        .section-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 0 0 10px;
        }

        .section-toggle {
            border: 0;
            background: transparent;
            color: var(--cs-subtle);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 13px;
            font-weight: 700;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
        }

        .section-toggle .icon {
            width: 14px;
            text-align: center;
            font-size: 11px;
            transition: transform .14s ease;
        }

        .section-toggle[aria-expanded="false"] .icon {
            transform: rotate(-90deg);
        }

        .section-body.is-collapsed {
            display: none;
        }

        .panel-head h3 {
            margin: 0;
        }

        .panel-head .btn {
            padding: 6px 10px;
            font-size: 11px;
            white-space: nowrap;
        }

        .manage-types-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(7, 20, 22, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .manage-types-modal.is-open {
            display: flex;
        }

        .rescheduler-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(7, 20, 22, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .rescheduler-modal.is-open {
            display: flex;
        }

        .rescheduler-dialog {
            width: min(860px, 96vw);
            max-height: min(88vh, 900px);
            background: #fff;
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: 0 22px 46px rgba(15, 35, 38, 0.28);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .rescheduler-dialog-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--cs-border);
            flex-shrink: 0;
        }

        .rescheduler-dialog-body {
            padding: 22px;
            overflow-y: auto;
            flex: 1;
        }

        .rp-pvbtn-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .rp-pvbtn-wrap .rp-pvbtn-del {
            display: none;
            position: absolute;
            right: -6px;
            top: -6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--cs-accent);
            color: #fff;
            font-size: 10px;
            line-height: 16px;
            text-align: center;
            cursor: pointer;
            border: none;
            padding: 0;
            z-index: 1;
        }

        .rp-pvbtn-wrap.has-override .rp-pvbtn-del {
            display: block;
        }

        .rp-pvbtn-wrap.has-override .rp-pvbtn {
            background: #dbeafe;
            color: var(--cs-accent);
            border-color: var(--cs-accent);
            font-weight: normal;
        }

        /* Coloris picker must float above everything */
        .clr-picker { z-index: 9999 !important; }

        .manage-types-dialog {
            width: min(1200px, 96vw);
            height: min(86vh, 900px);
            background: #ffffff;
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: 0 22px 46px rgba(15, 35, 38, 0.28);
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr;
        }

        .manage-types-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--cs-border);
            background: linear-gradient(180deg, #f0f6ff 0%, #ffffff 100%);
        }

        .manage-types-head strong {
            font-size: 13px;
            letter-spacing: 0.15px;
        }

        .manage-types-head .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .manage-types-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            min-height: 0;
            height: 100%;
        }

        .manage-types-list {
            border-right: 1px solid var(--cs-border);
            padding: 12px;
            overflow: auto;
            background: #f8faff;
        }

        .manage-types-form {
            padding: 12px;
            overflow: visible;
        }

        .type-row {
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: 8px 10px;
            margin-bottom: 8px;
            display: grid;
            grid-template-columns: 18px 16px 1fr auto;
            gap: 8px;
            align-items: center;
            background: #fff;
            transition: box-shadow 0.12s, opacity 0.12s;
        }

        .type-row.dragging {
            opacity: 0.4;
        }

        .type-row.drag-over {
            box-shadow: 0 0 0 2px var(--cs-accent);
            border-color: var(--cs-accent);
        }

        .type-row .drag-handle {
            cursor: grab;
            color: var(--cs-border);
            font-size: 14px;
            line-height: 1;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .type-row .drag-handle:active {
            cursor: grabbing;
        }

        .type-row .swatch {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            border: 1px solid rgba(0,0,0,0.2);
        }

        .type-row .meta strong {
            display: block;
            font-size: 12px;
        }

        .type-row .meta span {
            color: var(--cs-subtle);
            font-size: 11px;
        }

        .type-row .actions {
            display: flex;
            gap: 6px;
        }

        .manager-status {
            margin-top: 10px;
            min-height: 18px;
            font-size: 12px;
            color: var(--cs-subtle);
        }

        .manager-status.error {
            color: #b91c1c;
        }

        .manager-status.success {
            color: #1d4ed8;
        }

        .slot-edit-modal {
            position: fixed;
            inset: 0;
            z-index: 2100;
            background: rgba(7, 20, 22, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .slot-edit-modal.is-open {
            display: flex;
        }

        .slot-edit-dialog {
            width: min(520px, 96vw);
            background: #ffffff;
            border: 1px solid var(--cs-border);
            border-radius: 14px;
            box-shadow: 0 22px 46px rgba(15, 35, 38, 0.28);
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        .slot-edit-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-bottom: 1px solid var(--cs-border);
            background: linear-gradient(180deg, #f0f6ff 0%, #ffffff 100%);
        }

        .slot-edit-body {
            padding: 12px;
            display: grid;
            gap: 10px;
        }

        .slot-edit-body .field label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            color: var(--cs-subtle);
            font-weight: 700;
        }

        .slot-edit-rules {
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: 10px;
            background: #f0f6ff;
        }

        .slot-edit-rules label {
            display: block;
            margin: 0 0 6px;
            font-size: 13px;
            color: var(--cs-ink);
        }

        .slot-edit-foot {
            border-top: 1px solid var(--cs-border);
            padding: 10px 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        #typeLegend .chip {
            border: 1px solid rgba(0, 0, 0, 0.14);
            border-radius: 10px;
            padding: 8px 10px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: grab;
            color: #0f1f2d;
            user-select: none;
        }

        #typeLegend .chip small {
            display: block;
            font-size: 10px;
            opacity: 0.9;
            font-weight: 500;
            margin-top: 2px;
        }

        .patterns {
            margin-bottom: 12px;
            border: 1px dashed var(--cs-border);
            border-radius: 10px;
            padding: 10px;
            background: #f0f6ff;
        }

        .pattern-card {
            border: 1px solid var(--cs-border);
            border-radius: 9px;
            padding: 8px;
            margin-top: 8px;
            background: #fff;
        }

        .pattern-group {
            border: 1px solid #cfd9e3;
            border-radius: 10px;
            background: #f8faff;
            padding: 8px;
            margin-top: 10px;
        }

        .pattern-group:first-child {
            margin-top: 0;
        }

        .pattern-group-title {
            font-size: 12px;
            font-weight: 800;
            color: #1d3751;
            margin: 0 0 6px;
        }

        .pattern-card strong { font-size: 12px; }
        .pattern-card p { margin: 4px 0 8px; font-size: 11px; color: var(--cs-subtle); }

        /* The middle calendar panel must fill its grid cell and pass height to the grid. */
        .panel.calendar-wrap {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .calendar-wrap {
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 8px;
            min-height: 0;
            flex: 1;
            overflow: hidden;
        }

        .calendar-head {
            display: grid;
            grid-template-columns: 80px repeat(7, minmax(110px, 1fr));
            gap: 4px;
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--cs-bg, #f0f4f8);
            padding-bottom: 4px;
        }

        .calendar-head div {
            background: var(--cs-grid-head);
            border: 1px solid var(--cs-border);
            border-radius: 8px;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            font-weight: 700;
            color: #1f3a52;
            text-align: center;
        }

        .header-facility-label {
            font-size: 9px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
            color: var(--cs-subtle);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .facility-change-banner {
            font-size: 9px;
            font-weight: 600;
            color: var(--cs-accent);
            background: rgba(15, 118, 110, 0.07);
            border-top: 1px solid rgba(15, 118, 110, 0.25);
            border-bottom: 1px solid rgba(15, 118, 110, 0.25);
            padding: 1px 4px;
            margin-bottom: 1px;
            white-space: nowrap;
        }
        .calendar-grid {
            overflow: auto;
            height: 100%;
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            background: #fdfefe;
        }

        .grid-row {
            display: grid;
            grid-template-columns: 80px repeat(7, minmax(110px, 1fr));
            gap: 4px;
            padding: 4px;
        }

        .time-cell {
            font-size: 11px;
            text-align: center;
            color: #405c72;
            align-self: center;
            font-weight: 700;
        }

        .slot-cell {
            min-height: 28px;
            border: 1px dashed #bfdbfe;
            border-radius: 8px;
            background: #fff;
            padding: 4px;
            transition: background .15s ease, border-color .15s ease;
            position: relative;
            overflow: visible;
        }

        .slot-cell:hover {
            background: var(--cs-grid-hover);
            border-color: #93c5fd;
        }

        .slot-cell.drop-target {
            border-color: var(--cs-accent);
            background: #dbeafe;
        }

        .slot-cell.multi-selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .slot-cell.primary-selected {
            outline: 2px solid #1d4ed8;
            outline-offset: -1px;
        }

        .slot-cell.primary-selected.span-anchor {
            outline: none;
        }

        .slot-cell.primary-selected .slot-tag {
            outline: 3px solid #1d4ed8;
            outline-offset: -2px;
        }

        .slot-cell.multi-selected .slot-tag {
            outline: 2px solid rgba(47, 113, 107, 0.55);
            outline-offset: -1px;
        }

        .slot-cell.slot-occupied {
            border-color: transparent;
            background: transparent;
        }

        .slot-cell.span-tail {
            border-color: transparent;
            background: transparent;
        }

        .slot-cell.span-anchor {
            z-index: 6;
            padding: 0;
        }

        .slot-tag.span-block {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            z-index: 8;
            display: grid;
            grid-template-rows: minmax(14px, auto) minmax(12px, auto) 1fr;
            align-content: start;
            overflow: hidden;
            border-radius: 10px;
            padding: 4px 6px;
            min-height: 0;
        }

        .slot-cell.pattern-preview {
            background: var(--cs-pattern);
        }

        .preview-ghost {
            margin-top: 3px;
            border: 1px dashed rgba(15, 118, 110, 0.55);
            border-radius: 7px;
            padding: 2px 5px;
            font-size: 10px;
            line-height: 1.2;
            color: #0f2a3f;
            background: rgba(255, 255, 255, 0.7);
            font-weight: 700;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .slot-tag {
            border-radius: 8px;
            padding: 3px 6px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            cursor: pointer;
            user-select: none;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
        }

        .slot-tag .slot-title {
            font-size: 11px;
            line-height: 1.1;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
            min-height: 14px;
        }

        .slot-tag .slot-options {
            font-size: 10px;
            line-height: 1.1;
            font-weight: 700;
            opacity: 0.9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 12px;
        }

        .slot-tag.cramped {
            padding: 4px 6px;
        }

        .slot-tag.cramped .slot-title {
            font-size: 11px;
            margin-bottom: 0;
        }

        .slot-tag.cramped .slot-options {
            display: block;
        }

        .slot-flags {
            display: flex;
            gap: 4px;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .flag {
            font-size: 9px;
            font-weight: 700;
            border-radius: 999px;
            padding: 2px 6px;
            border: 1px solid rgba(0,0,0,0.08);
            background: rgba(255,255,255,0.9);
            color: #1e3a52;
        }

        .flag.staff { background: #e0f2fe; }
        .flag.book { background: #dbeafe; }
        .flag.rebook { background: #ede9fe; }

        .empty-note {
            font-size: 12px;
            color: #6a8a8f;
            margin-top: 8px;
            line-height: 1.45;
        }

        .inspector-section {
            border-top: 1px solid var(--cs-border);
            padding-top: 10px;
            margin-top: 10px;
        }

        .slot-summary {
            background: var(--cs-accent-soft);
            border: 1px solid #93c5fd;
            border-radius: 8px;
            padding: 8px;
            font-size: 12px;
        }

        .muted {
            color: var(--cs-subtle);
            font-size: 11px;
        }

        @media (max-width: 1200px) {
            .cs-config {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
                align-items: start;
            }
            .cs-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .manage-types-dialog {
                width: 98vw;
                height: 92vh;
            }

            .manage-types-grid {
                grid-template-columns: 1fr;
            }

            .manage-types-list {
                border-right: 0;
                border-bottom: 1px solid var(--cs-border);
            }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="cs-shell">
    <div class="cs-topbar">
        <nav class="cs-title-breadcrumb">
            <a href="#" id="bcStudioLink" onclick="closeEditor();return false;"><?php echo xlt('Calendar Studio'); ?></a>
            <span class="bc-sep" id="bcProviderSlash" style="display:none;">/</span>
            <strong id="bcProviderName" style="display:none;"></strong>
        </nav>
        <div class="cs-actions">
            <span id="editorActions" style="display:none;">
                <button class="btn" type="button" id="btnClearAll"><?php echo xlt('Clear Draft'); ?></button>
                <button class="btn primary" type="button" id="btnDeployTemplate"><?php echo xlt('Deploy Template'); ?></button>
            </span>
        </div>
    </div>
    <div id="deployBanner" style="display:none;padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:4px;display:none;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;"></div>

    <input id="askPrompt" type="hidden" value="">
    <!-- AI assist drawer — hidden until Help workflow is wired in -->
    <div class="ai-assist-drawer" id="aiAssistDrawer" style="display:none !important;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px;">
            <strong style="font-size:13px;"><?php echo xlt('Schedule Help'); ?></strong>
            <div style="display:flex;gap:6px;">
                <button class="btn ghost" type="button" id="btnHelpClear" style="padding:3px 8px;font-size:11px;"><?php echo xlt('Clear'); ?></button>
                <button class="btn ghost" type="button" id="btnAiAssistClose" style="padding:3px 8px;font-size:11px;"><?php echo xlt('Close'); ?></button>
            </div>
        </div>
        <div id="helpChatHistory" class="help-chat-history"></div>
        <div class="ai-assist-input-row">
            <input type="text" id="aiAssistPrompt" placeholder="<?php echo attr(xl('Ask about this schedule…')); ?>">
            <button class="btn primary" type="button" id="btnAiAssistAsk"><?php echo xlt('Send'); ?></button>
        </div>
        <div id="aiAssistStatus" style="font-size:11px;color:var(--cs-subtle);margin-top:4px;display:none;"></div>
    </div>
    <!-- Slot hover tooltip -->
    <div id="slotTooltip" style="display:none;position:fixed;z-index:9999;pointer-events:none;
         background:#1c2d3a;color:#f0f8ff;border-radius:9px;padding:8px 12px;font-size:12px;
         box-shadow:0 4px 18px rgba(0,0,0,0.28);line-height:1.55;max-width:220px;"></div>

    <!-- cfgProvider is auto-set by the provider roster; hidden from user -->
    <select id="cfgProvider" style="display:none;" multiple size="3">
        <?php foreach ($providers as $p): ?>
            <option value="<?php echo attr((string)$p['id']); ?>"><?php echo text($p['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <div id="csConfigWrap" style="display:none;background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:4px 12px 2px;margin-bottom:4px;">
        <button type="button" id="btnToggleConfig"
                style="display:flex;align-items:center;gap:6px;background:none;border:none;cursor:pointer;font-size:12px;font-weight:600;color:var(--cs-accent);padding:4px 0 5px;width:100%;"
                onclick="(function(){var b=document.getElementById('csConfig');var a=document.getElementById('btnToggleConfigArrow');if(b.style.display==='none'){b.style.display='';a.textContent='▼';}else{b.style.display='none';a.textContent='►';}})()">
            <span id="btnToggleConfigArrow">►</span>
            <?php echo xlt('Deploy Settings'); ?>
            <span style="font-size:10px;font-weight:400;color:var(--cs-subtle);"><?php echo xlt('cadence · horizon · facility · rules'); ?></span>
        </button>
        <div class="cs-config" id="csConfig" style="display:none;">
        <!-- Facility is now set per-slot-type in Manage Types, not globally here -->
        <div class="field">
            <label><?php echo xlt('Template Cadence'); ?>
                <span title="One Day: same schedule every day you're at this location&#10;Every Workday: Mon–Fri same schedule&#10;Weekly: different schedule per weekday&#10;4-Week Rotation: alternating weeks (1st/3rd vs 2nd/4th, different facilities)&#10;Monthly: specific occurrence each month (every 2nd Tuesday)"
                      style="cursor:help;color:var(--cs-subtle);font-weight:400;margin-left:4px;">?</span>
            </label>
            <select id="cfgCadence">
                <option value="one_day"><?php echo xlt('One Day — same slots every visit'); ?></option>
                <option value="workdays"><?php echo xlt('Every Workday — Mon–Fri, same schedule'); ?></option>
                <option value="weekly" selected><?php echo xlt('Weekly — different pattern per weekday'); ?></option>
                <option value="four_week"><?php echo xlt('4-Week Rotation — alternating week patterns'); ?></option>
                <option value="monthly"><?php echo xlt('Monthly — specific occurrence each month'); ?></option>
            </select>
        </div>
        <!-- One Day: which day do you work this schedule? -->
        <div class="field" id="cfgOneDayPanel" style="display:none;">
            <label><?php echo xlt('Which day?'); ?></label>
            <select id="cfgOneDaySelect">
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $i => $d): ?>
                    <option value="<?php echo attr((string)$i); ?>"><?php echo text($d); ?></option>
                <?php endforeach; ?>
            </select>
            <span style="font-size:10px;color:var(--cs-subtle);display:block;margin-top:3px;"><?php echo xlt('Grid shows only this day. Deploy to any dates in the horizon.'); ?></span>
        </div>
        <div class="field">
            <label><?php echo xlt('Deploy Start Date'); ?></label>
            <input type="date" id="cfgStartDate" value="<?php echo attr(date('Y-m-d')); ?>"
                   style="width:100%;padding:4px 6px;border:1px solid var(--cs-border);border-radius:4px;box-sizing:border-box;">
        </div>
        <div class="field">
            <label><?php echo xlt('Deploy Horizon'); ?></label>
            <select id="cfgHorizonDays">
                <option value="30" selected><?php echo xlt('1 month (30 days)'); ?></option>
                <option value="60"><?php echo xlt('2 months (60 days)'); ?></option>
                <option value="90"><?php echo xlt('3 months (90 days)'); ?></option>
                <option value="180"><?php echo xlt('6 months (180 days)'); ?></option>
                <option value="365"><?php echo xlt('1 year (365 days)'); ?></option>
                <option value="730"><?php echo xlt('2 years (730 days)'); ?></option>
            </select>
        </div>
        <div class="field">
            <label><?php echo xlt('Scheduling Rule'); ?></label>
            <div class="inline-group">
                <label><input type="radio" name="cfgStrictness" value="strict"> <?php echo xlt('Strict Type Match'); ?></label>
                <label><input type="radio" name="cfgStrictness" value="guide" checked> <?php echo xlt('Guide Only For Staff'); ?></label>
            </div>
        </div>
        <div class="field">
            <label><?php echo xlt('Patient Options'); ?></label>
            <div class="inline-group">
                <label><input type="checkbox" id="cfgPatientBook"> <?php echo xlt('Enable Patient Booking'); ?></label>
                <label><input type="checkbox" id="cfgPatientRebook"> <?php echo xlt('Enable Patient Rescheduling'); ?></label>
            </div>
        </div>
        <div class="field">
            <label><?php echo xlt('Max Bookings / Slot'); ?></label>
            <input type="number" id="cfgMaxBookings" min="1" max="20" value="1" style="width:70px;">
            <span style="font-size:10px;color:var(--cs-subtle);"><?php echo xlt('Allow overbooking'); ?></span>
        </div>

        <!-- 4-Week Rotation options (shown when cadence = four_week) -->
        <div class="field" id="cfgFourWeekPanel" style="display:none;">
            <label><?php echo xlt('Week A Starts'); ?></label>
            <input type="date" id="cfgWeekAStart" value="<?php echo attr(date('Y-m-d')); ?>"
                   style="width:100%;padding:4px 6px;border:1px solid var(--cs-border);border-radius:4px;box-sizing:border-box;">
            <span style="font-size:10px;color:var(--cs-subtle);"><?php echo xlt('Pick the Monday that begins your Week A cycle'); ?></span>
        </div>

        <!-- Monthly options (shown when cadence = monthly) -->
        <div class="field" id="cfgMonthlyPanel" style="display:none;">
            <label><?php echo xlt('Monthly Pattern'); ?></label>
            <select id="cfgMonthlyMode">
                <option value="nth_weekday" selected><?php echo xlt('Nth weekday of month'); ?></option>
                <option value="date_of_month"><?php echo xlt('Specific date of month'); ?></option>
            </select>
            <div id="cfgMonthlyNthPanel" style="margin-top:6px;">
                <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:6px;" id="cfgMonthlyNChecks">
                    <?php foreach ([1=>'1st',2=>'2nd',3=>'3rd',4=>'4th',5=>'5th (Last)'] as $n => $lbl): ?>
                    <label style="display:flex;align-items:center;gap:3px;font-size:12px;cursor:pointer;padding:2px 6px;border:1px solid var(--cs-border);border-radius:4px;background:var(--cs-bg);">
                        <input type="checkbox" name="cfgMonthlyN" value="<?php echo attr((string)$n); ?>"
                               <?php echo $n <= 4 ? 'style="accent-color:var(--cs-accent);"' : ''; ?>>
                        <?php echo xlt($lbl); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <select id="cfgMonthlyWeekday" style="width:100%;">
                    <option value="0"><?php echo xlt('Monday'); ?></option>
                    <option value="1"><?php echo xlt('Tuesday'); ?></option>
                    <option value="2"><?php echo xlt('Wednesday'); ?></option>
                    <option value="3" selected><?php echo xlt('Thursday'); ?></option>
                    <option value="4"><?php echo xlt('Friday'); ?></option>
                    <option value="5"><?php echo xlt('Saturday'); ?></option>
                    <option value="6"><?php echo xlt('Sunday'); ?></option>
                </select>
                <span style="font-size:10px;color:var(--cs-subtle);display:block;margin-top:3px;"><?php echo xlt('Check each occurrence to include (e.g. 2nd and 4th Thursday).'); ?></span>
            </div>
            <div id="cfgMonthlyDatePanel" style="display:none;margin-top:6px;">
                <input type="number" id="cfgMonthlyDate" min="1" max="31" value="1" style="width:70px;">
                <span style="font-size:11px;color:var(--cs-subtle);"><?php echo xlt('of each month'); ?></span>
            </div>
        </div>
    </div><!-- /.cs-config -->
    </div><!-- /#csConfigWrap -->


    <?php
    // Build a provider → first-template map for the roster.
    $providerTemplateMap = [];
    foreach ($detectedTemplates as $_tpl) {
        $_pid = (int)($_tpl['providerId'] ?? 0);
        if ($_pid > 0 && !isset($providerTemplateMap[$_pid])) {
            $providerTemplateMap[$_pid] = $_tpl;
        }
    }
    $shortDayNamesRoster = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    ?>
    <div class="cs-provider-roster" id="providerRoster">

        <!-- ── Studio Settings (first row) ─────────────────────── -->
        <div style="margin-bottom:28px;border-bottom:1px solid var(--cs-border);padding-bottom:22px;">
            <strong style="font-size:13px;display:block;margin-bottom:14px;"><?php echo xlt('Studio Settings'); ?></strong>
            <div style="display:flex;flex-wrap:wrap;gap:14px;justify-content:center;">

                <!-- Appointment Types card -->
                <div style="flex:1;min-width:220px;max-width:300px;background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;gap:10px;box-shadow:var(--cs-shadow);">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:20px;">🗂</span>
                        <strong style="font-size:13px;"><?php echo xlt('Appointment Types'); ?></strong>
                    </div>
                    <div style="font-size:12px;color:var(--cs-subtle);"><?php echo xlt('Define categories, colors, and durations used by provider templates.'); ?></div>
                    <button class="btn" type="button" style="margin-top:auto;"
                            onclick="document.getElementById('btnManageTypes').click();">
                        <?php echo xlt('Manage Types'); ?>
                    </button>
                </div>

                <!-- Scheduling Rules card — spans roughly 2 columns via flex-grow -->
                <div style="flex:2;min-width:440px;max-width:620px;background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:16px 18px;display:flex;flex-direction:column;gap:12px;box-shadow:var(--cs-shadow);" id="schedulingRulesCard">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:20px;">⚙️</span>
                        <strong style="font-size:13px;"><?php echo xlt('Staff Scheduling'); ?></strong>
                    </div>

                    <!-- Two-column grid for the two rule groups -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 24px;flex:1;">

                        <!-- Template enforcement -->
                        <fieldset style="border:none;padding:0;margin:0;border-right:1px solid var(--cs-border);padding-right:20px;">
                            <legend style="font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:6px;"><?php echo xlt('Template enforcement'); ?></legend>
                            <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;margin-bottom:6px;">
                                <input type="radio" name="sr_template_enforcement" id="srEnfGuideline" value="guideline"
                                       <?php echo $srEnforcement === 'guideline' ? 'checked' : ''; ?>
                                       onchange="saveSchedulingRules()">
                                <span><?php echo xlt('Templates are guidelines. Staff can override slot types if needed.'); ?></span>
                            </label>
                            <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;">
                                <input type="radio" name="sr_template_enforcement" id="srEnfStrict" value="strict"
                                       <?php echo $srEnforcement === 'strict' ? 'checked' : ''; ?>
                                       onchange="saveSchedulingRules()">
                                <span><?php echo xlt('Templates are strictly enforced. Only appointments of the same type can be added or moved into a slot.'); ?></span>
                            </label>
                        </fieldset>

                        <!-- Double-booking -->
                        <fieldset style="border:none;padding:0;margin:0;">
                            <legend style="font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:6px;"><?php echo xlt('Slot double-booking'); ?></legend>
                            <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;margin-bottom:6px;">
                                <input type="radio" name="sr_double_booking" id="srDblAllowed" value="1"
                                       <?php echo $srAllowDblBook ? 'checked' : ''; ?>
                                       onchange="saveSchedulingRules()">
                                <span><?php echo xlt('Slot double-booking is allowed.'); ?></span>
                            </label>
                            <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;">
                                <input type="radio" name="sr_double_booking" id="srDblForbidden" value="0"
                                       <?php echo !$srAllowDblBook ? 'checked' : ''; ?>
                                       onchange="saveSchedulingRules()">
                                <span><?php echo xlt('Slot double-booking is not allowed.'); ?></span>
                            </label>
                        </fieldset>

                    </div><!-- /two-column grid -->

                    <span id="srSaveStatus" style="font-size:11px;display:none;margin-top:auto;"></span>
                </div>

                <!-- Patient Rescheduler card -->
                <div style="flex:1;min-width:220px;max-width:300px;background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:16px 18px;position:relative;display:flex;flex-direction:column;box-shadow:var(--cs-shadow);" id="reschedulerCard">
                    <label style="position:absolute;top:14px;right:14px;display:inline-block;width:42px;height:24px;cursor:pointer;" title="<?php echo attr(xl('Toggle patient rescheduler')); ?>">
                        <input type="checkbox" id="reschedulerToggle" style="opacity:0;width:0;height:0;position:absolute;"
                            <?php echo $reschedulerPaused ? '' : 'checked'; ?>>
                        <span id="reschedulerSlider" style="position:absolute;cursor:pointer;inset:0;border-radius:24px;transition:.2s;
                            background:<?php echo $reschedulerPaused ? '#ef4444' : '#1c4568'; ?>;">
                            <span id="reschedulerThumb" style="position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;
                                top:3px;transition:.2s;
                                left:<?php echo $reschedulerPaused ? '3px' : '21px'; ?>;"></span>
                        </span>
                    </label>
                    <div style="padding-right:52px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <span style="font-size:20px;">🔄</span>
                            <strong style="font-size:13px;"><?php echo xlt('Patient Rescheduler'); ?></strong>
                        </div>
                        <div style="font-size:12px;color:var(--cs-subtle);" id="reschedulerCardDesc">
                            <?php echo xlt('With an active template and reschedulable slots, we can offer our patients auto-rescheduling.'); ?>
                        </div>
                    </div>
                    <div style="margin-top:auto;padding-top:14px;">
                        <span id="reschedulerSaveSpinner" style="visibility:hidden;display:block;font-size:11px;color:var(--cs-subtle);margin-bottom:6px;min-height:1em;"><?php echo xlt('Saving…'); ?></span>
                        <button class="btn" type="button" style="width:100%;text-align:center;"
                            onclick="openReschedulerModal();">
                            <?php echo xlt('Rescheduler Rules'); ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Providers (second row) ────────────────────────────── -->
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                <strong style="font-size:14px;"><?php echo xlt('Providers'); ?></strong>
                <span style="font-size:11px;color:var(--cs-subtle);"><?php echo count($providers); ?> <?php echo xlt('provider(s)'); ?> &mdash; <?php echo count($providerTemplateMap); ?> <?php echo xlt('with saved templates'); ?></span>
            </div>
            <?php if (empty($providers)): ?>
                <div class="empty-note"><?php echo xlt('No calendar providers found. Ensure providers have calendar access in OpenEMR.'); ?></div>
            <?php else: ?>
            <div class="provider-roster-grid">
                <?php foreach ($providers as $_p): ?>
                <?php
                $_pid = (int)($_p['id']);
                $_hasTemplate = isset($providerTemplateMap[$_pid]);
                $_tpl = $_hasTemplate ? $providerTemplateMap[$_pid] : null;
                $_blocks = $_hasTemplate ? ($_tpl['blocks'] ?? []) : [];
                $_daySet = array_unique(array_column($_blocks, 'weekday'));
                sort($_daySet);
                $_daySummary = implode(', ', array_map(fn($_d) => $shortDayNamesRoster[$_d] ?? '?', $_daySet));
                ?>
                <div class="provider-card<?php echo $_hasTemplate ? ' has-template' : ''; ?>"
                     id="pcard-<?php echo attr((string)$_pid); ?>"
                     data-provider-id="<?php echo attr((string)$_pid); ?>">
                    <div class="provider-card-name">&#x1F464; <?php echo text($_p['name']); ?></div>
                    <?php if ($_hasTemplate): ?>
                        <div class="provider-card-status">
                            <?php echo text($_daySummary ?: xlt('No days')); ?> &bull; <?php echo count($_blocks); ?> <?php echo xlt('block(s)'); ?>
                        </div>
                        <div class="provider-card-actions">
                            <button class="btn primary" type="button" style="font-size:11px;padding:6px 10px;"
                                    onclick="openProviderTemplate(<?php echo attr((string)$_pid); ?>)">
                                <?php echo xlt('Edit Template'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="provider-card-status"><?php echo xlt('No template yet'); ?></div>
                        <div class="provider-card-actions">
                            <button class="btn" type="button" style="font-size:11px;padding:6px 10px;"
                                    onclick="openProviderNewTemplate(<?php echo attr((string)$_pid); ?>)">
                                <?php echo xlt('Create Template'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="cs-main" id="csMain" style="display:none;">
        <aside class="panel">
            <div id="patternCards" style="display:none;"></div>

            <div class="section-row">
                <button class="section-toggle" type="button" data-collapse-toggle="types" aria-expanded="true" aria-controls="collapseTypesBody">
                    <span class="icon">▾</span><span><?php echo xlt('Appointment Types'); ?></span>
                </button>
                <button class="btn" type="button" id="btnManageTypes">
                    <?php echo xlt('Manage Types'); ?>
                </button>
            </div>
            <div class="section-body" id="collapseTypesBody">
                <div id="typeLegend">
                    <?php if (empty($appointmentTypes)): ?>
                        <div class="empty-note"><?php echo xlt('No appointment categories are active yet. Use Manage Types to add or reactivate categories.'); ?></div>
                    <?php else: ?>
                    <?php foreach ($appointmentTypes as $type): ?>
                        <div class="chip" draggable="true"
                             data-type-id="<?php echo attr((string)$type['id']); ?>"
                             data-type-name="<?php echo attr($type['name']); ?>"
                                data-type-duration="<?php echo attr((string)($type['duration'] ?? 30)); ?>"
                             data-type-color="<?php echo attr($type['color']); ?>"
                             style="background: <?php echo attr($type['color']); ?>;">
                            <?php echo text($type['name']); ?>
                            <small><?php echo xlt('Drag onto calendar'); ?></small>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                        <p class="empty-note"><?php echo xlt('Tip: drag a type onto a time cell. Click a slot to set whether patients can see it for booking and rescheduling.'); ?></p>
            </div>

            <!-- Template Snapshots -->
            <div class="section-head" style="margin-top:16px;">
                <button class="section-toggle" type="button" data-collapse-toggle="snapshots" aria-expanded="false" aria-controls="collapseSnapshotsBody">
                    <span class="icon">▾</span><span><?php echo xlt('Snapshots / Rollback'); ?></span>
                </button>
            </div>
            <div class="section-body" id="collapseSnapshotsBody" style="display:none;">
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">
                    <button class="btn primary" type="button" id="btnSaveSnapshot"><?php echo xlt('Save Snapshot'); ?></button>
                    <button class="btn" type="button" id="btnCopySnapshot"><?php echo xlt('Copy to Provider…'); ?></button>
                </div>
                <!-- Snapshot list loads automatically when panel opens -->
                <div id="snapshotList" style="max-height:260px;overflow-y:auto;font-size:12px;"></div>
                <div id="snapshotStatus" style="font-size:11px;margin-top:4px;"></div>
            </div>
        </aside>

        <section class="panel calendar-wrap">
            <!-- Cadence context hint — updates when cadence changes -->
            <div id="cadenceHint" style="font-size:11px;color:var(--cs-subtle);margin-bottom:6px;padding:4px 6px;background:var(--cs-bg);border-radius:6px;display:none;"></div>
            <!-- 4-week rotation tab bar (hidden unless cadence = four_week) -->
            <div id="fourWeekTabs" style="display:none;border-bottom:2px solid var(--cs-border);margin-bottom:4px;">
                <button class="btn four-week-tab active" data-week="0" type="button" style="margin:2px;">Week A</button>
                <button class="btn four-week-tab" data-week="1" type="button" style="margin:2px;">Week B</button>
                <button class="btn four-week-tab" data-week="2" type="button" style="margin:2px;">Week C</button>
                <button class="btn four-week-tab" data-week="3" type="button" style="margin:2px;">Week D</button>
                <span style="font-size:11px;color:var(--cs-subtle);margin-left:8px;"><?php echo xlt('Each tab is an independent week in the 4-week rotation'); ?></span>
            </div>
            <div class="calendar-head" id="calendarHead"></div>
            <div class="calendar-grid" id="calendarGrid"></div>
        </section>

        <aside class="panel">
            <h3><?php echo xlt('Slot Rules'); ?></h3>
            <div id="inspectorEmpty" class="empty-note"><?php echo xlt('Select a slot on the calendar to edit rules.'); ?></div>
            <div id="inspectorBody" style="display:none;">
                <div class="slot-summary" id="slotSummary"></div>

                <div class="inspector-section">
                    <label for="slotTypeSelect"><?php echo xlt('Appointment Type'); ?></label>
                    <select id="slotTypeSelect">
                        <?php foreach ($appointmentTypes as $type): ?>
                            <option value="<?php echo attr((string)$type['id']); ?>"><?php echo text($type['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="slotDuration" style="margin-top:8px; display:block;"><?php echo xlt('Slot Duration (minutes)'); ?></label>
                    <input id="slotDuration" type="number" min="5" max="240" step="5" value="30">
                    <label for="inspectorFacility" style="margin-top:8px; display:block;"><?php echo xlt('Facility'); ?></label>
                    <select id="inspectorFacility">
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?php echo attr((string)$f['id']); ?>"
                                <?php echo ($f['id'] === $defaultFacilityId ? ' selected' : ''); ?>>
                                <?php echo text($f['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="inspector-section">
                    <label><input type="checkbox" id="ruleStaffOnly" checked> <?php echo xlt('Staff can schedule this slot'); ?></label><br>
                    <label><input type="checkbox" id="rulePatientBook"> <?php echo xlt('Patients can book this slot'); ?></label><br>
                    <label><input type="checkbox" id="rulePatientRebook"> <?php echo xlt('Patients can reschedule into this slot'); ?></label>
                </div>

                <div class="inspector-section">
                    <button class="btn" type="button" id="btnCopySlot"><?php echo xlt('Copy Slot'); ?></button>
                    <button class="btn" type="button" id="btnPasteSlot" disabled><?php echo xlt('Paste'); ?></button>
                </div>

                <div class="inspector-section">
                    <button class="btn" type="button" id="btnDuplicateWeek"><?php echo xlt('Apply This Slot To Every Weekday'); ?></button>
                    <button class="btn" type="button" id="btnDeleteSlot" style="border-color:#ef4444;color:#b91c1c;"><?php echo xlt('Delete Slot'); ?></button>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="manage-types-modal" id="manageTypesModal" aria-hidden="true">
    <div class="manage-types-dialog" role="dialog" aria-modal="true" aria-label="<?php echo attr(xl('Manage Appointment Types')); ?>">
        <div class="manage-types-head">
            <strong><?php echo xlt('Appointment Types And Categories'); ?></strong>
            <div class="actions">
                <button class="btn" type="button" id="btnManageTypesNew"><?php echo xlt('New Type'); ?></button>
                <button class="btn" type="button" id="btnCloseManageTypes"><?php echo xlt('Close'); ?></button>
            </div>
        </div>
        <div class="manage-types-grid">
            <div class="manage-types-list" id="manageTypesList"></div>
            <div class="manage-types-form">
                <h3 style="margin-top:0;"><?php echo xlt('Edit Appointment Type'); ?></h3>
                <div class="field">
                    <label for="mtName"><?php echo xlt('Name'); ?></label>
                    <input id="mtName" type="text" maxlength="120" placeholder="<?php echo attr(xl('Exam Follow-up')); ?>">
                </div>
                <div class="field" style="margin-top:10px;">
                    <label for="mtColor"><?php echo xlt('Color'); ?></label>
                    <input id="mtColor" type="text" value="#1d4ed8" data-coloris
                           style="width:42px;height:36px;padding:0;border:1px solid var(--cs-border);border-radius:6px;cursor:pointer;background:#1d4ed8;color:transparent;font-size:1px;outline:none;box-shadow:none;">
                </div>
                <div class="field" style="margin-top:10px;">
                    <label for="mtDuration"><?php echo xlt('Default Duration (minutes)'); ?></label>
                    <input id="mtDuration" type="number" min="5" max="240" step="5" value="30">
                </div>
                <div class="field" style="margin-top:10px;">
                    <label for="mtFacility"><?php echo xlt('Default Facility'); ?></label>
                    <select id="mtFacility">
                        <option value="0"><?php echo xlt('— Practice Default —'); ?></option>
                        <?php foreach ($facilities as $f): ?>
                            <option value="<?php echo attr((string)$f['id']); ?>"><?php echo text($f['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; gap:8px; margin-top:12px;">
                    <button class="btn primary" type="button" id="btnManageTypesSave"><?php echo xlt('Save Type'); ?></button>
                    <button class="btn" type="button" id="btnManageTypesReset"><?php echo xlt('Reset'); ?></button>
                </div>
                <div class="manager-status" id="manageTypesStatus"></div>
            </div>
        </div>
    </div>
</div>

<div class="slot-edit-modal" id="slotEditModal" aria-hidden="true">
    <div class="slot-edit-dialog" role="dialog" aria-modal="true" aria-label="<?php echo attr(xl('Edit Slot')); ?>">
        <div class="slot-edit-head">
            <strong id="slotEditTitle"><?php echo xlt('Edit Slot'); ?></strong>
            <button class="btn" type="button" id="btnSlotEditCloseTop"><?php echo xlt('Close'); ?></button>
        </div>
        <div class="slot-edit-body">
            <div class="field">
                <label><?php echo xlt('Slot Start (read-only)'); ?></label>
                <input id="slotEditWhen" type="text" readonly>
            </div>
            <div class="field">
                <label for="slotEditFacility"><?php echo xlt('Facility'); ?></label>
                <select id="slotEditFacility">
                    <?php foreach ($facilities as $f): ?>
                        <option value="<?php echo attr((string)$f['id']); ?>"><?php echo text($f['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="slotEditType"><?php echo xlt('Appointment Type'); ?></label>
                <select id="slotEditType">
                    <?php foreach ($appointmentTypes as $type): ?>
                        <option value="<?php echo attr((string)$type['id']); ?>"><?php echo text($type['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="slotEditDuration"><?php echo xlt('Slot Duration (minutes)'); ?></label>
                <input id="slotEditDuration" type="number" min="5" max="240" step="5" value="30">
            </div>
            <div class="slot-edit-rules">
                <label><input type="checkbox" id="slotEditStaffOnly"> <?php echo xlt('Staff can schedule this slot'); ?></label>
                <label><input type="checkbox" id="slotEditPatientBook"> <?php echo xlt('Patients can book this slot'); ?></label>
                <label><input type="checkbox" id="slotEditPatientRebook"> <?php echo xlt('Patients can reschedule into this slot'); ?></label>
            </div>
        </div>
        <div class="slot-edit-foot">
            <button class="btn" type="button" id="btnSlotEditDelete" style="border-color:#ef4444;color:#b91c1c;"><?php echo xlt('Delete Slot'); ?></button>
            <button class="btn" type="button" id="btnSlotEditClose"><?php echo xlt('Cancel'); ?></button>
            <button class="btn primary" type="button" id="btnSlotEditSave"><?php echo xlt('Apply'); ?></button>
        </div>
    </div>
</div>

<div class="slot-edit-modal" id="askClarifyModal" aria-hidden="true">
    <div class="slot-edit-dialog" role="dialog" aria-modal="true" aria-label="<?php echo attr(xl('Need Clarification')); ?>">
        <div class="slot-edit-head">
            <strong><?php echo xlt('Need Clarification'); ?></strong>
            <button class="btn" type="button" id="btnAskClarifyCloseTop"><?php echo xlt('Close'); ?></button>
        </div>
        <div class="slot-edit-body">
            <div id="askClarifyMessage" class="empty-note"></div>
            <div class="field">
                <label for="askClarifyInput"><?php echo xlt('Your answer'); ?></label>
                <input id="askClarifyInput" type="text" placeholder="<?php echo attr(xl('Type details and submit')); ?>">
            </div>
        </div>
        <div class="slot-edit-foot">
            <button class="btn" type="button" id="btnAskClarifyCancel"><?php echo xlt('Cancel'); ?></button>
            <button class="btn primary" type="button" id="btnAskClarifySubmit"><?php echo xlt('Submit'); ?></button>
        </div>
    </div>
</div>

<script>
function getColorValue() {
    return String(document.getElementById('mtColor')?.value || '#1d4ed8').trim();
}

function syncColorPreview(val) {
    const el = document.getElementById('mtColor');
    if (!el) { return; }
    const color = String(val || '#1d4ed8').trim();
    el.value = color;
    el.style.background = color;
}

document.addEventListener('DOMContentLoaded', function() {
    const colorEl = document.getElementById('mtColor');
    if (colorEl && typeof Coloris === 'function') {
        Coloris({ el: '#mtColor', alpha: true, format: 'hex',
            onChange: function(color) {
                colorEl.style.background = color;
                colorEl.value = color;
            }
        });
    }
});

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Returns the day indices to display in the grid based on the selected cadence.
// one_day: single day chosen by cfgOneDaySelect; workdays: Mon–Fri; else: all 7.
function getActiveDayIndices() {
    const cadence = document.getElementById('cfgCadence')?.value || 'weekly';
    if (cadence === 'one_day') {
        const dayEl = document.getElementById('cfgOneDaySelect');
        return [dayEl ? Math.max(0, Math.min(6, parseInt(dayEl.value || '0', 10))) : 0];
    }
    if (cadence === 'workdays') { return [0, 1, 2, 3, 4]; }
    if (cadence === 'monthly')  { return [0, 1, 2, 3, 4, 5, 6]; } // show all; user picks pattern separately
    return [0, 1, 2, 3, 4, 5, 6]; // weekly / four_week / default
}

function rebuildGridForCadence() {
    buildHeaders();
    buildGrid();
}
const START_HOUR = 7;
const END_HOUR = 17;
const INITIAL_GRID_MINUTES = 60;
let SLOT_MINUTES = INITIAL_GRID_MINUTES;
let appointmentTypes = <?php echo json_encode($appointmentTypes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const foundPatterns = <?php echo json_encode($foundPatterns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const detectedTemplates = <?php echo json_encode($detectedTemplates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const facilityMap = <?php
    $fMap = [];
    foreach ($facilities as $f) { $fMap[(string)$f['id']] = $f['name']; }
    echo json_encode($fMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>;
const DEFAULT_FACILITY_ID = <?php echo (int)$defaultFacilityId; ?>;

// Resolve the effective facility for a slot: slot's own facility → type's facility → practice default.
// Never returns 0; always resolves to a real facility so the inspector is never blank.
function resolveSlotFacility(slotFacilityId, typeId) {
    const sid = Number(slotFacilityId) || 0;
    if (sid > 0 && facilityMap[String(sid)]) { return sid; }
    const type = getAppointmentTypeById(typeId);
    const tfid = Number((type && type.facilityId) || 0);
    if (tfid > 0 && facilityMap[String(tfid)]) { return tfid; }
    return DEFAULT_FACILITY_ID || Number(Object.keys(facilityMap)[0] || 0);
}

const slots = new Map();
const selectedSlotKeys = new Set();
let selectedSlotKey = null;
let slotModalKey = null;
let pendingAskPrompt = '';

const calendarHead = document.getElementById('calendarHead');
const calendarGrid = document.getElementById('calendarGrid');
const patternCards = document.getElementById('patternCards');

function restoreOpenEmrSessionIfAvailable() {
    try {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
    } catch (err) {
        // Ignore cross-frame/session refresh issues and continue request.
    }
}

async function postToSelfWithSession(bodyParams) {
    const body = bodyParams instanceof URLSearchParams ? bodyParams : new URLSearchParams(bodyParams || {});
    restoreOpenEmrSessionIfAvailable();
    const resp = await fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: body.toString(),
        credentials: 'same-origin'
    });
    return resp;
}

async function postJsonToSelfWithSession(bodyParams) {
    const resp = await postToSelfWithSession(bodyParams);
    return resp.json();
}

function getAppointmentTypeById(typeId) {
    const numericId = Number(typeId || 0);
    return (appointmentTypes || []).find((item) => Number(item.id || 0) === numericId) || null;
}

function getDefaultDurationForType(typeId) {
    const type = getAppointmentTypeById(typeId);
    const duration = Number((type && type.duration) || 0);
    return duration > 0 ? normalizeDurationMinutes(duration) : SLOT_MINUTES;
}

function getReadableTextColor(bgColor) {
    const hex = String(bgColor || '').trim();
    const match = /^#?([0-9a-fA-F]{6})$/.exec(hex);
    if (!match) {
        return '#ffffff';
    }
    const normalized = match[1];
    const r = parseInt(normalized.slice(0, 2), 16);
    const g = parseInt(normalized.slice(2, 4), 16);
    const b = parseInt(normalized.slice(4, 6), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.6 ? '#102a2f' : '#ffffff';
}

function colorWithAlpha(bgColor, alpha) {
    const hex = String(bgColor || '').trim();
    const match = /^#?([0-9a-fA-F]{6})$/.exec(hex);
    if (!match) {
        return 'rgba(15,118,110,' + String(alpha) + ')';
    }
    const normalized = match[1];
    const r = parseInt(normalized.slice(0, 2), 16);
    const g = parseInt(normalized.slice(2, 4), 16);
    const b = parseInt(normalized.slice(4, 6), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + String(alpha) + ')';
}

function timeLabel(minutesFromMidnight) {
    const h = Math.floor(minutesFromMidnight / 60);
    const m = minutesFromMidnight % 60;
    const dt = new Date();
    dt.setHours(h, m, 0, 0);
    return dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function getActiveFacilityName() {
    const el = document.getElementById('cfgFacility');
    const id = el ? String(el.value || '') : '';
    return id ? (facilityMap[id] || '') : '';
}

function buildHeaders() {
    const activeDays = getActiveDayIndices();
    const colCount = activeDays.length;
    // Update both head and grid CSS column layout dynamically
    const colDef = '80px repeat(' + colCount + ', minmax(110px, 1fr))';
    calendarHead.style.gridTemplateColumns = colDef;

    calendarHead.innerHTML = '';
    const corner = document.createElement('div');
    corner.textContent = 'Time';
    calendarHead.appendChild(corner);
    const facName = getActiveFacilityName();
    activeDays.forEach((dayIdx) => {
        const d = DAYS[dayIdx];
        const el = document.createElement('div');
        el.style.display = 'flex';
        el.style.flexDirection = 'column';
        el.style.alignItems = 'center';
        el.style.gap = '1px';
        const daySpan = document.createElement('span');
        daySpan.textContent = d;
        el.appendChild(daySpan);
        if (facName) {
            const facSpan = document.createElement('span');
            facSpan.className = 'header-facility-label';
            facSpan.textContent = facName;
            el.appendChild(facSpan);
        }
        calendarHead.appendChild(el);
    });
}

function slotKey(dayIdx, minuteOfDay) {
    return dayIdx + '|' + minuteOfDay;
}

function normalizeDurationMinutes(durationRaw) {
    let raw = Number(durationRaw || SLOT_MINUTES);
    if (!Number.isFinite(raw) || raw <= 0) {
        raw = SLOT_MINUTES;
    }
    while (raw > 240 && raw % 60 === 0) {
        raw = raw / 60;
    }
    return Math.max(5, Math.min(240, Math.round(raw / 5) * 5));
}

function gcdMinutes(a, b) {
    let x = Math.abs(Number(a || 0));
    let y = Math.abs(Number(b || 0));
    while (y) {
        const t = x % y;
        x = y;
        y = t;
    }
    return x || 0;
}

function computeAdaptiveGridMinutes() {
    const durations = Array.from(slots.values())
        .map((slot) => normalizeDurationMinutes(slot && slot.durationMinutes))
        .filter((value) => Number.isFinite(value) && value > 0);
    if (!durations.length) {
        return INITIAL_GRID_MINUTES;
    }
    let step = durations[0];
    for (let i = 1; i < durations.length; i += 1) {
        step = gcdMinutes(step, durations[i]);
    }
    return Math.max(5, Math.min(120, normalizeDurationMinutes(step)));
}

function syncGridStepFromSlots() {
    const nextStep = computeAdaptiveGridMinutes();
    if (nextStep === SLOT_MINUTES) {
        return false;
    }
    SLOT_MINUTES = nextStep;
    buildGrid();
    return true;
}

function getSlotAnchorForCell(dayIdx, minute) {
    for (const [key, slot] of slots.entries()) {
        if (!slot) {
            continue;
        }
        const slotDay = Number(slot.dayIdx);
        const slotStart = Number(slot.minute);
        const slotDuration = normalizeDurationMinutes(slot.durationMinutes || SLOT_MINUTES);
        const slotEnd = slotStart + slotDuration;
        if (slotDay === dayIdx && minute >= slotStart && minute < slotEnd) {
            return key;
        }
    }
    return null;
}

function clearOverlapsForSpan(dayIdx, startMinute, durationMinutes, excludeKey = null) {
    const endMinute = startMinute + normalizeDurationMinutes(durationMinutes);
    const toDelete = [];
    slots.forEach((slot, key) => {
        if (!slot || key === excludeKey) {
            return;
        }
        if (Number(slot.dayIdx) !== Number(dayIdx)) {
            return;
        }
        const slotStart = Number(slot.minute);
        const slotEnd = slotStart + normalizeDurationMinutes(slot.durationMinutes || SLOT_MINUTES);
        const overlaps = slotStart < endMinute && startMinute < slotEnd;
        if (overlaps) {
            toDelete.push(key);
        }
    });
    toDelete.forEach((key) => slots.delete(key));
}

function buildGrid() {
    const activeDays = getActiveDayIndices();
    const colCount = activeDays.length;
    const colDef = '80px repeat(' + colCount + ', minmax(110px, 1fr))';
    calendarGrid.innerHTML = '';
    calendarGrid.style.gridTemplateColumns = colDef;

    for (let mins = START_HOUR * 60; mins < END_HOUR * 60; mins += SLOT_MINUTES) {
        const row = document.createElement('div');
        row.className = 'grid-row';
        row.style.gridTemplateColumns = colDef;

        const timeCell = document.createElement('div');
        timeCell.className = 'time-cell';
        timeCell.textContent = (mins % 15 === 0) ? timeLabel(mins) : '';
        row.appendChild(timeCell);

        for (const dayIdx of activeDays) {
            const cell = document.createElement('div');
            cell.className = 'slot-cell';
            cell.dataset.dayIdx = String(dayIdx);
            cell.dataset.minute = String(mins);
            cell.addEventListener('dragover', onDragOverCell);
            cell.addEventListener('dragleave', onDragLeaveCell);
            cell.addEventListener('drop', onDropOnCell);
            cell.addEventListener('click', (event) => {
                const existingKey = getSlotAnchorForCell(dayIdx, mins);
                const key = existingKey || slotKey(dayIdx, mins);
                selectSlot(key, {
                    toggle: !!(event.metaKey || event.ctrlKey),
                    additive: !!event.shiftKey,
                });
            });
            cell.addEventListener('dblclick', () => {
                const existingKey = getSlotAnchorForCell(dayIdx, mins);
                openSlotModal(existingKey || slotKey(dayIdx, mins));
            });
            row.appendChild(cell);
        }

        calendarGrid.appendChild(row);
    }
    renderAllSlots();
}

function applyTypeToSlotKey(key, payload, options = {}) {
    if (!key || !payload || !payload.typeId) {
        return;
    }
    const [dayIdxRaw, minuteRaw] = key.split('|');
    const dayIdx = parseInt(dayIdxRaw, 10);
    const minute = parseInt(minuteRaw, 10);
    if (Number.isNaN(dayIdx) || Number.isNaN(minute)) {
        return;
    }
    const current = slots.get(key) || {
        key,
        dayIdx,
        minute,
        durationMinutes: getDefaultDurationForType(payload.typeId),
        staffOnly: true,
        patientBook: document.getElementById('cfgPatientBook').checked,
        patientRebook: document.getElementById('cfgPatientRebook').checked
    };

    current.typeId = payload.typeId;
    current.typeName = payload.typeName || 'Appointment';
    current.color = payload.color || '#1d4ed8';
    current.durationMinutes = normalizeDurationMinutes(payload.durationMinutes || current.durationMinutes || getDefaultDurationForType(payload.typeId));
    clearOverlapsForSpan(dayIdx, minute, current.durationMinutes, key);
    slots.set(key, current);
    renderAllSlots();
    if (!options.skipSelect) {
        selectSlot(key);
    }
}

function onDragStartType(e) {
    const target = e.currentTarget;
    e.dataTransfer.setData('text/plain', JSON.stringify({
        typeId: parseInt(target.dataset.typeId || '0', 10),
        typeName: target.dataset.typeName || 'Appointment',
        durationMinutes: parseInt(target.dataset.typeDuration || '0', 10) || getDefaultDurationForType(parseInt(target.dataset.typeId || '0', 10)),
        color: target.dataset.typeColor || '#1d4ed8'
    }));
}

function onDragOverCell(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drop-target');
}

function onDragLeaveCell(e) {
    e.currentTarget.classList.remove('drop-target');
}

function onDropOnCell(e) {
    e.preventDefault();
    const cell = e.currentTarget;
    cell.classList.remove('drop-target');

    let payload;
    try {
        payload = JSON.parse(e.dataTransfer.getData('text/plain') || '{}');
    } catch (err) {
        return;
    }

    const dayIdx = parseInt(cell.dataset.dayIdx, 10);
    const minute = parseInt(cell.dataset.minute, 10);
    const key = slotKey(dayIdx, minute);

    if (payload && payload.__slot_move && payload.fromKey) {
        const source = slots.get(String(payload.fromKey));
        if (!source) {
            return;
        }
        const targetKey = key;
        const moved = {
            ...source,
            key: targetKey,
            dayIdx,
            minute,
        };
        moved.durationMinutes = normalizeDurationMinutes(moved.durationMinutes || SLOT_MINUTES);
        clearOverlapsForSpan(dayIdx, minute, moved.durationMinutes, String(payload.fromKey));
        slots.delete(String(payload.fromKey));
        slots.set(targetKey, moved);
        renderAllSlots();
        selectSlot(targetKey);
        return;
    }

    if (!payload.typeId) {
        return;
    }
    applyTypeToSlotKey(key, payload);
}

function getCellForKey(key) {
    const [dayIdx, minute] = key.split('|').map((v) => parseInt(v, 10));
    return calendarGrid.querySelector('.slot-cell[data-day-idx="' + dayIdx + '"][data-minute="' + minute + '"]');
}

function renderSlot(key) {
    void key;
    renderAllSlots();
}

function renderAllSlots() {
    if (syncGridStepFromSlots()) {
        return;
    }

    calendarGrid.querySelectorAll('.slot-cell').forEach((cell) => {
        cell.innerHTML = '';
        cell.classList.remove('span-tail', 'slot-occupied', 'span-anchor');
        cell.style.background = '';
        cell.style.borderColor = '';
        cell.style.zIndex = '';
    });

    slots.forEach((slot, key) => {
        const anchorCell = getCellForKey(key);
        if (!anchorCell || !slot) {
            return;
        }

        const durationMinutes = normalizeDurationMinutes(slot.durationMinutes || SLOT_MINUTES);
        const slotEnd = Number(slot.minute) + durationMinutes;
        const anchorMinute = Number(slot.minute);
        const lastMinute = slotEnd - SLOT_MINUTES;
        const lastCell = getCellForKey(slotKey(Number(slot.dayIdx), lastMinute));
        for (let m = Number(slot.minute); m < slotEnd; m += SLOT_MINUTES) {
            const coveredCell = getCellForKey(slotKey(Number(slot.dayIdx), m));
            if (coveredCell) {
                coveredCell.classList.add('slot-occupied');
                if (m > Number(slot.minute)) {
                    coveredCell.classList.add('span-tail');
                }
            }
        }

        anchorCell.classList.add('span-anchor');
        const anchorRect = anchorCell.getBoundingClientRect();
        const lastRect = lastCell ? lastCell.getBoundingClientRect() : anchorRect;
        const spanHeightRaw = (lastRect.bottom - anchorRect.top);
        const spanHeight = Math.max(38, spanHeightRaw - 2);

        const tag = document.createElement('div');
        tag.className = 'slot-tag span-block';
        tag.style.background = 'linear-gradient(180deg, ' + colorWithAlpha(slot.color, 0.16) + ' 0%, ' + colorWithAlpha(slot.color, 0.08) + ' 100%)';
        tag.style.border = '2px solid ' + colorWithAlpha(slot.color, 0.88);
        tag.style.boxShadow = '0 1px 0 rgba(255,255,255,0.75) inset, 0 1px 2px rgba(0,0,0,0.05)';
        tag.style.color = '#102a2f';
        tag.style.height = String(spanHeight) + 'px';
        if (selectedSlotKeys.has(key)) {
            tag.style.boxShadow += ', 0 0 0 2px rgba(47, 113, 107, 0.55)';
        }
        if (selectedSlotKey === key) {
            tag.style.boxShadow += ', 0 0 0 3px rgba(15, 118, 110, 0.95)';
        }
        tag.draggable = true;
        tag.addEventListener('click', (e) => {
            e.stopPropagation();
            selectSlot(key);
        });
        tag.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                __slot_move: true,
                fromKey: key,
            }));
        });

        const title = document.createElement('div');
        title.className = 'slot-title';
        const safeTypeName = String(slot.typeName || '').trim() || 'Appointment';
        title.textContent = safeTypeName + ' - ' + String(durationMinutes) + 'm';
        tag.appendChild(title);

        const options = [];
        if (slot.staffOnly) {
            options.push('Staff');
        }
        if (slot.patientBook) {
            options.push('Book');
        }
        if (slot.patientRebook) {
            options.push('Reschedule');
        }
        const optionsLine = document.createElement('div');
        optionsLine.className = 'slot-options';
        optionsLine.textContent = options.length ? options.join(' | ') : '';
        tag.appendChild(optionsLine);

        // Modern hover tooltip
        const slotEndMin = Number(slot.minute) + durationMinutes;
        const tooltipLines = [
            timeLabel(Number(slot.minute)) + ' – ' + timeLabel(slotEndMin),
            safeTypeName + ' · ' + durationMinutes + ' min'
        ];
        if (options.length) { tooltipLines.push(options.join(' · ')); }
        const tooltipText = tooltipLines.join('\n');

        const tipEl = document.getElementById('slotTooltip');
        tag.addEventListener('mouseenter', (e) => {
            if (!tipEl) { return; }
            tipEl.textContent = '';
            tooltipLines.forEach((line, i) => {
                if (i > 0) { tipEl.appendChild(document.createElement('br')); }
                const span = document.createElement('span');
                if (i === 0) { span.style.fontWeight = '700'; span.style.fontSize = '13px'; }
                else if (i === tooltipLines.length - 1 && options.length) { span.style.color = '#a8d4f5'; span.style.fontSize = '11px'; }
                span.textContent = line;
                tipEl.appendChild(span);
            });
            void tooltipText;
            tipEl.style.display = 'block';
            const mx = e.clientX, my = e.clientY;
            const tw = tipEl.offsetWidth || 180;
            const th = tipEl.offsetHeight || 60;
            tipEl.style.left = (mx + 14 + tw > window.innerWidth ? mx - tw - 10 : mx + 14) + 'px';
            tipEl.style.top  = (my + th + 14 > window.innerHeight ? my - th - 8 : my + 8) + 'px';
        });
        tag.addEventListener('mousemove', (e) => {
            if (!tipEl || tipEl.style.display === 'none') { return; }
            const tw = tipEl.offsetWidth || 180;
            const th = tipEl.offsetHeight || 60;
            tipEl.style.left = (e.clientX + 14 + tw > window.innerWidth ? e.clientX - tw - 10 : e.clientX + 14) + 'px';
            tipEl.style.top  = (e.clientY + th + 14 > window.innerHeight ? e.clientY - th - 8 : e.clientY + 8) + 'px';
        });
        tag.addEventListener('mouseleave', () => { if (tipEl) { tipEl.style.display = 'none'; } });

        anchorCell.appendChild(tag);
    });

    // Inject facility-change banners when consecutive slots in the same day switch facilities.
    const templateFacilityId = String(document.getElementById('cfgFacility')?.value || '');
    for (let dayIdx = 0; dayIdx < DAYS.length; dayIdx++) {
        const daySlots = Array.from(slots.values())
            .filter((s) => Number(s.dayIdx) === dayIdx)
            .sort((a, b) => Number(a.minute) - Number(b.minute));
        let prevFacId = templateFacilityId;
        for (const slot of daySlots) {
            const slotFacId = String(slot.facilityId || templateFacilityId);
            if (slotFacId && slotFacId !== prevFacId) {
                const cell = getCellForKey(slotKey(dayIdx, Number(slot.minute)));
                if (cell) {
                    const banner = document.createElement('div');
                    banner.className = 'facility-change-banner';
                    banner.textContent = '📍 ' + (facilityMap[slotFacId] || slotFacId);
                    cell.insertBefore(banner, cell.firstChild);
                }
            }
            prevFacId = slotFacId;
        }
    }
}

function queueStableRender() {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            renderAllSlots();
        });
    });
}

function refreshSelectionVisuals() {
    calendarGrid.querySelectorAll('.slot-cell').forEach((el) => {
        el.classList.remove('multi-selected', 'primary-selected');
    });
    selectedSlotKeys.forEach((key) => {
        const cell = getCellForKey(key);
        if (cell) {
            cell.classList.add('multi-selected');
        }
    });
    if (selectedSlotKey) {
        const primary = getCellForKey(selectedSlotKey);
        if (primary) {
            primary.classList.add('primary-selected');
        }
    }
}

function selectSlot(key, options = {}) {
    const toggle = !!options.toggle;
    const additive = !!options.additive;

    if (!key) {
        selectedSlotKey = null;
        selectedSlotKeys.clear();
        refreshSelectionVisuals();
        const inspectorEmpty = document.getElementById('inspectorEmpty');
        const inspectorBody = document.getElementById('inspectorBody');
        inspectorEmpty.style.display = 'block';
        inspectorBody.style.display = 'none';
        return;
    }

    if (toggle) {
        if (selectedSlotKeys.has(key)) {
            selectedSlotKeys.delete(key);
            if (selectedSlotKey === key) {
                selectedSlotKey = selectedSlotKeys.size ? Array.from(selectedSlotKeys)[selectedSlotKeys.size - 1] : null;
            }
        } else {
            selectedSlotKeys.add(key);
            selectedSlotKey = key;
        }
    } else if (additive) {
        selectedSlotKeys.add(key);
        selectedSlotKey = key;
    } else {
        selectedSlotKeys.clear();
        selectedSlotKeys.add(key);
        selectedSlotKey = key;
    }

    refreshSelectionVisuals();

    const slot = selectedSlotKey ? slots.get(selectedSlotKey) : null;

    const inspectorEmpty = document.getElementById('inspectorEmpty');
    const inspectorBody = document.getElementById('inspectorBody');
    if (!slot) {
        inspectorEmpty.style.display = 'block';
        inspectorBody.style.display = 'none';
        return;
    }

    inspectorEmpty.style.display = 'none';
    inspectorBody.style.display = 'block';

    const summary = document.getElementById('slotSummary');
    summary.innerHTML = '<strong>' + slot.typeName + '</strong><br>'
        + DAYS[slot.dayIdx] + ' at ' + timeLabel(slot.minute) + '<br>'
        + '<span class="muted">' + (slot.durationMinutes || SLOT_MINUTES) + ' min'
        + (selectedSlotKeys.size > 1 ? ' | ' + selectedSlotKeys.size + ' selected' : '') + '</span>';

    const typeSelect = document.getElementById('slotTypeSelect');
    if (typeSelect) {
        typeSelect.value = String(slot.typeId || '');
    }
    const durationInput = document.getElementById('slotDuration');
    if (durationInput) {
        durationInput.value = String(slot.durationMinutes || SLOT_MINUTES);
    }

    document.getElementById('ruleStaffOnly').checked = !!slot.staffOnly;
    document.getElementById('rulePatientBook').checked = !!slot.patientBook;
    document.getElementById('rulePatientRebook').checked = !!slot.patientRebook;
    const facilityEl = document.getElementById('inspectorFacility');
    if (facilityEl) {
        facilityEl.value = String(resolveSlotFacility(slot.facilityId, slot.typeId));
    }
}

function syncInspectorToSlot() {
    if (!selectedSlotKey || !slots.has(selectedSlotKey)) {
        return;
    }
    const slot = slots.get(selectedSlotKey);
    const selectedTypeId = Number((document.getElementById('slotTypeSelect') || {}).value || slot.typeId || 0);
    const selectedType = getAppointmentTypeById(selectedTypeId);
    const durationRaw = Number((document.getElementById('slotDuration') || {}).value || slot.durationMinutes || SLOT_MINUTES);
    slot.typeId = selectedType ? Number(selectedType.id || selectedTypeId) : selectedTypeId;
    slot.typeName = selectedType ? String(selectedType.name || 'Appointment') : (slot.typeName || 'Appointment');
    slot.color = selectedType ? String(selectedType.color || '#1d4ed8') : (slot.color || '#1d4ed8');
    slot.durationMinutes = normalizeDurationMinutes(durationRaw);
    slot.staffOnly = document.getElementById('ruleStaffOnly').checked;
    slot.patientBook = document.getElementById('rulePatientBook').checked;
    slot.patientRebook = document.getElementById('rulePatientRebook').checked;
    const facilityEl = document.getElementById('inspectorFacility');
    slot.facilityId = facilityEl
        ? (Number(facilityEl.value) > 0 ? facilityEl.value : String(resolveSlotFacility(0, slot.typeId)))
        : String(resolveSlotFacility(slot.facilityId, slot.typeId));
    clearOverlapsForSpan(slot.dayIdx, slot.minute, slot.durationMinutes, selectedSlotKey);
    slots.set(selectedSlotKey, slot);
    renderAllSlots();
    selectSlot(selectedSlotKey);
}

function bindInspector() {
    document.getElementById('slotTypeSelect').addEventListener('change', () => {
        const typeId = Number(document.getElementById('slotTypeSelect').value || 0);
        document.getElementById('slotDuration').value = String(getDefaultDurationForType(typeId));
    });

    ['slotTypeSelect', 'slotDuration', 'inspectorFacility', 'ruleStaffOnly', 'rulePatientBook', 'rulePatientRebook'].forEach((id) => {
        document.getElementById(id).addEventListener('change', syncInspectorToSlot);
    });

    document.getElementById('btnDeleteSlot').addEventListener('click', () => {
        if (!selectedSlotKey) {
            return;
        }
        slots.delete(selectedSlotKey);
        const key = selectedSlotKey;
        selectedSlotKey = null;
        renderSlot(key);
        selectSlot(key);
    });

    let slotClipboard = null;

    const updatePasteBtn = () => {
        const btn = document.getElementById('btnPasteSlot');
        if (btn) { btn.disabled = !slotClipboard; }
    };

    document.getElementById('btnCopySlot').addEventListener('click', () => {
        if (!selectedSlotKey || !slots.has(selectedSlotKey)) { return; }
        slotClipboard = Object.assign({}, slots.get(selectedSlotKey));
        updatePasteBtn();
        const btn = document.getElementById('btnCopySlot');
        const orig = btn.textContent;
        btn.textContent = '✓ Copied';
        setTimeout(() => { btn.textContent = orig; }, 1200);
    });

    document.getElementById('btnPasteSlot').addEventListener('click', () => {
        if (!slotClipboard || !selectedSlotKey) { return; }
        const [dayRaw, minRaw] = selectedSlotKey.split('|');
        const dayIdx = parseInt(dayRaw, 10);
        const minute = parseInt(minRaw, 10);
        if (Number.isNaN(dayIdx) || Number.isNaN(minute)) { return; }
        // Place the paste AFTER the selected slot so repeated pastes stack downward.
        const clipDur = normalizeDurationMinutes(slotClipboard.durationMinutes || SLOT_MINUTES);
        const targetMinute = minute + clipDur;
        const key = slotKey(dayIdx, targetMinute);
        const pasted = Object.assign({}, slotClipboard, { key, dayIdx, minute: targetMinute });
        clearOverlapsForSpan(dayIdx, targetMinute, pasted.durationMinutes, key);
        slots.set(key, pasted);
        renderSlot(key);
        selectSlot(key);
    });

    document.addEventListener('keydown', (e) => {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA')) { return; }
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
            if (selectedSlotKey && slots.has(selectedSlotKey)) {
                slotClipboard = Object.assign({}, slots.get(selectedSlotKey));
                updatePasteBtn();
            }
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
            if (slotClipboard && selectedSlotKey) {
                document.getElementById('btnPasteSlot').click();
            }
        }
    });

    document.getElementById('btnDuplicateWeek').addEventListener('click', () => {
        if (!selectedSlotKey || !slots.has(selectedSlotKey)) {
            return;
        }
        const source = slots.get(selectedSlotKey);
        for (let dayIdx = 0; dayIdx < DAYS.length; dayIdx += 1) {
            const key = slotKey(dayIdx, source.minute);
            slots.set(key, {
                ...source,
                key,
                dayIdx,
            });
            renderSlot(key);
        }
    });
}

function bindToolbar() {
    document.getElementById('btnClearAll').addEventListener('click', () => {
        slots.clear();
        selectedSlotKey = null;
        buildGrid();
        selectSlot('');
    });

    document.getElementById('btnDeployTemplate').addEventListener('click', async () => {
        const providerValues = Array.from(document.getElementById('cfgProvider').selectedOptions || []).map((opt) => parseInt(opt.value, 10)).filter((v) => v > 0);
        if (!providerValues.length) {
            alert('Select at least one provider before deploy.');
            return;
        }
        if (!slots.size) {
            alert('Add at least one slot before deploy.');
            return;
        }

        const facilityValue = String(document.getElementById('cfgFacility').value || '');
        const cadenceVal = document.getElementById('cfgCadence')?.value || 'weekly';

        // For 4-week rotation, collect slots from all 4 week tabs (with weekIndex).
        // For other cadences, use the current grid slots (weekIndex defaults to 0).
        const fourWeekPayload = typeof window.getFourWeekSlotPayload === 'function'
            ? window.getFourWeekSlotPayload()
            : null;

        const slotPayload = fourWeekPayload !== null
            ? fourWeekPayload
            : Array.from(slots.values()).map((slot) => ({
                dayIdx:          slot.dayIdx,
                minute:          slot.minute,
                weekIndex:       0,
                facilityId:      resolveSlotFacility(slot.facilityId, slot.typeId),
                typeId:          slot.typeId,
                typeName:        slot.typeName,
                durationMinutes: Number(slot.durationMinutes || SLOT_MINUTES),
                patientBook:     !!slot.patientBook,
                patientRebook:   !!slot.patientRebook,
                staffOnly:       !!slot.staffOnly
            }));

        const startDateEl = document.getElementById('cfgStartDate');
        const startDateVal = (startDateEl && startDateEl.value) ? startDateEl.value : new Date().toISOString().slice(0, 10);

        const payload = {
            provider_ids:    providerValues,
            facility_id:     parseInt(facilityValue, 10) || 0,
            cadence:         cadenceVal,
            strictness:      document.querySelector('input[name="cfgStrictness"]:checked')?.value || 'strict',
            start_date:      startDateVal,
            horizon_days:    Math.max(30, Math.min(730, parseInt((document.getElementById('cfgHorizonDays') || {}).value || '30', 10) || 30)),
            slots:           slotPayload,
            // 4-week rotation
            week_a_start:    document.getElementById('cfgWeekAStart')?.value || '',
            // Monthly
            monthly_mode:    document.getElementById('cfgMonthlyMode')?.value || 'nth_weekday',
            monthly_ns:      JSON.stringify(Array.from(document.querySelectorAll('input[name="cfgMonthlyN"]:checked')).map((cb) => parseInt(cb.value, 10)).filter((n) => n > 0)),
            monthly_n:       parseInt(document.querySelector('input[name="cfgMonthlyN"]:checked')?.value || '1', 10) || 1,
            monthly_weekday: parseInt(document.getElementById('cfgMonthlyWeekday')?.value || '0', 10) || 0,
            monthly_date:    parseInt(document.getElementById('cfgMonthlyDate')?.value || '1', 10) || 1,
        };

        const body = new URLSearchParams();
        body.set('action', 'deploy_template');
        body.set('payload', JSON.stringify(payload));

        const btn = document.getElementById('btnDeployTemplate');
        const banner = document.getElementById('deployBanner');
        const originalLabel = btn.textContent;

        const showBanner = (html, color, bg) => {
            if (!banner) { return; }
            banner.innerHTML = html;
            banner.style.background = bg;
            banner.style.border = '1px solid ' + color;
            banner.style.color = '#102a2f';
            banner.style.display = 'flex';
            banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        let elapsedSec = 0;
        let timerInterval = null;
        const startTimer = () => {
            elapsedSec = 0;
            timerInterval = setInterval(() => {
                elapsedSec++;
                const elapsed = document.getElementById('deployElapsed');
                if (elapsed) { elapsed.textContent = elapsedSec + 's'; }
            }, 1000);
        };
        const stopTimer = () => { if (timerInterval) { clearInterval(timerInterval); timerInterval = null; } };

        btn.textContent = 'Deploying…';
        btn.disabled = true;
        showBanner(
            '<span style="display:flex;align-items:center;gap:8px;">'
            + '<span style="display:inline-block;width:14px;height:14px;border:2px solid #1d4ed8;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;"></span>'
            + '<?php echo xlt('Building open slots — large templates may take a minute or two…'); ?>'
            + '</span><span style="font-size:11px;color:#405c72;" id="deployElapsed">0s</span>',
            '#a7f3d0', '#ecfdf5'
        );
        startTimer();
        try {
            const data = await postJsonToSelfWithSession(body);
            stopTimer();
            if (!data.success) {
                throw new Error(String(data.error || 'Deploy failed'));
            }
            const calUrl = (typeof top !== 'undefined' && top.restoreSession)
                ? '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php?site=default'
                : null;
            showBanner(
                '<span>'
                + '✓ <strong><?php echo xlt('Deploy complete'); ?></strong> — '
                + data.inserted.toLocaleString() + ' <?php echo xlt('open slots created for'); ?> '
                + data.providers + ' <?php echo xlt('provider(s) over the next'); ?> '
                + data.days + ' <?php echo xlt('days'); ?>.'
                + ' <span style="font-size:11px;color:#405c72;"><?php echo xlt('Finished in'); ?> ' + elapsedSec + 's</span>'
                + '</span>'
                + (calUrl ? '<a href="' + calUrl + '" target="_parent" style="font-size:12px;color:#1d4ed8;font-weight:600;white-space:nowrap;" onclick="if(typeof top!==\'undefined\'&&top.restoreSession)top.restoreSession();"><?php echo xlt('View on Calendar →'); ?></a>' : ''),
                '#93c5fd', '#dbeafe'
            );
        } catch (err) {
            stopTimer();
            showBanner(
                '✗ <strong><?php echo xlt('Deploy failed'); ?>:</strong> ' + (err.message || String(err)),
                '#fca5a5', '#fef2f2'
            );
        } finally {
            btn.textContent = originalLabel;
            btn.disabled = false;
        }
    });
}

function openAskClarifyModal(message) {
    const modal = document.getElementById('askClarifyModal');
    document.getElementById('askClarifyMessage').textContent = String(message || 'Please clarify your request.');
    document.getElementById('askClarifyInput').value = '';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeAskClarifyModal() {
    const modal = document.getElementById('askClarifyModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function applyAskTemplate(template) {
    if (!template) {
        return;
    }
    const dayIndices = Array.isArray(template.dayIndices)
        ? template.dayIndices.map((d) => Number(d)).filter((d) => Number.isInteger(d) && d >= 0 && d < DAYS.length)
        : [Number(template.dayIdx)].filter((d) => Number.isInteger(d) && d >= 0 && d < DAYS.length);
    const count = Math.max(1, Number(template.count || 1));
    const durationMinutes = normalizeDurationMinutes(Number(template.durationMinutes || 30));
    const startMinute = Number(template.startMinute || (START_HOUR * 60));
    const payload = {
        typeId: Number(template.typeId || 0),
        typeName: String(template.typeName || 'Appointment'),
        durationMinutes,
        color: String(template.color || '#1d4ed8')
    };
    if (!payload.typeId || dayIndices.length === 0) {
        return;
    }

    if (Number(template.providerId || 0) > 0) {
        const providerSelect = document.getElementById('cfgProvider');
        Array.from(providerSelect.options || []).forEach((opt) => {
            opt.selected = Number(opt.value || 0) === Number(template.providerId);
        });
    }
    if (Number(template.facilityId || 0) > 0) {
        const facilitySelect = document.getElementById('cfgFacility');
        facilitySelect.value = String(Number(template.facilityId));
    }

    dayIndices.forEach((dayIdx) => {
        for (let i = 0; i < count; i += 1) {
            const minute = startMinute + (i * durationMinutes);
            const key = slotKey(dayIdx, minute);
            applyTypeToSlotKey(key, payload, { skipSelect: true });
        }
    });
    const firstKey = slotKey(dayIndices[0], startMinute);
    selectSlot(firstKey);
}

function applyScheduleData(sd) {
    if (!sd || !Array.isArray(sd.days) || sd.days.length === 0) { return; }

    const parseMins = (t) => {
        if (!t) { return null; }
        const parts = String(t).split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1] || 0, 10);
    };

    const startMin = parseMins(sd.start_time) ?? (8 * 60);
    const endMin   = parseMins(sd.end_time)   ?? (17 * 60);
    const lunchS   = parseMins(sd.lunch_start);
    const lunchE   = parseMins(sd.lunch_end);
    const dur      = Math.max(5, parseInt(sd.duration || 30, 10));

    // Resolve category — prefer the first entry in category_values
    let typeId = 0, typeName = 'Appointment', typeColor = '#1d4ed8';
    const catVals = Array.isArray(sd.category_values) ? sd.category_values : [];
    if (catVals.length > 0 && Array.isArray(appointmentTypes)) {
        const cat = appointmentTypes.find((t) =>
            catVals.some((c) => t.name && t.name.toLowerCase().includes(String(c).toLowerCase()))
        );
        if (cat) { typeId = cat.id; typeName = cat.name; typeColor = cat.color; }
    }
    if (!typeId && Array.isArray(appointmentTypes) && appointmentTypes.length > 0) {
        typeId = appointmentTypes[0].id; typeName = appointmentTypes[0].name; typeColor = appointmentTypes[0].color;
    }

    const facilityId = (Array.isArray(sd.target_facility_ids) && sd.target_facility_ids.length > 0)
        ? sd.target_facility_ids[0] : 0;

    // Clear existing slots for the affected days
    sd.days.forEach((dayIdx) => {
        for (const [k, s] of slots.entries()) {
            if (Number(s.dayIdx) === Number(dayIdx)) { slots.delete(k); }
        }
    });

    // Fill slots
    sd.days.forEach((dayIdx) => {
        let t = startMin;
        while (t + dur <= endMin) {
            if (lunchS !== null && lunchE !== null && t >= lunchS && t < lunchE) { t += 5; continue; }
            const k = slotKey(Number(dayIdx), t);
            clearOverlapsForSpan(Number(dayIdx), t, dur, k);
            slots.set(k, {
                key: k, dayIdx: Number(dayIdx), minute: t,
                typeId, typeName, color: typeColor, durationMinutes: dur,
                facilityId, patientBook: true, patientRebook: true, staffOnly: false
            });
            t += dur;
        }
    });

    renderAllSlots();
}

async function askForTemplate(promptText, clarificationText = '') {
    const body = new URLSearchParams();
    body.set('action', 'ask_template');
    body.set('prompt', String(promptText || ''));
    body.set('clarification', String(clarificationText || ''));
    return postJsonToSelfWithSession(body);
}

// Provider roster functions
function openProviderTemplate(providerId) {
    const pid = Number(providerId);
    const sel = document.getElementById('cfgProvider');
    if (sel) {
        Array.from(sel.options || []).forEach((opt) => { opt.selected = Number(opt.value) === pid; });
    }
    // Clear existing slots first so switching providers doesn't accumulate
    if (typeof slots !== 'undefined' && slots && typeof slots.clear === 'function') {
        slots.clear();
    }
    if (typeof renderGrid === 'function') { renderGrid(); }
    // Collect all templates for this provider (handles multiple saved templates)
    const providerTemplates = (Array.isArray(detectedTemplates) ? detectedTemplates : []).filter((t) => Number(t.providerId || 0) === pid);
    if (providerTemplates.length > 0 && typeof applyTemplateAsBase === 'function') {
        providerTemplates.forEach((tpl) => applyTemplateAsBase(tpl));
    }
    _activateProviderEditor(pid);
}

function openProviderNewTemplate(providerId) {
    const pid = Number(providerId);
    const sel = document.getElementById('cfgProvider');
    if (sel) {
        Array.from(sel.options || []).forEach((opt) => { opt.selected = Number(opt.value) === pid; });
    }
    // Clear calendar
    if (typeof slots !== 'undefined' && slots && typeof slots.clear === 'function') {
        slots.clear();
    }
    if (typeof renderGrid === 'function') { renderGrid(); }
    _activateProviderEditor(pid);
}

function closeEditor() {
    const csMain = document.getElementById('csMain');
    const csConfigWrap = document.getElementById('csConfigWrap');
    const roster = document.getElementById('providerRoster');
    if (csMain) { csMain.style.display = 'none'; }
    if (csConfigWrap) { csConfigWrap.style.display = 'none'; }
    if (roster) { roster.style.display = ''; }
    const bcSlash = document.getElementById('bcProviderSlash');
    const bcName = document.getElementById('bcProviderName');
    if (bcSlash) { bcSlash.style.display = 'none'; }
    if (bcName) { bcName.style.display = 'none'; }
    document.querySelectorAll('.provider-card').forEach((c) => c.classList.remove('active'));
    const editorActions = document.getElementById('editorActions');
    if (editorActions) { editorActions.style.display = 'none'; }
}

function _activateProviderEditor(pid) {
    document.querySelectorAll('.provider-card').forEach((c) => c.classList.remove('active'));
    const card = document.getElementById('pcard-' + pid);
    if (card) { card.classList.add('active'); }
    const nameEl = card ? card.querySelector('.provider-card-name') : null;
    const providerName = nameEl ? nameEl.textContent.trim() : ('Provider #' + pid);

    // Update breadcrumb
    const bcName = document.getElementById('bcProviderName');
    const bcSlash = document.getElementById('bcProviderSlash');
    if (bcName) { bcName.textContent = providerName; bcName.style.display = ''; }
    if (bcSlash) { bcSlash.style.display = ''; }

    // Hide roster, show config wrapper + editor
    const roster = document.getElementById('providerRoster');
    const csConfigWrap = document.getElementById('csConfigWrap');
    const csMain = document.getElementById('csMain');
    if (roster) { roster.style.display = 'none'; }
    if (csConfigWrap) { csConfigWrap.style.display = ''; }
    if (csMain) { csMain.style.display = ''; }
    const editorActions = document.getElementById('editorActions');
    if (editorActions) { editorActions.style.display = 'contents'; }

    // Re-render slots with correct dimensions now that the grid is visible
    if (typeof queueStableRender === 'function') { queueStableRender(); }
}

function bindAskPrompt() {
    // Help drawer — multi-turn AI chat
    const aiAssistBtn    = document.getElementById('btnAiAssist');
    const aiAssistDrawer = document.getElementById('aiAssistDrawer');
    const aiAssistCloseBtn = document.getElementById('btnAiAssistClose');
    const aiAssistInput  = document.getElementById('aiAssistPrompt');
    const aiAssistAskBtn = document.getElementById('btnAiAssistAsk');
    const aiAssistStatus = document.getElementById('aiAssistStatus');
    const helpClearBtn   = document.getElementById('btnHelpClear');
    const helpHistory    = document.getElementById('helpChatHistory');

    let helpConversation = [];

    const appendHelpBubble = (role, text, scheduleData) => {
        if (!helpHistory) { return; }
        const bubble = document.createElement('div');
        bubble.className = 'help-bubble ' + role;
        bubble.textContent = text;
        if (scheduleData && role === 'ai') {
            const applyBtn = document.createElement('button');
            applyBtn.className = 'help-apply-btn';
            applyBtn.textContent = 'Apply to Grid';
            applyBtn.addEventListener('click', () => applyScheduleData(scheduleData));
            bubble.appendChild(document.createElement('br'));
            bubble.appendChild(applyBtn);
        }
        helpHistory.appendChild(bubble);
        helpHistory.scrollTop = helpHistory.scrollHeight;
    };

    const runHelpChat = async () => {
        if (!aiAssistInput) { return; }
        const message = String(aiAssistInput.value || '').trim();
        if (!message) { aiAssistInput.focus(); return; }

        appendHelpBubble('user', message, null);
        helpConversation.push({ role: 'user', content: message });
        aiAssistInput.value = '';

        if (aiAssistAskBtn) { aiAssistAskBtn.disabled = true; aiAssistAskBtn.textContent = '…'; }
        if (aiAssistStatus) { aiAssistStatus.style.display = ''; aiAssistStatus.textContent = 'Thinking…'; }

        try {
            const slotSummary = Array.from(slots.values()).map((s) => ({
                day: DAYS[s.dayIdx], minute: s.minute, type: s.typeName,
                duration: s.durationMinutes, facility: s.facilityId
            }));
            const selProvider = document.getElementById('cfgProvider');
            const selProviderIds = selProvider
                ? Array.from(selProvider.selectedOptions).map((o) => parseInt(o.value, 10)).filter(Boolean)
                : [];

            const body = new URLSearchParams();
            body.set('action', 'help_chat');
            body.set('message', message);
            body.set('conversation', JSON.stringify(helpConversation));
            body.set('slot_summary', JSON.stringify(slotSummary));
            body.set('provider_ids', JSON.stringify(selProviderIds));

            const data = await postJsonToSelfWithSession(body);
            if (aiAssistStatus) { aiAssistStatus.style.display = 'none'; }

            if (!data.success) {
                appendHelpBubble('ai', String(data.error || 'Something went wrong. Try again.'), null);
            } else {
                const aiText = String(data.text || '');
                helpConversation.push({ role: 'assistant', content: aiText });
                appendHelpBubble('ai', aiText, data.schedule_data || null);
            }
        } catch (err) {
            if (aiAssistStatus) { aiAssistStatus.style.display = 'none'; }
            appendHelpBubble('ai', 'Connection error: ' + String(err.message || err), null);
        } finally {
            if (aiAssistAskBtn) { aiAssistAskBtn.disabled = false; aiAssistAskBtn.textContent = 'Send'; }
            if (aiAssistInput) { aiAssistInput.focus(); }
        }
    };

    if (aiAssistBtn && aiAssistDrawer) {
        aiAssistBtn.addEventListener('click', () => {
            aiAssistDrawer.classList.toggle('open');
            if (aiAssistDrawer.classList.contains('open') && aiAssistInput) { aiAssistInput.focus(); }
        });
    }
    if (aiAssistCloseBtn) { aiAssistCloseBtn.addEventListener('click', () => aiAssistDrawer.classList.remove('open')); }
    if (helpClearBtn) {
        helpClearBtn.addEventListener('click', () => {
            helpConversation = [];
            if (helpHistory) { helpHistory.innerHTML = ''; }
        });
    }
    if (aiAssistAskBtn) { aiAssistAskBtn.addEventListener('click', runHelpChat); }
    if (aiAssistInput) {
        aiAssistInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); runHelpChat(); } });
    }

    // Original ask engine (kept for clarify modal wiring)
    const askInput = document.getElementById('askPrompt');
    const askBtn = document.getElementById('btnAskPrompt');
    const clarifyModal = document.getElementById('askClarifyModal');
    const clarifyCloseTop = document.getElementById('btnAskClarifyCloseTop');
    const clarifyCancel = document.getElementById('btnAskClarifyCancel');
    const clarifySubmit = document.getElementById('btnAskClarifySubmit');

    const runAsk = async (clarification = '') => {
        const prompt = pendingAskPrompt || String(askInput ? askInput.value : '').trim();
        if (!prompt) {
            openAskClarifyModal('Tell me what template to create.');
            return;
        }
        pendingAskPrompt = prompt;
        if (askBtn) { askBtn.disabled = true; askBtn.textContent = 'Asking...'; }
        try {
            const data = await askForTemplate(prompt, clarification);
            if (!data.success) {
                openAskClarifyModal(String(data.message || data.error || 'Please clarify your request.'));
                return;
            }
            closeAskClarifyModal();
            applyAskTemplate(data.template || null);
        } finally {
            if (askBtn) { askBtn.disabled = false; askBtn.textContent = 'Ask'; }
        }
    };

    if (askBtn) { askBtn.addEventListener('click', () => runAsk()); }
    if (askInput) {
        askInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                runAsk();
            }
        });
    }

    if (clarifyCloseTop) { clarifyCloseTop.addEventListener('click', closeAskClarifyModal); }
    if (clarifyCancel) { clarifyCancel.addEventListener('click', closeAskClarifyModal); }
    clarifySubmit.addEventListener('click', () => {
        const answer = String((document.getElementById('askClarifyInput') || {}).value || '').trim();
        runAsk(answer);
    });
    if (clarifyModal) {
        clarifyModal.addEventListener('click', (event) => {
            if (event.target === clarifyModal) {
                closeAskClarifyModal();
            }
        });
    }
}

function renderAppointmentTypeChips() {
    const legend = document.getElementById('typeLegend');
    if (!legend) {
        return;
    }
    if (!Array.isArray(appointmentTypes) || appointmentTypes.length === 0) {
        legend.innerHTML = '<div class="empty-note">No appointment categories are active yet. Use Manage Types to add or reactivate categories.</div>';
        return;
    }
    legend.innerHTML = appointmentTypes.map((type) => {
        const id = Number(type.id || 0);
        const name = String(type.name || 'Appointment')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        const color = String(type.color || '#1d4ed8');
        const duration = Number(type.duration || 30);
        return '<div class="chip" draggable="true" data-type-id="' + id + '" data-type-name="' + name + '" data-type-color="' + color + '" data-type-duration="' + duration + '" style="background:' + color + ';">'
            + name + '<small>Drag onto calendar</small></div>';
    }).join('');
    initDragTypes();
    applyChipContrast();
}

function renderAppointmentTypeSelect(selectEl, selectedValue) {
    if (!selectEl) {
        return;
    }
    const selected = String(selectedValue ?? selectEl.value ?? '');
    selectEl.innerHTML = (appointmentTypes || []).map((type) => {
        const id = String(type.id || '');
        const name = String(type.name || 'Appointment')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        return '<option value="' + id + '">' + name + '</option>';
    }).join('');
    if (selected !== '') {
        selectEl.value = selected;
    }
}

function refreshAppointmentTypeControls() {
    renderAppointmentTypeChips();
    renderAppointmentTypeSelect(document.getElementById('slotTypeSelect'));
    renderAppointmentTypeSelect(document.getElementById('slotEditType'));
}

function bindTypeListDragReorder(listEl) {
    let dragSrcId = null;

    listEl.querySelectorAll('.type-row').forEach((row) => {
        row.addEventListener('dragstart', (e) => {
            dragSrcId = String(row.getAttribute('data-id') || '');
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            listEl.querySelectorAll('.type-row').forEach((r) => r.classList.remove('drag-over'));
        });

        row.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            listEl.querySelectorAll('.type-row').forEach((r) => r.classList.remove('drag-over'));
            row.classList.add('drag-over');
        });

        row.addEventListener('dragleave', () => {
            row.classList.remove('drag-over');
        });

        row.addEventListener('drop', async (e) => {
            e.preventDefault();
            row.classList.remove('drag-over');
            const targetId = String(row.getAttribute('data-id') || '');
            if (!dragSrcId || dragSrcId === targetId) { return; }

            const srcIdx = appointmentTypes.findIndex((t) => String(t.id) === dragSrcId);
            const tgtIdx = appointmentTypes.findIndex((t) => String(t.id) === targetId);
            if (srcIdx === -1 || tgtIdx === -1) { return; }

            const moved = appointmentTypes.splice(srcIdx, 1)[0];
            appointmentTypes.splice(tgtIdx, 0, moved);

            // Re-render immediately so the user sees the change
            listEl.innerHTML = appointmentTypes.map((type) => {
                const id = Number(type.id || 0);
                const name = String(type.name || 'Appointment')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                const color = String(type.color || '#1d4ed8');
                const duration = Number(type.duration || 30);
                return '<div class="type-row" data-id="' + id + '" draggable="true" style="cursor:pointer;">'
                    + '<span class="drag-handle" title="Drag to reorder">&#x2630;</span>'
                    + '<span class="swatch" style="background:' + color + ';"></span>'
                    + '<div class="meta"><strong>' + name + '</strong><span>' + duration + ' min</span></div>'
                    + '<div class="actions">'
                    + '<button class="btn" type="button" data-action="edit" data-id="' + id + '">Edit</button>'
                    + '<button class="btn" type="button" data-action="deactivate" data-id="' + id + '" style="border-color:#ef4444;color:#b91c1c;">Deactivate</button>'
                    + '</div>'
                    + '</div>';
            }).join('');
            bindTypeListDragReorder(listEl);
            refreshAppointmentTypeControls();

            // Persist new order
            const ids = appointmentTypes.map((t) => t.id);
            try {
                restoreOpenEmrSessionIfAvailable();
                const body = new URLSearchParams();
                body.set('action', 'reorder_categories');
                body.set('ids', JSON.stringify(ids));
                await postToSelfWithSession(body);
            } catch (_) { /* non-fatal */ }
        });
    });
}

function bindManageTypesModal() {
    const modal = document.getElementById('manageTypesModal');
    const openBtn = document.getElementById('btnManageTypes');
    const closeBtn = document.getElementById('btnCloseManageTypes');
    const listEl = document.getElementById('manageTypesList');
    const statusEl = document.getElementById('manageTypesStatus');
    const newBtn = document.getElementById('btnManageTypesNew');
    const saveBtn = document.getElementById('btnManageTypesSave');
    const resetBtn = document.getElementById('btnManageTypesReset');
    const nameEl     = document.getElementById('mtName');
    const colorEl    = document.getElementById('mtColor');
    const durationEl = document.getElementById('mtDuration');
    const facilityEl = document.getElementById('mtFacility');
    let editCategoryId = 0;

    if (!modal || !openBtn || !closeBtn || !listEl || !statusEl || !newBtn || !saveBtn || !resetBtn || !nameEl || !colorEl || !durationEl) {
        return;
    }

    const setStatus = (msg, mode = '') => {
        statusEl.textContent = msg || '';
        statusEl.classList.remove('error', 'success');
        if (mode) {
            statusEl.classList.add(mode);
        }
    };

    const api = async (action, extra = {}) => {
        const body = new URLSearchParams();
        body.set('action', action);
        Object.keys(extra).forEach((k) => body.set(k, String(extra[k])));
        const data = await postJsonToSelfWithSession(body);
        if (!data.success) {
            throw new Error(String(data.error || 'Request failed'));
        }
        return data;
    };

    const resetForm = () => {
        editCategoryId = 0;
        nameEl.value = '';
        colorEl.value = '#1d4ed8';
        durationEl.value = '30';
        if (facilityEl) facilityEl.value = '0';
        syncColorPreview('#1d4ed8');
    };

    const renderList = () => {
        if (!appointmentTypes.length) {
            listEl.innerHTML = '<div class="empty-note">No active appointment types found.</div>';
            return;
        }
        listEl.innerHTML = appointmentTypes.map((type) => {
            const id = Number(type.id || 0);
            const name = String(type.name || 'Appointment')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
            const color = String(type.color || '#1d4ed8');
            const duration = Number(type.duration || 30);
            return '<div class="type-row" data-id="' + id + '" draggable="true" style="cursor:pointer;">'
                + '<span class="drag-handle" title="Drag to reorder">&#x2630;</span>'
                + '<span class="swatch" style="background:' + color + ';"></span>'
                + '<div class="meta"><strong>' + name + '</strong><span>' + duration + ' min</span></div>'
                + '<div class="actions">'
                + '<button class="btn" type="button" data-action="edit" data-id="' + id + '">Edit</button>'
                + '<button class="btn" type="button" data-action="deactivate" data-id="' + id + '" style="border-color:#ef4444;color:#b91c1c;">Deactivate</button>'
                + '</div>'
                + '</div>';
        }).join('');
        bindTypeListDragReorder(listEl);
    };

    const loadTypes = async () => {
        const data = await api('list_categories');
        appointmentTypes = Array.isArray(data.types) ? data.types : [];
        refreshAppointmentTypeControls();
        renderList();
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    openBtn.addEventListener('click', async () => {
        setStatus('Loading appointment types...');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        try {
            await loadTypes();
            setStatus('Loaded.', 'success');
        } catch (err) {
            setStatus(String(err.message || err), 'error');
        }
    });

    closeBtn.addEventListener('click', closeModal);

    newBtn.addEventListener('click', () => {
        resetForm();
        nameEl.focus();
    });

    resetBtn.addEventListener('click', () => {
        resetForm();
        setStatus('Form reset.');
    });

    const loadRowIntoForm = (row) => {
        editCategoryId = Number(row.id || 0);
        nameEl.value = String(row.name || '');
        colorEl.value = String(row.color || '#1d4ed8');
        durationEl.value = String(Number(row.duration || 30));
        if (facilityEl) facilityEl.value = String(Number(row.facilityId || 0));
        syncColorPreview(colorEl.value);
        setStatus('');
    };

    listEl.addEventListener('click', async (event) => {
        const btn = event.target.closest('button[data-action]');
        const action = btn ? String(btn.dataset.action || '') : '';

        // Deactivate is the only action that doesn't also load the form.
        // Any other click on the row (including the Edit button) loads the form.
        const rowEl = event.target.closest('.type-row');
        if (rowEl && action !== 'deactivate') {
            const rowId = btn
                ? Number(btn.dataset.id || 0)
                : Number(rowEl.dataset.id || 0);
            const row = appointmentTypes.find((t) => Number(t.id || 0) === rowId);
            if (row) { loadRowIntoForm(row); }
            if (!btn || action === 'edit') { return; }
        }

        if (!btn) { return; }
        const id = Number(btn.dataset.id || 0);
        const row = appointmentTypes.find((t) => Number(t.id || 0) === id);
        if (!row) { return; }
        if (action === 'edit') { return; } // already handled above
        if (action === 'deactivate') {
            if (!window.confirm('Deactivate this appointment type?')) {
                return;
            }
            setStatus('Deactivating...');
            try {
                const data = await api('deactivate_category', { category_id: id });
                appointmentTypes = Array.isArray(data.types) ? data.types : [];
                refreshAppointmentTypeControls();
                renderList();
                const slotCount = Number(data.affected_slots || 0);
                const slotNote = slotCount > 0
                    ? ` ${slotCount} open slot${slotCount === 1 ? '' : 's'} converted to generic.`
                    : '';
                setStatus('Type deactivated.' + slotNote, 'success');
                if (editCategoryId === id) {
                    resetForm();
                }
            } catch (err) {
                setStatus(String(err.message || err), 'error');
            }
        }
    });

    saveBtn.addEventListener('click', async () => {
        const name = String(nameEl.value || '').trim();
        const duration = Math.max(5, Math.min(240, Math.round((Number(durationEl.value || 30) || 30) / 5) * 5));
        const color = getColorValue();
        if (!name) {
            setStatus('Category name is required.', 'error');
            nameEl.focus();
            return;
        }
        setStatus('Saving...');
        try {
            const data = await api('upsert_category', {
                category_id: editCategoryId,
                category_name: name,
                duration,
                color,
                facility_id: facilityEl ? Number(facilityEl.value || 0) : 0
            });
            appointmentTypes = Array.isArray(data.types) ? data.types : [];
            refreshAppointmentTypeControls();
            renderList();
            setStatus('Type saved.', 'success');
            resetForm();
        } catch (err) {
            setStatus(String(err.message || err), 'error');
        }
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}

function initCollapsibleSections() {
    const storagePrefix = 'medex.calendar_service.collapse.';
    const toggles = document.querySelectorAll('[data-collapse-toggle]');

    toggles.forEach((toggle) => {
        const key = String(toggle.getAttribute('data-collapse-toggle') || '').trim();
        const targetId = String(toggle.getAttribute('aria-controls') || '').trim();
        if (!key || !targetId) {
            return;
        }
        const body = document.getElementById(targetId);
        if (!body) {
            return;
        }

        const storeKey = storagePrefix + key;
        const saved = localStorage.getItem(storeKey);
        const collapsed = saved === 'collapsed';

        if (collapsed) {
            body.classList.add('is-collapsed');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            body.classList.remove('is-collapsed');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', () => {
            const nextCollapsed = !body.classList.contains('is-collapsed');
            body.classList.toggle('is-collapsed', nextCollapsed);
            toggle.setAttribute('aria-expanded', nextCollapsed ? 'false' : 'true');
            localStorage.setItem(storeKey, nextCollapsed ? 'collapsed' : 'expanded');
        });
    });
}

function bindPatterns() {
    const typeById = new Map((appointmentTypes || []).map((t) => [Number(t.id || 0), t]));
    const dayShort = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const dayLong = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const sunFirstOrder = [6, 0, 1, 2, 3, 4, 5];

    const summarizeCadence = (template) => {
        const explicitCadence = String((template && template.cadenceLabel) || '').trim();
        if (explicitCadence) {
            return explicitCadence;
        }
        const blocks = Array.isArray(template && template.blocks) ? template.blocks : [];
        if (!blocks.length) {
            return 'Unknown cadence';
        }
        const uniqueDays = Array.from(new Set(blocks
            .map((block) => Number(block.weekday))
            .filter((day) => Number.isInteger(day) && day >= 0 && day < 7)))
            .sort((a, b) => sunFirstOrder.indexOf(a) - sunFirstOrder.indexOf(b));

        if (!uniqueDays.length) {
            return 'Unknown cadence';
        }
        if (uniqueDays.length === 7) {
            return 'Weekly (Sun-Sat)';
        }
        if (uniqueDays.length === 5 && uniqueDays.join(',') === '0,1,2,3,4') {
            return 'Weekly (Mon-Fri)';
        }
        if (uniqueDays.length === 1) {
            return 'Weekly (' + dayLong[uniqueDays[0]] + ')';
        }
        return 'Weekly (' + uniqueDays.map((day) => dayShort[day]).join(', ') + ')';
    };

    const summarizeTimeWindow = (template) => {
        const blocks = Array.isArray(template && template.blocks) ? template.blocks : [];
        if (!blocks.length) {
            return 'No time window detected';
        }
        let startMinute = Number.POSITIVE_INFINITY;
        let endMinute = Number.NEGATIVE_INFINITY;
        blocks.forEach((block) => {
            const start = Number(block.startMinute || -1);
            const end = Number(block.endMinute || -1);
            if (start >= 0 && start < startMinute) {
                startMinute = start;
            }
            if (end >= 0 && end > endMinute) {
                endMinute = end;
            }
        });
        if (!Number.isFinite(startMinute) || !Number.isFinite(endMinute) || endMinute <= startMinute) {
            return 'No time window detected';
        }
        return timeLabel(startMinute) + '-' + timeLabel(endMinute);
    };

    const clearPatternPreview = () => {
        calendarGrid.querySelectorAll('.slot-cell').forEach((el) => {
            el.classList.remove('pattern-preview');
            el.querySelectorAll('.preview-ghost').forEach((ghost) => ghost.remove());
        });
    };

    const resolveTypeName = (block) => {
        const typeId = Number((block && block.typeId) || 0);
        const known = typeById.get(typeId);
        return String((known && known.name) || (block && block.typeName) || 'Appointment');
    };

    const paintTemplatePreview = (template) => {
        clearPatternPreview();
        if (!template || !Array.isArray(template.blocks)) {
            return;
        }
        template.blocks.forEach((block) => {
            const day = (block.weekday != null) ? Number(block.weekday) : -1;
            const start = (block.startMinute != null) ? Number(block.startMinute) : -1;
            const end = (block.endMinute != null) ? Number(block.endMinute) : -1;
            const typeName = resolveTypeName(block);
            const step = Math.max(SLOT_MINUTES, Number(block.slotDuration || SLOT_MINUTES));
            const startSnap = Math.floor(start / SLOT_MINUTES) * SLOT_MINUTES;
            const endSnap = Math.ceil(end / SLOT_MINUTES) * SLOT_MINUTES;
            for (let minute = startSnap; minute < endSnap; minute += SLOT_MINUTES) {
                const cell = calendarGrid.querySelector('.slot-cell[data-day-idx="' + day + '"][data-minute="' + minute + '"]');
                if (cell) {
                    cell.classList.add('pattern-preview');
                    if (minute === startSnap) {
                        const ghost = document.createElement('span');
                        ghost.className = 'preview-ghost';
                        ghost.textContent = typeName + ' ' + timeLabel(start) + '-' + timeLabel(end) + ' (' + step + 'm)';
                        cell.appendChild(ghost);
                    }
                }
            }
        });
    };

    const applyTemplateAsBase = (template) => {
        if (!template || !Array.isArray(template.blocks)) {
            return;
        }
        const providerSelect = document.getElementById('cfgProvider');
        const templateProviderId = Number(template.providerId || 0);
        if (providerSelect && templateProviderId > 0) {
            Array.from(providerSelect.options || []).forEach((opt) => {
                opt.selected = Number(opt.value || 0) === templateProviderId;
            });
        }
        const maxBookingsEl = document.getElementById('cfgMaxBookings');
        const maxBookings = maxBookingsEl ? Math.max(1, Number(maxBookingsEl.value || 1)) : 1;

        // Sort blocks: longer spans first so they claim time before shorter overlapping ones.
        const sortedBlocks = [...template.blocks].sort((a, b) => {
            const durA = (a.endMinute || 0) - (a.startMinute || 0);
            const durB = (b.endMinute || 0) - (b.startMinute || 0);
            return durB - durA;
        });

        // Track which grid-step minutes are already claimed per day to prevent visual overlap.
        const coveredByDay = new Map();

        sortedBlocks.forEach((block) => {
            const day = (block.weekday != null) ? Number(block.weekday) : -1;
            const start = (block.startMinute != null) ? Number(block.startMinute) : -1;
            const end = (block.endMinute != null) ? Number(block.endMinute) : -1;
            if (day < 0 || day > 6 || start < 0 || end <= start) {
                return;
            }
            const step = Math.max(5, Number(block.slotDuration || SLOT_MINUTES));
            const catId = Number(block.typeId || 0);
            const type = typeById.get(catId);
            const typeName = String((type && type.name) || block.typeName || 'Appointment');
            const color = String((type && type.color) || block.color || '#1d4ed8');
            const effectiveTypeId = type ? Number(type.id || 0) : (catId > 0 ? catId : Number((appointmentTypes[0] || {}).id || 0));
            if (!effectiveTypeId) {
                return;
            }

            if (!coveredByDay.has(day)) { coveredByDay.set(day, new Set()); }
            const covered = coveredByDay.get(day);

            for (let minute = start; minute < end; minute += step) {
                if (covered.has(minute)) { continue; } // already claimed by a longer block
                const key = slotKey(day, minute);
                slots.set(key, {
                    key,
                    dayIdx: day,
                    minute,
                    typeId: effectiveTypeId,
                    typeName,
                    color,
                    durationMinutes: step,
                    maxBookings,
                    staffOnly: true,
                    patientBook: true,
                    patientRebook: true
                });
                // Mark every grid step within this block's span as covered.
                const gridStep = Math.max(5, SLOT_MINUTES);
                for (let m = minute; m < minute + step; m += gridStep) { covered.add(m); }
                renderSlot(key);
            }
        });
        clearPatternPreview();
    };
    // Expose for use by the provider roster (openProviderTemplate is global, applyTemplateAsBase is not).
    window.applyTemplateAsBase = applyTemplateAsBase;

    if (Array.isArray(detectedTemplates) && detectedTemplates.length > 0) {
        const groupedByProvider = new Map();
        detectedTemplates.forEach((template) => {
            const providerId = Number(template.providerId || 0);
            const providerName = String(template.providerName || ('Provider #' + providerId) || 'Unknown Provider');
            const groupKey = String(providerId) + '|' + providerName;
            if (!groupedByProvider.has(groupKey)) {
                groupedByProvider.set(groupKey, {
                    providerId,
                    providerName,
                    templates: []
                });
            }
            groupedByProvider.get(groupKey).templates.push(template);
        });

        const providerGroups = Array.from(groupedByProvider.values()).sort((left, right) => {
            const lname = String(left.providerName || '').toLowerCase();
            const rname = String(right.providerName || '').toLowerCase();
            if (lname === rname) {
                return Number(left.providerId || 0) - Number(right.providerId || 0);
            }
            return lname < rname ? -1 : 1;
        });

        providerGroups.forEach((group) => {
            group.templates.sort((left, right) => {
                const ltemplate = String(left.templateName || '').toLowerCase();
                const rtemplate = String(right.templateName || '').toLowerCase();
                if (ltemplate === rtemplate) {
                    return Number(right.confidenceScore || 0) - Number(left.confidenceScore || 0);
                }
                return ltemplate < rtemplate ? -1 : 1;
            });

            const groupEl = document.createElement('div');
            groupEl.className = 'pattern-group';

            const titleEl = document.createElement('div');
            titleEl.className = 'pattern-group-title';
            titleEl.textContent = group.providerName + ' (' + group.templates.length + ' template' + (group.templates.length === 1 ? '' : 's') + ')';
            groupEl.appendChild(titleEl);

            group.templates.forEach((template, idx) => {
            const card = document.createElement('div');
            card.className = 'pattern-card';
            const slotCount = Number(template.slotCount || 0);
            const cadenceLabel = summarizeCadence(template);
            const timeWindowLabel = summarizeTimeWindow(template);
            const blockSummaries = (Array.isArray(template.blocks) ? template.blocks.slice(0, 3) : []).map((block) => {
                const day = DAYS[Number(block.weekday || 0)] || 'Day';
                const start = Number(block.startMinute || 0);
                const end = Number(block.endMinute || 0);
                return day.slice(0, 3) + ' ' + timeLabel(start) + '-' + timeLabel(end) + ' ' + resolveTypeName(block);
            });
            const moreCount = Math.max(0, (Array.isArray(template.blocks) ? template.blocks.length : 0) - blockSummaries.length);
            const isSample = !!template.isSample;
            const sourceBadge = isSample
                ? '<span style="background:#f59e0b;color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;margin-left:6px;">HISTORICAL SAMPLE</span>'
                : '<span style="background:#16a34a;color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:3px;margin-left:6px;">DEPLOYED</span>';
            card.innerHTML = '<strong>Template ' + (idx + 1) + ': ' + (template.templateName || 'Untitled') + '</strong>' + sourceBadge
                + '<p>Cadence: ' + cadenceLabel + ' | Window: ' + timeWindowLabel + '</p>'
                + '<p>Blocks: ' + (Array.isArray(template.blocks) ? template.blocks.length : 0)
                + ' | Slots: ' + slotCount + '</p>'
                + '<p>' + (blockSummaries.join(' | ') || 'No block detail available') + (moreCount > 0 ? ' | +' + moreCount + ' more' : '') + '</p>';

            const previewBtn = document.createElement('button');
            previewBtn.className = 'btn';
            previewBtn.type = 'button';
            previewBtn.textContent = 'Preview Full Template';
            previewBtn.addEventListener('click', () => paintTemplatePreview(template));

            const useBtn = document.createElement('button');
            useBtn.className = 'btn primary';
            useBtn.type = 'button';
            useBtn.textContent = 'Load Template';
            useBtn.style.marginLeft = '6px';
            useBtn.addEventListener('click', () => applyTemplateAsBase(template));

            card.appendChild(previewBtn);
            card.appendChild(useBtn);
            groupEl.appendChild(card);
            });

            patternCards.appendChild(groupEl);
        });
    }
}

function initDragTypes() {
    document.querySelectorAll('#typeLegend .chip').forEach((chip) => {
        chip.addEventListener('dragstart', onDragStartType);
        chip.addEventListener('dblclick', () => {
            const targetKeys = selectedSlotKeys.size ? Array.from(selectedSlotKeys) : (selectedSlotKey ? [selectedSlotKey] : []);
            if (!targetKeys.length) {
                alert('Select a slot first, then double-click an appointment type.');
                return;
            }
            const payload = {
                typeId: parseInt(chip.dataset.typeId || '0', 10),
                typeName: chip.dataset.typeName || 'Appointment',
                durationMinutes: parseInt(chip.dataset.typeDuration || '0', 10) || getDefaultDurationForType(parseInt(chip.dataset.typeId || '0', 10)),
                color: chip.dataset.typeColor || '#1d4ed8'
            };
            targetKeys.forEach((key) => applyTypeToSlotKey(key, payload, { skipSelect: true }));
            if (selectedSlotKey) {
                selectSlot(selectedSlotKey, { additive: true });
            }
        });
    });
}

function openSlotModal(key) {
    const [dayIdxRaw, minuteRaw] = key.split('|');
    const dayIdx = parseInt(dayIdxRaw, 10);
    const minute = parseInt(minuteRaw, 10);
    if (Number.isNaN(dayIdx) || Number.isNaN(minute)) {
        return;
    }

    const slot = slots.get(key) || {
        key,
        dayIdx,
        minute,
        typeId: Number((appointmentTypes[0] || {}).id || 0),
        typeName: String((appointmentTypes[0] || {}).name || 'Appointment'),
        color: String((appointmentTypes[0] || {}).color || '#1d4ed8'),
        durationMinutes: getDefaultDurationForType(Number((appointmentTypes[0] || {}).id || 0)),
        staffOnly: true,
        patientBook: document.getElementById('cfgPatientBook').checked,
        patientRebook: document.getElementById('cfgPatientRebook').checked
    };

    slotModalKey = key;
    selectSlot(key);

    document.getElementById('slotEditTitle').textContent = 'Edit Slot';
    document.getElementById('slotEditWhen').value = DAYS[slot.dayIdx] + ' at ' + timeLabel(slot.minute);
    document.getElementById('slotEditFacility').value = String(resolveSlotFacility(slot.facilityId, slot.typeId));
    document.getElementById('slotEditType').value = String(slot.typeId || '');
    const typeDefaultDuration = getDefaultDurationForType(Number(slot.typeId || 0));
    const currentDuration = Number(slot.durationMinutes || 0);
    const initialDuration = currentDuration > 0 ? currentDuration : typeDefaultDuration;
    document.getElementById('slotEditDuration').value = String(initialDuration);
    document.getElementById('slotEditStaffOnly').checked = !!slot.staffOnly;
    document.getElementById('slotEditPatientBook').checked = !!slot.patientBook;
    document.getElementById('slotEditPatientRebook').checked = !!slot.patientRebook;

    const modal = document.getElementById('slotEditModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeSlotModal() {
    const modal = document.getElementById('slotEditModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    slotModalKey = null;
}

function bindSlotModal() {
    const modal = document.getElementById('slotEditModal');
    const closeTop = document.getElementById('btnSlotEditCloseTop');
    const closeBtn = document.getElementById('btnSlotEditClose');
    const saveBtn = document.getElementById('btnSlotEditSave');
    const deleteBtn = document.getElementById('btnSlotEditDelete');
    const typeInput = document.getElementById('slotEditType');
    const durationInput = document.getElementById('slotEditDuration');

    typeInput.addEventListener('change', () => {
        const typeId = Number(typeInput.value || 0);
        durationInput.value = String(getDefaultDurationForType(typeId));
    });

    closeTop.addEventListener('click', closeSlotModal);
    closeBtn.addEventListener('click', closeSlotModal);

    saveBtn.addEventListener('click', () => {
        if (!slotModalKey) {
            return;
        }
        const [dayIdxRaw, minuteRaw] = slotModalKey.split('|');
        const dayIdx = parseInt(dayIdxRaw, 10);
        const minute = parseInt(minuteRaw, 10);
        const typeId = Number(typeInput.value || 0);
        const type = getAppointmentTypeById(typeId);
        if (!type) {
            alert('Select an appointment type.');
            return;
        }
        const duration = normalizeDurationMinutes(Number(durationInput.value || SLOT_MINUTES) || SLOT_MINUTES);
        const facilityVal = (document.getElementById('slotEditFacility') || {}).value || '';
        clearOverlapsForSpan(dayIdx, minute, duration, slotModalKey);
        slots.set(slotModalKey, {
            key: slotModalKey,
            dayIdx,
            minute,
            facilityId: facilityVal,
            typeId: Number(type.id || typeId),
            typeName: String(type.name || 'Appointment'),
            color: String(type.color || '#1d4ed8'),
            durationMinutes: duration,
            staffOnly: !!document.getElementById('slotEditStaffOnly').checked,
            patientBook: !!document.getElementById('slotEditPatientBook').checked,
            patientRebook: !!document.getElementById('slotEditPatientRebook').checked
        });
        renderAllSlots();
        selectSlot(slotModalKey);
        closeSlotModal();
    });

    deleteBtn.addEventListener('click', () => {
        if (!slotModalKey) {
            return;
        }
        slots.delete(slotModalKey);
        renderAllSlots();
        selectSlot(slotModalKey);
        closeSlotModal();
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeSlotModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeSlotModal();
        }
    });
}

function applyChipContrast() {
    const getRgb = (hex) => {
        const clean = String(hex || '').trim().replace('#', '');
        if (!/^[0-9a-fA-F]{6}$/.test(clean)) {
            return null;
        }
        return {
            r: parseInt(clean.slice(0, 2), 16),
            g: parseInt(clean.slice(2, 4), 16),
            b: parseInt(clean.slice(4, 6), 16)
        };
    };

    const luminance = (rgb) => {
        const toLinear = (v) => {
            const s = v / 255;
            return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
        };
        return (0.2126 * toLinear(rgb.r)) + (0.7152 * toLinear(rgb.g)) + (0.0722 * toLinear(rgb.b));
    };

    document.querySelectorAll('#typeLegend .chip').forEach((chip) => {
        const color = String(chip.dataset.typeColor || '').trim();
        const rgb = getRgb(color);
        if (!rgb) {
            return;
        }
        const lightBg = luminance(rgb) > 0.45;
        const ink = lightBg ? '#102a2d' : '#ffffff';
        const subInk = lightBg ? 'rgba(16,42,45,0.88)' : 'rgba(255,255,255,0.9)';
        chip.style.color = ink;
        const small = chip.querySelector('small');
        if (small) {
            small.style.color = subInk;
            small.style.opacity = '1';
        }
    });
}

buildHeaders();
buildGrid();
(function() {
    const facEl = document.getElementById('cfgFacility');
    if (facEl) { facEl.addEventListener('change', function() { buildHeaders(); renderAllSlots(); }); }
})();
initDragTypes();
applyChipContrast();
bindInspector();
bindToolbar();
bindAskPrompt();
bindPatterns();
bindManageTypesModal();
bindSlotModal();
initCollapsibleSections();
bindSnapshotPanel();
bindCadenceUI();
bindProviderCopyFrom();
queueStableRender();
window.addEventListener('load', queueStableRender);
window.addEventListener('resize', queueStableRender);

// ─── Cadence UI — show/hide 4-week tabs, monthly options ─────────────
function bindCadenceUI() {
    const cadenceEl        = document.getElementById('cfgCadence');
    const fourWeekTabs     = document.getElementById('fourWeekTabs');
    const fourWeekPanel    = document.getElementById('cfgFourWeekPanel');
    const monthlyPanel     = document.getElementById('cfgMonthlyPanel');
    const monthlyModeEl    = document.getElementById('cfgMonthlyMode');
    const nthPanel         = document.getElementById('cfgMonthlyNthPanel');
    const datePanel        = document.getElementById('cfgMonthlyDatePanel');

    // Slot state per 4-week tab (persisted in memory while editor is open).
    // slotsByWeek[0..3] = Map of key → payload (same format as main slot state)
    const slotsByWeek = [new Map(), new Map(), new Map(), new Map()];
    let activeWeek = 0;

    const oneDayPanel  = document.getElementById('cfgOneDayPanel');
    const oneDaySelect = document.getElementById('cfgOneDaySelect');
    const cadenceHint  = document.getElementById('cadenceHint');

    const CADENCE_HINTS = {
        one_day:    '📅 One Day — drag slots into the single column. Deploy writes these slots to every matching visit date in your horizon.',
        workdays:   '📅 Every Workday — Mon–Fri columns shown. Same slot pattern repeats on every workday in the horizon.',
        weekly:     '📅 Weekly — set a different pattern for each day of the week. Most common for specialists with varying daily schedules.',
        four_week:  '📅 4-Week Rotation — use Week A/B/C/D tabs. 1st Thursday = Week A, 2nd = Week B, 3rd = Week C, 4th = Week D. Perfect for alternating facilities.',
        monthly:    '📅 Monthly — configure the time slots below. The pattern deploys on every matching occurrence (e.g. every 2nd Tuesday) in the horizon.'
    };

    const showCadenceOptions = () => {
        const v = cadenceEl?.value || 'weekly';
        if (fourWeekTabs)  { fourWeekTabs.style.display  = v === 'four_week' ? '' : 'none'; }
        if (fourWeekPanel) { fourWeekPanel.style.display  = v === 'four_week' ? '' : 'none'; }
        if (monthlyPanel)  { monthlyPanel.style.display   = v === 'monthly'   ? '' : 'none'; }
        if (oneDayPanel)   { oneDayPanel.style.display    = v === 'one_day'   ? '' : 'none'; }
        if (cadenceHint)   {
            cadenceHint.textContent = CADENCE_HINTS[v] || '';
            cadenceHint.style.display = CADENCE_HINTS[v] ? '' : 'none';
        }
        // Rebuild grid columns to match cadence
        if (typeof rebuildGridForCadence === 'function' && document.getElementById('calendarGrid')?.children.length > 0) {
            rebuildGridForCadence();
        }
    };

    if (cadenceEl) { cadenceEl.addEventListener('change', showCadenceOptions); showCadenceOptions(); }
    // Rebuild grid when the one-day selector changes
    if (oneDaySelect) {
        oneDaySelect.addEventListener('change', () => {
            if (typeof rebuildGridForCadence === 'function') { rebuildGridForCadence(); }
        });
    }

    // Monthly mode toggle
    if (monthlyModeEl) {
        monthlyModeEl.addEventListener('change', () => {
            const m = monthlyModeEl.value;
            if (nthPanel)  { nthPanel.style.display  = m === 'nth_weekday' ? '' : 'none'; }
            if (datePanel) { datePanel.style.display  = m === 'date_of_month' ? '' : 'none'; }
        });
    }

    // 4-week tab switching: save current grid to slot store, load target week
    document.querySelectorAll('.four-week-tab').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetWeek = parseInt(btn.dataset.week || '0', 10);
            if (targetWeek === activeWeek) { return; }

            // Save current week's slots
            document.querySelectorAll('.grid-slot[data-day][data-minute]').forEach((cell) => {
                const payload = JSON.parse(cell.dataset.slotPayload || '{}');
                const key = cell.dataset.day + '_' + cell.dataset.minute;
                if (payload && payload.typeId > 0) {
                    slotsByWeek[activeWeek].set(key, payload);
                } else {
                    slotsByWeek[activeWeek].delete(key);
                }
            });

            // Clear grid
            document.querySelectorAll('.grid-slot').forEach((c) => {
                c.dataset.slotPayload = '{}';
                c.style.background = '';
                c.textContent = '';
                c.classList.remove('filled-slot');
            });

            // Load target week's slots
            slotsByWeek[targetWeek].forEach((payload, key) => {
                const [day, minute] = key.split('_');
                const cell = document.querySelector('.grid-slot[data-day="' + day + '"][data-minute="' + minute + '"]');
                if (cell && typeof applyTypeToSlotKey === 'function') {
                    applyTypeToSlotKey(key, payload);
                }
            });

            // Update active state
            activeWeek = targetWeek;
            document.querySelectorAll('.four-week-tab').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Patch the deploy slot collector to include weekIndex
    const origGetSlotPayload = window._getDeploySlotPayload;
    // We intercept the slotPayload built in the deploy handler by exposing the
    // four-week data globally so the deploy builder can include weekIndex.
    window.getFourWeekSlotPayload = () => {
        const cadence = cadenceEl?.value || 'weekly';
        if (cadence !== 'four_week') { return null; }

        // Save current active tab first
        document.querySelectorAll('.grid-slot[data-day][data-minute]').forEach((cell) => {
            const payload = JSON.parse(cell.dataset.slotPayload || '{}');
            const key = cell.dataset.day + '_' + cell.dataset.minute;
            if (payload && payload.typeId > 0) {
                slotsByWeek[activeWeek].set(key, payload);
            } else {
                slotsByWeek[activeWeek].delete(key);
            }
        });

        const combined = [];
        slotsByWeek.forEach((weekMap, weekIdx) => {
            weekMap.forEach((payload, key) => {
                const [day, minute] = key.split('_');
                combined.push({ dayIdx: parseInt(day, 10), minute: parseInt(minute, 10), weekIndex: weekIdx, ...payload });
            });
        });
        return combined;
    };
}

// ─── Provider copy-from ───────────────────────────────────────────────
function bindProviderCopyFrom() {
    // When a provider is selected and has no template, offer to copy from another.
    // We add a "Copy from provider" button that appears next to the provider selector.
    const providerSelect = document.getElementById('csProvider');
    if (!providerSelect) { return; }

    const copyBtn = document.createElement('button');
    copyBtn.type = 'button';
    copyBtn.className = 'btn';
    copyBtn.style.cssText = 'display:none;margin-left:6px;font-size:11px;';
    copyBtn.textContent = '📋 Start from another provider\'s template';
    providerSelect.parentNode.insertBefore(copyBtn, providerSelect.nextSibling);

    const checkForTemplate = () => {
        const pid = Number(providerSelect.value || 0);
        if (!pid) { copyBtn.style.display = 'none'; return; }
        // Show copy button if grid is empty
        const hasSlots = Array.from(document.querySelectorAll('.grid-slot[data-slot-payload]'))
            .some((c) => { try { return JSON.parse(c.dataset.slotPayload || '{}').typeId > 0; } catch(e) { return false; } });
        copyBtn.style.display = hasSlots ? 'none' : '';
    };

    providerSelect.addEventListener('change', () => setTimeout(checkForTemplate, 300));

    copyBtn.addEventListener('click', () => {
        // Build list of providers that have MEDEX_STUDIO slots (from detectedTemplates)
        const sources = (Array.isArray(window.detectedTemplates) ? window.detectedTemplates : [])
            .filter((t) => t.providerId && Number(providerSelect.value || 0) !== Number(t.providerId));

        if (!sources.length) {
            alert('No other providers have deployed templates yet.');
            return;
        }

        const options = sources.map((t) => t.providerId + ' — ' + (t.providerName || 'Provider #' + t.providerId)).join('\n');
        const choice = prompt('Enter the provider ID to copy from:\n\n' + options);
        if (!choice) { return; }

        const chosenId = parseInt(choice.trim().split(/[^0-9]/)[0], 10);
        const srcTemplate = sources.find((t) => Number(t.providerId) === chosenId);
        if (!srcTemplate || !Array.isArray(srcTemplate.blocks)) {
            alert('Provider #' + chosenId + ' has no detected template.');
            return;
        }

        // Apply the blocks to the grid
        document.querySelectorAll('.grid-slot').forEach((c) => {
            c.dataset.slotPayload = '{}';
            c.style.background = '';
            c.textContent = '';
            c.classList.remove('filled-slot');
        });
        srcTemplate.blocks.forEach((block) => {
            if (typeof applyTypeToSlotKey === 'function' && block.typeId > 0) {
                const key = block.weekday + '_' + block.startMinute;
                applyTypeToSlotKey(key, {
                    typeId: block.typeId,
                    typeName: block.typeName,
                    durationMinutes: block.slotDuration || 30,
                    color: block.color,
                    facilityId: resolveSlotFacility(0, block.typeId)
                });
            }
        });

        copyBtn.style.display = 'none';
        alert('Template from "' + (srcTemplate.providerName || 'Provider #' + chosenId) + '" loaded into the grid. Review and deploy when ready.');
    });
}

// ─── Snapshot / Rollback / Copy ─────────────────────────────────────
function bindSnapshotPanel() {
    const saveBtn  = document.getElementById('btnSaveSnapshot');
    const copyBtn  = document.getElementById('btnCopySnapshot');
    const listEl   = document.getElementById('snapshotList');
    const statusEl = document.getElementById('snapshotStatus');
    if (!saveBtn || !copyBtn) { return; }

    const providerSelect = document.getElementById('csProvider');
    const getProviderId  = () => providerSelect ? Number(providerSelect.value || 0) : 0;

    const setStatus = (msg, ok) => {
        if (!statusEl) { return; }
        statusEl.textContent = msg || '';
        statusEl.style.color = ok === true ? '#166534' : ok === false ? '#991b1b' : '#555';
    };

    // Capture current grid as a JSON blob
    const captureGrid = () => {
        const captured = [];
        document.querySelectorAll('.grid-slot[data-day][data-minute]').forEach((cell) => {
            const payload = JSON.parse(cell.dataset.slotPayload || '{}');
            if (payload && payload.typeId > 0) {
                captured.push({ dayIdx: Number(cell.dataset.day), minute: Number(cell.dataset.minute), ...payload });
            }
        });
        return JSON.stringify({
            slots:       captured,
            cadence:     document.getElementById('cfgCadence')?.value || 'weekly',
            strictness:  document.querySelector('input[name="cfgStrictness"]:checked')?.value || 'guide',
            horizonDays: document.getElementById('cfgHorizonDays')?.value || '30',
        });
    };

    // Restore a snapshot into the grid
    const restoreSnapshot = async (snapId, snapName) => {
        if (!confirm('Load snapshot "' + snapName + '" into the grid?\nUnsaved grid changes will be lost.')) { return; }
        const body = new URLSearchParams({ action: 'restore_snapshot', snapshot_id: snapId });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        try {
            const resp = await fetch(location.href, { method: 'POST', body });
            const data = await resp.json();
            if (data.success && data.template_json) {
                const tpl = JSON.parse(data.template_json);
                document.querySelectorAll('.grid-slot').forEach((c) => {
                    c.dataset.slotPayload = '{}';
                    c.style.background = '';
                    c.textContent = '';
                    c.classList.remove('filled-slot');
                });
                if (Array.isArray(tpl.slots)) {
                    tpl.slots.forEach((slot) => {
                        if (typeof applyTypeToSlotKey === 'function') {
                            applyTypeToSlotKey(slot.dayIdx + '_' + slot.minute, slot);
                        }
                    });
                }
                setStatus('✓ Loaded: ' + data.name, true);
                loadSnapshotList();
            } else {
                setStatus(data.error || 'Restore failed.', false);
            }
        } catch (e) { setStatus('Network error', false); }
    };

    // Load and render practice-wide snapshots (all providers, newest first)
    const loadSnapshotList = async () => {
        if (!listEl) { return; }
        listEl.innerHTML = '<em style="color:#888;font-size:11px;">Loading…</em>';
        // Pass provider_id=0 to signal "all practice snapshots"
        const body = new URLSearchParams({ action: 'list_snapshots', provider_id: 0 });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        try {
            const resp = await fetch(location.href, { method: 'POST', body });
            const data = await resp.json();
            if (!data.success) { listEl.innerHTML = '<em style="color:#991b1b;">' + (data.error || 'Error loading snapshots') + '</em>'; return; }
            const snaps = data.snapshots || [];

            if (!snaps.length) {
                listEl.innerHTML = '<div style="padding:8px 2px;color:#888;font-size:11px;font-style:italic;">Nothing saved yet for your practice. Use <strong>Save Snapshot</strong> to capture the current grid.</div>';
                return;
            }

            // Group by provider name for readability
            const byProvider = {};
            snaps.forEach((s) => {
                const pKey = s.providerName || ('Provider #' + s.providerId);
                if (!byProvider[pKey]) { byProvider[pKey] = []; }
                byProvider[pKey].push(s);
            });

            const groupCount = Object.keys(byProvider).length;
            let html = '';
            Object.entries(byProvider).forEach(([provName, provSnaps]) => {
                if (groupCount > 1) {
                    html += '<div style="font-size:10px;font-weight:700;color:#4b5563;padding:4px 0 2px;border-top:1px solid #e5e7eb;margin-top:4px;">'
                        + provName.replace(/</g,'&lt;') + '</div>';
                }
                provSnaps.forEach((s) => {
                    const d = new Date(s.date).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});
                    const label = s.name ? s.name.replace(/</g,'&lt;') : '(unnamed)';
                    html += '<div style="display:flex;align-items:center;gap:4px;padding:2px 0;">'
                        + '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;" title="' + s.name + '">' + label + '</span>'
                        + '<span style="color:#9ca3af;font-size:10px;white-space:nowrap;flex-shrink:0;">' + d + '</span>'
                        + '<button class="btn" style="padding:1px 7px;font-size:10px;flex-shrink:0;" '
                        +   'data-snap-id="' + s.id + '" data-snap-name="' + label + '" data-action="restore-snap">Load</button>'
                        + '</div>';
                });
            });

            listEl.innerHTML = html;

            listEl.querySelectorAll('button[data-action="restore-snap"]').forEach((btn) => {
                btn.addEventListener('click', () => restoreSnapshot(btn.dataset.snapId, btn.dataset.snapName));
            });
        } catch (e) {
            listEl.innerHTML = '<em style="color:#991b1b;font-size:11px;">Network error loading snapshots</em>';
        }
    };

    // Auto-load snapshot list when the panel is toggled open
    const snapSection = document.getElementById('collapseSnapshotsBody');
    if (snapSection) {
        const observer = new MutationObserver(() => {
            if (snapSection.style.display !== 'none') { loadSnapshotList(); }
        });
        observer.observe(snapSection, { attributes: true, attributeFilter: ['style'] });
    }

    saveBtn.addEventListener('click', async () => {
        const pid = getProviderId();
        if (!pid) { setStatus('Select a provider first.', false); return; }
        const name = (prompt('Snapshot name (leave blank for auto-name):') ?? '').trim();
        if (name === null) { return; }
        const body = new URLSearchParams({ action: 'save_snapshot', provider_id: pid, snapshot_name: name, template_json: captureGrid() });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        try {
            const resp = await fetch(location.href, { method: 'POST', body });
            const data = await resp.json();
            if (data.success) {
                setStatus('✓ Snapshot saved.', true);
                loadSnapshotList();
            } else {
                setStatus(data.error || 'Error', false);
            }
        } catch (e) { setStatus('Network error', false); }
    });

    copyBtn.addEventListener('click', async () => {
        const pid = getProviderId();
        if (!pid) { setStatus('Select a provider first.', false); return; }
        const targetPid = prompt('Target provider ID to copy current template to:');
        if (!targetPid || isNaN(Number(targetPid))) { return; }
        const newName = (prompt('Name for the copy:', 'Copy from provider #' + pid) ?? '').trim();
        const body = new URLSearchParams({
            action: 'save_snapshot', provider_id: Number(targetPid),
            snapshot_name: newName || ('Copy from provider #' + pid),
            template_json: captureGrid(),
            notes: 'Copied from provider #' + pid
        });
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        try {
            const resp = await fetch(location.href, { method: 'POST', body });
            const data = await resp.json();
            if (data.success) {
                setStatus('✓ Copied to provider #' + targetPid, true);
                loadSnapshotList();
            } else {
                setStatus(data.error || 'Error', false);
            }
        } catch (e) { setStatus('Network error', false); }
    });
}

// =====================================================================
// Scheduling Rules card — save on change
// =====================================================================
function saveSchedulingRules() {
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') { top.restoreSession(); }
    var enforcement = document.querySelector('input[name="sr_template_enforcement"]:checked');
    var dblBook     = document.querySelector('input[name="sr_double_booking"]:checked');
    if (!enforcement || !dblBook) { return; }
    var status = document.getElementById('srSaveStatus');
    if (status) { status.textContent = 'Saving…'; status.style.display = ''; status.style.color = 'var(--cs-subtle)'; }
    var fd = new FormData();
    fd.append('action', 'save_scheduling_rules');
    fd.append('template_enforcement', enforcement.value);
    fd.append('allow_double_booking', dblBook.value);
    fetch(window.location.href, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { throw new Error(d.error || 'Failed'); }
            if (status) { status.textContent = 'Saved.'; status.style.display = ''; status.style.color = '#1c4568'; setTimeout(function() { if (status) { status.style.display = 'none'; } }, 2000); }
        })
        .catch(function(e) {
            if (status) { status.textContent = 'Error: ' + e.message; status.style.display = ''; status.style.color = '#b91c1c'; }
        });
}

// =====================================================================
// Rescheduler Panel — open inside Calendar Studio (no cross-origin nav)
// =====================================================================
var _rpProviderRules = {};
var _rpCurrentPid = null;

function openReschedulerModal() {
    var modal = document.getElementById('reschedulerModal');
    if (!modal) { return; }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    loadReschedulerRulesPanel();
}

function closeReschedulerModal() {
    var modal = document.getElementById('reschedulerModal');
    if (!modal) { return; }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    closeRpProviderOverride();
}

function loadReschedulerRulesPanel() {
    var status = document.getElementById('rpSaveStatus');
    if (status) { status.textContent = 'Loading…'; status.style.display = ''; status.style.color = 'var(--cs-subtle)'; }
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') { top.restoreSession(); }
    var fd = new FormData();
    fd.append('action', 'get_rescheduler_rules');
    fetch(window.location.href, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { throw new Error(d.error || 'Failed'); }
            var rules    = d.rules || {};
            var defaults = rules.defaults || {};
            _rpProviderRules = rules.providers || {};
            var defMap = { max_offers: 3, min_hours_before: 1, max_days_before: 30, max_days_after: 60, slot_hold_minutes: 15 };
            Object.keys(defMap).forEach(function(f) {
                var el = document.getElementById('rp-' + f);
                if (el) { el.value = (defaults[f] !== undefined) ? defaults[f] : defMap[f]; }
            });
            var sameDayEl = document.getElementById('rp-allow_same_day');
            if (sameDayEl) { sameDayEl.checked = !!defaults.allow_same_day; }
            _updateRpProviderBadges();
            if (status) { status.style.display = 'none'; }
        })
        .catch(function(e) {
            if (status) { status.textContent = 'Could not load: ' + e.message; status.style.display = ''; status.style.color = '#b91c1c'; }
        });
}

function saveReschedulerRulesPanel(silent) {
    var status = document.getElementById('rpSaveStatus');
    if (!silent && status) { status.textContent = 'Saving…'; status.style.display = ''; status.style.color = 'var(--cs-subtle)'; }
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') { top.restoreSession(); }
    var fd = new FormData();
    fd.append('action', 'save_rescheduler_rules');
    fd.append('pd_max_offers',        document.getElementById('rp-max_offers').value);
    fd.append('pd_min_hours_before',  document.getElementById('rp-min_hours_before').value);
    fd.append('pd_max_days_before',   document.getElementById('rp-max_days_before').value);
    fd.append('pd_max_days_after',    document.getElementById('rp-max_days_after').value);
    fd.append('pd_slot_hold_minutes', document.getElementById('rp-slot_hold_minutes').value);
    fd.append('pd_allow_same_day',    document.getElementById('rp-allow_same_day').checked ? '1' : '0');
    fd.append('provider_rules',       JSON.stringify(_rpProviderRules));
    return fetch(window.location.href, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { throw new Error(d.error || 'Failed'); }
            if (!silent && status) {
                status.textContent = 'Saved.';
                status.style.display = '';
                status.style.color = '#1c4568';
                setTimeout(function() { if (status) { status.style.display = 'none'; } }, 2500);
            }
            return d;
        })
        .catch(function(e) {
            if (!silent && status) { status.textContent = 'Failed: ' + e.message; status.style.display = ''; status.style.color = '#b91c1c'; }
        });
}

function resetReschedulerRulesPanel() {
    document.getElementById('rp-max_offers').value        = 3;
    document.getElementById('rp-min_hours_before').value  = 1;
    document.getElementById('rp-max_days_before').value   = 30;
    document.getElementById('rp-max_days_after').value    = 60;
    document.getElementById('rp-slot_hold_minutes').value = 15;
    document.getElementById('rp-allow_same_day').checked  = false;
}

function _updateRpProviderBadges() {
    document.querySelectorAll('.rp-pvbtn-wrap').forEach(function(wrap) {
        var pid = String(wrap.getAttribute('data-pid'));
        var has = !!_rpProviderRules[pid];
        if (has) {
            wrap.classList.add('has-override');
        } else {
            wrap.classList.remove('has-override');
        }
    });
}

function deleteRpProviderOverride(pid) {
    _rpCurrentPid = String(pid);
    delete _rpProviderRules[_rpCurrentPid];
    _rpCurrentPid = null;
    _updateRpProviderBadges();
    closeRpProviderOverride();
    saveReschedulerRulesPanel(true).then(function() {
        var status = document.getElementById('rpSaveStatus');
        if (status) { status.textContent = 'Override removed.'; status.style.display = ''; status.style.color = 'var(--cs-subtle)'; setTimeout(function() { if (status) { status.style.display = 'none'; } }, 2500); }
    });
}

function openRpProviderOverride(pid) {
    _rpCurrentPid = String(pid);
    var form   = document.getElementById('rpProviderForm');
    var nameEl = document.getElementById('rpProviderFormName');
    var btn    = document.querySelector('.rp-pvbtn[data-pid="' + pid + '"]');
    if (nameEl) { nameEl.textContent = btn ? btn.textContent.trim() : ('Provider #' + pid); }
    if (form)  { form.style.display = ''; }
    document.querySelectorAll('.rp-pvbtn').forEach(function(b) {
        b.style.outline = (b.getAttribute('data-pid') === String(pid)) ? '2px solid var(--cs-accent)' : '';
    });
    var rule   = _rpProviderRules[_rpCurrentPid] || {};
    var defMap = { max_offers: 3, min_hours_before: 1, max_days_before: 30, max_days_after: 60, slot_hold_minutes: 15 };
    Object.keys(defMap).forEach(function(f) {
        var el = document.getElementById('rpv-' + f);
        if (el) { el.value = (rule[f] !== undefined) ? rule[f] : defMap[f]; }
    });
    var sameDayEl = document.getElementById('rpv-allow_same_day');
    if (sameDayEl) { sameDayEl.checked = !!rule.allow_same_day; }
    var enabledEl = document.getElementById('rpv-enabled');
    if (enabledEl) { enabledEl.checked = (rule.enabled !== false); }
}

function closeRpProviderOverride() {
    _rpCurrentPid = null;
    var form = document.getElementById('rpProviderForm');
    if (form) { form.style.display = 'none'; }
    document.querySelectorAll('.rp-pvbtn').forEach(function(b) { b.style.outline = ''; });
}

function saveRpProviderOverride() {
    if (!_rpCurrentPid) { return; }
    _rpProviderRules[_rpCurrentPid] = {
        enabled:          document.getElementById('rpv-enabled').checked,
        allow_same_day:   document.getElementById('rpv-allow_same_day').checked,
        max_offers:       parseInt(document.getElementById('rpv-max_offers').value, 10)        || 3,
        min_hours_before: parseInt(document.getElementById('rpv-min_hours_before').value, 10) || 1,
        max_days_before:  parseInt(document.getElementById('rpv-max_days_before').value, 10)  || 30,
        max_days_after:   parseInt(document.getElementById('rpv-max_days_after').value, 10)   || 60,
        slot_hold_minutes:parseInt(document.getElementById('rpv-slot_hold_minutes').value, 10)|| 15,
    };
    _updateRpProviderBadges();
    var pvStatus = document.getElementById('rpvSaveStatus');
    saveReschedulerRulesPanel(true).then(function() {
        if (pvStatus) { pvStatus.textContent = 'Saved'; pvStatus.style.display = ''; pvStatus.style.color = '#1c4568'; setTimeout(function() { if (pvStatus) { pvStatus.style.display = 'none'; } }, 2000); }
    });
}

function clearRpProviderOverride() {
    if (!_rpCurrentPid) { return; }
    delete _rpProviderRules[_rpCurrentPid];
    _rpCurrentPid = null;
    _updateRpProviderBadges();
    closeRpProviderOverride();
    saveReschedulerRulesPanel(true).then(function() {
        var status = document.getElementById('rpSaveStatus');
        if (status) { status.textContent = 'Override removed.'; status.style.display = ''; status.style.color = 'var(--cs-subtle)'; setTimeout(function() { if (status) { status.style.display = 'none'; } }, 2500); }
    });
}

// Patient Rescheduler kill switch toggle
(function() {
    const toggle = document.getElementById('reschedulerToggle');
    if (!toggle) { return; }
    toggle.addEventListener('change', function() {
        const paused = !toggle.checked; // checked = active (not paused)
        const spinner = document.getElementById('reschedulerSaveSpinner');
        const slider = document.getElementById('reschedulerSlider');
        const thumb = document.getElementById('reschedulerThumb');
        if (spinner) { spinner.style.visibility = 'visible'; }
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
        const fd = new FormData();
        fd.append('action', 'set_rescheduler_paused');
        fd.append('paused', paused ? '1' : '0');
        fetch(window.location.href, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.success) {
                    toggle.checked = !paused; // revert toggle
                    if (d.blocked && d.message) {
                        // Show inline alert below the card
                        var alertEl = document.getElementById('reschedulerBlockedAlert');
                        if (!alertEl) {
                            alertEl = document.createElement('div');
                            alertEl.id = 'reschedulerBlockedAlert';
                            alertEl.style.cssText = 'margin-top:8px;padding:10px 12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;color:#991b1b;font-size:12px;line-height:1.5;';
                            var card = document.getElementById('reschedulerToggle');
                            if (card) { card.closest('.studio-card') && card.closest('.studio-card').appendChild(alertEl); }
                        }
                        alertEl.textContent = d.message;
                        alertEl.style.display = '';
                        setTimeout(function() { if (alertEl) { alertEl.style.display = 'none'; } }, 12000);
                    }
                    return;
                }
                // Clear any previous blocked alert on success
                var alertEl = document.getElementById('reschedulerBlockedAlert');
                if (alertEl) { alertEl.style.display = 'none'; }
                const isNowPaused = !!d.paused;
                if (slider) { slider.style.background = isNowPaused ? '#ef4444' : '#1c4568'; }
                if (thumb) { thumb.style.left = isNowPaused ? '3px' : '21px'; }
            })
            .catch(function() {
                toggle.checked = !paused;
            })
            .finally(function() { if (spinner) { spinner.style.visibility = 'hidden'; } });
    });
}());
</script>

<!-- =========================================================
     Rescheduler Rules Modal
     ========================================================= -->
<div class="rescheduler-modal" id="reschedulerModal" aria-hidden="true"
     onclick="if(event.target===this){closeReschedulerModal();}">
    <div class="rescheduler-dialog" role="dialog" aria-modal="true" aria-label="<?php echo attr(xl('Patient Rescheduler Rules')); ?>">

        <div class="rescheduler-dialog-head">
            <div>
                <strong style="font-size:16px;color:var(--cs-ink);"><?php echo xlt('Patient Rescheduler Rules'); ?></strong>
                <p style="margin:4px 0 0;font-size:12px;color:var(--cs-subtle);"><?php echo xlt('Set practice-wide defaults. Select a provider below to fine-tune for that provider only.'); ?></p>
            </div>
            <button class="btn" type="button" onclick="closeReschedulerModal()" style="flex-shrink:0;"><?php echo xlt('Close'); ?></button>
        </div>

        <div class="rescheduler-dialog-body">

            <!-- Practice-wide defaults -->
            <div style="background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:20px 22px;margin-bottom:16px;">
                <h3 style="font-size:13px;font-weight:700;color:var(--cs-ink);margin:0 0 14px 0;"><?php echo xlt('Practice-wide Defaults'); ?></h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px 20px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Openings to offer'); ?></label>
                        <input type="number" id="rp-max_offers" min="1" max="12" value="3" style="width:100%;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Min hours before visit'); ?></label>
                        <input type="number" id="rp-min_hours_before" min="0" max="168" value="1" style="width:100%;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Days may move earlier'); ?></label>
                        <input type="number" id="rp-max_days_before" min="0" max="365" value="30" style="width:100%;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Days may move later'); ?></label>
                        <input type="number" id="rp-max_days_after" min="0" max="365" value="60" style="width:100%;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;" title="<?php echo attr(xl('How long to hold an offered slot before releasing it back to the pool')); ?>"><?php echo xlt('Hold offered slots (min)'); ?></label>
                        <input type="number" id="rp-slot_hold_minutes" min="5" max="60" value="15" style="width:100%;box-sizing:border-box;">
                    </div>
                    <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                            <input type="checkbox" id="rp-allow_same_day">
                            <?php echo xlt('Allow same-day reschedules'); ?>
                        </label>
                    </div>
                </div>
                <div style="margin-top:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button class="btn primary" type="button" onclick="saveReschedulerRulesPanel()"><?php echo xlt('Save Rules'); ?></button>
                    <button class="btn" type="button" onclick="resetReschedulerRulesPanel()"><?php echo xlt('Reset Defaults'); ?></button>
                    <span id="rpSaveStatus" style="font-size:12px;display:none;"></span>
                </div>
            </div>

            <!-- Provider fine-tuning -->
            <div style="background:var(--cs-surface);border:1px solid var(--cs-border);border-radius:10px;padding:20px 22px;">
                <h3 style="font-size:13px;font-weight:700;color:var(--cs-ink);margin:0 0 4px 0;"><?php echo xlt('Provider Fine-tuning'); ?> <span style="font-size:11px;font-weight:400;color:var(--cs-subtle);"><?php echo xlt('(optional)'); ?></span></h3>
                <p style="margin:0 0 14px 0;font-size:12px;color:var(--cs-subtle);"><?php echo xlt('Select a provider to override the practice defaults for that provider only.'); ?></p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;" id="rpProviderBtns">
                    <?php foreach ($providers as $_p): ?>
                    <div class="rp-pvbtn-wrap" data-pid="<?php echo attr((string)$_p['id']); ?>">
                        <button type="button" class="btn rp-pvbtn" data-pid="<?php echo attr((string)$_p['id']); ?>"
                                style="font-size:11px;padding:5px 12px;"
                                onclick="openRpProviderOverride(<?php echo (int)$_p['id']; ?>)">
                            <?php echo text($_p['name']); ?>
                        </button>
                        <button type="button" class="rp-pvbtn-del" title="<?php echo attr(xl('Delete provider override')); ?>"
                                onclick="deleteRpProviderOverride(<?php echo (int)$_p['id']; ?>)">&#x2715;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="rpProviderForm" style="display:none;background:var(--cs-bg);border:1px solid var(--cs-border);border-radius:8px;padding:16px 18px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                        <strong id="rpProviderFormName" style="font-size:13px;color:var(--cs-ink);"></strong>
                        <button type="button" class="btn" style="font-size:11px;padding:4px 10px;" onclick="closeRpProviderOverride()"><?php echo xlt('Close'); ?></button>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                            <input type="checkbox" id="rpv-enabled" checked>
                            <?php echo xlt('Rescheduling enabled for this provider'); ?>
                        </label>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px 20px;">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Openings to offer'); ?></label>
                            <input type="number" id="rpv-max_offers" min="1" max="12" value="3" style="width:100%;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Min hours before visit'); ?></label>
                            <input type="number" id="rpv-min_hours_before" min="0" max="168" value="1" style="width:100%;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Days may move earlier'); ?></label>
                            <input type="number" id="rpv-max_days_before" min="0" max="365" value="30" style="width:100%;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Days may move later'); ?></label>
                            <input type="number" id="rpv-max_days_after" min="0" max="365" value="60" style="width:100%;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--cs-subtle);margin-bottom:4px;"><?php echo xlt('Hold offered slots (min)'); ?></label>
                            <input type="number" id="rpv-slot_hold_minutes" min="5" max="60" value="15" style="width:100%;box-sizing:border-box;">
                        </div>
                        <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                                <input type="checkbox" id="rpv-allow_same_day">
                                <?php echo xlt('Allow same-day'); ?>
                            </label>
                        </div>
                    </div>
                    <div style="margin-top:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <button class="btn primary" type="button" onclick="saveRpProviderOverride()"><?php echo xlt('Save Provider Rules'); ?></button>
                        <button class="btn" type="button" onclick="clearRpProviderOverride()"><?php echo xlt('Remove Override'); ?></button>
                        <span id="rpvSaveStatus" style="font-size:12px;display:none;"></span>
                    </div>
                </div>
            </div>

        </div><!-- /.rescheduler-dialog-body -->
    </div><!-- /.rescheduler-dialog -->
</div><!-- /#reschedulerModal -->

</body>
</html>
