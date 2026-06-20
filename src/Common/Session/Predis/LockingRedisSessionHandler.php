<?php

/**
 * LockingRedisSessionHandler - adds distributed locking to Redis-backed sessions.
 *
 * Symfony's RedisSessionHandler stores and retrieves session data but provides
 * no locking. Without a lock, concurrent requests for the same session can race
 * and the last write wins, silently discarding earlier changes.
 *
 * This is the predis-side counterpart of ReadAndCloseNativeSessionStorage:
 * just as that class adds the read_and_close no-lock mechanism that Symfony's
 * NativeSessionStorage did not support, this class adds the locking mechanism
 * that Symfony's RedisSessionHandler does not support.
 *
 * Lock lifecycle:
 *  - read()            : acquire lock via Redis SET NX (spin-wait up to maxLockWaitSeconds)
 *  - write()           : write session data, then release lock
 *  - updateTimestamp() : extend session TTL, then release lock
 *  - destroy()         : delete session data, then release lock
 *  - close()           : release lock if still held (handles the read_and_close case
 *                        where PHP calls close() immediately after read())
 *  - open() / gc() / validateId() : delegate directly, no locking involved
 *
 * Lock release is done atomically via a Lua script that only deletes the lock
 * key when its value matches the token we set, preventing a request from
 * accidentally releasing a lock it no longer owns (e.g. after lock TTL expiry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session\Predis;

use OpenEMR\BC\ServiceContainer;
use Psr\Log\LoggerInterface;

class LockingRedisSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    /**
     * Prefix prepended to the session ID to form the Redis lock key.
     * Kept distinct from the session data key prefix used by RedisSessionHandler.
     */
    private const LOCK_PREFIX = 'lock_';

    /**
     * Microseconds to sleep between lock acquisition attempts.
     */
    private const LOCK_SPIN_WAIT_US = 50_000; // 50 ms

    /**
     * Default Redis TTL for the lock key. Configurable via REDIS_SESSION_LOCK_TTL.
     * Should comfortably exceed the longest expected request duration.
     */
    public const DEFAULT_LOCK_TTL_SECONDS = 60;

    /**
     * Default maximum time to spin-wait for the lock. Configurable via REDIS_SESSION_LOCK_MAX_WAIT.
     */
    public const DEFAULT_LOCK_MAX_WAIT_SECONDS = 60;

    /**
     * The Redis key holding the current lock, or null when no lock is held.
     */
    private ?string $currentLockKey = null;

    /**
     * Random token written to the lock key so we can verify ownership before release.
     */
    private ?string $lockToken = null;

    private readonly LoggerInterface $logger;

    /**
     * @param \Redis                                                              $redis              phpredis client (same instance used by $inner)
     * @param \SessionHandlerInterface&\SessionUpdateTimestampHandlerInterface    $inner              Inner handler that performs the actual Redis read/write
     * @param int                                                                 $lockTtlSeconds     Redis TTL for the lock key; should exceed the longest expected request
     * @param int                                                                 $maxLockWaitSeconds How long to spin-wait for the lock before giving up
     */
    public function __construct(
        private readonly \Redis $redis,
        private readonly \SessionHandlerInterface&\SessionUpdateTimestampHandlerInterface $inner,
        private readonly int $lockTtlSeconds = self::DEFAULT_LOCK_TTL_SECONDS,
        private readonly int $maxLockWaitSeconds = self::DEFAULT_LOCK_MAX_WAIT_SECONDS,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    public function open(string $path, string $name): bool
    {
        return $this->inner->open($path, $name);
    }

    /**
     * Acquires the Redis lock for this session, then reads and returns the data.
     * The lock is held until write(), updateTimestamp(), destroy(), or close().
     * If the inner read throws, the lock is released to avoid a leaked lock that
     * would block the session until TTL expiry.
     */
    public function read(string $id): string|false
    {
        $this->acquireLock($id);
        try {
            return $this->inner->read($id);
        } catch (\Throwable $e) {
            $this->releaseLock();
            throw $e;
        }
    }

    /**
     * Writes session data and releases the lock.
     */
    public function write(string $id, string $data): bool
    {
        try {
            return $this->inner->write($id, $data);
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Extends the session TTL without rewriting data, then releases the lock.
     * PHP calls this instead of write() when lazy_write is enabled and data is unchanged.
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        try {
            return $this->inner->updateTimestamp($id, $data);
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Destroys the session data and releases the lock.
     */
    public function destroy(string $id): bool
    {
        try {
            return $this->inner->destroy($id);
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Releases the lock if still held, then closes the inner handler.
     *
     * This is the path taken when the session was opened with read_and_close=true:
     * PHP calls open → read (lock acquired here) → close (lock released here).
     * For normal sessions the lock was already released by write/updateTimestamp/destroy,
     * so releaseLock() is a no-op.
     */
    public function close(): bool
    {
        $this->releaseLock();
        return $this->inner->close();
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->inner->gc($max_lifetime);
    }

    public function validateId(string $id): bool
    {
        return $this->inner->validateId($id);
    }

    /**
     * Spin-waits until the Redis lock for $sessionId is acquired.
     *
     * Uses SET … NX PX which is atomic: the key is only created if it does not exist,
     * and the TTL is set in the same command, so there is no window where the lock
     * exists without an expiry.
     *
     * @throws \RuntimeException if the lock cannot be acquired within maxLockWaitSeconds
     */
    private function acquireLock(string $sessionId): void
    {
        $this->currentLockKey = self::LOCK_PREFIX . $sessionId;
        $token = bin2hex(random_bytes(16));
        $ttlMs = $this->lockTtlSeconds * 1000;
        $deadline = microtime(true) + $this->maxLockWaitSeconds;

        while (true) {
            // phpredis SET with NX + PX — returns true on success, false when key exists
            $result = $this->redis->set($this->currentLockKey, $token, ['NX', 'PX' => $ttlMs]);

            if ($result === true) {
                $this->lockToken = $token;
                $this->logger->debug('Redis session lock acquired', [
                    'session_id_prefix' => substr($sessionId, 0, 8) . '…',
                    'lock_ttl_seconds' => $this->lockTtlSeconds,
                ]);
                return;
            }

            if (microtime(true) >= $deadline) {
                $this->currentLockKey = null;
                throw new \RuntimeException(sprintf(
                    'Failed to acquire Redis session lock within %d second(s).',
                    $this->maxLockWaitSeconds,
                ));
            }

            usleep(self::LOCK_SPIN_WAIT_US);
        }
    }

    /**
     * Atomically releases the lock only if we still own it.
     *
     * The Lua script checks that the lock key's value matches our token before
     * deleting it, preventing us from releasing a lock that was re-acquired by
     * another request after our TTL expired.
     */
    private function releaseLock(): void
    {
        if ($this->lockToken === null || $this->currentLockKey === null) {
            return;
        }

        $script = <<<'LUA'
if redis.call("GET", KEYS[1]) == ARGV[1] then
    return redis.call("DEL", KEYS[1])
else
    return 0
end
LUA;
        // phpredis eval: script, [keys + args], numKeys
        $this->redis->eval($script, [$this->currentLockKey, $this->lockToken], 1);

        $this->logger->debug('Redis session lock released');

        $this->lockToken = null;
        $this->currentLockKey = null;
    }
}
