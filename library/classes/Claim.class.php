<?php

define ("CLAIM_TARGET_TXT",1);
define ("CLAIM_TARGET_PDF",2);
define ("CLAIM_FORMAT_X12",1);
define ("CLAIM_TARGET_HCFA",2);

require_once("ORDataObject.class.php");
/**
 * class Claim
 *
 */
class Claim extends ORDataObject{
	var $id;
	var $billing_key;
	var $target;
	var $format;
	var $server;
	var $date;
	var $procedures;
	
	/**
	 * Constructor sets all Company attributes to their default value
	 */
	function claim($id = "")	{
		$this->id = $id;
		$this->billing_key = md5($GLOBALS['billing_key_salt'] . rand() . time());
		$this->target = CLAIM_TARGET_TXT;
		$this->target = CLAIM_FORMAT_X12;
		$this->date = date("Y-m-d h:m");
		$this->procedures = new array(); 
		$this->_table = "claims";
		if ($id != "") {
			$this->populate();
		}


	}
	function factory_company($foreign_id = "") {
		if (empty($foreign_id)) {
			 $foreign_id= "like '%'";
		}
		else {
			$foreign_id= " = '" . mysql_real_escape_string(strval($foreign_id)) . "'";
		}
		$a = new Address();
		$sql = "SELECT id FROM  " . $a->_table . " WHERE foreign_id " .$foreign_id ;
		//echo $sql . "<bR />";
		$results = sqlQ($sql);
		//echo "sql: $sql";
		$row = mysql_fetch_array($results);
		if (!empty($row)) {
			$a = new Address($row['id']);
		}

		return $a;
	}

	function toString($html = false) {
		$string .= "\n"
		. "ID: " . $this->id."\n"
		. "FID: " . $this->foreign_id."\n"
		.$this->line1 . "\n"
		.$this->line2 . "\n"
		.$this->city . ", " . strtoupper($this->state) . " " . $this->zip . "-" . $this->plus_four. "\n"
		.$this->country. "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}

	function set_id($id) {
		$this->id = $id;
	}
	function get_id() {
		return $this->id;
	}
	function set_name($name) {
		$this->name = $name;
	}
	function get_name() {
		return $this->name;
	}
	function set_foreign_id($fid) {
		$this->foreign_id = $fid;
	}
	function get_foreign_id() {
		return $this->foreign_id;
	}
	function set_line1($line1) {
		$this->line1 = $line1;
	}
	function get_line1() {
		return $this->line1;
	}
	function set_line2($line2) {
		$this->line2 = $line2;
	}
	function get_line2() {
		return $this->line2;
	}
	function set_city($city) {
		$this->city = $city;
	}
	function get_city() {
		return $this->city;
	}
	function set_state($state) {
		$this->state = $state;
	}
	function get_state() {
		return $this->state;
	}
	function set_zip($zip) {
		$this->zip = $zip;
	}
	function get_zip() {
		return $this->zip;
	}
	function set_plus_four($plus_four) {
		$this->plus_four = $plus_four;
	}
	function get_plus_four() {
		return $this->plus_four;
	}
	function set_country($country) {
		$this->country = $country;
	}
	function get_country() {
		return $this->country;
	}
	function persist($fid ="") {
		if (!empty($fid)) {
			$this->foreign_id = $fid;
		}
		parent::persist();
	}

} // end of Company
?>
