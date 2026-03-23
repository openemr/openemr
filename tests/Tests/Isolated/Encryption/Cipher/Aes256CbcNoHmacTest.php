<?php

/**
 * Unit tests for Aes256CbcNoHmac cipher.
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
use OpenEMR\Encryption\Cipher\Aes256CbcNoHmac;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated This cipher is for legacy v1 decryption only - no HMAC authentication
 */
final class Aes256CbcNoHmacTest extends TestCase
{
    private CryptoFixtureManager $fixtures;

    protected function setUp(): void
    {
        // No install() needed - we only use the static test vectors
        $this->fixtures = new CryptoFixtureManager('/dev/null');
    }

    public function testDecryptsKnownCiphertextCorrectly(): void
    {
        $cipher = new Aes256CbcNoHmac(
            key: new KeyMaterial($this->fixtures->getTestKey('one')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(1));

        $result = $cipher->decrypt($rawCiphertext);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $result->wrapped);
    }

    public function testThrowsOnWrongKey(): void
    {
        $cipher = new Aes256CbcNoHmac(
            key: new KeyMaterial('wrong_key_______________________'), // 32 bytes
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(1));

        // Without HMAC, wrong key results in decryption failure (garbage or padding error)
        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('Decryption failed');
        $cipher->decrypt($rawCiphertext);
    }

    public function testThrowsOnEmptyInput(): void
    {
        $cipher = new Aes256CbcNoHmac(
            key: new KeyMaterial($this->fixtures->getTestKey('one')),
        );

        // Empty string has no IV or ciphertext
        $this->expectException(CryptoGenException::class);
        $cipher->decrypt('');
    }

    /**
     * Note: Without HMAC, corrupted ciphertext produces garbage output rather
     * than throwing. This is a known limitation of unauthenticated encryption,
     * which is why this cipher is deprecated.
     */
    public function testCorruptedCiphertextProducesGarbageNotException(): void
    {
        $cipher = new Aes256CbcNoHmac(
            key: new KeyMaterial($this->fixtures->getTestKey('one')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(1));

        // Corrupt the ciphertext (after IV = 16 bytes)
        // Without HMAC, this just produces garbage output
        $corrupted = substr($rawCiphertext, 0, 16)
            . chr(ord($rawCiphertext[16]) ^ 0xFF)
            . substr($rawCiphertext, 17);

        $result = $cipher->decrypt($corrupted);

        // It decrypts to something, just not the right thing
        self::assertNotSame(CryptoFixtureManager::PLAINTEXT, $result->wrapped);
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
