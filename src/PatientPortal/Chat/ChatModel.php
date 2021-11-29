<?php

/**
 *  Chat Class ChatModel
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PatientPortal\Chat;

class ChatModel
{
    public function getAuthUsers()
    {
        $resultpd = array();
        $result = array();
        if (!IS_PORTAL) {
            $query = "SELECT patient_data.pid as recip_id, Concat_Ws(' ', patient_data.fname, patient_data.lname) as username FROM patient_data " .
                "LEFT JOIN patient_access_onsite pao ON pao.pid = patient_data.pid " .
                "WHERE patient_data.pid = pao.pid AND pao.portal_pwd_status = 1";
            $response = sqlStatementNoLog($query);
            while ($row = sqlFetchArray($response)) {
                $resultpd[] = $row;
            }
        }
        if (IS_PORTAL) {
            $query = "SELECT users.username as recip_id, users.authorized as dash, CONCAT(users.fname,' ',users.lname) as username  " .
                "FROM users WHERE active = 1 AND username > ''";
            $response = sqlStatementNoLog($query);

            while ($row = sqlFetchArray($response)) {
                $result[] = $row;
            }
        }
        $all = array_merge($result, $resultpd);

        return json_encode($all);
    }

    public function getMessages($limit = CHAT_HISTORY, $reverse = true)
    {
        $response = sqlStatementNoLog("(SELECT * FROM onsite_messages
            ORDER BY `date` DESC LIMIT " . escape_limit($limit) . ") ORDER BY `date` ASC");

        $result = array();
        while ($row = sqlFetchArray($response)) {
            if (IS_PORTAL || IS_DASHBOARD) {
                $u = json_decode($row['recip_id'], true);
                if (!is_array($u)) {
                    continue;
                }

                if ((in_array(C_USER, $u)) || $row['sender_id'] == C_USER) {
                    $result[] = $row; // only current patient messages
                }
            } else {
                $result[] = $row; // admin gets all
            }
        }

        return $result;
    }

    public function addMessage($username, $message, $ip, $senderid = 0, $recipid = '')
    {
        return sqlQueryNoLog("INSERT INTO onsite_messages VALUES (NULL, ?, ?, ?, NOW(), ?, ?)", array($username, $message, $ip, $senderid, $recipid));
    }

    public function removeMessages()
    {
        return sqlQueryNoLog("TRUNCATE TABLE onsite_messages");
    }

    public function removeOldMessages($limit = CHAT_HISTORY)
    {
        /* @todo Patched out to replace with soft delete. Besides this query won't work with current ado(or any) */
        /* return sqlStatementNoLog("DELETE FROM onsite_messages
            WHERE id NOT IN (SELECT id FROM onsite_messages
                ORDER BY date DESC LIMIT {$limit})"); */
    }

    public function getOnline($count = true, $timeRange = CHAT_ONLINE_RANGE)
    {
        if ($count) {
            $response = sqlStatementNoLog("SELECT count(*) as total FROM onsite_online");
            return sqlFetchArray($response);
        }

        $response = sqlStatementNoLog("SELECT * FROM onsite_online");
        $result = array();
        while ($row = sqlFetchArray($response)) {
            $result[] = $row;
        }

        return $result;
    }

    public function updateOnline($hash, $ip, $username = '', $userid = 0)
    {
        return sqlStatementNoLog("REPLACE INTO onsite_online
            VALUES ( ?, ?, NOW(), ?, ? )", array($hash, $ip, $username, $userid)) or die(mysql_error());
    }

    public function clearOffline($timeRange = CHAT_ONLINE_RANGE)
    {
        return sqlStatementNoLog("DELETE FROM onsite_online
            WHERE last_update <= (NOW() - INTERVAL " . escape_limit($timeRange) . " MINUTE)");
    }
}
