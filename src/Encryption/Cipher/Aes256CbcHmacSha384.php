<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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

    public const KEY_LENGTH = 32; // openssl_cipher_key_length('aes-256-cbc')

    public function __construct(
        private KeyMaterial $key,
        private KeyMaterial $hmacKey,
    ) {
    }

    public function decrypt(Ciphertext $ciphertext): Plaintext
    {
        $ciphertext = $ciphertext->value;
        $hmac = substr($ciphertext, 0, self::HMAC_LENGTH);
        $iv = substr($ciphertext, self::HMAC_LENGTH, self::IV_LENGTH);
        $data = substr($ciphertext, self::HMAC_LENGTH + self::IV_LENGTH);

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

    public function encrypt(Plaintext $plaintext): Ciphertext
    {
        $iv = random_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt(
            $plaintext->bytes,
            'aes-256-cbc',
            $this->key->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmac = hash_hmac('sha384', $iv . $encrypted, $this->hmacKey->key, true);

        return new Ciphertext(sprintf('%s%s%s', $hmac, $iv, $encrypted));
    }
}
