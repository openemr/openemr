<?php

/**
 * Steps a stored repeat forward.
 *
 * A step that does not move is null, so a calendar walk can stop
 * instead of spinning on that series.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class RepeatAdvance
{
    /**
     * The next date, or null when the step does not move forward.
     */
    public static function nextDate(mixed $day, mixed $month, mixed $year, mixed $frequency, mixed $repeatType, string $current): ?string
    {
        $next = __increment($day, $month, $year, $frequency, $repeatType);
        if (!is_string($next) || $next <= $current) {
            return null;
        }

        return $next;
    }

    /**
     * The next month, or null when the month does not advance.
     */
    public static function nextMonth(mixed $year, mixed $month, mixed $day, mixed $frequency, string $currentYearMonth): ?string
    {
        $yearInt = self::wholeNumber($year);
        $monthInt = self::wholeNumber($month);
        $dayInt = self::wholeNumber($day);
        $step = self::wholeNumber($frequency);
        if ($yearInt === null || $monthInt === null || $dayInt === null || $step === null) {
            return null;
        }

        $stamp = mktime(0, 0, 0, $monthInt + $step, $dayInt, $yearInt);
        if ($stamp === false) {
            return null;
        }

        $next = date('Y-m-d', $stamp);
        if (substr($next, 0, 7) <= $currentYearMonth) {
            return null;
        }

        return $next;
    }

    /**
     * The nth weekday of the month, or null when none is found.
     */
    public static function onDate(mixed $nth, mixed $dayOfWeek, mixed $month, mixed $year): ?string
    {
        $remaining = self::wholeNumber($nth);
        if (is_int($dayOfWeek)) {
            $dayOfWeek = (string) $dayOfWeek;
        }
        if (is_int($month)) {
            $month = (string) $month;
        }
        if (is_int($year)) {
            $year = (string) $year;
        }
        if ($remaining === null || !is_string($dayOfWeek) || !is_string($month) || !is_string($year)) {
            return null;
        }

        for ($tries = 0; $tries < 6 && $remaining >= 1; $tries++, $remaining--) {
            $found = \Date_Calc::NWeekdayOfMonth((string) $remaining, $dayOfWeek, $month, $year, '%Y-%m-%d');
            if (is_string($found)) {
                return $found;
            }
        }

        return null;
    }

    /**
     * A whole number, or null when the value is not one.
     */
    private static function wholeNumber(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?[0-9]+$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }
}
