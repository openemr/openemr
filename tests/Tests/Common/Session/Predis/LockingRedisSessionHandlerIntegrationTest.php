<?php

/**
 * Integration tests for LockingRedisSessionHandler against a real Redis Sentinel cluster.
 *
 * These tests verify behaviors that mocks cannot cover:
 *  - The lock key is actually written to Redis during read() with a positive TTL
 *  - The lock key is atomically removed after write(), updateTimestamp(), destroy(), and close()
 *  - A second read() for the same session ID throws while the first read()'s lock is still held
 *    (mutual exclusion)
 *  - The Lua release script is a no-op when the stored token has been replaced by another owner
 *    (token mismatch prevents accidental release of a lock we no longer own)
 *
 * These tests are registered under the 'redis-sentinel' PHPUnit testsuite and invoked by
 * build_test_redis_failover() in ci/ciLibrary.source before the failover steps, so they only
 * run inside the OpenEMR container when the full sentinel cluster is available.  They are
 * excluded from the 'common' testsuite to prevent double-execution in sentinel CI configs.
 *
 * Redis client construction delegates to SentinelUtil::configureClient() so that TLS and mTLS
 * modes are automatically supported based on the REDIS_TLS / REDIS_X509 environment variables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Session\Predis;

use OpenEMR\Common\Session\Predis\LockingRedisSessionHandler;
use OpenEMR\Common\Session\Predis\SentinelUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

/**
 * Combined inner-handler interface as required by LockingRedisSessionHandler.
 */
interface InnerHandlerInterface extends \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {}

class LockingRedisSessionHandlerIntegrationTest extends TestCase
{
    private \Redis $redis;

    /**
     * Fixed session ID used across all tests.
     * A short, recognisable prefix makes it easy to spot in redis-cli KEYS output.
     */
    private const SESSION_ID = 'oetest-lock-intg';

    private function lockKey(): string
    {
        return 'lock_' . self::SESSION_ID;
    }

    // =========================================================================
    // Test lifecycle
    // =========================================================================

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = $this->buildClient();

