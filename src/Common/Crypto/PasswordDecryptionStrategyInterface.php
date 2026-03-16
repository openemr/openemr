<?php

/**
 * Interface for password-based decryption strategies
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use SensitiveParameter;

interface PasswordDecryptionStrategyInterface
{
    /**
     * Decrypt the raw payload (after base64 decoding, without version prefix)
     *
     * @param string $payload The raw binary payload (already base64-decoded)
     * @param string $password The decryption password
     * @return string The decrypted plaintext
     * @throws CryptoGenException If decryption fails
     */
    public function decrypt(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string;

    /**
     * Get the minimum payload length required for this strategy
     */
    public function getMinPayloadLength(): int;
}
