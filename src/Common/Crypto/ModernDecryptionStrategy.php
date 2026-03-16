<?php

/**
 * Modern (V4-7) password-based decryption strategy
 *
 * Modern format: salt(32) + hmac(48) + iv(16) + ciphertext
 * Key derivation: PBKDF2 + HKDF with separate encryption and HMAC keys
 * HMAC-SHA384 authentication
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

final class ModernDecryptionStrategy implements PasswordDecryptionStrategyInterface
{
    private const CIPHER = 'aes-256-cbc';
    private const HASH_ALGO = 'sha384';
    private const SALT_LENGTH = 32;
    private const KEY_LENGTH = 32;
    private const HMAC_LENGTH = 48; // sha384 raw output
    private const PBKDF2_ITERATIONS = 100_000;
    private const HKDF_INFO_ENCRYPTION = 'aes-256-encryption';
    private const HKDF_INFO_HMAC = 'sha-384-authentication';
    private const IV_LENGTH = 16;
    private const MIN_CIPHERTEXT_LENGTH = 16; // one AES block

    public function decrypt(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $salt = mb_substr($payload, 0, self::SALT_LENGTH, '8bit');
        $rest = mb_substr($payload, self::SALT_LENGTH, null, '8bit');

        [$secretKey, $hmacKey] = $this->deriveKeys($password, $salt);

        $hmac = mb_substr($rest, 0, self::HMAC_LENGTH, '8bit');
        $iv = mb_substr($rest, self::HMAC_LENGTH, self::IV_LENGTH, '8bit');
        $encrypted = mb_substr($rest, self::HMAC_LENGTH + self::IV_LENGTH, null, '8bit');

        $expectedHmac = hash_hmac(self::HASH_ALGO, $iv . $encrypted, $hmacKey, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('Invalid HMAC');
        }

        $output = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv,
        );

        // @codeCoverageIgnoreStart
        // Unreachable in practice: if HMAC validates, the ciphertext is intact
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }
        // @codeCoverageIgnoreEnd

        return $output;
    }

    public function getMinPayloadLength(): int
    {
        return self::SALT_LENGTH + self::HMAC_LENGTH + self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH;
    }

    /**
     * Derive encryption and HMAC keys from password and salt.
     *
     * @return array{string, string} [$encryptionKey, $hmacKey]
     */
    private function deriveKeys(
        #[SensitiveParameter] string $password,
        string $salt,
    ): array {
        $preKey = hash_pbkdf2(self::HASH_ALGO, $password, $salt, self::PBKDF2_ITERATIONS, self::KEY_LENGTH, true);
        return [
            hash_hkdf(self::HASH_ALGO, $preKey, self::KEY_LENGTH, self::HKDF_INFO_ENCRYPTION, $salt),
            hash_hkdf(self::HASH_ALGO, $preKey, self::KEY_LENGTH, self::HKDF_INFO_HMAC, $salt),
        ];
    }
}
