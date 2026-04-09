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

class Address implements JsonSerializable
{
    /**
     * @param list<string> $street
     */
    public function __construct(
        public string $use = 'HP',
        public array $street = [],
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip = null,
        public ?string $country = null,
    ) {
    }

    /**
     * @return array{use: string, street: list<string>, city: ?string, state: ?string, zip: ?string, country: ?string}
     */
    public function jsonSerialize(): array
    {
        return [
            'use' => $this->use,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ];
    }
}
