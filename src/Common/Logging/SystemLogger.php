<?php

namespace OpenEMR\Common\Logging;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

/**
 * Class SystemLogger logs information out to the syslog and is a compatible PSR3 logger.
 * Other loggers can be added  here as needed.  We essentially decorate around the Monolog library
 * but it allows us to remove Monolog if needed in the future, or add additional loggers as needed.
 *
 * TODO: The constructor accepts Monolog-specific level types. To align with PSR-3:
 * 1. Accept PSR-3 log level strings (Psr\Log\LogLevel constants) instead of Monolog\Level
 * 2. Convert to Monolog levels internally using Logger::toMonologLevel()
 * This would allow SystemLoggerAwareTrait to use PSR-3 types in its public API.
 *
 * @package OpenEMR\Common\Logging
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SystemLogger implements LoggerInterface
{
    use LoggerTrait;

    private LoggerInterface $logger;

    const LOG_LEVEL_DEBUG = "DEBUG";

    public function __construct($logLevel = null)
    {
        /**
         * We use mono
         */
        $this->logger = new Logger('OpenEMR');

        // Override switch (this allows hard-coded setting of log level since there are several
        //  cases that are outside of the globals context if the developer needs to turn on
        //  DEBUG for them)
        // $logLevel = Logger::DEBUG;

        // Set log level per global setting (if set) if not hardcoded above
        if (empty($logLevel)) {
            if (!empty(OEGlobalsBag::getInstance()->get('system_error_logging')) && (OEGlobalsBag::getInstance()->get('system_error_logging') == "DEBUG")) {
                $logLevel = Logger::DEBUG;
            } else {
                $logLevel = Logger::WARNING;
            }
        }

//        $facility = LOG_SYSLOG; // @see syslog constants https://www.php.net/manual/en/network.constants.php
//        // Change the logger level to see what logs you want to log
//        $this->logger->pushHandler(new Monolog\Handler\ErrorLogHandler('OpenEMR - ', $facility, $logLevel));
        $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));
        $this->logger->pushProcessor(new PsrLogMessageProcessor(removeUsedContextFields: true));
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
