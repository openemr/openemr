<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

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
     *            SQL Query Statement
     * @param array $params
     *            SQL Parameters
     * @param bool $log
     *            Logging True / False
     * @param bool $error
     *            Error Display True / False
     * @return mixed
     */
    public function zQuery($sql, $params = '', $log = false, $error = true)
    {
        $return = false;
        $result = false;

        try {
            $return = sqlStatement($sql, $params);
            $result = true;
        } catch (\Throwable $e) {
            if ($error) {
                $this->errorHandler($e, $sql, $params);
            }
        }

        if ($log) {
            EventAuditLogger::getInstance()->auditSQLEvent($sql, $result, $params);
        }

        return $return;
    }

    public function portalLog($event = '', mixed $patient_id = null, $comments = "", $binds = '', $success = '1', $user_notes = '', $ccda_doc_id = 0)
    {
        $globalsBag = OEGlobalsBag::getInstance();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $groupname = $globalsBag->get('groupname') ?? 'none';
        $user = $session->get('portal_username') ?? $session->get('authUser') ?? null;
        $log_from = $session->has('portal_username') ? 'onsite-portal' : 'portal-dashboard';
        if (!$session->has('portal_username') && !$session->has('authUser')) {
            $log_from = 'portal-login';
        }

        $user_notes .= !empty($session->get('whereto')) ? (' Module:' . $session->get('whereto')) : "";

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
     * Same behavior of HelpfulDie function in OpenEMR
     * Path /library/sql.inc.php
     *
     * @param \Throwable $e
     * @param string $sql
     * @param array $binds
     */
    public function errorHandler($e, $sql, $binds = '')
    {
        $trace = $e->getTraceAsString();
        $nLast = strpos((string) $trace, '[internal function]');
        $trace = substr((string) $trace, 0, ( $nLast - 3 ));
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
    public function escapeHtml($string): string
    {
        return htmlspecialchars((string) $string, ENT_QUOTES);
    }
    /*
     * Retrieve the data format from GLOBALS
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

        $temp = explode(' ', (string) $input_date); // split using space and consider the first portion, in case of date with time
        $input_date = $temp[0];

        $output_format = $this->dateFormat($output_format);
        $input_format = $this->dateFormat($input_format);

        preg_match("/[^ymd]/", (string) $output_format, $date_seperator_output);
        $seperator_output = $date_seperator_output[0];
        $output_date_arr = explode($seperator_output, (string) $output_format);

        preg_match("/[^ymd]/", (string) $input_format, $date_seperator_input);
        $seperator_input = $date_seperator_input[0];
        $input_date_array = explode($seperator_input, (string) $input_format);

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
        return QueryUtils::generateId();
    }

    public function portalNewEvent($event, $user, $groupname, $success, $comments = "", mixed $patient_id = null, $log_from = '', $user_notes = "", $ccda_doc_id = 0)
    {
        $patient_id = (is_string($patient_id) || is_int($patient_id)) && $patient_id !== '' ? intval($patient_id) : null;
        EventAuditLogger::getInstance()->recordLogItem($success, $event, $user, $groupname, $comments, $patient_id, null, $log_from, null, $ccda_doc_id, $user_notes);
    }
}// app query class
