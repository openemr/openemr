<?php

/**
 * PSR-16 simple cache backed by Redis via the ext-redis PHP extension.
 *
 * Suitable for high-traffic and multi-instance deployments. Uses the
 * native `\Redis` class supplied by the `ext-redis` extension that
 * OpenEMR already requires (`ext-redis: *` in composer.json), the same
 * client used by `LockingRedisSessionHandler`.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Cache;

use Psr\SimpleCache\CacheInterface;

final readonly class RedisCache implements CacheInterface
{
    private const KEY_PREFIX = 'oidc_cache:';

    public function __construct(
        private \Redis $client,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);

        $raw = $this->client->get(self::KEY_PREFIX . $key);
        if ($raw === false) {
            return $default;
        }
        if (!is_string($raw)) {
            return $default;
        }

        $entry = @unserialize($raw, CacheEntry::unserializeOptions());
        if (!$entry instanceof CacheEntry) {
            $this->delete($key);
            return $default;
        }

        return $entry->value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->validateKey($key);

        $prefixedKey = self::KEY_PREFIX . $key;
        $entry = new CacheEntry($value);
        $serialized = serialize($entry);

        $seconds = $this->ttlToSeconds($ttl);

        if ($seconds !== null) {
            if ($seconds <= 0) {
                $this->client->del($prefixedKey);
                return true;
            }
            $this->client->setex($prefixedKey, $seconds, $serialized);
        } else {
            $this->client->set($prefixedKey, $serialized);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);

        $this->client->del(self::KEY_PREFIX . $key);

        return true;
    }

    public function clear(): bool
    {
        $pattern = self::KEY_PREFIX . '*';
        $cursor = null;

        do {
            /** @var list<string>|false $keys */
            $keys = $this->client->scan($cursor, $pattern, 100);

            if (is_array($keys) && $keys !== []) {
                $this->client->del(...$keys);
            }
        } while ($cursor !== 0 && $cursor !== '0' && $cursor !== null);

        return true;
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);

        return (bool) $this->client->exists(self::KEY_PREFIX . $key);
    }

    /**
     * @param iterable<string> $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /** @phpstan-ignore missingType.iterableValue (PSR-16 CacheInterface uses untyped iterable) */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $success = true;
        /** @var string $key */
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    private function ttlToSeconds(\DateInterval|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof \DateInterval) {
            $now = new \DateTimeImmutable();
            return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
        }

        return $ttl;
    }

    /**
     * @throws OidcCacheInvalidArgumentException
     */
    private function validateKey(string $key): void
    {
        if ($key === '') {
            throw new OidcCacheInvalidArgumentException('Cache key must not be empty');
        }

        if (preg_match('/[{}()\/\\\\@:]/', $key) === 1) {
            throw new OidcCacheInvalidArgumentException(
                "Cache key contains reserved characters: {$key}"
            );
        }
    }
}
