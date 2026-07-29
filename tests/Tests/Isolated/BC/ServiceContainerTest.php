<?php

/**
 * ServiceContainer Smoke Tests
 *
 * Tests that ServiceContainer methods return the expected interface types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use InvalidArgumentException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
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

    public function testGetGuzzle(): void
    {
        $client = ServiceContainer::getGuzzle();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(GuzzleClientInterface::class, $client);
    }

    public function testGetHttpClient(): void
    {
        $client = ServiceContainer::getHttpClient();
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testGetHttpClientFallsThroughToGuzzleByDefault(): void
    {
        ServiceContainer::reset();

        self::assertSame(
            ServiceContainer::getGuzzle(),
            ServiceContainer::getHttpClient(),
            'Without an override, the PSR-18 client should be the shared Guzzle instance',
        );
        ServiceContainer::reset();
    }

    public function testGetHttpClientHonorsPsr18Override(): void
    {
        ServiceContainer::reset();
        $mock = self::createStub(ClientInterface::class);

        ServiceContainer::override(ClientInterface::class, $mock);

        self::assertSame(
            $mock,
            ServiceContainer::getHttpClient(),
            'getHttpClient() must resolve under the PSR-18 key so it can be overridden independently of getGuzzle()',
        );
        ServiceContainer::reset();
    }

    public function testPsr18OverrideDoesNotLeakIntoGuzzle(): void
    {
        ServiceContainer::reset();
        $mock = self::createStub(ClientInterface::class);

        ServiceContainer::override(ClientInterface::class, $mock);

        self::assertNotSame(
            $mock,
            ServiceContainer::getGuzzle(),
            'Overriding the PSR-18 client must not affect the Guzzle seam',
        );
        ServiceContainer::reset();
    }

    public function testGetGuzzleHonorsOverride(): void
    {
        ServiceContainer::reset();
        $mock = self::createStubForIntersectionOfInterfaces([
            GuzzleClientInterface::class,
            ClientInterface::class,
        ]);

        ServiceContainer::override(GuzzleClientInterface::class, $mock);

        self::assertSame(
            $mock,
            ServiceContainer::getGuzzle(),
            'getGuzzle() should return the overridden Guzzle client',
        );
        ServiceContainer::reset();
    }

    public function testOverrideReturnsCustomInstance(): void
    {
        ServiceContainer::reset();
        $customLogger = new NullLogger();

        ServiceContainer::override(LoggerInterface::class, $customLogger);

        $this->assertSame($customLogger, ServiceContainer::getLogger());
        ServiceContainer::reset();
    }

    public function testOverrideLastWins(): void
    {
        ServiceContainer::reset();
        $first = new NullLogger();
        $second = new NullLogger();

        ServiceContainer::override(LoggerInterface::class, $first);
        ServiceContainer::override(LoggerInterface::class, $second);

        $this->assertSame($second, ServiceContainer::getLogger());
        $this->assertNotSame($first, ServiceContainer::getLogger());
        ServiceContainer::reset();
    }

    public function testOverrideThrowsOnTypeMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceContainer::override(LoggerInterface::class, new \stdClass());
    }

    public function testGetterReturnsSameInstanceOnRepeatedCalls(): void
    {
        ServiceContainer::reset();

        $first = ServiceContainer::getClock();
        $second = ServiceContainer::getClock();

        self::assertSame($first, $second);
        ServiceContainer::reset();
    }

    public function testResetClearsCachedInstances(): void
    {
        ServiceContainer::reset();

        $before = ServiceContainer::getClock();
        ServiceContainer::reset();
        $after = ServiceContainer::getClock();

        self::assertNotSame($before, $after);
    }
}
