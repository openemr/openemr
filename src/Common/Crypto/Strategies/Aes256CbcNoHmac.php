<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Strategies;

/**
 * Legacy "version 1" handling.
 *
 * @deprecated
 */
class Aes256CbcNoHmac
{
    private const IV_LENGTH = 16; // openssl_cipher_iv_length('aes-256-cbc')

    public function decrypt(string $ciphertext): string
    {
        // get key material

        $iv = substr($ciphertext, 0, self::IV_LENGTH);
        $data = substr($ciphertext, self::IV_LENGTH);

        $decrytped = openssl_decrypt(
            $data,
            'aes-256-cbc',
            $secret,
            OPENSSL_RAW_DATA,
            $iv,
        );
        if ($decrypted === false) {
            // throw
        }
        return $decrytped;
    }
}
