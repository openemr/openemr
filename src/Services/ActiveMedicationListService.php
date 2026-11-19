<?php

/**
 * ActiveMedicationListService
 *
 * One row per drug name. A later start replaces the earlier row.
 * The same start, or no start, keeps the issue.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Core\OEGlobalsBag;

class ActiveMedicationListService extends BaseService
{
    public const TABLE_NAME = 'lists';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * @param list<array<string, mixed>> $issues
     * @param list<array<string, mixed>> $prescriptions
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public static function merge(array $issues, array $prescriptions): array
    {
        $out = [];
        /** @var array<string, int> $seen */
        $seen = [];
        foreach ($issues as $row) {
            $title = self::cell($row['title'] ?? null);
            if ($title === '') {
                continue;
            }
            self::keepCurrent($out, $seen, [
                'source' => 'issue',
                'title' => $title,
                'dose' => self::cell($row['drug_dosage_instructions'] ?? null),
                'start' => self::optionalDate($row['begdate'] ?? null),
                'end' => self::optionalDate($row['enddate'] ?? null),
                'comments' => self::cell($row['comments'] ?? null),
            ]);
        }
        foreach ($prescriptions as $row) {
            $title = self::cell($row['drug'] ?? null);
            if ($title === '') {
                continue;
            }
            self::keepCurrent($out, $seen, [
                'source' => 'prescription',
                'title' => $title,
                'dose' => self::prescriptionDose($row),
                'start' => self::optionalDate($row['start_date'] ?? null),
                'end' => self::optionalDate($row['end_date'] ?? null),
                'comments' => self::cell($row['note'] ?? null),
            ]);
        }
        return $out;
    }

    /**
     * One row per drug name. A later start date replaces an earlier one.
     * The same start keeps the later row.
     *
     * @param list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}> $out
     * @param array<string, int> $seen
     * @param array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string} $row
     */
    private static function keepCurrent(array &$out, array &$seen, array $row): void
    {
        $key = self::nameKey($row['title']);
        if (!isset($seen[$key])) {
            $seen[$key] = count($out);
            $out[] = $row;
            return;
        }
        $index = $seen[$key];
        if (self::candidateIsCurrent($row, $out[$index])) {
            $out[$index] = $row;
        }
    }

    /**
     * A later start is current. The same start keeps the row already kept,
     * which is the issue when issues are merged first.
     *
     * @param array{start: ?string} $candidate
     * @param array{start: ?string} $kept
     */
    private static function candidateIsCurrent(array $candidate, array $kept): bool
    {
        $candidateStart = $candidate['start'];
        $keptStart = $kept['start'];
        if ($candidateStart === $keptStart) {
            return false;
        }
        if (!is_string($candidateStart)) {
            return false;
        }
        if (!is_string($keptStart)) {
            return true;
        }

        return $candidateStart > $keptStart;
    }

    /**
     * @param list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}> $rows
     * @param list<array{title: string}> $already
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public static function excludeListedNames(array $rows, array $already): array
    {
        $seen = [];
        foreach ($already as $row) {
            $title = self::cell($row['title']);
            if ($title === '') {
                continue;
            }
            $seen[self::nameKey($title)] = true;
        }
        $out = [];
        foreach ($rows as $row) {
            $title = self::cell($row['title']);
            if ($title === '' || isset($seen[self::nameKey($title)])) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * activity = 1 and no end date, or an end date today or later.
     * Outcome 1 is resolved and stays off this list. Prescription rows are
     * included only when the caller says the user may see them.
     *
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getActiveList(int $pid, bool $includePrescriptions = true): array
    {
        $hideErx = $this->hideUploadedErx();
        $erx = self::erxExcludeSql('l.', $hideErx);
        $rxErx = self::erxExcludeSql('p.', $hideErx);
        /** @var list<array<string, mixed>> $issues */
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' AND l.activity = 1 "
            . "AND COALESCE(l.outcome, 0) != 1 "
            . $erx
            . "AND (l.enddate IS NULL OR l.enddate = '0000-00-00' "
            . "OR l.enddate = '0000-00-00 00:00:00' OR l.enddate >= CURDATE()) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        /** @var list<array<string, mixed>> $prescriptions */
        $prescriptions = QueryUtils::fetchRecords(
            self::prescriptionSelectSql()
            . self::prescriptionFromSql()
            . "WHERE p.patient_id = ? AND p.active = '1' "
            . $rxErx
            . "AND (p.end_date IS NULL OR p.end_date = '0000-00-00' "
            . "OR p.end_date = '0000-00-00 00:00:00' OR p.end_date >= CURDATE()) "
            . "ORDER BY p.start_date, p.id",
            [$pid]
        ) ?: [];
        if (!$includePrescriptions) {
            $prescriptions = [];
        } else {
            $prescriptions = self::translateListTitles($prescriptions);
        }

        return self::merge($issues, $prescriptions);
    }

    /**
     * Stopped, a missing or zero activity flag, or an end date before today.
     * Skips names already on the active list. A NULL lists.activity is not
     * active, and it is not excluded by a bare inequality, so it is counted
     * here.
     *
     * @param list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>|null $active
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getInactiveList(int $pid, ?array $active = null, bool $includePrescriptions = true): array
    {
        $hideErx = $this->hideUploadedErx();
        $erx = self::erxExcludeSql('l.', $hideErx);
        $rxErx = self::erxExcludeSql('p.', $hideErx);
        /** @var list<array<string, mixed>> $issues */
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' "
            . $erx
            . "AND (COALESCE(l.activity, 0) != 1 "
            . "OR COALESCE(l.outcome, 0) = 1 "
            . "OR (l.enddate IS NOT NULL AND l.enddate != '0000-00-00' "
            . "AND l.enddate != '0000-00-00 00:00:00' AND l.enddate < CURDATE())) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        /** @var list<array<string, mixed>> $prescriptions */
        $prescriptions = QueryUtils::fetchRecords(
            self::prescriptionSelectSql()
            . self::prescriptionFromSql()
            . "WHERE p.patient_id = ? "
            . $rxErx
            . "AND (p.active != '1' "
            . "OR (p.end_date IS NOT NULL AND p.end_date != '0000-00-00' "
            . "AND p.end_date != '0000-00-00 00:00:00' AND p.end_date < CURDATE())) "
            . "ORDER BY p.start_date, p.id",
            [$pid]
        ) ?: [];
        if (!$includePrescriptions) {
            $prescriptions = [];
        } else {
            $prescriptions = self::translateListTitles($prescriptions);
        }
        $merged = self::merge($issues, $prescriptions);

        return self::excludeListedNames($merged, $active ?? $this->getActiveList($pid, $includePrescriptions));
    }

    /**
     * Trim a cell. Non-strings become an empty string.
     */
    private static function cell(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * Dose line for one prescription.
     *
     * Free-text instructions stay as written. Otherwise the line uses the
     * dosage, the form, the interval, and the strength.
     *
     * @param array<string, mixed> $row
     */
    private static function prescriptionDose(array $row): string
    {
        $dosage = self::cell($row['dosage'] ?? null);
        $instructions = self::cell($row['drug_dosage_instructions'] ?? null);
        if ($instructions !== '') {
            return self::joinParts([$dosage, $instructions]);
        }

        return self::joinParts([
            $dosage,
            self::cell($row['form_title'] ?? null),
            self::cell($row['interval_title'] ?? null),
            self::strength($row),
        ]);
    }

    /**
     * Strength is the size plus the unit title.
     *
     * @param array<string, mixed> $row
     */
    private static function strength(array $row): string
    {
        $size = $row['size'] ?? null;
        if (is_int($size) || is_float($size)) {
            $size = (string) $size;
        }

        return self::joinParts([
            self::cell($size),
            self::cell($row['unit_title'] ?? null),
        ]);
    }

    /**
     * Join the non-empty parts of a dose with single spaces.
     *
     * @param list<string> $parts
     */
    private static function joinParts(array $parts): string
    {
        $kept = [];
        foreach ($parts as $part) {
            if ($part !== '') {
                $kept[] = $part;
            }
        }

        return implode(' ', $kept);
    }

    /**
     * Columns shared by the active and inactive prescription queries.
     */
    private static function prescriptionSelectSql(): string
    {
        return "SELECT p.drug, p.dosage, p.drug_dosage_instructions, p.start_date, p.end_date, "
            . "p.note, p.size, form_opt.title AS form_title, "
            . "interval_opt.title AS interval_title, unit_opt.title AS unit_title ";
    }

    /**
     * Prescription rows with the list titles for form, interval, and unit.
     *
     * A zero id is unset. Comparing it as a number would match every
     * non-numeric option id, so the join keeps only a positive id and
     * compares that id as text. An inactive option still names the value
     * stored on the prescription.
     */
    private static function prescriptionFromSql(): string
    {
        return "FROM prescriptions p "
            . "LEFT JOIN list_options AS form_opt "
            . "ON form_opt.list_id = 'drug_form' "
            . "AND p.form > 0 AND form_opt.option_id = CAST(p.form AS CHAR) "
            . "LEFT JOIN list_options AS interval_opt "
            . "ON interval_opt.list_id = 'drug_interval' "
            . "AND p.interval > 0 AND interval_opt.option_id = CAST(p.interval AS CHAR) "
            . "LEFT JOIN list_options AS unit_opt "
            . "ON unit_opt.list_id = 'drug_units' "
            . "AND p.unit > 0 AND unit_opt.option_id = CAST(p.unit AS CHAR) ";
    }

    /**
     * Translate the list titles the dose line prints.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function translateListTitles(array $rows): array
    {
        foreach ($rows as $index => $row) {
            foreach (['form_title', 'interval_title', 'unit_title'] as $column) {
                $title = self::cell($row[$column] ?? null);
                $row[$column] = $title === '' ? '' : xl_list_label($title);
            }
            $rows[$index] = $row;
        }

        return $rows;
    }

    /**
     * SQL fragment that hides NewCrop-uploaded rows when that setting is on.
     */
    public static function erxExcludeSql(string $columnPrefix, bool $hide): string
    {
        return $hide ? ('AND ' . $columnPrefix . "erx_uploaded != '1' ") : '';
    }

    /**
     * True when Globals hide uploaded eRx medications.
     */
    private function hideUploadedErx(): bool
    {
        $g = OEGlobalsBag::getInstance();
        return $g->getBoolean('erx_enable') && $g->getBoolean('erx_medication_display');
    }

    /**
     * Case-fold a drug name for de-dupe.
     */
    private static function nameKey(string $title): string
    {
        return mb_strtoupper($title, 'UTF-8');
    }

    /**
     * Prefer a query pid over the session pid. Either must be a positive integer.
     *
     * Rejects decimals and scientific notation so '7.5' and '1e3' do not
     * steal the session pid.
     */
    public static function requestedPatientId(mixed $queryPid, mixed $sessionPid): int
    {
        foreach ([$queryPid, $sessionPid] as $candidate) {
            $id = ValidationUtils::validateInt($candidate, min: 1);
            if ($id !== false) {
                return $id;
            }
        }

        return 0;
    }

    /**
     * Patient id for the print page.
     *
     * When a session chart exists, a query pid is only a consistency check.
     * When the session has no patient, a validated query pid is used so a
     * demographics card that loaded via GET pid can still print.
     */
    public static function printPatientId(mixed $queryPid, mixed $sessionPid): int
    {
        $session = self::requestedPatientId(null, $sessionPid);
        $query = self::requestedPatientId($queryPid, 0);
        if ($session < 1) {
            return $query;
        }
        if ($query > 0 && $query !== $session) {
            return 0;
        }

        return $session;
    }

    /**
     * Print URL for the medication list of one patient.
     */
    public static function printHref(string $webRoot, int $pid): string
    {
        $base = rtrim($webRoot, '/') . '/interface/patient_file/summary/demographics.php?print_medications=1';
        return $pid > 0 ? ($base . '&pid=' . $pid) : $base;
    }

    /**
     * Display name from the patient row columns the print page already loads.
     *
     * @param array<string, mixed> $patient
     */
    public static function patientName(array $patient): string
    {
        $parts = [];
        foreach (['title', 'fname', 'mname', 'lname'] as $column) {
            $part = $patient[$column] ?? '';
            if (is_string($part) && trim($part) !== '') {
                $parts[] = trim($part);
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Format a stored medication date. A null date and a non-string result are blank.
     *
     * @param callable(string): mixed $formatter
     */
    public static function formatListDate(?string $value, callable $formatter): string
    {
        if ($value === null) {
            return '';
        }
        $formatted = $formatter($value);

        return is_string($formatted) ? $formatted : '';
    }

    /**
     * Keep a real calendar date, otherwise null.
     */
    private static function optionalDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            return null;
        }
        $s = trim($value);
        if ($s === '' || str_starts_with($s, '0000-00-00')) {
            return null;
        }
        if (
            preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})(?: (?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d)?$/D',
                $s,
                $m
            ) !== 1
        ) {
            return null;
        }
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }
        return $s;
    }
}
