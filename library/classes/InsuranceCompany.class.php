<?php

/**
 * insurance company class for smarty templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    duhlman
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) duhlman
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\ValueObjects\TypedPhoneNumber;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Services\PhoneNumberService;
use OpenEMR\Services\PhoneType;

/**
 * class Insurance Company
 *
 */

class InsuranceCompany extends ORDataObject
{
    public $name;
    /** @var TypedPhoneNumber[] */
    public array $phone_numbers = [];
    public $attn;
    public $cms_id;
    public $alt_cms_id;
    public $eligibility_id;
    public $x12_receiver_id;
    public $x12_default_partner_id;
    public $x12_default_eligibility_id;
    public $inactive;
    public $InsuranceCompany;
    /*
    *   OpenEMR can use this value to determine special formatting for the specified type of payer.
    *   It is the key to the array returned by the InsuranceCompanyService
    *   getInsuranceTypes and getInsuranceClaimTypes methods.
    *   @var int Holds constant for type of payer
    */
    public $ins_type_code;

    /*
    *   Array used to populate select dropdowns or other form elements
    *   It is the value of the array returned by the InsuranceCompanyService->getInsuranceTypes() method.
    */
    public $ins_type_code_array;

    /*
    *   Array used with electronic claim submissions and
    *   corresponds with $ins_type_code_array
    *   It is the value of the array returned by the InsuranceCompanyService->getInsuranceClaimTypes() method.
    */
    public $ins_claim_type_array;

    public $address;

    public $X12Partner;

    /**
     * @var Integer CQM SOP, Source of Payment, from HL7
     */
    public $cqm_sop;

    /**
     * @var Array contains code and description of above
     */
    public $cqm_sop_array;

    /**
     * Constructor sets all Insurance Company attributes to their default value
     */
    public function __construct(public $id = "", ?InsuranceCompanyService $insuranceCompanyService = null)
    {
        $this->name = "";
        $this->_table = "insurance_companies";
        $this->address = new Address();
        if ($insuranceCompanyService === null) {
            $this->InsuranceCompany = new InsuranceCompanyService();
        } else {
            $this->InsuranceCompany = $insuranceCompanyService;
        }
        $this->ins_type_code_array = $this->InsuranceCompany->getInsuranceTypesCached();
        $this->ins_claim_type_array = $this->InsuranceCompany->getInsuranceClaimTypes();
        if ($this->id != "") {
            $this->populate();
        }

        $this->X12Partner = new X12Partner();
        $this->cqm_sop_array = $this->InsuranceCompany->getInsuranceCqmSopCached();
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
        if ($id !== '') {
            $this->populate();
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

    public function get_display_name()
    {
        return InsuranceCompanyService::getDisplayNameForInsuranceRecord([
            'name' => $this->name,
            'line1' => $this->address->get_line1(),
            'line2' => $this->address->get_line2(),
            'city' => $this->address->get_city(),
            'state' => $this->address->get_state(),
            'zip' => $this->address->get_zip(),
            'cms_id' => $this->cms_id
        ]);
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
            if ($phone->type === PhoneType::WORK) {
                return $phone->formatLocal();
            }
        }
        return "";
    }

    public function set_number(string $num, PhoneType $type): void
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

    public function set_phone($phone)
    {
        $this->set_number($phone, PhoneType::WORK);
    }

    public function set_fax($fax)
    {
        $this->set_number($fax, PhoneType::FAX);
    }

    public function get_fax()
    {
        foreach ($this->phone_numbers as $phone) {
            if ($phone->type === PhoneType::FAX) {
                return $phone->formatLocal();
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

    public function persist()
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

    public function insurance_companies_factory()
    {
        $insuranceCompanyService = new InsuranceCompanyService();
        $icompanies = [];

        $listAll = $insuranceCompanyService->search([]);
        if ($listAll->hasData()) {
            $data = $listAll->getData();
            foreach ($data as $record) {
                // we pass in the service array so we don't recreate it each time
                $company = new InsuranceCompany("", $insuranceCompanyService);
                $company->populate_array($record);
                if (($record['work_id'] ?? null) !== null) {
                    $company->set_phone($record['work_number']);
                }
                if (($record['fax_id'] ?? null) !== null) {
                    $company->set_fax($record['fax_number']);
                }
                $icompanies[] = $company;
            }
        }
        // sort by name since we don't know that the sql query will return them in the correct order
        usort($icompanies, fn($a, $b): int => strcasecmp((string) $a->name, (string) $b->name));

        return $icompanies;
    }

    public function toString($html = false)
    {
        $string = "\n"
        . "ID: " . $this->id . "\n"
        . "Name: " . $this->name . "\n"
        . "Attn:" . $this->attn . "\n"
        . "Payer ID:" . $this->cms_id . "\n"
        . "ALT Payer ID:" . $this->alt_cms_id . "\n"
        //. "Phone: " . $this->phone_numbers[0]->toString($html) . "\n"
        . "Address: " . $this->address->toString($html) . "\n";
        return $html ? nl2br($string) : $string;
    }
}
