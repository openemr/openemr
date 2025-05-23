<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Model;

use DateTime;
use Exception;
use Laminas\Db\Adapter\ExceptionInterface;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\ResultSet\ResultSet;
use OpenEMR\Common\Logging\EventAuditLogger;

class ApplicationTable extends AbstractTableGateway
{
    protected $table = 'application';
    protected $adapter;

    /**
     *
     * @param \Laminas\Db\Adapter\Adapter $adapter
     */
    public function __construct()
    {
        // TODO: I can't find any reason why we grab the static adapter instead of injecting a regular DB adapter here...
        $adapter = \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Application());
        $this->initialize();
    }

    /**
     * Function zQuery
     * All DB Transactions take place
     *
     * @param String  $sql    SQL Query Statment
     * @param array   $params SQL Parameters
     * @param boolean $log    Logging True / False
     * @param boolean $error  Error Display True / False
     * @return type
     */
    public function zQuery($sql, $params = '', $log = true, $error = true)
    {
        $return = false;
        $result = false;

        if (!empty($GLOBALS['debug_ssl_mysql_connection'])) {
            $temp_return = $this->adapter->query("SHOW STATUS LIKE 'Ssl_cipher';")->execute();
            foreach ($temp_return as $temp_row) {
                error_log("CHECK SSL CIPHER IN ZEND: " . errorLogEscape(print_r($temp_row, true)));
            }
        }

        try {
            $statement = $this->adapter->query($sql);
            $return = $statement->execute($params);
            $result = true;
        } catch (ExceptionInterface $e) {
            if ($error) {
                $this->errorHandler($e, $sql, $params);
            }
        } catch (\Exception $e) {
            if ($error) {
                $this->errorHandler($e, $sql, $params);
            }
        }

        /**
         * Function auditSQLEvent
         * Logging Mechanism
         *
         * using OpenEMR log function (auditSQLEvent)
         *
         * @see EventAuditLogger::auditSQLEvent
         * Logging, if the $log is true
         */
        if ($log) {
            EventAuditLogger::instance()->auditSQLEvent($sql, $result, $params);
        }

        return $return;
    }

    /**
     * Function errorHandler
     * All error display and log
     * Display the Error, Line and File
     * Same behavior of HelpfulDie fuction in OpenEMR
     * Path /library/sql.inc.php
     *
     * @param type   $e
     * @param string $sql
     * @param array  $binds
     */
    public function errorHandler($e, $sql, $binds = '')
    {
        $escaper = new \Laminas\Escaper\Escaper('utf-8');
        $trace = $e->getTraceAsString();
        $nLast = strpos($trace, '[internal function]');
        $trace = substr($trace, 0, ($nLast - 3));
        $logMsg = '';
        do {
            $logMsg .= "\r Exception: " . $escaper->escapeHtml($e->getMessage());
        } while ($e = $e->getPrevious());
        /** List all Params */
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

            if (!empty($processedBinds)) {
                $processedBinds = "(" . $processedBinds . ")";
            }
        }

        echo '<pre><span style="color: red;">';
        echo 'ERROR : ' . $logMsg;
        echo "\r\n";
        echo 'SQL statement : ' . $escaper->escapeHtml($sql);
        echo $escaper->escapeHtml($processedBinds);
        echo '</span></pre>';
        echo '<pre>';
        echo $trace;
        echo '</pre>';
        /** Error Logging */
        $logMsg .= "\n SQL statement : $sql" . $processedBinds;
        $logMsg .= "\n $trace";
        error_log("ERROR: " . errorLogEscape($logMsg), 0);
    }

    /**
     * Function quoteValue
     * Escape Quotes in the value
     *
     * @param type $value
     * @return type
     */
    public function quoteValue($value)
    {
        return $this->adapter->platform->quoteValue($value);
    }

    /**
     * Function zAclCheck
     * Check ACL in Laminas
     *
     * Same Functionality in the OpemEMR
     * for Left Nav ACL Check
     * Path openemr/src/Common/Acl/AclMain.php
     * Function Name zhAclCheck
     *
     * @param int $user_id Auth user Id
     *                     $param String  $section_identifier ACL Section id
     * @return boolean
     */
    public function zAclCheck($user_id, $section_identifier)
    {
        $sql_user_acl = " SELECT
                                COUNT(allowed) AS count
                            FROM
                                module_acl_user_settings AS usr_settings
                                LEFT JOIN module_acl_sections AS acl_sections
                                    ON usr_settings.section_id = acl_sections.`section_id`
                            WHERE
                                acl_sections.section_identifier = ? AND usr_settings.user_id = ? AND usr_settings.allowed = ?";
        $sql_group_acl = " SELECT
                                COUNT(allowed) AS count
                            FROM
                                module_acl_group_settings AS group_settings
                                LEFT JOIN module_acl_sections AS  acl_sections
                                  ON group_settings.section_id = acl_sections.section_id
                            WHERE
                                acl_sections.`section_identifier` = ? AND group_settings.group_id IN (?) AND group_settings.allowed = ?";
        $sql_user_group = " SELECT
                                gagp.id AS group_id
                            FROM
                                gacl_aro AS garo
                                LEFT JOIN `gacl_groups_aro_map` AS gamp
                                    ON garo.id = gamp.aro_id
                                LEFT JOIN `gacl_aro_groups` AS gagp
                                    ON gagp.id = gamp.group_id
                                RIGHT JOIN `users_secure` usr
                                    ON usr. username =  garo.value
                            WHERE
                                garo.section_value = ? AND usr. id = ?";

        $res_groups = $this->zQuery($sql_user_group, array('users', $user_id));
        $groups = array();
        foreach ($res_groups as $row) {
            array_push($groups, $row['group_id']);
        }

        $groups_str = implode(",", $groups);

        $count_user_denied = 0;
        $count_user_allowed = 0;
        $count_group_denied = 0;
        $count_group_allowed = 0;

        $res_user_denied = $this->zQuery($sql_user_acl, array($section_identifier, $user_id, 0));
        foreach ($res_user_denied as $row) {
            $count_user_denied = $row['count'];
        }

        $res_user_allowed = $this->zQuery($sql_user_acl, array($section_identifier, $user_id, 1));
        foreach ($res_user_allowed as $row) {
            $count_user_allowed = $row['count'];
        }

        $res_group_denied = $this->zQuery($sql_group_acl, array($section_identifier, $groups_str, 0));
        foreach ($res_group_denied as $row) {
            $count_group_denied = $row['count'];
        }

        $res_group_allowed = $this->zQuery($sql_group_acl, array($section_identifier, $groups_str, 1));
        foreach ($res_group_allowed as $row) {
            $count_group_allowed = $row['count'];
        }

        if ($count_user_denied > 0) {
            return false;
        } elseif ($count_user_allowed > 0) {
            return true;
        } elseif ($count_group_denied > 0) {
            return false;
        } elseif ($count_group_allowed > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Auto Suggest
     */
    public function listAutoSuggest($post, $limit)
    {
        $pages = 0;
        $limitEnd = \Application\Plugin\CommonPlugin::escapeLimit($limit);

        if (isset($GLOBALS['set_autosuggest_options'])) {
            if ($GLOBALS['set_autosuggest_options'] == 1) {
                $leading = '%';
            } else {
                $leading = $post->leading;
            }

            if ($GLOBALS['set_autosuggest_options'] == 2) {
                $trailing = '%';
            } else {
                $trailing = $post->trailing;
            }

            if ($GLOBALS['set_autosuggest_options'] == 3) {
                $leading = '%';
                $trailing = '%';
            }
        } else {
            $leading = $post->leading;
            $trailing = $post->trailing;
        }

        $queryString = $post->queryString;


        $page = $post->page;
        $searchType = $post->searchType;
        $searchEleNo = $post->searchEleNo;

        if ($page == '') {
            $limitStart = 0;
        } else {
            $limitStart = \Application\Plugin\CommonPlugin::escapeLimit($page);
        }

        $keyword = $leading . $queryString . $trailing;
        if (strtolower($searchType) == 'patient') {
            $sql = "SELECT fname, mname, lname, pid, DOB FROM patient_data
                WHERE pid LIKE ?
                OR  CONCAT(fname, ' ', lname) LIKE ?
                OR  CONCAT(lname, ' ', fname) LIKE ?
                OR DATE_FORMAT(DOB,'%m-%d-%Y') LIKE ?
                OR DATE_FORMAT(DOB,'%d-%m-%Y') LIKE ?
                OR DATE_FORMAT(DOB,'%Y-%m-%d') LIKE ?
                ORDER BY fname ";
            $result = $this->zQuery($sql, array(
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword
            ));
            $rowCount = $result->count();
            $sql .= "LIMIT $limitStart, $limitEnd";
            $result = $this->zQuery($sql, array(
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,

            ));
        } elseif (strtolower($searchType) == 'emrdirect') {
            $sql = "SELECT fname, mname, lname,email_direct AS 'email',id FROM users
                WHERE (CONCAT(fname, ' ', lname) LIKE ?
                OR  CONCAT(lname, ' ', fname) LIKE ?
                OR email_direct LIKE ?)
                AND abook_type = 'emr_direct'
                AND active = 1
                ORDER BY fname ";
            $result = $this->zQuery($sql, array(
                $keyword,
                $keyword,
                $keyword,
            ));
            $rowCount = $result->count();
            $sql .= "LIMIT $limitStart, $limitEnd";
            $result = $this->zQuery($sql, array(
                $keyword,
                $keyword,
                $keyword,
            ));
        }

        $arr = array();
        if ($result) {
            foreach ($result as $row) {
                $arr[] = $row;
            }

            $arr['rowCount'] = $rowCount;
        }

        return $arr;
    }

    /**
     * Converts a given format setting (or string) to a PHP date format.
     *
     * @param mixed $format 0, 1, 2, or a custom format string.
     * @return string        PHP date format string.
     */
    public static function dateFormat($format = null)
    {
        $map = [
            '0' => 'Y-m-d', // e.g. "1920-01-01"
            1 => 'm/d/Y', // e.g. "01/01/1920"
            2 => 'd/m/Y', // e.g. "01/01/1920"
            'yyyy-mm-dd' => 'Y-m-d',
            'mm/dd/yyyy' => 'm/d/Y',
            'dd/mm/yyyy' => 'd/m/Y',
        ];
        if (isset($map[$format])) {
            return $map[$format];
        }

        return $format;
    }

    /*
    * Retrive the data format from GLOBALS
    *
    * @param    Date format set in GLOBALS
    * @return   Date format in datepicker
    **/
    public static function datePickerFormat($format = null)
    {
        if ($format == "0") {
            $date_format = 'yy-mm-dd';
        } elseif ($format == 1) {
            $date_format = 'mm/dd/yy';
        } elseif ($format == 2) {
            $date_format = 'dd/mm/yy';
        } else {
            $date_format = $format;
        }

        return $date_format;
    }

    /**
     * Converts an input date from one format to another.
     *
     * @param string $input_date    The date to convert.
     * @param mixed  $output_format The desired output format (as defined by dateFormat).
     * @param mixed  $input_format  The format of the input date (as defined by dateFormat).
     *                              If null, the method will attempt to detect the format.
     * @return string|false         The formatted date or false if conversion fails.
     */
    public static function fixDate($input_date, $output_format = null, $input_format = null)
    {
        if (!$input_date) {
            return false;
        }

        $input_date = preg_replace('/[TZ]/', ' ', $input_date);
        $outputFormat = self::dateFormat($output_format);

        if ($input_format) {
            $inputFormat = self::dateFormat($input_format);
        } else {
            if (preg_match('/^\d{8}$/', $input_date)) {
                $inputFormat = 'Ymd';
            } elseif (preg_match('/^\d{14}$/', $input_date)) {
                $inputFormat = 'YmdHis';
            } else {
                $inputFormat = null;
            }
        }
        if ($inputFormat) {
            $dateObj = DateTime::createFromFormat($inputFormat, $input_date);
        } else {
            try {
                $dateObj = new DateTime($input_date);
            } catch (Exception $e) {
                return false;
            }
        }

        if (!$dateObj) {
            return false;
        }

        return $dateObj->format($outputFormat);
    }

    /*
    * Using generate id function from OpenEMR sql.inc.php library file
    * @param  string  $seqname     table name containing sequence (default is adodbseq)
    * @param  integer $startID     id to start with for a new sequence (default is 1)
    * @return integer              returns the sequence integer
    */
    public function generateSequenceID()
    {
        return generate_id();
    }
}
