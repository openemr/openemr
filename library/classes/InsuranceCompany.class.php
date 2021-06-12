<?php

/**
 * insurance company class for smarty templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    duhlman
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) duhlman
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Services\InsuranceCompanyService;

/**
 * class Insurance Company
 *
 */

class InsuranceCompany extends ORDataObject
{
    var $id;
    var $name;
    var $phone_numbers;
    var $attn;
    var $cms_id;
    var $alt_cms_id;
    var $eligibility_id;
    var $x12_default_partner_id;
    var $x12_default_eligibility_id;
    var $inactive;
    /*
    *   OpenEMR can use this value to determine special formatting for the specified type of payer.
    *   @var int Holds constant for type of payer
    */
    var $ins_type_code;

    /*
    *   Array used to populate select dropdowns or other form elements
    */
    var $ins_type_code_array;

    /*
    *   Array used with electronic claim submissions and
    *   corresponds with $ins_type_code_array
    */
    var $ins_claim_type_array;

    var $address;

    /**
     * Constructor sets all Insurance Company attributes to their default value
     */
    function __construct($id = "", $prefix = "")
    {
        $this->id = $id;
        $this->name = "";
        $this->_table = "insurance_companies";
        $phone = new PhoneNumber();
        $phone->set_type(TYPE_WORK);
        $fax = new PhoneNumber();
        $fax->set_type(TYPE_FAX);
        $this->address = new Address();
        $this->phone_numbers = array($phone, $fax);
        $this->ins_type_code_array = (new InsuranceCompanyService())->getInsuranceTypes();
        if ($id != "") {
            $this->populate();
        }

        $this->X12Partner = new X12Partner();
    }

    function set_id($id = "")
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }

    // special function that the html forms use to prepopulate which allows for partial edits and wizard functionality
    function set_form_id($id = "")
    {
        if (!empty($id)) {
            $this->populate($id);
        }
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
    function set_inactive($inactive)
    {
        $this->inactive = $inactive;
    }
    function get_inactive()
    {
        return $this->inactive;
    }
    function set_name($name)
    {
        $this->name = $name;
    }
    function get_name()
    {
        return $this->name;
    }
    function set_attn($attn)
    {
        $this->attn = $attn;
    }
    function get_attn()
    {
        return $this->attn;
    }
    function set_cms_id($id)
    {
        $this->cms_id = $id;
    }
    function get_cms_id()
    {
        return $this->cms_id;
    }
    function set_alt_cms_id($id)
    {
        $this->alt_cms_id = $id;
    }
    function get_alt_cms_id()
    {
        return $this->alt_cms_id;
    }
    function set_eligibility_id($id)
    {
        $this->eligibility_id = $id;
    }
    function get_eligibility_id()
    {
        return $this->eligibility_id;
    }
    function set_ins_type_code($type)
    {
        $this->ins_type_code = $type;
    }
    function get_ins_type_code()
    {
        return $this->ins_type_code;
    }
    function get_ins_type_code_display()
    {
        return $this->ins_type_code_array[$this->ins_type_code];
    }
    function get_ins_claim_type()
    {
        return $this->ins_claim_type_array[$this->ins_type_code];
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

    function set_x12_default_partner_id($id)
    {
        $this->x12_receiver_id = $id;
    }

    function get_x12_default_partner_id()
    {
        return $this->x12_receiver_id;
    }

    function get_x12_default_partner_name()
    {
        $xa = $this->_utility_array($this->X12Partner->x12_partner_factory());
        return ($xa[$this->get_x12_default_partner_id()] ?? null);
    }

    function set_x12_default_eligibility_id($id)
    {
        $this->x12_default_eligibility_id = $id;
    }

    function get_x12_default_eligibility_id()
    {
        return $this->x12_default_eligibility_id;
    }

    function get_x12_default_eligibility_name()
    {
        $xa = $this->_utility_array($this->X12Partner->x12_partner_factory());
        return $xa[$this->get_x12_default_eligibility_id()];
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

    function insurance_companies_factory($city = "", $sort = "ORDER BY name, id")
    {
        if (empty($city)) {
             $city = "";
        } else {
            $city = " WHERE city = '" . add_escape_custom($foreign_id) . "'";
        }

        $p = new InsuranceCompany();
        $icompanies = array();
        $sql = "SELECT p.id, a.city " .
            "FROM " . escape_table_name($p->_table) . " AS p " .
            "INNER JOIN addresses as a on p.id = a.foreign_id " . $city . " " . add_escape_custom($sort);

        //echo $sql . "<bR />";
        $results = sqlQ($sql);
        //echo "sql: $sql";
        //print_r($results);
        while ($row = sqlFetchArray($results)) {
                $icompanies[] = new InsuranceCompany($row['id']);
        }

        return $icompanies;
    }

    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "Name: " . $this->name . "\n"
        . "Attn:" . $this->attn . "\n"
        . "Payer ID:" . $this->cms_id . "\n"
        . "ALT Payer ID:" . $this->alt_cms_id . "\n"
        //. "Phone: " . $this->phone_numbers[0]->toString($html) . "\n"
        . "Address: " . $this->address->toString($html) . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }
}
