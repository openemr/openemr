<?php
/************************************************************************
  			InsuranceCompany.php - Copyright duhlman



This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

define ("FREEB_TYPE_OTHER_HCFA",1);
define ("FREEB_TYPE_MEDICARE",2);
define ("FREEB_TYPE_MEDICAID",3);
define ("FREEB_TYPE_CHAMPUSVA",4);
define ("FREEB_TYPE_CHAMPUS",5);
define ("FREEB_TYPE_BCBS",6);
define ("FREEB_TYPE_FECA",7);


require_once("PhoneNumber.class.php");
require_once("Address.class.php");


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
	//this is now deprecated use new x12 partners instead
	var $x12_receiver_id;
	var $x12_default_partner_id;

	/*
	*	Freeb used this value to determine special formatting for the specified type of payer.
	*	This value is a mutually exclusive choice answering the FB.Payer.isX API calls
	*	It references a set of constant defined in this file FREEB_TYPE_XXX
	*	Defaults to type FREEB_TYPE_OTHER_HCFA
	*	@var int Holds constant for type of payer as far as FREEB is concerned, see FB.Payer.isXXX API calls
	*/
	var $freeb_type;

	/*
	*	Array used to populate select dropdowns or other form elements, it must coincide with the FREEB_TYPE_XXX constants
	*	@var array Values are display strings that match constants for FB.Payer.isXXX payer types, used for populating select dropdowns, etc
	*/
	var $freeb_type_array = array("","Other HCFA", "Medicare", "Medicaid", "ChampUSVA", "ChampUS", "Blue Cross Blue Shield", "FECA");
	var $address;

	/**
	 * Constructor sets all Insurance Company attributes to their default value
	 */
	function InsuranceCompany($id = "", $prefix = "")	{
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
	function set_freeb_type($type) {
		$this->freeb_type = $type;
	}
	function get_freeb_type() {
		return $this->freeb_type;
	}
	function get_freeb_type_display() {
		return $this->freeb_type_array[$this->freeb_type];
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
		$xa = $this->_utility_array(X12Partner::x12_partner_factory());
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
		while ($row = mysql_fetch_array($res) ) {
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
			$city = " WHERE city = " . mysql_real_escape_string($foreign_id);
		}
		$p = new InsuranceCompany();
		$icompanies = array();
		$sql = "SELECT p.id, a.city FROM  " . $p->_table . " as p INNER JOIN addresses as a on p.id = a.foreign_id " .$city . " " . mysql_real_escape_string($sort);

		//echo $sql . "<bR />";
		$results = sqlQ($sql);
		//echo "sql: $sql";
		//print_r($results);
		while($row = mysql_fetch_array($results) ) {
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
