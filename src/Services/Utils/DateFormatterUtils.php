<?php

/**
 * Formatting utility class for dates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2010-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Utils;

use DateTime;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class DateFormatterUtils
{
    /**
     * Date display format constants
     */
    public const DATE_FORMAT_ISO = 0;      // Y-m-d (ISO 8601)
    public const DATE_FORMAT_US = 1;       // m/d/Y (US format)
    public const DATE_FORMAT_INTL = 2;     // d/m/Y (International format)

    /**
     * Time display format constants
     */
    public const TIME_FORMAT_24HR = 0;
    public const TIME_FORMAT_12HR = 1;

    public static function isNotEmptyDateTimeString(?string $dateString): bool
    {
        return $dateString !== null
            && $dateString !== ''
            && $dateString !== '0000-00-00 00:00:00'
            && $dateString !== '1970-01-01 00:00:00';
    }

    /**
     * Convert a date from the configured display format to YYYY-MM-DD (ISO 8601).
     *
     * @param mixed $DateValue The date string in the configured format
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function DateToYYYYMMDD($DateValue, ?ParameterBag $globals = null)
    {
        if (trim($DateValue ?? '') === '') {
            return '';
        }

        $globals ??= OEGlobalsBag::getInstance();
        $dateFormat = $globals->getInt('date_display_format', 0);

        if ($dateFormat === self::DATE_FORMAT_ISO) {
            return $DateValue;
        }

        if ($dateFormat === self::DATE_FORMAT_US || $dateFormat === self::DATE_FORMAT_INTL) {
            $DateValueArray = explode('/', (string) $DateValue);
            if ($dateFormat === self::DATE_FORMAT_US) {
                return $DateValueArray[2] . '-' . $DateValueArray[0] . '-' . $DateValueArray[1];
            }

            return $DateValueArray[2] . '-' . $DateValueArray[1] . '-' . $DateValueArray[0];
        }

        return '';
    }

    /**
     * Given a date string it will return a DateTime object using the global date format.
     *
     * If the date could not be parsed then it returns false. The DateTime must be in the
     * format of Y-m-d, d/m/Y, or m/d/Y depending on the global settings to be parsed correctly.
     * If an empty string is passed in then the current date is returned as a DateTime object.
     *
     * @param string $DateValue
     * @param bool $includeSeconds Whether seconds are included in the time portion
     * @param ?ParameterBag $globals Optional globals bag for testing
     * @return bool|DateTime false if the date could not be parsed
     */
    public static function dateStringToDateTime(string $DateValue, bool $includeSeconds = false, ?ParameterBag $globals = null)
    {
        $globals ??= OEGlobalsBag::getInstance();
        $dateFormat = $globals->getInt('date_display_format', 0);

        $timeFormat = '';
        if (str_contains($DateValue, ":")) {
            $timeFormat = " " . self::getTimeFormat($includeSeconds, $globals);
        }

        if (trim($DateValue) === '') {
            return new DateTime();
        }

        if ($dateFormat === self::DATE_FORMAT_ISO) {
            return DateTime::createFromFormat("Y-m-d$timeFormat", $DateValue);
        }

        if ($dateFormat === self::DATE_FORMAT_US) {
            return DateTime::createFromFormat("m/d/Y$timeFormat", $DateValue);
        }

        if ($dateFormat === self::DATE_FORMAT_INTL) {
            return DateTime::createFromFormat("d/m/Y$timeFormat", $DateValue);
        }

        return new DateTime();
    }

    /**
     * Get the PHP date format string for the configured date display format.
     *
     * @param bool $showYear Whether to include the year (currently unused but kept for compatibility)
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function getShortDateFormat($showYear = true, ?ParameterBag $globals = null): string
    {
        $globals ??= OEGlobalsBag::getInstance();
        $dateFormat = $globals->getInt('date_display_format', 0);

        return match ($dateFormat) {
            self::DATE_FORMAT_US => 'm/d/Y',
            self::DATE_FORMAT_INTL => 'd/m/Y',
            default => 'Y-m-d',
        };
    }

    /**
     * Format a time string according to the configured time format.
     *
     * @param mixed $time The time to format
     * @param mixed $format "global" to use configured format, or 0 (24hr) / 1 (12hr)
     * @param bool $seconds Whether to include seconds
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function oeFormatTime($time, $format = "global", $seconds = false, ?ParameterBag $globals = null): string
    {
        if ($time === null || $time === '') {
            return "";
        }

        $globals ??= OEGlobalsBag::getInstance();

        if ($format === "global") {
            $format = $globals->getInt('time_display_format', 0);
        }

        if ($format == self::TIME_FORMAT_12HR) {
            return $seconds ? date("g:i:s a", strtotime((string) $time)) : date("g:i a", strtotime((string) $time));
        }

        // Default: 24hr format
        return $seconds ? date("H:i:s", strtotime((string) $time)) : date("H:i", strtotime((string) $time));
    }

    /**
     * Returns the complete formatted datetime string according the global date and time format.
     *
     * @param mixed $datetime The datetime string in Y-m-d H:i:s format
     * @param mixed $formatTime "global" to use configured format, or 0 (24hr) / 1 (12hr)
     * @param bool $seconds Whether to include seconds
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function oeFormatDateTime($datetime, $formatTime = "global", $seconds = false, ?ParameterBag $globals = null): string
    {
        return self::oeFormatShortDate(substr($datetime ?? '', 0, 10), true, $globals)
            . " "
            . self::oeFormatTime(substr($datetime ?? '', 11), $formatTime, $seconds, $globals);
    }

    /**
     * Format a date string for display according to the configured date format.
     *
     * @param mixed $date The date in Y-m-d format, or 'today' for current date
     * @param bool $showYear Whether to include the year in the output
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function oeFormatShortDate($date = 'today', $showYear = true, ?ParameterBag $globals = null)
    {
        if ($date === 'today') {
            $date = date('Y-m-d');
        }

        if (strlen($date ?? '') < 10) {
            return $date;
        }

        $globals ??= OEGlobalsBag::getInstance();
        $dateFormat = $globals->getInt('date_display_format', 0);

        // Input is assumed to be yyyy-mm-dd
        $year = substr((string) $date, 0, 4);
        $month = substr((string) $date, 5, 2);
        $day = substr((string) $date, 8, 2);

        if ($dateFormat === self::DATE_FORMAT_US) {
            return $showYear ? "$month/$day/$year" : "$month/$day";
        }

        if ($dateFormat === self::DATE_FORMAT_INTL) {
            return $showYear ? "$day/$month/$year" : "$day/$month";
        }

        // Default: ISO format (Y-m-d)
        return $showYear ? "$year-$month-$day" : "$month-$day";
    }

    /**
     * Get the PHP time format string for the configured time display format.
     *
     * @param bool $seconds Whether to include seconds in the format
     * @param ?ParameterBag $globals Optional globals bag for testing
     */
    public static function getTimeFormat($seconds = false, ?ParameterBag $globals = null): string
    {
        $globals ??= OEGlobalsBag::getInstance();
        $format = $globals->getInt('time_display_format', 0);

        if ($format == self::TIME_FORMAT_12HR) {
            return $seconds ? "g:i:s a" : "g:i a";
        }

        // Default: 24hr format
        return $seconds ? "H:i:s" : "H:i";
    }

    /**
     * Format a DateTime object as ISO 8601 with milliseconds.
     */
    public static function getFormattedISO8601DateFromDateTime(\DateTime $dateTime): string
    {
        // ISO8601 doesn't support fractional dates so we need to change from microseconds to milliseconds
        return substr($dateTime->format('Y-m-d\TH:i:s.u'), 0, -3) . $dateTime->format('P');
    }
}
