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

/**
 * Utility class for accessing common system services.
 *
 * While we eventually want to support a PSR-11 container and service
 * auto-wiring, there's a lot of work to get to that point. Going through this
 * wrapper rather than inline `new`/`instance`/`factory`/etc calls, and
 * referencing interfaces instead of implementations, should ease the eventual
 * migration.
 *
 * Prefer the static methods in this class over `new`ing the underlying
 * utilities directly.
 */
class ServiceContainer
{
    /** @var array<class-string, object> */
    private static array $overrides = [];

    /**
     * Reset all registered overrides. For testing only.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$overrides = [];
    }

    /**
     * Override a service with a custom implementation.
     *
     * Modules can use this during bootstrap to provide alternative
     * implementations of core services. Last override wins.
     *
     * @template T of object
     * @param class-string<T> $interface
     * @param T $instance
     * @throws InvalidArgumentException if instance doesn't implement interface
     */
    public static function override(string $interface, object $instance): void
    {
        if (!($instance instanceof $interface)) {
            throw new InvalidArgumentException(sprintf(
                '%s does not implement %s',
                $instance::class,
                $interface,
            ));
        }
        self::$overrides[$interface] = $instance;
    }

    /**
     * @template T of object
     * @param class-string<T> $interface
     * @return ?T
     */
    private static function resolve(string $interface): ?object
    {
        /** @var ?T */
        return self::$overrides[$interface] ?? null;
    }

    public static function getClock(): ClockInterface
    {
        return self::resolve(ClockInterface::class) ?? SystemClock::fromSystemTimezone();
    }

    public static function getCrypto(): Crypto\CryptoInterface
    {
        return self::resolve(Crypto\CryptoInterface::class) ?? new Crypto\CryptoGen();
    }

    public static function getLogger(): LoggerInterface
    {
        return self::resolve(LoggerInterface::class) ?? new Logging\SystemLogger();
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        return self::resolve(RequestFactoryInterface::class) ?? new Psr17Factory();
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        return self::resolve(ResponseFactoryInterface::class) ?? new Psr17Factory();
    }

    public static function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return self::resolve(ServerRequestFactoryInterface::class) ?? new Psr17Factory();
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        return self::resolve(StreamFactoryInterface::class) ?? new Psr17Factory();
    }

    public static function getUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return self::resolve(UploadedFileFactoryInterface::class) ?? new Psr17Factory();
    }

    public static function getUriFactory(): UriFactoryInterface
    {
        return self::resolve(UriFactoryInterface::class) ?? new Psr17Factory();
    }
}
