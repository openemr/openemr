<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use Psr\Log\LoggerInterface;
use OpenEMR\Common\Crypto;
use OpenEMR\Common\Logging;

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
    public static function getCrypto(): Crypto\CryptoInterface
    {
        return new Crypto\CryptoGen();
    }

    public static function getLogger(): LoggerInterface
    {
        return new Logging\SystemLogger();
    }
}
