<?php

/**
 * Integration tests for BCCrypto decryption across all key versions.
 *
 * These tests verify that BCCrypto can correctly decrypt data encrypted
 * with all supported key versions (v1-v7). This ensures the new BCCrypto
 * implementation is backward compatible with CryptoGen.
 *
 * Tests use known keys seeded into the database and filesystem, with
 * pre-computed ciphertext, to verify decryption produces expected plaintext.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Crypto;

use OpenEMR\Common\Crypto\KeySource;
use OpenEMR\Encryption\BCCrypto;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class BCCryptoDecryptionTest extends TestCase
{
    private static CryptoFixtureManager $fixtureManager;
    private BCCrypto $crypto;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtureManager = new CryptoFixtureManager();
        self::$fixtureManager->install();
    }

    public static function tearDownAfterClass(): void
    {
        self::$fixtureManager->remove();

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->crypto = BCCrypto::instance(new NullLogger());
    }

    /**
     * Provides test cases for all key versions with drive key source.
     *
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
     * Provides test cases for versions that support database key source.
     * Only v4+ supports database key source.
     *
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
        $ciphertext = self::$fixtureManager->getCiphertext($version);
        $expectedPlaintext = self::$fixtureManager->getPlaintext();

        $result = $this->crypto->decryptStandard($ciphertext, KeySource::Drive);

        $this->assertSame(
            $expectedPlaintext,
            $result,
            "Decryption failed for version $version with drive key source"
        );
    }

    #[DataProvider('databaseKeyVersionProvider')]
    public function testDecryptionWorksForModernVersionsWithDatabaseKeys(int $version): void
    {
        $ciphertext = self::$fixtureManager->getCiphertextForDatabaseKeys($version);
        $expectedPlaintext = self::$fixtureManager->getPlaintext();

        $result = $this->crypto->decryptStandard($ciphertext, KeySource::Database);

        $this->assertSame(
            $expectedPlaintext,
            $result,
            "Decryption failed for version $version with database key source"
        );
    }

    public function testDecryptionRejectsInvalidVersion(): void
    {
        $invalidCiphertext = '999' . base64_encode('garbage data');

        $result = $this->crypto->decryptStandard($invalidCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionRejectsMalformedCiphertext(): void
    {
        // Valid version prefix but garbage base64
        $malformedCiphertext = '007not_valid_base64!!!';

        $result = $this->crypto->decryptStandard($malformedCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionRejectsTamperedHmac(): void
    {
        // Get valid v7 ciphertext and tamper with the HMAC
        $ciphertext = self::$fixtureManager->getCiphertext(7);

        // Decode, tamper with first byte of HMAC, re-encode
        $decoded = base64_decode(substr($ciphertext, 3));
        $tampered = chr(ord($decoded[0]) ^ 0xFF) . substr($decoded, 1);
        $tamperedCiphertext = '007' . base64_encode($tampered);

        $result = $this->crypto->decryptStandard($tamperedCiphertext, KeySource::Drive);

        $this->assertFalse($result);
    }

    public function testDecryptionRejectsVersionBelowMinimum(): void
    {
        $ciphertext = self::$fixtureManager->getCiphertext(3);

        // Require minimum version 5
        $result = $this->crypto->decryptStandard(
            $ciphertext,
            KeySource::Drive,
            minimumVersion: 5
        );

        $this->assertFalse($result);
    }

    public function testDecryptionAcceptsVersionAtMinimum(): void
    {
        $ciphertext = self::$fixtureManager->getCiphertext(5);
        $expectedPlaintext = self::$fixtureManager->getPlaintext();

        $result = $this->crypto->decryptStandard(
            $ciphertext,
            KeySource::Drive,
            minimumVersion: 5
        );

        $this->assertSame($expectedPlaintext, $result);
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
