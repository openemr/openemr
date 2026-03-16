<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

class PasswordBasedCrypto
{
    public function encrypt(string $plaintext, string $password): string
    {
        $salt = random_bytes(32);
        $preKey = hash_pbkdf2('sha384', $password, $salt, 100_000, 32, true);

        $secretKey = hash_hkdf('sha384', $preKey, 32, 'aes-256-encryption', $salt);
        $hmacKey = hash_hkdf('sha384', $preKey, 32, 'sha-384-authentication', $salt);

        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        $encrypted = openssl_encrypt(
            $plaintext,
            'aes-256-cbc',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv,
        );

        $hmac = hash_hmac('sha384', $iv . $encrypted, $hmacKey, true);

        $output = $hmac . $iv . $encrypted;
        return base64_encode($output);
    }

    public function decrypt(string $ciphertext, string $password): string
    {
        return '';
    }
}
