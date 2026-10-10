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

declare(strict_types=1);

namespace Exetazo\Grapheus;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Uuid\UuidRegistry;

final readonly class Applier
{
    public const NOTE_FOOTER = 'Documentation drafted with Grapheus (AI scribe) and reviewed by the clinician.';
    public const RX_NOTE = 'Drafted by Grapheus from the visit. Review and send it yourself; Grapheus never transmits prescriptions.';

    public function __construct(
        private int $pid,
        private int $encounter,
        private int $userId,
        private string $userName,
        private string $groupName,
        private int $authorized = 0
    ) {
    }

    /**
     * @param array<string, mixed> $p
     * @return array{note: bool, problems: list<string>, allergies: list<string>, prescriptions: list<string>, billing: list<string>, skipped: list<string>}
     */
    public function apply(array $p): array
    {
        $exists = Db::one("SELECT COUNT(*) AS n FROM form_encounter WHERE pid = ? AND encounter = ?", [$this->pid, $this->encounter]);
        if (Val::int($exists['n'] ?? 0) === 0) {
            throw new \RuntimeException('This encounter no longer exists.');
        }
        return Db::transaction(function () use ($p): array {
            $summary = ['note' => false, 'problems' => [], 'allergies' => [], 'prescriptions' => [], 'billing' => [], 'skipped' => []];
            $soap = Val::map($p['soap'] ?? null);
            if ($soap !== []) {
                $summary['note'] = $this->addSoap($soap);
            }
            foreach (Val::maps($p['problems'] ?? null) as $pr) {
                $r = $this->addProblem($pr);
                if ($r !== null) {
                    $summary['problems'][] = $r;
                } else {
                    $summary['skipped'][] = 'Problem already listed: ' . Val::str($pr['title'] ?? '');
                }
            }
            foreach (Val::maps($p['allergies'] ?? null) as $al) {
                $r = $this->addAllergy($al);
                if ($r !== null) {
                    $summary['allergies'][] = $r;
                } else {
                    $summary['skipped'][] = 'Allergy already listed: ' . Val::str($al['substance'] ?? '');
                }
            }
            foreach (Val::maps($p['prescriptions'] ?? null) as $rx) {
                if (Val::str($rx['action'] ?? 'new') === 'stop') {
                    $summary['skipped'][] = 'Stop ' . Val::str($rx['drug'] ?? '') . ': discontinue it in the medication list yourself.';
                    continue;
                }
                $summary['prescriptions'][] = $this->addPrescription($rx);
            }
            $summary['billing'] = $this->addBilling(Val::maps($p['diagnoses'] ?? null), Val::maps($p['billing'] ?? null));
            return $summary;
        });
    }

    /**
     * @param array<string, mixed> $s
     */
    private function addSoap(array $s): bool
    {
        $plan = Val::str($s['plan'] ?? '', 60000);
        $plan = ($plan === '' ? '' : $plan . "\n\n") . self::NOTE_FOOTER;
        $id = Db::insert(
            "INSERT INTO form_soap (date, pid, user, groupname, authorized, activity, subjective, objective, assessment, plan) VALUES (NOW(), ?, ?, ?, ?, 1, ?, ?, ?, ?)",
            [$this->pid, $this->userName, $this->groupName, $this->authorized,
                Val::str($s['subjective'] ?? '', 60000), Val::str($s['objective'] ?? '', 60000), Val::str($s['assessment'] ?? '', 60000), $plan]
        );
        Compat::addForm($this->encounter, 'SOAP', $id, 'soap', $this->pid, $this->authorized);
        return true;
    }

    /**
     * @param array<string, mixed> $pr
     */
    private function addProblem(array $pr): ?string
    {
        $title = Val::str($pr['title'] ?? '', 255);
        $code = strtoupper(Val::str($pr['icd10'] ?? '', 20));
        if ($title === '') {
            return null;
        }
        $diagnosis = $code !== '' ? 'ICD10:' . $code : '';
        $dupe = Db::one(
            "SELECT id FROM lists WHERE pid = ? AND type = 'medical_problem' AND activity = 1 AND (enddate IS NULL OR enddate = '0000-00-00')
             AND ((? <> '' AND FIND_IN_SET(?, REPLACE(REPLACE(COALESCE(diagnosis, ''), '; ', ','), ';', ',')) > 0) OR title = ?)",
            [$this->pid, $diagnosis, $diagnosis, $title]
        );
        if ($dupe !== null) {
            return null;
        }
        Db::insert(
            "INSERT INTO lists (uuid, date, type, title, diagnosis, begdate, activity, comments, pid, user, groupname, verification) VALUES (?, NOW(), 'medical_problem', ?, ?, CURDATE(), 1, ?, ?, ?, ?, 'provisional')",
            [UuidRegistry::getRegistryForTable('lists')->createUuid(), $title, $diagnosis, 'Added by Grapheus from encounter ' . $this->encounter, $this->pid, $this->userName, $this->groupName]
        );
        return $title . ($code !== '' ? " ($code)" : '');
    }

    /**
     * @param array<string, mixed> $al
     */
    private function addAllergy(array $al): ?string
    {
        $title = Val::str($al['substance'] ?? '', 255);
        if ($title === '') {
            return null;
        }
        if (Db::one("SELECT id FROM lists WHERE pid = ? AND type = 'allergy' AND activity = 1 AND title = ?", [$this->pid, $title]) !== null) {
            return null;
        }
        $sev = ['mild' => 'mild', 'moderate' => 'moderate', 'severe' => 'severe'][strtolower(Val::str($al['severity'] ?? ''))] ?? '';
        Db::insert(
            "INSERT INTO lists (uuid, date, type, title, begdate, activity, comments, pid, user, groupname, reaction, severity_al, verification) VALUES (?, NOW(), 'allergy', ?, CURDATE(), 1, ?, ?, ?, ?, ?, ?, 'unconfirmed')",
            [UuidRegistry::getRegistryForTable('lists')->createUuid(), $title, 'Reported in encounter ' . $this->encounter . ' (Grapheus)',
                $this->pid, $this->userName, $this->groupName, Val::str($al['reaction'] ?? '', 255), $sev]
        );
        return $title;
    }

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
            if (preg_match('/(^|[^a-z])' . preg_quote($word, '/') . '/', $t) === 1) {
                return $id;
            }
        }
        return '0';
    }

    /**
     * "50 mg" -> ["50", "1"] (size, unit option id)
     *
     * @return array{0: string, 1: string}
     */
    public static function strength(string $s): array
    {
        if (preg_match('/([\d.]+)\s*(mcg|mg|g|ml|units?)\b/i', $s, $m) === 1) {
            $unit = ['mg' => '1', 'mcg' => '7', 'g' => '8', 'ml' => '9'][strtolower($m[2])] ?? '0';
            return [$m[1], $unit];
        }
        return ['', '0'];
    }

    /**
     * @param array<string, mixed> $rx
     */
    private function addPrescription(array $rx): string
    {
        $drug = Val::str($rx['drug'] ?? '', 150);
        if ($drug === '') {
            throw new \RuntimeException('A prescription is missing the drug name.');
        }
        $strengthText = Val::str($rx['strength'] ?? '');
        [$size, $unit] = self::strength($strengthText);
        $sig = Val::str($rx['sig'] ?? '', 2000);
        $dosage = '';
        if (preg_match('/\b(?:take|use|inhale|apply|instill)?\s*(\d+(?:\.\d+)?|one|two|half)\b/i', $sig, $m) === 1) {
            $dosage = ['one' => '1', 'two' => '2', 'half' => '0.5'][strtolower($m[1])] ?? $m[1];
        }
        $missing = Val::strings($rx['missing'] ?? null);
        $note = self::RX_NOTE
            . ($missing !== [] ? ' Not stated in the visit: ' . implode(', ', $missing) . '.' : '')
            . (Val::str($rx['action'] ?? '') === 'change' ? ' This changes an existing prescription.' : '');
        Db::insert(
            "INSERT INTO prescriptions (uuid, patient_id, date_added, date_modified, provider_id, encounter, start_date, drug, rxnorm_drugcode, form, dosage, quantity, size, unit, route, `interval`, substitute, refills, per_refill, note, active, datetime, user, erx_source, erx_uploaded, indication, ntx, request_intent, request_intent_title, usage_category, usage_category_title, drug_dosage_instructions, created_by, updated_by)
             VALUES (?, ?, NOW(), NOW(), ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 1, NOW(), ?, 0, 0, ?, 0, 'proposal', 'Proposal', 'outpatient', 'Outpatient', ?, ?, ?)",
            [UuidRegistry::getRegistryForTable('prescriptions')->createUuid(), $this->pid, $this->userId, $this->encounter, $drug,
                Val::str($rx['rxcui'] ?? '', 25), Val::int(self::optionFor('drug_form', Val::str($rx['form'] ?? ''))), mb_substr($dosage, 0, 100),
                Val::str($rx['quantity'] ?? '', 31), mb_substr($size, 0, 25), Val::int($unit), self::optionFor('drug_route', Val::str($rx['route'] ?? '')),
                Val::int(self::optionFor('drug_interval', $sig)), Val::bool($rx['substitution_allowed'] ?? true) ? 1 : 2,
                Val::int(preg_replace('/\D/', '', Val::str($rx['refills'] ?? '0'))), $note, $this->userName, Val::str($rx['indication'] ?? '', 1000),
                $sig, $this->userId, $this->userId]
        );
        return trim($drug . ' ' . $strengthText) . ($sig !== '' ? ' — ' . $sig : '');
    }

    /**
     * @param list<array<string, mixed>> $diagnoses
     * @param list<array<string, mixed>> $lines
     * @return list<string>
     */
    private function addBilling(array $diagnoses, array $lines): array
    {
        $out = [];
        $enc = Db::one("SELECT provider_id FROM form_encounter WHERE pid = ? AND encounter = ?", [$this->pid, $this->encounter]);
        $provider = Val::int($enc['provider_id'] ?? 0);
        if ($provider === 0) {
            $provider = $this->userId;
        }
        $justify = '';
        foreach ($diagnoses as $d) {
            $code = strtoupper(Val::str($d['icd10'] ?? '', 20));
            if (preg_match('/^[A-Z][0-9][0-9A-Z](\.[0-9A-Z]{1,4})?$/', $code) !== 1) {
                continue;
            }
            $have = Db::one("SELECT id FROM billing WHERE pid = ? AND encounter = ? AND code_type = 'ICD10' AND code = ? AND activity = 1", [$this->pid, $this->encounter, $code]);
            if ($have === null) {
                BillingUtilities::addBilling($this->encounter, 'ICD10', $code, Val::str($d['description'] ?? '', 255), $this->pid, '0', $provider, '', '', '0.00', '', '', 0);
                $out[] = 'ICD10 ' . $code;
            }
            $justify .= 'ICD10|' . $code . ':';
        }
        foreach ($lines as $b) {
            $code = strtoupper(Val::str($b['code'] ?? '', 20));
            if (preg_match('/^[A-Z0-9]{4,6}$/', $code) !== 1) {
                continue;
            }
            $type = preg_match('/^[A-Z]\d{4}$/', $code) === 1 ? 'HCPCS' : 'CPT4';
            if (Db::one("SELECT id FROM billing WHERE pid = ? AND encounter = ? AND code_type = ? AND code = ? AND activity = 1", [$this->pid, $this->encounter, $type, $code]) !== null) {
                continue;
            }
            $mods = array_slice(array_values(array_filter(preg_split('/[\s,:-]+/', strtoupper(Val::str($b['modifiers'] ?? ''))) ?: [], static fn (string $m): bool => $m !== '')), 0, 4);
            $modifier = implode(':', $mods);
            $price = Db::one(
                "SELECT p.pr_price FROM codes c JOIN code_types t ON t.ct_id = c.code_type JOIN prices p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = 'standard' WHERE t.ct_key = ? AND c.code = ? LIMIT 1",
                [$type, $code]
            );
            $fee = sprintf('%.2f', (float) Val::str($price['pr_price'] ?? '0'));
            BillingUtilities::addBilling($this->encounter, $type, $code, Val::str($b['description'] ?? '', 255), $this->pid, '0', $provider, $modifier, '1', $fee, '', $justify, 0);
            $out[] = $type . ' ' . $code . ($modifier !== '' ? '-' . $modifier : '');
        }
        return $out;
    }
}
