<?php
/************************************************************************
  			pharmacy.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

//require_once("../../interface/globals.php");
require_once("PhoneNumber.class.php");
require_once("Address.class.php");
//$GLOBALS['fileroot'] = "/var/www/openemr";
//$GLOBALS['webroot'] = "/openemr";

define ("TRANSMIT_PRINT",1);
define ("TRANSMIT_EMAIL", 2);
define ("TRANSMIT_FAX", 3);

require_once("ORDataObject.class.php");
/**
 * class Pharmacy
 *
 */
class Pharmacy extends ORDataObject{
	var $id;
	var $name;
	var $phone_numbers;
	var $address;
	var $transmit_method;
	var $email;
	var $transmit_method_array = array("","Print", "Email" ,"Fax");

	/**
	 * Constructor sets all Prescription attributes to their default value
	 */
	function Pharmacy($id = "", $prefix = "")	{
		$this->id = $id;
		$this->name = "";
		$this->email = "";
		$this->transmit_method = 1;
		$this->_table = "pharmacies";
		$phone  = new PhoneNumber();
		$phone->set_type(TYPE_WORK);
		$this->phone_numbers = array($phone);
		$this->address = new Address();
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
	function set_form_id ($id = "") {
		if (!empty($id)) {
			$this->populate($id);
		}
	}
	function set_fax_id($id) {
		$this->id = $id;
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
	function set_email($email) {
		$this->email = $email;
	}
	function get_email() {
		return $this->email;
	}
	function set_transmit_method($tm) {
		$this->transmit_method = $tm;
	}
	function get_transmit_method() {
		if ($this->transmit_method == TYPE_EMAIL && empty($this->email)) {
			return TYPE_PRINT;
		}
		return $this->transmit_method;
	}
	function get_transmit_method_display() {
		return $this->transmit_method_array[$this->transmit_method];
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
	function set_fax($fax) {
		$this->_set_number($fax, TYPE_FAX);
	}

	function get_fax() {
		foreach($this->phone_numbers as $phone) {
			if ($phone->type == TYPE_FAX) {
				return $phone->get_phone_display();
			}
		}
		return "";
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

	function utility_pharmacy_array() {
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

	function pharmacies_factory ($city = "", $sort = "ORDER BY name") {
		if (empty($city)) {
			 $city= "";
		}
		else {
			$city = " WHERE city = " . mysql_real_escape_string($foreign_id);
		}
		$p = new Pharmacy();
		$pharmacies = array();
		$sql = "SELECT p.id, a.city FROM  " . $p->_table . " as p INNER JOIN addresses as a on p.id = a.foreign_id " .$city . " " . mysql_real_escape_string($sort);

		//echo $sql . "<bR />";
		$results = sqlQ($sql);
		//echo "sql: $sql";
		//print_r($results);
		while($row = mysql_fetch_array($results) ) {
				$pharmacies[] = new Pharmacy($row['id']);
		}
		return $pharmacies;
	}

	function toString($html = false) {
		$string .= "\n"
		. "ID: " . $this->id."\n"
		. "Name: " . $this->name ."\n"
		. "Phone: " . $this->phone_numbers[0]->toString($html) . "\n"
		. "Email:" . $this->email . "\n"
		. "Address: " . $this->address->toString($html) . "\n"
		. "Method: " . $this->transmit_method_array[$this->transmit_method];

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}

} // end of Pharmacy
/*$p = new Pharmacy("1");
echo $p->toString(true);
*/
?>
