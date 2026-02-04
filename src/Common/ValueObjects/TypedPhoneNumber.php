<?php

/**
 * TypedPhoneNumber - Associates a phone number with its type
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ValueObjects;

use OpenEMR\Services\PhoneType;

/**
 * Pairs a PhoneNumber value object with its application-specific type (home, work, fax, etc.).
 *
 * The type is not intrinsic to the phone number itself - it describes how the
 * number is used in the context of the application (e.g., "this is John's home phone").
 */
final readonly class TypedPhoneNumber
{
    public function __construct(
        public PhoneNumber $phoneNumber,
        public PhoneType $type = PhoneType::HOME
    ) {
    }

    /**
     * Create from a phone string and type.
     *
     * @throws \libphonenumber\NumberParseException If the number cannot be parsed
     */
    public static function create(string $number, PhoneType $type = PhoneType::HOME, string $defaultRegion = 'US'): self
    {
        return new self(PhoneNumber::parse($number, $defaultRegion), $type);
    }

    /**
     * Try to create from a phone string and type, returning null on failure.
     */
    public static function tryCreate(string $number, PhoneType $type = PhoneType::HOME, string $defaultRegion = 'US'): ?self
    {
        $phoneNumber = PhoneNumber::tryParse($number, $defaultRegion);
        if ($phoneNumber === null) {
            return null;
        }
        return new self($phoneNumber, $type);
    }

    /**
     * Delegate to the underlying PhoneNumber for local formatting.
     */
    public function formatLocal(): string
    {
        return $this->phoneNumber->formatLocal();
    }

    /**
     * Delegate to the underlying PhoneNumber for E.164.
     */
    public function toE164(): string
    {
        return $this->phoneNumber->toE164();
    }

    /**
     * Delegate to the underlying PhoneNumber for HL7 format.
     */
    public function toHL7(): string
    {
        return $this->phoneNumber->toHL7();
    }

    /**
     * Get phone parts as an array for legacy compatibility.
     *
     * @return array{area_code: string, prefix: string, number: string, type: int}
     */
    public function toParts(): array
    {
        return array_merge(
            $this->phoneNumber->toParts(),
            ['type' => $this->type->value]
        );
    }
}
