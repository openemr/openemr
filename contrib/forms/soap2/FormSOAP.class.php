<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

define("EVENT_VEHICLE",1);
define("EVENT_WORK_RELATED",2);
define("EVENT_SLIP_FALL",3);
define("EVENT_OTHER",4);


/**
 * class FormHpTjePrimary
 *
 */
class FormSOAP extends ORDataObject {

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
	var $subjective;
	//var $objective;
	var $assessment;
	var $general;
	var $heent;
	var $neck;
	var $cardio;
	var $respiratory;
	var $breasts;
	var $abdomen;
	var $gastro;
	var $extremities;
	var $skin;
	var $neurological;
	var $mentalstatus;
	var $plan;
	 
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormSOAP($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");	
		}
		
		$this->_table = "form_soap2";
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
	function get_subjective() {
		return $this->subjective;
	}
	function set_subjective($data) {
		if(!empty($data)){
			$this->subjective = $data;
		}
	}
	function get_objective() {
		return $this->objective;
	}
	function set_objective($data) {
		if(!empty($data)){
			$this->objective = $data;
		}
	}
	
	function get_assessment() {
		return $this->assessment;
	}
	function set_assessment($data) {
		if(!empty($data)){
			$this->assessment = $data;
		}
	}
		
	
	
	/*  The following code replaces assessment.  It is
	  part of the SOAP form Dr J has requested.
	*/
	
	
	// **** General *****
	function get_general() {
	   return $this->general;
	}
	function set_general($data) {
	   if(!empty($data)) {
	      $this->general = $data;
	   }
	}
	
	// **** HEENT  ******
	function get_heent() {
	   return $this->heent;
	}
	function set_heent($data) {
	   if(!empty($data)) {
	     $this->heent = $data;
	   }
	}
	
	// **** Neck *****
	function get_neck() {
	   return $this->neck;
	}
	function set_neck($data) {
	   if(!empty($data)) {
	     $this->neck = $data;
	   }
	}
	
	// **** CV *****
	function get_cardio() {
	   return $this->cardio;
	}
	function set_cardio($data) {
	   if(!empty($data)) {
	     $this->cardio = $data;
	   }
	}
	
	// **** Lungs *****
	function get_respiratory() {
	   return $this->respiratory;
	}
	function set_respiratory($data) {
	   if(!empty($data)) {
	     $this->respiratory = $data;
	   }
	}
	
	// **** Breasts *****
	// * my personal favorite :>  ***
	function get_breasts() {
	   return $this->breasts;
	}
	function set_breasts($data) {
	   if(!empty($data)) {
	     $this->breasts = $data;
	   }
	}
	
	// **** Abdomen *****
	function get_abdomen() {
	   return $this->abdomen;
	}
	function set_abdomen($data) {
	   if(!empty($data)) {
	     $this->abdomen = $data;
	   }
	}
	
	// **** GU *****
	function get_gastro() {
	   return $this->gastro;
	}
	function set_gastro($data) {
	   if(!empty($data)) {
	     $this->gastro = $data;
	   }
	}
	
	// **** Bones/Joints/Extremities *****
	function get_extremities() {
	   return $this->extremities;
	}
	function set_extremities($data) {
	   if(!empty($data)) {
	     $this->extremities = $data;
	   }
	}
	
	// **** Skin *****
	function get_skin() {
	   return $this->skin;
	}
	function set_skin($data) {
	   if(!empty($data)) {
	     $this->skin = $data;
	   }
	}
	
	// **** Neuro/Psych *****
	function get_neurological() {
	   return $this->neurological;
	}
	function set_neurological($data) {
	   if(!empty($data)) {
	     $this->neurological = $data;
	   }
	}
	
	// **** Mental Status *****
	function get_mentalstatus() {
	   return $this->mentalstatus;
	}
	function set_mentalstatus($data) {
	   if(!empty($data)) {
	     $this->mentalstatus = $data;
	   }
	}
	
	function get_plan() {
		return $this->plan;
	}
	function set_plan($data) {
		if(!empty($data)){
			$this->plan = $data;
		}
	}
	
	function persist() {
		parent::persist();
	}
}	// end of Form

?>
