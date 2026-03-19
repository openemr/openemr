<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Decrypt;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    Keys\KeyManagerInterface,
};

/**
 * Legacy "version 2-3" handling.
 *
 * @deprecated
 */
class Aes256CbcHmacSha256 implements DecoderInterface
{
    private const HMAC_LENGTH = 32; // 256/8

    private const IV_LENGTH = 16; // openssl_cipher_iv_length('aes-256-cbc')

    public function decrypt(string $ciphertext, string $keyId, KeyManagerInterface $manager): string
    {
        $hmac = mb_substr($ciphertext, 0, self::HMAC_LENGTH, '8bit');
        $iv = mb_substr($ciphertext, self::HMAC_LENGTH, self::IV_LENGTH, '8bit');
        $data = mb_substr($ciphertext, (self::HMAC_LENGTH + self::IV_LENGTH), null, '8bit');

        $decryptionKey = $manager->getKey($keyId . 'a');
        $hmacKey = $manager->getKey($keyId . 'b');

        $expectedHmac = hash_hmac('sha256', $iv . $data, $hmacKey->key, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('HMAC invalid while decrypting message');
        }

        $decrypted = openssl_decrypt(
            $data,
            'aes-256-cbc',
            $decryptionKey->key,
            OPENSSL_RAW_DATA,
            $iv,
        );

        if ($decrypted === false) {
            throw new CryptoGenException('Decryption failed despite HMAC validating');
        }
        return $decrypted;
    }
}
