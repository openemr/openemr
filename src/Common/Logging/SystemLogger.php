<?php

namespace OpenEMR\Common\Logging;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;

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
    /**
     * @var LoggerInterface;
     */
    private $logger;

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
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.  This function automatically logs the class and function method that invoked the
     * error log.
     * @param $message
     * @param array $context
     */
    public function errorLogCaller($message, array $context = []): void
    {
        // we skip over arguments and go 2 stack traces to get the current call and the caller function into this one.
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerContext = $dbt[1] ?? [];
        $callerClass = $callerContext['class'] ?? "";
        $callerType = $callerContext['type'] ?? "";
        $callerFunction = $callerContext['function'] ?? "";
        $caller = $callerClass . $callerType . $callerFunction;
        if ($caller != "") {
            // make it look like a method signature
            $caller .= "() ";
        }
        $this->error($caller . $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

}