        // Remove any stale key left by a previous failed run
        $this->redis->del($this->lockKey());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Always clean up, even when a test fails mid-way
        if (isset($this->redis)) {
            $this->redis->del($this->lockKey());
            $this->redis->del($this->sessionDataKey());
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Build a phpredis client via SentinelUtil which handles plain TCP, TLS,
     * and mTLS modes based on the REDIS_TLS / REDIS_X509 environment variables.
     */
    private function buildClient(): \Redis
    {
        return (new SentinelUtil())->configureClient();
    }

    /**
     * @return InnerHandlerInterface&MockObject
     */
    private function makeInner(): InnerHandlerInterface&MockObject
    {
        $mock = $this->createMock(InnerHandlerInterface::class);
        $mock->method('read')->willReturn('');
        $mock->method('write')->willReturn(true);
        $mock->method('updateTimestamp')->willReturn(true);
        $mock->method('destroy')->willReturn(true);
        $mock->method('close')->willReturn(true);
        return $mock;
    }

    private function makeHandler(int $maxLockWaitSeconds = 10, int $lockTtlSeconds = 30): LockingRedisSessionHandler
    {
        return new LockingRedisSessionHandler(
            $this->redis,
            $this->makeInner(),
            lockTtlSeconds: $lockTtlSeconds,
            maxLockWaitSeconds: $maxLockWaitSeconds,
            logger: new NullLogger(),
        );
    }

    /**
     * Build a handler backed by a real RedisSessionHandler (not a mock) so
     * session data actually round-trips through Redis.
     */
    private function makeRealHandler(): LockingRedisSessionHandler
    {
        $inner = new RedisSessionHandler($this->redis, ['ttl' => 300]);
        return new LockingRedisSessionHandler(
            $this->redis,
            $inner,
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 10,
            logger: new NullLogger(),
        );
    }

    /**
     * Build a handler backed by a real RedisSessionHandler with WAIT replication
     * tuning. The real inner is required because Redis WAIT tracks writes
     * issued on the \Redis connection — a mocked inner issues no \Redis writes,
     * so wait() would observe only pre-existing cluster state.
     */
    private function makeRealWaitHandler(
        int $waitReplicas,
        int $waitTimeoutMs,
        LoggerInterface $logger,
    ): LockingRedisSessionHandler {
        $inner = new RedisSessionHandler($this->redis, ['ttl' => 300]);
        return new LockingRedisSessionHandler(
            $this->redis,
            $inner,
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 10,
            waitReplicas: $waitReplicas,
            waitTimeoutMs: $waitTimeoutMs,
            logger: $logger,
        );
    }

    /**
     * Logger that captures every record for post-hoc assertions.
     *
     * PSR-3 LoggerInterface::log() takes a `mixed` $level by spec, so the
     * captured shape keeps $level untyped — assertions compare against
     * `LogLevel::*` constants directly.
     *
     * @return AbstractLogger&object{records: list<array{mixed, string, array<mixed>}>}
     */
    private function capturingLogger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var list<array{mixed, string, array<mixed>}> level, message, context */
            public array $records = [];

            /** @param array<mixed> $context */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message, $context];
            }
        };
    }

    /**
     * Filter captured log records to those emitted by waitForReplication().
     * waitForReplication() is the ONLY site in LockingRedisSessionHandler that
     * logs a message beginning with "Redis session WAIT".
     *
     * @param list<array{mixed, string, array<mixed>}> $records
     * @return list<array{mixed, string, array<mixed>}>
     */
    private function filterWaitWarnings(array $records): array
    {
        return array_values(array_filter(
            $records,
            static fn (array $r): bool => str_starts_with($r[1], 'Redis session WAIT'),
        ));
    }

    private function sessionDataKey(): string
    {
        // Symfony's RedisSessionHandler stores data under "php_sessions:{id}" by default,
        // but the prefix depends on the configured prefix option. The default prefix when
        // none is specified uses the PHPREDIS_SESSION prefix. We clean up using a pattern.
        return 'php_sessions:' . self::SESSION_ID;
    }

    // =========================================================================
    // Lock key lifecycle — verifies the key actually appears and disappears in Redis
    // =========================================================================

    public function testLockKeyExistsInRedisAfterRead(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);

        $this->assertSame(1, $this->redis->exists($this->lockKey()), 'lock key must be present in Redis after read()');
        $this->assertGreaterThan(0, $this->redis->pttl($this->lockKey()), 'lock key must carry a positive TTL');

        $handler->write(self::SESSION_ID, '');
    }

    public function testLockKeyRemovedAfterWrite(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);
        $handler->write(self::SESSION_ID, '');

        $this->assertSame(0, $this->redis->exists($this->lockKey()), 'lock key must be gone from Redis after write()');
    }

    public function testLockKeyRemovedAfterUpdateTimestamp(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);
        $handler->updateTimestamp(self::SESSION_ID, '');

        $this->assertSame(0, $this->redis->exists($this->lockKey()), 'lock key must be gone from Redis after updateTimestamp()');
    }

    public function testLockKeyRemovedAfterDestroy(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);
        $handler->destroy(self::SESSION_ID);

        $this->assertSame(0, $this->redis->exists($this->lockKey()), 'lock key must be gone from Redis after destroy()');
    }

    public function testLockKeyRemovedAfterClose(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);
        $handler->close();

        $this->assertSame(0, $this->redis->exists($this->lockKey()), 'lock key must be gone from Redis after close()');
    }

    // =========================================================================
    // Mutual exclusion — a second reader cannot acquire the lock while held
    // =========================================================================

    public function testSecondReadThrowsWhileLockIsHeld(): void
    {
        $holder = $this->makeHandler();
        $holder->read(self::SESSION_ID);  // acquires lock

        // A second handler with zero wait budget cannot acquire the same lock
        $waiter = $this->makeHandler(maxLockWaitSeconds: 0);

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Failed to acquire Redis session lock');
            $waiter->read(self::SESSION_ID);
        } finally {
            $holder->write(self::SESSION_ID, '');  // release so tearDown can clean up cleanly
        }
    }

    public function testSecondReadSucceedsAfterFirstReleasesLock(): void
    {
        $handlerA = $this->makeHandler();
        $handlerA->read(self::SESSION_ID);
        $handlerA->write(self::SESSION_ID, '');  // releases lock

        // Lock is now free; a second handler should acquire it without throwing
        $handlerB = $this->makeHandler();
        $handlerB->read(self::SESSION_ID);

        $this->assertSame(1, $this->redis->exists($this->lockKey()), 'handler B must hold the lock after handler A released it');

        $handlerB->write(self::SESSION_ID, '');
    }

    // =========================================================================
    // Lua atomicity — release must be a no-op when we no longer own the lock
    // =========================================================================

    public function testLuaReleaseIsNoOpWhenTokenMismatches(): void
    {
        $handler = $this->makeHandler();
        $handler->read(self::SESSION_ID);  // acquires lock, stores token X internally

        // Simulate another process replacing the lock value after our TTL expired
        // and re-acquiring the key with its own token.
        $this->redis->set($this->lockKey(), 'foreign-token');

        // write() triggers releaseLock(): the Lua script calls GET and compares to
        // our original token X.  'foreign-token' != X, so DEL is not executed.
        $handler->write(self::SESSION_ID, '');

        $this->assertSame(1, $this->redis->exists($this->lockKey()), 'lock key must survive when the stored token does not match ours');
        // tearDown will del() the key
    }

    // =========================================================================
    // Session data round-trip — verifies data persists through Redis
    // =========================================================================

    public function testSessionDataSurvivesWriteThenRead(): void
    {
        $writer = $this->makeRealHandler();
        $writer->open('', 'test');
        $writer->read(self::SESSION_ID);
        $writer->write(self::SESSION_ID, 'colour|s:4:"blue";');

        // A second handler reading the same session should get the data back
        $reader = $this->makeRealHandler();
        $reader->open('', 'test');
        $data = $reader->read(self::SESSION_ID);
        $reader->close();

        $this->assertSame('colour|s:4:"blue";', $data, 'session data must survive a write→read cycle through Redis');
    }

    // =========================================================================
    // Sentinel failover discovery — falls back to second sentinel
    // =========================================================================

    public function testDiscoveryFallsBackToSecondSentinel(): void
    {
        // Build a SentinelUtil with a bogus first sentinel and the real second.
        // The constructor reads env vars, so we temporarily prepend a bad host.
        $origSentinels = getenv('REDIS_SENTINELS');
        putenv('REDIS_SENTINELS=bogus-host-that-does-not-exist|||' . $origSentinels);

        try {
            $util = new SentinelUtil();
            $redis = $util->configureClient();

            // If we get here, discovery fell back to a working sentinel
            $this->assertTrue($redis->ping(), 'Redis should be reachable after falling back to the second sentinel');
        } finally {
            // Restore original env
            putenv('REDIS_SENTINELS=' . $origSentinels);
        }
    }

    // =========================================================================
    // Lock expiry recovery — PX TTL acts as a safety net
    // =========================================================================

    public function testSecondReaderAcquiresLockAfterTtlExpiry(): void
    {
        // Use a very short lock TTL (1 second) so the test doesn't wait long
        $holder = new LockingRedisSessionHandler(
            $this->redis,
            $this->makeInner(),
            lockTtlSeconds: 1,
            maxLockWaitSeconds: 0,
            logger: new NullLogger(),
        );
        $holder->read(self::SESSION_ID);  // acquires lock with 1 s TTL

        // Lock exists now
        $this->assertSame(1, $this->redis->exists($this->lockKey()), 'lock must exist immediately after read');

        // Do NOT release — simulate a crash.  Wait for the TTL to expire.
        // 1 s TTL + small buffer.
        usleep(1_200_000); // 1.2 seconds

        // Lock should have auto-expired
        $this->assertSame(0, $this->redis->exists($this->lockKey()), 'lock must auto-expire after TTL');

        // A second handler should now acquire the lock without waiting
        $waiter = new LockingRedisSessionHandler(
            $this->redis,
            $this->makeInner(),
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 0,
            logger: new NullLogger(),
        );
        $waiter->read(self::SESSION_ID);  // should succeed immediately

        $this->assertSame(1, $this->redis->exists($this->lockKey()), 'second handler must hold the lock after TTL expiry');

        $waiter->write(self::SESSION_ID, '');  // clean up
    }

    // =========================================================================
    // WAIT replication — synchronous replica ACK on session writes
    //
    // Cluster topology: 1 master + 2 replicas (see
    // ci/compose-shared-redis-sentinel/docker-compose.yml). Tests cover:
    //   - waitReplicas=0: opt-out short-circuit — no \Redis::wait() issued
    //   - waitReplicas=1: minimum durability — 1 of 2 replicas must ACK
    //   - waitReplicas=2: full durability — both replicas must ACK
    //   - waitReplicas > cluster: shortfall — warning logged, write still OK
    //   - updateTimestamp + destroy paths also exercise the WAIT call site
    // =========================================================================

    public function testWriteWithWaitDisabledIssuesNoWaitLogEntries(): void
    {
        $logger = $this->capturingLogger();
        $handler = $this->makeRealWaitHandler(waitReplicas: 0, waitTimeoutMs: 1000, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->write(self::SESSION_ID, 'wait0|s:7:"disabled";'),
            'write() must return true with WAIT disabled',
        );

        $this->assertSame(
            [],
            $this->filterWaitWarnings($logger->records),
            'waitReplicas=0 must short-circuit before \Redis::wait() can log anything',
        );
    }

    public function testWriteWithWaitReplicasOneAgainstHealthyCluster(): void
    {
        $logger = $this->capturingLogger();
        // 2000 ms timeout is well under SentinelUtil's 2500 ms ceiling (which
        // doesn't apply here — we construct the handler directly — but matches
        // the clamp invariant so the integration test stays representative of
        // a production-configured handler).
        $handler = $this->makeRealWaitHandler(waitReplicas: 1, waitTimeoutMs: 2000, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->write(self::SESSION_ID, 'wait1|s:6:"minone";'),
            'write() must return true when 1 replica ACK is achievable on a 2-replica cluster',
        );

        $this->assertSame(
            [],
            $this->filterWaitWarnings($logger->records),
            'no shortfall warning should fire when the cluster can satisfy the requested replica count',
        );
    }

    public function testWriteWithWaitReplicasTwoAgainstHealthyCluster(): void
    {
        $logger = $this->capturingLogger();
        $handler = $this->makeRealWaitHandler(waitReplicas: 2, waitTimeoutMs: 2000, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->write(self::SESSION_ID, 'wait2|s:4:"both";'),
            'write() must return true when both replicas ACK (full durability on this cluster)',
        );

        $this->assertSame(
            [],
            $this->filterWaitWarnings($logger->records),
            'no shortfall warning should fire when both replicas ACK within the timeout',
        );
    }

    public function testShortfallWhenWaitReplicasExceedsClusterCapacity(): void
    {
        $logger = $this->capturingLogger();
        // 5 replicas requested against a 2-replica cluster — wait() blocks up
        // to the timeout and returns the count that actually ACKed (expected 2).
        // 500 ms is long enough for both existing replicas to ACK but short
        // enough that the test doesn't sit idle waiting for the impossible
        // extra three to appear.
        $handler = $this->makeRealWaitHandler(waitReplicas: 5, waitTimeoutMs: 500, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->write(self::SESSION_ID, 'wait5|s:9:"shortfall";'),
            'write() must still return true on shortfall — durability is best-effort, not a correctness gate',
        );

        $waitWarnings = $this->filterWaitWarnings($logger->records);
        $this->assertCount(
            1,
            $waitWarnings,
            'shortfall must emit exactly one WAIT warning log entry',
        );

        [$level, $message, $context] = $waitWarnings[0];
        $this->assertSame(LogLevel::WARNING, $level, 'shortfall must log at WARNING level for operator alerting');
        $this->assertSame(
            'Redis session WAIT did not reach the configured replica count',
            $message,
            'shortfall log message must match the operator-alertable form',
        );
        $this->assertSame(5, $context['requested_replicas'] ?? null, 'context must include requested_replicas');
        $this->assertSame(500, $context['timeout_ms'] ?? null, 'context must include timeout_ms');
        $this->assertLessThan(
            5,
            $context['acked_replicas'] ?? PHP_INT_MAX,
            'acked_replicas must reflect actual cluster capacity (<5 against a 2-replica cluster)',
        );
        $this->assertGreaterThanOrEqual(
            0,
            $context['acked_replicas'] ?? -1,
            'acked_replicas must be a non-negative integer',
        );
    }

    public function testUpdateTimestampCallsWaitWithConfiguredReplicas(): void
    {
        // Seed data so updateTimestamp has something to refresh.
        $seed = $this->makeRealWaitHandler(waitReplicas: 0, waitTimeoutMs: 1000, logger: $this->capturingLogger());
        $seed->open('', 'test');
        $seed->read(self::SESSION_ID);
        $seed->write(self::SESSION_ID, 'seed|s:4:"data";');

        $logger = $this->capturingLogger();
        // Matches the production value set by openemr-devops#931:
        // REDIS_SESSION_WAIT_REPLICAS=2 on the k8s stack's
        // 1-primary+2-replica Sentinel cluster. The dedicated
        // replica-count tests above (testWriteWithWaitReplicasOne*
        // and testWriteWithWaitReplicasTwo*) establish that =1
        // and =2 both work end-to-end against the real cluster;
        // using =2 here aligns the lazy_write path's coverage
        // with what actually runs in production.
        $handler = $this->makeRealWaitHandler(waitReplicas: 2, waitTimeoutMs: 2000, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->updateTimestamp(self::SESSION_ID, 'seed|s:4:"data";'),
            'updateTimestamp() must return true when the lazy_write path is WAIT-wrapped and the cluster can ACK',
        );

        $this->assertSame(
            [],
            $this->filterWaitWarnings($logger->records),
            'updateTimestamp happy path must not emit a shortfall warning',
        );
    }

    public function testDestroyCallsWaitWithConfiguredReplicas(): void
    {
        // Seed so destroy has something to tombstone.
        $seed = $this->makeRealWaitHandler(waitReplicas: 0, waitTimeoutMs: 1000, logger: $this->capturingLogger());
        $seed->open('', 'test');
        $seed->read(self::SESSION_ID);
        $seed->write(self::SESSION_ID, 'seed|s:4:"data";');

        $logger = $this->capturingLogger();
        // Same production-alignment rationale as
        // testUpdateTimestampCallsWaitWithConfiguredReplicas above.
        $handler = $this->makeRealWaitHandler(waitReplicas: 2, waitTimeoutMs: 2000, logger: $logger);
        $handler->open('', 'test');
        $handler->read(self::SESSION_ID);

        $this->assertTrue(
            $handler->destroy(self::SESSION_ID),
            'destroy() must return true when the session-deletion path is WAIT-wrapped and the cluster can ACK',
        );

        $this->assertSame(
            [],
            $this->filterWaitWarnings($logger->records),
            'destroy happy path must not emit a shortfall warning',
        );
    }
}
