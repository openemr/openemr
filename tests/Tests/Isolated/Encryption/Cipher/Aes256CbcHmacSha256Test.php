<?php

/**
 * Unit tests for Aes256CbcHmacSha256 cipher.
 *
 * Uses known test vectors from CryptoFixtureManager to verify decryption
 * works correctly in isolation without database or filesystem dependencies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Cipher;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha256;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated This cipher is for legacy v2-3 decryption only
 */
final class Aes256CbcHmacSha256Test extends TestCase
{
    private CryptoFixtureManager $fixtures;

    protected function setUp(): void
    {
        // No install() needed - we only use the static test vectors
        $this->fixtures = new CryptoFixtureManager('/dev/null');
    }

    /**
     * v2 and v3 both use 'two' keys (v3 has no separate keys).
     *
     * @return iterable<string, array{version: int}>
     */
    public static function versionProvider(): iterable
    {
        yield 'version 2' => ['version' => 2];
        yield 'version 3' => ['version' => 3];
    }

    #[DataProvider('versionProvider')]
    public function testDecryptsKnownCiphertextCorrectly(int $version): void
    {
        // Both v2 and v3 use 'two' keys
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext($version));

        $result = $cipher->decrypt($rawCiphertext);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $result->wrapped);
    }

    public function testThrowsOnTamperedHmac(): void
    {
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(2));

        // Tamper with HMAC (first 32 bytes for SHA256)
        $tampered = chr(ord($rawCiphertext[0]) ^ 0xFF) . substr($rawCiphertext, 1);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt($tampered);
    }

    public function testThrowsOnTamperedCiphertext(): void
    {
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(2));

        // Tamper with ciphertext (after HMAC + IV = 32 + 16 = 48 bytes)
        $tampered = substr($rawCiphertext, 0, 48)
            . chr(ord($rawCiphertext[48]) ^ 0xFF)
            . substr($rawCiphertext, 49);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt($tampered);
    }

    public function testThrowsOnWrongHmacKey(): void
    {
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
            hmacKey: new KeyMaterial('wrong_hmac_key__________________'), // 32 bytes
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(2));

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt($rawCiphertext);
    }

    public function testThrowsOnWrongEncryptionKey(): void
    {
        // Correct HMAC key (so HMAC passes) but wrong encryption key
        // This causes openssl_decrypt to fail due to invalid PKCS7 padding
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial('wrong_key_______________________'), // 32 bytes
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(2));

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('Decryption failed despite HMAC validating');
        $cipher->decrypt($rawCiphertext);
    }

    public function testThrowsOnTruncatedInput(): void
    {
        $cipher = new Aes256CbcHmacSha256(
            key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
        );

        // Less than HMAC_LENGTH + IV_LENGTH = 32 + 16 = 48 bytes
        $truncated = str_repeat("\x00", 24);

        $this->expectException(CryptoGenException::class);
        $cipher->decrypt($truncated);
    }

    /**
     * Strip version prefix and base64 decode to get raw ciphertext.
     */
    private function extractRawCiphertext(string $encoded): string
    {
        $raw = base64_decode(substr($encoded, 3), strict: true);
        self::assertIsString($raw, 'Test vector base64 decode failed');
        return $raw;
    }
}
