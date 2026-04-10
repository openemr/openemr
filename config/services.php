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
use League\Flysystem\{
    Filesystem,
    FilesystemAdapter,
    FilesystemOperator,
    FilesystemReader,
    FilesystemWriter,
    Local\LocalFilesystemAdapter,
};
use Monolog\{
    Formatter\LineFormatter,
    Handler\ErrorLogHandler,
    Level,
    Logger,
    Processor\PsrLogMessageProcessor,
};
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Core\ErrorHandler;
use OpenEMR\Services\Storage\{
    Location,
    Manager,
    ManagerInterface,
};
use Psr\Log\LoggerInterface;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    StreamFactoryInterface,
};

return [
    // Error handling
    ErrorHandler::class => fn (TC $c) => new ErrorHandler(
        logger: $c->get(LoggerInterface::class),
        rf: $c->get(ResponseFactoryInterface::class),
        sf: $c->get(StreamFactoryInterface::class),
        // Once there are more well-defined environments, set this using them
        shouldDisplayErrors: false,
    ),

    // Filesystem abstraction
    ManagerInterface::class => Manager::class,
    Manager::class => function (TC $c): Manager {
        $siteDir = sprintf('%s/sites/%s', $c->getString('installRoot'), $c->getString('OPENEMR_SITE'));
        $m = new Manager();
        // For now, use the default paths on the local FS. Eventually this will
        // support more customization.
        foreach (Location::cases() as $location) {
            $path = sprintf('%s/%s', $siteDir, $location->getDefaultPath());
            $m->register($location, new Filesystem(new LocalFilesystemAdapter($path)));
        }
        return $m;
    },
    /* Filesystem::class, */
    /* FilesystemAdapter::class => LocalFilesystemAdapter::class, */
    /* FilesystemOperator::class => Filesystem::class, */
    /* FilesystemReader::class => Filesystem::class, */
    /* FilesystemWriter::class => Filesystem::class, */
    /* LocalFilesystemAdapter::class => fn (TC $c) => new LocalFilesystemAdapter( */
    /*     location: $c->getString('installRoot'), */
    /* ), */
    // (will arrive from elsewhere)
    'installRoot' => fn () => dirname(__DIR__),

    // Logging
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
