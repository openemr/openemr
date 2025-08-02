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

declare(strict_types=1);

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
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive'): ?string
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
     * @return false|string|null The same value that was passed in
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string|null
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
     * Get the unique identifier for this encryption strategy.
     *
     * @return string Strategy identifier
     */
    public function getId(): string
    {
        return 'null';
    }

    /**
     * Get the human-readable name for this encryption strategy.
     *
     * @return string Strategy display name
     */
    public function getName(): string
    {
        return 'No Encryption';
    }

    /**
     * Get a description of this encryption strategy.
     *
     * @return string Strategy description
     */
    public function getDescription(): string
    {
        return 'Disables encryption - data is stored in plain text. Only use in environments where encryption is handled by the OS or at a higher level.';
    }
}
