<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
  `patient_uri`   TEXT        NOT NULL comment 'Patient URI',
  `provider_code` VARCHAR(8)  NOT NULL comment 'Provider PIN',
  `provider_uri`  TEXT        NOT NULL comment 'Provider URI',
  `event_date`    DATE    NOT NULL,
  `event_time`    TIME    NOT NULL,
  `event_status`  VARCHAR(15)  NOT NULL,
  `updatedAt`     DATETIME    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB COMMENT = 'lifemesh chime sessions';
DB;

        $DBSQL = <<<'DB'
 CREATE TABLE IF NOT EXISTS `lifemesh_account`
(
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` TEXT DEFAULT NULL,
    `password` TEXT DEFAULT NULL,
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
        $sql = "INSERT INTO lifemesh_account SET id = 1, username = ?, password = ?";
        sqlStatement($sql, [$username, $pass]);
        return true;
    }

    /**
     * @return string
     */
    public function removeAccountInfo()
    {
        $sql = "DELETE FROM lifemesh_account";
         sqlStatement($sql);
         return "completed";
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        $returnArray = [];
        $credentials = sqlQuery("SELECT username, password FROM lifemesh_account");
        $pass = $this->cryptoGen->decryptStandard($credentials['password']);
        $returnArray[] = $pass;
        $returnArray[] = $credentials['username'];
        return $returnArray;
    }

    /**
     * @param $pid
     * @return array|false|void|null
     */
    public function getPatientDetails($pid)
    {
        $sql = "SELECT email, phone_cell FROM patient_data where pid = ?";
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
        $programtz = sqlQuery("SELECT gl_value FROM `globals` WHERE `gl_name` = 'gbl_time_zone'");
        return $programtz['gl_value'];
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
        $sql = "REPLACE INTO lifemesh_chime_sessions SET pc_eid = ?, " .
            "meeting_id = ?, " .
            "patient_code = ?, " .
            "patient_uri = ?, " .
            "provider_code = ?, " .
            "provider_uri = ?, " .
            "event_date = ?, " .
            "event_time = ?, " .
            "event_status = ?, " .
            "updatedAt = ? ";

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
        $sql = "select event_date, event_time from lifemesh_chime_sessions where pc_eid = ?";
        $appt = sqlQuery($sql, [$eventid]);
        return $appt;
    }

    /**
     * @param $eventid
     * @param $eventdatetime
     */
    public function updateSession($eventid, $eventdatetime)
    {
        $sql = "update lifemesh_chime_sessions set event_date = ?, event_time = ?, updatedAt = NOW(), "
                                  ." event_status = 'Rescheduled' WHERE pc_eid = ?";
        $time = explode("T", $eventdatetime);
        sqlStatement($sql, [$eventdatetime, $time[1], $eventid]);
    }

    public function getStoredSession($eid)
    {
        $sql = "select provider_code, provider_uri from lifemesh_chime_sessions where pc_eid = ?";
        return sqlQuery($sql, [$eid]);
    }
}
