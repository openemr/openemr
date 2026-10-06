<?php

/**
 * Grapheus Assistant inside OpenEMR: who may do what, the configuration
 * snapshot Grapheus sees (never patient records), and the handlers that apply
 * an approved change and remember how to undo it.
 *
 * Authority follows OpenEMR's own permissions:
 *   admin      (admin/super)            setup + daily tasks + how-to
 *   clinician  (encounters/notes write) daily tasks + how-to
 *   scheduler  (patients/appt write)    daily tasks + how-to
 *   viewer                              how-to only
 * The owner can limit the Assistant to administrators (setting "assistant_admins_only").
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\AppointmentService;

class Assistant
{
    public const SETUP_OPS = ['set_global', 'upsert_facility', 'add_list_option', 'update_list_option', 'upsert_fee', 'upsert_appt_category', 'set_form_enabled'];
    public const DAILY_OPS = ['create_appointment'];

    /** Globals the Assistant never reads or writes: security, credentials, integrations. */
    public const BLOCKED_GLOBAL = '/pass|secret|token|key|salt|encrypt|crypt|oauth|auth|login|mfa|2fa|totp|ssl|cert|session|timeout|audit|log|api|smtp|sms|twilio|fax|portal|ccr|hl7|phimail|direct|weno|erx|backup|cron|webroot|url|path|dir|host|port|server|email/i';

    public static function role(): string
    {
        if (AclMain::aclCheckCore('admin', 'super')) {
            return 'admin';
        }
        if (self::adminsOnly()) {
            return 'none';
        }
        if (AclMain::aclCheckCore('encounters', 'notes', '', 'write') || AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
            return 'clinician';
        }
        if (AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
            return 'scheduler';
        }
        return 'viewer';
    }

    public static function adminsOnly(): bool
    {
        $r = sqlQuery("SELECT `value` FROM grapheus_settings WHERE `name` = 'assistant_admins_only'");
        return ($r['value'] ?? '0') === '1';
    }

    public static function setAdminsOnly(bool $on): void
    {
        sqlStatement("REPLACE INTO grapheus_settings (`name`, `value`) VALUES ('assistant_admins_only', ?)", [$on ? '1' : '0']);
    }

    public static function allowed(string $op, string $role): bool
    {
        if ($role === 'admin') {
            return in_array($op, self::SETUP_OPS, true) || in_array($op, self::DAILY_OPS, true);
        }
        if ($role === 'clinician' || $role === 'scheduler') {
            return in_array($op, self::DAILY_OPS, true);
        }
        return false;
    }

    // ---------------------------------------------------------------- snapshot (no patient records)
    private static function rows(string $sql, array $binds = []): array
    {
        $out = [];
        $res = sqlStatement($sql, $binds);
        while ($row = sqlFetchArray($res)) {
            $out[] = $row;
        }
        return $out;
    }

    public static function snapshot(string $role, string $username): array
    {
        $tz = date_default_timezone_get();
        $s = [
            'today' => date('Y-m-d (l) H:i') . ' ' . $tz,
            'user' => ['username' => $username, 'role' => $role],
            'facilities' => self::rows("SELECT id, name, street, city, state, postal_code, phone, facility_npi, pos_code, billing_location, service_location, primary_business_entity FROM facility ORDER BY id"),
            'providers' => self::rows("SELECT username, fname, lname, specialty, facility_id FROM users WHERE authorized = 1 AND active = 1 AND username <> '' ORDER BY lname"),
            'appointment_categories' => array_map(fn ($c) => ['id' => $c['pc_catid'], 'name' => $c['pc_catname'], 'minutes' => (int) round(((int) $c['pc_duration']) / 60), 'active' => $c['pc_active'], 'type' => $c['pc_cattype']],
                self::rows("SELECT pc_catid, pc_catname, pc_duration, pc_active, pc_cattype FROM openemr_postcalendar_categories ORDER BY pc_seq, pc_catid")),
        ];
        if ($role !== 'admin') {
            return $s;
        }
        $globals = [];
        foreach (self::rows("SELECT gl_name, gl_value FROM globals WHERE gl_index = 0 ORDER BY gl_name") as $g) {
            if (!preg_match(self::BLOCKED_GLOBAL, $g['gl_name'])) {
                $globals[$g['gl_name']] = mb_substr((string) $g['gl_value'], 0, 120);
            }
        }
        $s['globals'] = $globals;
        $s['forms'] = self::rows("SELECT directory, name, state FROM registry ORDER BY name");
        $lists = [];
        foreach (self::rows("SELECT option_id AS id, title FROM list_options WHERE list_id = 'lists' AND activity = 1 ORDER BY title") as $l) {
            $n = sqlQuery("SELECT COUNT(*) AS n FROM list_options WHERE list_id = ?", [$l['id']]);
            $entry = ['id' => $l['id'], 'title' => $l['title'], 'count' => (int) $n['n']];
            if ((int) $n['n'] <= 30) {
                $entry['options'] = array_map(fn ($o) => $o['option_id'] . '=' . $o['title'] . ($o['activity'] ? '' : ' (hidden)'),
                    self::rows("SELECT option_id, title, activity FROM list_options WHERE list_id = ? ORDER BY seq", [$l['id']]));
            }
            $lists[] = $entry;
        }
        $s['lists'] = $lists;
        $s['fee_schedule'] = self::rows("SELECT t.ct_key AS type, c.code, c.code_text AS description, p.pr_price AS price FROM codes c JOIN code_types t ON t.ct_id = c.code_type
            LEFT JOIN prices p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = 'standard' WHERE t.ct_key IN ('CPT4','HCPCS') ORDER BY c.code LIMIT 300");
        return $s;
    }

    // ---------------------------------------------------------------- find a patient (stays in OpenEMR)
    public static function findPatients(string $name, string $dob = ''): array
    {
        $parts = preg_split('/\s+/', trim($name));
        if (!$parts || $parts[0] === '') {
            return [];
        }
        $first = $parts[0];
        $last = count($parts) > 1 ? end($parts) : '';
        $sql = "SELECT pid, fname, lname, DOB FROM patient_data WHERE (fname LIKE ? OR preferred_name LIKE ?)";
        $binds = [$first . '%', $first . '%'];
        if ($last !== '') {
            $sql .= " AND lname LIKE ?";
            $binds[] = $last . '%';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $sql .= " AND DOB = ?";
            $binds[] = $dob;
        }
        return self::rows($sql . " ORDER BY lname, fname LIMIT 10", $binds);
    }

    // ---------------------------------------------------------------- apply one approved change
    /** @return array{ok:bool, result?:string, error?:string, log_id?:int} */
    public static function apply(string $op, array $a, string $role, int $userId, ?int $pid = null): array
    {
        if (!self::allowed($op, $role)) {
            return ['ok' => false, 'error' => 'Your OpenEMR role cannot make this change.'];
        }
        $before = null;
        $undo = null;
        switch ($op) {
            case 'set_global':
                $name = (string) ($a['name'] ?? '');
                if ($name === '' || preg_match(self::BLOCKED_GLOBAL, $name)) {
                    return ['ok' => false, 'error' => "Grapheus cannot change the setting $name."];
                }
                $row = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0", [$name]);
                if ($row === false || $row === null || !array_key_exists('gl_value', (array) $row)) {
                    return ['ok' => false, 'error' => "There is no setting named $name."];
                }
                $before = ['value' => $row['gl_value']];
                sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0", [mb_substr((string) ($a['value'] ?? ''), 0, 255), $name]);
                $undo = ['op' => 'set_global', 'name' => $name];
                $result = "Setting $name changed";
                break;

            case 'upsert_facility':
                $name = trim((string) ($a['name'] ?? ''));
                if ($name === '') {
                    return ['ok' => false, 'error' => 'The facility needs a name.'];
                }
                $cols = ['street', 'city', 'state', 'postal_code', 'phone', 'fax', 'email', 'website', 'federal_ein', 'facility_npi', 'facility_taxonomy', 'pos_code'];
                $flags = ['billing_location', 'service_location', 'primary_business_entity', 'accepts_assignment'];
                $set = [];
                foreach ($cols as $c) {
                    if (isset($a[$c]) && $a[$c] !== '') {
                        $set[$c] = mb_substr((string) $a[$c], 0, 255);
                    }
                }
                foreach ($flags as $f) {
                    if (isset($a[$f])) {
                        $set[$f] = $a[$f] ? 1 : 0;
                    }
                }
                $existing = sqlQuery("SELECT * FROM facility WHERE name = ?", [$name]);
                if (!empty($existing['id'])) {
                    $before = array_intersect_key($existing, $set);
                    if ($set) {
                        sqlStatement("UPDATE facility SET " . implode(', ', array_map(fn ($c) => "`$c` = ?", array_keys($set))) . " WHERE id = ?", array_merge(array_values($set), [$existing['id']]));
                    }
                    $undo = ['op' => 'restore_facility', 'id' => (int) $existing['id']];
                    $result = "Facility $name updated";
                } else {
                    $set['name'] = mb_substr($name, 0, 255);
                    $id = sqlInsert("INSERT INTO facility (" . implode(', ', array_map(fn ($c) => "`$c`", array_keys($set))) . ") VALUES (" . implode(', ', array_fill(0, count($set), '?')) . ")", array_values($set));
                    $undo = ['op' => 'delete_facility', 'id' => (int) $id];
                    $result = "Facility $name added";
                }
                break;

            case 'add_list_option':
                $list = (string) ($a['list_id'] ?? '');
                $oid = mb_substr((string) ($a['option_id'] ?? ''), 0, 100);
                if (!sqlQuery("SELECT option_id FROM list_options WHERE list_id = 'lists' AND option_id = ?", [$list])) {
                    return ['ok' => false, 'error' => "There is no list $list."];
                }
                if ($oid === '' || sqlQuery("SELECT option_id FROM list_options WHERE list_id = ? AND option_id = ?", [$list, $oid])) {
                    return ['ok' => false, 'error' => "$list already has an option $oid."];
                }
                sqlStatement("INSERT INTO list_options (list_id, option_id, title, seq, is_default, notes, activity) VALUES (?, ?, ?, ?, ?, ?, 1)",
                    [$list, $oid, mb_substr((string) ($a['title'] ?? $oid), 0, 255), (int) ($a['seq'] ?? 0), !empty($a['is_default']) ? 1 : 0, mb_substr((string) ($a['notes'] ?? ''), 0, 255)]);
                $undo = ['op' => 'delete_list_option', 'list_id' => $list, 'option_id' => $oid];
                $result = "Added \"" . ($a['title'] ?? $oid) . "\" to $list";
                break;

            case 'update_list_option':
                $list = (string) ($a['list_id'] ?? '');
                $oid = (string) ($a['option_id'] ?? '');
                $row = sqlQuery("SELECT title, seq, activity FROM list_options WHERE list_id = ? AND option_id = ?", [$list, $oid]);
                if (empty($row)) {
                    return ['ok' => false, 'error' => "There is no option $oid in $list."];
                }
                $before = $row;
                sqlStatement("UPDATE list_options SET title = ?, seq = ?, activity = ? WHERE list_id = ? AND option_id = ?", [
                    isset($a['title']) ? mb_substr((string) $a['title'], 0, 255) : $row['title'], isset($a['seq']) ? (int) $a['seq'] : $row['seq'],
                    isset($a['active']) ? ($a['active'] ? 1 : 0) : $row['activity'], $list, $oid]);
                $undo = ['op' => 'restore_list_option', 'list_id' => $list, 'option_id' => $oid];
                $result = "Updated $oid in $list";
                break;

            case 'upsert_fee':
                $type = strtoupper((string) ($a['code_type'] ?? 'CPT4'));
                $code = strtoupper(trim((string) ($a['code'] ?? '')));
                $ct = sqlQuery("SELECT ct_id FROM code_types WHERE ct_key = ?", [$type]);
                if (empty($ct['ct_id']) || !preg_match('/^[A-Z0-9]{4,6}$/', $code)) {
                    return ['ok' => false, 'error' => "Cannot add code $type $code."];
                }
                $price = sprintf('%.2f', (float) ($a['price'] ?? 0));
                $row = sqlQuery("SELECT id, code_text FROM codes WHERE code_type = ? AND code = ? AND modifier = ''", [$ct['ct_id'], $code]);
                if (!empty($row['id'])) {
                    $p = sqlQuery("SELECT pr_price FROM prices WHERE pr_id = ? AND pr_selector = '' AND pr_level = 'standard'", [$row['id']]);
                    $before = ['code_text' => $row['code_text'], 'price' => $p['pr_price'] ?? null];
                    $cid = (int) $row['id'];
                    if (!empty($a['description'])) {
                        sqlStatement("UPDATE codes SET code_text = ? WHERE id = ?", [mb_substr((string) $a['description'], 0, 255), $cid]);
                    }
                    $undo = ['op' => 'restore_fee', 'code_id' => $cid];
                } else {
                    $cid = (int) sqlInsert("INSERT INTO codes (code_text, code, code_type, modifier, units, fee, active, reportable, financial_reporting) VALUES (?, ?, ?, '', 1, ?, 1, 1, 1)",
                        [mb_substr((string) ($a['description'] ?? $code), 0, 255), $code, $ct['ct_id'], $price]);
                    $undo = ['op' => 'delete_fee', 'code_id' => $cid];
                }
                sqlStatement("REPLACE INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', ?)", [$cid, $price]);
                $result = "$type $code at \$$price";
                break;

            case 'upsert_appt_category':
                $name = trim((string) ($a['name'] ?? ''));
                if ($name === '') {
                    return ['ok' => false, 'error' => 'The visit type needs a name.'];
                }
                $mins = max(5, min(480, (int) ($a['duration_minutes'] ?? 15)));
                $color = preg_match('/^#[0-9a-f]{6}$/i', (string) ($a['color'] ?? '')) ? $a['color'] : '#cce5ff';
                $row = sqlQuery("SELECT pc_catid, pc_catname, pc_catcolor, pc_catdesc, pc_duration, pc_active FROM openemr_postcalendar_categories WHERE pc_catname = ?", [$name]);
                if (!empty($row['pc_catid'])) {
                    $before = $row;
                    sqlStatement("UPDATE openemr_postcalendar_categories SET pc_duration = ?, pc_catcolor = ?, pc_catdesc = ?, pc_active = ? WHERE pc_catid = ?",
                        [$mins * 60, $color, mb_substr((string) ($a['description'] ?? $row['pc_catdesc']), 0, 255), isset($a['active']) ? ($a['active'] ? 1 : 0) : $row['pc_active'], $row['pc_catid']]);
                    $undo = ['op' => 'restore_category', 'id' => (int) $row['pc_catid']];
                    $result = "Visit type $name updated ($mins min)";
                } else {
                    $seq = sqlQuery("SELECT COALESCE(MAX(pc_seq), 0) + 1 AS s FROM openemr_postcalendar_categories");
                    $id = sqlInsert("INSERT INTO openemr_postcalendar_categories (pc_catname, pc_catcolor, pc_catdesc, pc_recurrtype, pc_recurrspec, pc_recurrfreq, pc_duration, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_dailylimit, pc_cattype, pc_active, pc_seq, pc_constant_id)
                        VALUES (?, ?, ?, 0, 'a:5:{s:17:\"event_repeat_freq\";s:1:\"0\";s:22:\"event_repeat_freq_type\";s:1:\"0\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";}', 0, ?, 0, NULL, 0, 0, 0, 0, 1, ?, ?)",
                        [mb_substr($name, 0, 100), $color, mb_substr((string) ($a['description'] ?? ''), 0, 255), $mins * 60, (int) $seq['s'], 'grapheus_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($name))]);
                    $undo = ['op' => 'delete_category', 'id' => (int) $id];
                    $result = "Visit type $name added ($mins min)";
                }
                break;

            case 'set_form_enabled':
                $dir = (string) ($a['directory'] ?? '');
                $row = sqlQuery("SELECT id, state FROM registry WHERE directory = ?", [$dir]);
                if (empty($row['id'])) {
                    return ['ok' => false, 'error' => "There is no form $dir."];
                }
                $before = ['state' => $row['state']];
                sqlStatement("UPDATE registry SET state = ? WHERE id = ?", [!empty($a['enabled']) ? 1 : 0, $row['id']]);
                $undo = ['op' => 'restore_form', 'id' => (int) $row['id']];
                $result = "Form $dir " . (!empty($a['enabled']) ? 'turned on' : 'turned off');
                break;

            case 'create_appointment':
                if (!$pid) {
                    return ['ok' => false, 'error' => 'Choose which patient first.'];
                }
                $prov = sqlQuery("SELECT id, facility_id FROM users WHERE username = ? AND active = 1", [(string) ($a['provider_username'] ?? '')]);
                if (empty($prov['id'])) {
                    return ['ok' => false, 'error' => 'Unknown provider.'];
                }
                $cat = !empty($a['category_name']) ? sqlQuery("SELECT pc_catid, pc_catname, pc_duration FROM openemr_postcalendar_categories WHERE pc_catname = ? AND pc_active = 1", [$a['category_name']]) : null;
                if (empty($cat['pc_catid'])) {
                    $cat = sqlQuery("SELECT pc_catid, pc_catname, pc_duration FROM openemr_postcalendar_categories WHERE pc_cattype = 0 AND pc_active = 1 ORDER BY (pc_constant_id = 'office_visit') DESC, pc_seq LIMIT 1");
                }
                $date = (string) ($a['date'] ?? '');
                $time = (string) ($a['time'] ?? '');
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
                    return ['ok' => false, 'error' => 'The date or time is not clear.'];
                }
                $fac = !empty($a['facility_name']) ? sqlQuery("SELECT id FROM facility WHERE name = ?", [$a['facility_name']]) : null;
                $facId = (int) ($fac['id'] ?? $prov['facility_id'] ?? 0);
                $mins = (int) ($a['duration_minutes'] ?? 0) ?: (int) round(((int) $cat['pc_duration']) / 60) ?: 15;
                $clash = sqlQuery("SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_aid = ? AND pc_eventDate = ? AND pc_startTime < ? AND pc_endTime > ? AND pc_pid <> ''",
                    [$prov['id'], $date, date('H:i:s', strtotime("$time +$mins minutes")), $time . ':00']);
                if (!empty($clash['pc_eid'])) {
                    return ['ok' => false, 'error' => 'That provider already has an appointment at that time.'];
                }
                $eid = (new AppointmentService())->insert($pid, [
                    'pc_catid' => $cat['pc_catid'], 'pc_title' => $cat['pc_catname'], 'pc_duration' => $mins * 60, 'pc_hometext' => mb_substr((string) ($a['reason'] ?? ''), 0, 255),
                    'pc_eventDate' => $date, 'pc_apptstatus' => '-', 'pc_startTime' => $time, 'pc_facility' => $facId, 'pc_billing_location' => $facId, 'pc_aid' => $prov['id'],
                ]);
                $undo = ['op' => 'delete_appointment', 'id' => (int) $eid];
                $result = "Appointment booked $date $time ($mins min)";
                break;

            default:
                return ['ok' => false, 'error' => "Unknown change $op."];
        }
        $logId = (int) sqlInsert("INSERT INTO grapheus_setup_log (at, user_id, op, args, before_json, undo_json) VALUES (NOW(), ?, ?, ?, ?, ?)",
            [$userId, $op, json_encode($a), json_encode($before), json_encode($undo)]);
        return ['ok' => true, 'result' => $result, 'log_id' => $logId];
    }

    /** Put one change back the way it was. */
    public static function undo(int $logId, string $role): array
    {
        $log = sqlQuery("SELECT * FROM grapheus_setup_log WHERE id = ? AND undone_at IS NULL", [$logId]);
        if (empty($log['id'])) {
            return ['ok' => false, 'error' => 'Nothing to undo.'];
        }
        if (!self::allowed($log['op'], $role)) {
            return ['ok' => false, 'error' => 'Your OpenEMR role cannot undo this change.'];
        }
        $u = json_decode((string) $log['undo_json'], true) ?: [];
        $b = json_decode((string) $log['before_json'], true) ?: [];
        switch ($u['op'] ?? '') {
            case 'set_global':
                sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0", [$b['value'] ?? '', $u['name']]);
                break;
            case 'restore_facility':
                if ($b) {
                    sqlStatement("UPDATE facility SET " . implode(', ', array_map(fn ($c) => "`" . preg_replace('/[^a-z_]/', '', $c) . "` = ?", array_keys($b))) . " WHERE id = ?", array_merge(array_values($b), [$u['id']]));
                }
                break;
            case 'delete_facility':
                sqlStatement("DELETE FROM facility WHERE id = ?", [$u['id']]);
                break;
            case 'delete_list_option':
                sqlStatement("DELETE FROM list_options WHERE list_id = ? AND option_id = ?", [$u['list_id'], $u['option_id']]);
                break;
            case 'restore_list_option':
                sqlStatement("UPDATE list_options SET title = ?, seq = ?, activity = ? WHERE list_id = ? AND option_id = ?", [$b['title'], $b['seq'], $b['activity'], $u['list_id'], $u['option_id']]);
                break;
            case 'restore_fee':
                sqlStatement("UPDATE codes SET code_text = ? WHERE id = ?", [$b['code_text'], $u['code_id']]);
                if ($b['price'] === null) {
                    sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector = '' AND pr_level = 'standard'", [$u['code_id']]);
                } else {
                    sqlStatement("REPLACE INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', ?)", [$u['code_id'], $b['price']]);
                }
                break;
            case 'delete_fee':
                sqlStatement("DELETE FROM prices WHERE pr_id = ?", [$u['code_id']]);
                sqlStatement("DELETE FROM codes WHERE id = ?", [$u['code_id']]);
                break;
            case 'restore_category':
                sqlStatement("UPDATE openemr_postcalendar_categories SET pc_duration = ?, pc_catcolor = ?, pc_catdesc = ?, pc_active = ? WHERE pc_catid = ?", [$b['pc_duration'], $b['pc_catcolor'], $b['pc_catdesc'], $b['pc_active'], $u['id']]);
                break;
            case 'delete_category':
                if (sqlQuery("SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_catid = ? LIMIT 1", [$u['id']])) {
                    return ['ok' => false, 'error' => 'Appointments already use this visit type; hide it instead.'];
                }
                sqlStatement("DELETE FROM openemr_postcalendar_categories WHERE pc_catid = ?", [$u['id']]);
                break;
            case 'restore_form':
                sqlStatement("UPDATE registry SET state = ? WHERE id = ?", [$b['state'], $u['id']]);
                break;
            case 'delete_appointment':
                sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$u['id']]);
                break;
            default:
                return ['ok' => false, 'error' => 'This change cannot be undone automatically.'];
        }
        sqlStatement("UPDATE grapheus_setup_log SET undone_at = NOW() WHERE id = ?", [$logId]);
        return ['ok' => true];
    }

    public static function recent(int $limit = 50): array
    {
        return self::rows("SELECT l.id, l.at, l.op, l.args, l.undone_at, u.username FROM grapheus_setup_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT " . (int) $limit);
    }
}
