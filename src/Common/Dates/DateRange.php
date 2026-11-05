<?php

/**
 * The one form for "rows dated from day F through day T" over a DATE or
 * DATETIME column: col >= F 00:00:00 AND col < (T + 1 day) 00:00:00.
 *
 * A report's "to" date is a day, and most columns it filters hold a time.
 * col <= '2026-11-04' compares with midnight at the start of the 4th, so
 * anything later that day falls out. The half-open form keeps the last day
 * whole and still lets the database use an index on the column, which is
 * why daily_summary_report.php already writes it this way.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

final class DateRange
{
    /**
     * From day F through day T, both included.
     *
     * @param string $column a column reference, written into the SQL as given (callers pass literals)
     */
    public static function days(string $column, Day $first, Day $last): SqlCondition
    {
        // The bounds as the column stores wall-clock time, built from the days (not through a time zone, where a
        // daylight-saving change at midnight would move 00:00 to 01:00).
        return new SqlCondition("$column >= ? AND $column < ?", [$first->storedStart(), $last->plusDays(1)->storedStart()]);
    }

    /**
     * The end side alone: rows through day T, when the bound is a day (YYYY-M-D, as DateToYYYYMMDD
     * returns it). A bound that isn't a day (a datetime, blank, NULL, or what a mistyped date became) keeps
     * the comparison it had, col <= the value, so nothing but the last day's later rows changes; so does
     * 9999-12-31, whose next day a DATETIME can't hold.
     *
     * @param string $column a column reference, written into the SQL as given (callers pass literals)
     */
    public static function through(string $column, mixed $lastDay): SqlCondition
    {
        $iso = is_string($lastDay) ? DateInput::iso($lastDay) : null;
        $day = $iso === null ? null : Day::fromIso($iso);
        if ($day === null || $day->year >= 9999 && $day->month === 12 && $day->day === 31) {
            return new SqlCondition("$column <= ?", [self::asBound($lastDay)]);
        }
        return new SqlCondition("$column < ?", [$day->plusDays(1)->storedStart()]);
    }

    /**
     * A whole range when both sides are days; otherwise each side as through() and a plain
     * col >= value keep it (the first day's midnight is the value itself).
     */
    public static function between(string $column, mixed $firstDay, mixed $lastDay): SqlCondition
    {
        $end = self::through($column, $lastDay);
        return new SqlCondition("$column >= ? AND " . $end->sql, [self::asBound($firstDay), ...$end->binds]);
    }

    /**
     * A bound as the caller passed it: a string or number as text, anything else NULL (which matches nothing,
     * as it did when the caller bound it).
     */
    private static function asBound(mixed $value): ?string
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string)$value : null;
    }
}
