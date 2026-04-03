<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Cache;

use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheException;
use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheInvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilesystemCache::class)]
final class FilesystemCacheTest extends TestCase
{
    private string $cacheDir;
    private FilesystemCache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/oidc_cache_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new FilesystemCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir($this->cacheDir);
    }

    public function testConstructorRejectsNonexistentDirectory(): void
    {
        $this->expectException(OidcCacheException::class);
        $this->expectExceptionMessage('Cache directory does not exist');

        new FilesystemCache('/nonexistent/path/that/does/not/exist');
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        self::assertNull($this->cache->get('nonexistent'));
        self::assertSame('fallback', $this->cache->get('nonexistent', 'fallback'));
    }

    public function testSetAndGet(): void
    {
        self::assertTrue($this->cache->set('key1', 'value1'));
        self::assertSame('value1', $this->cache->get('key1'));
    }

    public function testSetOverwritesExistingValue(): void
    {
        $this->cache->set('key1', 'original');
        $this->cache->set('key1', 'updated');

        self::assertSame('updated', $this->cache->get('key1'));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function cacheableValueProvider(): array
    {
        return [
            'string' => ['hello'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'null' => [null],
            'array' => [['a' => 1, 'b' => [2, 3]]],
            'empty string' => [''],
            'zero' => [0],
            'empty array' => [[]],
        ];
    }

    #[DataProvider('cacheableValueProvider')]
    public function testCachesVariousTypes(mixed $value): void
    {
        $this->cache->set('typed', $value);
        self::assertSame($value, $this->cache->get('typed'));
    }

    public function testSetWithIntTtl(): void
    {
        $this->cache->set('short-lived', 'data', 3600);
        self::assertSame('data', $this->cache->get('short-lived'));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $this->cache->set('interval-lived', 'data', new \DateInterval('PT1H'));
        self::assertSame('data', $this->cache->get('interval-lived'));
    }

    public function testExpiredEntryReturnsDefault(): void
    {
        // TTL of 0 or negative means already expired
        $this->cache->set('expired', 'data', 0);
        self::assertNull($this->cache->get('expired'));
    }

    public function testNegativeTtlReturnsDefault(): void
    {
        $this->cache->set('negative-ttl', 'data', -10);
        self::assertNull($this->cache->get('negative-ttl'));
    }

    public function testNullTtlMeansNoExpiration(): void
    {
        $this->cache->set('forever', 'data', null);
        self::assertSame('data', $this->cache->get('forever'));
    }

    public function testDelete(): void
    {
        $this->cache->set('to-delete', 'data');
        self::assertTrue($this->cache->delete('to-delete'));
        self::assertNull($this->cache->get('to-delete'));
    }

    public function testDeleteNonexistentKeyReturnsTrue(): void
    {
        self::assertTrue($this->cache->delete('never-existed'));
    }

    public function testClear(): void
    {
        $this->cache->set('a', '1');
        $this->cache->set('b', '2');
        $this->cache->set('c', '3');

        self::assertTrue($this->cache->clear());

        self::assertNull($this->cache->get('a'));
        self::assertNull($this->cache->get('b'));
        self::assertNull($this->cache->get('c'));
    }

    public function testHas(): void
    {
        self::assertFalse($this->cache->has('key'));
        $this->cache->set('key', 'value');
        self::assertTrue($this->cache->has('key'));
    }

    public function testHasReturnsFalseForExpired(): void
    {
        $this->cache->set('expired', 'data', 0);
        self::assertFalse($this->cache->has('expired'));
    }

    public function testGetMultiple(): void
    {
        $this->cache->set('m1', 'v1');
        $this->cache->set('m2', 'v2');

        $results = $this->cache->getMultiple(['m1', 'm2', 'm3'], 'default');

        self::assertSame(['m1' => 'v1', 'm2' => 'v2', 'm3' => 'default'], $results);
    }

    public function testSetMultiple(): void
    {
        self::assertTrue($this->cache->setMultiple(['s1' => 'v1', 's2' => 'v2']));

        self::assertSame('v1', $this->cache->get('s1'));
        self::assertSame('v2', $this->cache->get('s2'));
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('d1', 'v1');
        $this->cache->set('d2', 'v2');

        self::assertTrue($this->cache->deleteMultiple(['d1', 'd2']));

        self::assertNull($this->cache->get('d1'));
        self::assertNull($this->cache->get('d2'));
    }

    public function testEmptyKeyThrows(): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key must not be empty');

        $this->cache->get('');
    }

    /**
     * @return array<string, array{string}>
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
    public function testReservedCharacterKeysThrow(string $key): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key contains reserved characters');

        $this->cache->get($key);
    }

    public function testCorruptedFileReturnsDefault(): void
    {
        // Write a corrupted cache file directly
        $path = $this->cacheDir . '/' . hash('sha256', 'corrupted') . '.cache';
        file_put_contents($path, 'not-valid-serialized-data');

        self::assertNull($this->cache->get('corrupted'));
        // File should be cleaned up
        self::assertFileDoesNotExist($path);
    }

    public function testConcurrentSafeWrite(): void
    {
        // Ensure atomic writes via temp file + rename
        $this->cache->set('atomic', 'value');
        self::assertSame('value', $this->cache->get('atomic'));

        // No orphan temp files
        $tempFiles = glob($this->cacheDir . '/*.tmp');
        self::assertSame([], $tempFiles);
    }
}
