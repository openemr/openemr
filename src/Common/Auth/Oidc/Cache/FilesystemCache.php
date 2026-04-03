<?php

/**
 * PSR-16 simple cache backed by the local filesystem.
 *
 * Each cache entry is stored as a serialized file. Suitable for single-instance
 * deployments and development. For multi-instance or containerized environments,
 * prefer DatabaseCache or RedisCache.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Cache;

use Psr\SimpleCache\CacheInterface;

final class FilesystemCache implements CacheInterface
{
    private readonly string $directory;

    /**
     * @param string $directory Absolute path to the cache directory.
     */
    public function __construct(string $directory)
    {
        $realpath = realpath($directory);
        if ($realpath === false || !is_dir($realpath)) {
            throw new OidcCacheException("Cache directory does not exist: {$directory}");
        }
        if (!is_writable($realpath)) {
            throw new OidcCacheException("Cache directory is not writable: {$directory}");
        }
        $this->directory = $realpath;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);

        $path = $this->pathForKey($key);
        if (!is_file($path)) {
            return $default;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return $default;
        }

        $entry = @unserialize($contents, CacheEntry::unserializeOptions());
        if (!$entry instanceof CacheEntry) {
            @unlink($path);
            return $default;
        }

        if ($entry->isExpired(time())) {
            @unlink($path);
            return $default;
        }

        return $entry->value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->validateKey($key);

        $expiresAt = $this->ttlToTimestamp($ttl);
        $entry = new CacheEntry($value, $expiresAt);

        $path = $this->pathForKey($key);
        $tempPath = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';

        $written = file_put_contents($tempPath, serialize($entry));
        if ($written === false) {
            @unlink($tempPath);
            return false;
        }

        return rename($tempPath, $path);
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);

        $path = $this->pathForKey($key);
        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    public function clear(): bool
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.cache');
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (!@unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
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

    private function pathForKey(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.cache';
    }

    private function ttlToTimestamp(\DateInterval|int|null $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof \DateInterval) {
            $now = new \DateTimeImmutable();
            return $now->add($ttl)->getTimestamp();
        }

        if ($ttl <= 0) {
            return 0;
        }

        return time() + $ttl;
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
