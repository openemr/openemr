<?php

/**
 * PhoneNumber value object wrapping libphonenumber
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\ValueObjects;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as LibPhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Immutable value object representing a parsed phone number.
 *
 * Use the static factory methods to create instances:
 * - PhoneNumber::parse() throws on invalid input
 * - PhoneNumber::tryParse() returns null on invalid input
 */
final readonly class PhoneNumber implements \Stringable
{
    private PhoneNumberUtil $util;

    private function __construct(
        private LibPhoneNumber $phoneNumber
    ) {
        $this->util = PhoneNumberUtil::getInstance();
    }

    /**
     * Parse a phone number string.
     *
     * @param string $number The phone number to parse
     * @param string $defaultRegion Default region code (e.g., 'US')
     * @throws NumberParseException If the number cannot be parsed
     */
    public static function parse(string $number, string $defaultRegion = 'US'): self
    {
        $util = PhoneNumberUtil::getInstance();
        $parsed = $util->parse($number, $defaultRegion);
        return new self($parsed);
    }

    /**
     * Try to parse a phone number, returning null on failure.
     *
     * @param string $number The phone number to parse
     * @param string $defaultRegion Default region code (e.g., 'US')
     */
    public static function tryParse(string $number, string $defaultRegion = 'US'): ?self
    {
        if ($number === '') {
            return null;
        }
        try {
            return self::parse($number, $defaultRegion);
        } catch (NumberParseException) {
            return null;
        }
    }

    /**
     * Check if this is a valid phone number (validates against real area codes).
     */
    public function isValid(): bool
    {
        return $this->util->isValidNumber($this->phoneNumber);
    }

    /**
     * Check if this is a possible phone number (less strict - only checks format/length).
     */
    public function isPossible(): bool
    {
        return $this->util->isPossibleNumber($this->phoneNumber);
    }

    /**
     * Format as E.164 (e.g., +15551234567).
     */
    public function toE164(): string
    {
        return $this->util->format($this->phoneNumber, PhoneNumberFormat::E164);
    }

    /**
     * Format for national dialing (e.g., (555) 123-4567).
     */
    public function toNational(): string
    {
        return $this->util->format($this->phoneNumber, PhoneNumberFormat::NATIONAL);
    }

    /**
     * Format for international dialing (e.g., +1 555-123-4567).
     */
    public function toInternational(): string
    {
        return $this->util->format($this->phoneNumber, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Format as RFC 3966 URI (e.g., tel:+1-555-123-4567).
     */
    public function toRFC3966(): string
    {
        return $this->util->format($this->phoneNumber, PhoneNumberFormat::RFC3966);
    }

    /**
     * Format for local/national display.
     *
     * For NANP numbers (US/Canada/Caribbean with country code 1), formats as XXX-XXX-XXXX.
     * For other regions, uses libphonenumber's locale-aware national format.
     */
    public function formatLocal(): string
    {
        // NANP numbers have country code 1 and 10-digit national numbers
        if ($this->getCountryCode() === 1) {
            $national = $this->getNationalDigits();
            if ($national !== null) {
                return substr($national, 0, 3) . '-' . substr($national, 3, 3) . '-' . substr($national, 6, 4);
            }
        }
        return $this->toNational();
    }

    /**
     * Format for HL7 messaging (XXX^XXXXXXX).
     */
    public function toHL7(): string
    {
        $national = $this->getNationalDigits();
        if ($national !== null) {
            return substr($national, 0, 3) . '^' . substr($national, 3, 7);
        }
        return '000^0000000';
    }

    /**
     * Get the 10-digit national number as a string.
     *
     * @return string|null 10 digits or null if not a NANP number
     */
    public function getNationalDigits(): ?string
    {
        $national = (string) $this->phoneNumber->getNationalNumber();
        return strlen($national) === 10 ? $national : null;
    }

    /**
     * Get the country code (e.g., 1 for US).
     */
    public function getCountryCode(): ?int
    {
        return $this->phoneNumber->getCountryCode();
    }

    /**
     * Get the region code (e.g., 'US').
     */
    public function getRegionCode(): ?string
    {
        return $this->util->getRegionCodeForNumber($this->phoneNumber);
    }

    /**
     * Get the extension, if any.
     */
    public function getExtension(): ?string
    {
        return $this->phoneNumber->getExtension();
    }

    /**
     * Get the area code (first 3 digits of NANP number).
     */
    public function getAreaCode(): string
    {
        $national = (string) $this->phoneNumber->getNationalNumber();
        return strlen($national) >= 3 ? substr($national, 0, 3) : '';
    }

    /**
     * Get the prefix/exchange (digits 4-6 of NANP number).
     */
    public function getPrefix(): string
    {
        $national = (string) $this->phoneNumber->getNationalNumber();
        return strlen($national) >= 6 ? substr($national, 3, 3) : '';
    }

    /**
     * Get the subscriber number (last 4 digits of NANP number).
     */
    public function getSubscriberNumber(): string
    {
        $national = (string) $this->phoneNumber->getNationalNumber();
        return strlen($national) >= 10 ? substr($national, 6, 4) : '';
    }

    /**
     * Get phone parts as an array for legacy compatibility.
     *
     * @return array{area_code: string, prefix: string, number: string}
     */
    public function toParts(): array
    {
        return [
            'area_code' => $this->getAreaCode(),
            'prefix' => $this->getPrefix(),
            'number' => $this->getSubscriberNumber(),
        ];
    }

    /**
     * Default string representation is E.164 format.
     */
    public function __toString(): string
    {
        return $this->toE164();
    }
}
