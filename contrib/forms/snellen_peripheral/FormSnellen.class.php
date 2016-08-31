<?php
/**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 *
 *
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Aron Racho <aron@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

define("EVENT_VEHICLE",1);
define("EVENT_WORK_RELATED",2);
define("EVENT_SLIP_FALL",3);
define("EVENT_OTHER",4);


/**
 * class FormHpTjePrimary
 *
 */
class FormSnellen extends ORDataObject {

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
	var $left_1;
	var $left_2;
	var $right_1;
	var $right_2;
	var $both_1;
	var $both_2;
	var $peripheral_r1;
	var $peripheral_l1; 
	var $peripheral_r2;
	var $peripheral_l2;	
	var $colors="UNK";
	var $monocular="UNK";
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormSnellen($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");	
		}
		
		$this->_table = "form_snellen_peripheral";
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
	function get_options(){
		$ret = array("YES" => xl('YES'),"NO" => xl('NO'));
		return $ret;
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

	function set_left_1($tf) {
		$this->left_1 = $tf;
	}
	function get_left_1() {
		return $this->left_1;
	}

	function set_left_2($tf) {
		$this->left_2 = $tf;
	}
	function get_left_2() {
		return $this->left_2;
	}

	//
	function set_right_1($tf) {
		$this->right_1 = $tf;
	}
	function get_right_1() {
		return $this->right_1;
	}

	function set_right_2($tf) {
		$this->right_2 = $tf;
	}
	function get_right_2() {
		return $this->right_2;
	}
// both	
	function set_both_1($tf) {
		$this->both_1 = $tf;
	}
	function get_both_1() {
		return $this->both_1;
	}
	function set_both_2($tf) {
		$this->both_2 = $tf;
	}
	function get_both_2() {
		return $this->both_2;
	}
// peripheral
	function set_peripheral_r1($tf) {
		$this->peripheral_r1 = $tf;
	}
	function get_peripheral_r1() {
		return $this->peripheral_r1;
	}
	function set_peripheral_r2($tf) {
		$this->peripheral_r2 = $tf;
	}
	function get_peripheral_r2() {
		return $this->peripheral_r2;
	}
	
	function set_peripheral_l1($tf) {
		$this->peripheral_l1 = $tf;
	}
	function get_peripheral_l1() {
		return $this->peripheral_l1;
	}
	function set_peripheral_l2($tf) {
		$this->peripheral_l2 = $tf;
	}
	function get_peripheral_l2() {
		return $this->peripheral_l2;
	}	
// colors

	function set_colors($tf) {
		$this->colors = $tf;
	}
	function get_colors() {
		return $this->colors;
	}

// monocular
	function set_monocular($tf) {
		$this->monocular = $tf;
	}
	function get_monocular() {
		return $this->monocular;
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
