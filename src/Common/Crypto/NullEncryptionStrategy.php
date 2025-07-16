<?php

namespace OpenEMR\Common\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

/**
 * Null encryption strategy that returns values unchanged.
 *
 * This strategy provides no encryption - it is an identity function
 * that returns all values exactly as provided. Used when encryption
 * is disabled or not required.
 */
class NullEncryptionStrategy implements EncryptionStrategyInterface
{
    /**
     * Returns the value unchanged (no encryption).
     *
     * @param string|null $value The value to "encrypt"
     * @param string|null $customPassword Ignored parameter for interface compatibility
     * @param string $keySource Ignored parameter for interface compatibility
     * @return string|null The same value that was passed in
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        return $value;
    }

    /**
     * Returns the value unchanged (no decryption).
     *
     * @param string|null $value The value to "decrypt"
     * @param string|null $customPassword Ignored parameter for interface compatibility
     * @param string $keySource Ignored parameter for interface compatibility
     * @param int|null $minimumVersion Ignored parameter for interface compatibility
     * @return false|string The same value that was passed in
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        return $value;
    }

    /**
     * Always returns true since no encryption validation is needed.
     *
     * @param string|null $value The value to check
     * @return bool Always true
     */
    public function cryptCheckStandard(?string $value): bool
    {
        return true;
    }
}
