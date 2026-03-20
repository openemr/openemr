<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Encryption\Keys\KeyManagerInterface;

/**
 * Legacy "version 1" handling.
 *
 * @deprecated
 */
class Aes256CbcNoHmac implements CipherInterface
{
    private const IV_LENGTH = 16; // openssl_cipher_iv_length('aes-256-cbc')

    public function decrypt(string $ciphertext, string $keyId, KeyManagerInterface $manager): string
    {
        $iv = substr($ciphertext, 0, self::IV_LENGTH);
        $data = substr($ciphertext, self::IV_LENGTH);

        assert($keyId === 'one', 'This should only be used for the very oldest keys');
        $keyMaterial = $manager->getKey($keyId);

        $decrypted = openssl_decrypt(
            $data,
            'aes-256-cbc',
            $keyMaterial->key,
            OPENSSL_RAW_DATA,
            $iv,
        );
        if ($decrypted === false) {
            // This *is* reachable since there's no HMAC
            throw new CryptoGenException('Decryption failed');
        }
        return $decrypted;
    }
}
