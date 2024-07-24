<?php

/**
 * For tracking session in database
 *  At this time only used for lastupdate tracking. Using for this case since this is used on essentially
 *   every script and avoiding use of functions in SessionUtil that prevent session locking since may
 *   cause session concurrency issues.
 *  Note these are maintained automatically and cleared out after 7 days of inactivity.
 *  Note that all time collection/derivation is from the mysql/mariadb server (in order to ensure things do not
 *   break in case the time set on the php server and mysql/mariadb server are different).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Uuid\UuidRegistry;

class SessionTracker
{
    public static function setupSessionDatabaseTracker(): void
    {
        // create the session uuid which will use as primary key in database session_tracker table
        $_SESSION['session_database_uuid'] =  (new UuidRegistry(['disable_tracker' => true, 'table_name' => 'session_tracker']))->createUuid();

        // maintenance, remove entries that have not been updated for more than 7 days
        $expiredDateTime = date("Y-m-d H:i:s", strtotime('-7 day'));
        sqlStatementNoLog("DELETE FROM `session_tracker` WHERE `last_updated` < ?", [$expiredDateTime]);

        // insert new entry into database
        sqlStatementNoLog("INSERT INTO `session_tracker` (`uuid`, `created`, `last_updated`) VALUES (? , NOW(), NOW())", [$_SESSION['session_database_uuid']]);
    }

    public static function isSessionExpired(): bool
    {
        if (empty($_SESSION['session_database_uuid'])) {
            error_log("OpenEMR Error: session_database_uuid session variable is missing");
            return true;
        }
        $sessionTracker = sqlQueryNoLog("SELECT `last_updated`, NOW() as `current_time` FROM `session_tracker` WHERE `uuid` = ?", [$_SESSION['session_database_uuid']]);
        if (empty($sessionTracker) || empty($sessionTracker['last_updated']) || empty($sessionTracker['current_time'])) {
            error_log("OpenEMR Error: session entry in session_tracker table is missing or invalid");
            return true;
        }
        $last_updated = strtotime($sessionTracker['last_updated']);
        $current_time = strtotime($sessionTracker['current_time']);
        if ($last_updated > $current_time) {
            error_log("OpenEMR Error: isSessionExpired error (last_updated time is ahead of current time which should be impossible)");
            return true;
        }
        if (($current_time - $last_updated) > $GLOBALS['timeout']) {
            return true;
        }

        // session is not expired
        return false;
    }

    public static function updateSessionExpiration(): void
    {
        sqlStatementNoLog("UPDATE `session_tracker` SET `last_updated` = NOW() WHERE `uuid` = ?", [$_SESSION['session_database_uuid']]);
    }

    // Function to update the throttle down function (ie. counting scripts)
    //  Only basically used for the online demos to prevent abuse of demo farm
    public static function updateSessionThrottleDown(): void
    {
        sqlStatementNoLog("UPDATE `session_tracker` SET `number_scripts` = `number_scripts` + 1 WHERE `uuid` = ?", [$_SESSION['session_database_uuid']]);
    }

    // Function to throttle down requests when using the online demos to prevent abuse of the demo farm
    public static function processSessionThrottleDown($throttleDownWaitMilliseconds): void
    {
        // calculate $timeThrottle['time_throttle'], which will be average time (in milliseconds) per script call
        $timeThrottle = sqlQueryNoLog("SELECT `number_scripts`, `created`, NOW() as `current_timestamp` FROM `session_tracker` WHERE `uuid` = ?", [$_SESSION['session_database_uuid']]);
        $timeThrottle['time_throttle'] = ((new \DateTime($timeThrottle['created']))->format('Uv') + ((int)$throttleDownWaitMilliseconds * $timeThrottle['number_scripts'])) - (new \DateTime($timeThrottle['current_timestamp']))->format('Uv');

        // ensure scripts on average do not go faster than the THROTTLE_DOWN_WAIT_MIllISECONDS' environment setting
        if (($timeThrottle['time_throttle'] ?? 0) > 0) {
            $dieMilliseconds = getenv('THROTTLE_DOWN_DIE_MILLISECONDS', true) ?? 0;
            if ($dieMilliseconds > 0 && ($timeThrottle['time_throttle'] ?? 0) > $dieMilliseconds) {
                // throttle down and die since the 'THROTTLE_DOWN_DIE_MILLISECONDS' environment setting has been exceeded
                error_log("DEBUG: die for script number " . $timeThrottle['number_scripts'] . " for " . $timeThrottle['time_throttle'] . " milliseconds");
                usleep($timeThrottle['time_throttle'] * 1000);
                die(xlt("These demos are not meant for headless server testing. Please do this on your own servers."));
            }
            // throttle down since the 'THROTTLE_DOWN_WAIT_MILLISECONDS' environment setting has been exceeded
            error_log("DEBUG: throttling down for script number " . $timeThrottle['number_scripts'] . " for " . $timeThrottle['time_throttle'] . " milliseconds");
            usleep($timeThrottle['time_throttle'] * 1000);
        }
    }
}
