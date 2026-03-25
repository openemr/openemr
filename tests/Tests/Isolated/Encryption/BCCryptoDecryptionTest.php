<?php

/**
 * Isolated tests for BCCrypto decryption across all key versions.
 *
 * These tests verify BCCrypto decryption without requiring a database or
 * filesystem. Test keys are injected directly via a constructed keychain,
 * making these tests fast and portable.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use OpenEMR\Common\Crypto\KeySource;
use OpenEMR\Encryption\BCCrypto;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha256;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha384;
use OpenEMR\Encryption\Cipher\Aes256CbcNoHmac;
use OpenEMR\Encryption\Keys\Id;
use OpenEMR\Encryption\Keys\Keychain;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class BCCryptoDecryptionTest extends TestCase
{
    private static CryptoFixtureManager $fixtures;
    private BCCrypto $crypto;

    public static function setUpBeforeClass(): void
    {
        // No siteDir needed - we only use static data accessors
        self::$fixtures = new CryptoFixtureManager('/dev/null');
    }

    protected function setUp(): void
    {
        $keychain = $this->buildKeychain();
        $this->crypto = new BCCrypto(
            $keychain,
            new NullLogger(),
            new Id('seven-drive'),
        );
    }

    private function buildKeychain(): Keychain
    {
        $f = self::$fixtures;
        $keychain = new Keychain();

        // v1: single key, no HMAC
        $keychain->addCipher(new Id('one'), new Aes256CbcNoHmac(
            new KeyMaterial($f->getTestKey('one'))
        ));

        // v2 & v3: encryption + HMAC (v3 shares v2 keys)
        $keychain->addCipher(new Id('two'), new Aes256CbcHmacSha256(
            key: new KeyMaterial($f->getTestKey('twoa')),
            hmacKey: new KeyMaterial($f->getTestKey('twob'))
        ));

        // v4-7: SHA384 HMAC for both drive and database keys
        foreach (['four', 'five', 'six', 'seven'] as $version) {
            $keychain->addCipher(new Id("{$version}-drive"), new Aes256CbcHmacSha384(
                key: new KeyMaterial($f->getTestKey("{$version}a")),
                hmacKey: new KeyMaterial($f->getTestKey("{$version}b"))
            ));
            $keychain->addCipher(new Id("{$version}-db"), new Aes256CbcHmacSha384(
                key: new KeyMaterial($f->getDbKey("{$version}a")),
                hmacKey: new KeyMaterial($f->getDbKey("{$version}b"))
            ));
        }

        return $keychain;
    }

    /**
     * @return iterable<string, array{version: int}>
     */
    public static function driveKeyVersionProvider(): iterable
    {
        yield 'version 1' => ['version' => 1];
        yield 'version 2' => ['version' => 2];
        yield 'version 3' => ['version' => 3];
        yield 'version 4' => ['version' => 4];
        yield 'version 5' => ['version' => 5];
        yield 'version 6' => ['version' => 6];
        yield 'version 7' => ['version' => 7];
    }

    /**
     * @return iterable<string, array{version: int}>
     */
    public static function databaseKeyVersionProvider(): iterable
    {
        yield 'version 4' => ['version' => 4];
        yield 'version 5' => ['version' => 5];
        yield 'version 6' => ['version' => 6];
        yield 'version 7' => ['version' => 7];
    }

    #[DataProvider('driveKeyVersionProvider')]
    public function testDecryptionWorksForAllVersionsWithDriveKeys(int $version): void
    {
        $ciphertext = self::$fixtures->getCiphertext($version);
        $expected = self::$fixtures->getPlaintext();

        $result = $this->crypto->decryptStandard($ciphertext, KeySource::Drive);

        $this->assertSame($expected, $result, "Decryption failed for version $version with drive keys");
    }

    #[DataProvider('databaseKeyVersionProvider')]
    public function testDecryptionWorksForModernVersionsWithDatabaseKeys(int $version): void
    {
        $ciphertext = self::$fixtures->getCiphertextForDatabaseKeys($version);
        $expected = self::$fixtures->getPlaintext();

        $result = $this->crypto->decryptStandard($ciphertext, KeySource::Database);

        $this->assertSame($expected, $result, "Decryption failed for version $version with database keys");
    }

    public function testDecryptionRejectsInvalidVersion(): void
    {
        $invalidCiphertext = '999' . base64_encode('garbage data');

        $result = $this->crypto->decryptStandard($invalidCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionRejectsMalformedCiphertext(): void
    {
        $malformedCiphertext = '007not_valid_base64!!!';

        $result = $this->crypto->decryptStandard($malformedCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionRejectsTamperedHmac(): void
    {
        $ciphertext = self::$fixtures->getCiphertext(7);

        $decoded = base64_decode(substr($ciphertext, 3));
        $tampered = chr(ord($decoded[0]) ^ 0xFF) . substr($decoded, 1);
        $tamperedCiphertext = '007' . base64_encode($tampered);

        $result = $this->crypto->decryptStandard($tamperedCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionHandlesEmptyString(): void
    {
        $result = $this->crypto->decryptStandard('', KeySource::Drive);

        $this->assertSame('', $result);
    }

    public function testDecryptionHandlesNull(): void
    {
        $result = $this->crypto->decryptStandard(null, KeySource::Drive);

        $this->assertSame('', $result);
    }
}
