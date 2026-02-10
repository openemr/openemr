<?php

/**
 * ServiceContainer Smoke Tests
 *
 * Tests that ServiceContainer methods return the expected interface types.
 * getTwig() is excluded as it requires global state.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use InvalidArgumentException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface,
};
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Smoke tests intentionally verify runtime types match declared types.
 */
class ServiceContainerTest extends TestCase
{
    public function testGetClock(): void
    {
        $clock = ServiceContainer::getClock();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    public function testGetCrypto(): void
    {
        $crypto = ServiceContainer::getCrypto();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(CryptoInterface::class, $crypto);
    }

    public function testGetLogger(): void
    {
        $logger = ServiceContainer::getLogger();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testGetRequestFactory(): void
    {
        $factory = ServiceContainer::getRequestFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(RequestFactoryInterface::class, $factory);
    }

    public function testGetResponseFactory(): void
    {
        $factory = ServiceContainer::getResponseFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory);
    }

    public function testGetServerRequestFactory(): void
    {
        $factory = ServiceContainer::getServerRequestFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory);
    }

    public function testGetStreamFactory(): void
    {
        $factory = ServiceContainer::getStreamFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory);
    }

    public function testGetUploadedFileFactory(): void
    {
        $factory = ServiceContainer::getUploadedFileFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory);
    }

    public function testGetUriFactory(): void
    {
        $factory = ServiceContainer::getUriFactory();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(UriFactoryInterface::class, $factory);
    }

    public function testRegisterReturnsCustomInstance(): void
    {
        ServiceContainer::reset();
        $customLogger = new NullLogger();

        ServiceContainer::register(LoggerInterface::class, $customLogger);

        $this->assertSame($customLogger, ServiceContainer::getLogger());
        ServiceContainer::reset();
    }

    public function testRegisterLastWins(): void
    {
        ServiceContainer::reset();
        $first = new NullLogger();
        $second = new NullLogger();

        ServiceContainer::register(LoggerInterface::class, $first);
        ServiceContainer::register(LoggerInterface::class, $second);

        $this->assertSame($second, ServiceContainer::getLogger());
        $this->assertNotSame($first, ServiceContainer::getLogger());
        ServiceContainer::reset();
    }

    public function testRegisterThrowsOnTypeMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceContainer::register(LoggerInterface::class, new \stdClass());
    }
}
