<?php

/**
 * Pharmacy class for smarty templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    duhlman
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) duhlman
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

define("TRANSMIT_PRINT", 1);
define("TRANSMIT_EMAIL", 2);
define("TRANSMIT_FAX", 3);
define("TRANSMIT_ERX", 4);

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\ValueObjects\TypedPhoneNumber;
use OpenEMR\Services\PhoneNumberService;
use OpenEMR\Services\PhoneType;

class Pharmacy extends ORDataObject
{
    public $name;
    /** @var TypedPhoneNumber[] */
    public array $phone_numbers = [];
    public $address;
    public $transmit_method;
    public $email;
    public $transmit_method_array; //set in constructor
    public $pageno;
    public $state;
    public $npi;
    public $ncpdp;

    /**
     * Constructor sets all Prescription attributes to their default value
     */
    function __construct(public $id = "")
    {
        $this->state = $this->getState();
        $this->name = "";
        $this->email = "";
        $this->transmit_method = 1;
        $this->transmit_method_array = [xl("None Selected"), xl("Print"), xl("Email"), xl("Fax"), xl("Transmit"), xl("eRx")];
        $this->_table = "pharmacies";
        $this->address = new Address();
        if ($this->id != "") {
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
        if ($id !== '') {
            $this->populate();
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
        if ($this->transmit_method == TRANSMIT_EMAIL && $this->email === '') {
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
            if ($phone->type === PhoneType::WORK) {
                return $phone->formatLocal();
            }
        }
        return "";
    }
    function set_number(string $num, PhoneType $type): void
    {
        $typed = TypedPhoneNumber::tryCreate($num, $type);
        if ($typed === null) {
            return;
        }

        // Replace existing phone of same type, or add new
        foreach ($this->phone_numbers as $i => $phone) {
            if ($phone->type === $type) {
                $this->phone_numbers[$i] = $typed;
                return;
            }
        }
        $this->phone_numbers[] = $typed;
    }

    function set_phone($phone)
    {
        $this->set_number($phone, PhoneType::WORK);
    }
    function set_fax($fax)
    {
        $this->set_number($fax, PhoneType::FAX);
    }

    function get_fax()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type === PhoneType::FAX) {
                return $phone->formatLocal();
            }
        }
        return "";
    }
    function populate()
    {
        parent::populate();
        $this->address = Address::factory_address($this->id);
        $phoneService = new PhoneNumberService();
        $this->phone_numbers = [];
        foreach ($phoneService->getPhonesByForeignId($this->id) as $record) {
            $areaCode = (string) ($record['area_code'] ?? '');
            $prefix = (string) ($record['prefix'] ?? '');
            $number = (string) ($record['number'] ?? '');
            $phoneStr = $areaCode . $prefix . $number;
            $typeValue = is_int($record['type']) ? $record['type'] : PhoneType::WORK->value;
            $type = PhoneType::tryFrom($typeValue) ?? PhoneType::WORK;
            $typed = TypedPhoneNumber::tryCreate($phoneStr, $type);
            if ($typed !== null) {
                $this->phone_numbers[] = $typed;
            }
        }
    }

    function persist()
    {
        parent::persist();
        $this->address->persist($this->id);
        $phoneService = new PhoneNumberService();
        foreach ($this->phone_numbers as $phone) {
            $phoneData = ['phone' => $phone->phoneNumber->getNationalDigits()];
            $phoneService->type = $phone->type->value;
            // Always insert for now - PhoneNumberService handles upsert logic
            $phoneService->insert($phoneData, $this->id);
        }
    }

    function utility_pharmacy_array()
    {
        $pharmacy_array = [];
        $sql = "SELECT p.id, p.name, a.city, a.state " .
            "FROM " . escape_table_name($this->_table) . " AS p INNER JOIN addresses AS a ON  p.id = a.foreign_id";
        $res = sqlQ($sql);
        while ($row = sqlFetchArray($res)) {
            $d_string = $row['city'];
            if (($row['city'] ?? '') !== '' && ($row['state'] ?? '') !== '') {
                $d_string .= ", ";
            }
            $d_string .= $row['state'];
            $pharmacy_array[strval($row['id'])] = $row['name'] . " " . $d_string;
        }

        return ($pharmacy_array);
    }

    function pharmacies_factory()
    {
        $p = new Pharmacy();
        $pharmacies = [];
        $sql = "SELECT p.id, a.city " .
            "FROM " . escape_table_name($p->_table) . " AS p " .
            "INNER JOIN addresses AS a ON p.id = a.foreign_id ";
        if ($GLOBALS['weno_rx_enable'] ?? false) {
            $sql .= "WHERE state = '" . add_escape_custom($this->state) . "' ";
        }
        $sql .= "ORDER BY name";

        $results = sqlStatement($sql);
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
        $phoneDisplay = ($this->phone_numbers[0] ?? null)?->formatLocal() ?? '';
        $string = "\n"
        . "ID: " . $this->id . "\n"
        . "Name: " . $this->name . "\n"
        . "Phone: " . $phoneDisplay . "\n"
        . "Email:" . $this->email . "\n"
        . "Address: " . $this->address->toString($html) . "\n"
        . "Method: " . $this->transmit_method_array[$this->transmit_method];
        return $html ? nl2br($string) : $string;
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
