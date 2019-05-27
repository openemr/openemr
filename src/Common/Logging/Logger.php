<?php
/**
 * Application logger. Puts daily logs in the server's temporary directory. Log level is set in
 * globals as $GLOBALS["log_level"] (this is customizable in the globals file).

 * A *nix user can run the following command to see the logs:
 * <code>
 *     > tail -f /var/www/openemr/logs/2016_11_24_application.log
 *     2016-11-24 20:15:07 [TRACE] \common\database\Connector - Connecting with pooled mode
 *     2016-11-24 20:15:07 [TRACE] \common\database\Connector - Wiring up Doctrine entities
 *     2016-11-24 20:15:07 [TRACE] \common\database\Connector - Creating connection
 *     2016-11-24 20:15:07 [INFO] \some\other\Class - Some info message
 *     2016-11-24 20:18:01 [WARN] \some\other\Class - Some field is missing
 * </code>
 *
 * @note Error level logs will also be sent to Apache error log.
 *
 * @note Application logging is sparse at the moment (will be introduced w/ the modernization project).
 *
 * Copyright (C) 2016 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Common\Logging;

class Logger
{
    /**
     * The class that is associated with a log entry.
     */
    private $classContext;

    /**
     * The fully qualified log file path.
     */
    private $logFile;

    /**
     * Default constructor. Only continues with instance creation
     * if logging is enabled.
     *
     * @param $classContext - provided when a class uses the logger.
     */
    public function __construct($classContext = "UnknownClassContext")
    {
        if (isset($GLOBALS["log_level"]) && $GLOBALS["log_level"] !== "OFF") {
            $this->classContext = $classContext;
            $this->determineLogFilePath();
        }
    }

    /**
     * Sets the log file on the operating system's temporary directory. Format is:
     * [log area] + FILE_SEP + YYYY_MM_DD_application.log
     *
     * On *nix, the file will be stored in /home/current_user/openemr/ (if writable). On Windows, it will
     * be stored in C:\Users\current_user\openemr\ (if writable).
     */
    private function determineLogFilePath()
    {
        $fileName = date("Y_m_d") . "_application.log";

        global $webserver_root;
        $currentDir = $webserver_root;
        $logDirName = 'logs';
        $combinedLogDir = $currentDir . DIRECTORY_SEPARATOR . $logDirName;

        if (!is_dir($combinedLogDir)) {
            mkdir($combinedLogDir);
        }

        if (is_writable($combinedLogDir)) {
            $this->logFile = $combinedLogDir . DIRECTORY_SEPARATOR . $fileName;
        } else {
            error_log('Can\'t write application log file to ' . errorLogEscape($combinedLogDir));
        }
    }

    /**
     * Determines if the log level is allowed by the log level in the global
     * configuration.
     *
     * Hierarchy/conditions:
     *     - TRACE (allows TRACE, DEBUG, INFO, WARN, ERROR)
     *     - DEBUG (allows DEBUG, INFO, WARN, ERROR)
     *     - INFO  (allows INFO, WARN, ERROR)
     *     - WARN  (allows WARN, ERROR)
     *     - ERROR (allows ERROR)
     *     - OFF   (no logs)
     *
     * @param $level the incoming log level
     * @return boolean that represents if log entry should be made
     */
    private function isLogLevelInDesiredHierarchy($level)
    {
        if (empty($GLOBALS["log_level"]) || $GLOBALS["log_level"] === "OFF") {
            return false;
        }

        if ($GLOBALS["log_level"] === "TRACE") {
            return true;
        } else if ($GLOBALS["log_level"] === "DEBUG" && in_array($level, array("DEBUG", "INFO", "WARN", "ERROR"))) {
            return true;
        } else if ($GLOBALS["log_level"] === "INFO" && in_array($level, array("INFO", "WARN", "ERROR"))) {
            return true;
        } else if ($GLOBALS["log_level"] === "WARN" && in_array($level, array("WARN", "ERROR"))) {
            return true;
        } else if ($GLOBALS["log_level"] === "ERROR" && $level === "ERROR") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Used for informational messages of the application.
     *
     * @param $message - the log message
     */
    public function info($message)
    {
        $this->log($message, "INFO");
    }

    /**
     * Used by developers that wish to expose information that is
     * notable for developers.
     *
     * @param $message - the log message
     */
    public function debug($message)
    {
        $this->log($message, "DEBUG");
    }

    /**
     * Used by developers that wish to expose information that is
     * notable for developers. Note this is more "noisy" than debug.
     *
     * @param $message - the log message
     */
    public function trace($message)
    {
        $this->log($message, "TRACE");
    }

    /**
     * Used for in case of harmful conditions.
     *
     * @param $message - the log message
     */
    public function warn($message)
    {
        $this->log($message, "WARN");
    }

    /**
     * Used for in case an error occurs and the application might continue running.
     *
     * @param $message - the log message
     */
    public function error($message)
    {
        $this->log($message, "ERROR");
    }

    /**
     * Writes the entry to the log file. If the level is type "error", the message
     * will additionally go to the Apache error log.
     *
     * @param $message - the log message
     * @param $type - the log type
     */
    private function log($message, $type)
    {
        if ($this->isLogLevelInDesiredHierarchy($type) && !empty($this->logFile)) {
            $logEntry = date("Y-m-d H:i:s") . " [" . $type . "] " . $this->classContext . " - " . $message;

            file_put_contents($this->logFile, $logEntry.PHP_EOL, FILE_APPEND | LOCK_EX);

            if ($type === "ERROR") {
                error_log(errorLogEscape($message));
            }
        }
    }
}
