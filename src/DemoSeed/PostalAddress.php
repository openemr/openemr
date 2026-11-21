<?php

/**
 * A street address. `state` is a list `state` option id (e.g. MA).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed;

use OpenEMR\DemoSeed\SeedFile\MapReader;

final readonly class PostalAddress
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $postalCode,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['street', 'city', 'state', 'postal_code']);
        return new self(
            $map->string('street'),
            $map->string('city'),
            $map->string('state'),
            $map->string('postal_code'),
        );
    }

    public function withStreet(string $street): self
    {
        return new self($street, $this->city, $this->state, $this->postalCode);
    }
}
