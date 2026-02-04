<?php

/**
 * AddressData value object for address insert/update operations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Address;

/**
 * Immutable value object representing address data for database operations.
 *
 * This DTO is used for insert and update operations on the addresses table.
 * It can be constructed from an array (for backward compatibility with existing
 * code that passes mixed data arrays) or directly with named arguments.
 */
readonly class AddressData
{
    public function __construct(
        public string $line1,
        public string $line2,
        public string $city,
        public string $state,
        public string $zip,
        public string $country,
        public ?string $plusFour = null,
    ) {
    }

    /**
     * Create an AddressData instance from an array.
     *
     * Supports both camelCase and snake_case keys for flexibility.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            line1: self::getString($data, 'line1'),
            line2: self::getString($data, 'line2'),
            city: self::getString($data, 'city'),
            state: self::getString($data, 'state'),
            zip: self::getString($data, 'zip'),
            country: self::getString($data, 'country'),
            plusFour: self::getStringOrNull($data, 'plus_four'),
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
     * @param array<string, mixed> $data
     */
    private static function getStringOrNull(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }
        $value = $data[$key];
        return is_string($value) ? $value : null;
    }

    /**
     * Convert to array for database operations.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'plus_four' => $this->plusFour,
        ];
    }
}
