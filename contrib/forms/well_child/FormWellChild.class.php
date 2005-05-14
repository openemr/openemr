<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

/**
 * class FormWellChild
 *
 */
class FormWellChild extends ORDataObject {

	/**
	 *
	 * @access private
	 */

	var $id;
	var $wt;
	var $ht;
	var $bp;
	var $t;
	var $years;
	var $months;
	var $wt_percentile;
	var $ht_percentile;
	var $history;
	var $drug_allergy;
	var $school_development;
	var $additional_findings;
	var $assesment;
	var $ua_dip;
	var $hct;
	var $lead;
	var $ppd;
	var $rx;
	var $rtc;
	
	var $ou_corrected;
	var $ou_uncorrected;
	var $od_corrected;
	var $od_uncorrected;
	var $os_corrected;
	var $os_uncorrected;
	var $right_ear_1000;
	var $right_ear_2000;
	var $right_ear_4000;
	var $left_ear_1000;
	var $left_ear_2000;
	var $left_ear_4000;
	
	var $checks;
	var $checks2;
	var $checks3;
	
	var $tanner_stage_array = array (" ", "I", "II", "III", "IV", "V");
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormWellChild($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		
		$this->_table = "form_well_child";
		$this->date = date("Y-m-d H:i:s");
		$this->checks = array();
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		
		if ($id != "") {
			$this->populate();
		}
	}
	
	function populate() {
		parent::populate();
		
		$sql = "SELECT name from form_well_child_checks where foreign_id = '" . mysql_real_escape_string($this->id) . "'";
		$results = sqlQ($sql);

		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
			$this->checks[] = $row['name'];	
		}
		$this->checks2 = $this->checks;
		$this->checks3 = $this->checks;
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
	
	function set_date($string) {
		$this->date = $string;
	}

	function get_wt() {
		return $this->wt;
	}
	
	function set_wt($string) {
		$this->wt = $string;
	}
	
	function get_ht() {
		return $this->ht;
	}
	
	function set_ht($string) {
		$this->ht = $string;
	}
	
	function get_bp() {
		return $this->bp;
	}
	
	function set_bp($string) {
		$this->bp = $string;
	}
	
	function get_t() {
		return $this->t;
	}
	
	function set_t($string) {
		$this->t = $string;
	}
	
	function get_years() {
		return $this->years;
	}
	
	function set_years($string) {
		$this->years = $string;
	}
	
	function get_months() {
		return $this->months;
	}
	
	function set_months($string) {
		$this->months = $string;
	}
	
	function get_wt_percentile() {
		return $this->wt_percentile;
	}
	
	function set_wt_percentile($string) {
		$this->wt_percentile = $string;
	}
	
	function get_ht_percentile() {
		return $this->ht_percentile;
	}
	
	function set_ht_percentile($string) {
		$this->ht_percentile = $string;
	}
	
	function get_history() {
		return $this->history;
	}
	
	function set_history($string) {
		$this->history = $string;
	}
	
	function get_breast_tanner() {
		return $this->breast_tanner;
	}
	
	function set_breast_tanner($string) {
		$this->breast_tanner = $string;
	}
	
	function get_male_tanner() {
		return $this->male_tanner;
	}
	
	function set_male_tanner($string) {
		$this->male_tanner = $string;
	}
	
	function get_female_tanner() {
		return $this->female_tanner;
	}
	
	function set_female_tanner($string) {
		$this->female_tanner = $string;
	}
	
	function get_drug_allergy() {
		return $this->drug_allergy;
	}
	
	function set_drug_allergy($string) {
		$this->drug_allergy = $string;
	}
	
	function get_school_development() {
		return $this->school_development;
	}
	
	function set_school_development($string) {
		$this->school_development = $string;
	}
	
	function get_additional_findings() {
		return $this->additional_findings;
	}
	
	function set_additional_findings($string) {
		$this->additional_findings = $string;
	}
	
	function set_assesment($string) {
		$this->assesment = $string;
	}
	
	function get_assesment() {
		return $this->assesment;	
	}
	
	function get_ua_dip() {
		return $this->ua_dip;
	}
	
	function set_ua_dip($string) {
		$this->ua_dip = $string;
	}
	
	function get_hct() {
		return $this->hct;
	}
	
	function set_hct($string) {
		$this->hct = $string;
	}
	
	function get_lead() {
		return $this->lead;
	}
	
	function set_lead($string) {
		$this->lead = $string;
	}
	
