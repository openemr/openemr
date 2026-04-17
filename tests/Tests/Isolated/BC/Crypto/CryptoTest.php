<?php

/**
 * Isolated Crypto Test
 *
 * Tests BC\Crypto\Crypto class methods using CryptoFixtureManager test vectors.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC\Crypto;

use OpenEMR\BC\Crypto\Crypto;
use OpenEMR\BC\Crypto\Key;
use OpenEMR\Common\Crypto\KeySource;
use OpenEMR\Common\Crypto\KeyVersion;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha256;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha384;
use OpenEMR\Encryption\Cipher\Aes256CbcNoHmac;
use OpenEMR\Encryption\Keys\Keychain;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @deprecated The SUT is deprecated
 */
final class CryptoTest extends TestCase
{
    private CryptoFixtureManager $fixtures;
    private Keychain $keychain;
    private Crypto $crypto;

    protected function setUp(): void
    {
        $this->fixtures = new CryptoFixtureManager('/dev/null');
        $this->keychain = $this->buildKeychain();
        $this->crypto = new Crypto($this->keychain, new NullLogger());
    }

    private function buildKeychain(): Keychain
    {
        $keychain = new Keychain();

        // v1: AES-256-CBC without HMAC
        $keychain->registerCipher(
            Key::v1->getId(),
            new Aes256CbcNoHmac(new KeyMaterial($this->fixtures->getTestKey('one'))),
        );

        // v2/v3: AES-256-CBC with HMAC-SHA256
        $keychain->registerCipher(
            Key::v2->getId(),
            new Aes256CbcHmacSha256(
                key: new KeyMaterial($this->fixtures->getTestKey('twoa')),
                hmacKey: new KeyMaterial($this->fixtures->getTestKey('twob')),
            ),
        );

        // v4+: AES-256-CBC with HMAC-SHA384 (drive keys only)
        foreach (['four', 'five', 'six', 'seven'] as $version) {
            $keychain->registerCipher(
                Key::fromCryptoGen(
                    KeyVersion::fromString($version),
                    KeySource::Drive,
                )->getId(),
                new Aes256CbcHmacSha384(
                    key: new KeyMaterial($this->fixtures->getTestKey($version . 'a')),
                    hmacKey: new KeyMaterial($this->fixtures->getTestKey($version . 'b')),
                ),
            );
        }

        return $keychain;
    }

    public function testEncryptStandardReturnsEmptyStringForNull(): void
    {
        self::assertSame('', $this->crypto->encryptStandard(null));
    }

    public function testEncryptStandardReturnsEmptyStringForEmptyString(): void
    {
        self::assertSame('', $this->crypto->encryptStandard(''));
    }

    public function testEncryptStandardProducesVersionSevenPrefix(): void
    {
        $encrypted = $this->crypto->encryptStandard('test data');

        self::assertStringStartsWith('007', $encrypted);
    }

    public function testEncryptStandardProducesDecryptableOutput(): void
    {
        $plaintext = CryptoFixtureManager::PLAINTEXT;

        $encrypted = $this->crypto->encryptStandard($plaintext);
        $decrypted = $this->crypto->decryptStandard($encrypted);

        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptStandardProducesDifferentOutputEachCall(): void
    {
        $plaintext = 'test data';

        $encrypted1 = $this->crypto->encryptStandard($plaintext);
        $encrypted2 = $this->crypto->encryptStandard($plaintext);

        // Different IVs should produce different ciphertext
        self::assertNotSame($encrypted1, $encrypted2);

        // But both should decrypt to the same value
        self::assertSame($plaintext, $this->crypto->decryptStandard($encrypted1));
        self::assertSame($plaintext, $this->crypto->decryptStandard($encrypted2));
    }

    public function testDecryptStandardReturnsEmptyStringForNull(): void
    {
        self::assertSame('', $this->crypto->decryptStandard(null));
    }

    public function testDecryptStandardReturnsEmptyStringForEmptyString(): void
    {
        self::assertSame('', $this->crypto->decryptStandard(''));
    }

    /**
     * @return iterable<string, array{version: int}>
     *
     * @codeCoverageIgnore
     */
    public static function versionProvider(): iterable
    {
        yield 'version 1' => ['version' => 1];
        yield 'version 2' => ['version' => 2];
        yield 'version 3' => ['version' => 3];
        yield 'version 4' => ['version' => 4];
        yield 'version 5' => ['version' => 5];
        yield 'version 6' => ['version' => 6];
        yield 'version 7' => ['version' => 7];
    }

    #[DataProvider('versionProvider')]
    public function testDecryptStandardDecryptsKnownCiphertext(int $version): void
    {
        $ciphertext = $this->fixtures->getCiphertext($version);

        $decrypted = $this->crypto->decryptStandard($ciphertext);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $decrypted);
    }

    public function testDecryptStandardReturnsFalseForInvalidData(): void
    {
        $result = $this->crypto->decryptStandard('not valid encrypted data');

        self::assertFalse($result);
    }

    public function testDecryptStandardReturnsFalseForTamperedCiphertext(): void
    {
        $ciphertext = $this->fixtures->getCiphertext(7);
        // Tamper with the ciphertext (after the 3-byte prefix)
        $tampered = substr($ciphertext, 0, 10) . 'X' . substr($ciphertext, 11);

        $result = $this->crypto->decryptStandard($tampered);

        self::assertFalse($result);
    }

    public function testDecryptStandardRespectsMinimumVersion(): void
    {
        // v4 ciphertext should fail with minimumVersion=5
        $ciphertextV4 = $this->fixtures->getCiphertext(4);

        $result = $this->crypto->decryptStandard($ciphertextV4, minimumVersion: 5);

        self::assertFalse($result);
    }

    public function testDecryptStandardAllowsAtMinimumVersion(): void
    {
        // v5 ciphertext should succeed with minimumVersion=5
        $ciphertextV5 = $this->fixtures->getCiphertext(5);

        $result = $this->crypto->decryptStandard($ciphertextV5, minimumVersion: 5);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $result);
    }

    public function testDecryptStandardAllowsAboveMinimumVersion(): void
    {
        // v7 ciphertext should succeed with minimumVersion=5
        $ciphertextV7 = $this->fixtures->getCiphertext(7);

        $result = $this->crypto->decryptStandard($ciphertextV7, minimumVersion: 5);

        self::assertSame(CryptoFixtureManager::PLAINTEXT, $result);
    }

    public function testCryptCheckStandardReturnsFalseForNull(): void
    {
        self::assertFalse($this->crypto->cryptCheckStandard(null));
    }

    public function testCryptCheckStandardReturnsFalseForInvalidFormat(): void
    {
        self::assertFalse($this->crypto->cryptCheckStandard('not encrypted'));
    }

    public function testCryptCheckStandardReturnsFalseForEmptyString(): void
    {
        self::assertFalse($this->crypto->cryptCheckStandard(''));
    }

    #[DataProvider('versionProvider')]
    public function testCryptCheckStandardReturnsTrueForValidCiphertext(int $version): void
    {
        $ciphertext = $this->fixtures->getCiphertext($version);

        self::assertTrue($this->crypto->cryptCheckStandard($ciphertext));
    }

    public function testCryptCheckStandardReturnsTrueForFreshlyEncryptedData(): void
    {
        $encrypted = $this->crypto->encryptStandard('test data');

        self::assertTrue($this->crypto->cryptCheckStandard($encrypted));
    }
}
