<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

define("EVENT_VEHICLE",1);
define("EVENT_WORK_RELATED",2);
define("EVENT_SLIP_FALL",3);
define("EVENT_OTHER",4);


/**
 * class FormHpTjePrimary
 *
 */
class FormLegLength extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	/**
	 *
	 * static
	 */
	var $id;
	var $date;
	var $pid;
	var $user;
	var $groupname;
	var $activity;

	var $AE_left;
	var $AE_right;
	var $BE_left;
	var $BE_right;
	var $AK_left;
	var $AK_right;
	var $K_left;
	var $K_right;
	var $BK_left;
	var $BK_right;
	var $ASIS_left;
	var $ASIS_right;
	var $UMB_left;
	var $UMB_right;
	 
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormLegLength($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");	
		}
		
		$this->_table = "form_leg_length";
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		if ($id != "") {
			$this->populate();
			//$this->date = $this->get_date();
		}
	}
	function populate() {
		parent::populate();
		//$this->temp_methods = parent::_load_enum("temp_locations",false);		
	}

	function toString($html = false) {
		$string .= "\n"
			."ID: " . $this->id . "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}
	function set_id($id) {
		if (!empty($id) && is_numeric($id)) {
			$this->id = $id;
		}
	}
	function get_id() {
		return $this->id;
	}
	function set_pid($pid) {
		if (!empty($pid) && is_numeric($pid)) {
			$this->pid = $pid;
		}
	}
	function get_pid() {
		return $this->pid;
	}
	
	function get_date() {
		return $this->date;
	}
	function set_date($dt) {
		if (!empty($dt)) {
			$this->date = $dt;
		}
	}
	function get_user() {
		return $this->user;
	}
	function set_user($u) {
		if(!empty($u)){
			$this->user = $u;
		}
	}

	function set_activity($tf) {
		if (!empty($tf) && is_numeric($tf)) {
			$this->activity = $tf;
		}
	}
	function get_activity() {
		return $this->activity;
	}
	
	function persist() {
		parent::persist();
	}
	
	//

	function get_AE_left() {
		return $this->AE_left;
	}
	function set_AE_left($tf) {
		if (true) {
			$this->AE_left = $tf;
		}
	}
	function get_AE_right() {
		return $this->AE_right;
	}

	function set_AE_right($tf) {
		if (true) {
			$this->AE_right = $tf;
		}
	}
	function get_BE_left() {
		return $this->BE_left;
	}

	function set_BE_left($tf) {
		if (true) {
			$this->BE_left = $tf;
		}
	}
	function get_BE_right() {
		return $this->BE_right;
	}

	function set_BE_right($tf) {
		if (true) {
			$this->BE_right = $tf;
		}
	}
	function get_AK_left() {
		return $this->AK_left;
	}
	function set_AK_left($tf) {
		if (true) {
			$this->AK_left = $tf;
		}
	}
	function get_AK_right() {
		return $this->AK_right;
	}
	function set_AK_right($tf) {
		if (true) {
			$this->AK_right = $tf;
		}
	}
	function get_K_left() {
		return $this->K_left;
	}

	function set_K_left($tf) {
		if (true) {
			$this->K_left = $tf;
		}
	}
	function get_K_right() {
		return $this->K_right;
	}

	function set_K_right($tf) {
		if (true) {
			$this->K_right = $tf;
		}
	}
	function get_BK_left() {
		return $this->BK_left;
	}

	function set_BK_left($tf) {
		if (true) {
			$this->BK_left = $tf;
		}
	}
	function get_BK_right() {
		return $this->BK_right;
	}

	function set_BK_right($tf) {
		if (true) {
			$this->BK_right = $tf;
		}
	}
	function get_ASIS_left() {
		return $this->ASIS_left;
	}

	function set_ASIS_left($tf) {
		if (true) {
			$this->ASIS_left = $tf;
		}
	}
	function get_ASIS_right() {
		return $this->ASIS_right;
	}

	function set_ASIS_right($tf) {
		if (true) {
			$this->ASIS_right = $tf;
		}
	}
	function get_UMB_left() {
		return $this->UMB_left;
	}

	function set_UMB_left($tf) {
		if (true) {
			$this->UMB_left = $tf;
		}
	}
	function get_UMB_right() {
		return $this->UMB_right;
	}

	function set_UMB_right($tf) {
		if (true) {
			$this->UMB_right = $tf;
		}
	}

	// ----- notes -----

	var $notes;
	function get_notes() {
		return $this->notes;
	}
	function set_notes($data) {
		if(!empty($data)) {
			$this->notes = $data;
		}
	}

}	// end of Form

?>
