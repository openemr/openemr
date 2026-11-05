<?php

/**
 * One conversion for a date a person or a script typed, which refuses what
 * it can't parse instead of passing it on mangled.
 *
 * Sites show dates in one of three formats (Y-m-d, m/d/Y, d/m/Y). A field
 * can still hold ISO: the issue form's Active checkbox and Resolved outcome
 * type today's date as YYYY-M-D whatever the format. ISO is unambiguous
 * (four digits, then dashes), so it is accepted on every site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

final class DateInput
{
    public const FORMAT_ISO = 0;   // Y-m-d
    public const FORMAT_US = 1;    // m/d/Y
    public const FORMAT_INTL = 2;  // d/m/Y

    /**
     * An ISO date as typed: YYYY-M-D, with one or two digits for the month and the day, a real day.
     * Returns it as YYYY-MM-DD, or null.
     */
    public static function iso(string $typed): ?string
    {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $typed, $m) !== 1) {
            return null;
        }
        return self::day((int)$m[1], (int)$m[2], (int)$m[3]);
    }

    /**
     * A date typed in the site's display format, or in ISO on any site, as YYYY-MM-DD; null when it is
     * neither (blank included).
     */
    public static function toIso(string $typed, int $displayFormat): ?string
    {
        $typed = trim($typed);
        $iso = self::iso($typed);
        if ($iso !== null || $displayFormat === self::FORMAT_ISO) {
            return $iso;
        }
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $typed, $m) !== 1) {
            return null;
        }
        return $displayFormat === self::FORMAT_US
            ? self::day((int)$m[3], (int)$m[1], (int)$m[2])
            : ($displayFormat === self::FORMAT_INTL ? self::day((int)$m[3], (int)$m[2], (int)$m[1]) : null);
    }

    private static function day(int $year, int $month, int $day): ?string
    {
        return $year >= 1 && checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
    }
}
