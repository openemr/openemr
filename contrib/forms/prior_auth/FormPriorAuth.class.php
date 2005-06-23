<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");



/**
 * class PriorAuth
 *
 */
class FormPriorAuth extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	
	/**
	 *
	 * @access private
	 */

	var $id;
	var $date;
	var $pid;
	var $activity;
	var $prior_auth_number;
	var $comments;
	
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormPriorAuth($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		$this->_table = "form_prior_auth";
		$this->date = date("Y-m-d H:i:s");
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		$this->prior_auth_number = "";
		if ($id != "") {
			$this->populate();
		}
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
	
	
	function set_comments($string) {
		$this->comments = $string;
	}
	
	function get_comments() {
		return $this->comments;	
	}
	
	function set_prior_auth_number($string) {
		$this->prior_auth_number = $string;
	}
	
	function get_prior_auth_number() {
		return $this->prior_auth_number;	
	}
	
	
	function get_date() {
		return $this->date;
	}
	

}	// end of Form

?>