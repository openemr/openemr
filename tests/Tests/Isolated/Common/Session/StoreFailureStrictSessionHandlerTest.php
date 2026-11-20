<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\SessionStoreUnavailableException;
use OpenEMR\Common\Session\Storage\Handler\StoreFailureStrictSessionHandler;
use PHPUnit\Framework\TestCase;
use SessionHandler;

class StoreFailureStrictSessionHandlerTest extends TestCase
{
    public function testStoreFailureKeepsTheSessionIdAndFailsTheRead(): void
    {
        $native = $this->createMock(SessionHandler::class);
        $native->expects($this->once())->method('read')->with('id')->willReturn(false);
        $handler = new StoreFailureStrictSessionHandler($native);

        self::assertTrue($handler->validateId('id'));

        $this->expectException(SessionStoreUnavailableException::class);
        $handler->read('id');
    }

    public function testStoreFailureOnDirectReadIsReported(): void
    {
        $native = $this->createMock(SessionHandler::class);
        $native->expects($this->once())->method('read')->with('id')->willReturn(false);
        $handler = new StoreFailureStrictSessionHandler($native);

        $this->expectException(SessionStoreUnavailableException::class);
        $handler->read('id');
    }

    public function testUnknownSessionIdStillStartsFresh(): void
    {
        $native = $this->createMock(SessionHandler::class);
        $native->expects($this->once())->method('read')->with('id')->willReturn('');
        $handler = new StoreFailureStrictSessionHandler($native);

        self::assertFalse($handler->validateId('id'));
        self::assertSame('', $handler->read('id'));
    }

    public function testExistingSessionIsReadNormally(): void
    {
        $native = $this->createMock(SessionHandler::class);
        $native->expects($this->once())->method('read')->with('id')->willReturn('data');
        $handler = new StoreFailureStrictSessionHandler($native);

        self::assertTrue($handler->validateId('id'));
        self::assertSame('data', $handler->read('id'));
    }

    public function testFailureIsRetryable(): void
    {
        $exception = new SessionStoreUnavailableException();

        self::assertSame(503, $exception->getStatusCode());
        self::assertArrayHasKey('Retry-After', $exception->getHeaders());
    }
}
