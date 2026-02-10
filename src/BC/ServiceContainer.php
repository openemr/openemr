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
    public static function getClock(): ClockInterface
    {
        return SystemClock::fromSystemTimezone();
    }

    public static function getCrypto(): Crypto\CryptoInterface
    {
        return new Crypto\CryptoGen();
    }

    public static function getLogger(): LoggerInterface
    {
        return new Logging\SystemLogger();
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        return new Psr17Factory();
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        return new Psr17Factory();
    }

    public static function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        return new Psr17Factory();
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        return new Psr17Factory();
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
        return new Psr17Factory();
    }

    public static function getUriFactory(): UriFactoryInterface
    {
        return new Psr17Factory();
    }
}
