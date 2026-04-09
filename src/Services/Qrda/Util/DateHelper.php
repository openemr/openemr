<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\Services\Qrda\Util;

class DateHelper
{
    /**
     * For the JSON that gets passed to cqm-execution, this is the datetime format
     */
    public static function format_datetime_cqm(?string $datetime): ?string
    {
        return !empty($datetime) ? date('Y-m-d\TH:i:s', strtotime($datetime)) . ".000+00:00" : null;
    }

    /**
     * For QRDA XML exports, this is the datetime format
     */
    public static function format_datetime(?string $datetime): ?string
    {
        return !empty($datetime) ? date('YmdHis', strtotime($datetime)) : null;
    }

    public static function format_date(string $date): string
    {
        return date('Ymd', strtotime($date));
    }
}
