<?php

/**
 * Standard Services Base class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Exception\InvalidValueException;

class BaseService
{
    /**
     * Passed in data should be vetted and fully qualified from calling service class
     * Expect to see some search helpers here as well.
     */
    private $table;
    private $fields;
    private $autoIncrements;

    /**
     * Default constructor.
     */
    public function __construct($table)
    {
        $this->table = $table;
        $this->fields = sqlListFields($table);
        $this->autoIncrements = self::getAutoIncrements($table);
    }

    /**
     * queryFields
     * Build SQL Query for Selecting Fields
     *
     * @param array $map
     * @return array
     */
    public function queryFields($map = null, $data = null)
    {
        if ($data == null || $data == "*" || $data == "all") {
            $value = "*";
        } else {
            $value = implode(", ", $data);
        }
        $sql = "SELECT $value from $this->table";
        return $this->selectHelper($sql, $map);
    }

    /**
     * buildInsertColumns
     * Build an insert set and bindings
     *
     * @param array $passed_in
     * @return array
     */
    protected function buildInsertColumns($passed_in = array())
    {
        $keyset = '';
        $bind = array();
        $result = array();

        foreach ($passed_in as $key => $value) {
            // Ensure auto's not passed in.
            if (in_array($key, array_column($this->autoIncrements, 'Field'))) {
                continue;
            }
            // include existing columns
            if (!in_array($key, $this->fields)) {
                continue;
            }
            if ($value == 'YYYY-MM-DD' || $value == 'MM/DD/YYYY') {
                $value = "";
            }
            if ($value === null || $value === false) {
                $value = "";
            }
            $keyset .= ($keyset) ? ", `$key` = ? " : "`$key` = ? ";
            $bind[] = ($value === null || $value === false) ? '' : $value;
        }

        $result['set'] = $keyset;
        $result['bind'] = $bind;

        return $result;
    }

    /**
     * buildUpdateColumns
     * Build an update set and bindings
     *
     * @param array $passed_in
     * @return array
     */
    protected function buildUpdateColumns($passed_in = array())
    {
        $keyset = '';
        $bind = array();
        $result = array();

        foreach ($passed_in as $key => $value) {
            if (in_array($key, array_column($this->autoIncrements, 'Field'))) {
                continue;
            }
            // exclude uuid columns
            if ($key == 'uuid') {
                continue;
            }
            // exclude pid columns
            if ($key == 'pid') {
                continue;
            }
            if (!in_array($key, $this->fields)) {
                // placeholder. could be for where clauses
                $bind[] = ($value == 'NULL') ? "" : $value;
                continue;
            }
            if ($value == 'YYYY-MM-DD' || $value == 'MM/DD/YYYY') {
                $value = "";
            }
            if ($value === null || $value === false) {
                // in case unwanted values passed in.
                continue;
            }
            $keyset .= ($keyset) ? ", `$key` = ? " : "`$key` = ? ";
            $bind[] = ($value == 'NULL') ? "" : $value;
        }

        $result['set'] = $keyset;
        $result['bind'] = $bind;

        return $result;
    }

    /**
     * Verify pid exist
     * @param $pid
     * @return mixed
     */
    public function verifyPid($pid)
    {
        $rtn = sqlQuery(
            "Select pid From patient_data Where pid = ?",
            array($pid)
        )['pid'];
        if (!$rtn) {
            $this->throwException('Unable to find the user with id ' . $pid, 'error');
        }
        return true;
    }

    /**
     * @param $table
     * @return array
     */
    private static function getAutoIncrements($table)
    {
        $results = array();
        $rtn = sqlStatementNoLog(
            "SHOW COLUMNS FROM $table Where extra Like ?",
            array('%auto_increment%')
        );
        while ($row = sqlFetchArray($rtn)) {
            array_push($results, $row);
        }

        return $results;
    }

    /**
     * Shared getter for SQL selects.
     * Shared from original OpenEMR\Common\Utils\QueryUtils
     *
     * @param $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param $map                  - Query information (where clause(s), join clause(s), order, data, etc).
     * @return array of associative arrays | one associative array.
     */
    public static function selectHelper($sqlUpToFromStatement, $map)
    {
        $where = isset($map["where"]) ? $map["where"] : null;
        $data = isset($map["data"]) ? $map["data"] : null;
        $join = isset($map["join"]) ? $map["join"] : null;
        $order = isset($map["order"]) ? $map["order"] : null;
        $limit = isset($map["limit"]) ? $map["limit"] : null;

        $sql = $sqlUpToFromStatement;

        $sql .= !empty($join) ? " " . $join : "";
        $sql .= !empty($where) ? " " . $where : "";
        $sql .= !empty($order) ? " " . $order : "";
        $sql .= !empty($limit) ? " LIMIT " . $limit : "";

        if (!empty($data)) {
            if (empty($limit) || $limit > 1) {
                $multipleResults = sqlStatement($sql, $data);
                $results = array();

                while ($row = sqlFetchArray($multipleResults)) {
                    array_push($results, $row);
                }

                return $results;
            }

            return sqlQuery($sql, $data);
        }

        if (empty($limit) || $limit > 1) {
            $multipleResults = sqlStatement($sql);
            $results = array();

            while ($row = sqlFetchArray($multipleResults)) {
                array_push($results, $row);
            }

            return $results;
        }

        return sqlQuery($sql);
    }

    /**
     * Build and Throw Invalid Value Exception
     *
     * @param $message              - The error message which will be displayed
     * @param $type                 - Type of Exception
     * @throws InvalidValueException
     */
    public static function throwException($message, $type = "Error")
    {
        throw new InvalidValueException($message, $type);
    }

    // Taken from -> https://stackoverflow.com/a/24401462
    /**
     * Validate Date and Time
     *
     * @param $dateString              - The Date string which is to be verified
     * @return bool
     */
    public static function isValidDate($dateString)
    {
        return (bool) strtotime($dateString);
    }
}
