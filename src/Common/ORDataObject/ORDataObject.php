<?php

/**
 * class ORDataObject
 *
 */

namespace OpenEMR\Common\ORDataObject;

use OpenEMR\Common\Database\QueryUtils;

class ORDataObject
{
    // TODO: @adunsulag at some point we need to set this default to false
    // currently most objects assume we need to save when we call persist
    private $_isObjectModified = true;
    private $_throwExceptionOnError = false;
    protected $_prefix;
    protected $_table;
    public $_db; // Need to be public so can access from C_Document class

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

        $this->_db = $GLOBALS['adodb']['db'];
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

        // NOTE: REPLACE INTO does a DELETE and then INSERT, if you have foreign keys setup the delete call will trigger
        $sql = "REPLACE INTO " . $this->_prefix . $this->_table . " SET ";
        //echo "<br /><br />";
        $fields = QueryUtils::listTableFields($this->_table);
        $db = get_db();
        $pkeys = $db->MetaPrimaryKeys($this->_table);

        foreach ($fields as $field) {
            $func = "get_" . $field;
            //echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br />";
            if (is_callable(array($this,$func))) {
                $val = call_user_func(array($this,$func));

                if (in_array($field, $pkeys)  && empty($val)) {
                    $last_id = generate_id();
                    call_user_func(array(&$this,"set_" . $field), $last_id);
                    $val = $last_id;
                }

                // TODO: This fails to save any numeric column with a value of 0, such as a status with 0/1 being
                // false/true, we should change this but we will need to heavily test it.
                if (!empty($val)) {
                    if ($val instanceof \DateTime) {
                        // we are storing up to the second in precision, if we need to store fractional seconds
                        // that will be more complicated as the mysql datetime needs to specify the decimal seconds
                        // that can be stored
                        $val = $val->format("Y-m-d H:i:s");
                    }
                    //echo "s: $field to: $val <br />";

                                        //modified 01-2010 by BGM to centralize to formdata.inc.php
                            // have place several debug statements to allow standardized testing over next several months
                    $sql .= " `" . $field . "` = '" . add_escape_custom(strval($val)) . "',";
                        //DEBUG LINE - error_log("ORDataObject persist after escape: ".add_escape_custom(strval($val)), 0);
                        //DEBUG LINE - error_log("ORDataObject persist after escape and then stripslashes test: ".stripslashes(add_escape_custom(strval($val))), 0);
                        //DEBUG LINE - error_log("ORDataObject original before the escape and then stripslashes test: ".strval($val), 0);
                }
            }
        }

        if (strrpos($sql, ",") == (strlen($sql) - 1)) {
                $sql = substr($sql, 0, (strlen($sql) - 1));
        }

        if ($this->_throwExceptionOnError) {
            QueryUtils::sqlStatementThrowException($sql, []);
        } else {
            sqlQuery($sql);
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
                if (is_callable(array($this,$func))) {
                    if (!empty($field)) {
                        //echo "s: $field_name to: $field <br />";
                        call_user_func(array(&$this,$func), $field);
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
                $val = call_user_func([$this, $func]);
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
     * Helper function that loads enumerations from the data as an array, this is also efficient
     * because it uses psuedo-class variables so that it doesnt have to do database work for each instance
     *
     * @param string $field_name name of the enumeration in this objects table
     * @param boolean $blank optional value to include a empty element at position 0, default is true
     * @return array array of values as name to index pairs found in the db enumeration of this field
     */
    protected function _load_enum($field_name, $blank = true)
    {
        if (
            !empty($GLOBALS['static']['enums'][$this->_table][$field_name])
            && is_array($GLOBALS['static']['enums'][$this->_table][$field_name])
            && !empty($this->_table)
        ) {
            return $GLOBALS['static']['enums'][$this->_table][$field_name];
        } else {
            $cols = $this->_db->MetaColumns($this->_table);
            if ((is_array($cols) && !empty($cols)) || ($cols && !$cols->EOF)) {
                //why is there a foreach here? at some point later there will be a scheme to autoload all enums
                //for an object rather than 1x1 manually as it is now
                foreach ($cols as $col) {
                    if ($col->name == $field_name && $col->type == "enum") {
                        for ($idx = 0; $idx < count($col->enums); $idx++) {
                            $col->enums[$idx] = str_replace("'", "", $col->enums[$idx]);
                        }

                        $enum = $col->enums;
                        //for future use
                        //$enum[$col->name] = $enum_types[1];
                    }
                }

                array_unshift($enum, " ");

               //keep indexing consistent whether or not a blank is present
                if (!$blank) {
                    unset($enum[0]);
                }

                $enum = array_flip($enum);
                $GLOBALS['static']['enums'][$this->_table][$field_name] = $enum;
            }

            return $enum;
        }
    }

    public function _utility_array($obj_ar, $reverse = false, $blank = true, $name_func = "get_name", $value_func = "get_id")
    {
        $ar = array();
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