	function get_ppd() {
		return $this->ppd;
	}
	
	function set_ppd($string) {
		$this->ppd = $string;
	}
	
	function get_rx() {
		return $this->rx;
	}
	
	function set_rx($string) {
		$this->rx = $string;
	}
	
	function get_rtc() {
		return $this->rtc;
	}
	
	function set_rtc($string) {
		$this->rtc = $string;
	}
	
	function get_ou_corrected() {
		return $this->ou_corrected;
	}
	
	function set_ou_corrected($string) {
		$this->ou_corrected = $string;
	}
	
	function get_ou_uncorrected() {
		return $this->ou_uncorrected;
	}
	
	function set_ou_uncorrected($string) {
		$this->ou_uncorrected = $string;
	}
	
	function get_od_corrected() {
		return $this->od_corrected;
	}
	
	function set_od_corrected($string) {
		$this->od_corrected = $string;
	}
	
	function get_od_uncorrected() {
		return $this->od_uncorrected;
	}
	
	function set_od_uncorrected($string) {
		$this->od_uncorrected = $string;
	}
	
	function get_os_corrected() {
		return $this->os_corrected;
	}
	
	function set_os_corrected($string) {
		$this->os_corrected = $string;
	}
	
	function get_os_uncorrected() {
		return $this->os_uncorrected;
	}
	
	function set_os_uncorrected($string) {
		$this->os_uncorrected = $string;
	}
	
	function get_right_ear_1000() {
		return $this->right_ear_1000;
	}
	
	function set_right_ear_1000($string) {
		$this->right_ear_1000 = $string;
	}
	
	function get_right_ear_2000() {
		return $this->right_ear_2000;
	}
	
	function set_right_ear_2000($string) {
		$this->right_ear_2000 = $string;
	}
	
	function get_right_ear_4000() {
		return $this->right_ear_4000;
	}
	
	function set_right_ear_4000($string) {
		$this->right_ear_4000 = $string;
	}
	
	function get_left_ear_1000() {
		return $this->left_ear_1000;
	}
	
	function set_left_ear_1000($string) {
		$this->left_ear_1000 = $string;
	}
	
	function get_left_ear_2000() {
		return $this->left_ear_2000;
	}
	
	function set_left_ear_2000($string) {
		$this->left_ear_2000 = $string;
	}
	
	function get_left_ear_4000() {
		return $this->left_ear_4000;
	}
	
	function set_left_ear_4000($string) {
		$this->left_ear_4000 = $string;
	}
	
	function get_checks() {
		return $this->checks;
	}
	
	function set_checks($check_array) {
		$this->checks = $check_array;
	}
	
	function get_checks2() {
		return $this->checks2;
	}
	
	function set_checks2($check_array) {
		$this->checks2 = $check_array;
	}
	
	function get_checks3() {
		return $this->checks3;
	}
	
	function set_checks3($check_array) {
		$this->checks3 = $check_array;
	}
	
	function persist() {
		
		parent::persist();
		if (is_numeric($this->id) and (!empty($this->checks) || !empty($this->checks2) || !empty($this->checks3))) {
			$sql = "delete FROM form_well_child_checks where foreign_id = '" . $this->id . "'";
			sqlQuery($sql);
			
			if (!empty($this->checks2)) {
			
				foreach ($this->checks as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_child_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
						sqlQuery($sql);
						//echo "$sql<br>";
					}
				}
			}
			
			if (!empty($this->checks2)) {
				foreach ($this->checks2 as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_child_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
						sqlQuery($sql);
						//echo "$sql<br>";
					}
				}
			}
			
