<?php

/**
 * Version 2/3 password-based decryption strategy
 *
 * V2/V3 format: hmac(32) + iv(16) + ciphertext
 * Key derivation: sha256(password) as binary (32 bytes)
 * HMAC-SHA256 authentication
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use SensitiveParameter;

final class V2V3DecryptionStrategy implements PasswordDecryptionStrategyInterface
{
    private const CIPHER = 'aes-256-cbc';
    private const IV_LENGTH = 16;
    private const HMAC_LENGTH = 32; // sha256 raw output
    private const MIN_CIPHERTEXT_LENGTH = 16; // one AES block

    public function decrypt(
        string $payload,
        #[SensitiveParameter] string $password,
    ): string {
        $hmac = mb_substr($payload, 0, self::HMAC_LENGTH, '8bit');
        $iv = mb_substr($payload, self::HMAC_LENGTH, self::IV_LENGTH, '8bit');
        $encrypted = mb_substr($payload, self::HMAC_LENGTH + self::IV_LENGTH, null, '8bit');

        // V2/V3 used sha256 binary as key
        $key = hash('sha256', $password, true);

        $expectedHmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        if (!hash_equals(known_string: $expectedHmac, user_string: $hmac)) {
            throw new CryptoGenException('Invalid HMAC');
        }

        $output = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
        );

        // @codeCoverageIgnoreStart
        // Unreachable in practice: if HMAC validates, the ciphertext is intact
        if ($output === false) {
            throw new CryptoGenException('Could not decrypt data');
        }
        // @codeCoverageIgnoreEnd

        return $output;
    }

    public function getMinPayloadLength(): int
    {
        return self::HMAC_LENGTH + self::IV_LENGTH + self::MIN_CIPHERTEXT_LENGTH;
    }
}
