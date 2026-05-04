<?php

/**
 * Interface for Encrypt/Decrypt operations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

interface CryptoInterface
{
    /**
     * Encrypts data using the standard encryption method
     *
     * @param ?string $value     The data to encrypt
     * @param KeySource $keySource The source of the standard keys.
     * @return string The encrypted data
     */
    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string;

    /**
     * Decrypts data using the standard decryption method
     *
     * @param ?string $value          The data to decrypt
     * @param KeySource $keySource      The source of the standard keys.
     * @param ?int    $minimumVersion The minimum encryption version supported (useful when accepting encrypted data
     *                                from outside OpenEMR to prevent bad actors from using older versions)
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function decryptStandard(?string $value, KeySource $keySource = KeySource::Drive, ?int $minimumVersion = null): false|string;

    /**
     * Checks if a crypt block is valid for use with the standard method
     *
     * @param ?string $value The data to validate
     * @return bool True if valid, false otherwise
     */
    public function cryptCheckStandard(?string $value): bool;

    /**
     * Encrypts data for storage in the database.
     *
     * Uses KeySource::Drive explicitly for database field encryption.
     *
     * @param ?string $value The value to encrypt
     * @return string The encrypted value, or empty string if input is null/empty
     */
    public function encryptForDatabase(?string $value): string;

    /**
     * Encrypts data for storage on a filesystem.
     *
     * @param ?string $value The value to encrypt
     * @return string The encrypted value, or empty string if input is null/empty
     */
    public function encryptForFilesystem(?string $value): string;

    /**
     * Decrypts data retrieved from the database.
     *
     * If the value is not encrypted (no valid prefix), returns it unchanged (plaintext passthrough).
     * If decryption fails, throws CryptoGenException.
     *
     * @param ?string $value The value to decrypt
     * @param ?int $minimumVersion Minimum encryption version required
     * @return string The decrypted value, or original value if not encrypted
     * @throws CryptoGenException If decryption of encrypted data fails
     */
    public function decryptFromDatabase(?string $value, ?int $minimumVersion = null): string;


    /**
     * Decrypts data retrieved from the filesystem.
     *
     * If the value is not encrypted, it will pass through the plaintext
     * unchanged.
     * If decryption fails, throws CryptoGenException.
     *
     * @param ?string $value The value to decrypt
     * @return string The decrypted value, or original value if not encrypted
     * @throws CryptoGenException If decryption of encrypted data fails
     */
    public function decryptFromFilesystem(?string $value): string;
}
