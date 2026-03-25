<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Encryption\{
    Ciphertext,
    Plaintext,
};

/**
 * "Modern" (v4-7) handling.
 */
readonly class Aes256CbcHmacSha384 implements CipherInterface
{
    private const HMAC_LENGTH = 48; // 384/8

    private const IV_LENGTH = 16; // openssl_cipher_iv_length('aes-256-cbc')

    public function __construct(
        private KeyMaterial $key,
        private KeyMaterial $hmacKey,
    ) {
    }

    public function decrypt(Ciphertext $ciphertext): Plaintext
    {
        $ciphertext = $ciphertext->wrapped;
        $hmac = mb_substr($ciphertext, 0, self::HMAC_LENGTH, '8bit');
        $iv = mb_substr($ciphertext, self::HMAC_LENGTH, self::IV_LENGTH, '8bit');
        $data = mb_substr($ciphertext, (self::HMAC_LENGTH + self::IV_LENGTH), null, '8bit');

        $expectedHmac = hash_hmac('sha384', $iv . $data, $this->hmacKey->key, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('HMAC invalid while decrypting message');
        }

        $decrypted = openssl_decrypt(
            $data,
            'aes-256-cbc',
            $this->key->key,
            OPENSSL_RAW_DATA,
            $iv,
        );

        if ($decrypted === false) {
            throw new CryptoGenException('Decryption failed despite HMAC validating');
        }

        return new Plaintext($decrypted);
    }
}
