<?php

/**
 * Integration tests for DatabaseCache against a real database.
 *
 * Requires Docker MySQL to be running with the oidc_cache table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\Oidc;

use OpenEMR\Common\Auth\Oidc\Cache\DatabaseCache;
use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheInvalidArgumentException;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DatabaseCacheTest extends TestCase
{
    private DatabaseCache $cache;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->cache = new DatabaseCache();
        $this->cleanTable();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTable();
        }
    }

    private function cleanTable(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . DatabaseCache::TABLE_NAME . '`',
        );
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('test-key', 'test-value');

        self::assertSame('test-value', $this->cache->get('test-key'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        self::assertNull($this->cache->get('nonexistent'));
        self::assertSame('fallback', $this->cache->get('nonexistent', 'fallback'));
    }

    public function testSetOverwritesExistingKey(): void
    {
        $this->cache->set('key', 'first');
        $this->cache->set('key', 'second');

        self::assertSame('second', $this->cache->get('key'));
    }

    public function testSetWithIntTtl(): void
    {
        $this->cache->set('ttl-key', 'data', 3600);

        self::assertSame('data', $this->cache->get('ttl-key'));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $this->cache->set('interval-key', 'data', new \DateInterval('PT1H'));

        self::assertSame('data', $this->cache->get('interval-key'));
    }

    public function testSetWithZeroTtlExpires(): void
    {
        $this->cache->set('expired-key', 'data', 0);

        self::assertNull($this->cache->get('expired-key'));
    }

    public function testSetWithNegativeTtlExpires(): void
    {
        $this->cache->set('negative-ttl', 'data', -1);

        self::assertNull($this->cache->get('negative-ttl'));
    }

    public function testDelete(): void
    {
        $this->cache->set('to-delete', 'value');
        $result = $this->cache->delete('to-delete');

        self::assertTrue($result);
        self::assertNull($this->cache->get('to-delete'));
    }

    public function testDeleteNonexistentReturnsTrue(): void
    {
        self::assertTrue($this->cache->delete('nonexistent'));
    }

    public function testClear(): void
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);

        $result = $this->cache->clear();

        self::assertTrue($result);
        self::assertNull($this->cache->get('a'));
        self::assertNull($this->cache->get('b'));
    }

    public function testHas(): void
    {
        self::assertFalse($this->cache->has('missing'));

        $this->cache->set('present', 'value');

        self::assertTrue($this->cache->has('present'));
    }

    public function testGetMultiple(): void
    {
        $this->cache->set('m1', 'one');
        $this->cache->set('m2', 'two');

        /** @var array<string, mixed> $results */
        $results = iterator_to_array($this->cache->getMultiple(['m1', 'm2', 'm3'], 'default'));

        self::assertSame('one', $results['m1']);
        self::assertSame('two', $results['m2']);
        self::assertSame('default', $results['m3']);
    }

    public function testSetMultiple(): void
    {
        $result = $this->cache->setMultiple(['s1' => 'val1', 's2' => 'val2']);

        self::assertTrue($result);
        self::assertSame('val1', $this->cache->get('s1'));
        self::assertSame('val2', $this->cache->get('s2'));
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('d1', 'one');
        $this->cache->set('d2', 'two');
        $this->cache->set('d3', 'three');

        $result = $this->cache->deleteMultiple(['d1', 'd3']);

        self::assertTrue($result);
        self::assertNull($this->cache->get('d1'));
        self::assertSame('two', $this->cache->get('d2'));
        self::assertNull($this->cache->get('d3'));
    }

    public function testPurgeExpiredRemovesOnlyExpiredEntries(): void
    {
        // Insert an already-expired entry directly
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `' . DatabaseCache::TABLE_NAME . '` (`cache_key`, `cache_value`, `expires_at`) VALUES (?, ?, ?)',
            ['expired', serialize(new \OpenEMR\Common\Auth\Oidc\Cache\CacheEntry('old')), '2000-01-01 00:00:00'],
        );

        $this->cache->set('valid', 'still-here', 3600);

        $this->cache->purgeExpired();

        self::assertNull($this->cache->get('expired'));
        self::assertSame('still-here', $this->cache->get('valid'));
    }

    public function testPurgeExpiredKeepsNullExpiryEntries(): void
    {
        $this->cache->set('no-expiry', 'forever');

        $this->cache->purgeExpired();

        self::assertSame('forever', $this->cache->get('no-expiry'));
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function variousValueTypesProvider(): array
    {
        return [
            'string' => ['hello'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'array' => [['nested' => [1, 2, 3]]],
            'null' => [null],
        ];
    }

    #[DataProvider('variousValueTypesProvider')]
    public function testRoundTripsVariousTypes(mixed $value): void
    {
        $this->cache->set('typed', $value);

        self::assertSame($value, $this->cache->get('typed'));
    }

    public function testRejectsEmptyKey(): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);

        $this->cache->get('');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function reservedCharacterKeyProvider(): array
    {
        return [
            'curly open' => ['key{bad'],
            'curly close' => ['key}bad'],
            'paren open' => ['key(bad'],
            'paren close' => ['key)bad'],
            'slash' => ['key/bad'],
            'backslash' => ['key\\bad'],
            'at sign' => ['key@bad'],
            'colon' => ['key:bad'],
        ];
    }

    #[DataProvider('reservedCharacterKeyProvider')]
    public function testRejectsKeysWithReservedCharacters(string $key): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);

        $this->cache->get($key);
    }

    public function testRejectsKeyExceedingMaxLength(): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);

        $this->cache->get(str_repeat('a', 256));
    }
}
