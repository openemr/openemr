<?php

/**
 * Version 1 password-based decryption strategy
 *
 * V1 format: iv(16) + ciphertext
 * Key derivation: sha256(password) as hex string (64 bytes, truncated by OpenSSL)
 * No HMAC authentication
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

final class V1DecryptionStrategy implements PasswordDecryptionStrategyInterface
{
    private const CIPHER = 'aes-256-cbc';
    private const IV_LENGTH = 16;
    private const MIN_CIPHERTEXT_LENGTH = 16; // one AES block

    public function decrypt(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $iv = mb_substr($payload, 0, self::IV_LENGTH, '8bit');
        $encrypted = mb_substr($payload, self::IV_LENGTH, null, '8bit');

        // V1 used sha256 hex as key (64 chars, OpenSSL truncates to 32 bytes)
        $key = hash('sha256', $password);

        $output = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
        );

        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }

        return $output;
    }

    public function getMinPayloadLength(): int
    {
        return self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH;
    }
}
