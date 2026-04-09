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

class Interval implements JsonSerializable
{
    public function __construct(
        public ?DateTime $low = null,
        public ?DateTime $high = null,
        public ?bool $lowClosed = null,
        public ?bool $highClosed = null,
    ) {
    }

    /**
     * @return array{low: ?string, high: ?string, lowClosed: ?bool, highClosed: ?bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'low' => $this->low?->jsonSerialize(),
            'high' => $this->high?->jsonSerialize(),
            'lowClosed' => $this->lowClosed,
            'highClosed' => $this->highClosed,
        ];
    }
}
