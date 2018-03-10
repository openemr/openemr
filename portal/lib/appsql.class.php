<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
// namespace OnsitePortal;
/**
 *
 * @param
 *            wrapper class for moving some care coordination zend product
 */
require_once(dirname(__FILE__) . '/../../library/sql.inc');
require_once(dirname(__FILE__) . '/../../library/crypto.php');
class ApplicationTable
{

    public function __construct()
    {
    }

    /**
     * Function zQuery
     * All DB Transactions take place
     *
     * @param String $sql
     *            SQL Query Statment
     * @param array $params
     *            SQL Parameters
     * @param boolean $log
     *            Logging True / False
     * @param boolean $error
     *            Error Display True / False
     * @return type
     */
    public function zQuery($sql, $params = '', $log = false, $error = true)
    {
        $return = false;
        $result = false;

        try {
            $return = sqlStatement($sql, $params);
            $result = true;
        } catch (Exception $e) {
            if ($error) {
                $this->errorHandler($e, $sql, $params);
            }
        }

        if ($log) {
            auditSQLEvent($sql, $result, $params);
        }

        return $return;
    }
    public function getPortalAudit($patientid, $action = 'review', $activity = 'profile', $status = 'waiting', $auditflg = 1, $rtn = 'last', $oelog = true, $error = true)
    {
        $return = false;
        $result = false;
        $audit = array (
                    $patientid,
                    $activity,
                    $auditflg,
                    $status,
                    $action
            );
        try {
            $sql = "Select * From onsite_portal_activity As pa Where  pa.patient_id = ? And  pa.activity = ? And  pa.require_audit = ? ".
                                    "And pa.status = ? And  pa.pending_action = ? ORDER BY pa.date DESC LIMIT 1"; // @todo setup condional for limit
            $return = sqlStatementNoLog($sql, $audit);
            $result = true;
        } catch (Exception $e) {
            if ($error) {
                $this->errorHandler($e, $logsql, $audit);
            }
        }

        if ($oelog) {
            auditSQLEvent($sql, $result, $audit);
        }

        if ($rtn == 'last') {
            return sqlFetchArray($return);
        } else {
            return $return;
        }
    }
    /**
     * Function portalAudit
     * All Portal audit Transactions log
     * Hoping to work both ends, patient and user, from one or most two tables
     *
     * @param String $sql
     *            SQL Query Statment for actions will execute sql as normal for cases
     *            user auth is not required.
     * @param array $params
     *            Parameters for actions
     * @param array $auditvals
     *            Parameters of audit
     * @param boolean $log
     *            openemr Logging True / False
     * @param boolean $error
     *            Error Display True / False
     * @param type audit array params for portal audits
     *         $audit = Array();
     *         $audit['patient_id']="";
     *         $audit['activity']="";
     *         $audit['require_audit']="";
     *         $audit['pending_action']="";
     *         $audit['action_taken']="";
     *         $audit['status']="";
     *         $audit['narrative']="";
     *         $audit['table_action']=""; //auth user action sql to run after review
     *         $audit['table_args']=""; //auth user action data to run after review
     *         $audit['action_user']="";
     *         $audit['action_taken_time']="";
     *         $audit['checksum']="";
     */
    public function portalAudit($type = 'insert', $rec = '', array $auditvals, $oelog = true, $error = true)
    {
        $return = false;
        $result = false;
        $audit = array ();
        if ($type != 'insert') {
            $audit['date'] = $auditvals['date'] ? $auditvals['date'] : date("Y-m-d H:i:s");
        }

        $audit['patient_id'] = $auditvals['patient_id'] ? $auditvals['patient_id'] : $_SESSION['pid'];
        $audit['activity'] = $auditvals['activity'] ? $auditvals['activity'] : "";
        $audit['require_audit'] = $auditvals['require_audit'] ? $auditvals['require_audit'] : "";
        $audit['pending_action'] = $auditvals['pending_action'] ? $auditvals['pending_action'] : "";
        $audit['action_taken'] = $auditvals['action_taken'] ? $auditvals['action_taken'] : "";
        $audit['status'] = $auditvals['status'] ? $auditvals['status'] : "new";
        $audit['narrative'] = $auditvals['narrative'] ? $auditvals['narrative'] : "";
        $audit['table_action'] = $auditvals['table_action'] ? $auditvals['table_action'] : "";
        if ($auditvals['activity'] == 'profile') {
            $audit['table_args'] = serialize($auditvals['table_args']);
        } else {
            $audit['table_args'] = $auditvals['table_args'];
        }

        $audit['action_user'] = $auditvals['action_user'] ? $auditvals['action_user'] : "";
        $audit['action_taken_time'] = $auditvals['action_taken_time'] ? $auditvals['action_taken_time'] : "";
        $audit['checksum'] = $auditvals['checksum'] ? $auditvals['checksum'] : "";

        try {
            if ($type != 'update') {
                $logsql = "INSERT INTO onsite_portal_activity".
                        "( date, patient_id, activity, require_audit, pending_action, action_taken, status, narrative,".
                            "table_action, table_args, action_user, action_taken_time, checksum) ".
                                "VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            } else {
                $logsql = "update onsite_portal_activity set date=?, patient_id=?, activity=?, require_audit=?,".
                        "            pending_action=?, action_taken=?,status=?, narrative=?, table_action=?, table_args=?,".
                                        "action_user=?, action_taken_time=?, checksum=? ";
                $logsql .= "where id=".$rec ." And patient_id=".$audit['patient_id'];
            }

            $return = sqlStatementNoLog($logsql, $audit);
            $result = true;
        } catch (Exception $e) {
            if ($error) {
                $this->errorHandler($e, $logsql, $audit);
            }
        }

        if ($oelog) {
            $this->portalLog('profile audit transaction', $audit['patient_id'], $logsql, $audit, $result, 'See portal audit activity');
            //auditSQLEvent( $logsql, $result, $audit );
        }

        return $return;
    }

