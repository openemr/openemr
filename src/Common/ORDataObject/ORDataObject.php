<?php

/**
 * class ORDataObject
 *
 */

namespace OpenEMR\Common\ORDataObject;

use OpenEMR\Common\Database\QueryUtils;

class ORDataObject
{
    protected $_prefix;
    protected $_table;
    public $_db; // Need to be public so can access from C_Document class

    public function __construct($table = null, $prefix = null)
    {
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

    public function persist()
    {
        $this_table = escape_table_name($this->_prefix . $this->_table);
        $db = get_db();
        $fields = $db->metaColumns($this_table);

        $this_rec = [];
        $pkeys = [];
        $pkey_id = '';
        foreach ($fields as $field => $objAdoField) {
            $func = "get_" . $field;
            if (is_callable(array($this,$func))) {
                $val = call_user_func(array($this,$func));
                if ($objAdoField->primary_key) {
                    // Potential issue if multiple fields included as primary key
                    $pkey_id = $field;
                    if (empty($val) && ($objAdoField->auto_increment)) {
                        // Skip the field from sql statement.
                        continue;
                    } else {
                        $val = generate_id();
                    }
                    $pkeys[] = $field;
                }
                if (!empty($val)) {
                    $this_rec[$field] = $val;
                }
            }
        }
        if (empty($this_rec)) {
            // WTF?
            return false;
        }

        $update = false;
        if (!empty($pkeys)) {
            // Find record with matching primary keys
            $sql = sprintf('select * FROM `%s` WHERE %s=?',
                $this_table,
                implode("=?, ", $pkeys)
            );
            $rs = $db->execute($sql, array_values(array_intersect_key($this_rec, array_flip($pkeys))));
            $update = ($rs->recordCount() > 0);
        }

        if ($update) {
            $sql = $db->getUpdateSql($rs, $this_rec);
            if (!$sql) {
                // Nothing to update
                return false;
            } else {
                sqlQuery($sql);
            }
        } else {
            $sql = $db->getInsertSql($this_table, $this_rec);
            // Capture inserted autoincremented value
            $pkey = sqlInsert($sql);
            call_user_func([$this,'set_'.$pkey_id], $pkey);
        }

        return true;
    }

    public function populate()
    {
        $sql = "SELECT * from " . escape_table_name($this->_prefix . $this->_table) . " WHERE id = ?";
        $results = sqlQuery($sql, [strval($this->id)]);
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
