<?php

/**
 * DuplicatePatientRow is one line of the Duplicate Patient Management report.
 *
 * It is a presentation row rather than a patient record: names, phone numbers and dates arrive
 * already assembled and formatted so that the HTML table and the CSV export cannot drift apart.
 *
 * Rows come in two kinds. The first row of a group is the "primary" -- the patient whose stored
 * dupscore put the group on the report -- and offers the Mark Unique / Recompute actions. The rest
 * are "matches", scored against that primary, and offer the merge actions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final readonly class DuplicatePatientRow
{
    /** Scope label for a chart that should be merged away. */
    public const SCOPE_MERGE_FROM = 'Merge From';

    /** Scope label for the chart the group should be merged into. */
    public const SCOPE_MERGE_TO = 'Merge To';

    /**
     * @param string $scopeLabel     Untranslated label ('', 'Merge From', 'Merge To'); the view
     *                               translates it.
     * @param string $highlightClass CSS class driving the row background ('', 'highlight',
     *                               'highlight-master').
     * @param int    $topPid         The pid of the group's primary row. The merge actions need both
     *                               it and this row's pid to know which chart survives.
     * @param bool   $isMatch        True for a scored match, false for the group's primary row.
     */
    private function __construct(
        public int $pid,
        public string $publicId,
        public int $score,
        public string $name,
        public string $dateOfBirth,
        public string $sex,
        public string $email,
        public string $phones,
        public string $registeredOn,
        public string $street,
        public int $topPid,
        public bool $isMatch,
        public string $scopeLabel,
        public string $highlightClass,
    ) {
    }

    /**
     * The row a group is built around, scored by its stored dupscore.
     *
     * @param array<mixed> $row A patient_data row.
     */
    public static function forPrimary(array $row): self
    {
        return self::fromPatientRow($row, self::intField($row, 'pid'), false);
    }

    /**
     * A candidate duplicate, scored against the group's primary row.
     *
     * @param array<mixed> $row A patient_data row plus a `myscore` column.
     */
    public static function forMatch(array $row, int $topPid): self
    {
        return self::fromPatientRow($row, $topPid, true);
    }

    /**
     * @param array<mixed> $row
     */
    private static function fromPatientRow(array $row, int $topPid, bool $isMatch): self
    {
        // The stored dupscore decides the highlight for every row; a match that scores high against
        // this particular primary then overrides it to mark the chart to merge into.
        $storedScore = self::intField($row, 'dupscore');
        $matchScore = self::intField($row, 'myscore');

        $scopeLabel = '';
        $highlightClass = '';
        if ($storedScore > DuplicatePatientService::HIGHLIGHT_THRESHOLD) {
            $scopeLabel = self::SCOPE_MERGE_FROM;
            $highlightClass = 'highlight';
        }
        if ($isMatch && $matchScore > DuplicatePatientService::HIGHLIGHT_THRESHOLD) {
            $scopeLabel = self::SCOPE_MERGE_TO;
            $highlightClass = 'highlight-master';
        }

        return new self(
            pid: self::intField($row, 'pid'),
            publicId: self::stringField($row, 'pubpid'),
            score: $isMatch ? $matchScore : $storedScore,
            name: self::stringField($row, 'lname') . ', ' . self::stringField($row, 'fname')
                . ' ' . self::stringField($row, 'mname'),
            // Times are never shown, and a DOB carrying one would break the short-date format.
            dateOfBirth: self::formatShortDate(substr(self::stringField($row, 'DOB'), 0, 10)),
            sex: self::stringField($row, 'sex'),
            email: self::stringField($row, 'email'),
            phones: self::joinPhones($row),
            registeredOn: self::formatShortDate(self::stringField($row, 'regdate')),
            street: self::stringField($row, 'street'),
            topPid: $topPid,
            isMatch: $isMatch,
            scopeLabel: $scopeLabel,
            highlightClass: $highlightClass,
        );
    }

    private static function formatShortDate(string $date): string
    {
        $formatted = oeFormatShortDate($date);
        return is_scalar($formatted) ? (string) $formatted : '';
    }

    /**
     * @param array<mixed> $row
     */
    private static function joinPhones(array $row): string
    {
        $phones = [];
        foreach (['phone_home', 'phone_biz', 'phone_cell'] as $field) {
            $phone = trim(self::stringField($row, $field));
            if ($phone !== '') {
                $phones[] = $phone;
            }
        }

        return implode(', ', $phones);
    }

    /**
     * @param array<mixed> $row
     */
    private static function stringField(array $row, string $field): string
    {
        $value = $row[$field] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param array<mixed> $row
     */
    private static function intField(array $row, string $field): int
    {
        $value = $row[$field] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }
}
