<?php

/**
 * One result from the NPPES registry (https://npiregistry.cms.hhs.gov/api/,
 * version 2.1), mapped to the fields OpenEMR stores for a pharmacy.
 *
 * The shape of a result matters in three places:
 *  - the name is 'organization_name' for an organization and first/last name
 *    for an individual; there is no 'name' key;
 *  - 'addresses' holds the practice LOCATION and the MAILING address, in no
 *    fixed order, and only the location belongs on a pharmacy record;
 *  - 'postal_code' is five or nine digits, and only the first five are stored.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Pharmacy\Services;

final readonly class NppesPharmacy
{
    private function __construct(
        public string $npi,
        public string $name,
        public ?string $ncpdp,
        public string $addressLine1,
        public string $city,
        public string $state,
        public string $zip,
        public ?string $phone,
        public ?string $fax,
    ) {
    }

    /**
     * @param  array<array-key, mixed> $result one entry of the registry's "results"
     * @return ?self                   null when the result carries no NPI to store it under
     */
    public static function fromResult(array $result): ?self
    {
        $npi = self::text($result['number'] ?? null);
        if ($npi === '') {
            return null;
        }

        $basic = is_array($result['basic'] ?? null) ? $result['basic'] : [];
        $address = self::practiceLocation($result['addresses'] ?? null);
        $identifiers = is_array($result['identifiers'] ?? null) ? $result['identifiers'] : [];

        return new self(
            npi: $npi,
            name: self::name($basic),
            ncpdp: self::ncpdp($identifiers),
            addressLine1: self::text($address['address_1'] ?? null),
            city: self::text($address['city'] ?? null),
            state: self::text($address['state'] ?? null),
            zip: substr(self::text($address['postal_code'] ?? null), 0, 5),
            phone: self::optionalText($address['telephone_number'] ?? null),
            fax: self::optionalText($address['fax_number'] ?? null),
        );
    }

    /**
     * The address the pharmacy practises from. A result carries a LOCATION and
     * a MAILING address in either order, and for a chain the mailing address is
     * often a head office in another state.
     *
     * @return array<array-key, mixed>
     */
    private static function practiceLocation(mixed $addresses): array
    {
        if (!is_array($addresses)) {
            return [];
        }
        $first = [];
        foreach ($addresses as $address) {
            if (!is_array($address)) {
                continue;
            }
            if (self::text($address['address_purpose'] ?? null) === 'LOCATION') {
                return $address;
            }
            if ($first === []) {
                $first = $address;
            }
        }
        return $first;
    }

    /** @param array<array-key, mixed> $basic */
    private static function name(array $basic): string
    {
        $organization = self::text($basic['organization_name'] ?? null);
        if ($organization !== '') {
            return $organization;
        }
        // An individual pharmacist, stored the way the rest of OpenEMR shows a
        // person: "Last, First".
        $last = self::text($basic['last_name'] ?? null);
        $first = self::text($basic['first_name'] ?? null);
        if ($last !== '' && $first !== '') {
            return $last . ', ' . $first;
        }
        return $last !== '' ? $last : $first;
    }

    /** @param array<array-key, mixed> $identifiers */
    private static function ncpdp(array $identifiers): ?string
    {
        foreach ($identifiers as $identifier) {
            if (!is_array($identifier)) {
                continue;
            }
            if (self::text($identifier['desc'] ?? null) === 'Other') {
                return self::optionalText($identifier['identifier'] ?? null);
            }
        }
        return null;
    }

    private static function text(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }
        return is_int($value) ? (string) $value : '';
    }

    private static function optionalText(mixed $value): ?string
    {
        $text = self::text($value);
        return $text === '' ? null : $text;
    }
}
