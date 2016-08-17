<?php
/************************************************************************
  			InsuranceCompany.php - Copyright duhlman



This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

define ("INS_TYPE_OTHER_HCFA",1);
define ("INS_TYPE_MEDICARE",2);
define ("INS_TYPE_MEDICAID",3);
define ("INS_TYPE_CHAMPUSVA",4);
define ("INS_TYPE_CHAMPUS",5);
define ("INS_TYPE_BCBS",6);
define ("INS_TYPE_FECA",7);
define ("INS_TYPE_SELF_PAY",8);
define ("INS_TYPE_CENTRAL_CERTIFICATION",9);
define ("INS_TYPE_OTHER_NON-FEDERAL_PROGRAMS",10);
define ("INS_TYPE_PREFERRED_PROVIDER_ORGANIZATION",11);
define ("INS_TYPE_POINT_OF_SERVICE",12);
define ("INS_TYPE_EXCLUSIVE_PROVIDER_ORGANIZATION",13);
define ("INS_TYPE_INDEMNITY_INSURANCE",14);
define ("INS_TYPE_HMO_MEDICARE_RISK",15);
define ("INS_TYPE_AUTOMOBILE_MEDICAL",16);
define ("INS_TYPE_COMMERCIAL_INSURANCE",17);
define ("INS_TYPE_DISABILITY",18);
define ("INS_TYPE_HEALTH_MAINTENANCE_ORGANIZATION",19);
define ("INS_TYPE_LIABILITY",20);
define ("INS_TYPE_LIABILITY_MEDICAL",21);
define ("INS_TYPE_OTHER_FEDERAL_PROGRAM",22);
define ("INS_TYPE_TITLE_V",23);
define ("INS_TYPE_VETERANS_ADMINISTRATION_PLAN",24);
define ("INS_TYPE_WORKERS_COMPENSATION_HEALTH_PLAN",25);
define ("INS_TYPE_MUTUALLY_DEFINED",26);

require_once("PhoneNumber.class.php");
require_once("Address.class.php");
require_once("X12Partner.class.php");


require_once("ORDataObject.class.php");
/**
 * class Insurance Company
 *
 */
class InsuranceCompany extends ORDataObject{
	var $id;
	var $name;
	var $phone;
	var $attn;
	var $cms_id;
	var $alt_cms_id;
	//this is now deprecated use new x12 partners instead
	var $x12_receiver_id;
	var $x12_default_partner_id;

	/*
	*	Freeb used this value to determine special formatting for the specified type of payer.
	*	This value is a mutually exclusive choice answering the FB.Payer.isX API calls
	*	It references a set of constant defined in this file INS_TYPE_XXX
	*	Defaults to type INS_TYPE_OTHER_HCFA
	*	@var int Holds constant for type of payer as far as INS is concerned, see FB.Payer.isXXX API calls
	*/
	var $ins_type_code;

	/*
	*	Array used to populate select dropdowns or other form elements, it must coincide with the INS_TYPE_XXX constants
	*	@var array Values are display strings that match constants for FB.Payer.isXXX payer types, used for populating select dropdowns, etc
	*/
	var $ins_type_code_array = array('','Other HCFA'
                                        ,'Medicare Part B'
                                        ,'Medicaid'
                                        ,'ChampUSVA'
                                        ,'ChampUS'
                                        ,'Blue Cross Blue Shield'
                                        ,'FECA'
                                        ,'Self Pay'
                                        ,'Central Certification'
                                        ,'Other Non-Federal Programs'
                                        ,'Preferred Provider Organization (PPO)'
                                        ,'Point of Service (POS)'
                                        ,'Exclusive Provider Organization (EPO)'
                                        ,'Indemnity Insurance'
                                        ,'Health Maintenance Organization (HMO) Medicare Risk'
                                        ,'Automobile Medical'
                                        ,'Commercial Insurance Co.'
                                        ,'Disability'
                                        ,'Health Maintenance Organization'
                                        ,'Liability'
                                        ,'Liability Medical'
                                        ,'Other Federal Program'
                                        ,'Title V'
                                        ,'Veterans Administration Plan'
                                        ,'Workers Compensation Health Plan'
                                        ,'Mutually Defined'
                                        );

	var $ins_claim_type_array = array(''
	                                   ,'16'
	                                   ,'MB'
	                                   ,'MC'
	                                   ,'CH'
	                                   ,'CH'
	                                   ,'BL'
	                                   ,'16'
	                                   ,'09'
	                                   ,'10'
	                                   ,'11'
	                                   ,'12'
	                                   ,'13'
	                                   ,'14'
	                                   ,'15'
	                                   ,'16'
	                                   ,'AM'
	                                   ,'CI'
	                                   ,'DS'
	                                   ,'HM'
	                                   ,'LI'
	                                   ,'LM'
	                                   ,'OF'
	                                   ,'TV'
	                                   ,'VA'
	                                   ,'WC'
	                                   ,'ZZ'
	                                   );


	var $address;

	/**
	 * Constructor sets all Insurance Company attributes to their default value
	 */
	function __construct($id = "", $prefix = "")	{
		$this->id = $id;
		$this->name = "";
		$this->_table = "insurance_companies";
		$phone  = new PhoneNumber();
		$phone->set_type(TYPE_WORK);
		$this->phone = $phone;
		$this->address = new Address();
		$this->phone_numbers = array();
		if ($id != "") {
			$this->populate();
		}
		$this->X12Partner = new X12Partner();
	}

