<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");



/**
 * class Prosthesis
 *
 */
class FormProsthesis extends ORDataObject {

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
	var $therapist;
	var $involvement_left;
	var $involvement_right;
	var $involvement_bilateral;
	var $location;
	var $location_array = array("office" => "Office", "home" => "Home", "skilled_nurse_fac" => "Skilled Nurs. Fac.", "acute_hospital" => "Acute Hosp.", 
						"nursing_home" => "Nursing Home", "rehab_hospital" => "Rehab. Hosp.", "other" => "Other");
	var $diagnosis;
	var $hx;
	var $worn_le_past_five;
	var $model;
	var $size;
	var $new;
	var $replacement;
	var $foam_impressions;
	var $shoe_size;
	var $calf;
	var $ankle; 
	var $purpose;
	var $purpose_array  = array("pain_reduction" => "Pain Reduction", "offload_involved_area" => "Offload invloved Area", "immobilize" => "Immobilize", 
						"limit_motion" => "Limit Motion", "accomodation" => "Accomodation", "reduce_edema" => "Reduce Edema", 
						"facilitate_healing" => "Facilitate Healing", "other" => "Other");
	var $notes;
	var $goals_discussed;
	var $use_reviewed;
	var $wear_reviewed;
	var $worn_years;
	var $age_months;
	var $age_years;
	var $wear_hours;
	var $plan_to_order;
	var $plan_to_order_date;
	var $receiveded_product;
	var $received_product_date;
	var $given_instructions;
	var $patient_understands;	
	
	var $cpt_array = array( "L0500" => "L0500 LS corset", 			"L3010" => "L3010 Molded FO", 			"L3010" => "L3020 Molded FO + Met pad",
							"L3221" => "L3221 Men's depth shoes", 	"L3216" => "L3216 Women's depth shoes", "L3332" => "L3332 In-shoe .5\" heel lift",
							"L8100" => "L8100 BK comp hose (20-30mmHg)","L8110" => "L8110 BK comp hose (30-40mmHg)", "L8130" => "L8130 AK comp hose (20-30mmHg)",
							"L8140" => "L8140 AK comp hose (30-40mmHg)");
	
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormProsthesis($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		$this->_table = "form_prosthesis";
		$this->date = date("Y-m-d H:i:s");
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
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
	
	function set_therapist($string) {
		$this->therapist = $string;
	}
	
	function get_therapist() {
		return $this->therapist;	
	}
	
	function set_involvement_left($tf) {
		$this->involvement_left = $tf;
	}
	
	function get_involvement_left() {
		return $this->involvement_left;	
	}
	
	function set_involvement_right($tf) {
		$this->involvement_right = $tf;
	}
	
	function get_involvement_right() {
		return $this->involvement_right;	
	}
	
	function set_involvement_bilateral($tf) {
		$this->involvement_bilateral = $tf;
	}
	
	function get_involvement_bilateral() {
		return $this->involvement_bilateral;	
	}
	
	function set_location($string) {
		$this->location = $string;
	}
	
	function get_location() {
		return $this->location;	
	}
	function set_diagnosis($string) {
		$this->diagnosis = $string;
	}
	
	function get_diagnosis() {
		return $this->diagnosis;	
	}
	function set_hx($string) {
		$this->hx = $string;
	}
	
	function get_hx() {
		return $this->hx;	
	}
	
	function set_worn_le_past_five($tf) {
		$this->worn_le_past_five = $tf;
	}
	
	function get_worn_le_past_five() {
		return $this->worn_le_past_five;	
	}
	
	function set_model($string) {
		$this->model = $string;
	}
	
	function get_model() {
		return $this->model;	
	}
	
	function set_new($tf) {
		$this->new = $tf;
	}
	
	function get_new() {
		return $this->new;	
	}
	
	function set_size($string) {
		$this->size = $string;
	}
	
	function get_size() {
		return $this->size;	
	}
	
	function set_replacement($tf) {
		$this->replacement = $tf;
	}
	
	function get_replacement() {
		return $this->replacement;	
	}
	
	function set_foam_impressions($tf) {
		$this->foam_impressions = $tf;
	}
	
	function get_foam_impressions() {
		return $this->foam_impressions;	
	}
	
	function set_shoe_size($string) {
		$this->shoe_size = $string;
	}
	
	function get_shoe_size() {
		return $this->shoe_size;	
	}
	function set_calf($string) {
		$this->calf = $string;
	}
	
	function get_calf() {
		return $this->calf;	
	}
	function set_ankle($string) {
		$this->ankle = $string;
	}
	
	function get_ankle() {
		return $this->ankle;	
	}
	
	function set_purpose($string) {
		$this->purpose = $string;
	}
	
	function get_purpose() {
		return $this->purpose;	
	}
	function set_purpose_other($string) {
		$this->purpose_other = $string;
	}
	
	function get_purpose_other() {
		return $this->purpose_other;	
	}
	
	function set_notes($string) {
		$this->notes = $string;
	}
	
	function get_notes() {
		return $this->notes;	
	}
	function set_goals_discussed($tf) {
		$this->goals_discussed = $tf;
	}
	
	function get_goals_discussed() {
		return $this->goals_discussed;	
	}
	
	function set_use_reviewed($tf) {
		$this->use_reviewed = $tf;
	}
	
	function get_use_reviewed() {
		return $this->use_reviewed;	
	}
	
	function set_wear_reviewed($tf) {
		$this->wear_reviewed = $tf;
	}
	
	function get_wear_reviewed() {
		return $this->wear_reviewed;	
	}
		
	function get_date() {
		return $this->date;
	}
	
	function set_worn_years($string) {
		$this->worn_years = $string;
	}
	
	function get_worn_years() {
		return $this->worn_years;	
	}
	function set_age_months($string) {
		$this->age_months = $string;
	}
	
	function get_age_months() {
		return $this->age_months;	
	}
	function set_age_years($string) {
		$this->age_years = $string;
	}
	
	function get_age_years() {
		return $this->age_years;	
	}
	function set_wear_hours($string) {
		$this->wear_hours = $string;
	}
	
	function get_wear_hours() {
		return $this->wear_hours;	
	}
	
	function set_plan_to_order($tf) {
		$this->plan_to_order = $tf;
	}
	
	function get_plan_to_order() {
		return $this->plan_to_order;	
	}
	
	function set_plan_to_order_date($string) {
		$this->plan_to_order_date = $string;
	}
	
	function get_plan_to_order_date() {
		return $this->plan_to_order_date;	
	}
	
	function set_received_product($tf) {
		$this->received_product = $tf;
	}
	
	function get_received_product() {
		return $this->received_product;	
	}
	function set_received_product_date($string) {
		$this->received_product_date = $string;
	}
	
	function get_received_product_date() {
		return $this->received_product_date;	
	}
	
	function set_given_instructions($tf) {
		$this->given_instructions = $tf;
	}
	
	function get_given_instructions() {
		return $this->given_instructions;	
	}
	
	function set_patient_understands($tf) {
		$this->patient_understands = $tf;
	}
	
	function get_patient_understands() {
		return $this->patient_understands;	
	}
	
}	// end of Form

?>