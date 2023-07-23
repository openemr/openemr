<?php

/**
 * Formatting utility class for dates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Utils;

use DateTime;

class DateFormatterUtils
{
    public static function DateToYYYYMMDD($DateValue)
    {
        //With the help of function DateFormatRead() now the user can enter date is any of the 3 formats depending upon the global setting.
        //But in database the date can be stored only in the yyyy-mm-dd format.
        //This function accepts a date in any of the 3 formats, and as per the global setting, converts it to the yyyy-mm-dd format.
        if (trim($DateValue ?? '') == '') {
            return '';
        }

        if ($GLOBALS['date_display_format'] == 0) {
            return $DateValue;
        } elseif ($GLOBALS['date_display_format'] == 1 || $GLOBALS['date_display_format'] == 2) {
            $DateValueArray = explode('/', $DateValue);
            if ($GLOBALS['date_display_format'] == 1) {
                return $DateValueArray[2] . '-' . $DateValueArray[0] . '-' . $DateValueArray[1];
            }

            if ($GLOBALS['date_display_format'] == 2) {
                return $DateValueArray[2] . '-' . $DateValueArray[1] . '-' . $DateValueArray[0];
            }
        }
    }

    /**
     * Given a date string it will return a DateTime object using the global date format.  If the date could not be parsed
     * then it returns false.  The DateTime must be in the format of Y-m-d, d/m/Y, or m/d/Y depending on the global settings
     * to be parsed correct.  If an empty string is passed in then the current date is returned as a DateTime object.
     * @param string $DateValue
     * @return bool|DateTime false if the date could not be parsed
     */
    public static function dateStringToDateTime(string $DateValue)
    {
        $dateTime = new DateTime();
        //With the help of function DateFormatRead() now the user can enter date is any of the 3 formats depending upon the global setting.
        //But in database the date can be stored only in the yyyy-mm-dd format.
        //This function accepts a date in any of the 3 formats, and as per the global setting, converts it to the yyyy-mm-dd format.
        $timeFormat = '';
        if (strpos($DateValue, ":") !== false) {
            $timeFormat = " H:i:s";
        }
        if (trim($DateValue ?? '') == '') {
            $dateTime = new DateTime();
        } else if ($GLOBALS['date_display_format'] == 0) {
            $dateTime = \DateTime::createFromFormat("Y-m-d$timeFormat", $DateValue);
        } elseif ($GLOBALS['date_display_format'] == 1 || $GLOBALS['date_display_format'] == 2) {
            if ($GLOBALS['date_display_format'] == 1) {
                $dateTime = \DateTime::createFromFormat("m/d/Y$timeFormat", $DateValue);
            }

            if ($GLOBALS['date_display_format'] == 2) {
                $dateTime = \DateTime::createFromFormat("d/m/Y$timeFormat", $DateValue);
            }
        }
        return $dateTime;
    }

    public static function getShortDateFormat($showYear = true)
    {
        if ($GLOBALS['date_display_format'] == 0) { // $GLOBALS['date_display_format'] == 0
            return 'Y-m-d';
        } elseif ($GLOBALS['date_display_format'] == 1) {
            return 'm/d/Y';
        } elseif ($GLOBALS['date_display_format'] == 2) { // dd/mm/yyyy, note year is added below
            return 'd/m/Y';
        }
    }

    public static function oeFormatShortDate($date = 'today', $showYear = true)
    {
        if ($date === 'today') {
            $date = date('Y-m-d');
        }

        if (strlen($date ?? '') >= 10) {
            // assume input is yyyy-mm-dd
            if ($GLOBALS['date_display_format'] == 1) {      // mm/dd/yyyy, note year is added below
                $newDate = substr($date, 5, 2) . '/' . substr($date, 8, 2);
            } elseif ($GLOBALS['date_display_format'] == 2) { // dd/mm/yyyy, note year is added below
                $newDate = substr($date, 8, 2) . '/' . substr($date, 5, 2);
            }

            // process the year (add for formats 1 and 2; remove for format 0)
            if ($GLOBALS['date_display_format'] == 1 || $GLOBALS['date_display_format'] == 2) {
                if ($showYear) {
                    $newDate .= '/' . substr($date, 0, 4);
                }
            } elseif (!$showYear) { // $GLOBALS['date_display_format'] == 0
                // need to remove the year
                $newDate = substr($date, 5, 2) . '-' . substr($date, 8, 2);
            } else { // $GLOBALS['date_display_format'] == 0
                // keep the year (so will simply be the original $date)
                $newDate = substr($date, 0, 10);
            }

            return $newDate;
        }

        // this is case if the $date does not have 10 characters
        return $date;
    }

    public static function getTimeFormat($seconds = false)
    {
        $format = $GLOBALS['time_display_format'] ?? 0;

        if ($format == 1) {
            if ($seconds) {
                $formatted = "g:i:s a";
            } else {
                $formatted = "g:i a";
            }
        } else { // ($format == 0)
            if ($seconds) {
                $formatted = "H:i:s";
            } else {
                $formatted = "H:i";
            }
        }
        return $formatted;
    }
}
