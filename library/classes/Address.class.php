<?php
/************************************************************************
  			address.php - Copyright duhlman

/usr/share/apps/umbrello/headings/heading.php

This file was generated on %date% at %time%
The original location of this file is /home/duhlman/uml-generated-code/prescription.php
**************************************************************************/

require_once("ORDataObject.class.php");
/**
 * class Address
 *
 */
class Address extends ORDataObject{
	var $id;
	var $foreign_id;
	var $line1;
	var $line2;
	var $city;
	var $state;
	var $zip;
	var $plus_four;
	var $country;

	/**
	 * Constructor sets all Address attributes to their default value
	 */
	function Address($id = "", $foreign_id = "")	{
		$this->id = $id;
		$this->foreign_id = $foreign_id;
		$this->_table = "addresses";
		$this->line1 = "";
		$this->line2 = "";
		$this->city = "";
		$this->state = "";
		$this->zip = "";
		$this->plus_four = "";
		$this->country = "USA";
		if ($id != "") {
			$this->populate();
		}


	}
	static function factory_address($foreign_id = "") {
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
	function get_lines_display() {
		$string .= $this->get_line1();
		$string .= " " . $this->get_line2();
		return $string;
	}
	function set_city($city) {
		$this->city = $city;
	}
	function get_city() {
		return $this->city;
	}
	function set_state($state) {
		$this->state = strtoupper($state);
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

} // end of Address
/*
$a = new Address("0");

echo $a->toString(true);*/
?>
