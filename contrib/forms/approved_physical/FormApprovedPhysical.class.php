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
class FormApprovedPhysical extends ORDataObject {

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
	var $authorized;
	var $activity;
	 
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormApprovedPhysical($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");	
		}
		
		$this->_table = "form_approved_physical";
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
	function set_activity($tf) {
		if (!empty($tf) && is_numeric($tf)) {
			$this->activity = $tf;
		}
	}
	function get_activity() {
		return $this->activity;
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
	
	function persist() {
		parent::persist();
	}
	


	// ----- General Appearance (eg, development, nutrition, body habitus, deformities, attention to grooming) -----

	var $col_1;
	var $col_1_textbox;
	function get_col_1() {
		return $this->col_1;
	}
	function set_col_1($data) {
		if(!empty($data)) {
			$this->col_1 = $data;
		}
	}
	function get_col_1_wnl() {
		return $this->col_1 == "WNL" ? "CHECKED" : "";	}
	function get_col_1_abn() {
		return $this->col_1 == "ABN" ? "CHECKED" : "";	}
	function get_col_1_na() {
		return $this->col_1 == "" ? "CHECKED" : "";	}
	function get_col_1_textbox() {
		return $this->col_1_textbox;
	}
	function set_col_1_textbox($data) {
		if(!empty($data)) {
			$this->col_1_textbox = $data;
		}
	}
	
	// ----- Inspection of conjunctiva and lids -----

	var $col_2;
	var $col_2_textbox;
	function get_col_2() {
		return $this->col_2;
	}
	function set_col_2($data) {
		if(!empty($data)) {
			$this->col_2 = $data;
		}
	}
	function get_col_2_wnl() {
		return $this->col_2 == "WNL" ? "CHECKED" : "";	}
	function get_col_2_abn() {
		return $this->col_2 == "ABN" ? "CHECKED" : "";	}
	function get_col_2_na() {
		return $this->col_2 == "" ? "CHECKED" : "";	}
	function get_col_2_textbox() {
		return $this->col_2_textbox;
	}
	function set_col_2_textbox($data) {
		if(!empty($data)) {
			$this->col_2_textbox = $data;
		}
	}
	
	
	var $col_3;
	var $col_3_textbox;
	function get_col_3() {
		return $this->col_3;
	}
	function set_col_3($data) {
		if(!empty($data)) {
			$this->col_3 = $data;
		}
	}
	function get_col_3_wnl() {
		return $this->col_3 == "WNL" ? "CHECKED" : "";	}
	function get_col_3_abn() {
		return $this->col_3 == "ABN" ? "CHECKED" : "";	}
	function get_col_3_na() {
		return $this->col_3 == "" ? "CHECKED" : "";	}
	function get_col_3_textbox() {
		return $this->col_3_textbox;
	}
	function set_col_3_textbox($data) {
		if(!empty($data)) {
			$this->col_3_textbox = $data;
		}
	}
	
	
	var $col_4;
	var $col_4_textbox;
	function get_col_4() {
		return $this->col_4;
	}
	function set_col_4($data) {
		if(!empty($data)) {
			$this->col_4 = $data;
		}
	}
	function get_col_4_wnl() {
		return $this->col_4 == "WNL" ? "CHECKED" : "";	}
	function get_col_4_abn() {
		return $this->col_4 == "ABN" ? "CHECKED" : "";	}
	function get_col_4_na() {
		return $this->col_4 == "" ? "CHECKED" : "";	}
	function get_col_4_textbox() {
		return $this->col_4_textbox;
	}
	function set_col_4_textbox($data) {
		if(!empty($data)) {
			$this->col_4_textbox = $data;
		}
	}
	
	// ----- External inspection of ears and nose (overall appearance, scars, lesions, masses) -----

	var $col_5;
	var $col_5_textbox;
	function get_col_5() {
		return $this->col_5;
	}
	function set_col_5($data) {
		if(!empty($data)) {
			$this->col_5 = $data;
		}
	}
	function get_col_5_wnl() {
		return $this->col_5 == "WNL" ? "CHECKED" : "";	}
	function get_col_5_abn() {
		return $this->col_5 == "ABN" ? "CHECKED" : "";	}
	function get_col_5_na() {
		return $this->col_5 == "" ? "CHECKED" : "";	}
	function get_col_5_textbox() {
		return $this->col_5_textbox;
	}
	function set_col_5_textbox($data) {
		if(!empty($data)) {
			$this->col_5_textbox = $data;
		}
	}
	
	
	var $col_6;
	var $col_6_textbox;
	function get_col_6() {
		return $this->col_6;
	}
	function set_col_6($data) {
		if(!empty($data)) {
			$this->col_6 = $data;
		}
	}
	function get_col_6_wnl() {
		return $this->col_6 == "WNL" ? "CHECKED" : "";	}
	function get_col_6_abn() {
		return $this->col_6 == "ABN" ? "CHECKED" : "";	}
	function get_col_6_na() {
		return $this->col_6 == "" ? "CHECKED" : "";	}
	function get_col_6_textbox() {
		return $this->col_6_textbox;
	}
	function set_col_6_textbox($data) {
		if(!empty($data)) {
			$this->col_6_textbox = $data;
		}
	}
	
	
	var $col_7;
	var $col_7_textbox;
	function get_col_7() {
		return $this->col_7;
	}
	function set_col_7($data) {
		if(!empty($data)) {
			$this->col_7 = $data;
		}
	}
	function get_col_7_wnl() {
		return $this->col_7 == "WNL" ? "CHECKED" : "";	}
	function get_col_7_abn() {
		return $this->col_7 == "ABN" ? "CHECKED" : "";	}
	function get_col_7_na() {
		return $this->col_7 == "" ? "CHECKED" : "";	}
	function get_col_7_textbox() {
		return $this->col_7_textbox;
	}
	function set_col_7_textbox($data) {
		if(!empty($data)) {
			$this->col_7_textbox = $data;
		}
	}
	
	
	var $col_8;
	var $col_8_textbox;
	function get_col_8() {
		return $this->col_8;
	}
	function set_col_8($data) {
		if(!empty($data)) {
			$this->col_8 = $data;
		}
	}
	function get_col_8_wnl() {
		return $this->col_8 == "WNL" ? "CHECKED" : "";	}
	function get_col_8_abn() {
		return $this->col_8 == "ABN" ? "CHECKED" : "";	}
	function get_col_8_na() {
		return $this->col_8 == "" ? "CHECKED" : "";	}
	function get_col_8_textbox() {
		return $this->col_8_textbox;
	}
	function set_col_8_textbox($data) {
		if(!empty($data)) {
			$this->col_8_textbox = $data;
		}
	}
	
	
	var $col_9;
	var $col_9_textbox;
	function get_col_9() {
		return $this->col_9;
	}
	function set_col_9($data) {
		if(!empty($data)) {
			$this->col_9 = $data;
		}
	}
	function get_col_9_wnl() {
		return $this->col_9 == "WNL" ? "CHECKED" : "";	}
	function get_col_9_abn() {
		return $this->col_9 == "ABN" ? "CHECKED" : "";	}
	function get_col_9_na() {
		return $this->col_9 == "" ? "CHECKED" : "";	}
	function get_col_9_textbox() {
		return $this->col_9_textbox;
	}
	function set_col_9_textbox($data) {
		if(!empty($data)) {
			$this->col_9_textbox = $data;
		}
	}
	
	
	var $col_10;
	var $col_10_textbox;
	function get_col_10() {
		return $this->col_10;
	}
	function set_col_10($data) {
		if(!empty($data)) {
			$this->col_10 = $data;
		}
	}
	function get_col_10_wnl() {
		return $this->col_10 == "WNL" ? "CHECKED" : "";	}
	function get_col_10_abn() {
		return $this->col_10 == "ABN" ? "CHECKED" : "";	}
	function get_col_10_na() {
		return $this->col_10 == "" ? "CHECKED" : "";	}
	function get_col_10_textbox() {
		return $this->col_10_textbox;
	}
	function set_col_10_textbox($data) {
		if(!empty($data)) {
			$this->col_10_textbox = $data;
		}
	}
	
	// ----- Examination of neck (eg, masses, overall appearance, symmetry, tracheal position, crepitus) -----

	var $col_11;
	var $col_11_textbox;
	function get_col_11() {
		return $this->col_11;
	}
	function set_col_11($data) {
		if(!empty($data)) {
			$this->col_11 = $data;
		}
	}
	function get_col_11_wnl() {
		return $this->col_11 == "WNL" ? "CHECKED" : "";	}
	function get_col_11_abn() {
		return $this->col_11 == "ABN" ? "CHECKED" : "";	}
	function get_col_11_na() {
		return $this->col_11 == "" ? "CHECKED" : "";	}
	function get_col_11_textbox() {
		return $this->col_11_textbox;
	}
	function set_col_11_textbox($data) {
		if(!empty($data)) {
			$this->col_11_textbox = $data;
		}
	}
	
	
	var $col_12;
	var $col_12_textbox;
	function get_col_12() {
		return $this->col_12;
	}
	function set_col_12($data) {
		if(!empty($data)) {
			$this->col_12 = $data;
		}
	}
	function get_col_12_wnl() {
		return $this->col_12 == "WNL" ? "CHECKED" : "";	}
	function get_col_12_abn() {
		return $this->col_12 == "ABN" ? "CHECKED" : "";	}
	function get_col_12_na() {
		return $this->col_12 == "" ? "CHECKED" : "";	}
	function get_col_12_textbox() {
		return $this->col_12_textbox;
	}
	function set_col_12_textbox($data) {
		if(!empty($data)) {
			$this->col_12_textbox = $data;
		}
	}
	
	// ----- Assessment of respiratory effort (eg, intercostal retractions, use of accessory muscles, diaphragmatic movement) -----

	var $col_13;
	var $col_13_textbox;
	function get_col_13() {
		return $this->col_13;
	}
	function set_col_13($data) {
		if(!empty($data)) {
			$this->col_13 = $data;
		}
	}
	function get_col_13_wnl() {
		return $this->col_13 == "WNL" ? "CHECKED" : "";	}
	function get_col_13_abn() {
		return $this->col_13 == "ABN" ? "CHECKED" : "";	}
	function get_col_13_na() {
		return $this->col_13 == "" ? "CHECKED" : "";	}
	function get_col_13_textbox() {
		return $this->col_13_textbox;
	}
	function set_col_13_textbox($data) {
		if(!empty($data)) {
			$this->col_13_textbox = $data;
		}
	}
	
	
	var $col_14;
	var $col_14_textbox;
	function get_col_14() {
		return $this->col_14;
	}
	function set_col_14($data) {
		if(!empty($data)) {
			$this->col_14 = $data;
		}
	}
	function get_col_14_wnl() {
		return $this->col_14 == "WNL" ? "CHECKED" : "";	}
	function get_col_14_abn() {
		return $this->col_14 == "ABN" ? "CHECKED" : "";	}
	function get_col_14_na() {
		return $this->col_14 == "" ? "CHECKED" : "";	}
	function get_col_14_textbox() {
		return $this->col_14_textbox;
	}
	function set_col_14_textbox($data) {
		if(!empty($data)) {
			$this->col_14_textbox = $data;
		}
	}
	
	
	var $col_15;
	var $col_15_textbox;
	function get_col_15() {
		return $this->col_15;
	}
	function set_col_15($data) {
		if(!empty($data)) {
			$this->col_15 = $data;
		}
	}
	function get_col_15_wnl() {
		return $this->col_15 == "WNL" ? "CHECKED" : "";	}
	function get_col_15_abn() {
		return $this->col_15 == "ABN" ? "CHECKED" : "";	}
	function get_col_15_na() {
		return $this->col_15 == "" ? "CHECKED" : "";	}
	function get_col_15_textbox() {
		return $this->col_15_textbox;
	}
	function set_col_15_textbox($data) {
		if(!empty($data)) {
			$this->col_15_textbox = $data;
		}
	}
	
	
	var $col_16;
	var $col_16_textbox;
	function get_col_16() {
		return $this->col_16;
	}
	function set_col_16($data) {
		if(!empty($data)) {
			$this->col_16 = $data;
		}
	}
	function get_col_16_wnl() {
		return $this->col_16 == "WNL" ? "CHECKED" : "";	}
	function get_col_16_abn() {
		return $this->col_16 == "ABN" ? "CHECKED" : "";	}
	function get_col_16_na() {
		return $this->col_16 == "" ? "CHECKED" : "";	}
	function get_col_16_textbox() {
		return $this->col_16_textbox;
	}
	function set_col_16_textbox($data) {
		if(!empty($data)) {
			$this->col_16_textbox = $data;
		}
	}
	
	// ----- Palpation of heart (eg, location, size, thrills) -----

	var $col_17;
	var $col_17_textbox;
	function get_col_17() {
		return $this->col_17;
	}
	function set_col_17($data) {
		if(!empty($data)) {
			$this->col_17 = $data;
		}
	}
	function get_col_17_wnl() {
		return $this->col_17 == "WNL" ? "CHECKED" : "";	}
	function get_col_17_abn() {
		return $this->col_17 == "ABN" ? "CHECKED" : "";	}
	function get_col_17_na() {
		return $this->col_17 == "" ? "CHECKED" : "";	}
	function get_col_17_textbox() {
		return $this->col_17_textbox;
	}
	function set_col_17_textbox($data) {
		if(!empty($data)) {
			$this->col_17_textbox = $data;
		}
	}
	
	
	var $col_18;
	var $col_18_textbox;
	function get_col_18() {
		return $this->col_18;
	}
	function set_col_18($data) {
		if(!empty($data)) {
			$this->col_18 = $data;
		}
	}
	function get_col_18_wnl() {
		return $this->col_18 == "WNL" ? "CHECKED" : "";	}
	function get_col_18_abn() {
		return $this->col_18 == "ABN" ? "CHECKED" : "";	}
	function get_col_18_na() {
		return $this->col_18 == "" ? "CHECKED" : "";	}
	function get_col_18_textbox() {
		return $this->col_18_textbox;
	}
	function set_col_18_textbox($data) {
		if(!empty($data)) {
			$this->col_18_textbox = $data;
		}
	}
	
	
	var $col_19;
	var $col_19_textbox;
	function get_col_19() {
		return $this->col_19;
	}
	function set_col_19($data) {
		if(!empty($data)) {
			$this->col_19 = $data;
		}
	}
	function get_col_19_wnl() {
		return $this->col_19 == "WNL" ? "CHECKED" : "";	}
	function get_col_19_abn() {
		return $this->col_19 == "ABN" ? "CHECKED" : "";	}
	function get_col_19_na() {
		return $this->col_19 == "" ? "CHECKED" : "";	}
	function get_col_19_textbox() {
		return $this->col_19_textbox;
	}
	function set_col_19_textbox($data) {
		if(!empty($data)) {
			$this->col_19_textbox = $data;
		}
	}
	
	
	var $col_20;
	var $col_20_textbox;
	function get_col_20() {
		return $this->col_20;
	}
	function set_col_20($data) {
		if(!empty($data)) {
			$this->col_20 = $data;
		}
	}
	function get_col_20_wnl() {
		return $this->col_20 == "WNL" ? "CHECKED" : "";	}
	function get_col_20_abn() {
		return $this->col_20 == "ABN" ? "CHECKED" : "";	}
	function get_col_20_na() {
		return $this->col_20 == "" ? "CHECKED" : "";	}
	function get_col_20_textbox() {
		return $this->col_20_textbox;
	}
	function set_col_20_textbox($data) {
		if(!empty($data)) {
			$this->col_20_textbox = $data;
		}
	}
	
	
	var $col_21;
	var $col_21_textbox;
	function get_col_21() {
		return $this->col_21;
	}
	function set_col_21($data) {
		if(!empty($data)) {
			$this->col_21 = $data;
		}
	}
	function get_col_21_wnl() {
		return $this->col_21 == "WNL" ? "CHECKED" : "";	}
	function get_col_21_abn() {
		return $this->col_21 == "ABN" ? "CHECKED" : "";	}
	function get_col_21_na() {
		return $this->col_21 == "" ? "CHECKED" : "";	}
	function get_col_21_textbox() {
		return $this->col_21_textbox;
	}
	function set_col_21_textbox($data) {
		if(!empty($data)) {
			$this->col_21_textbox = $data;
		}
	}
	
	
	var $col_22;
	var $col_22_textbox;
	function get_col_22() {
		return $this->col_22;
	}
	function set_col_22($data) {
		if(!empty($data)) {
			$this->col_22 = $data;
		}
	}
	function get_col_22_wnl() {
		return $this->col_22 == "WNL" ? "CHECKED" : "";	}
	function get_col_22_abn() {
		return $this->col_22 == "ABN" ? "CHECKED" : "";	}
	function get_col_22_na() {
		return $this->col_22 == "" ? "CHECKED" : "";	}
	function get_col_22_textbox() {
		return $this->col_22_textbox;
	}
	function set_col_22_textbox($data) {
		if(!empty($data)) {
			$this->col_22_textbox = $data;
		}
	}
	
	
	var $col_23;
	var $col_23_textbox;
	function get_col_23() {
		return $this->col_23;
	}
	function set_col_23($data) {
		if(!empty($data)) {
			$this->col_23 = $data;
		}
	}
	function get_col_23_wnl() {
		return $this->col_23 == "WNL" ? "CHECKED" : "";	}
	function get_col_23_abn() {
		return $this->col_23 == "ABN" ? "CHECKED" : "";	}
	function get_col_23_na() {
		return $this->col_23 == "" ? "CHECKED" : "";	}
	function get_col_23_textbox() {
		return $this->col_23_textbox;
	}
	function set_col_23_textbox($data) {
		if(!empty($data)) {
			$this->col_23_textbox = $data;
		}
	}
	
	// ----- Inspection of breasts (eg, symmetry, nipple discharge) -----

	var $col_24;
	var $col_24_textbox;
	function get_col_24() {
		return $this->col_24;
	}
	function set_col_24($data) {
		if(!empty($data)) {
			$this->col_24 = $data;
		}
	}
	function get_col_24_wnl() {
		return $this->col_24 == "WNL" ? "CHECKED" : "";	}
	function get_col_24_abn() {
		return $this->col_24 == "ABN" ? "CHECKED" : "";	}
	function get_col_24_na() {
		return $this->col_24 == "" ? "CHECKED" : "";	}
	function get_col_24_textbox() {
		return $this->col_24_textbox;
	}
	function set_col_24_textbox($data) {
		if(!empty($data)) {
			$this->col_24_textbox = $data;
		}
	}
	
	
	var $col_25;
	var $col_25_textbox;
	function get_col_25() {
		return $this->col_25;
	}
	function set_col_25($data) {
		if(!empty($data)) {
			$this->col_25 = $data;
		}
	}
	function get_col_25_wnl() {
		return $this->col_25 == "WNL" ? "CHECKED" : "";	}
	function get_col_25_abn() {
		return $this->col_25 == "ABN" ? "CHECKED" : "";	}
	function get_col_25_na() {
		return $this->col_25 == "" ? "CHECKED" : "";	}
	function get_col_25_textbox() {
		return $this->col_25_textbox;
	}
	function set_col_25_textbox($data) {
		if(!empty($data)) {
			$this->col_25_textbox = $data;
		}
	}
	
	// ----- Examination of abdomen with notation of presence of masses or tenderness -----

	var $col_26;
	var $col_26_textbox;
	function get_col_26() {
		return $this->col_26;
	}
	function set_col_26($data) {
		if(!empty($data)) {
			$this->col_26 = $data;
		}
	}
	function get_col_26_wnl() {
		return $this->col_26 == "WNL" ? "CHECKED" : "";	}
	function get_col_26_abn() {
		return $this->col_26 == "ABN" ? "CHECKED" : "";	}
	function get_col_26_na() {
		return $this->col_26 == "" ? "CHECKED" : "";	}
	function get_col_26_textbox() {
		return $this->col_26_textbox;
	}
	function set_col_26_textbox($data) {
		if(!empty($data)) {
			$this->col_26_textbox = $data;
		}
	}
	
	
	var $col_27;
	var $col_27_textbox;
	function get_col_27() {
		return $this->col_27;
	}
	function set_col_27($data) {
		if(!empty($data)) {
			$this->col_27 = $data;
		}
	}
	function get_col_27_wnl() {
		return $this->col_27 == "WNL" ? "CHECKED" : "";	}
	function get_col_27_abn() {
		return $this->col_27 == "ABN" ? "CHECKED" : "";	}
	function get_col_27_na() {
		return $this->col_27 == "" ? "CHECKED" : "";	}
	function get_col_27_textbox() {
		return $this->col_27_textbox;
	}
	function set_col_27_textbox($data) {
		if(!empty($data)) {
			$this->col_27_textbox = $data;
		}
	}
	
	
	var $col_28;
	var $col_28_textbox;
	function get_col_28() {
		return $this->col_28;
	}
	function set_col_28($data) {
		if(!empty($data)) {
			$this->col_28 = $data;
		}
	}
	function get_col_28_wnl() {
		return $this->col_28 == "WNL" ? "CHECKED" : "";	}
	function get_col_28_abn() {
		return $this->col_28 == "ABN" ? "CHECKED" : "";	}
	function get_col_28_na() {
		return $this->col_28 == "" ? "CHECKED" : "";	}
	function get_col_28_textbox() {
		return $this->col_28_textbox;
	}
	function set_col_28_textbox($data) {
		if(!empty($data)) {
			$this->col_28_textbox = $data;
		}
	}
	
	
	var $col_29;
	var $col_29_textbox;
	function get_col_29() {
		return $this->col_29;
	}
	function set_col_29($data) {
		if(!empty($data)) {
			$this->col_29 = $data;
		}
	}
	function get_col_29_wnl() {
		return $this->col_29 == "WNL" ? "CHECKED" : "";	}
	function get_col_29_abn() {
		return $this->col_29 == "ABN" ? "CHECKED" : "";	}
	function get_col_29_na() {
		return $this->col_29 == "" ? "CHECKED" : "";	}
	function get_col_29_textbox() {
		return $this->col_29_textbox;
	}
	function set_col_29_textbox($data) {
		if(!empty($data)) {
			$this->col_29_textbox = $data;
		}
	}
	
	
	var $col_30;
	var $col_30_textbox;
	function get_col_30() {
		return $this->col_30;
	}
	function set_col_30($data) {
		if(!empty($data)) {
			$this->col_30 = $data;
		}
	}
	function get_col_30_wnl() {
		return $this->col_30 == "WNL" ? "CHECKED" : "";	}
	function get_col_30_abn() {
		return $this->col_30 == "ABN" ? "CHECKED" : "";	}
	function get_col_30_na() {
		return $this->col_30 == "" ? "CHECKED" : "";	}
	function get_col_30_textbox() {
		return $this->col_30_textbox;
	}
	function set_col_30_textbox($data) {
		if(!empty($data)) {
			$this->col_30_textbox = $data;
		}
	}
	
	// ----- Examination of the scrotal contents (eg, hydrocele, spermatocele, tenderness of cord, testicular mass) -----

	var $col_31;
	var $col_31_textbox;
	function get_col_31() {
		return $this->col_31;
	}
	function set_col_31($data) {
		if(!empty($data)) {
			$this->col_31 = $data;
		}
	}
	function get_col_31_wnl() {
		return $this->col_31 == "WNL" ? "CHECKED" : "";	}
	function get_col_31_abn() {
		return $this->col_31 == "ABN" ? "CHECKED" : "";	}
	function get_col_31_na() {
		return $this->col_31 == "" ? "CHECKED" : "";	}
	function get_col_31_textbox() {
		return $this->col_31_textbox;
	}
	function set_col_31_textbox($data) {
		if(!empty($data)) {
			$this->col_31_textbox = $data;
		}
	}
	
	
	var $col_32;
	var $col_32_textbox;
	function get_col_32() {
		return $this->col_32;
	}
	function set_col_32($data) {
		if(!empty($data)) {
			$this->col_32 = $data;
		}
	}
	function get_col_32_wnl() {
		return $this->col_32 == "WNL" ? "CHECKED" : "";	}
	function get_col_32_abn() {
		return $this->col_32 == "ABN" ? "CHECKED" : "";	}
	function get_col_32_na() {
		return $this->col_32 == "" ? "CHECKED" : "";	}
	function get_col_32_textbox() {
		return $this->col_32_textbox;
	}
	function set_col_32_textbox($data) {
		if(!empty($data)) {
			$this->col_32_textbox = $data;
		}
	}
	
	
	var $col_33;
	var $col_33_textbox;
	function get_col_33() {
		return $this->col_33;
	}
	function set_col_33($data) {
		if(!empty($data)) {
			$this->col_33 = $data;
		}
	}
	function get_col_33_wnl() {
		return $this->col_33 == "WNL" ? "CHECKED" : "";	}
	function get_col_33_abn() {
		return $this->col_33 == "ABN" ? "CHECKED" : "";	}
	function get_col_33_na() {
		return $this->col_33 == "" ? "CHECKED" : "";	}
	function get_col_33_textbox() {
		return $this->col_33_textbox;
	}
	function set_col_33_textbox($data) {
		if(!empty($data)) {
			$this->col_33_textbox = $data;
		}
	}
	
	// ----- Pelvic examination (with or without specimen collection for smears and cultures), including -----

	var $col_34;
	var $col_34_textbox;
	function get_col_34() {
		return $this->col_34;
	}
	function set_col_34($data) {
		if(!empty($data)) {
			$this->col_34 = $data;
		}
	}
	function get_col_34_wnl() {
		return $this->col_34 == "WNL" ? "CHECKED" : "";	}
	function get_col_34_abn() {
		return $this->col_34 == "ABN" ? "CHECKED" : "";	}
	function get_col_34_na() {
		return $this->col_34 == "" ? "CHECKED" : "";	}
	function get_col_34_textbox() {
		return $this->col_34_textbox;
	}
	function set_col_34_textbox($data) {
		if(!empty($data)) {
			$this->col_34_textbox = $data;
		}
	}
	
	
	var $col_35;
	var $col_35_textbox;
	function get_col_35() {
		return $this->col_35;
	}
	function set_col_35($data) {
		if(!empty($data)) {
			$this->col_35 = $data;
		}
	}
	function get_col_35_wnl() {
		return $this->col_35 == "WNL" ? "CHECKED" : "";	}
	function get_col_35_abn() {
		return $this->col_35 == "ABN" ? "CHECKED" : "";	}
	function get_col_35_na() {
		return $this->col_35 == "" ? "CHECKED" : "";	}
	function get_col_35_textbox() {
		return $this->col_35_textbox;
	}
	function set_col_35_textbox($data) {
		if(!empty($data)) {
			$this->col_35_textbox = $data;
		}
	}
	
	
	var $col_36;
	var $col_36_textbox;
	function get_col_36() {
		return $this->col_36;
	}
	function set_col_36($data) {
		if(!empty($data)) {
			$this->col_36 = $data;
		}
	}
	function get_col_36_wnl() {
		return $this->col_36 == "WNL" ? "CHECKED" : "";	}
	function get_col_36_abn() {
		return $this->col_36 == "ABN" ? "CHECKED" : "";	}
	function get_col_36_na() {
		return $this->col_36 == "" ? "CHECKED" : "";	}
	function get_col_36_textbox() {
		return $this->col_36_textbox;
	}
	function set_col_36_textbox($data) {
		if(!empty($data)) {
			$this->col_36_textbox = $data;
		}
	}
	
	
	var $col_37;
	var $col_37_textbox;
	function get_col_37() {
		return $this->col_37;
	}
	function set_col_37($data) {
		if(!empty($data)) {
			$this->col_37 = $data;
		}
	}
	function get_col_37_wnl() {
		return $this->col_37 == "WNL" ? "CHECKED" : "";	}
	function get_col_37_abn() {
		return $this->col_37 == "ABN" ? "CHECKED" : "";	}
	function get_col_37_na() {
		return $this->col_37 == "" ? "CHECKED" : "";	}
	function get_col_37_textbox() {
		return $this->col_37_textbox;
	}
	function set_col_37_textbox($data) {
		if(!empty($data)) {
			$this->col_37_textbox = $data;
		}
	}
	
	
	var $col_38;
	var $col_38_textbox;
	function get_col_38() {
		return $this->col_38;
	}
	function set_col_38($data) {
		if(!empty($data)) {
			$this->col_38 = $data;
		}
	}
	function get_col_38_wnl() {
		return $this->col_38 == "WNL" ? "CHECKED" : "";	}
	function get_col_38_abn() {
		return $this->col_38 == "ABN" ? "CHECKED" : "";	}
	function get_col_38_na() {
		return $this->col_38 == "" ? "CHECKED" : "";	}
	function get_col_38_textbox() {
		return $this->col_38_textbox;
	}
	function set_col_38_textbox($data) {
		if(!empty($data)) {
			$this->col_38_textbox = $data;
		}
	}
	
	
	var $col_39;
	var $col_39_textbox;
	function get_col_39() {
		return $this->col_39;
	}
	function set_col_39($data) {
		if(!empty($data)) {
			$this->col_39 = $data;
		}
	}
	function get_col_39_wnl() {
		return $this->col_39 == "WNL" ? "CHECKED" : "";	}
	function get_col_39_abn() {
		return $this->col_39 == "ABN" ? "CHECKED" : "";	}
	function get_col_39_na() {
		return $this->col_39 == "" ? "CHECKED" : "";	}
	function get_col_39_textbox() {
		return $this->col_39_textbox;
	}
	function set_col_39_textbox($data) {
		if(!empty($data)) {
			$this->col_39_textbox = $data;
		}
	}
	
	
	var $col_40;
	var $col_40_textbox;
	function get_col_40() {
		return $this->col_40;
	}
	function set_col_40($data) {
		if(!empty($data)) {
			$this->col_40 = $data;
		}
	}
	function get_col_40_wnl() {
		return $this->col_40 == "WNL" ? "CHECKED" : "";	}
	function get_col_40_abn() {
		return $this->col_40 == "ABN" ? "CHECKED" : "";	}
	function get_col_40_na() {
		return $this->col_40 == "" ? "CHECKED" : "";	}
	function get_col_40_textbox() {
		return $this->col_40_textbox;
	}
	function set_col_40_textbox($data) {
		if(!empty($data)) {
			$this->col_40_textbox = $data;
		}
	}
	
	// ----- Palpation of lymph nodes in or more areas: -----

	var $col_41;
	var $col_41_textbox;
	function get_col_41() {
		return $this->col_41;
	}
	function set_col_41($data) {
		if(!empty($data)) {
			$this->col_41 = $data;
		}
	}
	function get_col_41_wnl() {
		return $this->col_41 == "WNL" ? "CHECKED" : "";	}
	function get_col_41_abn() {
		return $this->col_41 == "ABN" ? "CHECKED" : "";	}
	function get_col_41_na() {
		return $this->col_41 == "" ? "CHECKED" : "";	}
	function get_col_41_textbox() {
		return $this->col_41_textbox;
	}
	function set_col_41_textbox($data) {
		if(!empty($data)) {
			$this->col_41_textbox = $data;
		}
	}
	
	
	var $col_42;
	var $col_42_textbox;
	function get_col_42() {
		return $this->col_42;
	}
	function set_col_42($data) {
		if(!empty($data)) {
			$this->col_42 = $data;
		}
	}
	function get_col_42_wnl() {
		return $this->col_42 == "WNL" ? "CHECKED" : "";	}
	function get_col_42_abn() {
		return $this->col_42 == "ABN" ? "CHECKED" : "";	}
	function get_col_42_na() {
		return $this->col_42 == "" ? "CHECKED" : "";	}
	function get_col_42_textbox() {
		return $this->col_42_textbox;
	}
	function set_col_42_textbox($data) {
		if(!empty($data)) {
			$this->col_42_textbox = $data;
		}
	}
	
	
	var $col_43;
	var $col_43_textbox;
	function get_col_43() {
		return $this->col_43;
	}
	function set_col_43($data) {
		if(!empty($data)) {
			$this->col_43 = $data;
		}
	}
	function get_col_43_wnl() {
		return $this->col_43 == "WNL" ? "CHECKED" : "";	}
	function get_col_43_abn() {
		return $this->col_43 == "ABN" ? "CHECKED" : "";	}
	function get_col_43_na() {
		return $this->col_43 == "" ? "CHECKED" : "";	}
	function get_col_43_textbox() {
		return $this->col_43_textbox;
	}
	function set_col_43_textbox($data) {
		if(!empty($data)) {
			$this->col_43_textbox = $data;
		}
	}
	
	
	var $col_44;
	var $col_44_textbox;
	function get_col_44() {
		return $this->col_44;
	}
	function set_col_44($data) {
		if(!empty($data)) {
			$this->col_44 = $data;
		}
	}
	function get_col_44_wnl() {
		return $this->col_44 == "WNL" ? "CHECKED" : "";	}
	function get_col_44_abn() {
		return $this->col_44 == "ABN" ? "CHECKED" : "";	}
	function get_col_44_na() {
		return $this->col_44 == "" ? "CHECKED" : "";	}
	function get_col_44_textbox() {
		return $this->col_44_textbox;
	}
	function set_col_44_textbox($data) {
		if(!empty($data)) {
			$this->col_44_textbox = $data;
		}
	}
	
	
	var $col_45;
	var $col_45_textbox;
	function get_col_45() {
		return $this->col_45;
	}
	function set_col_45($data) {
		if(!empty($data)) {
			$this->col_45 = $data;
		}
	}
	function get_col_45_wnl() {
		return $this->col_45 == "WNL" ? "CHECKED" : "";	}
	function get_col_45_abn() {
		return $this->col_45 == "ABN" ? "CHECKED" : "";	}
	function get_col_45_na() {
		return $this->col_45 == "" ? "CHECKED" : "";	}
	function get_col_45_textbox() {
		return $this->col_45_textbox;
	}
	function set_col_45_textbox($data) {
		if(!empty($data)) {
			$this->col_45_textbox = $data;
		}
	}
	
	// ----- Examination of gait and station -----

	var $col_46;
	var $col_46_textbox;
	function get_col_46() {
		return $this->col_46;
	}
	function set_col_46($data) {
		if(!empty($data)) {
			$this->col_46 = $data;
		}
	}
	function get_col_46_wnl() {
		return $this->col_46 == "WNL" ? "CHECKED" : "";	}
	function get_col_46_abn() {
		return $this->col_46 == "ABN" ? "CHECKED" : "";	}
	function get_col_46_na() {
		return $this->col_46 == "" ? "CHECKED" : "";	}
	function get_col_46_textbox() {
		return $this->col_46_textbox;
	}
	function set_col_46_textbox($data) {
		if(!empty($data)) {
			$this->col_46_textbox = $data;
		}
	}
	
	
	var $col_47;
	var $col_47_textbox;
	function get_col_47() {
		return $this->col_47;
	}
	function set_col_47($data) {
		if(!empty($data)) {
			$this->col_47 = $data;
		}
	}
	function get_col_47_wnl() {
		return $this->col_47 == "WNL" ? "CHECKED" : "";	}
	function get_col_47_abn() {
		return $this->col_47 == "ABN" ? "CHECKED" : "";	}
	function get_col_47_na() {
		return $this->col_47 == "" ? "CHECKED" : "";	}
	function get_col_47_textbox() {
		return $this->col_47_textbox;
	}
	function set_col_47_textbox($data) {
		if(!empty($data)) {
			$this->col_47_textbox = $data;
		}
	}
	
	
	var $col_48;
	var $col_48_textbox;
	function get_col_48() {
		return $this->col_48;
	}
	function set_col_48($data) {
		if(!empty($data)) {
			$this->col_48 = $data;
		}
	}
	function get_col_48_wnl() {
		return $this->col_48 == "WNL" ? "CHECKED" : "";	}
	function get_col_48_abn() {
		return $this->col_48 == "ABN" ? "CHECKED" : "";	}
	function get_col_48_na() {
		return $this->col_48 == "" ? "CHECKED" : "";	}
	function get_col_48_textbox() {
		return $this->col_48_textbox;
	}
	function set_col_48_textbox($data) {
		if(!empty($data)) {
			$this->col_48_textbox = $data;
		}
	}
	
	
	var $col_49;
	var $col_49_textbox;
	function get_col_49() {
		return $this->col_49;
	}
	function set_col_49($data) {
		if(!empty($data)) {
			$this->col_49 = $data;
		}
	}
	function get_col_49_wnl() {
		return $this->col_49 == "WNL" ? "CHECKED" : "";	}
	function get_col_49_abn() {
		return $this->col_49 == "ABN" ? "CHECKED" : "";	}
	function get_col_49_na() {
		return $this->col_49 == "" ? "CHECKED" : "";	}
	function get_col_49_textbox() {
		return $this->col_49_textbox;
	}
	function set_col_49_textbox($data) {
		if(!empty($data)) {
			$this->col_49_textbox = $data;
		}
	}
	
	
	var $col_50;
	var $col_50_textbox;
	function get_col_50() {
		return $this->col_50;
	}
	function set_col_50($data) {
		if(!empty($data)) {
			$this->col_50 = $data;
		}
	}
	function get_col_50_wnl() {
		return $this->col_50 == "WNL" ? "CHECKED" : "";	}
	function get_col_50_abn() {
		return $this->col_50 == "ABN" ? "CHECKED" : "";	}
	function get_col_50_na() {
		return $this->col_50 == "" ? "CHECKED" : "";	}
	function get_col_50_textbox() {
		return $this->col_50_textbox;
	}
	function set_col_50_textbox($data) {
		if(!empty($data)) {
			$this->col_50_textbox = $data;
		}
	}
	
	
	var $col_51;
	var $col_51_textbox;
	function get_col_51() {
		return $this->col_51;
	}
	function set_col_51($data) {
		if(!empty($data)) {
			$this->col_51 = $data;
		}
	}
	function get_col_51_wnl() {
		return $this->col_51 == "WNL" ? "CHECKED" : "";	}
	function get_col_51_abn() {
		return $this->col_51 == "ABN" ? "CHECKED" : "";	}
	function get_col_51_na() {
		return $this->col_51 == "" ? "CHECKED" : "";	}
	function get_col_51_textbox() {
		return $this->col_51_textbox;
	}
	function set_col_51_textbox($data) {
		if(!empty($data)) {
			$this->col_51_textbox = $data;
		}
	}
	
	
	var $col_52;
	var $col_52_textbox;
	function get_col_52() {
		return $this->col_52;
	}
	function set_col_52($data) {
		if(!empty($data)) {
			$this->col_52 = $data;
		}
	}
	function get_col_52_wnl() {
		return $this->col_52 == "WNL" ? "CHECKED" : "";	}
	function get_col_52_abn() {
		return $this->col_52 == "ABN" ? "CHECKED" : "";	}
	function get_col_52_na() {
		return $this->col_52 == "" ? "CHECKED" : "";	}
	function get_col_52_textbox() {
		return $this->col_52_textbox;
	}
	function set_col_52_textbox($data) {
		if(!empty($data)) {
			$this->col_52_textbox = $data;
		}
	}
	
	// ----- Inspection of skin and subcutaneous tissue (eg, rashes, lesions, ulcers) -----

	var $col_53;
	var $col_53_textbox;
	function get_col_53() {
		return $this->col_53;
	}
	function set_col_53($data) {
		if(!empty($data)) {
			$this->col_53 = $data;
		}
	}
	function get_col_53_wnl() {
		return $this->col_53 == "WNL" ? "CHECKED" : "";	}
	function get_col_53_abn() {
		return $this->col_53 == "ABN" ? "CHECKED" : "";	}
	function get_col_53_na() {
		return $this->col_53 == "" ? "CHECKED" : "";	}
	function get_col_53_textbox() {
		return $this->col_53_textbox;
	}
	function set_col_53_textbox($data) {
		if(!empty($data)) {
			$this->col_53_textbox = $data;
		}
	}
	
	
	var $col_54;
	var $col_54_textbox;
	function get_col_54() {
		return $this->col_54;
	}
	function set_col_54($data) {
		if(!empty($data)) {
			$this->col_54 = $data;
		}
	}
	function get_col_54_wnl() {
		return $this->col_54 == "WNL" ? "CHECKED" : "";	}
	function get_col_54_abn() {
		return $this->col_54 == "ABN" ? "CHECKED" : "";	}
	function get_col_54_na() {
		return $this->col_54 == "" ? "CHECKED" : "";	}
	function get_col_54_textbox() {
		return $this->col_54_textbox;
	}
	function set_col_54_textbox($data) {
		if(!empty($data)) {
			$this->col_54_textbox = $data;
		}
	}
	
	// ----- Test cranial nerves with notation of any deficits -----

	var $col_55;
	var $col_55_textbox;
	function get_col_55() {
		return $this->col_55;
	}
	function set_col_55($data) {
		if(!empty($data)) {
			$this->col_55 = $data;
		}
	}
	function get_col_55_wnl() {
		return $this->col_55 == "WNL" ? "CHECKED" : "";	}
	function get_col_55_abn() {
		return $this->col_55 == "ABN" ? "CHECKED" : "";	}
	function get_col_55_na() {
		return $this->col_55 == "" ? "CHECKED" : "";	}
	function get_col_55_textbox() {
		return $this->col_55_textbox;
	}
	function set_col_55_textbox($data) {
		if(!empty($data)) {
			$this->col_55_textbox = $data;
		}
	}
	
	
	var $col_56;
	var $col_56_textbox;
	function get_col_56() {
		return $this->col_56;
	}
	function set_col_56($data) {
		if(!empty($data)) {
			$this->col_56 = $data;
		}
	}
	function get_col_56_wnl() {
		return $this->col_56 == "WNL" ? "CHECKED" : "";	}
	function get_col_56_abn() {
		return $this->col_56 == "ABN" ? "CHECKED" : "";	}
	function get_col_56_na() {
		return $this->col_56 == "" ? "CHECKED" : "";	}
	function get_col_56_textbox() {
		return $this->col_56_textbox;
	}
	function set_col_56_textbox($data) {
		if(!empty($data)) {
			$this->col_56_textbox = $data;
		}
	}
	
	
	var $col_57;
	var $col_57_textbox;
	function get_col_57() {
		return $this->col_57;
	}
	function set_col_57($data) {
		if(!empty($data)) {
			$this->col_57 = $data;
		}
	}
	function get_col_57_wnl() {
		return $this->col_57 == "WNL" ? "CHECKED" : "";	}
	function get_col_57_abn() {
		return $this->col_57 == "ABN" ? "CHECKED" : "";	}
	function get_col_57_na() {
		return $this->col_57 == "" ? "CHECKED" : "";	}
	function get_col_57_textbox() {
		return $this->col_57_textbox;
	}
	function set_col_57_textbox($data) {
		if(!empty($data)) {
			$this->col_57_textbox = $data;
		}
	}
	
	// ----- Description of patient’s judgment and insight -----

	var $col_58;
	var $col_58_textbox;
	function get_col_58() {
		return $this->col_58;
	}
	function set_col_58($data) {
		if(!empty($data)) {
			$this->col_58 = $data;
		}
	}
	function get_col_58_wnl() {
		return $this->col_58 == "WNL" ? "CHECKED" : "";	}
	function get_col_58_abn() {
		return $this->col_58 == "ABN" ? "CHECKED" : "";	}
	function get_col_58_na() {
		return $this->col_58 == "" ? "CHECKED" : "";	}
	function get_col_58_textbox() {
		return $this->col_58_textbox;
	}
	function set_col_58_textbox($data) {
		if(!empty($data)) {
			$this->col_58_textbox = $data;
		}
	}
	
	
	var $col_59;
	var $col_59_textbox;
	function get_col_59() {
		return $this->col_59;
	}
	function set_col_59($data) {
		if(!empty($data)) {
			$this->col_59 = $data;
		}
	}
	function get_col_59_wnl() {
		return $this->col_59 == "WNL" ? "CHECKED" : "";	}
	function get_col_59_abn() {
		return $this->col_59 == "ABN" ? "CHECKED" : "";	}
	function get_col_59_na() {
		return $this->col_59 == "" ? "CHECKED" : "";	}
	function get_col_59_textbox() {
		return $this->col_59_textbox;
	}
	function set_col_59_textbox($data) {
		if(!empty($data)) {
			$this->col_59_textbox = $data;
		}
	}
	
	
	var $col_60;
	var $col_60_textbox;
	function get_col_60() {
		return $this->col_60;
	}
	function set_col_60($data) {
		if(!empty($data)) {
			$this->col_60 = $data;
		}
	}
	function get_col_60_wnl() {
		return $this->col_60 == "WNL" ? "CHECKED" : "";	}
	function get_col_60_abn() {
		return $this->col_60 == "ABN" ? "CHECKED" : "";	}
	function get_col_60_na() {
		return $this->col_60 == "" ? "CHECKED" : "";	}
	function get_col_60_textbox() {
		return $this->col_60_textbox;
	}
	function set_col_60_textbox($data) {
		if(!empty($data)) {
			$this->col_60_textbox = $data;
		}
	}
	
	
	var $col_61;
	var $col_61_textbox;
	function get_col_61() {
		return $this->col_61;
	}
	function set_col_61($data) {
		if(!empty($data)) {
			$this->col_61 = $data;
		}
	}
	function get_col_61_wnl() {
		return $this->col_61 == "WNL" ? "CHECKED" : "";	}
	function get_col_61_abn() {
		return $this->col_61 == "ABN" ? "CHECKED" : "";	}
	function get_col_61_na() {
		return $this->col_61 == "" ? "CHECKED" : "";	}
	function get_col_61_textbox() {
		return $this->col_61_textbox;
	}
	function set_col_61_textbox($data) {
		if(!empty($data)) {
			$this->col_61_textbox = $data;
		}
	}
	
	
	var $col_62;
	var $col_62_textbox;
	function get_col_62() {
		return $this->col_62;
	}
	function set_col_62($data) {
		if(!empty($data)) {
			$this->col_62 = $data;
		}
	}
	function get_col_62_wnl() {
		return $this->col_62 == "WNL" ? "CHECKED" : "";	}
	function get_col_62_abn() {
		return $this->col_62 == "ABN" ? "CHECKED" : "";	}
	function get_col_62_na() {
		return $this->col_62 == "" ? "CHECKED" : "";	}
	function get_col_62_textbox() {
		return $this->col_62_textbox;
	}
	function set_col_62_textbox($data) {
		if(!empty($data)) {
			$this->col_62_textbox = $data;
		}
	}
						
}	// end of Form

?>
