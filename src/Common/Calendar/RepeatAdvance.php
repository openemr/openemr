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
    public static function nextDate(mixed $day, mixed $month, mixed $year, mixed $frequency, mixed $repeatType, string $current): ?string
    {
        $next = __increment($day, $month, $year, $frequency, $repeatType);
        if (!is_string($next) || $next <= $current) {
            return null;
        }

        return $next;
    }

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

    public static function onDate(mixed $nth, mixed $dayOfWeek, mixed $month, mixed $year): ?string
    {
        $remaining = self::wholeNumber($nth);
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
