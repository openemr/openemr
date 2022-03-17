<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Util;

class DateHelper
{
    public static function format_datetime_gmdate($datetime)
    {
        return gmdate('Ymd\THis\Z', date('U', strtotime($datetime)));
    }
}