			if (!empty($this->checks3)) {
				foreach ($this->checks3 as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_child_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
						sqlQuery($sql);
						//echo "$sql<br>";
					}
				}
			}
		}		
		
	}
	
	function _form_layout() {
		$a = array();
		
		//at is array temp
		//a is array
		//a_bottom is the textually identified rows of a checkbox group
		$at = array();
		$at[1]['appearance_well_nourished_and_developed'] 	=  "Well Nourished and Developed";
    	$a['Gen. Appearance'] = $at;
    	
    	$at = array();
    	$at[1]['head_no_lesions'] 	=  "No Lesions";
    	$a["Head"] = $at;
    	
    	$at = array();
    	$at[1]['eyes_perl_conjunctiva_sclera_clear'] 	=  "Perl, Conjunctiva & Sclera Clear";
    	$at[1]['eyes_vision_grossly_normal'] 	=  "Vision Grossly Normal";
    	$a["Eyes"] = $at;
    	
    	$at = array();
    	$at[1]['ears_ext_canals_clear_tms_normal'] 	=  "EXT. Canals Clear - TMS Normal";
    	$at[1]['ears_hearing_grossly_normal'] 	=  "Hearing Grossly Normal";
    	$a["Ears"] = $at;
    	
    	$at = array();
    	$at[1]['nose_passages_patent'] 	=  "Passages Patent";
    	$a["Nose"] = $at; 
    	
    	$at = array();
    	$at[1]['mouth_throat_passages_clear_mm_pink_no_lesions'] 	=  "Passages Clear, MM Pink. No Lesions";
    	$a['Mouth/Throat'] = $at;
    	
    	$at = array();
    	$at[1]['teeth_grossly_normal'] 	=  "Grossly Normal";
    	$a["Teeth"] = $at;
    	
    	$at = array();
    	$at[1]['neck_throat_supple_no_masses_thyroid_normal'] 	=  "Supple, No Masses, Thyroid Normal";
    	$a["Neck"] = $at;
    	
    	$at = array();
    	$at[1]['chest_symmetrical'] 	=  "Symmetrical";
    	$a["Chest"] = $at;
    	
    	$at = array();
    	$at[1]['breast_no_masses'] 	=  "No Masses";
    	$a["Breast:(FEMALE)"] = $at;
    	 	
    	$at = array();
    	$at[1]['lungs_clear_to_auscultation_bilat'] 	=  "Clear to Auscultation Bilat";
    	$a["Lungs"] = $at;
    	
    	$at = array();
    	$at[1]['heart_reg_rhythm_no_organic_murmurs'] 	=  "Reg. Rhythm, No Organic Murmurs";
    	$a["Heart"] = $at;
    	
    	$at = array();
    	$at[1]['abdomen_soft_no_masses_liver_spleen_normal'] 	=  "Soft. No Masses, Liver & Spleen NL";
    	$a["Abdomen"] = $at;
    	
    	$at = array();
    	$at[1]['femoral_pulses_normal'] 	=  "Normal";
    	$a["Femoral Pulses"] = $at;
    	
    	$at = array();
    	$at[1]['genitalia_grossly_normal'] 	=  "Grossly Normal";
    	$at["Male"]['genitalia_male_circ_uncirc_testes_scrotum_rt_lt'] 	=  "CIRC UNCIRC Testes in Scrotum RT LT";
    	$at["Female"] = null;
    	$a["Genitalia"] = $at;
    	
    	$at = array();
    	$at[1]['extremeties_full_rom_no_deformaties_lesions'] 	=  "Full ROM, No Deformaties or Lesions";
    	$a["Extremeties"] = $at;
    	
    	$at = array();
    	$at[1]['lymph_not_enlarged'] 	=  "Not Enlarged";
    	$a["Lymph Nodes"] = $at;
    	
    	$at = array();
    	$at[1]['back_no_scoliosis'] 	=  "No Scoliosis";
    	$a["Back"] = $at;
    	
    	$at = array();
    	$at[1]['skin_clear_no_significant_lesions'] 	=  "Clear, No Significant Lesions";
    	$a["Skin"] = $at;
    	
    	$at = array();
    	$at[1]['nuerologic_alert_no_gross_sens_motor_deficit'] 	=  "Alert, No Gross Ses/Motor Deficit";
    	$a["Nuerologic"] = $at;
    	
    	$at = array();
    	$at[1]['advice_nutrition'] 	=  "Nutrition";
    	$at[1]['advice_dental_care'] 	=  "Dental Care";
    	$at[1]['advice_accident_prev_auto_safety'] 	=  "Accident Prev./Auto Safety";
    	$at[1]['advice_sexuality_birth_control'] 	=  "Sexuality/Birth Control";
    	$a["Advice"] = $at;
    	    		
    	    		
		return $a;	
	}
	
	function _form_layout2() {
		$a = array();
		
		//at is array temp
		//a is array
		//a_bottom is the textually identified rows of a checkbox group
		
		$at = array();
    	$at[1]['dpt_1'] 	=  "1";
    	$at[1]['dpt_2'] 	=  "2";
    	$at[1]['dpt_3'] 	=  "3";
    	$at[1]['dpt_4'] 	=  "4";
    	$at[1]['dpt_5'] 	=  "5";
    	$a["DPT"] = $at; 
    	
    	$at = array();
    	$at[1]['dt_1'] 	=  "1";
    	$at[1]['dt_2'] 	=  "2";
    	$at[1]['dt_3'] 	=  "3";
    	$at[1]['dt_4'] 	=  "4";
    	$at[1]['dt_5'] 	=  "5";
    	$a["DT"] = $at;
    	    	
    	$at = array();
    	$at[1]['opv_1'] 	=  "1";
    	$at[1]['opv_2'] 	=  "2";
    	$at[1]['opv_3'] 	=  "3";
    	$at[1]['opv_4'] 	=  "4";
    	$at[1]['opv_5'] 	=  "5";
    	$a["OPV"] = $at;
    	
    	$at = array();
    	$at[1]['hepb_1'] 	=  "1";
    	$at[1]['hepb_2'] 	=  "2";
    	$at[1]['hepb_3'] 	=  "3";
    	$a["HEP. B"] = $at;    		
    	
    	$at = array();
    	$at[1]['hib_1'] 	=  "1";
    	$at[1]['hib_2'] 	=  "2";
    	$at[1]['hib_3'] 	=  "3";
    	$at[1]['hib_4'] 	=  "4";
    	$at[1]['hib_5'] 	=  "5";
    	$a["HIB"] = $at;
    	
    	$at = array();
    	$at[1]['mmr_1'] 	=  "1";
    	$at[1]['mmr_2'] 	=  "2";
    	$a["MMR"] = $at;    		
    	
    	$at = array();
    	$at[1]['td_1'] 	=  "1";
    	$at[1]['td_2'] 	=  "2";
    	$a["Td"] = $at;    		
    	    		
		return $a;	
	}
	

	function _form_layout3() {
		$a = array();
		
		//at is array temp
		//a is array
		//a_bottom is the textually identified rows of a checkbox group
		
		$at = array();
    	$at[1]['3_4_years_car_safety'] 	=  "Car Safety";
    	$at[1]['3_4_years_truck_safety'] 	=  "Truck Safety";
    	$at[1]['3_4_years_ipecac'] 	=  "Ipecac";
    	$at[1]['3_4_years_poison_control_number'] 	=  "Poison Control Number";
    	$at[1]['3_4_years_swimming_pool_safety'] 	=  "Swimming Pool Safety";
    	$at[1]['3_4_years_pediatric_cpr'] 	=  "Pediatric CPR";
    	$a["3 To 4 Years"] = $at; 
    	
    	$at = array();
    	$at[1]['5_10_years_car_safety'] 	=  "Car Safety";
    	$at[1]['5_10_years_truck_safety'] 	=  "Truck Safety";
    	$at[1]['5_10_years_swimming_pool_safety'] 	=  "Swimming Pool Safety";
    	$at[1]['5_10_years_pediatric_cpr'] 	=  "Pediatric CPR";
    	$at[1]['5_10_years_bike_skateboard_safety'] 	=  "Bike / Skateboard Safety";
    	$at[1]['5_10_years_stranger_safety'] 	=  "Stranger Safety";
    	$at[1]['5_10_years_substance_abuse'] 	=  "Substance Abuse";
    	
    	$a["5 To 10 Years"] = $at;
    	
    	$at = array();
    	$at[1]['10_15_years_car_safety'] 	=  "Car Safety";
    	$at[1]['10_15_years_truck_safety'] 	=  "Truck Safety";
    	$at[1]['10_15_years_substance_abuse'] 	=  "Substance Abuse";
    	$at[1]['10_15_years_safe_sex'] 	=  "Safe Sex";
    	$at[1]['10_15_years_bike_skateboard_safety'] 	=  "Bike / Skateboard Safety";
    	$at[1]['10_15_years_swimming_pool_safety'] 	=  "Swimming Pool Safety";
    	$at[1]['10_15_years_cpr'] 	=  "CPR";
    	$a["10 To 15 Years"] = $at;
    	
    	$at = array();
    	$at[1]['15_20_years_car_safety'] 	=  "Car Safety";
    	$at[1]['15_20_years_truck_safety'] 	=  "Truck Safety";
    	$at[1]['15_20_years_substance_abuse'] 	=  "Substance Abuse";
    	$at[1]['15_20_years_safe_sex'] 	=  "Safe Sex";
    	$at[1]['15_20_years_cpr'] 	=  "CPR";
    	$a["15 To 20 Years"] = $at;
        	    		
		return $a;	
	}
	

}	// end of Form
?>