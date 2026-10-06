<?php

/**
 * Puts a reviewed Grapheus draft into the OpenEMR chart, in one transaction.
 *
 *   - SOAP note        -> form_soap + forms (the encounter's note), like the SOAP form does.
 *   - Problems         -> lists (medical_problem, ICD10:code); skipped if already active.
 *   - Allergies        -> lists (allergy, reaction, severity); skipped if already active.
 *   - Prescriptions    -> prescriptions with request_intent "proposal": ENTERED, NEVER
 *                         TRANSMITTED (ntx = 0, erx_uploaded = 0). The clinician reviews
 *                         and sends them through their own e-prescribing.
 *   - Billing          -> the encounter's fee sheet (billing table) with billed = 0:
 *                         diagnoses first, then procedure codes justified by them.
 *                         Nothing is finalized; the clinician or biller does that.
 * Only items the clinician left checked on the review screen arrive here. Nothing
 * already in the chart is changed or removed.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Uuid\UuidRegistry;

class Applier
{
    public const NOTE_FOOTER = 'Documentation drafted with Grapheus (AI scribe) and reviewed by the clinician.';
    public const RX_NOTE = 'Drafted by Grapheus from the visit. Review and send it yourself; Grapheus never transmits prescriptions.';

    public function __construct(private int $pid, private int $encounter, private int $userId, private string $userName, private string $groupName, private int $authorized = 0)
    {
    }

    /** @return array summary of what was added */
    public function apply(array $p): array
    {
        $summary = ['note' => false, 'problems' => [], 'allergies' => [], 'prescriptions' => [], 'billing' => [], 'skipped' => []];
        $this->assertEncounter();
        sqlBeginTrans();
        try {
            if (!empty($p['soap']) && is_array($p['soap'])) {
                $summary['note'] = $this->addSoap($p['soap']);
            }
            foreach ($p['problems'] ?? [] as $pr) {
                $r = $this->addProblem($pr);
                $r ? $summary['problems'][] = $r : $summary['skipped'][] = 'Problem already listed: ' . ($pr['title'] ?? '');
            }
            foreach ($p['allergies'] ?? [] as $al) {
                $r = $this->addAllergy($al);
                $r ? $summary['allergies'][] = $r : $summary['skipped'][] = 'Allergy already listed: ' . ($al['substance'] ?? '');
            }
            foreach ($p['prescriptions'] ?? [] as $rx) {
                if (($rx['action'] ?? 'new') === 'stop') {
                    $summary['skipped'][] = 'Stop ' . ($rx['drug'] ?? '') . ': discontinue it in the medication list yourself.';
                    continue;
                }
                $summary['prescriptions'][] = $this->addPrescription($rx);
            }
            $summary['billing'] = $this->addBilling($p['diagnoses'] ?? [], $p['billing'] ?? []);
            sqlCommitTrans();
        } catch (\Throwable $e) {
            sqlRollbackTrans();
            throw $e;
        }
        return $summary;
    }

    private function assertEncounter(): void
    {
        $row = sqlQuery("SELECT COUNT(*) AS n FROM form_encounter WHERE pid = ? AND encounter = ?", [$this->pid, $this->encounter]);
        if (empty($row['n'])) {
            throw new \RuntimeException('This encounter no longer exists.');
        }
    }

    private static function clip($v, int $n): string
    {
        return mb_substr(trim((string) $v), 0, $n);
    }

    private function addSoap(array $s): bool
    {
        $plan = trim((string) ($s['plan'] ?? ''));
        $plan = ($plan === '' ? '' : $plan . "\n\n") . self::NOTE_FOOTER;
        $id = sqlInsert(
            "INSERT INTO form_soap (date, pid, user, groupname, authorized, activity, subjective, objective, assessment, plan) VALUES (NOW(), ?, ?, ?, ?, 1, ?, ?, ?, ?)",
            [$this->pid, $this->userName, $this->groupName, $this->authorized,
                self::clip($s['subjective'] ?? '', 60000), self::clip($s['objective'] ?? '', 60000), self::clip($s['assessment'] ?? '', 60000), self::clip($plan, 60000)]
        );
        addForm($this->encounter, 'SOAP', $id, 'soap', $this->pid, $this->authorized);
        return true;
    }

    private function addProblem(array $pr): ?string
    {
        $title = self::clip($pr['title'] ?? '', 255);
        $code = strtoupper(self::clip($pr['icd10'] ?? '', 20));
        if ($title === '') {
            return null;
        }
        $diagnosis = $code !== '' ? 'ICD10:' . $code : '';
        $dupe = sqlQuery(
            "SELECT id FROM lists WHERE pid = ? AND type = 'medical_problem' AND activity = 1 AND (enddate IS NULL OR enddate = '0000-00-00') AND ((? <> '' AND diagnosis LIKE ?) OR title = ?)",
            [$this->pid, $diagnosis, '%' . $diagnosis . '%', $title]
        );
        if (!empty($dupe['id'])) {
            return null;
        }
        sqlInsert(
            "INSERT INTO lists (uuid, date, type, title, diagnosis, begdate, activity, comments, pid, user, groupname, verification) VALUES (?, NOW(), 'medical_problem', ?, ?, CURDATE(), 1, ?, ?, ?, ?, 'provisional')",
            [UuidRegistry::getRegistryForTable('lists')->createUuid(), $title, $diagnosis, 'Added by Grapheus from encounter ' . $this->encounter, $this->pid, $this->userName, $this->groupName]
        );
        return $title . ($code ? " ($code)" : '');
    }

    private function addAllergy(array $al): ?string
    {
        $title = self::clip($al['substance'] ?? '', 255);
        if ($title === '') {
            return null;
        }
        $dupe = sqlQuery("SELECT id FROM lists WHERE pid = ? AND type = 'allergy' AND activity = 1 AND title = ?", [$this->pid, $title]);
        if (!empty($dupe['id'])) {
            return null;
        }
        $sev = ['mild' => 'mild', 'moderate' => 'moderate', 'severe' => 'severe'][strtolower((string) ($al['severity'] ?? ''))] ?? '';
        sqlInsert(
            "INSERT INTO lists (uuid, date, type, title, begdate, activity, comments, pid, user, groupname, reaction, severity_al, verification) VALUES (?, NOW(), 'allergy', ?, CURDATE(), 1, ?, ?, ?, ?, ?, ?, 'unconfirmed')",
            [UuidRegistry::getRegistryForTable('lists')->createUuid(), $title, 'Reported in encounter ' . $this->encounter . ' (Grapheus)',
                $this->pid, $this->userName, $this->groupName, self::clip($al['reaction'] ?? '', 255), $sev]
        );
        return $title;
    }

    // ---------------------------------------------------------------- prescriptions
    /** OpenEMR stores form/route/interval as list option ids; map the words the clinician used. */
    public static function optionFor(string $list, string $text): string
    {
        $t = strtolower($text);
        $maps = [
            'drug_form' => ['tablet' => '2', 'tab' => '2', 'capsule' => '3', 'cap' => '3', 'suspension' => '1', 'solution' => '4', 'cream' => '10', 'ointment' => '11', 'inhal' => '8', 'puff' => '12', 'drop' => '9'],
            'drug_route' => ['mouth' => 'bymouth', 'oral' => 'bymouth', 'po' => 'bymouth', 'sublingual' => '5', 'topical' => '3', 'skin' => '3', 'rectal' => '2', 'subcutaneous' => '9', 'intramuscular' => 'intramuscular', 'im' => '10', 'iv' => '11', 'intravenous' => '11', 'nasal' => '12', 'inhal' => 'inhale', 'transdermal' => 'transdermal'],
            'drug_interval' => ['twice' => '1', 'bid' => '1', 'b.i.d' => '1', 'three times' => '2', 'tid' => '2', 'four times' => '3', 'qid' => '3', 'every 4' => '5', 'every 6' => '7', 'every 8' => '8', 'bedtime' => '16', 'nightly' => '16', 'as needed' => '17', 'prn' => '17', 'weekly' => '19', 'monthly' => '20', 'once daily' => '9', 'daily' => '9', 'every day' => '9', 'every morning' => '12', 'in the morning' => '12'],
        ];
        foreach ($maps[$list] ?? [] as $word => $id) {
            if ($word !== '' && preg_match('/(^|[^a-z])' . preg_quote($word, '/') . '/', $t)) {
                return $id;
            }
        }
        return '0';
    }

    /** "50 mg" -> [size "50", unit option "1"] */
    public static function strength(string $s): array
    {
        if (preg_match('/([\d.]+)\s*(mcg|mg|g|ml|units?)\b/i', $s, $m)) {
            $unit = ['mg' => '1', 'mcg' => '7', 'g' => '8', 'ml' => '9'][strtolower($m[2])] ?? '0';
            return [$m[1], $unit];
        }
        return ['', '0'];
    }

    private function addPrescription(array $rx): string
    {
        $drug = self::clip($rx['drug'] ?? '', 150);
        if ($drug === '') {
            throw new \RuntimeException('A prescription is missing the drug name.');
        }
        [$size, $unit] = self::strength((string) ($rx['strength'] ?? ''));
        $sig = self::clip($rx['sig'] ?? '', 2000);
        $dosage = preg_match('/\b(?:take|use|inhale|apply|instill)?\s*(\d+(?:\.\d+)?|one|two|half)\b/i', $sig, $m)
            ? (['one' => '1', 'two' => '2', 'half' => '0.5'][strtolower($m[1])] ?? $m[1]) : '';
        $missing = array_filter(array_map('strval', (array) ($rx['missing'] ?? [])));
        $note = self::RX_NOTE . ($missing ? ' Not stated in the visit: ' . implode(', ', $missing) . '.' : '') . (($rx['action'] ?? '') === 'change' ? ' This changes an existing prescription.' : '');
        sqlInsert(
            "INSERT INTO prescriptions (uuid, patient_id, date_added, date_modified, provider_id, encounter, start_date, drug, rxnorm_drugcode, form, dosage, quantity, size, unit, route, `interval`, substitute, refills, per_refill, note, active, datetime, user, erx_source, erx_uploaded, indication, ntx, request_intent, request_intent_title, usage_category, usage_category_title, drug_dosage_instructions, created_by, updated_by)
             VALUES (?, ?, NOW(), NOW(), ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 1, NOW(), ?, 0, 0, ?, 0, 'proposal', 'Proposal', 'outpatient', 'Outpatient', ?, ?, ?)",
            [UuidRegistry::getRegistryForTable('prescriptions')->createUuid(), $this->pid, $this->userId, $this->encounter, $drug,
                self::clip($rx['rxcui'] ?? '', 25), (int) self::optionFor('drug_form', (string) ($rx['form'] ?? '')), self::clip($dosage, 100),
                self::clip($rx['quantity'] ?? '', 31), self::clip($size, 25), (int) $unit, self::optionFor('drug_route', (string) ($rx['route'] ?? '')),
                (int) self::optionFor('drug_interval', $sig), empty($rx['substitution_allowed']) ? 2 : 1, (int) preg_replace('/\D/', '', (string) ($rx['refills'] ?? '0')),
                $note, $this->userName, self::clip($rx['indication'] ?? '', 1000), $sig, $this->userId, $this->userId]
        );
        return trim($drug . ' ' . ($rx['strength'] ?? '')) . ($sig ? ' — ' . $sig : '');
    }

    // ---------------------------------------------------------------- fee sheet
    private function addBilling(array $diagnoses, array $lines): array
    {
        $out = [];
        $provider = (int) (getProviderIdOfEncounter($this->encounter) ?: $this->userId);
        $justify = '';
        foreach ($diagnoses as $d) {
            $code = strtoupper(self::clip($d['icd10'] ?? '', 20));
            if ($code === '' || !preg_match('/^[A-Z][0-9][0-9A-Z](\.[0-9A-Z]{1,4})?$/', $code)) {
                continue;
            }
            $exists = sqlQuery("SELECT id FROM billing WHERE pid = ? AND encounter = ? AND code_type = 'ICD10' AND code = ? AND activity = 1", [$this->pid, $this->encounter, $code]);
            if (empty($exists['id'])) {
                BillingUtilities::addBilling($this->encounter, 'ICD10', $code, self::clip($d['description'] ?? '', 255), $this->pid, '0', $provider, '', '', '0.00', '', '', 0);
                $out[] = 'ICD10 ' . $code;
            }
            $justify .= 'ICD10|' . $code . ':';
        }
        foreach ($lines as $b) {
            $code = strtoupper(self::clip($b['code'] ?? '', 20));
            if ($code === '' || !preg_match('/^[A-Z0-9]{4,6}$/', $code)) {
                continue;
            }
            $type = preg_match('/^[A-Z]\d{4}$/', $code) ? 'HCPCS' : 'CPT4';
            $exists = sqlQuery("SELECT id FROM billing WHERE pid = ? AND encounter = ? AND code_type = ? AND code = ? AND activity = 1", [$this->pid, $this->encounter, $type, $code]);
            if (!empty($exists['id'])) {
                continue;
            }
            $mods = implode(':', array_slice(array_filter(preg_split('/[\s,:-]+/', strtoupper((string) ($b['modifiers'] ?? '')))), 0, 4));
            $price = sqlQuery(
                "SELECT p.pr_price FROM codes c JOIN code_types t ON t.ct_id = c.code_type JOIN prices p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = 'standard' WHERE t.ct_key = ? AND c.code = ? LIMIT 1",
                [$type, $code]
            );
            $fee = !empty($price['pr_price']) ? sprintf('%.2f', $price['pr_price']) : '0.00';
            BillingUtilities::addBilling($this->encounter, $type, $code, self::clip($b['description'] ?? '', 255), $this->pid, '0', $provider, $mods, '1', $fee, '', $justify, 0);
            $out[] = $type . ' ' . $code . ($mods ? '-' . $mods : '');
        }
        return $out;
    }
}
