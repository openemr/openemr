<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use SensitiveParameter;

class PasswordBasedCrypto
{
    private const CIPHER = 'aes-256-cbc';
    private const HASH_ALGO = 'sha384';
    private const SALT_LENGTH = 32;
    private const KEY_LENGTH = 32;
    private const HMAC_LENGTH = 48; // sha384 raw output
    private const PBKDF2_ITERATIONS = 100_000;
    private const HKDF_INFO_ENCRYPTION = 'aes-256-encryption';
    private const HKDF_INFO_HMAC = 'sha-384-authentication';
    private const IV_LENGTH = 16; // aes-256-cbc
    private const MIN_CIPHERTEXT_LENGTH = 16; // one AES block (padding for empty input)
    // salt + hmac + iv + min_ciphertext
    private const MIN_PAYLOAD_LENGTH = self::SALT_LENGTH + self::HMAC_LENGTH + self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH;

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

        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        assert($ivLen > 0);
        $iv = random_bytes($ivLen);

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

        $ciphertext = mb_substr($ciphertextWithVersion, 3, null, '8bit');

        $input = base64_decode($ciphertext, true);
        if ($input === false) {
            throw new CryptoGenException('Could not base64-decode the ciphertext');
        }

        if (mb_strlen($input, '8bit') < self::MIN_PAYLOAD_LENGTH) {
            throw new CryptoGenException('Ciphertext too short');
        }

        $salt = mb_substr($input, 0, self::SALT_LENGTH, '8bit');
        $rest = mb_substr($input, self::SALT_LENGTH, null, '8bit');

        [$secretKey, $hmacKey] = $this->deriveKeys($password, $salt);

        $hashHmac = mb_substr($rest, 0, self::HMAC_LENGTH, '8bit');
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = mb_substr($rest, self::HMAC_LENGTH, $ivLen, '8bit');
        $encrypted = mb_substr($rest, ($ivLen + self::HMAC_LENGTH), null, '8bit');

        $expectedHmac = hash_hmac(self::HASH_ALGO, $iv . $encrypted, $hmacKey, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hashHmac)) {
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
        // and openssl_decrypt will succeed. This is defensive against OpenSSL bugs.
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }
        // @codeCoverageIgnoreEnd
        return $output;
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
