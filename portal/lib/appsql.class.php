<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../interface/globals.php');

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;

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
            EventAuditLogger::instance()->auditSQLEvent($sql, $result, $params);
        }

        return $return;
    }

    public function getPortalAuditRec($recid)
    {
        $return = false;
        $result = false;
        try {
            $sql = "Select * From onsite_portal_activity Where  id = ?";
            $return = sqlStatementNoLog($sql, $recid);
            $result = true;
        } catch (Exception $e) {
            $this->errorHandler($e, $sql);
        }
        if ($result === true) {
            return sqlFetchArray($return);
        } else {
            return false;
        }
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
            $sql = "Select * From onsite_portal_activity As pa Where  pa.patient_id = ? And  pa.activity = ? And  pa.require_audit = ? " .
                                    "And pa.status = ? And  pa.pending_action = ? ORDER BY pa.date ASC LIMIT 1";
            $return = sqlStatementNoLog($sql, $audit);
            $result = true;
        } catch (Exception $e) {
            if ($error) {
                $this->errorHandler($e, $sql, $audit);
            }
        }

        if ($oelog) {
            EventAuditLogger::instance()->auditSQLEvent($sql, $result, $audit);
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
    public function portalAudit(string $type = null, string $rec = null, array $auditvals, $oelog = true, $error = true)
    {
        $return = false;
        $result = false;
        $audit = array ();
        if (!$type) {
            $type = 'insert';
        }
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
                $logsql = "INSERT INTO onsite_portal_activity" .
                        "( date, patient_id, activity, require_audit, pending_action, action_taken, status, narrative," .
                            "table_action, table_args, action_user, action_taken_time, checksum) " .
                                "VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            } else {
                $logsql = "update onsite_portal_activity set date=?, patient_id=?, activity=?, require_audit=?," .
                        "            pending_action=?, action_taken=?,status=?, narrative=?, table_action=?, table_args=?," .
                                        "action_user=?, action_taken_time=?, checksum=? ";
                $logsql .= "where id='" . add_escape_custom($rec) . "' And patient_id='" . add_escape_custom($audit['patient_id']) . "'";
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
        $user = isset($_SESSION['portal_username']) ? $_SESSION['portal_username'] : $_SESSION['authUser'] ?? null;
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
     * Path /library/sql.inc.php
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
        error_log("ERROR: " . htmlspecialchars($logMsg, ENT_QUOTES), 0);
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
        } elseif ($format == 1) {
            $date_format = 'mm/dd/yyyy';
        } elseif ($format == 2) {
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
     * Using generate id function from OpenEMR sql.inc.php library file
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
        EventAuditLogger::instance()->recordLogItem($success, $event, $user, $groupname, $comments, $patient_id, null, $log_from, null, $ccda_doc_id, $user_notes);
    }
}// app query class
