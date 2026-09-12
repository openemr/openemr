<?php

/**
 * Active and inactive medications for a patient chart print.
 *
 * Issues (lists type medication) come first. Prescriptions whose drug name
 * already appears as an issue title are skipped so the printed list has
 * each drug once. Inactive rows are a separate list; a name already on
 * the active list is not repeated there.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

final class ActiveMedicationListService
{
    /**
     * Combine issue rows and prescription rows, dropping prescriptions
     * whose drug name already appears as an issue title.
     *
     * @param list<array<string, mixed>> $issues
     * @param list<array<string, mixed>> $prescriptions
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public static function merge(array $issues, array $prescriptions): array
    {
        $out = [];
        $seen = [];
        foreach ($issues as $row) {
            $title = trim((string) ($row['title'] ?? ''));
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
                'dose' => trim((string) ($row['drug_dosage_instructions'] ?? '')),
                'start' => self::optionalDate($row['begdate'] ?? null),
                'end' => self::optionalDate($row['enddate'] ?? null),
                'comments' => trim((string) ($row['comments'] ?? '')),
            ];
        }
        foreach ($prescriptions as $row) {
            $title = trim((string) ($row['drug'] ?? ''));
            if ($title === '') {
                continue;
            }
            $key = self::nameKey($title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $doseParts = [
                trim((string) ($row['dosage'] ?? '')),
                trim((string) ($row['drug_dosage_instructions'] ?? '')),
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
     * Drop rows whose title already appears on another list (case-insensitive).
     *
     * Used so an ended prescription does not reprint a drug that is still
     * an active issue.
     *
     * @param list<array{title: string, ...}> $rows
     * @param list<array{title: string, ...}> $already
     * @return list<array{title: string, ...}>
     */
    public static function excludeListedNames(array $rows, array $already): array
    {
        $seen = [];
        foreach ($already as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $seen[self::nameKey($title)] = true;
        }
        $out = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '' || isset($seen[self::nameKey($title)])) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Active medications for one patient.
     *
     * An issue is active when activity is 1 and enddate is empty or today
     * or later. A prescription is active when active is 1 and end_date is
     * empty or today or later.
     *
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getActiveList(int $pid): array
    {
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' AND l.activity = 1 "
            . "AND (l.enddate IS NULL OR l.enddate = '0000-00-00' "
            . "OR l.enddate = '0000-00-00 00:00:00' OR l.enddate >= CURDATE()) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        $prescriptions = QueryUtils::fetchRecords(
            "SELECT drug, dosage, drug_dosage_instructions, start_date, end_date "
            . "FROM prescriptions "
            . "WHERE patient_id = ? AND active = '1' "
            . "AND (end_date IS NULL OR end_date = '0000-00-00' "
            . "OR end_date = '0000-00-00 00:00:00' OR end_date >= CURDATE()) "
            . "ORDER BY start_date, id",
            [$pid]
        ) ?: [];
        return self::merge($issues, $prescriptions);
    }

    /**
     * Inactive / historical medications for one patient.
     *
     * An issue is inactive when activity is not 1, or when enddate is a
     * real date before today. A prescription is inactive when active is
     * not 1, or when end_date is a real date before today. Names already
     * on the active list are omitted.
     *
     * @param list<array{title: string, ...}>|null $active
     * @return list<array{source: string, title: string, dose: string, start: ?string, end: ?string, comments: string}>
     */
    public function getInactiveList(int $pid, ?array $active = null): array
    {
        $issues = QueryUtils::fetchRecords(
            "SELECT l.title, l.begdate, l.enddate, l.comments, m.drug_dosage_instructions "
            . "FROM lists l "
            . "LEFT JOIN lists_medication m ON m.list_id = l.id "
            . "WHERE l.pid = ? AND l.type = 'medication' "
            . "AND (l.activity != 1 "
            . "OR (l.enddate IS NOT NULL AND l.enddate != '0000-00-00' "
            . "AND l.enddate != '0000-00-00 00:00:00' AND l.enddate < CURDATE())) "
            . "ORDER BY l.begdate, l.id",
            [$pid]
        ) ?: [];
        $prescriptions = QueryUtils::fetchRecords(
            "SELECT drug, dosage, drug_dosage_instructions, start_date, end_date "
            . "FROM prescriptions "
            . "WHERE patient_id = ? "
            . "AND (active != '1' "
            . "OR (end_date IS NOT NULL AND end_date != '0000-00-00' "
            . "AND end_date != '0000-00-00 00:00:00' AND end_date < CURDATE())) "
            . "ORDER BY start_date, id",
            [$pid]
        ) ?: [];
        $merged = self::merge($issues, $prescriptions);
        return self::excludeListedNames($merged, $active ?? $this->getActiveList($pid));
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
        $s = trim((string) $value);
        if ($s === '' || str_starts_with($s, '0000-00-00')) {
            return null;
        }
        return $s;
    }
}
