<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

class PasswordBasedCrypto
{
    public function __construct(
        private KeyVersion $version,
    ) {
    }

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

        $output = $salt . $hmac . $iv . $encrypted;
        // prefix
        return $this->version->toPaddedString() . base64_encode($output);
    }

    public function decrypt(string $ciphertextWithVersion, string $password): string
    {
        $version = KeyVersion::fromPrefix($ciphertextWithVersion);

        $ciphertext = mb_substr($ciphertextWithVersion, 3, null, '8bit');
        // trim prefix and get key version

        $input = base64_decode($ciphertext, true);
        if ($input === false) {
            throw new CryptoGenException('Could not base64-decode the ciphertext');
        }

        // (hmac)
        $salt = mb_substr($input, 0, 32, '8bit');
        $rest = mb_substr($input, 32, null, '8bit');

        $preKey = hash_pbkdf2('sha384', $password, $salt, 100_000, 32, true);
        $secretKey = hash_hkdf('sha384', $preKey, 32, 'aes-256-encryption', $salt);
        $hmacKey = hash_hkdf('sha384', $preKey, 32, 'sha-384-authentication', $salt);

        $hashHmac = mb_substr($rest, 0, 48, '8bit');
        $ivLen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = mb_substr($rest, 48, $ivLen, '8bit');
        $encrypted = mb_substr($rest, ($ivLen + 48), null, '8bit');

        $expectedHmac = hash_hmac('sha384', $iv . $encrypted, $hmacKey, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hashHmac)) {
            throw new CryptoGenException('Invalid HMAC');
        }

        $output = openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $secretKey,
            OPENSSL_RAW_DATA,
            $iv,
        );
        if ($output === false) {
            throw new \Exception('Could not decrypt');
        }
        return $output;
    }
}
