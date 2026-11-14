<?php

/**
 * Isolated tests for LockingRedisSessionHandler
 *
 * Tests that LockingRedisSessionHandler correctly:
 *  - Acquires a Redis lock before reading session data
 *  - Releases the lock atomically via Lua script after writing
 *  - Releases the lock after updateTimestamp (lazy_write path)
 *  - Releases the lock after destroy
 *  - Releases the lock in close() for the read_and_close path
 *  - Is a no-op in close() when the lock was already released
 *  - Throws when spin-wait expires without acquiring the lock
 *  - Delegates open(), gc(), validateId() without any lock interaction
 *
 * All Redis and inner-handler interactions are verified with mocks — no
 * real Redis connection or Docker environment is required.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session\Predis;

use OpenEMR\Common\Session\Predis\LockingRedisSessionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class LockingRedisSessionHandlerTest extends TestCase
{
    private \Redis&MockObject $redis;
    private SessionIdInterface&SessionHandlerInterface&SessionUpdateTimestampHandlerInterface&MockObject $inner;
    private LockingRedisSessionHandler $handler;

    protected function setUp(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension not available');
        }

        parent::setUp();

        $this->redis = $this->createMock(\Redis::class);
        $this->inner = $this->createMockForIntersectionOfInterfaces([
            SessionHandlerInterface::class,
            SessionIdInterface::class,
            SessionUpdateTimestampHandlerInterface::class,
        ]);

        $this->handler = new LockingRedisSessionHandler(
            $this->redis,
            $this->inner,
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 5,
            logger: new NullLogger(),
        );
    }

    /**
     * Configure the Redis mock to record every set() and eval() invocation and
     * return sensible defaults: true for SET NX (lock acquired), 1 for EVAL (lock released).
     *
     * @param list<array{cmd: string, args: list<mixed>}> $calls Passed by reference; populated on each invocation
     */
    private function configureRedisTracking(array &$calls): void
    {
        $this->redis->method('set')
            ->willReturnCallback(
                static function (string $key, mixed $value, mixed $options = null) use (&$calls): bool {
                    $calls[] = ['cmd' => 'set', 'args' => [$key, $value, $options]];
                    return true;
                }
            );

        $this->redis->method('eval')
            ->willReturnCallback(
                static function (string $script, array $args = [], int $numKeys = 0) use (&$calls): mixed {
                    $calls[] = ['cmd' => 'eval', 'args' => [$script, $args, $numKeys]];
                    return 1;
                }
            );
    }

    /**
     * Filter recorded invocations by command name and re-index.
     *
     * @param list<array{cmd: string, args: list<mixed>}> $calls
     * @return list<array{cmd: string, args: list<mixed>}>
     */
    private function callsFor(array $calls, string $cmd): array
    {
        return array_values(array_filter($calls, static fn(array $c): bool => $c['cmd'] === $cmd));
    }

    /**
     * Extract a single arg from a recorded call, narrowed from mixed.
     *
     * @param array{cmd: string, args: list<mixed>} $call
     */
    private static function arg(array $call, int $index): mixed
    {
        return $call['args'][$index];
    }

    /**
     * Extract a sub-array arg (e.g. the [keys+args] array passed to eval(),
     * or the options array passed to set()).
     *
     * @param array{cmd: string, args: list<mixed>} $call
     * @return array<array-key, mixed>
     */
    private static function argArray(array $call, int $index): array
    {
        $val = $call['args'][$index];
        self::assertIsArray($val);
        return $val;
    }

    // =========================================================================
    // open() / gc() / validateId() — no lock interaction
    // =========================================================================

    public function testOpenDelegatesToInner(): void
    {
        $this->redis->expects($this->never())->method('set');
        $this->redis->expects($this->never())->method('eval');
        $this->inner->expects($this->once())->method('open')->with('/path', 'sess')->willReturn(true);

        $this->assertTrue($this->handler->open('/path', 'sess'));
    }

    public function testGcDelegatesToInner(): void
    {
        $this->redis->expects($this->never())->method('set');
        $this->redis->expects($this->never())->method('eval');
        $this->inner->expects($this->once())->method('gc')->with(3600)->willReturn(5);

        $this->assertSame(5, $this->handler->gc(3600));
    }

    public function testValidateIdDelegatesToInner(): void
    {
        $this->redis->expects($this->never())->method('set');
        $this->redis->expects($this->never())->method('eval');
        $this->inner->expects($this->once())->method('validateId')->with('abc123')->willReturn(true);

        $this->assertTrue($this->handler->validateId('abc123'));
    }

    // =========================================================================
    // read() — acquires lock, then delegates
    // =========================================================================

    public function testReadAcquiresLockThenDelegates(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->expects($this->once())->method('read')->with('sess123')->willReturn('session-data');

        $result = $this->handler->read('sess123');

        $this->assertSame('session-data', $result);

        $setCalls = $this->callsFor($calls, 'set');
        $this->assertCount(1, $setCalls, 'SET NX should be called once for lock acquisition');
        // args: [key, token, options]
        $this->assertSame('lock_sess123', self::arg($setCalls[0], 0));
        $this->assertIsString(self::arg($setCalls[0], 1));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', self::arg($setCalls[0], 1));
        // Options array with NX and PX
        $setOptions = self::argArray($setCalls[0], 2);
        $this->assertContains('NX', $setOptions);
        $this->assertArrayHasKey('PX', $setOptions);
    }

    public function testReadUsesCorrectLockKey(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('read')->willReturn('');

        $this->handler->read('my-session-id');

        $setCalls = $this->callsFor($calls, 'set');
        $this->assertCount(1, $setCalls);
        $this->assertSame('lock_my-session-id', self::arg($setCalls[0], 0));
    }

    // =========================================================================
    // write() — writes then releases lock via Lua
    // =========================================================================

    public function testWriteReleasesLockAfterWriting(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->expects($this->once())->method('write')->with('sess123', 'data')->willReturn(true);

        $this->handler->read('sess123');
        $result = $this->handler->write('sess123', 'data');

        $this->assertTrue($result);

        $evalCalls = $this->callsFor($calls, 'eval');
        $this->assertCount(1, $evalCalls, 'Lua release script should be called once after write');
        // args: [script, [lockKey, token], numKeys]
        $this->assertIsString(self::arg($evalCalls[0], 0));
        $this->assertStringContainsString('redis.call("GET"', self::arg($evalCalls[0], 0));
        $evalArgs = self::argArray($evalCalls[0], 1);
        $this->assertSame('lock_sess123', $evalArgs[0]);
    }

    public function testWriteReleasesLockEvenWhenInnerThrows(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('write')->willThrowException(new \RuntimeException('Redis write failed'));

        $this->handler->read('sess123');

        try {
            $this->handler->write('sess123', 'data');
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException) {
            // expected
        }

        $evalCalls = $this->callsFor($calls, 'eval');
        $this->assertCount(1, $evalCalls, 'Lock must be released even when write throws');
    }

    // =========================================================================
    // updateTimestamp() — extends TTL then releases lock
    // =========================================================================

    public function testUpdateTimestampReleasesLock(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->expects($this->once())->method('updateTimestamp')->with('sess123', 'data')->willReturn(true);

        $this->handler->read('sess123');
        $result = $this->handler->updateTimestamp('sess123', 'data');

        $this->assertTrue($result);
        $this->assertCount(1, $this->callsFor($calls, 'eval'), 'Lock must be released after updateTimestamp');
    }

    // =========================================================================
    // destroy() — destroys session then releases lock
    // =========================================================================

    public function testDestroyReleasesLock(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->expects($this->once())->method('destroy')->with('sess123')->willReturn(true);

        $this->handler->read('sess123');
        $result = $this->handler->destroy('sess123');

        $this->assertTrue($result);
        $this->assertCount(1, $this->callsFor($calls, 'eval'), 'Lock must be released after destroy');
    }

    // =========================================================================
    // close() — the read_and_close path
    // =========================================================================

    public function testCloseReleasesLockWhenLockIsStillHeld(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('read')->willReturn('data');
        $this->inner->method('close')->willReturn(true);

        $this->handler->read('sess123');
        $this->handler->close();

        $this->assertCount(1, $this->callsFor($calls, 'eval'), 'Lock must be released by close() in read_and_close path');
    }

    public function testCloseIsNoOpForLockWhenWriteAlreadyReleasedIt(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('read')->willReturn('data');
        $this->inner->method('write')->willReturn(true);
        $this->inner->method('close')->willReturn(true);

        $this->handler->read('sess123');
        $this->handler->write('sess123', 'data');
        $this->handler->close();

        $this->assertCount(1, $this->callsFor($calls, 'eval'), 'eval called once by write(), not again by close()');
    }

    public function testCloseWithoutPriorReadDoesNotCallRedis(): void
    {
        $this->redis->expects($this->never())->method('set');
        $this->redis->expects($this->never())->method('eval');
        $this->inner->method('close')->willReturn(true);

        $this->handler->close();
    }

    // =========================================================================
    // Lock timeout — throws when spin-wait expires
    // =========================================================================

    public function testThrowsWhenLockCannotBeAcquiredWithinTimeout(): void
    {
        // set() returns false — SET NX never succeeds (key already exists)
        $this->redis->method('set')->willReturn(false);

        $fastHandler = new LockingRedisSessionHandler(
            $this->redis,
            $this->inner,
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 0,
            logger: new NullLogger(),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to acquire Redis session lock');

        $fastHandler->read('sess123');
    }

    public function testInnerReadIsNotCalledWhenLockTimesOut(): void
    {
        $this->redis->method('set')->willReturn(false);
        $this->inner->expects($this->never())->method('read');

        $fastHandler = new LockingRedisSessionHandler(
            $this->redis,
            $this->inner,
            maxLockWaitSeconds: 0,
            logger: new NullLogger(),
        );

        try {
            $fastHandler->read('sess123');
        } catch (\RuntimeException) {
            // expected
        }
    }

    // =========================================================================
    // Lua release script correctness
    // =========================================================================

    public function testLuaScriptPassesCorrectKeysAndArgs(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('read')->willReturn('');
        $this->inner->method('write')->willReturn(true);

        $this->handler->read('sess-xyz');
        $this->handler->write('sess-xyz', '');

        $evalCalls = $this->callsFor($calls, 'eval');
        $this->assertCount(1, $evalCalls);

        // phpredis eval args: [script, [keys + args], numKeys]
        $evalArgs = self::argArray($evalCalls[0], 1);
        // evalArgs[0] = KEYS[1] = lock key
        $this->assertSame('lock_sess-xyz', $evalArgs[0]);
        // evalArgs[1] = ARGV[1] = token (32-char hex)
        $this->assertIsString($evalArgs[1]);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $evalArgs[1]);
        // numKeys = 1
        $this->assertSame(1, self::arg($evalCalls[0], 2));
    }

    public function testLuaTokenMatchesTokenUsedInSetNx(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->inner->method('read')->willReturn('');
        $this->inner->method('write')->willReturn(true);

        $this->handler->read('sess-xyz');
        $this->handler->write('sess-xyz', '');

        $setCalls  = $this->callsFor($calls, 'set');
        $evalCalls = $this->callsFor($calls, 'eval');

        // The token written to the lock key must be the same token checked in the Lua release
        $evalArgs = self::argArray($evalCalls[0], 1);
        $this->assertSame(self::arg($setCalls[0], 1), $evalArgs[1]);
    }

    // =========================================================================
    // create_sid() — delegates or falls back to session_create_id()
    // =========================================================================

    public function testCreateSidDelegatesToInnerWhenItImplementsSessionIdInterface(): void
    {
        $this->inner->expects($this->once())
            ->method('create_sid')
            ->willReturn('inner-generated-id');

        $result = $this->handler->create_sid();

        $this->assertSame('inner-generated-id', $result, 'should delegate to inner when it implements SessionIdInterface');
    }

    public function testCreateSidUsesSessionCreateIdWhenInnerDoesNotImplementSessionIdInterface(): void
    {
        $innerWithoutSessionId = $this->createMockForIntersectionOfInterfaces([
            \SessionHandlerInterface::class,
            \SessionUpdateTimestampHandlerInterface::class,
        ]);

        $handler = new LockingRedisSessionHandler(
            $this->redis,
            $innerWithoutSessionId,
            logger: new NullLogger(),
        );

        $result = $handler->create_sid();

        $this->assertNotEmpty($result, 'session ID should not be empty');
    }

    // =========================================================================
    // WAIT replica-sync semantics (optional, opt-in via waitReplicas > 0)
    // =========================================================================

    /**
     * Construct a handler with WAIT enabled (waitReplicas > 0) — all other
     * tests use the setUp() handler which has waitReplicas: 0 (disabled),
     * matching legacy behavior.
     */
    private function handlerWithWait(int $waitReplicas = 1, int $waitTimeoutMs = 500): LockingRedisSessionHandler
    {
        return new LockingRedisSessionHandler(
            $this->redis,
            $this->inner,
            lockTtlSeconds: 30,
            maxLockWaitSeconds: 5,
            waitReplicas: $waitReplicas,
            waitTimeoutMs: $waitTimeoutMs,
            logger: new NullLogger(),
        );
    }

    public function testWriteDoesNotCallWaitWhenWaitReplicasIsZero(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->never())->method('wait');
        $this->inner->method('write')->willReturn(true);

        $this->handler->read('sess123');
        $this->handler->write('sess123', 'data');
    }

    public function testWriteCallsWaitAfterInnerWriteWhenEnabled(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())
            ->method('wait')
            ->with(1, 500)
            ->willReturn(1);
        $this->inner->method('write')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $this->assertTrue($handler->write('sess123', 'data'));
    }

    public function testUpdateTimestampCallsWaitWhenEnabled(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())->method('wait')->with(1, 500)->willReturn(1);
        $this->inner->method('updateTimestamp')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $handler->updateTimestamp('sess123', 'data');
    }

    public function testDestroyCallsWaitWhenEnabled(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())->method('wait')->with(1, 500)->willReturn(1);
        $this->inner->method('destroy')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $handler->destroy('sess123');
    }

    // Lock down the production value (openemr-devops#931 sets
    // REDIS_SESSION_WAIT_REPLICAS=2) at the mocked-\Redis tier: the three
    // tests below assert that \Redis::wait() is called with replica count
    // of 2 when waitReplicas=2 is configured. The matching integration-
    // tier tests (LockingRedisSessionHandlerIntegrationTest::testWrite
    // WithWaitReplicasTwoAgainstHealthyCluster and the UpdateTimestamp /
    // Destroy sibling tests) assert end-to-end behavior against a real
    // cluster; these isolated tests assert the handler threads the
    // argument through structurally.

    public function testWriteCallsWaitWithReplicasTwoWhenConfigured(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())
            ->method('wait')
            ->with(2, 500)
            ->willReturn(2);
        $this->inner->method('write')->willReturn(true);

        $handler = $this->handlerWithWait(2, 500);
        $handler->read('sess123');
        $this->assertTrue($handler->write('sess123', 'data'));
    }

    public function testUpdateTimestampCallsWaitWithReplicasTwoWhenConfigured(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())
            ->method('wait')
            ->with(2, 500)
            ->willReturn(2);
        $this->inner->method('updateTimestamp')->willReturn(true);

        $handler = $this->handlerWithWait(2, 500);
        $handler->read('sess123');
        $handler->updateTimestamp('sess123', 'data');
    }

    public function testDestroyCallsWaitWithReplicasTwoWhenConfigured(): void
    {
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->once())
            ->method('wait')
            ->with(2, 500)
            ->willReturn(2);
        $this->inner->method('destroy')->willReturn(true);

        $handler = $this->handlerWithWait(2, 500);
        $handler->read('sess123');
        $handler->destroy('sess123');
    }

    public function testWriteDoesNotCallWaitWhenInnerWriteReturnsFalse(): void
    {
        // If the inner write failed, there's nothing to replicate — calling
        // WAIT would block for a timeout waiting for a write that never
        // happened on the primary. Skip the WAIT step in that case.
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->expects($this->never())->method('wait');
        $this->inner->method('write')->willReturn(false);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $this->assertFalse($handler->write('sess123', 'data'));
    }

    public function testWaitFailureDoesNotPropagateToWrite(): void
    {
        // The WAIT step is a durability hardening, not a correctness
        // guarantee. Failures (including a thrown exception from the
        // redis client, or the timeout expiring before the required
        // replica count ACKs) must NOT fail the request path — losing
        // availability on transient replication blips is a worse UX
        // than occasionally losing an in-flight session on failover.
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->method('wait')->willThrowException(new \RedisException('WAIT failed'));
        $this->inner->method('write')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $this->assertTrue($handler->write('sess123', 'data'), 'WAIT throwing must not propagate to the caller');
    }

    public function testWaitShortfallDoesNotFailWrite(): void
    {
        // WAIT returning fewer acks than requested (e.g. 0 when we asked
        // for 1) is a shortfall but not a hard error — same rationale as
        // the exception case: log and continue.
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->method('wait')->willReturn(0);
        $this->inner->method('write')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $this->assertTrue($handler->write('sess123', 'data'));
    }

    public function testWriteStillReleasesLockWhenWaitThrows(): void
    {
        // The lock release must run regardless of WAIT outcome — a
        // leaked lock blocks every subsequent request for this session
        // until TTL expiry.
        /** @var list<array{cmd: string, args: list<mixed>}> $calls */
        $calls = [];
        $this->configureRedisTracking($calls);
        $this->redis->method('wait')->willThrowException(new \RedisException('WAIT failed'));
        $this->inner->method('write')->willReturn(true);

        $handler = $this->handlerWithWait(1, 500);
        $handler->read('sess123');
        $handler->write('sess123', 'data');

        $this->assertCount(1, $this->callsFor($calls, 'eval'), 'Lock must be released even when WAIT throws');
    }

    public function testWriteReturnsTrueWhenLockReleaseThrows(): void
    {
        // Scenario: waitForReplication() hits a transient connection
        // drop mid-WAIT, catches its own \RedisException, but the
        // underlying phpredis client is now in a broken state. The
        // next op (our Lua eval in releaseLock) throws fresh about
        // the dead socket. Without releaseLock's own \RedisException
        // catch, this fresh throw propagates through the `finally`
        // block of write() and silently overrides the successful
        // inner-write return value — the controller boundary sees a
        // thrown exception even though every byte the caller asked
        // for already landed on the primary.
        //
        // Contract: lock-release failure is logged (operators can
        // alert on it) but does NOT propagate. The lock's PX TTL is
        // the safety net — the next reader for this session waits up
        // to lockTtlSeconds before the key auto-expires.
        $this->redis->method('set')->willReturn(true);
        $this->redis->method('eval')
            ->willThrowException(new \RedisException('Redis server went away'));
        $this->inner->method('write')->willReturn(true);

        $this->handler->read('sess123');
        $this->assertTrue(
            $this->handler->write('sess123', 'data'),
            'write() must return the inner write result even when releaseLock throws',
        );
    }

    public function testDestroyReturnsTrueWhenLockReleaseThrows(): void
    {
        // Same rationale as testWriteReturnsTrueWhenLockReleaseThrows
        // but exercises the destroy() path, which also routes through
        // releaseLock in its finally block.
        $this->redis->method('set')->willReturn(true);
        $this->redis->method('eval')
            ->willThrowException(new \RedisException('Redis server went away'));
        $this->inner->method('destroy')->willReturn(true);

        $this->handler->read('sess123');
        $this->assertTrue(
            $this->handler->destroy('sess123'),
            'destroy() must return the inner destroy result even when releaseLock throws',
        );
    }

    public function testUpdateTimestampReturnsTrueWhenLockReleaseThrows(): void
    {
        // Same rationale, exercises the updateTimestamp() / lazy_write
        // path.
        $this->redis->method('set')->willReturn(true);
        $this->redis->method('eval')
            ->willThrowException(new \RedisException('Redis server went away'));
        $this->inner->method('updateTimestamp')->willReturn(true);

        $this->handler->read('sess123');
        $this->assertTrue(
            $this->handler->updateTimestamp('sess123', 'data'),
            'updateTimestamp() must return the inner result even when releaseLock throws',
        );
    }
}
