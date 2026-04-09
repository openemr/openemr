<?php

/**
 * Generalized service configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use Lcobucci\Clock\SystemClock;
use Monolog\{
    Formatter\LineFormatter,
    Handler\ErrorLogHandler,
    Level,
    Logger,
    Processor\PsrLogMessageProcessor,
};
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Core\ErrorHandler;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    StreamFactoryInterface,
};

return [
    ErrorHandler::class => fn (TC $c) => new ErrorHandler(
        logger: $c->get(LoggerInterface::class),
        rf: $c->get(ResponseFactoryInterface::class),
        sf: $c->get(StreamFactoryInterface::class),
        // Once there are more well-defined environments, set this using them
        shouldDisplayErrors: false,
    ),
    Level::class => fn (TC $c) => Level::fromName($c->get('LOG_LEVEL')),
    Logger::class => function (TC $c) {
        // Duplicated from setup in SystemLogger (for now)
        $logger = new Logger('OpenEMR');
        $handler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $c->get(Level::class));
        $formatter = new LineFormatter("%channel%.%level_name%: %message% %context% %extra%");
        $formatter->includeStacktraces(true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        $logger->pushProcessor(new PsrLogMessageProcessor(removeUsedContextFields: true));
        return $logger;
    },

    Psr17Factory::class,

    SystemClock::class => fn () => SystemClock::fromSystemTimezone(),
];
