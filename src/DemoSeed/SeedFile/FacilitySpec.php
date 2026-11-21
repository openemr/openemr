<?php

/**
 * A facility to create (`facilities[]`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

use OpenEMR\DemoSeed\PostalAddress;

final readonly class FacilitySpec
{
    public function __construct(
        public string $key,
        public string $name,
        public PostalAddress $address,
        public ?string $phone,
        public ?string $fax,
        public ?string $npi,
        public ?string $taxId,
        public ?int $posCode,
        public bool $serviceLocation,
        public bool $billingLocation,
        public ?string $color,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys([
            'key', 'name', 'street', 'city', 'state', 'postal_code', 'phone', 'fax', 'npi',
            'tax_id', 'pos_code', 'service_location', 'billing_location', 'color',
        ]);
        $color = $map->optionalString('color');
        if ($color !== null && preg_match('/^#[0-9a-f]{6}$/i', $color) !== 1) {
            throw SeedFileException::at($map->pathOf('color'), 'must be a color like "#99CCFF"');
        }
        $posCode = $map->optionalInt('pos_code');
        if ($posCode !== null && ($posCode < 1 || $posCode > 99)) {
            throw SeedFileException::at($map->pathOf('pos_code'), 'must be a place-of-service code from 1 to 99');
        }
        return new self(
            key: $map->string('key'),
            name: $map->string('name'),
            address: new PostalAddress(
                $map->string('street'),
                $map->string('city'),
                $lists->add(ListReference::STATE, $map->string('state'), $map->pathOf('state')),
                $map->string('postal_code'),
            ),
            phone: $map->optionalString('phone'),
            fax: $map->optionalString('fax'),
            npi: $map->has('npi') ? Npi::parse($map, 'npi') : null,
            taxId: $map->optionalString('tax_id'),
            posCode: $posCode,
            serviceLocation: $map->optionalBool('service_location', true),
            billingLocation: $map->optionalBool('billing_location'),
            color: $color,
        );
    }
}
