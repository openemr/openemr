<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Util;

class DateHelper
{
    /**
     * @param  $datetime
     * @return false|string
     *
     * For the JSON that gets passed to cqm-execution, this is the datetime format
     */
    public static function format_datetime_cqm($datetime)
    {
        return !empty($datetime) ? date('Y-m-d\TH:i:s', strtotime($datetime)) . ".000+00:00" : null;
    }

    /**
     * @param  $datetime
     * @return false|string
     *
     * For QRDA XML exports, this is the datetime format
     */
    public static function format_datetime($datetime)
    {
        return !empty($datetime) ? date('YmdHis', strtotime($datetime)) : null;
    }

    public static function format_date($date)
    {
        return date('Ymd', strtotime($date));
    }
}
