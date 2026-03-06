<?php

/**
 * PSR-specific interface-to-implementation mappings
 */

declare(strict_types=1);

use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Logging\SystemLogger;
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
use OpenEMR\Common\Http\Psr17Factory;

return [
    // PSR-3
    LoggerInterface::class => SystemLogger::class,

    // PSR-17
    RequestFactoryInterface::class => Psr17Factory::class,
    ResponseFactoryInterface::class => Psr17Factory::class,
    ServerRequestFactoryInterface::class => Psr17Factory::class,
    StreamFactoryInterface::class => Psr17Factory::class,
    UploadedFileFactoryInterface::class => Psr17Factory::class,
    UriFactoryInterface::class => Psr17Factory::class,

    // PSR-20
    ClockInterface::class => SystemClock::class,

    // Future (not all will necessarily be added):
    // 6/16: Caching
    // 7: HTTP Messages
    // 11: NO! Putting the conatiner in the container means you're doing it wrong.
    // 13: Hypermedia Links
    // 14: Event Dispatcher
    // 15: HTTP Handlers
    // 18: HTTP Client
];
