<?php

/**
 * class ORDataObject
 *
 */

namespace OpenEMR\Common\ORDataObject;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class ORDataObject
{
    /**
     * @var array<string, list<string>>
     * Cache of raw enum values keyed by "table.field_name".
     * Stores cleaned values before blank/flip processing.
     */
    private static array $enumCache = [];

    // TODO: @adunsulag at some point we need to set this default to false
    // currently most objects assume we need to save when we call persist
    private $_isObjectModified = true;
    private $_throwExceptionOnError = false;
    protected $_prefix;
    protected $_table;
    protected $_db;

    public function __construct($table = null, $prefix = null)
    {
        // default is to just die in SQL
        $this->setThrowExceptionOnError(false);
        // TODO: with testing we could probably remove the isset... but we will leave this here until there are more
        // unit tests saying this doesn't break subclass constructors
        if (isset($table)) {
            $this->_table = $table;
        }
        if (isset($prefix)) {
            $this->_prefix = $prefix;
        }

        $this->_db = OEGlobalsBag::getInstance()->get('adodb')['db'];
    }

    public function markObjectModified()
    {
        $this->_isObjectModified = true;
    }

    public function isObjectModified()
    {
        return $this->_isObjectModified;
    }

    protected function setIsObjectModified($isModified)
    {
        $this->_isObjectModified = $isModified;
    }

    public function persist()
    {
        if (!$this->isObjectModified()) {
            return true;
        }

        $sql = "INSERT INTO " . $this->_prefix . $this->_table . " SET ";
        //echo "<br /><br />";
        $fields = QueryUtils::listTableFields($this->_table);
        $db = get_db();
        $pkeys = $db->MetaPrimaryKeys($this->_table);
        $setClause = "";

        foreach ($fields as $field) {
            $func = "get_" . $field;
            //echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br />";
            if (is_callable([$this, $func])) {
                $val = $this->$func();

                if (in_array($field, $pkeys) && empty($val)) {
                    $last_id = QueryUtils::generateId();
                    $this->{"set_" . $field}($last_id);
                    $val = $last_id;
                }
                // Normalize before deciding to persist
                if ($val instanceof \DateTime) {
                    // we are storing up to the second in precision, if we need to store fractional seconds
                    // that will be more complicated as the mysql datetime needs to specify the decimal seconds
                    // that can be stored
                    $val = $val->format("Y-m-d H:i:s");
                } elseif (is_bool($val)) {
                    // Ensure boolean false doesn't get treated as empty
                    $val = $val ? 1 : 0;
                }

                // Persist if not NULL and not an empty string (''); allow 0/'0'/false(->0)
                if ($val !== null && !(is_string($val) && $val === '')) {
                    //echo "s: $field to: $val <br />";

                    //modified 01-2010 by BGM to centralize to formdata.inc.php
                    // have place several debug statements to allow standardized testing over next several months
                    $setClause .= " `" . $field . "` = '" . add_escape_custom(strval($val)) . "',";
                    //DEBUG LINE - error_log("ORDataObject persist after escape: ".add_escape_custom(strval($val)), 0);
                    //DEBUG LINE - error_log("ORDataObject persist after escape and then stripslashes test: ".stripslashes(add_escape_custom(strval($val))), 0);
                    //DEBUG LINE - error_log("ORDataObject original before the escape and then stripslashes test: ".strval($val), 0);
                }
            }
        }

        $setClause = rtrim($setClause, ',');

        $sql .= $setClause;
        $sql .= " ON DUPLICATE KEY UPDATE " . $setClause;

        if ($this->_throwExceptionOnError) {
            QueryUtils::sqlStatementThrowException($sql, []);
        } else {
            sqlStatement($sql);
        }
        return true;
    }

    public function populate()
    {
        $sql = "SELECT * from " . escape_table_name($this->_prefix . $this->_table) . " WHERE id = ?";
        $results = sqlQuery($sql, [strval($this->get_id())]);
        $this->populate_array($results);
    }

    public function populate_array($results)
    {
        if (is_array($results)) {
            foreach ($results as $field_name => $field) {
                $func = "set_" . $field_name;
                //echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br />";
                if (is_callable([$this, $func])) {
                    if (!empty($field)) {
                        //echo "s: $field_name to: $field <br />";
                        $this->$func($field);
                    }
                }
            }
        }
    }

    public function get_data_for_save()
    {
        $fields = QueryUtils::listTableFields($this->_table);
        foreach ($fields as $field) {
            $func = "get_" . $field;
            if (is_callable([$this, $func])) {
                $val = $this->$func();
                $values[$field] = $val;
            }
        }
        return $values;
    }

    public function shouldthrowExceptionOnError()
    {
        return $this->_throwExceptionOnError;
    }

    public function setThrowExceptionOnError($throwException)
    {
        $this->_throwExceptionOnError = $throwException;
    }

    /**
     * Helper function that loads enumerations from the database as an array.
     * Results are cached per table.field_name combination.
     *
     * @param string $field_name name of the enumeration in this object's table
     * @param bool   $blank      include an empty element at position 0 (default true)
     * @return array<string, int> values as name-to-index pairs
     */
    protected function _load_enum(string $field_name, bool $blank = true): array
    {
        if ($this->_table === null || $this->_table === '') {
            return [];
        }

        $cacheKey = $this->_table . '.' . $field_name;

        if (!array_key_exists($cacheKey, self::$enumCache)) {
            $cols = $this->_db->MetaColumns($this->_table);
            $rawEnums = [];

            if (is_array($cols) || ($cols && !$cols->EOF)) {
                foreach ($cols as $col) {
                    if ($col->name === $field_name && $col->type === 'enum') {
                        foreach ($col->enums as $enumValue) {
                            $rawEnums[] = str_replace("'", '', $enumValue);
                        }
                        break;
                    }
                }
            }

            self::$enumCache[$cacheKey] = $rawEnums;
        }

        $rawEnums = self::$enumCache[$cacheKey];

        if ($rawEnums === []) {
            return [];
        }

        $enum = $rawEnums;
        array_unshift($enum, ' ');

        if (!$blank) {
            unset($enum[0]);
        }

        return array_flip($enum);
    }

    public function _utility_array($obj_ar, $reverse = false, $blank = true, $name_func = "get_name", $value_func = "get_id")
    {
        $ar = [];
        if ($blank) {
            $ar[0] = " ";
        }

        if (!is_array($obj_ar)) {
            return $ar;
        }

        foreach ($obj_ar as $obj) {
            $ar[$obj->$value_func()] = $obj->$name_func();
        }

        if ($reverse) {
            $ar = array_flip($ar);
        }

        return $ar;
    }
} // end of ORDataObject
