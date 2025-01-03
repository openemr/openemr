<?php

/************************************************************************
            pharmacy.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

define("TRANSMIT_PRINT", 1);
define("TRANSMIT_EMAIL", 2);
define("TRANSMIT_FAX", 3);
define("TRANSMIT_ERX", 4);

/**
 * class Pharmacy
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ORDataObject\Address;

class Pharmacy extends ORDataObject
{
    var $id;
    var $name;
    var $phone_numbers;
    var $address;
    var $transmit_method;
    var $email;
    var $transmit_method_array; //set in constructor
    var $pageno;
    var $state;
    var $npi;
    var $ncpdp;

    /**
     * Constructor sets all Prescription attributes to their default value
     */
    function __construct($id = "", $prefix = "")
    {
        $this->id = $id;
        $this->state = $this->getState();
        $this->name = "";
        $this->email = "";
        $this->transmit_method = 1;
        $this->transmit_method_array = array(xl("None Selected"), xl("Print"), xl("Email"), xl("Fax"), xl("Transmit"), xl("eRx"));
        $this->_table = "pharmacies";
        $phone  = new PhoneNumber();
        $phone->set_type(TYPE_WORK);
        $this->phone_numbers = array($phone);
        $this->address = new Address();
        if ($id != "") {
            $this->populate();
        }
    }

    function set_id($id = "")
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }
    function set_form_id($id = "")
    {
        if (!empty($id)) {
            $this->populate($id);
        }
    }
    function set_fax_id($id)
    {
        $this->id = $id;
    }
    function set_address($aobj)
    {
        $this->address = $aobj;
    }
    function get_address()
    {
        return $this->address;
    }
    function set_address_line1($line)
    {
        $this->address->set_line1($line);
    }
    function set_address_line2($line)
    {
        $this->address->set_line2($line);
    }
    function set_city($city)
    {
        $this->address->set_city($city);
    }
    function set_state($state)
    {
        $this->address->set_state($state);
    }
    function set_zip($zip)
    {
        $this->address->set_zip($zip);
    }

    function set_name($name)
    {
        $this->name = $name;
    }
    function get_name()
    {
        return $this->name;
    }
    function set_npi($npi)
    {
        $this->npi = $npi;
    }
    function get_npi()
    {
        return $this->npi;
    }
    function set_ncpdp($ncpdp)
    {
        $this->ncpdp = $ncpdp;
    }
    function get_ncpdp()
    {
        return $this->ncpdp;
    }
    function set_email($email)
    {
        $this->email = $email;
    }
    function get_email()
    {
        return $this->email;
    }
    function set_transmit_method($tm)
    {
        $this->transmit_method = $tm;
    }
    function get_transmit_method()
    {
        if ($this->transmit_method == TRANSMIT_EMAIL && empty($this->email)) {
            return TRANSMIT_PRINT;
        }

        return $this->transmit_method;
    }
    function get_transmit_method_display()
    {
        return $this->transmit_method_array[$this->transmit_method];
    }
    function get_phone()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type == TYPE_WORK) {
                return $phone->get_phone_display();
            }
        }

        return "";
    }
    function set_number($num, $type)
    {
        $found = false;
        for ($i = 0; $i < count($this->phone_numbers); $i++) {
            if ($this->phone_numbers[$i]->type == $type) {
                $found = true;
                $this->phone_numbers[$i]->set_phone($num);
            }
        }

        if ($found == false) {
            $p = new PhoneNumber("", $this->id);
            $p->set_type($type);
            $p->set_phone($num);
            $this->phone_numbers[] = $p;
            //print_r($this->phone_numbers);
            //echo "num is now:" . $p->get_phone_display()  . "<br />";
        }
    }

    function set_phone($phone)
    {
        $this->set_number($phone, TYPE_WORK);
    }
    function set_fax($fax)
    {
        $this->set_number($fax, TYPE_FAX);
    }

    function get_fax()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type == TYPE_FAX) {
                return $phone->get_phone_display();
            }
        }

        return "";
    }
    function populate()
    {
        parent::populate();
        $this->address = Address::factory_address($this->id);
        $this->phone_numbers = PhoneNumber::factory_phone_numbers($this->id);
    }

    function persist()
    {
        parent::persist();
        $this->address->persist($this->id);
        foreach ($this->phone_numbers as $phone) {
            $phone->persist($this->id);
        }
    }

    function utility_pharmacy_array()
    {
        $pharmacy_array = array();
        $sql = "SELECT p.id, p.name, a.city, a.state " .
            "FROM " . escape_table_name($this->_table) . " AS p INNER JOIN addresses AS a ON  p.id = a.foreign_id";
        $res = sqlQ($sql);
        while ($row = sqlFetchArray($res)) {
                $d_string = $row['city'];
            if (!empty($row['city']) && $row['state']) {
                $d_string .= ", ";
            }

                $d_string .=  $row['state'];
                $pharmacy_array[strval($row['id'])] = $row['name'] . " " . $d_string;
        }

        return ($pharmacy_array);
    }

    function pharmacies_factory($city = "", $sort = "ORDER BY name")
    {
        if (empty($city)) {
             $city = "";
        } else {
            $city = " WHERE city = '" . add_escape_custom($foreign_id) . "'";
        }

        $p = new Pharmacy();
        $pharmacies = array();
        $sql = "SELECT p.id, a.city " .
            "FROM " . escape_table_name($p->_table) . " AS p " .
            "INNER JOIN addresses AS a ON p.id = a.foreign_id " . $city . " ";
        if (!empty($GLOBALS['weno_rx_enable'])) {
            $sql .= "WHERE state = '" . add_escape_custom($this->state) . "' ";
        }
        $sql .= add_escape_custom($sort);

        //echo $sql . "<bR />";
        $results = sqlQ($sql);
        //echo "sql: $sql";
        //print_r($results);
        while ($row = sqlFetchArray($results)) {
                $pharmacies[] = new Pharmacy($row['id']);
        }

        return $pharmacies;
    }

    function getState()
    {
        $sql = "SELECT state FROM facility";
        $res = sqlQuery($sql);
        return $res['state'];
    }

    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "Name: " . $this->name . "\n"
        . "Phone: " . $this->phone_numbers[0]->toString($html) . "\n"
        . "Email:" . $this->email . "\n"
        . "Address: " . $this->address->toString($html) . "\n"
        . "Method: " . $this->transmit_method_array[$this->transmit_method];

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    function totalPages()
    {
        $sql = "select count(*) AS numberof from " . escape_table_name($this->_table);
        $count = sqlQuery($sql);
        return $count['numberof'];
    }

    function getPageno()
    {
        return $this->pageno = 1;
    }
}
