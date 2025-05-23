<?php

/************************************************************************
            address.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ORDataObject\Address;

/**
 * class Address
 *
 */
class Company extends ORDataObject
{
    var $id;
    var $name;
    var $foreign_id;
    var $line1;
    var $line2;
    var $city;
    var $state;
    var $zip;
    var $plus_four;
    var $country;

    /**
     * Constructor sets all Company attributes to their default value
     */
    function __construct($id = "", $foreign_id = "")
    {
        $this->id = $id;
        $this->name = "";
        $this->foreign_id = $foreign_id;
        $this->_table = "companies";
        $this->line1 = "";
        $this->line2 = "";
        $this->city = "";
        $this->state = "";
        $this->zip = "";
        $this->plus_four = "";
        $this->country = "USA";
        if ($id != "") {
            $this->populate();
        }
    }
    function factory_company($foreign_id = "")
    {
        $sqlArray = array();

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $a = new Address();
        $sql = "SELECT id FROM  " . escape_table_name($a->_table) . " WHERE foreign_id " . $foreign_id_sql;
        //echo $sql . "<bR />";
        $results = sqlQ($sql, $sqlArray);
        //echo "sql: $sql";
        $row = sqlFetchArray($results);
        if (!empty($row)) {
            $a = new Address($row['id']);
        }

        return $a;
    }

    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . $this->line1 . "\n"
        . $this->line2 . "\n"
        . $this->city . ", " . strtoupper($this->state) . " " . $this->zip . "-" . $this->plus_four . "\n"
        . $this->country . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    function set_id($id)
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }
    function set_name($name)
    {
        $this->name = $name;
    }
    function get_name()
    {
        return $this->name;
    }
    function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
    }
    function get_foreign_id()
    {
        return $this->foreign_id;
    }
    function set_line1($line1)
    {
        $this->line1 = $line1;
    }
    function get_line1()
    {
        return $this->line1;
    }
    function set_line2($line2)
    {
        $this->line2 = $line2;
    }
    function get_line2()
    {
        return $this->line2;
    }
    function set_city($city)
    {
        $this->city = $city;
    }
    function get_city()
    {
        return $this->city;
    }
    function set_state($state)
    {
        $this->state = $state;
    }
    function get_state()
    {
        return $this->state;
    }
    function set_zip($zip)
    {
        $this->zip = $zip;
    }
    function get_zip()
    {
        return $this->zip;
    }
    function set_plus_four($plus_four)
    {
        $this->plus_four = $plus_four;
    }
    function get_plus_four()
    {
        return $this->plus_four;
    }
    function set_country($country)
    {
        $this->country = $country;
    }
    function get_country()
    {
        return $this->country;
    }
    function persist($fid = "")
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        parent::persist();
    }
} // end of Company
