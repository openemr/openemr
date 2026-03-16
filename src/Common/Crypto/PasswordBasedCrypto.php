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
    private const PBKDF2_ITERATIONS = 100_000;
    private const HKDF_INFO_ENCRYPTION = 'aes-256-encryption';
    private const HKDF_INFO_HMAC = 'sha-384-authentication';
    private const IV_LENGTH = 16; // aes-256-cbc

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

        $ciphertext = mb_substr($ciphertextWithVersion, KeyVersion::PREFIX_LENGTH, null, '8bit');

        $payload = base64_decode($ciphertext, true);
        if ($payload === false) {
            throw new CryptoGenException('Could not base64-decode the ciphertext');
        }

        $strategy = $this->getDecryptionStrategy($version);

        if (mb_strlen($payload, '8bit') < $strategy->getMinPayloadLength()) {
            throw new CryptoGenException('Ciphertext too short');
        }

        return $strategy->decrypt($payload, $password);
    }

    private function getDecryptionStrategy(KeyVersion $version): PasswordDecryptionStrategyInterface
    {
        return match ($version) {
            KeyVersion::ONE => new V1DecryptionStrategy(),
            KeyVersion::TWO, KeyVersion::THREE => new V2V3DecryptionStrategy(),
            KeyVersion::FOUR, KeyVersion::FIVE, KeyVersion::SIX, KeyVersion::SEVEN => new ModernDecryptionStrategy(),
        };
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
