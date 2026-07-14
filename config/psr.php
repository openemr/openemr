<?php

/**
 * PSR-specific interface-to-implementation mappings
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use GuzzleHttp\{
    Client,
    RequestOptions,
};
use Lcobucci\Clock\SystemClock;
use Monolog\Logger;
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
use OpenEMR\Common\Http\Psr17Factory;

return [
    // PSR-3
    LoggerInterface::class => Logger::class,

    // PSR-17
    RequestFactoryInterface::class => Psr17Factory::class,
    ResponseFactoryInterface::class => Psr17Factory::class,
    ServerRequestFactoryInterface::class => Psr17Factory::class,
    StreamFactoryInterface::class => Psr17Factory::class,
    UploadedFileFactoryInterface::class => Psr17Factory::class,
    UriFactoryInterface::class => Psr17Factory::class,

    // PSR-18
    ClientInterface::class => Client::class,
    Client::class => fn () => new Client([
        // PSR-18 does not specify behavior on following redirects; it's
        // usually the correct thing to do.
        RequestOptions::ALLOW_REDIRECTS => true,
        // Set _reasonable_ timeouts to avoid permanent hangs
        RequestOptions::CONNECT_TIMEOUT => 5,
        RequestOptions::TIMEOUT => 15,
        // PSR-18 explicitly states that implementations must not throw on
        // 4xx/5xx responses.
        RequestOptions::HTTP_ERRORS => false,
    ]),

    // PSR-20
    ClockInterface::class => SystemClock::class,

    // Future (not all will necessarily be added):
    // 6/16: Caching
    // 7: HTTP Messages
    // 11: NO! Putting the container in the container means you're doing it wrong.
    // 13: Hypermedia Links
    // 14: Event Dispatcher
    // 15: HTTP Handlers
];
