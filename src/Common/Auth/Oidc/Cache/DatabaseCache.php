<?php

/**
 * PSR-16 simple cache backed by a database table.
 *
 * Suitable for multi-instance and containerized deployments where all instances
 * share the same database. Uses the `oidc_cache` table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Cache;

use OpenEMR\Common\Database\QueryUtils;
use Psr\SimpleCache\CacheInterface;

final class DatabaseCache implements CacheInterface
{
    public const TABLE_NAME = 'oidc_cache';

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);

        $row = QueryUtils::fetchRecords(
            'SELECT `cache_value`, `expires_at` FROM `' . self::TABLE_NAME . '` WHERE `cache_key` = ?',
            [$key],
        );

        if ($row === []) {
            return $default;
        }

        $record = $row[0];
        $expiresAt = $record['expires_at'];
        $cacheValue = $record['cache_value'];

        if (is_string($expiresAt) && strtotime($expiresAt) < time()) {
            $this->delete($key);
            return $default;
        }

        if (!is_string($cacheValue)) {
            $this->delete($key);
            return $default;
        }

        $entry = @unserialize($cacheValue, CacheEntry::unserializeOptions());
        if (!$entry instanceof CacheEntry) {
            $this->delete($key);
            return $default;
        }

        return $entry->value;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->validateKey($key);

        $expiresAt = $this->ttlToDatetime($ttl);
        $entry = new CacheEntry($value);
        $serialized = serialize($entry);

        $existing = QueryUtils::fetchRecords(
            'SELECT 1 FROM `' . self::TABLE_NAME . '` WHERE `cache_key` = ?',
            [$key],
        );

        if ($existing !== []) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `' . self::TABLE_NAME . '` SET `cache_value` = ?, `expires_at` = ? WHERE `cache_key` = ?',
                [$serialized, $expiresAt, $key],
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `' . self::TABLE_NAME . '` (`cache_key`, `cache_value`, `expires_at`) VALUES (?, ?, ?)',
                [$key, $serialized, $expiresAt],
            );
        }

        return true;
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);

        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '` WHERE `cache_key` = ?',
            [$key],
        );

        return true;
    }

    public function clear(): bool
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '`',
        );

        return true;
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

    /**
     * Remove all expired entries. Intended to be called periodically (cron or on-demand).
     */
    public function purgeExpired(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '` WHERE `expires_at` IS NOT NULL AND `expires_at` < NOW()',
        );
    }

    private function ttlToDatetime(\DateInterval|int|null $ttl): ?string
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof \DateInterval) {
            return (new \DateTimeImmutable())->add($ttl)->format('Y-m-d H:i:s');
        }

        if ($ttl <= 0) {
            return '1970-01-01 00:00:00';
        }

        return (new \DateTimeImmutable())->modify("+{$ttl} seconds")->format('Y-m-d H:i:s');
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

        if (strlen($key) > 255) {
            throw new OidcCacheInvalidArgumentException(
                "Cache key exceeds maximum length of 255 characters: {$key}"
            );
        }
    }
}
