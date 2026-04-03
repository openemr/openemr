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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RedisCache::class)]
final class RedisCacheTest extends TestCase
{
    private FakePredisClient $client;
    private RedisCache $cache;

    protected function setUp(): void
    {
        $this->client = new FakePredisClient();
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
        self::assertNull($this->client->getTtl('oidc_cache:key1'));
    }

    public function testSetWithIntTtl(): void
    {
        self::assertTrue($this->cache->set('key2', 'value2', 3600));
        self::assertSame('value2', $this->cache->get('key2'));
        self::assertSame(3600, $this->client->getTtl('oidc_cache:key2'));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        self::assertTrue($this->cache->set('key3', 'value3', new \DateInterval('PT1H')));
        self::assertSame('value3', $this->cache->get('key3'));
        $ttl = $this->client->getTtl('oidc_cache:key3');
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
        // Set a key with the oidc prefix and one without
        $this->client->set('oidc_cache:oidc-key', serialize('oidc-value'));
        $this->client->set('other:key', serialize('other-value'));

        $this->cache->clear();

        self::assertNull($this->client->get('oidc_cache:oidc-key'));
        self::assertSame(serialize('other-value'), $this->client->get('other:key'));
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
}
