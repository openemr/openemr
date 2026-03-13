<?php

/**
 * Isolated KeyVersion Test
 *
 * Tests encryption key version enum methods.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Crypto;

use OpenEMR\Common\Crypto\KeyVersion;
use PHPUnit\Framework\TestCase;

class KeyVersionTest extends TestCase
{
    public function testToStringReturnsWordForm(): void
    {
        $this->assertSame('one', KeyVersion::ONE->toString());
        $this->assertSame('two', KeyVersion::TWO->toString());
        $this->assertSame('three', KeyVersion::THREE->toString());
        $this->assertSame('four', KeyVersion::FOUR->toString());
        $this->assertSame('five', KeyVersion::FIVE->toString());
        $this->assertSame('six', KeyVersion::SIX->toString());
        $this->assertSame('seven', KeyVersion::SEVEN->toString());
    }

    public function testToPaddedStringReturnsZeroPadded(): void
    {
        $this->assertSame('001', KeyVersion::ONE->toPaddedString());
        $this->assertSame('002', KeyVersion::TWO->toPaddedString());
        $this->assertSame('003', KeyVersion::THREE->toPaddedString());
        $this->assertSame('004', KeyVersion::FOUR->toPaddedString());
        $this->assertSame('005', KeyVersion::FIVE->toPaddedString());
        $this->assertSame('006', KeyVersion::SIX->toPaddedString());
        $this->assertSame('007', KeyVersion::SEVEN->toPaddedString());
    }

    public function testUsesLegacyStorageForVersionsOneToFour(): void
    {
        $this->assertTrue(KeyVersion::ONE->usesLegacyStorage());
        $this->assertTrue(KeyVersion::TWO->usesLegacyStorage());
        $this->assertTrue(KeyVersion::THREE->usesLegacyStorage());
        $this->assertTrue(KeyVersion::FOUR->usesLegacyStorage());
    }

    public function testUsesLegacyStorageFalseForVersionsFiveAndAbove(): void
    {
        $this->assertFalse(KeyVersion::FIVE->usesLegacyStorage());
        $this->assertFalse(KeyVersion::SIX->usesLegacyStorage());
        $this->assertFalse(KeyVersion::SEVEN->usesLegacyStorage());
    }

    public function testUsesLegacyDecryptionForVersionsOneToThree(): void
    {
        $this->assertTrue(KeyVersion::ONE->usesLegacyDecryption());
        $this->assertTrue(KeyVersion::TWO->usesLegacyDecryption());
        $this->assertTrue(KeyVersion::THREE->usesLegacyDecryption());
    }

    public function testUsesLegacyDecryptionFalseForVersionsFourAndAbove(): void
    {
        $this->assertFalse(KeyVersion::FOUR->usesLegacyDecryption());
        $this->assertFalse(KeyVersion::FIVE->usesLegacyDecryption());
        $this->assertFalse(KeyVersion::SIX->usesLegacyDecryption());
        $this->assertFalse(KeyVersion::SEVEN->usesLegacyDecryption());
    }

    public function testFromStringReturnsCorrectVersion(): void
    {
        $this->assertSame(KeyVersion::ONE, KeyVersion::fromString('one'));
        $this->assertSame(KeyVersion::TWO, KeyVersion::fromString('two'));
        $this->assertSame(KeyVersion::THREE, KeyVersion::fromString('three'));
        $this->assertSame(KeyVersion::FOUR, KeyVersion::fromString('four'));
        $this->assertSame(KeyVersion::FIVE, KeyVersion::fromString('five'));
        $this->assertSame(KeyVersion::SIX, KeyVersion::fromString('six'));
        $this->assertSame(KeyVersion::SEVEN, KeyVersion::fromString('seven'));
    }

    public function testFromStringThrowsOnInvalidVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key version: invalid');
        KeyVersion::fromString('invalid');
    }

    public function testFromStringThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        KeyVersion::fromString('');
    }

    public function testFromStringThrowsOnNumericString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        KeyVersion::fromString('1');
    }

    public function testFromPrefixExtractsVersion(): void
    {
        $this->assertSame(KeyVersion::ONE, KeyVersion::fromPrefix('001encrypted_data'));
        $this->assertSame(KeyVersion::FIVE, KeyVersion::fromPrefix('005encrypted_data'));
        $this->assertSame(KeyVersion::SEVEN, KeyVersion::fromPrefix('007encrypted_data'));
    }

    public function testFromPrefixThrowsOnShortString(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Input string must be at least 3 bytes long');
        KeyVersion::fromPrefix('01');
    }

    public function testFromPrefixThrowsOnNonNumericPrefix(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Invalid KeyVersion prefix');
        KeyVersion::fromPrefix('abc123');
    }

    public function testFromPrefixThrowsOnInvalidVersionNumber(): void
    {
        $this->expectException(\ValueError::class);
        KeyVersion::fromPrefix('999data');
    }

    public function testFromPrefixThrowsOnZeroVersion(): void
    {
        $this->expectException(\ValueError::class);
        KeyVersion::fromPrefix('000data');
    }

    public function testRoundTripStringConversion(): void
    {
        foreach (KeyVersion::cases() as $version) {
            $stringForm = $version->toString();
            $restored = KeyVersion::fromString($stringForm);
            $this->assertSame($version, $restored);
        }
    }

    public function testRoundTripPrefixConversion(): void
    {
        foreach (KeyVersion::cases() as $version) {
            $padded = $version->toPaddedString();
            $restored = KeyVersion::fromPrefix($padded . 'data');
            $this->assertSame($version, $restored);
        }
    }
}
