<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Model;

use DateTime;

class ApplicationTable
{
    /**
     * Converts a given format setting (or string) to a PHP date format.
     *
     * @param mixed $format 0, 1, 2, or a custom format string.
     * @return string        PHP date format string.
     */
    public static function dateFormat($format = null)
    {
        $map = [
            '0' => 'Y-m-d', // e.g. "1920-01-01"
            1 => 'm/d/Y', // e.g. "01/01/1920"
            2 => 'd/m/Y', // e.g. "01/01/1920"
            'yyyy-mm-dd' => 'Y-m-d',
            'mm/dd/yyyy' => 'm/d/Y',
            'dd/mm/yyyy' => 'd/m/Y',
        ];

        return $map[$format] ?? $format;
    }

    /*
    * Retrieve the data format from GLOBALS
    *
    * @param    Date format set in GLOBALS
    * @return   Date format in datepicker
    **/
    public static function datePickerFormat($format = null)
    {
        if ($format == "0") {
            $date_format = 'yy-mm-dd';
        } elseif ($format == 1) {
            $date_format = 'mm/dd/yy';
        } elseif ($format == 2) {
            $date_format = 'dd/mm/yy';
        } else {
            $date_format = $format;
        }

        return $date_format;
    }

    /**
     * Converts an input date from one format to another.
     *
     * @param string $input_date    The date to convert.
     * @param mixed  $output_format The desired output format (as defined by dateFormat).
     * @param mixed  $input_format  The format of the input date (as defined by dateFormat).
     *                              If null, the method will attempt to detect the format.
     * @return string|false         The formatted date or false if conversion fails.
     */
    public static function fixDate($input_date, $output_format = null, $input_format = null)
    {
        if (!$input_date) {
            return false;
        }

        $input_date = preg_replace('/[TZ]/', ' ', $input_date);
        $outputFormat = self::dateFormat($output_format);

        if ($input_format) {
            $inputFormat = self::dateFormat($input_format);
        } else {
            if (preg_match('/^\d{8}$/', (string) $input_date)) {
                $inputFormat = 'Ymd';
            } elseif (preg_match('/^\d{14}$/', (string) $input_date)) {
                $inputFormat = 'YmdHis';
            } else {
                $inputFormat = null;
            }
        }
        if ($inputFormat) {
            $dateObj = DateTime::createFromFormat($inputFormat, $input_date);
        } else {
            try {
                $dateObj = new DateTime($input_date);
            } catch (\Throwable) {
                return false;
            }
        }

        if (!$dateObj) {
            return false;
        }

        return $dateObj->format($outputFormat);
    }
}
