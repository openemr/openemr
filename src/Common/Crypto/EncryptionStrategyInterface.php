<?php

/**
 * EncryptionStrategyInterface - Interface for encryption strategy implementations.
 *
 * This interface defines the contract that all encryption strategies must implement
 * to be compatible with the CryptoGen class. It supports both standard key-based
 * encryption and custom password-based encryption.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

/**
 * Interface for encryption strategy implementations.
 *
 * All encryption strategies must implement these methods to provide
 * encryption, decryption, and validation functionality.
 */
interface EncryptionStrategyInterface
{
    /**
     * Encrypt data using the strategy's encryption method.
     *
     * @param string|null $value The data to encrypt
     * @param string|null $customPassword If provided, use password-based encryption
     * @param string $keySource Source for standard keys ('drive' or 'database')
     * @return string|null Encrypted data or null if input is null
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive');

    /**
     * Decrypt data using the strategy's decryption method.
     *
     * @param string|null $value The encrypted data to decrypt
     * @param string|null $customPassword If provided, use password-based decryption
     * @param string $keySource Source for standard keys ('drive' or 'database')
     * @param int|null $minimumVersion Minimum encryption version required
     * @return false|string|null Decrypted data or false on failure
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string|null;

    /**
     * Check if a value was encrypted using this strategy.
     *
     * @param string|null $value The value to check
     * @return bool True if the value is compatible with this strategy
     */
    public function cryptCheckStandard(?string $value): bool;

    /**
     * Get the unique identifier for this encryption strategy.
     *
     * @return string Strategy identifier (e.g., 'cryptogen', 'null')
     */
    public function getId(): string;

    /**
     * Get the human-readable name for this encryption strategy.
     *
     * @return string Strategy display name
     */
    public function getName(): string;

    /**
     * Get a description of this encryption strategy.
     *
     * @return string Strategy description
     */
    public function getDescription(): string;
}
