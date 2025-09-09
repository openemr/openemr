<?php

/**
 * KeyVersion enum
 *
 * Defines encryption algorithm versions used by OpenEMR's CryptoGen class.
 * This versioning system allows for algorithm improvements while maintaining
 * backwards compatibility for decrypting data encrypted with older versions.
 *
 * Version history:
 * - Version 1: Basic AES-256-CBC with simple key derivation
 * - Version 2-3: AES-256-CBC with HMAC-SHA256 authentication
 * - Version 4-7: Modern AES-256-CBC with HMAC-SHA384 authentication and improved key derivation
 *
 * Storage differences:
 * - Versions 1-4: Keys stored as base64 on drive (unencrypted)
 * - Versions 5-7: Keys encrypted using database keys before drive storage
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

enum KeyVersion: int
{
    case ONE = 1;
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;
    case SIX = 6;
    case SEVEN = 7;

    /**
     * Get the string representation of the key version
     *
     * @return string The string name (e.g., "one", "two", etc.)
     */
    public function toString(): string
    {
        return match ($this) {
            self::ONE => 'one',
            self::TWO => 'two',
            self::THREE => 'three',
            self::FOUR => 'four',
            self::FIVE => 'five',
            self::SIX => 'six',
            self::SEVEN => 'seven'
        };
    }

    /**
     * Get zero-padded 3-digit string representation of the version
     *
     * @return string Zero-padded version (e.g., "001", "002", etc.)
     */
    public function toPaddedString(): string
    {
        return str_pad((string)$this->value, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if this version uses legacy base64 storage (unencrypted on drive)
     *
     * @return bool True for versions 1-4 (stored as base64), false for versions 5+ (encrypted)
     */
    public function usesLegacyStorage(): bool
    {
        return $this->value <= 4;
    }

    /**
     * Check if this version uses legacy AES decryption algorithms
     *
     * @return bool True for versions 1-3 (legacy AES), false for versions 4+ (coreDecrypt)
     */
    public function usesLegacyDecryption(): bool
    {
        return $this->value <= 3;
    }

    /**
     * Create KeyVersion from string representation
     *
     * @param  string $version The string version (e.g., "one", "two", etc.)
     * @return self
     * @throws InvalidArgumentException If version string is invalid
     */
    public static function fromString(string $version): self
    {
        return match ($version) {
            'one' => self::ONE,
            'two' => self::TWO,
            'three' => self::THREE,
            'four' => self::FOUR,
            'five' => self::FIVE,
            'six' => self::SIX,
            'seven' => self::SEVEN,
            default => throw new \InvalidArgumentException("Invalid key version: $version")
        };
    }

    /**
     * Extract a KeyVersion from the prefix of a string.
     *
     * @param  string $value The string to check (should be at least 3 bytes)
     * @return self the KeyVersion extracted from the first 3 bytes of the string
     * @throws \ValueError If the prefix cannot be converted to a KeyVersion
     */
    public static function fromPrefix(string $value): self
    {
        if (strlen($value) < 3) {
            throw new \ValueError("Input string must be at least 3 bytes long");
        }
        $rawPrefixStr = mb_substr($value, 0, 3, '8bit');
        $rawPrefixInt = intval($rawPrefixStr);
        return self::from($rawPrefixInt);
    }
}
