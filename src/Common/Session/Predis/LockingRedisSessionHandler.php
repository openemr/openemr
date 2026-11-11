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
use RuntimeException;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class LockingRedisSessionHandler implements
    SessionHandlerInterface,
    SessionIdInterface,
    SessionUpdateTimestampHandlerInterface
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
     * Default number of Redis replicas the WAIT command must receive the
     * write before we consider it durable. Zero (the default) disables the
     * WAIT step entirely — the inner write returns as soon as the primary
     * has acknowledged, matching legacy behavior. Configure via
     * REDIS_SESSION_WAIT_REPLICAS on a Sentinel-managed Redis cluster to
     * trade a few ms of RTT for durability against primary failover losing
     * in-flight session writes.
     *
     * See Redis docs: https://redis.io/commands/wait/
     */
    public const DEFAULT_WAIT_REPLICAS = 0;

    /**
     * Default per-call timeout (milliseconds) for the Redis WAIT command.
     * Configurable via REDIS_SESSION_WAIT_TIMEOUT_MS. Only consulted when
     * waitReplicas > 0.
     */
    public const DEFAULT_WAIT_TIMEOUT_MS = 1000;

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
     * @param int                                                                 $waitReplicas       Number of Redis replicas that must receive each session write before it is considered durable (0 disables)
     * @param int                                                                 $waitTimeoutMs      Per-call timeout for the Redis WAIT command (only relevant when $waitReplicas > 0)
     */
    public function __construct(
        private readonly \Redis $redis,
        private readonly \SessionHandlerInterface&\SessionUpdateTimestampHandlerInterface $inner,
        private readonly int $lockTtlSeconds = self::DEFAULT_LOCK_TTL_SECONDS,
        private readonly int $maxLockWaitSeconds = self::DEFAULT_LOCK_MAX_WAIT_SECONDS,
        private readonly int $waitReplicas = self::DEFAULT_WAIT_REPLICAS,
        private readonly int $waitTimeoutMs = self::DEFAULT_WAIT_TIMEOUT_MS,
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
     * Writes session data, waits for replica ACK (when configured), then releases the lock.
     */
    public function write(string $id, string $data): bool
    {
        try {
            $result = $this->inner->write($id, $data);
            if ($result) {
                $this->waitForReplication();
            }
            return $result;
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Extends the session TTL without rewriting data, waits for replica ACK
     * (when configured), then releases the lock. PHP calls this instead of
     * write() when lazy_write is enabled and data is unchanged.
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        try {
            $result = $this->inner->updateTimestamp($id, $data);
            if ($result) {
                $this->waitForReplication();
            }
            return $result;
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Destroys the session data, waits for replica ACK (when configured), then releases the lock.
     */
    public function destroy(string $id): bool
    {
        try {
            $result = $this->inner->destroy($id);
            if ($result) {
                $this->waitForReplication();
            }
            return $result;
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

    public function create_sid(): string
    {
        // We use Symfony's implementations in practice which don't implement
        // this, so it can't be required in the constructor. Hopefully future
        // versions will add it since create_sid will become required in PHP9.
        if ($this->inner instanceof SessionIdInterface) {
            return $this->inner->create_sid();
        }
        $id = session_create_id();
        if ($id === false) {
            throw new RuntimeException('Could not create session id');
        }
        return $id;
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
     *
     * A \RedisException thrown by the eval is swallowed (logged, not
     * propagated) because this method is called from the `finally` block
     * of write() / updateTimestamp() / destroy(), where a throw would
     * silently override the successful return value of the inner handler
     * call that just preceded it. The most common way this trips is:
     * waitForReplication() hits a transient connection drop mid-WAIT,
     * catches its own \RedisException, but leaves the phpredis client
     * in a broken state — the next operation on that client (our Lua
     * eval here) throws fresh about the dead socket. Without this
     * catch, a successful session write would surface as a thrown
     * exception at the controller boundary even though every byte the
     * caller asked for landed on the primary.
     *
     * The failure cost of a swallowed release is bounded: the lock key
     * carries a `PX` TTL (REDIS_SESSION_LOCK_TTL, default 60s), so the
     * next reader for this session waits up to that TTL before the lock
     * auto-expires and acquireLock()'s spin wait succeeds. That path is
     * already covered by `testSecondReaderAcquiresLockAfterTtlExpiry`
     * in the integration suite.
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
        try {
            // phpredis eval: script, [keys + args], numKeys
            $this->redis->eval($script, [$this->currentLockKey, $this->lockToken], 1);
            $this->logger->debug('Redis session lock released');
        } catch (\RedisException $e) {
            // Catch \RedisException specifically (not \Throwable or
            // \Exception) — the only runtime failure we want to swallow
            // is a Redis-protocol problem (network, server-side error,
            // dead connection after a mid-WAIT drop). Programming
            // errors (TypeError, ParseError, ErrorException) must
            // propagate.
            $this->logger->warning('Redis session lock release raised; lock will expire via TTL safety net', [
                'exception' => $e,
            ]);
        } finally {
            // Clear ownership state either way: whether the release
            // succeeded or the eval threw, this request no longer owns
            // the lock from its own perspective. A subsequent same-
            // request operation would re-acquire via read() if needed.
            $this->lockToken = null;
            $this->currentLockKey = null;
        }
    }

    /**
     * Blocks until the Redis primary confirms the configured number of
     * replicas have received every write issued on this connection since
     * the last WAIT call (Redis WAIT semantics — see
     * https://redis.io/commands/wait/). Called after a successful write /
     * updateTimestamp / destroy so the session write is durable across a
     * primary failover before the request returns.
     *
     * No-op when waitReplicas is 0 (the default), matching legacy behavior.
     *
     * Errors — including the WAIT timeout expiring before the required
     * replica count ACKs — are LOGGED but NOT propagated. The WAIT step
     * is a durability hardening, not a correctness guarantee; blocking
     * the request path on it would turn a transient Redis-replication
     * blip into a user-visible session failure, which is a worse UX than
     * occasionally losing an in-flight session on failover. Operators
     * who need strict durability should alert on the warning log entries
     * this emits.
     */
    private function waitForReplication(): void
    {
        if ($this->waitReplicas <= 0) {
            return;
        }
        try {
            $acked = $this->redis->wait($this->waitReplicas, $this->waitTimeoutMs);
            if ($acked === false || $acked < $this->waitReplicas) {
                $this->logger->warning('Redis session WAIT did not reach the configured replica count', [
                    'requested_replicas' => $this->waitReplicas,
                    'acked_replicas' => $acked,
                    'timeout_ms' => $this->waitTimeoutMs,
                ]);
            }
        } catch (\RedisException $e) {
            // Catch \RedisException specifically (not \Throwable or
            // \Exception) — the only runtime failure we want to swallow
            // is a Redis-protocol problem (network, server-side error,
            // timeout). Programming errors (TypeError, ParseError,
            // ErrorException) must propagate.
            $this->logger->warning('Redis session WAIT raised', [
                'exception' => $e,
                'requested_replicas' => $this->waitReplicas,
                'timeout_ms' => $this->waitTimeoutMs,
            ]);
        }
    }
}
