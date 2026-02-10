<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use OpenEMR\Common\Crypto;
use OpenEMR\Common\Logging;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface,
};
use Twig\Environment;

/**
 * Utility class for accessing common system services.
 *
 * While we eventually want to support a PSR-11 container and service
 * auto-wiring, there's a lot of work to get to that point. Going through this
 * wrapper rather than inline `new`/`instance`/`factory`/etc calls, and
 * referencing interfaces instead of implementations, should ease the eventual
 * migration.
 */
class ServiceContainer
{
    /** @var array<class-string, object> */
    private static array $instances = [];

    /**
     * Reset all registered instances. For testing only.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$instances = [];
    }

    /**
     * Register a custom implementation for an interface.
     *
     * Modules can use this during bootstrap to provide alternative
     * implementations of core services. Last registration wins.
     *
     * @param class-string $interface
     * @throws InvalidArgumentException if instance doesn't implement interface
     */
    public static function register(string $interface, object $instance): void
    {
        if (!is_a($instance, $interface)) {
            throw new InvalidArgumentException(sprintf(
                '%s does not implement %s',
                $instance::class,
                $interface,
            ));
        }
        self::$instances[$interface] = $instance;
    }

    public static function getClock(): ClockInterface
    {
        /** @var ClockInterface */
        return self::$instances[ClockInterface::class] ?? SystemClock::fromSystemTimezone();
    }

    public static function getCrypto(): Crypto\CryptoInterface
    {
        /** @var Crypto\CryptoInterface */
        return self::$instances[Crypto\CryptoInterface::class] ?? new Crypto\CryptoGen();
    }

    public static function getLogger(): LoggerInterface
    {
        /** @var LoggerInterface */
        return self::$instances[LoggerInterface::class] ?? new Logging\SystemLogger();
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        /** @var RequestFactoryInterface */
        return self::$instances[RequestFactoryInterface::class] ?? new Psr17Factory();
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        /** @var ResponseFactoryInterface */
        return self::$instances[ResponseFactoryInterface::class] ?? new Psr17Factory();
    }

    public static function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        /** @var ServerRequestFactoryInterface */
        return self::$instances[ServerRequestFactoryInterface::class] ?? new Psr17Factory();
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        /** @var StreamFactoryInterface */
        return self::$instances[StreamFactoryInterface::class] ?? new Psr17Factory();
    }

    /**
     * Get a configured Twig environment.
     *
     * @param string|null $path Additional template path to include alongside
     *                          the default /templates directory
     */
    public static function getTwig(?string $path = null): Environment
    {
        /** @var Kernel $kernel */
        $kernel = OEGlobalsBag::getInstance()->get('kernel');
        return (new TwigContainer($path, $kernel))->getTwig();
    }

    public static function getUploadedFileFactory(): UploadedFileFactoryInterface
    {
        /** @var UploadedFileFactoryInterface */
        return self::$instances[UploadedFileFactoryInterface::class] ?? new Psr17Factory();
    }

    public static function getUriFactory(): UriFactoryInterface
    {
        /** @var UriFactoryInterface */
        return self::$instances[UriFactoryInterface::class] ?? new Psr17Factory();
    }
}
