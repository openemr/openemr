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
class FormVitals extends ORDataObject {

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
	var $bps;
	var $bpd;
	var $weight;
	var $height;
	var $temperature;
	var $temp_method;
	var $pulse;
	var $respiration;
	var $note;
	var $BMI;
	var $BMI_status;
	var $waist_circ;
	var $head_circ;
	var $oxygen_saturation;

	var $temp_methods;
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormVitals($id= "", $_prefix = "")	{
		if ($id > 0) {
			$this->id = $id;
			
		}
		else {
			$id = "";
			$this->date = $this->get_date();	
		}
		
		$this->_table = "form_vitals";
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		if ($id != "") {
			$this->populate();
			
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
		
	if(!$this->date){			
		$dbconn = $GLOBALS['adodb']['db'];
		$sql  = "SELECT date from form_encounter where encounter =" . $GLOBALS{'encounter'} ;
		$result = $dbconn->Execute($sql);
    	$this->date = $result->fields['date'];
	}
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
	function get_bps() {
		return $this->bps;
	}
	function set_bps($bps) {
		if(!empty($bps)){
			$this->bps = $bps;
		}
	}
	function get_bpd() {
		return $this->bpd;
	}
	function set_bpd($bpd) {
		if(!empty($bpd)){
			$this->bpd = $bpd;
		}
	}
	function get_weight() {
		return $this->weight;
	}
	function set_weight($w) {
		if(!empty($w) && is_numeric($w)){
			$this->weight = $w;
		}
	}
	function get_height() {
		return $this->height;
	}
	function set_height($h) {
		if(!empty($h) && is_numeric($h)){
			$this->height = $h;
		}
	}
	function get_temperature() {
		return $this->temperature;
	}
	function set_temperature($t) {
		if(!empty($t) && is_numeric($t)){
			$this->temperature = $t;
		}
	}
	function get_temp_method() {
		return $this->temp_method;
	}
	function set_temp_method($tm) {
		$this->temp_method = $tm;
	}
	function get_temp_methods() {
		return $this->temp_methods;
	}
	function get_pulse() {
		return $this->pulse;
	}
	function set_pulse($p) {
		if(!empty($p) && is_numeric($p)){
			$this->pulse = $p;
		}
	}
	function get_respiration() {
		return $this->respiration;
	}
	function set_respiration($r) {
		if(!empty($r) && is_numeric($r)){
			$this->respiration = $r;
		}
	}
	function get_note() {
		return $this->note;
	}
	function set_note($n) {
		if(!empty($n)){
			$this->note = $n;
		}
	}
	function get_BMI() {
		return $this->BMI;
	}
	function set_BMI($bmi) {
		if(!empty($bmi) && is_numeric($bmi)){
			$this->BMI = $bmi;
		}
	}
	function get_BMI_status() {
		return $this->BMI_status;
	}
	function set_BMI_status($status) {
		$this->BMI_status = $status;
	}
	function get_waist_circ() {
		return $this->waist_circ;
	}
	function set_waist_circ($w) {
		if(!empty($w) && is_numeric($w)){
			$this->waist_circ = $w;
		}
	}
	function get_head_circ() {
		return $this->head_circ;
	}
	function set_head_circ($h) {
		if(!empty($h) && is_numeric($h)){
			$this->head_circ = $h;
		}
	}
	function get_oxygen_saturation() {
		return $this->oxygen_saturation;
	}
	function set_oxygen_saturation($o) {
		if(!empty($o) && is_numeric($o)){
			$this->oxygen_saturation = $o;
		}
	}
	function persist() {
		parent::persist();
	}
}	// end of Form

?>
