<?php

/**
 * For tracking session in database
 *  At this time only used for lastupdate tracking. Using for this case since this is used on essentially
 *   every script and avoiding use of functions in SessionUtil that prevent session locking since may
 *   cause session concurrency issues.
 *  Note these are maintained automatically and cleared out after 7 days of inactivity.
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
        $last_updated = sqlQueryNoLog("SELECT `last_updated` FROM `session_tracker` WHERE `uuid` = ?", $_SESSION['session_database_uuid'])['last_updated'];
        if (empty($last_updated)) {
            error_log("OpenEMR Error: session entry in session_tracker table is missing");
            return true;
        }
        if ((time() - strtotime($last_updated)) > $GLOBALS['timeout']) {
            return true;
        }

        // session is not expired
        return false;
    }

    public static function updateSessionExpiration(): void
    {
        sqlStatementNoLog("UPDATE `session_tracker` SET `last_updated` = NOW() WHERE `uuid` = ?", [$_SESSION['session_database_uuid']]);
    }
}