    public function portalLog($event = '', $patient_id = null, $comments = "", $binds = '', $success = '1', $user_notes = '', $ccda_doc_id = 0)
    {
        $groupname = isset($GLOBALS['groupname']) ? $GLOBALS['groupname'] : 'none';
        $user = isset($_SESSION['portal_username']) ? $_SESSION['portal_username'] : $_SESSION['authUser'];
        $log_from = isset($_SESSION['portal_username']) ? 'onsite-portal' : 'portal-dashboard';
        if (!isset($_SESSION['portal_username']) && !isset($_SESSION['authUser'])) {
            $log_from = 'portal-login';
        }

        $user_notes .= isset($_SESSION['whereto']) ? (' Module:' . $_SESSION['whereto']) : "";

        $processed_binds = "";
        if (is_array($binds)) {
            $first_loop = true;
            foreach ($binds as $value_bind) {
                if ($first_loop) {
                    $processed_binds .= "'" . add_escape_custom($value_bind) . "'";
                    $first_loop = false;
                } else {
                    $processed_binds .= ",'" . add_escape_custom($value_bind) . "'";
                }
            }

            if (! empty($processed_binds)) {
                $processed_binds = "(" . $processed_binds . ")";
                $comments .= " " . $processed_binds;
            }
        }

        $this->portalNewEvent($event, $user, $groupname, $success, $comments, $patient_id, $log_from, $user_notes, $ccda_doc_id);
    }
    /**
     * Function errorHandler
     * All error display and log
     * Display the Error, Line and File
     * Same behavior of HelpfulDie fuction in OpenEMR
     * Path /library/sql.inc
     *
     * @param type $e
     * @param string $sql
     * @param array $binds
     */
    public function errorHandler($e, $sql, $binds = '')
    {
        $trace = $e->getTraceAsString();
        $nLast = strpos($trace, '[internal function]');
        $trace = substr($trace, 0, ( $nLast - 3 ));
        $logMsg = '';
        do {
            $logMsg .= "\r Exception: " . self::escapeHtml($e->getMessage());
        } while ($e = $e->getPrevious());
        /**
         * List all Params
         */
        $processedBinds = "";
        if (is_array($binds)) {
            $firstLoop = true;
            foreach ($binds as $valueBind) {
                if ($firstLoop) {
                    $processedBinds .= "'" . $valueBind . "'";
                    $firstLoop = false;
                } else {
                    $processedBinds .= ",'" . $valueBind . "'";
                }
            }

            if (! empty($processedBinds)) {
                $processedBinds = "(" . $processedBinds . ")";
            }
        }

        echo '<pre><span style="color: red;">';
        echo 'ERROR : ' . $logMsg;
        echo "\r\n";
        echo 'SQL statement : ' . self::escapeHtml($sql);
        echo self::escapeHtml($processedBinds);
        echo '</span></pre>';
        echo '<pre>';
        echo $trace;
        echo '</pre>';
        /**
         * Error Logging
         */
        $logMsg .= "\n SQL statement : $sql" . $processedBinds;
        $logMsg .= "\n $trace";
        error_log("ERROR: " . $logMsg, 0);
    }
    public function escapeHtml($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }
    /*
     * Retrive the data format from GLOBALS
     *
     * @param Date format set in GLOBALS
     * @return Date format in PHP
     */
    public function dateFormat($format)
    {
        if ($format == "0") {
            $date_format = 'yyyy/mm/dd';
        } else if ($format == 1) {
            $date_format = 'mm/dd/yyyy';
        } else if ($format == 2) {
            $date_format = 'dd/mm/yyyy';
        } else {
            $date_format = $format;
        }

        return $date_format;
    }
    /**
     * fixDate - Date Conversion Between Different Formats
     *
     * @param String $input_date
     *            Date to be converted
     * @param String $date_format
     *            Target Date Format
     */
    public function fixDate($input_date, $output_format = null, $input_format = null)
    {
        if (! $input_date) {
            return;
        }

        $input_date = preg_replace('/T|Z/', ' ', $input_date);

        $temp = explode(' ', $input_date); // split using space and consider the first portion, in case of date with time
        $input_date = $temp[0];

        $output_format = ApplicationTable::dateFormat($output_format);
        $input_format = ApplicationTable::dateFormat($input_format);

        preg_match("/[^ymd]/", $output_format, $date_seperator_output);
        $seperator_output = $date_seperator_output[0];
        $output_date_arr = explode($seperator_output, $output_format);

        preg_match("/[^ymd]/", $input_format, $date_seperator_input);
        $seperator_input = $date_seperator_input[0];
        $input_date_array = explode($seperator_input, $input_format);

        preg_match("/[^1234567890]/", $input_date, $date_seperator_input);
        $seperator_input = $date_seperator_input[0];
        $input_date_arr = explode($seperator_input, $input_date);

        foreach ($output_date_arr as $key => $format) {
            $index = array_search($format, $input_date_array);
            $output_date_arr[$key] = $input_date_arr[$index];
        }

        $output_date = implode($seperator_output, $output_date_arr);

        $output_date = $temp[1] ? $output_date . " " . $temp[1] : $output_date; // append the time, if exists, with the new formatted date
        return $output_date;
    }

