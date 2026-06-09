<?php

/**
 * Static narrowing helpers for the PostCalendar legacy entry-point
 * files (pnuserapi.php, pnuser.php, pnadmin.php).
 *
 * Those files extract their inputs from $args via extract() and from
 * pnVarCleanFromInput(), both of which yield mixed-typed values.
 * Before passing those to the strict-typed CalendarRenderDataBuilder
 * methods (or echoing them into URLs / template paths), they have to
 * be narrowed to the typed shapes the consumer expects. Doing the
 * narrowing in one place avoids duplicating the same is_array /
 * is_string boilerplate at every call site and keeps the narrowing
 * versioned alongside the builder it serves.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar;

final class LegacyInputNarrowing
{
    /**
     * Narrow a mixed value to a list of associative-array rows. Drops
     * non-array entries; re-keys preserved keys to strings.
     *
     * @return list<array<string, mixed>>
     */
    public static function rowList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $result = [];
        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }
            $assoc = [];
            foreach ($row as $k => $v) {
                $assoc[(string) $k] = $v;
            }
            $result[] = $assoc;
        }
        return $result;
    }

    /**
     * Narrow a mixed value to a date-keyed map of event-row lists
     * (the shape build*RenderData()'s $aEvents parameter expects).
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function dateEvents(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $result = [];
        foreach ($value as $date => $events) {
            $result[(string) $date] = self::rowList($events);
        }
        return $result;
    }

    /**
     * Narrow a mixed value to the time-rows shape the timed-view
     * builders (day/week-screen, day-print) expect.
     *
     * @return list<array{hour: int|string, minute: int|string, mer?: string}>
     */
    public static function timeRows(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $result = [];
        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }
            $hour = $row['hour'] ?? 0;
            $minute = $row['minute'] ?? 0;
            $entry = [
                'hour'   => is_int($hour) || is_string($hour) ? $hour : 0,
                'minute' => is_int($minute) || is_string($minute) ? $minute : 0,
            ];
            $mer = $row['mer'] ?? null;
            if (is_string($mer)) {
                $entry['mer'] = $mer;
            }
            $result[] = $entry;
        }
        return $result;
    }

    /**
     * Narrow a mixed value to a string, with an optional default for
     * non-string inputs.
     */
    public static function stringValue(mixed $value, string $default = ''): string
    {
        return is_string($value) ? $value : $default;
    }

    /**
     * Narrow a mixed value to a list of strings, dropping non-strings.
     *
     * @return list<string>
     */
    public static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $result = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Narrow a mixed value to an int, with an optional default for
     * inputs that aren't int|string-coerceable. Non-empty strings get
     * the PHP `(int)` cast — "12abc" becomes 12, matching the
     * truncating behavior the legacy entry points rely on.
     */
    public static function intValue(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            return (int) $value;
        }
        return $default;
    }
}
