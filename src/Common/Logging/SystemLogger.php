<?php

namespace OpenEMR\Common\Logging;

use Psr\Log\LoggerInterface;
use Waryway\PhpTraitsLibrary\Singleton;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

/**
 * Class SystemLogger logs information out to the syslog and is a compatabile PSR3 logger.
 * Other loggers can be added  here as needed.  We essentially decorate around the Monolog library
 * but it allows us to remove Monolog if needed in the future, or add additional loggers as needed.
 * @package OpenEMR\Common\Logging
 */
class SystemLogger implements LoggerInterface
{
    use Singleton;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    public function __construct()
    {
        /**
         * We use mono
         */
        $this->logger = new Logger('OpenEMR');
        $logLevel = Logger::DEBUG; // change this if you want to filter what logs you see.

//        $facility = LOG_SYSLOG; // @see syslog constants https://www.php.net/manual/en/network.constants.php
//        // Change the logger level to see what logs you want to log
//        $this->logger->pushHandler(new Monolog\Handler\ErrorLogHandler('OpenEMR - ', $facility, $logLevel));
        $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = array())
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
    public function alert($message, array $context = array())
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
    public function critical($message, array $context = array())
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
    public function error($message, array $context = array())
    {
        $this->logger->error($message, $context);
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
    public function warning($message, array $context = array())
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
    public function notice($message, array $context = array())
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
    public function info($message, array $context = array())
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
    public function debug($message, array $context = array())
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
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}
