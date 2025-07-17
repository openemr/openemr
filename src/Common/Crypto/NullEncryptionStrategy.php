<?php

/**
 * Null encryption strategy that returns values unchanged.
 *
 * This strategy provides no encryption - it is an identity function
 * that returns all values exactly as provided. Used when encryption
 * is disabled or not required.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

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

    /**
     * Serialize the strategy for database storage.
     *
     * Since this is a null strategy with no state, we just return a simple identifier.
     *
     * @return string Serialized strategy data
     */
    public function serialize(): string
    {
        return serialize(['type' => 'null']);
    }

    /**
     * Unserialize the strategy from database storage.
     *
     * Since this is a null strategy with no state, there's nothing to restore.
     *
     * @param string $data Serialized strategy data
     */
    public function unserialize(string $data): void
    {
        // Nothing to unserialize for null strategy
    }

    /**
     * Modern PHP serialization method.
     *
     * @return array Data to serialize
     */
    public function __serialize(): array
    {
        return ['type' => 'null'];
    }

    /**
     * Modern PHP unserialization method.
     *
     * @param array $data Serialized data
     */
    public function __unserialize(array $data): void
    {
        // Nothing to unserialize for null strategy
    }
}
