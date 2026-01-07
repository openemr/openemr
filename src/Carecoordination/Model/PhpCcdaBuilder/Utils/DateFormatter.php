<?php

/**
 * DateFormatter.php - Date Formatting Utilities for CCDA
 *
 * PHP equivalent of utils/date/date.js from serveccda.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Utils;

use DateTime;
use DateTimeZone;

class DateFormatter
{
    /**
     * Format date for CCDA (equivalent to fDate in serveccda.js)
     *
     * Formats a date string to CCDA-compliant format: YYYYMMDDHHMM+/-ZZZZ (no seconds)
     *
     * @param string|null $dateStr The input date string
     * @param bool $includeTime Whether to include time component
     * @return string Formatted date string or empty string if invalid
     */
    public static function fDate(?string $dateStr, bool $includeTime = true): string
    {
        if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
            // Return current datetime if empty
            return self::formatNow($includeTime);
        }

        try {
            // Parse the date
            $date = new DateTime($dateStr);

            if ($includeTime) {
                // Full datetime with timezone: YYYYMMDDHHMM+0000 (NO SECONDS per CCDA spec)
                return $date->format('YmdHi') . self::formatTimezoneOffset($date);
            } else {
                // Date only: YYYYMMDD
                return $date->format('Ymd');
            }
        } catch (\Exception) {
            // If parsing fails, try to salvage what we can
            return self::attemptPartialParse($dateStr, $includeTime);
        }
    }

    /**
     * Format current datetime
     */
    private static function formatNow(bool $includeTime): string
    {
        $now = new DateTime();
        if ($includeTime) {
            // YYYYMMDDHHMM+ZZZZ (no seconds)
            return $now->format('YmdHi') . self::formatTimezoneOffset($now);
        }
        return $now->format('Ymd');
    }

    /**
     * Format timezone offset for CCDA
     */
    private static function formatTimezoneOffset(DateTime $date): string
    {
        $offset = $date->getOffset();
        $hours = intval($offset / 3600);
        $minutes = abs(intval(($offset % 3600) / 60));

        return sprintf('%+03d%02d', $hours, $minutes);
    }

    /**
     * Attempt to parse partial date strings
     */
    private static function attemptPartialParse(string $dateStr, bool $includeTime): string
    {
        // Try various formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'm/d/Y H:i:s',
            'm/d/Y',
            'd/m/Y',
            'Ymd',
            'YmdHis',
            'YmdHi',
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                if ($includeTime) {
                    // YYYYMMDDHHMM+ZZZZ (no seconds)
                    return $date->format('YmdHi') . self::formatTimezoneOffset($date);
                }
                return $date->format('Ymd');
            }
        }

        // Last resort - return empty
        return '';
    }

    /**
     * Format date for template display (equivalent to templateDate)
     *
     * @param string|null $dateStr The input date string
     * @param string $precision The precision level (day, month, year, tz)
     * @return array Date array with 'date' and 'precision' keys
     */
    public static function templateDate(?string $dateStr, string $precision = 'tz'): array
    {
        $includeTime = ($precision === 'tz' || $precision === 'time');

        return [
            'date' => self::fDate($dateStr, $includeTime),
            'precision' => $precision,
        ];
    }

    /**
     * Parse a CCDA-formatted date back to DateTime
     *
     * @param string $ccdaDate CCDA formatted date (YYYYMMDDHHmmss+ZZZZ)
     * @return DateTime|null
     */
    public static function parseCcdaDate(string $ccdaDate): ?DateTime
    {
        // Remove any whitespace
        $ccdaDate = trim($ccdaDate);

        if (empty($ccdaDate)) {
            return null;
        }

        // Try to parse with timezone
        $date = DateTime::createFromFormat('YmdHisO', $ccdaDate);
        if ($date !== false) {
            return $date;
        }

        // Try without timezone
        $date = DateTime::createFromFormat('YmdHis', substr($ccdaDate, 0, 14));
        if ($date !== false) {
            return $date;
        }

        // Try date only
        $date = DateTime::createFromFormat('Ymd', substr($ccdaDate, 0, 8));
        if ($date !== false) {
            return $date;
        }

        return null;
    }
}