	function set_id($id = "") {
		$this->id = $id;
	}
	function get_id() {
		return $this->id;
	}

	//special function the the html forms use to prepopulate which allows for partial edits and wizard functionality
	function set_form_id ($id = "") {
		if (!empty($id)) {
			$this->populate($id);
		}
	}

	function set_address($aobj) {
		$this->address = $aobj;
	}
	function get_address() {
		return $this->address;
	}
	function set_address_line1($line) {
		$this->address->set_line1($line);
	}
	function set_address_line2($line) {
		$this->address->set_line2($line);
	}

	function set_city($city) {
		$this->address->set_city($city);
	}
	function set_state($state) {
		$this->address->set_state($state);
	}
	function set_zip($zip) {
		$this->address->set_zip($zip);
	}

	function set_name($name) {
		$this->name = $name;
	}
	function get_name() {
		return $this->name;
	}
	function set_attn($attn) {
		$this->attn = $attn;
	}
	function get_attn() {
		return $this->attn;
	}
	function set_cms_id($id) {
		$this->cms_id = $id;
	}
	function get_cms_id() {
		return $this->cms_id;
	}
	function set_alt_cms_id($id) {
		$this->alt_cms_id = $id;
	}
	function get_alt_cms_id() {
		return $this->alt_cms_id;
	}
	function set_ins_type_code($type) {
		$this->ins_type_code = $type;
	}
	function get_ins_type_code() {
		return $this->ins_type_code;
	}
	function get_ins_type_code_display() {
		return $this->ins_type_code_array[$this->ins_type_code];
	}
	function get_ins_claim_type() {
		return $this->ins_claim_type_array[$this->ins_type_code];
	}
	function get_phone() {
		foreach($this->phone_numbers as $phone) {
			if ($phone->type == TYPE_WORK) {
				return $phone->get_phone_display();
			}
		}
		return "";
	}
	function _set_number($num, $type) {
		$found = false;
		for ($i=0;$i<count($this->phone_numbers);$i++) {
			if ($this->phone_numbers[$i]->type == $type) {
				$found = true;
				$this->phone_numbers[$i]->set_phone($num);
			}
		}
		if ($found == false) {
			$p = new PhoneNumber("",$this->id);
			$p->set_type($type);
			$p->set_phone($num);
			$this->phone_numbers[] = $p;
			//print_r($this->phone_numbers);
			//echo "num is now:" . $p->get_phone_display()  . "<br />";
		}
	}

	function set_phone($phone) {
		$this->_set_number($phone, TYPE_WORK);
	}

	function set_x12_receiver_id($id) {
		//trigger_error("The set_x12_receiver_id function is now deprecated use the newer x12 partners code instead.",E_USER_NOTICE);
		$this->x12_receiver_id = $id;
	}

	function get_x12_receiver_id() {
		//trigger_error("The get_x12_receiver_id function is now deprecated use the newer x12 partners code instead.",E_USER_NOTICE);
		return $this->x12_receiver_id;
	}

	function set_x12_default_partner_id($id) {
		$this->x12_receiver_id = $id;
	}

	function get_x12_default_partner_id() {
		return $this->x12_receiver_id;
	}

	function get_x12_default_partner_name() {
		$xa = $this->_utility_array($this->X12Partner->x12_partner_factory());
		return $xa[$this->get_x12_default_partner_id()];
	}

	function populate() {
		parent::populate();
		$this->address = Address::factory_address($this->id);
		$this->phone_numbers = PhoneNumber::factory_phone_numbers($this->id);
	}

	function persist() {
		parent::persist();
		$this->address->persist($this->id);
		foreach ($this->phone_numbers as $phone) {
			$phone->persist($this->id);
		}
	}

	function utility_insurance_companies_array() {
		$pharmacy_array = array();
		$sql = "Select p.id, p.name, a.city, a.state from " . $this->_table ." as p INNER JOIN addresses as a on  p.id = a.foreign_id";
		$res = sqlQ($sql);
		while ($row = sqlFetchArray($res) ) {
				$d_string = $row['city'];
				if (!empty($row['city']) && $row['state']) {
					$d_string .= ", ";
				}
				$d_string .=  $row['state'];
				$pharmacy_array[strval($row['id'])] = $row['name'] . " " . $d_string;
		}
		return ($pharmacy_array);
	}

	function insurance_companies_factory ($city = "", $sort = "ORDER BY name, id") {
		if (empty($city)) {
			 $city= "";
		}
		else {
			$city = " WHERE city = " . add_escape_custom($foreign_id);
		}
		$p = new InsuranceCompany();
		$icompanies = array();
		$sql = "SELECT p.id, a.city FROM  " . $p->_table . " as p INNER JOIN addresses as a on p.id = a.foreign_id " .$city . " " . add_escape_custom($sort);

		//echo $sql . "<bR />";
		$results = sqlQ($sql);
		//echo "sql: $sql";
		//print_r($results);
		while($row = sqlFetchArray($results) ) {
				$icompanies[] = new InsuranceCompany($row['id']);
		}
		return $icompanies;
	}

	function toString($html = false) {
		$string .= "\n"
		. "ID: " . $this->id."\n"
		. "Name: " . $this->name ."\n"
		. "Attn:" . $this->attn . "\n"
		. "CMS ID:" . $this->cms_id . "\n"
		. "ALT CMS ID:" . $this->alt_cms_id . "\n"
		//. "Phone: " . $this->phone_numbers[0]->toString($html) . "\n"
		. "Address: " . $this->address->toString($html) . "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}

} //End Of InsuranceCompanies
?>
