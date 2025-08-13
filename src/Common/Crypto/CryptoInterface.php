<?php

/**
 * Interface for Encrypt/Decrypt operations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

interface CryptoInterface
{
    /**
     * Encrypts data using the standard encryption method
     *
     * @param ?string $value          The data to encrypt
     * @param ?string $customPassword If provided, keys will be derived from this password (standard keys will not be used)
     * @param string  $keySource      The source of the standard keys. Options are 'drive' and 'database'
     * @return string The encrypted data
     */
    public function encryptStandard(?string $value, ?string $customPassword, string $keySource): string;

    /**
     * Decrypts data using the standard decryption method
     *
     * @param ?string $value          The data to decrypt
     * @param ?string $customPassword If provided, keys will be derived from this password (standard keys will not be used)
     * @param string  $keySource      The source of the standard keys. Options are 'drive' and 'database'
     * @param ?int    $minimumVersion The minimum encryption version supported (useful when accepting encrypted data
     *                                from outside OpenEMR to prevent bad actors from using older versions)
     * @return false|string The decrypted data, or false if decryption fails
     */
    public function decryptStandard(?string $value, ?string $customPassword, string $keySource, ?int $minimumVersion): false|string;

    /**
     * Checks if a crypt block is valid for use with the standard method
     *
     * @param ?string $value The data to validate
     * @return bool True if valid, false otherwise
     */
    public function cryptCheckStandard(?string $value): bool;
}
