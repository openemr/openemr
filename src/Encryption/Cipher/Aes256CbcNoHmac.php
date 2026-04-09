<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use BadMethodCallException;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Encryption\{
    Ciphertext,
    Plaintext,
};

/**
 * Legacy "version 1" handling.
 *
 * @deprecated
 */
readonly class Aes256CbcNoHmac implements CipherInterface
{
    private const IV_LENGTH = 16; // openssl_cipher_iv_length('aes-256-cbc')

    public function __construct(
        private KeyMaterial $key,
    ) {
    }

    public function decrypt(Ciphertext $ciphertext): Plaintext
    {
        $ciphertext = $ciphertext->value;
        $iv = substr($ciphertext, 0, self::IV_LENGTH);
        $data = substr($ciphertext, self::IV_LENGTH);

        $decrypted = openssl_decrypt(
            $data,
            'aes-256-cbc',
            $this->key->key,
            OPENSSL_RAW_DATA,
            $iv,
        );
        if ($decrypted === false) {
            // This *is* reachable since there's no HMAC
            throw new CryptoGenException('Decryption failed');
        }
        return new Plaintext($decrypted);
    }

    public function encrypt(Plaintext $plaintext): Ciphertext
    {
        throw new BadMethodCallException(sprintf(
            'Encrypting new data with %s is not supported',
            self::class,
        ));
    }
}
