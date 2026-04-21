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

/**
 * Combined inner-handler interface as used by LockingRedisSessionHandler.
 */
interface InnerHandlerInterface extends \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {}

class LockingRedisSessionHandlerTest extends TestCase
{
    private \Redis&MockObject $redis;
    private InnerHandlerInterface&MockObject $inner;
    private LockingRedisSessionHandler $handler;

    protected function setUp(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension not available');
        }

        parent::setUp();

        $this->redis = $this->createMock(\Redis::class);
        $this->inner = $this->createMock(InnerHandlerInterface::class);

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
        assert(is_array($val));
        /** @var array<array-key, mixed> $val */
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
}
