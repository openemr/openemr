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
use OpenEMR\Common\ORDataObject\Address;

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
    var $x12_receiver_id;
    var $x12_default_partner_id;
    var $x12_default_eligibility_id;
    var $inactive;
    var $InsuranceCompany;
    /*
    *   OpenEMR can use this value to determine special formatting for the specified type of payer.
    *   It is the key to the array returned by the InsuranceCompanyService
    *   getInsuranceTypes and getInsuranceClaimTypes methods.
    *   @var int Holds constant for type of payer
    */
    var $ins_type_code;

    /*
    *   Array used to populate select dropdowns or other form elements
    *   It is the value of the array returned by the InsuranceCompanyService->getInsuranceTypes() method.
    */
    var $ins_type_code_array;

    /*
    *   Array used with electronic claim submissions and
    *   corresponds with $ins_type_code_array
    *   It is the value of the array returned by the InsuranceCompanyService->getInsuranceClaimTypes() method.
    */
    var $ins_claim_type_array;

    var $address;

    var $X12Partner;

    /**
     * @var Integer CQM SOP, Source of Payment, from HL7
     */
    var $cqm_sop;

    /**
     * @var Array contains code and description of above
     */
    var $cqm_sop_array;

    /**
     * Constructor sets all Insurance Company attributes to their default value
     */
    public function __construct($id = "", $prefix = "")
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
        $this->InsuranceCompany = new InsuranceCompanyService();
        $this->ins_type_code_array = $this->InsuranceCompany->getInsuranceTypes();
        $this->ins_claim_type_array = $this->InsuranceCompany->getInsuranceClaimTypes();
        if ($id != "") {
            $this->populate();
        }

        $this->X12Partner = new X12Partner();
        $this->cqm_sop_array = $this->InsuranceCompany->getInsuranceCqmSop();
    }

    public function set_id($id = "")
    {
        $this->id = $id;
    }
    public function get_id()
    {
        return $this->id;
    }

    // special function that the html forms use to prepopulate which allows for partial edits and wizard functionality
    public function set_form_id($id = "")
    {
        if (!empty($id)) {
            $this->populate($id);
        }
    }

    public function set_address($aobj)
    {
        $this->address = $aobj;
    }
    public function get_address()
    {
        return $this->address;
    }
    public function set_address_line1($line)
    {
        $this->address->set_line1($line);
    }
    public function set_address_line2($line)
    {
        $this->address->set_line2($line);
    }

    public function set_city($city)
    {
        $this->address->set_city($city);
    }
    public function set_state($state)
    {
        $this->address->set_state($state);
    }
    public function set_zip($zip)
    {
        $this->address->set_zip($zip);
    }
    public function set_inactive($inactive)
    {
        $this->inactive = $inactive;
    }
    public function get_inactive()
    {
        return $this->inactive;
    }
    public function set_name($name)
    {
        $this->name = $name;
    }
    public function get_name()
    {
        return $this->name;
    }
    public function set_attn($attn)
    {
        $this->attn = $attn;
    }
    public function get_attn()
    {
        return $this->attn;
    }
    public function set_cms_id($id)
    {
        $this->cms_id = $id;
    }
    public function get_cms_id()
    {
        return $this->cms_id;
    }
    public function set_alt_cms_id($id)
    {
        $this->alt_cms_id = $id;
    }
    public function get_alt_cms_id()
    {
        return $this->alt_cms_id;
    }
    public function set_eligibility_id($id)
    {
        $this->eligibility_id = $id;
    }
    public function get_eligibility_id()
    {
        return $this->eligibility_id;
    }
    public function set_ins_type_code($type)
    {
        $this->ins_type_code = $type;
    }
    public function get_ins_type_code()
    {
        return $this->ins_type_code;
    }
    public function get_ins_type_code_display()
    {
        return $this->ins_type_code_array[$this->ins_type_code];
    }
    public function get_ins_claim_type()
    {
        return $this->ins_claim_type_array[$this->ins_type_code];
    }

    public function set_cqm_sop($code)
    {
        $this->cqm_sop = $code;
    }

    public function get_cqm_sop()
    {
        return $this->cqm_sop;
    }

    public function get_phone()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type == TYPE_WORK) {
                return $phone->get_phone_display();
            }
        }

        return "";
    }
    public function set_number($num, $type)
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

    public function set_phone($phone)
    {
        $this->set_number($phone, TYPE_WORK);
    }

    public function set_fax($fax)
    {
        $this->set_number($fax, TYPE_FAX);
    }

    public function get_fax()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type == TYPE_FAX) {
                return $phone->get_phone_display();
            }
        }

        return "";
    }

    public function set_x12_default_partner_id($id)
    {
        $this->x12_receiver_id = $id;
    }

    public function get_x12_default_partner_id()
    {
        return $this->x12_receiver_id;
    }

    public function get_x12_default_partner_name()
    {
        $xa = $this->_utility_array($this->X12Partner->x12_partner_factory());
        return ($xa[$this->get_x12_default_partner_id()] ?? null);
    }

    public function set_x12_default_eligibility_id($id)
    {
        $this->x12_default_eligibility_id = $id;
    }

    public function get_x12_default_eligibility_id()
    {
        return $this->x12_default_eligibility_id;
    }

    public function get_x12_default_eligibility_name()
    {
        $xa = $this->_utility_array($this->X12Partner->x12_partner_factory());
        return $xa[$this->get_x12_default_eligibility_id()];
    }

    public function populate()
    {
        parent::populate();
        $this->address = Address::factory_address($this->id);
        $this->phone_numbers = PhoneNumber::factory_phone_numbers($this->id);
    }

    public function persist()
    {
        parent::persist();
        $this->address->persist($this->id);
        foreach ($this->phone_numbers as $phone) {
            $phone->persist($this->id);
        }
    }

    public function insurance_companies_factory($city = "", $sort = "ORDER BY name, id")
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

    public function toString($html = false)
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
