<?php

namespace OpenEMR\Common\Logging;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class SystemLogger logs information out to the syslog and is a compatible PSR3 logger.
 * Other loggers can be added  here as needed.  We essentially decorate around the Monolog library
 * but it allows us to remove Monolog if needed in the future, or add additional loggers as needed.
 * @package OpenEMR\Common\Logging
 * @link      http://www.open-emr.org
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

    public function __construct()
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
            if (!empty($GLOBALS['system_error_logging']) && ($GLOBALS['system_error_logging'] == "DEBUG")) {
                $logLevel = Logger::DEBUG;
            } else {
                $logLevel = Logger::WARNING;
            }
        }

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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
        $this->logger->error($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.  This function automatically logs the class and function method that invoked the
     * error log.
     * @param $message
     * @param array $context
     */
    public function errorLogCaller($message, array $context = array())
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
    public function warning($message, array $context = array())
    {
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
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
        $context = $this->escapeVariables($context);
        $this->logger->log($level, $message, $context);
    }

    private function escapeVariables($dictionary, $recurseLimit = 0)
    {
        if ($recurseLimit > 25) {
            return "Cannot escape further. Maximum nested limit reached";
        }

        // the inner library may already be safely escaping values, but we don't want to assume that
        // so we go through and make sure we use the OpenEMR errorLogEscape to make sure nothing
        // hits the log file that could be an attack vector
        // if we have a different LogHandler this logic may need to be revisited.
        $escapedDict = [];
        foreach ($dictionary as $key => $value) {
            $escapedKey = $this->escapeValue($key);
            if (is_array($value)) {
                $escapedDict[$key] = $this->escapeVariables($value, $recurseLimit + 1);
            } else if (is_object($value)) {
                try {
                    $object = json_encode($value);
                    $escapedDict[$escapedKey] = $this->escapeValue($object);
                } catch (\Exception $error) {
                    error_log($error->getMessage());
                }
            } else {
                $escapedDict[$escapedKey] = $this->escapeValue($value);
            }
        }
        return $escapedDict;
    }

    /**
     * Safely escape a single value that can be written out to a log file.
     * @param $var
     * @return string
     */
    private function escapeValue($var)
    {
        return errorLogEscape($var);
    }
}
