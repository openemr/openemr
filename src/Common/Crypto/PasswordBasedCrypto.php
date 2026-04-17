<?php

/**
 * Password-based encryption/decryption with support for legacy formats
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use SensitiveParameter;

readonly class PasswordBasedCrypto
{
    private const CIPHER = 'aes-256-cbc';
    private const IV_LENGTH = 16;
    private const MIN_CIPHERTEXT_LENGTH = 16; // one AES block

    // Modern format (v4-7) constants
    private const HASH_ALGO = 'sha384';
    private const SALT_LENGTH = 32;
    private const KEY_LENGTH = 32;
    private const HMAC_LENGTH = 48; // sha384 raw output
    private const PBKDF2_ITERATIONS = 100_000;
    private const HKDF_INFO_ENCRYPTION = 'aes-256-encryption';
    private const HKDF_INFO_HMAC = 'sha-384-authentication';

    // Legacy v2/v3 constant
    private const V2_HMAC_LENGTH = 32; // sha256 raw output

    public function __construct(
        private KeyVersion $version,
    ) {
    }

    public function encrypt(
        #[SensitiveParameter] string $plaintext,
        #[SensitiveParameter] string $password,
    ): string {
        $salt = random_bytes(self::SALT_LENGTH);
        [$secretKey, $hmacKey] = $this->deriveKeys($password, $salt);

        $iv = random_bytes(self::IV_LENGTH);

        $encrypted = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv,
        );
        // @codeCoverageIgnoreStart
        // openssl_encrypt only fails with invalid parameters (wrong cipher, bad IV length, etc.)
        // which can't happen with our hardcoded constants. Defensive check only.
        if ($encrypted === false) {
            throw new CryptoGenException('Encryption failed');
        }
        // @codeCoverageIgnoreEnd

        $hmac = hash_hmac(self::HASH_ALGO, $iv . $encrypted, $hmacKey, true);

        $output = $salt . $hmac . $iv . $encrypted;
        return $this->version->toPaddedString() . base64_encode($output);
    }

    public function decrypt(
        string $ciphertextWithVersion,
        #[SensitiveParameter] string $password,
    ): string {
        $version = KeyVersion::fromPrefix($ciphertextWithVersion);

        $ciphertext = substr($ciphertextWithVersion, KeyVersion::PREFIX_LENGTH);

        $payload = base64_decode($ciphertext, true);
        if ($payload === false) {
            throw new CryptoGenException('Could not base64-decode the ciphertext');
        }

        $minLength = match ($version) {
            KeyVersion::ONE => self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH,
            KeyVersion::TWO, KeyVersion::THREE => self::V2_HMAC_LENGTH + self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH,
            default => self::SALT_LENGTH + self::HMAC_LENGTH + self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH,
        };

        if (strlen($payload) < $minLength) {
            throw new CryptoGenException('Ciphertext too short');
        }

        return match ($version) {
            KeyVersion::ONE => $this->decryptV1($payload, $password),
            KeyVersion::TWO, KeyVersion::THREE => $this->decryptV2V3($payload, $password),
            default => $this->decryptModern($payload, $password),
        };
    }

    /**
     * V1: sha256(password) as hex, no HMAC, format: iv + ciphertext
     */
    private function decryptV1(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $iv = substr($payload, 0, self::IV_LENGTH);
        $encrypted = substr($payload, self::IV_LENGTH);

        // V1 used sha256 hex as key (64 chars, OpenSSL truncates to 32 bytes)
        $key = hash('sha256', $password);

        $output = openssl_decrypt($encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }

        return $output;
    }

    /**
     * V2/V3: sha256(password) as binary, HMAC-SHA256, format: hmac(32) + iv + ciphertext
     */
    private function decryptV2V3(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $hmac = substr($payload, 0, self::V2_HMAC_LENGTH);
        $iv = substr($payload, self::V2_HMAC_LENGTH, self::IV_LENGTH);
        $encrypted = substr($payload, self::V2_HMAC_LENGTH + self::IV_LENGTH);

        $key = hash('sha256', $password, true);

        $expectedHmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('Invalid HMAC');
        }

        $output = openssl_decrypt($encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        // @codeCoverageIgnoreStart
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }
        // @codeCoverageIgnoreEnd

        return $output;
    }

    /**
     * V4-7: PBKDF2+HKDF, HMAC-SHA384, format: salt(32) + hmac(48) + iv + ciphertext
     */
    private function decryptModern(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $salt = substr($payload, 0, self::SALT_LENGTH);
        $rest = substr($payload, self::SALT_LENGTH);

        [$secretKey, $hmacKey] = $this->deriveKeys($password, $salt);

        $hmac = substr($rest, 0, self::HMAC_LENGTH);
        $iv = substr($rest, self::HMAC_LENGTH, self::IV_LENGTH);
        $encrypted = substr($rest, self::HMAC_LENGTH + self::IV_LENGTH);

        $expectedHmac = hash_hmac(self::HASH_ALGO, $iv . $encrypted, $hmacKey, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('Invalid HMAC');
        }

        $output = openssl_decrypt($encrypted, self::CIPHER, $secretKey, OPENSSL_RAW_DATA, $iv);
        // @codeCoverageIgnoreStart
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }
        // @codeCoverageIgnoreEnd

        return $output;
    }

    /**
     * Derive encryption and HMAC keys from password and salt (modern format only).
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
