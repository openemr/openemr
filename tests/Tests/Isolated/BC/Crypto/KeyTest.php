<?php

/**
 * Isolated Key Test
 *
 * Tests BC\Crypto\Key enum methods.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\BC\Crypto;

use OpenEMR\BC\Crypto\Key;
use OpenEMR\Common\Crypto\KeySource;
use OpenEMR\Common\Crypto\KeyVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated The SUT is deprecated
 */
class KeyTest extends TestCase
{
    /**
     * @return array<string, array{KeyVersion, KeySource, Key}>
     *
     * @codeCoverageIgnore
     */
    public static function fromCryptoGenProvider(): array
    {
        return [
            // v1 ignores source
            'v1 drive' => [KeyVersion::ONE, KeySource::Drive, Key::v1],
            'v1 database' => [KeyVersion::ONE, KeySource::Database, Key::v1],

            // v2 and v3 both map to v2, ignoring source
            'v2 drive' => [KeyVersion::TWO, KeySource::Drive, Key::v2],
            'v2 database' => [KeyVersion::TWO, KeySource::Database, Key::v2],
            'v3 drive' => [KeyVersion::THREE, KeySource::Drive, Key::v2],
            'v3 database' => [KeyVersion::THREE, KeySource::Database, Key::v2],

            // v4+ have separate drive and database keys
            'v4 drive' => [KeyVersion::FOUR, KeySource::Drive, Key::v4Drive],
            'v4 database' => [KeyVersion::FOUR, KeySource::Database, Key::v4Db],
            'v5 drive' => [KeyVersion::FIVE, KeySource::Drive, Key::v5Drive],
            'v5 database' => [KeyVersion::FIVE, KeySource::Database, Key::v5Db],
            'v6 drive' => [KeyVersion::SIX, KeySource::Drive, Key::v6Drive],
            'v6 database' => [KeyVersion::SIX, KeySource::Database, Key::v6Db],
            'v7 drive' => [KeyVersion::SEVEN, KeySource::Drive, Key::v7Drive],
            'v7 database' => [KeyVersion::SEVEN, KeySource::Database, Key::v7Db],
        ];
    }

    #[DataProvider('fromCryptoGenProvider')]
    public function testFromCryptoGen(KeyVersion $version, KeySource $source, Key $expected): void
    {
        $this->assertSame($expected, Key::fromCryptoGen($version, $source));
    }

    public function testGetIdReturnsKeyIdWithEnumValue(): void
    {
        foreach (Key::cases() as $key) {
            $keyId = $key->getId();
            self::assertSame($key->value, $keyId->id);
        }
    }

    /**
     * Verify all Key cases have unique backing values.
     * Catches copy-paste errors when adding new cases.
     */
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(fn(Key $k) => $k->value, Key::cases());
        self::assertSame($values, array_unique($values), 'Duplicate backing values found');
    }

    /**
     * Verify all Key cases have non-empty backing values.
     */
    public function testAllCasesHaveNonEmptyValues(): void
    {
        foreach (Key::cases() as $key) {
            // @phpstan-ignore staticMethod.alreadyNarrowedType
            self::assertNotSame('', $key->value, "Key::{$key->name} has empty value");
        }
    }

    /**
     * Verify v4+ produce different keys for Drive vs Database.
     * These versions require source differentiation for security.
     */
    public function testVersionsFourAndAboveProduceDifferentKeysPerSource(): void
    {
        $versions = [
            KeyVersion::FOUR,
            KeyVersion::FIVE,
            KeyVersion::SIX,
            KeyVersion::SEVEN,
        ];

        foreach ($versions as $version) {
            $driveKey = Key::fromCryptoGen($version, KeySource::Drive);
            $dbKey = Key::fromCryptoGen($version, KeySource::Database);
            $this->assertNotSame(
                $driveKey,
                $dbKey,
                "Version {$version->value} should produce different keys for Drive vs Database"
            );
        }
    }

    /**
     * Verify v1-v3 produce the same key regardless of source.
     * These legacy versions don't distinguish by source.
     */
    public function testVersionsOneToThreeIgnoreSource(): void
    {
        $versions = [
            KeyVersion::ONE,
            KeyVersion::TWO,
            KeyVersion::THREE,
        ];

        foreach ($versions as $version) {
            $driveKey = Key::fromCryptoGen($version, KeySource::Drive);
            $dbKey = Key::fromCryptoGen($version, KeySource::Database);
            $this->assertSame(
                $driveKey,
                $dbKey,
                "Version {$version->value} should produce the same key regardless of source"
            );
        }
    }
}
