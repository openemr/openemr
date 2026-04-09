<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\Cqm\Qdm\BaseTypes;

use JsonSerializable;

class Quantity implements JsonSerializable
{
    public function __construct(
        public int|float|null $value = null,
        public ?string $unit = null,
    ) {
    }

    /**
     * @return array{value: int|float|null, unit: ?string, _type: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit,
            '_type' => 'QDM::Quantity',
        ];
    }
}
