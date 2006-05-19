<?php
/************************************************************************
  			phone_number.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

define("TYPE_HOME",1);
define("TYPE_WORK",2);
define("TYPE_CELL",3);
define("TYPE_EMERGENCY",4);
define("TYPE_FAX",5);

require_once("ORDataObject.class.php");

/**
 * class Address
 *
 */
class PhoneNumber extends ORDataObject{
	var $id;
	var $foreign_id;
	var $country_code;
	var $area_code;
	var $prefix;
	var $number;
	var $type_array = array("","Home", "Work", "Cell" , "Emergency" , "Fax");

	/**
	 * Constructor sets all Prescription attributes to their default value
	 */
	function PhoneNumber($id = "",$foreign_id = "")	{
		$this->id = $id;
		$this->foreign_id = $foreign_id;
		$this->country_code = "+1";
		$this->prefix = "";
		$this->number = "";
		$this->type = TYPE_HOME;
		$this->_table = "phone_numbers";
		if ($id != "") {
			$this->populate();
		}

	}

	function factory_phone_numbers($foreign_id = "") {
		if (empty($foreign_id)) {
			 $foreign_id= "like '%'";
		}
		else {
			$foreign_id= " = '" . mysql_real_escape_string(strval($foreign_id)) . "'";
		}
		$phone_numbers = array();
		$p = new PhoneNumber();
		$sql = "SELECT id FROM  " . $p->_table . " WHERE foreign_id " .$foreign_id . " ORDER BY type";
		//echo $sql . "<bR />";
		$results = sqlQ($sql);
		//echo "sql: $sql";
		while ($row = mysql_fetch_array($results) ) {
			$phone_numbers[] = new PhoneNumber($row['id']);
		}
		return $phone_numbers;
	}

	function set_id ($id) {
		$this->id = $id;
	}

	function get_id () {
		return $this->id;
	}

	function foreign_id ($id) {
		$this->foreign_id = $id;
	}

	function get_foreign_id () {
		return $this->foreign_id;
	}

	function set_country_code ($ccode) {
		$this->country_code = $ccode;
	}

	function get_country_code () {
		return $this->country_code;
	}
	function set_area_code ($acode) {
		$this->area_code = $acode;
	}

	function get_area_code () {
		return $this->area_code;
	}

	function set_number ($num) {
		$this->number = $num;
	}

	function get_number () {
		return $this->number;
	}


	function set_type ($type) {
		$this->type = $type;
	}

	function get_type () {
		return $this->type;
	}

	function set_prefix ($prefix) {
		$this->prefix = $prefix;
	}

	function get_prefix () {
		return $this->prefix;
	}
	
	function get_phone_display() {
		if (is_numeric($this->area_code) && is_numeric($this->prefix) && is_numeric($this->number)) {
			// return  "(" . $this->area_code . ") " . $this->prefix . "-" . $this->number;
			return  $this->area_code . "-" . $this->prefix . "-" . $this->number;
		}
		return "";
	}

	function set_phone($num) {
		if (strlen($num) == 10 && is_numeric($num)) {
			$this->area_code = substr ($num,0,3);
			$this->prefix = substr ($num,3,3);
			$this->number = substr ($num,6,4);
		}
		elseif (strlen($num) == 12) {
			$nums = split("-",$num);
			if (count($nums) == 3) {
				$this->area_code = $nums[0];
				$this->prefix = $nums[1];
				$this->number = $nums[2];
			}
		}
		elseif (strlen($num) == 14 && substr($num,0,1) == "(") {
			$nums[0] = substr($num,1,3);
			$nums[1] = substr($num,6,3);
			$nums[2] = substr($num,10,4);
			
			foreach ($nums as $n) {
				if (!is_numeric($n)) {
					return false;	
				}
			}
			
			if (count($nums) == 3) {
				$this->area_code = $nums[0];
				$this->prefix = $nums[1];
				$this->number = $nums[2];
			}
		}
	}

	function toString($html = false) {
		$string .= "\n"
		. "ID: " . $this->id."\n"
		. "FID: " . $this->foreign_id."\n"
		. $this->country_code . " (" . $this->area_code . ") " . $this->prefix . "-" . $this->number . " " . $this->type_array[$this->type];
		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}

	function persist($fid ="") {
		if (!empty($fid)) {
			$this->foreign_id = $fid;
		}
		parent::persist();
	}

} // end of PhoneNumber
/*$p = new PhoneNumber(1);
echo $p->toString();
$p = new PhoneNumber(true);

$ps = PhoneNumber::factory_phone_numbers(55);
foreach($ps as $p) {
	echo $p->toString(true);
}*/
?>