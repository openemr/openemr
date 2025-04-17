<?php

/**
 * phone number class for smarty templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    duhlman
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) duhlman
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

define("TYPE_HOME", 1);
define("TYPE_WORK", 2);
define("TYPE_CELL", 3);
define("TYPE_EMERGENCY", 4);
define("TYPE_FAX", 5);


/**
 * class Address
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class PhoneNumber extends ORDataObject
{
    var $id;
    var $foreign_id;
    var $country_code;
    var $area_code;
    var $prefix;
    var $number;
    var $type;
    var $type_array = array("","Home", "Work", "Cell" , "Emergency" , "Fax");

    /**
     * Constructor sets all attributes to their default value
     */
    function __construct($id = "", $foreign_id = "")
    {
        $this->id = $id;
        $this->foreign_id = $foreign_id;
        $this->country_code = "+1";
        $this->prefix = "";
        $this->number = "";
        $this->type = TYPE_HOME;
        $this->_table = "phone_numbers";
        if ($id != "") {
            $this->populate();
        }
    }

    static function factory_phone_numbers($foreign_id = "")
    {
        $sqlArray = array();

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $phone_numbers = array();
        $p = new PhoneNumber();
        $sql = "SELECT id FROM " . escape_table_name($p->_table) . " WHERE foreign_id " . $foreign_id_sql . " ORDER BY type";
        //echo $sql . "<bR />";
        $results = sqlQ($sql, $sqlArray);
        //echo "sql: $sql";
        while ($row = sqlFetchArray($results)) {
            $phone_numbers[] = new PhoneNumber($row['id']);
        }

        return $phone_numbers;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function foreign_id($id)
    {
        $this->foreign_id = $id;
    }

    function get_foreign_id()
    {
        return $this->foreign_id;
    }

    function set_country_code($ccode)
    {
        $this->country_code = $ccode;
    }

    function get_country_code()
    {
        return $this->country_code;
    }
    function set_area_code($acode)
    {
        $this->area_code = $acode;
    }

    function get_area_code()
    {
        return $this->area_code;
    }

    function set_number($num)
    {
        $this->number = $num;
    }

    function get_number()
    {
        return $this->number;
    }


    function set_type($type)
    {
        $this->type = $type;
    }

    function get_type()
    {
        return $this->type;
    }

    function set_prefix($prefix)
    {
        $this->prefix = $prefix;
    }

    function get_prefix()
    {
        return $this->prefix;
    }

    function get_phone_display()
    {
        if (is_numeric($this->area_code) && is_numeric($this->prefix) && is_numeric($this->number)) {
            // return  "(" . $this->area_code . ") " . $this->prefix . "-" . $this->number;
            return  $this->area_code . "-" . $this->prefix . "-" . $this->number;
        }

        return "";
    }

    function set_phone($num)
    {
        if (strlen($num) == 10 && is_numeric($num)) {
            $this->area_code = substr($num, 0, 3);
            $this->prefix = substr($num, 3, 3);
            $this->number = substr($num, 6, 4);
        } elseif (strlen($num) == 12) {
            $nums = explode("-", $num);
            if (count($nums) == 3) {
                $this->area_code = $nums[0];
                $this->prefix = $nums[1];
                $this->number = $nums[2];
            }
        } elseif (strlen($num) == 14 && substr($num, 0, 1) == "(") {
            $nums[0] = substr($num, 1, 3);
            $nums[1] = substr($num, 6, 3);
            $nums[2] = substr($num, 10, 4);

            foreach ($nums as $n) {
                if (!is_numeric($n)) {
                    return false;
                }
            }

            if (count($nums) == 3) {
                $this->area_code = $nums[0];
                $this->prefix = $nums[1];
                $this->number = $nums[2];
            }
        } else {
            $this->area_code = '';
            $this->prefix = '';
            $this->number = '';
        }
    }

    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . $this->country_code . " (" . $this->area_code . ") " . $this->prefix . "-" . $this->number . " " . $this->type_array[$this->type];
        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    function persist($fid = "")
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        parent::persist();
    }
}
