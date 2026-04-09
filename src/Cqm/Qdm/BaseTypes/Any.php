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

class Any implements JsonSerializable
{
    public function __construct(
        public ?string $bundleId = null,
    ) {
    }

    /**
     * @return array{bundleId: ?string}
     */
    public function jsonSerialize(): array
    {
        return [
            'bundleId' => $this->bundleId,
        ];
    }
}
