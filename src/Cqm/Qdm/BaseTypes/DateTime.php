<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm\BaseTypes;


use OpenEMR\Services\Qrda\Util\DateHelper;

class DateTime extends AbstractType implements \JsonSerializable
{
    public $date;

    public function jsonSerialize(): mixed
    {
        $formatted = DateHelper::format_datetime_cqm($this->date);
        return $formatted;
    }
}
