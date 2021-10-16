<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

namespace OpenEMR\Modules\LifeMesh;

use OpenEMR\Common\Crypto;

class Database
{
    public $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new Crypto\CryptoGen();
    }

    private function createLifemeshDb()
    {
        $DBSQL_SESSIONS = <<<'DB'
CREATE TABLE IF NOT EXISTS lifemesh_chime_sessions(
  `id`            int         NOT NULL primary key AUTO_INCREMENT comment 'Primary Key',
  `pc_eid`        int(11)     NOT NULL UNIQUE comment 'Event ID from Calendar Table',
  `meeting_id`    VARCHAR(50) NOT NULL comment 'chime session ID',
  `patient_code`  VARCHAR(8)  NOT NULL comment 'Patient PIN',
  `patient_uri`   TEXT        comment 'Patient URI',
  `provider_code` VARCHAR(8)  NOT NULL comment 'Provider PIN',
  `provider_uri`  TEXT        comment 'Provider URI',
  `event_date`    DATE    DEFAULT NULL,
  `event_time`    TIME    DEFAULT NULL,
  `event_status`  VARCHAR(15)  NOT NULL,
  `cancelled`     tinyint(4) NOT NULL DEFAULT 0,
  `updatedAt`     DATETIME    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB COMMENT = 'lifemesh chime sessions';
DB;

        $DBSQL = <<<'DB'
 CREATE TABLE IF NOT EXISTS `lifemesh_account`
(
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` TEXT,
    `password` TEXT,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB COMMENT = 'Lifemesh Telehealth';
DB;
        $db = $GLOBALS['dbase'];
        $exist = sqlQuery("SHOW TABLES FROM `$db` LIKE 'lifemesh_account'");
        if (empty($exist)) {
             sqlQuery($DBSQL);
             sqlQuery($DBSQL_SESSIONS);
        }

    }

    /**
     * @return string
     */
    public function doesTableExist()
    {
        $db = $GLOBALS['dbase'];
        $exist = sqlQuery("SHOW TABLES FROM `$db` LIKE 'lifemesh_account'");
        if (empty($exist)) {
            self::createLifemeshDb();
            return "created";
        } else {
            return "exist";
        }
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function saveUserInformation($username, $password)
    {
        $pass = $this->cryptoGen->encryptStandard($password);
        $sql = "INSERT INTO `lifemesh_account` SET `id` = 1, `username` = ?, `password` = ?";
        sqlStatement($sql, [$username, $pass]);
        return true;
    }

    /**
     * @return string
     */
    public function removeAccountInfo()
    {
        $sql = "DELETE FROM `lifemesh_account`";
        sqlStatement($sql);
        return "completed";
    }

    /**
     * @return array or boolean
     */
    public function getCredentials()
    {
        $returnArray = [];
        $credentials = sqlQuery("SELECT `username`, `password` FROM `lifemesh_account`");
        if (!empty($credentials)) {
            $pass = $this->cryptoGen->decryptStandard($credentials['password']);
            $returnArray[] = $pass;
            $returnArray[] = $credentials['username'];
            return $returnArray;
        } else {
            return false;
        }
    }

    /**
     * @param $pid
     * @return array|false|void|null
     */
    public function getPatientDetails($pid)
    {
        $sql = "SELECT `email`, `phone_cell` FROM `patient_data` where `pid` = ?";
        $comm = sqlQuery($sql, [$pid]);
        if ($comm['phone_cell'] == '') {
            die('Please add cell number to patient chart and save appointment again to create life mesh service token');
        }
        if ($comm['email'] == '') {
            die('Please add email address to patient chart and save appointment again to create life mesh service token');
        }
        return $comm;
    }

    /**
     * @return mixed
     */
    public function getTimeZone()
    {
        $programtz = sqlQuery("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'gbl_time_zone'");
        if (empty($programtz['gl_value'])) {
            return date_default_timezone_get();
        } else {
            return $programtz['gl_value'];
        }
    }

    /**
     * @param $eventid
     * @param $meetingid
     * @param $patient_code
     * @param $patient_uri
     * @param $provider_code
     * @param $provider_uri
     * @param $event_date
     * @param $event_time
     * @param $event_status
     * @param $updatedAt
     */
    public function saveSessionData($eventid,
                                    $meetingid,
                                    $patient_code,
                                    $patient_uri,
                                    $provider_code,
                                    $provider_uri,
                                    $event_date,
                                    $event_time,
                                    $event_status,
                                    $updatedAt)
    {
        $sql = "REPLACE INTO `lifemesh_chime_sessions` SET `pc_eid` = ?, " .
            "`meeting_id` = ?, " .
            "`patient_code` = ?, " .
            "`patient_uri` = ?, " .
            "`provider_code` = ?, " .
            "`provider_uri` = ?, " .
            "`event_date` = ?, " .
            "`event_time` = ?, " .
            "`event_status` = ?, " .
            "`updatedAt` = ? ";

        sqlStatement($sql, [$eventid,
            $meetingid,
            $patient_code,
            $patient_uri,
            $provider_code,
            $provider_uri,
            $event_date,
            $event_time,
            $event_status,
            $updatedAt]);
    }

    /**
     * @param $eventid
     * @return array
     */
    public function hasAppointment($eventid)
    {
        $sql = "select `event_date`, `event_time` from `lifemesh_chime_sessions` where `pc_eid` = ?";
        $appt = sqlQuery($sql, [$eventid]);
        return $appt;
    }

    /**
     * @param $eventid
     * @param $eventdatetime
     */
    public function updateSession($eventid, $eventdatetime)
    {
        $sql = "update `lifemesh_chime_sessions` set `event_date` = ?, `event_time` = ?, `updatedAt` = NOW(), "
                                  ." `event_status` = 'Rescheduled' WHERE `pc_eid` = ?";
        $time = explode("T", $eventdatetime);
        sqlStatement($sql, [$eventdatetime, $time[1], $eventid]);
    }

    public function getStoredSession($eid)
    {
        $sql = "select `provider_code`, `provider_uri`, `cancelled` from `lifemesh_chime_sessions` where `pc_eid` = ?";
        return sqlQuery($sql, [$eid]);
    }

    /**
     * @param $eventid
     */
    public function cancelSessionDatabase($eventid)
    {
        $sql = "update `lifemesh_chime_sessions` set `cancelled` = 1 WHERE `pc_eid` = ?";
        sqlStatement($sql, [$eventid]);
    }
}
