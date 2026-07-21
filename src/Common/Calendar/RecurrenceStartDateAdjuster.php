<?php

/**
 * Advance a calendar event's start date to the first matching weekday for
 * recurrence rules that target a specific day of the week.
 *
 * Postcalendar repeat types 5-9 map to Monday-Friday respectively. When a
 * user schedules an event of one of those types, the start date must land on
 * the target weekday so the recurrence calculations downstream produce the
 * expected sequence. Any other repeat type returns the date unchanged.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class RecurrenceStartDateAdjuster
{
    /**
     * Return $eventDate advanced forward to the next occurrence of the weekday
     * implied by $repeatType. Dates that already fall on the target weekday,
     * unrecognised repeat types, and unparsable dates are returned unchanged.
     */
    public static function adjust(string $eventDate, int $repeatType): string
    {
        $target = match ($repeatType) {
            5 => DayOfWeek::Monday,
            6 => DayOfWeek::Tuesday,
            7 => DayOfWeek::Wednesday,
            8 => DayOfWeek::Thursday,
            9 => DayOfWeek::Friday,
            default => null,
        };

        if ($target === null) {
            return $eventDate;
        }

        $baseTimestamp = strtotime($eventDate);
        if ($baseTimestamp === false) {
            return $eventDate;
        }

        // date('N') returns 1=Mon..7=Sun (ISO-8601). For Mon-Fri these values
        // coincide with the DayOfWeek enum's backing ints, so the comparison
        // is safe for the weekday cases handled above.
        $currentDay = (int) date('N', $baseTimestamp);
        if ($currentDay === $target->value) {
            return $eventDate;
        }

        // $daysUntilTarget is in 1..6 (from the modulo of two small ints),
        // and $baseTimestamp is already validated above, so strtotime() here
        // cannot fail and always returns an int.
        $daysUntilTarget = ($target->value - $currentDay + 7) % 7;
        $adjustedTimestamp = strtotime("+{$daysUntilTarget} days", $baseTimestamp);

        return date('Y-m-d', $adjustedTimestamp);
    }
}
