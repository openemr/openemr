<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
define("FM_TYPE_UNKNOWN", 0);
define("FM_TYPE_DECIMAL", 1);
define("FM_TYPE_INT", 2);
define("FM_TYPE_SMALLINT", 3);
define("FM_TYPE_TINYINT", 4);
define("FM_TYPE_MEDIUMINT", 15);
define("FM_TYPE_BIGINT", 16);
define("FM_TYPE_FLOAT", 20);
define("FM_TYPE_VARCHAR", 5);
define("FM_TYPE_BLOB", 6);
define("FM_TYPE_DATE", 7);
define("FM_TYPE_DATETIME", 8);
define("FM_TYPE_TEXT", 9);
define("FM_TYPE_SMALLTEXT", 10);
define("FM_TYPE_MEDIUMTEXT", 11);
define("FM_TYPE_CHAR", 12);
define("FM_TYPE_LONGBLOB", 13);
define("FM_TYPE_LONGTEXT", 14);

define("FM_TYPE_TIMESTAMP", 17);
define("FM_TYPE_ENUM", 18);
define("FM_TYPE_TINYTEXT", 19);

define("FM_TYPE_YEAR", 21);
define("FM_TYPE_TIME", 22);

define("FM_CALCULATION", 99); // not to be used during save

/**
 * FieldMap is a base object for mapping a Phreezable object to a database table
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class FieldMap
{
    /**
     * Given a MySQL column type, return the Phreeze constant name
     *
     * @param string $type
     */
    static function GetConstantFromType($type)
    {
        $const = 'FM_TYPE_' . strtoupper($type);
        return (defined($const)) ? $const : 'FM_TYPE_UNKNOWN';
    }

    /**
     * Initializes the FieldMap
     *
     * @param string $PropertyName Model property name
     * @param string $TableName DB table name
     * @param string $ColumnName DB column name
     * @param bool $IsPrimaryKey True if column is a primary key (optional default = false)
     * @param int $FieldType Field type FM_TYPE_VARCHAR | FM_TYPE_INT | etc... (optional default = FM_TYPE_UNKNOWN)
     * @param mixed $FieldSize Field size, 0 for unlimited. for enums, an array of acceptable values (default = 0)
     * @param mixed $DefaultValue Default value (optional default = null)
     * @param bool $IsAutoInsert True if column is auto insert column (optional default = null)
     */
    public function __construct(public $PropertyName, public $TableName, public $ColumnName, public $IsPrimaryKey = false, public $FieldType = FM_TYPE_UNKNOWN, public $FieldSize = 0, public $DefaultValue = null, public $IsAutoInsert = null)
    {
    }

    /**
     * Return true if this column is an enum type
     *
     * @return boolean
     */
    function IsEnum()
    {
        return $this->FieldType == FM_TYPE_ENUM;
    }

    /**
     * Return the enum values if this column type is an enum
     *
     * @return array
     */
    function GetEnumValues()
    {
        return $this->IsEnum() ? $this->FieldSize :  [];
    }

    /**
     * Return true if this column is a numeric type
     */
    public function IsNumeric()
    {
        return (in_array($this->FieldType, [FM_TYPE_DECIMAL, FM_TYPE_INT, FM_TYPE_SMALLINT, FM_TYPE_TINYINT, FM_TYPE_MEDIUMINT, FM_TYPE_BIGINT, FM_TYPE_FLOAT]));
    }
}
