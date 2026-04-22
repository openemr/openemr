<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Cache;

use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheInvalidArgumentException;
use OpenEMR\Common\Auth\Oidc\Cache\RedisCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * In-memory-backed \Redis mock pattern. Each test exercises the real
 * RedisCache logic; the mock is wired with willReturnCallback to simulate
 * a Redis server against the private $store/$ttls arrays.
 */
final class RedisCacheTest extends TestCase
{
    /** @var \Redis&MockObject */
    private \Redis $client;

    private RedisCache $cache;

    /** @var array<string, string> */
    private array $store = [];

    /** @var array<string, int> */
    private array $ttls = [];

    protected function setUp(): void
    {
        $this->store = [];
        $this->ttls = [];

        $this->client = $this->getMockBuilder(\Redis::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'set', 'setex', 'del', 'exists', 'scan'])
            ->getMock();

        $this->wireMock();

        $this->cache = new RedisCache($this->client);
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        self::assertNull($this->cache->get('missing'));
        self::assertSame('default', $this->cache->get('missing', 'default'));
    }

    public function testGetReturnsCachedValue(): void
    {
        $this->cache->set('hit', 'cached-value');
        self::assertSame('cached-value', $this->cache->get('hit'));
    }

    public function testGetHandlesFalseValue(): void
    {
        $this->cache->set('false-val', false);
        self::assertFalse($this->cache->get('false-val', 'not-false'));
    }

    public function testSetWithoutTtl(): void
    {
        self::assertTrue($this->cache->set('key1', 'value1'));
        self::assertSame('value1', $this->cache->get('key1'));
        self::assertNull($this->getTtl('oidc_cache:key1'));
    }

    public function testSetWithIntTtl(): void
    {
        self::assertTrue($this->cache->set('key2', 'value2', 3600));
        self::assertSame('value2', $this->cache->get('key2'));
        self::assertSame(3600, $this->getTtl('oidc_cache:key2'));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        self::assertTrue($this->cache->set('key3', 'value3', new \DateInterval('PT1H')));
        self::assertSame('value3', $this->cache->get('key3'));
        $ttl = $this->getTtl('oidc_cache:key3');
        self::assertNotNull($ttl);
        self::assertGreaterThanOrEqual(3598, $ttl);
        self::assertLessThanOrEqual(3602, $ttl);
    }

    public function testSetWithZeroTtlDeletes(): void
    {
        $this->cache->set('zero-ttl', 'data');
        $this->cache->set('zero-ttl', 'new-data', 0);
        self::assertNull($this->cache->get('zero-ttl'));
    }

    public function testSetWithNegativeTtlDeletes(): void
    {
        $this->cache->set('neg-ttl', 'data');
        $this->cache->set('neg-ttl', 'new-data', -5);
        self::assertNull($this->cache->get('neg-ttl'));
    }

    public function testDelete(): void
    {
        $this->cache->set('to-delete', 'data');
        self::assertTrue($this->cache->delete('to-delete'));
        self::assertNull($this->cache->get('to-delete'));
    }

    public function testHasReturnsTrueWhenExists(): void
    {
        $this->cache->set('existing', 'value');
        self::assertTrue($this->cache->has('existing'));
    }

    public function testHasReturnsFalseWhenMissing(): void
    {
        self::assertFalse($this->cache->has('missing'));
    }

    public function testEmptyKeyThrows(): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key must not be empty');

        $this->cache->get('');
    }

    public function testReservedCharacterKeyThrows(): void
    {
        $this->expectException(OidcCacheInvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key contains reserved characters');

        $this->cache->get('key:bad');
    }

    public function testClear(): void
    {
        $this->cache->set('a', '1');
        $this->cache->set('b', '2');

        self::assertTrue($this->cache->clear());

        self::assertNull($this->cache->get('a'));
        self::assertNull($this->cache->get('b'));
    }

    public function testClearOnlyDeletesOidcKeys(): void
    {
        // Seed the backing store directly with one prefixed and one unprefixed key.
        $this->store['oidc_cache:oidc-key'] = serialize('oidc-value');
        $this->store['other:key'] = serialize('other-value');

        $this->cache->clear();

        self::assertArrayNotHasKey('oidc_cache:oidc-key', $this->store);
        self::assertSame(serialize('other-value'), $this->store['other:key']);
    }

    public function testGetMultiple(): void
    {
        $this->cache->set('a', 'v1');

        $results = $this->cache->getMultiple(['a', 'b'], 'default');

        self::assertSame(['a' => 'v1', 'b' => 'default'], $results);
    }

    public function testSetMultiple(): void
    {
        self::assertTrue($this->cache->setMultiple(['x' => 'v1', 'y' => 'v2']));

        self::assertSame('v1', $this->cache->get('x'));
        self::assertSame('v2', $this->cache->get('y'));
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('x', 'v1');
        $this->cache->set('y', 'v2');

        self::assertTrue($this->cache->deleteMultiple(['x', 'y']));

        self::assertNull($this->cache->get('x'));
        self::assertNull($this->cache->get('y'));
    }

    public function testCachesArrayValues(): void
    {
        $data = ['nested' => ['key' => 'value'], 'list' => [1, 2, 3]];

        $this->cache->set('arr', $data);
        self::assertSame($data, $this->cache->get('arr'));
    }

    public function testCachesNullValue(): void
    {
        $this->cache->set('null-val', null);
        // null is cached, so get with a different default should return null
        self::assertNull($this->cache->get('null-val', 'not-null'));
    }

    // ------------------------------------------------------------------
    // Test helpers
    // ------------------------------------------------------------------

    private function getTtl(string $key): ?int
    {
        return $this->ttls[$key] ?? null;
    }

    private function wireMock(): void
    {
        $this->client->method('get')->willReturnCallback(
            fn (string $key): string|false => $this->store[$key] ?? false,
        );

        $this->client->method('set')->willReturnCallback(
            function (string $key, string $value): bool {
                $this->store[$key] = $value;
                unset($this->ttls[$key]);
                return true;
            },
        );

        $this->client->method('setex')->willReturnCallback(
            function (string $key, int $seconds, string $value): bool {
                $this->store[$key] = $value;
                $this->ttls[$key] = $seconds;
                return true;
            },
        );

        // ext-redis del signature: del(array|string $key, string ...$other_keys): int|false
        $this->client->method('del')->willReturnCallback(
            function (array|string $key, string ...$others): int {
                $keys = is_array($key) ? $key : array_merge([$key], $others);
                $count = 0;
                foreach ($keys as $k) {
                    if (!is_string($k)) {
                        continue;
                    }
                    if (isset($this->store[$k])) {
                        unset($this->store[$k], $this->ttls[$k]);
                        $count++;
                    }
                }
                return $count;
            },
        );

        $this->client->method('exists')->willReturnCallback(
            fn (string $key): int => isset($this->store[$key]) ? 1 : 0,
        );

        // ext-redis scan signature: scan(&$iterator, ?string $pattern = null, int $count = 0, ?string $type = null): array|false
        // We always return all matched keys in a single batch and signal
        // completion by setting the iterator to 0.
        $this->client->method('scan')->willReturnCallback(
            function (mixed &$iterator, ?string $pattern = null): array|false {
                $keys = array_keys($this->store);
                $matched = $pattern === null
                    ? $keys
                    : array_values(array_filter(
                        $keys,
                        fn (string $k): bool => fnmatch($pattern, $k),
                    ));
                $iterator = 0;
                return $matched === [] ? false : $matched;
            },
        );
    }
}
