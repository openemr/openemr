<?php

/**
 * One rule for "is this dated record in force": allergies, list medications
 * and problems (the lists table), prescriptions, and, through the coverage
 * day, insurance.
 *
 * A record is in force at a moment when it isn't withdrawn and hasn't ended:
 *
 * - Withdrawn: an issue whose activity is 0 (the Rx editor's "Add to
 *   medication list" unticked), a prescription whose active isn't 1 (the Rx
 *   editor's "currently active" unticked, or -1 from the C-CDA import's
 *   discontinue). An issue's activity left NULL (the fee sheet's new problem,
 *   the CCR import) is not withdrawn. [open question W]
 * - Ended: the end date, when there is one, has passed. A blank end (NULL,
 *   or 0000-00-00, which sql_mode '' stores for a mistyped date) is no end,
 *   as dateEmptySql() and the coverage rule read it. [open question Z]
 *   - A DATETIME end (lists.enddate) is the instant it names: the record is
 *     over after that instant, so an end at midnight today is already over
 *     during the day (every reader reads it so today). [open question M]
 *   - A prescription's DATE end (prescriptions.end_date) is the day it
 *     stopped: in force while the end is after today. OpenEMR's own writers
 *     store today when they stop one (a FHIR write of "completed", the
 *     C-CDA import's discontinue), and master's two end-date readers (FHIR's
 *     status, the portal) read today's end as over. [open question F: the
 *     coverage rule reads a DATE end as including its day, as FHIR's Period,
 *     HL7's IN1-13 and QDM do; insurance keeps that rule through its day]
 * - Not read: the begin date (an issue's begdate is its onset; a future one
 *   doesn't hide the record) [open question B], outcome and occurrence (a
 *   clinical judgement and a recurrence kind; the export vocabularies read
 *   them), and verification [open question V].
 *
 * Each caller passes its own moment: now for what a screen or an export
 * shows as current, a visit's or a report's date where the question is
 * about that date. Now is the site's clock (SiteClock, the time-zone
 * decision). The SQL forms bind the same moment, so a query and the PHP
 * check on the rows it returns can't disagree.
 *
 * Insurance applies the same rule through its own day (CoverageDay and the
 * coverage rule, both DATE columns): see Day::fromStored().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

use DateTimeInterface;

final class InForce
{
    private const ZERO_DATETIME = '0000-00-00 00:00:00';
    private const ZERO_DATE = '0000-00-00';

    // ---- issues (lists): allergies, list medications, problems --------------------------------------

    /**
     * Whether an issue row is in force at a moment.
     *
     * @param array<mixed> $row a lists row (or what a service built from one) with activity and enddate
     */
    public static function issue(array $row, DateTimeInterface $at): bool
    {
        return !self::issueWithdrawn($row) && !self::issueEnded($row, $at);
    }

    /**
     * Whether an issue is withdrawn: activity 0 (as an int or a string). NULL is not withdrawn.
     *
     * @param array<mixed> $row
     */
    public static function issueWithdrawn(array $row): bool
    {
        $activity = $row['activity'] ?? null;
        return (is_int($activity) || is_string($activity)) && trim((string)$activity) === '0';
    }

    /**
     * Whether an issue's end has passed at a moment: its DATETIME end is before it (the end instant itself
     * is still in force). A blank end is none.
     *
     * @param array<mixed> $row
     */
    public static function issueEnded(array $row, DateTimeInterface $at): bool
    {
        $end = self::storedDateTime($row['enddate'] ?? null);
        return $end !== null && strcmp($end, $at->format('Y-m-d H:i:s')) < 0;
    }

    /**
     * The rule as SQL over a lists alias: one placeholder, bound by issueBinds().
     *
     * @param string $alias a table alias or name with no dot ('' for none), written into the SQL as given
     */
    public static function issueSql(string $alias = ''): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        return "(({$a}`activity` IS NULL OR {$a}`activity` <> 0) AND " . self::issueNotEndedSql($alias) . ")";
    }

    /**
     * The end part alone, for a reader that keeps its own withdrawn test: one placeholder.
     */
    public static function issueNotEndedSql(string $alias = ''): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        return "({$a}`enddate` IS NULL OR {$a}`enddate` = '" . self::ZERO_DATETIME . "' OR {$a}`enddate` >= ?)";
    }

    /**
     * @return list<string> the moment, as the site's wall clock a DATETIME column holds
     */
    public static function issueBinds(DateTimeInterface $at): array
    {
        return [$at->format('Y-m-d H:i:s')];
    }

    /**
     * The moment as a caller already holds it in stored form (a visit's DATETIME, a rules engine's target),
     * bound as given, so a value that isn't a datetime compares as it always did.
     *
     * @return list<mixed>
     */
    public static function issueBindsStored(mixed $moment): array
    {
        return [$moment];
    }

    /**
     * The end part with the moment written in, for SQL where no value can be bound at that place (a
     * computed column that a later WHERE filters on). The moment is formatted here from a DateTime, digits
     * and separators only, so nothing typed reaches the statement.
     */
    public static function issueNotEndedSqlAt(string $alias, DateTimeInterface $at): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        $moment = $at->format('Y-m-d H:i:s');
        return "({$a}`enddate` IS NULL OR {$a}`enddate` = '" . self::ZERO_DATETIME . "' OR {$a}`enddate` >= '$moment')";
    }

    // ---- prescriptions ------------------------------------------------------------------------------

    /**
     * Whether a prescription row is in force on a day: active = 1 and its end date, if any, after the day.
     *
     * @param array<mixed> $row a prescriptions row with active and end_date
     */
    public static function prescription(array $row, Day $on): bool
    {
        return !self::prescriptionWithdrawn($row) && !self::prescriptionEnded($row, $on);
    }

    /**
     * @param array<mixed> $row
     */
    public static function prescriptionWithdrawn(array $row): bool
    {
        $active = $row['active'] ?? null;
        return !((is_int($active) || is_string($active)) && trim((string)$active) === '1');
    }

    /**
     * Whether a prescription's DATE end is on or before the day (the end day is the day it stopped).
     *
     * @param array<mixed> $row
     */
    public static function prescriptionEnded(array $row, Day $on): bool
    {
        $end = self::storedDate($row['end_date'] ?? null);
        return $end !== null && strcmp($end, $on->iso()) <= 0;
    }

    /**
     * The rule as SQL over a prescriptions alias: one placeholder, bound by prescriptionBinds().
     */
    public static function prescriptionSql(string $alias = ''): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        return "({$a}`active` = 1 AND " . self::prescriptionNotEndedSql($alias) . ")";
    }

    /**
     * The end part alone: one placeholder.
     */
    public static function prescriptionNotEndedSql(string $alias = ''): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        return "({$a}`end_date` IS NULL OR {$a}`end_date` = '" . self::ZERO_DATE . "' OR {$a}`end_date` > ?)";
    }

    /**
     * @return list<string> the day, as a DATE column holds it
     */
    public static function prescriptionBinds(Day $on): array
    {
        return [$on->iso()];
    }

    /**
     * The end part with the day written in (see issueNotEndedSqlAt()): a Day formats as digits and dashes.
     */
    public static function prescriptionNotEndedSqlOn(string $alias, Day $on): string
    {
        $a = $alias === '' ? '' : "`$alias`.";
        return "({$a}`end_date` IS NULL OR {$a}`end_date` = '" . self::ZERO_DATE . "' OR {$a}`end_date` > '" . $on->iso() . "')";
    }

    // ---- stored values, read as SQL compares them ---------------------------------------------------

    /**
     * Whether a stored end date is an end at all: not NULL, '' or zero.
     */
    public static function hasEnd(mixed $value): bool
    {
        return self::storedDateTime($value) !== null;
    }

    /**
     * A stored DATETIME as SQL compares it ('Y-m-d H:i:s' text; a DATE-only value at midnight), or null
     * when it is blank (NULL, '' or zero). A zero day or month (2026-05-00) is a stored value and
     * compares as text, as SQL compares it.
     */
    private static function storedDateTime(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || $value === self::ZERO_DATETIME || $value === self::ZERO_DATE) {
            return null;
        }
        return strlen($value) === 10 ? $value . ' 00:00:00' : substr($value, 0, 19);
    }

    /**
     * A stored DATE as SQL compares it ('Y-m-d'), or null when it is blank or zero.
     */
    private static function storedDate(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || $value === self::ZERO_DATE || $value === self::ZERO_DATETIME) {
            return null;
        }
        return substr($value, 0, 10);
    }
}
