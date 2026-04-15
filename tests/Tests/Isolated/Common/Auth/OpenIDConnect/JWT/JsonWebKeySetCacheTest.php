<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\JWT;

use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery\FakeHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

#[CoversClass(JsonWebKeySet::class)]
final class JsonWebKeySetCacheTest extends TestCase
{
    private const JWKS_URI = 'https://accounts.example.com/jwks';
    private const JWKS_JSON = '{"keys":[{"kty":"RSA","kid":"k1","alg":"RS256","n":"x","e":"AQAB"}]}';

    private FakeHttpClient $httpClient;
    private string $cacheDir;
    private FilesystemCache $cache;

    protected function setUp(): void
    {
        $this->httpClient = new FakeHttpClient();
        $this->cacheDir = sys_get_temp_dir() . '/jwks_cache_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new FilesystemCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir($this->cacheDir);
    }

    public function testFetchesFromHttpWhenNoCacheProvided(): void
    {
        $this->httpClient->setNextResponse(200, self::JWKS_JSON);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger());

        self::assertSame(self::JWKS_JSON, $set->contents());
        self::assertSame(1, $this->httpClient->getRequestCount());
    }

    public function testCacheMissPopulatesCacheAndFetchesOverHttp(): void
    {
        $this->httpClient->setNextResponse(200, self::JWKS_JSON);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger(), $this->cache);

        self::assertSame(self::JWKS_JSON, $set->contents());
        self::assertSame(1, $this->httpClient->getRequestCount());

        // A fresh instance with the same URI + same cache must hit cache, not HTTP.
        $secondSet = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger(), $this->cache);

        self::assertSame(self::JWKS_JSON, $secondSet->contents());
        self::assertSame(1, $this->httpClient->getRequestCount(), 'Second construction should be served from cache');
    }

    public function testInlineJwksDoesNotTouchHttpOrCache(): void
    {
        $set = new JsonWebKeySet($this->httpClient, null, self::JWKS_JSON, new NullLogger(), $this->cache);

        self::assertSame(self::JWKS_JSON, $set->contents());
        self::assertSame(0, $this->httpClient->getRequestCount());
    }

    public function testCacheReadFailureFallsBackToHttp(): void
    {
        $brokenCache = new class implements CacheInterface {
            public function get(string $key, mixed $default = null): mixed
            {
                throw new \RuntimeException('cache read exploded');
            }

            public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
            {
                return true;
            }

            public function delete(string $key): bool
            {
                return true;
            }

            public function clear(): bool
            {
                return true;
            }

            /**
             * @return iterable<string, mixed>
             */
            public function getMultiple(iterable $keys, mixed $default = null): iterable
            {
                return [];
            }

            /** @param iterable<mixed> $values */
            public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
            {
                return true;
            }

            /** @param iterable<mixed> $keys */
            public function deleteMultiple(iterable $keys): bool
            {
                return true;
            }

            public function has(string $key): bool
            {
                return false;
            }
        };

        $this->httpClient->setNextResponse(200, self::JWKS_JSON);

        $set = new JsonWebKeySet($this->httpClient, self::JWKS_URI, null, new NullLogger(), $brokenCache);

        self::assertSame(self::JWKS_JSON, $set->contents());
        self::assertSame(1, $this->httpClient->getRequestCount());
    }
}
