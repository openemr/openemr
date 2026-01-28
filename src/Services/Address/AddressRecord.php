<?php

/**
 * AddressRecord value object for address display formatting
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Address;

/**
 * Immutable value object representing an address record for display formatting.
 *
 * This DTO represents address data as stored in patient/user records, which
 * uses different field names than the addresses table (street vs line1, etc.).
 */
readonly class AddressRecord implements \Stringable
{
    public function __construct(
        public string $street = '',
        public string $city = '',
        public string $state = '',
        public string $postalCode = '',
        public string $countryCode = '',
    ) {
    }

    /**
     * Create an AddressRecord instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: self::getString($data, 'street'),
            city: self::getString($data, 'city'),
            state: self::getString($data, 'state'),
            postalCode: self::getString($data, 'postal_code'),
            countryCode: self::getString($data, 'country_code'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function getString(array $data, string $key): string
    {
        $value = $data[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    /**
     * Format the address as a multi-line string.
     */
    public function toString(): string
    {
        $lines = [];

        if ($this->street !== '') {
            $lines[] = $this->street;
        }

        $addressLine = implode(' ', array_filter([
            $this->city !== '' ? $this->city . ',' : '',
            $this->state,
            $this->postalCode,
            $this->countryCode,
        ]));

        if ($addressLine !== '') {
            $lines[] = $addressLine;
        }

        return implode("\n", $lines);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
