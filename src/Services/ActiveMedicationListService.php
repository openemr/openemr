<?php

/**
 * ActiveMedicationListService
 *
 * One row per drug name. Issue titles win over prescriptions.
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
use OpenEMR\Core\OEGlobalsBag;

class ActiveMedicationListService
{
    /**
     * @param list<array<string, mixed>> $issues
     * @param list<array<string, mixed>> $prescriptions
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public static function merge(array $issues, array $prescriptions): array
    {
        $out = [];
        $seen = [];
        foreach ($issues as $row) {
            $title = self::cell($row['title'] ?? null);
            if ($title === '') {
                continue;
            }
            $key = self::nameKey($title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = [
                'source' => 'issue',
                'title' => $title,
                'dose' => self::cell($row['drug_dosage_instructions'] ?? null),
                'start' => self::optionalDate($row['begdate'] ?? null),
                'end' => self::optionalDate($row['enddate'] ?? null),
                'comments' => self::cell($row['comments'] ?? null),
            ];
        }
        foreach ($prescriptions as $row) {
            $title = self::cell($row['drug'] ?? null);
            if ($title === '') {
                continue;
            }
            $key = self::nameKey($title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $doseParts = [
                self::cell($row['dosage'] ?? null),
                self::cell($row['drug_dosage_instructions'] ?? null),
            ];
            $out[] = [
                'source' => 'prescription',
                'title' => $title,
                'dose' => trim(implode(' ', array_filter($doseParts, static fn(string $p): bool => $p !== ''))),
                'start' => self::optionalDate($row['start_date'] ?? null),
                'end' => self::optionalDate($row['end_date'] ?? null),
                'comments' => '',
            ];
        }
        return $out;
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
     * activity = 1 (or prescriptions.active = 1) and no end date, or end date today or later.
     *
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getActiveList(int $pid): array
    {
        $hideErx = $this->hideUploadedErx();
        $erx = self::erxExcludeSql('l.', $hideErx);
        $rxErx = self::erxExcludeSql('', $hideErx);
        /** @var list<array<string, mixed>> $issues */
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' AND l.activity = 1 "
            . $erx
            . "AND (l.enddate IS NULL OR l.enddate = '0000-00-00' "
            . "OR l.enddate = '0000-00-00 00:00:00' OR l.enddate >= CURDATE()) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        /** @var list<array<string, mixed>> $prescriptions */
        $prescriptions = QueryUtils::fetchRecords(
            "SELECT drug, dosage, drug_dosage_instructions, start_date, end_date "
            . "FROM prescriptions "
            . "WHERE patient_id = ? AND active = '1' "
            . $rxErx
            . "AND (end_date IS NULL OR end_date = '0000-00-00' "
            . "OR end_date = '0000-00-00 00:00:00' OR end_date >= CURDATE()) "
            . "ORDER BY start_date, id",
            [$pid]
        ) ?: [];
        return self::merge($issues, $prescriptions);
    }

    /**
     * Stopped, activity = 0, or an end date before today. Skips names already on the active list.
     *
     * @param list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>|null $active
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getInactiveList(int $pid, ?array $active = null): array
    {
        $hideErx = $this->hideUploadedErx();
        $erx = self::erxExcludeSql('l.', $hideErx);
        $rxErx = self::erxExcludeSql('', $hideErx);
        /** @var list<array<string, mixed>> $issues */
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' "
            . $erx
            . "AND (l.activity != 1 "
            . "OR (l.enddate IS NOT NULL AND l.enddate != '0000-00-00' "
            . "AND l.enddate != '0000-00-00 00:00:00' AND l.enddate < CURDATE())) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        /** @var list<array<string, mixed>> $prescriptions */
        $prescriptions = QueryUtils::fetchRecords(
            "SELECT drug, dosage, drug_dosage_instructions, start_date, end_date "
            . "FROM prescriptions "
            . "WHERE patient_id = ? "
            . $rxErx
            . "AND (active != '1' "
            . "OR (end_date IS NOT NULL AND end_date != '0000-00-00' "
            . "AND end_date != '0000-00-00 00:00:00' AND end_date < CURDATE())) "
            . "ORDER BY start_date, id",
            [$pid]
        ) ?: [];
        $merged = self::merge($issues, $prescriptions);
        return self::excludeListedNames($merged, $active ?? $this->getActiveList($pid));
    }

    private static function cell(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    public static function erxExcludeSql(string $columnPrefix, bool $hide): string
    {
        return $hide ? ('AND ' . $columnPrefix . "erx_uploaded != '1' ") : '';
    }

    private function hideUploadedErx(): bool
    {
        $g = OEGlobalsBag::getInstance();
        return $g->getBoolean('erx_enable') && $g->getBoolean('erx_medication_display');
    }

    private static function nameKey(string $title): string
    {
        return mb_strtoupper($title, 'UTF-8');
    }

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
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m) !== 1) {
            return null;
        }
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }
        return $s;
    }
}
