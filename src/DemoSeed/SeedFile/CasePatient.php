<?php

/**
 * A patient a case claims (`cases[].patients[]`), and what it changes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CasePatient
{
    /**
     * @param ?string $lastName a new last name
     * @param ?string $lastNameSameAs key of an earlier patient in the case whose last name to take
     * @param ?string $addressSameAs key of an earlier patient in the case whose address to take
     */
    public function __construct(
        public string $key,
        public PatientCondition $pick,
        public ?string $maritalStatus,
        public ?string $providerKey,
        public ?string $lastName,
        public ?string $lastNameSameAs,
        public ?string $addressSameAs,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['key', 'pick', 'marital_status', 'provider', 'last_name', 'address']);

        $lastName = null;
        $lastNameSameAs = null;
        if ($map->has('last_name')) {
            if (is_array($map->raw('last_name'))) {
                $lastNameSameAs = self::sameAs($map->map('last_name'));
            } else {
                $lastName = $map->string('last_name');
            }
        }
        $address = $map->optionalMap('address');
        $maritalStatus = $map->optionalString('marital_status');

        return new self(
            $map->string('key'),
            PatientCondition::parse($map->map('pick')),
            $maritalStatus === null ? null : $lists->add(ListReference::MARITAL, $maritalStatus, $map->pathOf('marital_status')),
            $map->optionalString('provider'),
            $lastName,
            $lastNameSameAs,
            $address === null ? null : self::sameAs($address),
        );
    }

    private static function sameAs(MapReader $map): string
    {
        $map->rejectUnknownKeys(['same_as']);
        return $map->string('same_as');
    }
}
