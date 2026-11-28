<?php

/**
 * A related person written out in a case (not a patient).
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

final readonly class RelatedPersonEntry
{
    /**
     * @param ?string $key lets other sections (insurance) refer to this person
     * @param ?string $lastName null = the patient's last name
     * @param list<TelecomEntry> $telecom
     */
    public function __construct(
        public ?string $key,
        public string $firstName,
        public ?string $lastName,
        public string $relationship,
        public string $role,
        public RelationFlags $flags,
        public bool $sameAddress,
        public array $telecom,
        public ?PostalAddress $address,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys([
            'key', 'first_name', 'last_name', 'relationship', 'role', 'same_address', 'telecom', 'address',
            ...RelationFlags::KEYS,
        ]);
        $sameAddress = $map->optionalBool('same_address');
        $addressMap = $map->optionalMap('address');
        if ($sameAddress && $addressMap !== null) {
            throw SeedFileException::at($map->pathOf('address'), 'can not go with same_address');
        }
        $address = null;
        if ($addressMap !== null) {
            $address = PostalAddress::parse($addressMap);
            $lists->add(ListReference::STATE, $address->state, $addressMap->pathOf('state'));
        }
        return new self(
            $map->optionalString('key'),
            $map->string('first_name'),
            $map->optionalString('last_name'),
            $lists->add(ListReference::RELATIONSHIP, $map->string('relationship'), $map->pathOf('relationship')),
            $lists->add(ListReference::ROLE, $map->string('role'), $map->pathOf('role')),
            RelationFlags::parse($map),
            $sameAddress,
            TelecomEntry::parseList($map, 'telecom', $lists),
            $address,
        );
    }
}
