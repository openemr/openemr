<?php

/**
 * DuplicatePatientService finds and scores charts that look like the same person.
 *
 * The duplication score between two patient_data rows is a weighted tally of matching email, DOB,
 * name, sex, surname soundex and phone numbers -- see {@see self::dupScoreSql()}. Each patient's
 * best score against any other patient is cached on patient_data.dupscore, so the report can be
 * built with one indexed read instead of an N-squared comparison. A score of
 * {@see self::SCORE_UNIQUE} is a operator's assertion that the chart is not a duplicate, and is
 * never recomputed.
 *
 * This is the shared home for the scoring rules: library/dupscore.inc.php and
 * library/patient.inc.php delegate here so CLI tools and legacy callers stay in step with the
 * report and with {@see PatientMergeService}.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

use OpenEMR\Common\Database\QueryUtils;

class DuplicatePatientService
{
    /** An operator has declared this chart unique; it is excluded from scoring forever. */
    public const SCORE_UNIQUE = -1;

    /** Queued for recomputation by a full rescore pass. */
    public const SCORE_PENDING = -9;

    /** Charts scoring above this are worth an operator's attention. */
    public const DISPLAY_THRESHOLD = 12;

    /** Charts scoring above this are near-certain duplicates and get flagged in the report. */
    public const HIGHLIGHT_THRESHOLD = 17;

    /** Groups shown on one page of the report. */
    public const MAX_GROUPS = 100;

    /** Patients rescored per batch by {@see self::recalculateAllScores()}. */
    private const RESCORE_BATCH_SIZE = 5000;

    /** Wall-clock ceiling on a full rescore pass, in seconds. */
    private const RESCORE_TIME_LIMIT = 240 * 60;

    /**
     * SQL expression computing the duplication score between patient_data rows aliased p1 and p2.
     *
     * Callers must alias the two rows exactly `p1` and `p2`. The weights are deliberately coarse:
     * six points each for the strong identifiers, two for a surname soundex hit and one for any
     * shared phone number, so that {@see self::DISPLAY_THRESHOLD} means "at least two strong
     * identifiers agree".
     */
    public static function dupScoreSql(): string
    {
        return
            "6 * (TRIM(p1.email) != '' AND TRIM(p1.email) = TRIM(p2.email)) + " .
            "6 * (p1.DOB IS NOT NULL AND p2.DOB IS NOT NULL AND p1.DOB = p2.DOB) + " .
            "6 * (LOWER(CONCAT(TRIM(p1.fname), '', TRIM(p1.lname))) = LOWER(CONCAT(TRIM(p2.fname), '', TRIM(p2.lname)))) + " .
            "6 * (TRIM(p1.sex) != '' AND TRIM(p1.sex) = TRIM(p2.sex)) + " .
            "2 * (SOUNDEX(p1.lname) = SOUNDEX(p2.lname)) + " .
            "1 * (" .
            "(TRIM(p1.phone_home) != '' AND ( " .
            "REPLACE(REPLACE(p1.phone_home, '-', ''), ' ', '') IN ( " .
            "REPLACE(REPLACE(p2.phone_home, '-', ''), ' ', ''), " .
            "REPLACE(REPLACE(p2.phone_biz , '-', ''), ' ', ''), " .
            "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', '')))) " .
            "OR (TRIM(p1.phone_biz) != '' AND ( " .
            "REPLACE(REPLACE(p1.phone_biz , '-', ''), ' ', '') IN ( " .
            "REPLACE(REPLACE(p2.phone_biz , '-', ''), ' ', ''), " .
            "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', '')))) " .
            "OR (TRIM(p1.phone_cell) != '' AND ( " .
            "REPLACE(REPLACE(p1.phone_cell, '-', ''), ' ', '') = " .
            "REPLACE(REPLACE(p2.phone_cell, '-', ''), ' ', ''))) " .
            ")";
    }

    /**
     * Recompute and store one patient's score, returning it.
     *
     * Uses a symmetric comparison (p2.pid != p1.pid) rather than the asymmetric p2.pid < p1.pid
     * that {@see self::recalculateAllScores()} uses. A single-patient update has to look in both
     * directions or editing a low-pid chart would never notice it now matches a higher-pid one.
     */
    public function recalculateScore(int $pid): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT MAX(" . self::dupScoreSql() . ") AS dupscore " .
            "FROM patient_data AS p1, patient_data AS p2 WHERE " .
            "p1.pid = ? AND p2.pid != p1.pid AND p2.dupscore != ?",
            [$pid, self::SCORE_UNIQUE]
        );

        $score = is_array($row) && is_numeric($row['dupscore'] ?? null) ? (int) $row['dupscore'] : 0;
        QueryUtils::sqlStatementThrowException(
            "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
            [$score, $pid]
        );

        return $score;
    }

    /**
     * Exclude a chart from duplicate detection permanently.
     */
    public function markUnique(int $pid): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
            [self::SCORE_UNIQUE, $pid]
        );
    }

    /**
     * Rescore every patient that has not been marked unique, and report how many were updated.
     *
     * Runs in batches: each pass grabs up to {@see self::RESCORE_BATCH_SIZE} still-pending patients,
     * scores them and writes the results back, stopping when a short batch shows the queue is
     * drained. The asymmetric p2.pid < p1.pid comparison halves the work and is correct here only
     * because every patient is being scored in the same pass.
     */
    public function recalculateAllScores(): int
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE patient_data SET dupscore = ? WHERE dupscore != ?",
            [self::SCORE_PENDING, self::SCORE_UNIQUE],
            noLog: true
        );

        $deadline = time() + self::RESCORE_TIME_LIMIT;
        $updated = 0;
        $finished = false;

        while (!$finished && time() < $deadline) {
            $rows = QueryUtils::fetchRecordsNoLog(
                "SELECT p1.pid, MAX(" . self::dupScoreSql() . ") AS dupscore" .
                " FROM patient_data AS p1, patient_data AS p2" .
                " WHERE p1.dupscore = ? AND p2.pid < p1.pid" .
                " GROUP BY p1.pid ORDER BY p1.pid LIMIT " . QueryUtils::escapeLimit(self::RESCORE_BATCH_SIZE),
                [self::SCORE_PENDING]
            );

            foreach ($rows as $row) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
                    [$row['dupscore'] ?? 0, $row['pid'] ?? 0],
                    noLog: true
                );
                ++$updated;
            }

            if (count($rows) < self::RESCORE_BATCH_SIZE) {
                $finished = true;
            }
        }

        return $updated;
    }

    /**
     * Build the report: clusters of charts that scored as likely duplicates of one another.
     *
     * Each candidate is scored against every other chart on demand, because the cached dupscore
     * only records the best score, not who it was against. Charts already listed in an earlier
     * group are skipped, and a candidate whose every match was already listed produces no group at
     * all rather than an orphan row.
     *
     * @return list<DuplicatePatientGroup>
     */
    public function findDuplicateGroups(int $limit = self::MAX_GROUPS): array
    {
        $scoreSql = self::dupScoreSql();

        $candidates = QueryUtils::fetchRecords(
            "SELECT * FROM patient_data WHERE dupscore > ? ORDER BY dupscore DESC, pid DESC"
            . " LIMIT " . QueryUtils::escapeLimit($limit),
            [self::DISPLAY_THRESHOLD]
        );

        /** @var array<int, true> $listed */
        $listed = [];
        $groups = [];

        foreach ($candidates as $candidate) {
            $primaryPid = is_numeric($candidate['pid'] ?? null) ? (int) $candidate['pid'] : 0;
            if ($primaryPid === 0 || isset($listed[$primaryPid])) {
                continue;
            }

            // p2.pid != p1.pid rather than p2.pid < p1.pid: a duplicate introduced by editing an
            // older chart to match a newer one only shows up when both directions are considered.
            $matchRows = QueryUtils::fetchRecords(
                "SELECT p2.*, ($scoreSql) AS myscore "
                . "FROM patient_data AS p1, patient_data AS p2 "
                . "WHERE p1.pid = ? AND p2.pid != p1.pid AND p2.dupscore != ? AND ($scoreSql) > ? "
                . "ORDER BY myscore DESC, p2.pid DESC",
                [$primaryPid, self::SCORE_UNIQUE, self::DISPLAY_THRESHOLD]
            );

            /** @var list<array{pid: int, row: array<mixed>}> $fresh */
            $fresh = [];
            foreach ($matchRows as $matchRow) {
                $matchPid = is_numeric($matchRow['pid'] ?? null) ? (int) $matchRow['pid'] : 0;
                if ($matchPid === 0 || isset($listed[$matchPid])) {
                    continue;
                }
                $fresh[] = ['pid' => $matchPid, 'row' => $matchRow];
            }

            if ($fresh === []) {
                continue;
            }

            // Only mark charts as listed once the group is known to be real; otherwise a discarded
            // candidate would suppress its matches from every later group.
            $listed[$primaryPid] = true;
            $matches = [];
            foreach ($fresh as $match) {
                $listed[$match['pid']] = true;
                $matches[] = DuplicatePatientRow::forMatch($match['row'], $primaryPid);
            }

            $groups[] = new DuplicatePatientGroup(
                count($groups) + 1,
                DuplicatePatientRow::forPrimary($candidate),
                $matches
            );
        }

        return $groups;
    }
}
