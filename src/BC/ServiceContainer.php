<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use InvalidArgumentException;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Log\{
    LoggerInterface,
    NullLogger,
};
use OpenEMR\Common\Crypto;
use OpenEMR\Common\Logging;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Storage\Location;
use OpenEMR\Services\Storage\Manager;
use OpenEMR\Services\Storage\ManagerInterface;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Http\Psr17Factory;
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

    /** @var array<class-string, object> */
    private static array $cache = [];

    /**
     * Reset all registered overrides and cached instances. For testing only.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$overrides = [];
        self::$cache = [];
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
     * Resolve a service from cache, overrides, or create via default factory.
     *
     * @template T of object
     * @param class-string<T> $interface
     * @param callable(): T $default
     * @return T
     */
    private static function resolveOrCreate(string $interface, callable $default): object
    {
        if (array_key_exists($interface, self::$cache)) {
            /** @var T */
            return self::$cache[$interface];
        }
        /** @var T */
        $instance = self::$overrides[$interface] ?? $default();
        self::$cache[$interface] = $instance;
        return $instance;
    }

    public static function getClock(): ClockInterface
    {
        return self::resolveOrCreate(
            ClockInterface::class,
            // @phpstan-ignore openemr.deprecatedSqlFunction
            static fn() => SystemClock::fromSystemTimezone(),
        );
    }

    public static function getCrypto(): Crypto\CryptoInterface
    {
        return self::resolveOrCreate(
            Crypto\CryptoInterface::class,
            static fn() => new Crypto\CryptoGen(),
        );
    }

    public static function getLogger(): LoggerInterface
    {
        return self::resolveOrCreate(
            LoggerInterface::class,
            static function () {
                if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                    return new NullLogger();
                }
                return new Logging\SystemLogger();
            },
        );
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        return self::resolveOrCreate(
            RequestFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        return self::resolveOrCreate(
            ResponseFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }

    public static function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return self::resolveOrCreate(
            ServerRequestFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        return self::resolveOrCreate(
            StreamFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }

    public static function getStorageManager(): ManagerInterface
    {
        return self::resolveOrCreate(
            ManagerInterface::class,
            static function () {
                $manager = new Manager();
                $siteDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR');
                foreach (Location::cases() as $location) {
                    $path = $siteDir . '/' . $location->getDefaultPath();
                    $manager->register(
                        $location,
                        new Filesystem(new LocalFilesystemAdapter($path)),
                    );
                }
                return $manager;
            },
        );
    }

    public static function getUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return self::resolveOrCreate(
            UploadedFileFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }

    public static function getUriFactory(): UriFactoryInterface
    {
        return self::resolveOrCreate(
            UriFactoryInterface::class,
            static fn() => new Psr17Factory(),
        );
    }
}
