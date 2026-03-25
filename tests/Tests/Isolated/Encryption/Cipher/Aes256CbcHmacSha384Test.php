<?php

/**
 * Unit tests for Aes256CbcHmacSha384 cipher.
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
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha384;
use OpenEMR\Encryption\Ciphertext;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Aes256CbcHmacSha384Test extends TestCase
{
    use CipherTestHelperTrait;

    /**
     * @return iterable<string, array{version: int, keyPrefix: string}>
     */
    public static function versionProvider(): iterable
    {
        yield 'version 4' => ['version' => 4, 'keyPrefix' => 'four'];
        yield 'version 5' => ['version' => 5, 'keyPrefix' => 'five'];
        yield 'version 6' => ['version' => 6, 'keyPrefix' => 'six'];
        yield 'version 7' => ['version' => 7, 'keyPrefix' => 'seven'];
    }

    #[DataProvider('versionProvider')]
    public function testDecryptsKnownCiphertextCorrectly(int $version, string $keyPrefix): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial($this->fixtures->getTestKey($keyPrefix . 'a')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey($keyPrefix . 'b')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext($version));

        $result = $cipher->decrypt($rawCiphertext);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $result->wrapped);
    }

    public function testThrowsOnTamperedHmac(): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial($this->fixtures->getTestKey('sevena')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('sevenb')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(7))
            ->wrapped;

        // Tamper with HMAC (first 48 bytes)
        $tampered = chr(ord($rawCiphertext[0]) ^ 0xFF) . substr($rawCiphertext, 1);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt(new Ciphertext($tampered));
    }

    public function testThrowsOnTamperedCiphertext(): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial($this->fixtures->getTestKey('sevena')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('sevenb')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(7))
            ->wrapped;

        // Tamper with ciphertext (after HMAC + IV = 48 + 16 = 64 bytes)
        $tampered = substr($rawCiphertext, 0, 64)
            . chr(ord($rawCiphertext[64]) ^ 0xFF)
            . substr($rawCiphertext, 65);

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt(new Ciphertext($tampered));
    }

    public function testThrowsOnWrongEncryptionKey(): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial('wrong_key_______________________'), // 32 bytes
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('sevenb')),
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(7));

        // Wrong encryption key means HMAC will fail (HMAC covers the ciphertext
        // that was produced with the correct key)
        $this->expectException(CryptoGenException::class);
        $cipher->decrypt($rawCiphertext);
    }

    public function testThrowsOnWrongHmacKey(): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial($this->fixtures->getTestKey('sevena')),
            hmacKey: new KeyMaterial('wrong_hmac_key__________________'), // 32 bytes
        );

        $rawCiphertext = $this->extractRawCiphertext($this->fixtures->getCiphertext(7));

        $this->expectException(CryptoGenException::class);
        $this->expectExceptionMessage('HMAC invalid');
        $cipher->decrypt($rawCiphertext);
    }

    public function testThrowsOnTruncatedInput(): void
    {
        $cipher = new Aes256CbcHmacSha384(
            key: new KeyMaterial($this->fixtures->getTestKey('sevena')),
            hmacKey: new KeyMaterial($this->fixtures->getTestKey('sevenb')),
        );

        // Less than HMAC_LENGTH + IV_LENGTH = 48 + 16 = 64 bytes
        $truncated = str_repeat("\x00", 32);

        $this->expectException(CryptoGenException::class);
        $cipher->decrypt(new Ciphertext($truncated));
    }
}
