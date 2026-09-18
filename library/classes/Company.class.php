<?php

/************************************************************************
            address.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\ORDataObject\ORDataObject;

/**
 * class Address
 *
 */
class Company extends ORDataObject
{
    public $name;
    public $line1;
    public $line2;
    public $city;
    public $state;
    public $zip;
    public $plus_four;
    public $country;

    /**
     * Constructor sets all Company attributes to their default value
     */
    public function __construct(public $id = "", public $foreign_id = "")
    {
        $this->name = "";
        $this->_table = "companies";
        $this->line1 = "";
        $this->line2 = "";
        $this->city = "";
        $this->state = "";
        $this->zip = "";
        $this->plus_four = "";
        $this->country = "USA";
        if ($this->id != "") {
            $this->populate();
        }
    }
    public function factory_company($foreign_id = "")
    {
        $sqlArray = [];

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $a = new Address();
        $sql = "SELECT id FROM  " . escape_table_name($a->_table) . " WHERE foreign_id " . $foreign_id_sql;
        $row = QueryUtils::querySingleRow($sql, $sqlArray);
        if (!empty($row)) {
            $a = new Address($row['id']);
        }

        return $a;
    }

    public function toString($html = false)
    {
        $string = "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . $this->line1 . "\n"
        . $this->line2 . "\n"
        . $this->city . ", " . strtoupper((string) $this->state) . " " . $this->zip . "-" . $this->plus_four . "\n"
        . $this->country . "\n";
        return $html ? nl2br($string) : $string;
    }

    public function set_id($id)
    {
        $this->id = $id;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function set_name($name)
    {
        $this->name = $name;
    }
    public function get_name()
    {
        return $this->name;
    }
    public function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
    }
    public function get_foreign_id()
    {
        return $this->foreign_id;
    }
    public function set_line1($line1)
    {
        $this->line1 = $line1;
    }
    public function get_line1()
    {
        return $this->line1;
    }
    public function set_line2($line2)
    {
        $this->line2 = $line2;
    }
    public function get_line2()
    {
        return $this->line2;
    }
    public function set_city($city)
    {
        $this->city = $city;
    }
    public function get_city()
    {
        return $this->city;
    }
    public function set_state($state)
    {
        $this->state = $state;
    }
    public function get_state()
    {
        return $this->state;
    }
    public function set_zip($zip)
    {
        $this->zip = $zip;
    }
    public function get_zip()
    {
        return $this->zip;
    }
    public function set_plus_four($plus_four)
    {
        $this->plus_four = $plus_four;
    }
    public function get_plus_four()
    {
        return $this->plus_four;
    }
    public function set_country($country)
    {
        $this->country = $country;
    }
    public function get_country()
    {
        return $this->country;
    }
    public function persist($fid = ""): mixed
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        return parent::persist();
    }
} // end of Company
