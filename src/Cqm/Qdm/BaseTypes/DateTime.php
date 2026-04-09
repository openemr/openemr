<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Qdm\BaseTypes;

use JsonSerializable;
use OpenEMR\Services\Qrda\Util\DateHelper;

class DateTime implements JsonSerializable
{
    public function __construct(
        public ?string $date = null,
    ) {
    }

    public function jsonSerialize(): ?string
    {
        $formatted = DateHelper::format_datetime_cqm($this->date);
        return $formatted === false ? null : $formatted;
    }
}
