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

final class Assistant
{
    public const SETUP_OPS = ['set_global', 'upsert_facility', 'add_list_option', 'update_list_option', 'upsert_fee', 'upsert_appt_category', 'set_form_enabled'];
    public const DAILY_OPS = ['create_appointment'];

    /** Globals the Assistant never reads or writes: security, credentials, integrations. */
    public const BLOCKED_GLOBAL = '/pass|secret|token|key|salt|encrypt|crypt|oauth|auth|login|mfa|2fa|totp|ssl|cert|session|timeout|audit|log|api|smtp|sms|twilio|fax|portal|ccr|hl7|phimail|direct|weno|erx|backup|cron|webroot|url|path|dir|host|port|server|email/i';

    private const FACILITY_TEXT = ['street', 'city', 'state', 'postal_code', 'phone', 'fax', 'email', 'website', 'federal_ein', 'facility_npi', 'facility_taxonomy', 'pos_code'];
    private const FACILITY_FLAGS = ['billing_location', 'service_location', 'primary_business_entity', 'accepts_assignment'];
    private const NO_REPEAT = 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}';

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
        return Store::setting('assistant_admins_only') === '1';
    }

    public static function setAdminsOnly(bool $on): void
    {
        Store::setSetting('assistant_admins_only', $on ? '1' : '0');
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

    /**
     * What Grapheus sees: configuration only, never patient records.
     *
     * @return array<string, mixed>
     */
    public static function snapshot(string $role, string $username): array
    {
        $categories = [];
        foreach (Db::all("SELECT pc_catid, pc_catname, pc_duration, pc_active, pc_cattype FROM openemr_postcalendar_categories ORDER BY pc_seq, pc_catid") as $c) {
            $categories[] = ['id' => $c['pc_catid'] ?? null, 'name' => $c['pc_catname'] ?? null, 'minutes' => (int) round(Val::int($c['pc_duration'] ?? 0) / 60),
                'active' => $c['pc_active'] ?? null, 'type' => $c['pc_cattype'] ?? null];
        }
        $s = [
            'today' => date('Y-m-d (l) H:i') . ' ' . date_default_timezone_get(),
            'user' => ['username' => $username, 'role' => $role],
            'facilities' => Db::all("SELECT id, name, street, city, state, postal_code, phone, facility_npi, pos_code, billing_location, service_location, primary_business_entity FROM facility ORDER BY id"),
            'providers' => Db::all("SELECT username, fname, lname, specialty, facility_id FROM users WHERE authorized = 1 AND active = 1 AND username <> '' ORDER BY lname"),
            'appointment_categories' => $categories,
        ];
        if ($role !== 'admin') {
            return $s;
        }
        $globals = [];
        foreach (Db::all("SELECT gl_name, gl_value FROM globals WHERE gl_index = 0 ORDER BY gl_name") as $g) {
            $name = Val::str($g['gl_name'] ?? '');
            if (preg_match(self::BLOCKED_GLOBAL, $name) !== 1) {
                $globals[$name] = Val::str($g['gl_value'] ?? '', 120);
            }
        }
        $s['globals'] = $globals;
        $s['forms'] = Db::all("SELECT directory, name, state FROM registry ORDER BY name");
        $lists = [];
        foreach (Db::all("SELECT option_id AS id, title FROM list_options WHERE list_id = 'lists' AND activity = 1 ORDER BY title") as $l) {
            $id = Val::str($l['id'] ?? '');
            $count = Val::int(Db::one("SELECT COUNT(*) AS n FROM list_options WHERE list_id = ?", [$id])['n'] ?? 0);
            $entry = ['id' => $id, 'title' => $l['title'] ?? '', 'count' => $count];
            if ($count <= 30) {
                $options = [];
                foreach (Db::all("SELECT option_id, title, activity FROM list_options WHERE list_id = ? ORDER BY seq", [$id]) as $o) {
                    $options[] = Val::str($o['option_id'] ?? '') . '=' . Val::str($o['title'] ?? '') . (Val::int($o['activity'] ?? 0) === 1 ? '' : ' (hidden)');
                }
                $entry['options'] = $options;
            }
            $lists[] = $entry;
        }
        $s['lists'] = $lists;
        $s['fee_schedule'] = Db::all("SELECT t.ct_key AS type, c.code, c.code_text AS description, p.pr_price AS price FROM codes c JOIN code_types t ON t.ct_id = c.code_type
            LEFT JOIN prices p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = 'standard' WHERE t.ct_key IN ('CPT4','HCPCS') ORDER BY c.code LIMIT 300");
        return $s;
    }

    /**
     * Patients matching a name the user typed. Stays inside OpenEMR.
     *
     * @return list<array<string, mixed>>
     */
    public static function findPatients(string $name, string $dob = ''): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        if ($first === '') {
            return [];
        }
        $last = count($parts) > 1 ? end($parts) : '';
        $sql = "SELECT pid, fname, lname, DOB FROM patient_data WHERE (fname LIKE ? OR preferred_name LIKE ?)";
        $binds = [$first . '%', $first . '%'];
        if ($last !== '') {
            $sql .= " AND lname LIKE ?";
            $binds[] = $last . '%';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) === 1) {
            $sql .= " AND DOB = ?";
            $binds[] = $dob;
        }
        return Db::all($sql . " ORDER BY lname, fname LIMIT 10", $binds);
    }

    /**
     * Apply one approved change and log how to undo it.
     *
     * @param array<string, mixed> $a
     * @return array{ok: bool, result?: string, error?: string, log_id?: int}
     */
    public static function apply(string $op, array $a, string $role, int $userId, ?int $pid = null): array
    {
        if (!self::allowed($op, $role)) {
            return ['ok' => false, 'error' => 'Your OpenEMR role cannot make this change.'];
        }
        $done = match ($op) {
            'set_global' => self::setGlobal($a),
            'upsert_facility' => self::upsertFacility($a),
            'add_list_option' => self::addListOption($a),
            'update_list_option' => self::updateListOption($a),
            'upsert_fee' => self::upsertFee($a),
            'upsert_appt_category' => self::upsertCategory($a),
            'set_form_enabled' => self::setFormEnabled($a),
            'create_appointment' => self::createAppointment($a, $pid),
            default => ['error' => "Unknown change $op."],
        };
        if (isset($done['error'])) {
            return ['ok' => false, 'error' => $done['error']];
        }
        $logId = Db::insert(
            "INSERT INTO grapheus_setup_log (at, user_id, op, args, before_json, undo_json) VALUES (NOW(), ?, ?, ?, ?, ?)",
            [$userId, $op, (string) json_encode($a), (string) json_encode($done['before'] ?? null), (string) json_encode($done['undo'] ?? [])]
        );
        return ['ok' => true, 'result' => $done['result'] ?? '', 'log_id' => $logId];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function setGlobal(array $a): array
    {
        $name = Val::str($a['name'] ?? '');
        if ($name === '' || preg_match(self::BLOCKED_GLOBAL, $name) === 1) {
            return ['error' => "Grapheus cannot change the setting $name."];
        }
        $row = Db::one("SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0", [$name]);
        if ($row === null) {
            return ['error' => "There is no setting named $name."];
        }
        Db::exec("UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0", [Val::str($a['value'] ?? '', 255), $name]);
        return ['result' => "Setting $name changed", 'before' => ['value' => $row['gl_value'] ?? ''], 'undo' => ['op' => 'set_global', 'name' => $name]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function upsertFacility(array $a): array
    {
        $name = Val::str($a['name'] ?? '', 255);
        if ($name === '') {
            return ['error' => 'The facility needs a name.'];
        }
        $set = [];
        foreach (self::FACILITY_TEXT as $c) {
            $v = Val::str($a[$c] ?? '', 255);
            if ($v !== '') {
                $set[$c] = $v;
            }
        }
        foreach (self::FACILITY_FLAGS as $f) {
            if (array_key_exists($f, $a)) {
                $set[$f] = Val::bool($a[$f]) ? 1 : 0;
            }
        }
        $existing = Db::one("SELECT * FROM facility WHERE name = ?", [$name]);
        if ($existing !== null) {
            $before = array_intersect_key($existing, $set);
            if ($set !== []) {
                $cols = implode(', ', array_map(static fn (string $c): string => "`$c` = ?", array_keys($set)));
                Db::exec("UPDATE facility SET $cols WHERE id = ?", [...array_values($set), Val::int($existing['id'] ?? 0)]);
            }
            return ['result' => "Facility $name updated", 'before' => $before, 'undo' => ['op' => 'restore_facility', 'id' => Val::int($existing['id'] ?? 0)]];
        }
        $set['name'] = $name;
        $cols = implode(', ', array_map(static fn (string $c): string => "`$c`", array_keys($set)));
        $marks = implode(', ', array_fill(0, count($set), '?'));
        $id = Db::insert("INSERT INTO facility ($cols) VALUES ($marks)", array_values($set));
        return ['result' => "Facility $name added", 'undo' => ['op' => 'delete_facility', 'id' => $id]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function addListOption(array $a): array
    {
        $list = Val::str($a['list_id'] ?? '');
        $oid = Val::str($a['option_id'] ?? '', 100);
        if (Db::one("SELECT option_id FROM list_options WHERE list_id = 'lists' AND option_id = ?", [$list]) === null) {
            return ['error' => "There is no list $list."];
        }
        if ($oid === '' || Db::one("SELECT option_id FROM list_options WHERE list_id = ? AND option_id = ?", [$list, $oid]) !== null) {
            return ['error' => "$list already has an option $oid."];
        }
        $title = Val::str($a['title'] ?? '', 255);
        Db::exec(
            "INSERT INTO list_options (list_id, option_id, title, seq, is_default, notes, activity) VALUES (?, ?, ?, ?, ?, ?, 1)",
            [$list, $oid, $title !== '' ? $title : $oid, Val::int($a['seq'] ?? 0), Val::bool($a['is_default'] ?? false) ? 1 : 0, Val::str($a['notes'] ?? '', 255)]
        );
        return ['result' => 'Added "' . ($title !== '' ? $title : $oid) . "\" to $list", 'undo' => ['op' => 'delete_list_option', 'list_id' => $list, 'option_id' => $oid]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function updateListOption(array $a): array
    {
        $list = Val::str($a['list_id'] ?? '');
        $oid = Val::str($a['option_id'] ?? '');
        $row = Db::one("SELECT title, seq, activity FROM list_options WHERE list_id = ? AND option_id = ?", [$list, $oid]);
        if ($row === null) {
            return ['error' => "There is no option $oid in $list."];
        }
        Db::exec("UPDATE list_options SET title = ?, seq = ?, activity = ? WHERE list_id = ? AND option_id = ?", [
            array_key_exists('title', $a) ? Val::str($a['title'], 255) : Val::str($row['title'] ?? ''),
            array_key_exists('seq', $a) ? Val::int($a['seq']) : Val::int($row['seq'] ?? 0),
            array_key_exists('active', $a) ? (Val::bool($a['active']) ? 1 : 0) : Val::int($row['activity'] ?? 1),
            $list, $oid]);
        return ['result' => "Updated $oid in $list", 'before' => $row, 'undo' => ['op' => 'restore_list_option', 'list_id' => $list, 'option_id' => $oid]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function upsertFee(array $a): array
    {
        $type = strtoupper(Val::str($a['code_type'] ?? 'CPT4'));
        $code = strtoupper(Val::str($a['code'] ?? ''));
        $ct = Val::int(Db::one("SELECT ct_id FROM code_types WHERE ct_key = ?", [$type])['ct_id'] ?? 0);
        if ($ct === 0 || preg_match('/^[A-Z0-9]{4,6}$/', $code) !== 1) {
            return ['error' => "Cannot add code $type $code."];
        }
        $price = sprintf('%.2f', (float) Val::str($a['price'] ?? '0'));
        $description = Val::str($a['description'] ?? '', 255);
        $row = Db::one("SELECT id, code_text FROM codes WHERE code_type = ? AND code = ? AND modifier = ''", [$ct, $code]);
        if ($row !== null) {
            $cid = Val::int($row['id'] ?? 0);
            $old = Db::one("SELECT pr_price FROM prices WHERE pr_id = ? AND pr_selector = '' AND pr_level = 'standard'", [$cid]);
            $before = ['code_text' => $row['code_text'] ?? '', 'price' => $old['pr_price'] ?? null];
            if ($description !== '') {
                Db::exec("UPDATE codes SET code_text = ? WHERE id = ?", [$description, $cid]);
            }
            $undo = ['op' => 'restore_fee', 'code_id' => $cid];
        } else {
            $cid = Db::insert(
                "INSERT INTO codes (code_text, code, code_type, modifier, units, fee, active, reportable, financial_reporting) VALUES (?, ?, ?, '', 1, ?, 1, 1, 1)",
                [$description !== '' ? $description : $code, $code, $ct, $price]
            );
            $before = null;
            $undo = ['op' => 'delete_fee', 'code_id' => $cid];
        }
        Db::exec("REPLACE INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', ?)", [$cid, $price]);
        return ['result' => "$type $code at \$$price", 'before' => $before, 'undo' => $undo];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function upsertCategory(array $a): array
    {
        $name = Val::str($a['name'] ?? '', 100);
        if ($name === '') {
            return ['error' => 'The visit type needs a name.'];
        }
        $mins = max(5, min(480, Val::int($a['duration_minutes'] ?? 15)));
        $color = Val::str($a['color'] ?? '');
        $color = preg_match('/^#[0-9a-f]{6}$/i', $color) === 1 ? $color : '#cce5ff';
        $row = Db::one("SELECT pc_catid, pc_catname, pc_catcolor, pc_catdesc, pc_duration, pc_active FROM openemr_postcalendar_categories WHERE pc_catname = ?", [$name]);
        if ($row !== null) {
            $id = Val::int($row['pc_catid'] ?? 0);
            Db::exec(
                "UPDATE openemr_postcalendar_categories SET pc_duration = ?, pc_catcolor = ?, pc_catdesc = ?, pc_active = ? WHERE pc_catid = ?",
                [$mins * 60, $color, array_key_exists('description', $a) ? Val::str($a['description'], 255) : Val::str($row['pc_catdesc'] ?? ''),
                    array_key_exists('active', $a) ? (Val::bool($a['active']) ? 1 : 0) : Val::int($row['pc_active'] ?? 1), $id]
            );
            return ['result' => "Visit type $name updated ($mins min)", 'before' => $row, 'undo' => ['op' => 'restore_category', 'id' => $id]];
        }
        $seq = Val::int(Db::one("SELECT COALESCE(MAX(pc_seq), 0) + 1 AS s FROM openemr_postcalendar_categories")['s'] ?? 1);
        $id = Db::insert(
            "INSERT INTO openemr_postcalendar_categories (pc_catname, pc_catcolor, pc_catdesc, pc_recurrtype, pc_recurrspec, pc_recurrfreq, pc_duration, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_dailylimit, pc_cattype, pc_active, pc_seq, pc_constant_id)
             VALUES (?, ?, ?, 0, ?, 0, ?, 0, NULL, 0, 0, 0, 0, 1, ?, ?)",
            [$name, $color, Val::str($a['description'] ?? '', 255), self::NO_REPEAT, $mins * 60, $seq, 'grapheus_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($name))]
        );
        return ['result' => "Visit type $name added ($mins min)", 'undo' => ['op' => 'delete_category', 'id' => $id]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function setFormEnabled(array $a): array
    {
        $dir = Val::str($a['directory'] ?? '');
        $row = Db::one("SELECT id, state FROM registry WHERE directory = ?", [$dir]);
        if ($row === null) {
            return ['error' => "There is no form $dir."];
        }
        $on = Val::bool($a['enabled'] ?? false);
        Db::exec("UPDATE registry SET state = ? WHERE id = ?", [$on ? 1 : 0, Val::int($row['id'] ?? 0)]);
        return ['result' => "Form $dir " . ($on ? 'turned on' : 'turned off'), 'before' => ['state' => $row['state'] ?? 0], 'undo' => ['op' => 'restore_form', 'id' => Val::int($row['id'] ?? 0)]];
    }

    /**
     * @param array<string, mixed> $a
     * @return array{error?: string, result?: string, before?: mixed, undo?: array<string, mixed>}
     */
    private static function createAppointment(array $a, ?int $pid): array
    {
        if ($pid === null || $pid <= 0) {
            return ['error' => 'Choose which patient first.'];
        }
        $prov = Db::one("SELECT id, facility_id FROM users WHERE username = ? AND active = 1", [Val::str($a['provider_username'] ?? '')]);
        if ($prov === null) {
            return ['error' => 'Unknown provider.'];
        }
        $catName = Val::str($a['category_name'] ?? '');
        $cat = $catName !== '' ? Db::one("SELECT pc_catid, pc_catname, pc_duration FROM openemr_postcalendar_categories WHERE pc_catname = ? AND pc_active = 1", [$catName]) : null;
        $cat ??= Db::one("SELECT pc_catid, pc_catname, pc_duration FROM openemr_postcalendar_categories WHERE pc_cattype = 0 AND pc_active = 1 ORDER BY (pc_constant_id = 'office_visit') DESC, pc_seq LIMIT 1");
        if ($cat === null) {
            return ['error' => 'There is no visit type to book.'];
        }
        $date = Val::str($a['date'] ?? '');
        $time = Val::str($a['time'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || preg_match('/^\d{2}:\d{2}$/', $time) !== 1) {
            return ['error' => 'The date or time is not clear.'];
        }
        $facName = Val::str($a['facility_name'] ?? '');
        $fac = $facName !== '' ? Db::one("SELECT id FROM facility WHERE name = ?", [$facName]) : null;
        $facId = Val::int($fac['id'] ?? ($prov['facility_id'] ?? 0));
        $mins = Val::int($a['duration_minutes'] ?? 0);
        if ($mins <= 0) {
            $mins = (int) round(Val::int($cat['pc_duration'] ?? 0) / 60);
        }
        if ($mins <= 0) {
            $mins = 15;
        }
        $end = date('H:i:s', (int) strtotime("$time +$mins minutes"));
        $clash = Db::one(
            "SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_aid = ? AND pc_eventDate = ? AND pc_startTime < ? AND pc_endTime > ? AND pc_pid <> ''",
            [Val::int($prov['id'] ?? 0), $date, $end, $time . ':00']
        );
        if ($clash !== null) {
            return ['error' => 'That provider already has an appointment at that time.'];
        }
        $eid = (new AppointmentService())->insert($pid, [
            'pc_catid' => $cat['pc_catid'] ?? null, 'pc_title' => $cat['pc_catname'] ?? '', 'pc_duration' => $mins * 60, 'pc_hometext' => Val::str($a['reason'] ?? '', 255),
            'pc_eventDate' => $date, 'pc_apptstatus' => '-', 'pc_startTime' => $time, 'pc_facility' => $facId, 'pc_billing_location' => $facId, 'pc_aid' => Val::int($prov['id'] ?? 0),
        ]);
        return ['result' => "Appointment booked $date $time ($mins min)", 'undo' => ['op' => 'delete_appointment', 'id' => Val::int($eid)]];
    }

    /**
     * Put one change back the way it was.
     *
     * @return array{ok: bool, error?: string}
     */
    public static function undo(int $logId, string $role): array
    {
        $log = Db::one("SELECT * FROM grapheus_setup_log WHERE id = ? AND undone_at IS NULL", [$logId]);
        if ($log === null) {
            return ['ok' => false, 'error' => 'Nothing to undo.'];
        }
        if (!self::allowed(Val::str($log['op'] ?? ''), $role)) {
            return ['ok' => false, 'error' => 'Your OpenEMR role cannot undo this change.'];
        }
        $u = Val::map(json_decode(Val::str($log['undo_json'] ?? ''), true));
        $b = Val::map(json_decode(Val::str($log['before_json'] ?? ''), true));
        $id = Val::int($u['id'] ?? 0);
        switch (Val::str($u['op'] ?? '')) {
            case 'set_global':
                Db::exec("UPDATE globals SET gl_value = ? WHERE gl_name = ? AND gl_index = 0", [Val::str($b['value'] ?? ''), Val::str($u['name'] ?? '')]);
                break;
            case 'restore_facility':
                $cols = array_values(array_intersect(array_keys($b), [...self::FACILITY_TEXT, ...self::FACILITY_FLAGS]));
                if ($cols !== []) {
                    $set = implode(', ', array_map(static fn (string $c): string => "`$c` = ?", $cols));
                    Db::exec("UPDATE facility SET $set WHERE id = ?", [...array_map(static fn (string $c): mixed => $b[$c], $cols), $id]);
                }
                break;
            case 'delete_facility':
                Db::exec("DELETE FROM facility WHERE id = ?", [$id]);
                break;
            case 'delete_list_option':
                Db::exec("DELETE FROM list_options WHERE list_id = ? AND option_id = ?", [Val::str($u['list_id'] ?? ''), Val::str($u['option_id'] ?? '')]);
                break;
            case 'restore_list_option':
                Db::exec("UPDATE list_options SET title = ?, seq = ?, activity = ? WHERE list_id = ? AND option_id = ?",
                    [Val::str($b['title'] ?? ''), Val::int($b['seq'] ?? 0), Val::int($b['activity'] ?? 1), Val::str($u['list_id'] ?? ''), Val::str($u['option_id'] ?? '')]);
                break;
            case 'restore_fee':
                $cid = Val::int($u['code_id'] ?? 0);
                Db::exec("UPDATE codes SET code_text = ? WHERE id = ?", [Val::str($b['code_text'] ?? ''), $cid]);
                if (($b['price'] ?? null) === null) {
                    Db::exec("DELETE FROM prices WHERE pr_id = ? AND pr_selector = '' AND pr_level = 'standard'", [$cid]);
                } else {
                    Db::exec("REPLACE INTO prices (pr_id, pr_selector, pr_level, pr_price) VALUES (?, '', 'standard', ?)", [$cid, Val::str($b['price'])]);
                }
                break;
            case 'delete_fee':
                $cid = Val::int($u['code_id'] ?? 0);
                Db::exec("DELETE FROM prices WHERE pr_id = ?", [$cid]);
                Db::exec("DELETE FROM codes WHERE id = ?", [$cid]);
                break;
            case 'restore_category':
                Db::exec("UPDATE openemr_postcalendar_categories SET pc_duration = ?, pc_catcolor = ?, pc_catdesc = ?, pc_active = ? WHERE pc_catid = ?",
                    [Val::int($b['pc_duration'] ?? 900), Val::str($b['pc_catcolor'] ?? ''), Val::str($b['pc_catdesc'] ?? ''), Val::int($b['pc_active'] ?? 1), $id]);
                break;
            case 'delete_category':
                if (Db::one("SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_catid = ? LIMIT 1", [$id]) !== null) {
                    return ['ok' => false, 'error' => 'Appointments already use this visit type; hide it instead.'];
                }
                Db::exec("DELETE FROM openemr_postcalendar_categories WHERE pc_catid = ?", [$id]);
                break;
            case 'restore_form':
                Db::exec("UPDATE registry SET state = ? WHERE id = ?", [Val::int($b['state'] ?? 0), $id]);
                break;
            case 'delete_appointment':
                Db::exec("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", [$id]);
                break;
            default:
                return ['ok' => false, 'error' => 'This change cannot be undone automatically.'];
        }
        Db::exec("UPDATE grapheus_setup_log SET undone_at = NOW() WHERE id = ?", [$logId]);
        return ['ok' => true];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function recent(int $limit = 50): array
    {
        return Db::all("SELECT l.id, l.at, l.op, l.args, l.undone_at, u.username FROM grapheus_setup_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT " . max(1, min(200, $limit)));
    }
}