    /*
     * Using generate id function from OpenEMR sql.inc library file
     * @param string $seqname table name containing sequence (default is adodbseq)
     * @param integer $startID id to start with for a new sequence (default is 1)
     * @return integer returns the sequence integer
     */
    public function generateSequenceID()
    {
        return generate_id();
    }
    public function portalNewEvent($event, $user, $groupname, $success, $comments = "", $patient_id = null, $log_from = '', $user_notes = "", $ccda_doc_id = 0)
    {
        $adodb = $GLOBALS['adodb']['db'];
        $crt_user = isset($_SERVER['SSL_CLIENT_S_DN_CN']) ? $_SERVER['SSL_CLIENT_S_DN_CN'] : null;

        $encrypt_comment = 'No';
        if (! empty($comments)) {
            if ($GLOBALS["enable_auditlog_encryption"]) {
                $comments = aes256Encrypt($comments);
                $encrypt_comment = 'Yes';
            }
        }

        $sql = "insert into log ( date, event, user, groupname, success, comments, log_from, crt_user, patient_id, user_notes) " . "values ( NOW(), " . $adodb->qstr($event) . "," .
            $adodb->qstr($user) . "," . $adodb->qstr($groupname) . "," . $adodb->qstr($success) . "," .
            $adodb->qstr($comments) . "," . $adodb->qstr($log_from) . "," . $adodb->qstr($crt_user) . "," .
            $adodb->qstr($patient_id) . "," . $adodb->qstr($user_notes) .")";

        $ret = sqlInsertClean_audit($sql);

        $last_log_id = $GLOBALS['adodb']['db']->Insert_ID();
        $encryptLogQry = "INSERT INTO log_comment_encrypt (log_id, encrypt, checksum) " . " VALUES ( " . $adodb->qstr($last_log_id) . "," . $adodb->qstr($encrypt_comment) . "," . "'')";
        sqlInsertClean_audit($encryptLogQry);

        if (( $patient_id == "NULL" ) || ( $patient_id == null )) {
            $patient_id = 0;
        }

        send_atna_audit_msg($user, $groupname, $event, $patient_id, $success, $comments);
    }
}// app query class
