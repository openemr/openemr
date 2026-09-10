<?php

/**
 * Clinic schedule hour window helpers for available-appointment search.
 *
 * Mirrors Admin -> Config -> Calendar schedule_start / schedule_end handling
 * used by the day/week calendar grid: ending hour is exclusive for slot starts
 * (Ending Hour 5 PM allows starts before 17:00, e.g. last 30-min start is 4:30).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class ConfiguredScheduleHours
{
    /**
     * Normalize configured schedule hours to a valid [start, end) window.
     *
     * @return array{0: int, 1: int}
     */
    public static function normalizeWindow(int $scheduleStartHour, int $scheduleEndHour): array
    {
        if ($scheduleStartHour < 0 || $scheduleStartHour > 23) {
            $scheduleStartHour = 8;
        }
        if ($scheduleEndHour < 1 || $scheduleEndHour > 24) {
            $scheduleEndHour = 17;
        }
        if ($scheduleEndHour <= $scheduleStartHour) {
            $scheduleEndHour = min(24, $scheduleStartHour + 1);
        }

        return [$scheduleStartHour, $scheduleEndHour];
    }

    /**
     * True when the slot start is within configured clinic hours and the
     * appointment duration finishes on or before schedule_end.
     */
    public static function containsSlot(
        int $utime,
        int $durationSlots,
        int $slotsecs,
        int $scheduleStartHour,
        int $scheduleEndHour
    ): bool {
        [$scheduleStartHour, $scheduleEndHour] = self::normalizeWindow($scheduleStartHour, $scheduleEndHour);

        $minuteOfDay = ((int) date('G', $utime) * 60) + (int) date('i', $utime);
        $windowStartMinute = $scheduleStartHour * 60;
        $windowEndMinute = $scheduleEndHour * 60;
        if ($minuteOfDay < $windowStartMinute || $minuteOfDay >= $windowEndMinute) {
            return false;
        }

        // Duration must also finish by ending hour (exclusive end boundary).
        $endMinuteOfDay = $minuteOfDay + max(1, $durationSlots) * max(1, (int) ($slotsecs / 60));

        return $endMinuteOfDay <= $windowEndMinute;
    }
}
