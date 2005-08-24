<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

/**
 * class FormWellInfant
 *
 */
class FormWellInfant extends ORDataObject {

	/**
	 *
	 * @access private
	 */

	var $id;
	var $wt;
	var $ht;
	var $hcirc;
	var $t;
	var $years;
	var $months;
	var $wt_percentile;
	var $ht_percentile;
	var $hcirc_percentile;
	var $head_open_cm;
	var $history;
	var $formula_type;
	var $feeding_oz;
	var $feeding_24h;
	var $additional_findings;
	var $assesment;
	var $hct;
	var $lead;
	var $ppd;
	var $feeding;
	var $advice;
	var $rtc;
	
	var $checks;
	var $checks2;
	var $checks3;
	
	var $tanner_stage_array = array (" ", "I", "II", "III", "IV", "V");
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormWellInfant($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		
		$this->_table = "form_well_infant";
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
		
		$sql = "SELECT name from form_well_infant_checks where foreign_id = '" . mysql_real_escape_string($this->id) . "'";
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
	
	function get_hcirc() {
		return $this->hcirc;
	}
	
	function set_hcirc($string) {
		$this->hcirc = $string;
	}
	
	function get_head_open_cm() {
		return $this->head_open_cm;
	}
	
	function set_head_open_cm($string) {
		$this->head_open_cm = $string;
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
	
	function get_hcirc_percentile() {
		return $this->hcirc_percentile;
	}
	
	function set_hcirc_percentile($string) {
		$this->hcirc_percentile = $string;
	}
	
	function get_history() {
		return $this->history;
	}
	
	function set_history($string) {
		$this->history = $string;
	}
	
	function get_formula_type() {
		return $this->formula_type;
	}
	
	function set_formula_type($string) {
		$this->formula_type = $string;
	}
	
	function get_feeding_oz() {
		return $this->feeding_oz;
	}
	
	function set_feeding_oz($string) {
		$this->feeding_oz = $string;
	}
	
	function get_feeding_24h() {
		return $this->feeding_24h;
	}
	
	function set_feeding_24h($string) {
		$this->feeding_24h = $string;
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
	
	function get_feeding() {
		return $this->feeding;
	}
	
	function set_feeding($string) {
		$this->feeding = $string;
	}
	
	function get_advice() {
		return $this->advice;
	}
	
	function set_advice($string) {
		$this->advice = $string;
	}
	
	function get_rtc() {
		return $this->rtc;
	}
	
	function set_rtc($string) {
		$this->rtc = $string;
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
			$sql = "delete FROM form_well_infant_checks where foreign_id = '" . $this->id . "'";
			sqlQuery($sql);
			
			if (!empty($this->checks)) {
			
				foreach ($this->checks as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_infant_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
						sqlQuery($sql);
						//echo "$sql<br>";
					}
				}
			}
			
			if (!empty($this->checks2)) {
				foreach ($this->checks2 as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_infant_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
						sqlQuery($sql);
						//echo "$sql<br>";
					}
				}
			}
			
			if (!empty($this->checks3)) {
				foreach ($this->checks3 as $check) {
					if (!empty($check)) {
						$sql = "INSERT INTO form_well_infant_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
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
    	$at[1]['head_symmetrical_af'] 	=  "Symmetrical A.F.";
    	$a["Head"] = $at;
    	
    	$at = array();
    	$at[1]['eyes_conjunctiva_sclera_pupils_nl'] 	=  "Conjunctiva, Sclera, Pupils NL";
    	$at[1]['eyes_red_reflexes_present'] 	=  "Red Reflexes Present";
    	$at[1]['eyes_appears_to_see_no_strabismus'] 	=  "Appears to See, No Strabismus";
    	$a["Eyes"] = $at;
    	
    	$at = array();
    	$at[1]['ears_canals_clear_tms_normal'] 	=  "Canals Clear - TMS Normal";
    	$at[1]['ears_appears_to_hear'] 	=  "Appears to Hear";
    	$a["Ears"] = $at;
    	
    	$at = array();
    	$at[1]['nose_passages_patent'] 	=  "Passages Patent";
    	$a["Nose"] = $at; 
    	
    	$at = array();
    	$at[1]['mouth_pharynx_normal_color_without_lesions'] 	=  "Normal Color/Without Lesions";
    	$a['Mouth/Pharynx'] = $at;
    	
    	$at = array();
    	$at[1]['teeth_grossly_normal'] 	=  "Grossly Normal";
    	$a["Teeth"] = $at;
    	
    	$at = array();
    	$at[1]['neck_throat_supple_no_masses_palpated'] 	=  "Supple, No Masses, Palpated";
    	$a["Neck"] = $at;
    	   	
		$at = array();
    	$at[1]['heart_no_murmurs_regular_rhythm'] 	=  "No Murmurs, Regular Rhythm";
    	$a["Heart"] = $at;
		
		$at = array();
    	$at[1]['femoral_pulses_present'] 	=  "Present";
    	$a["Femoral Pulses"] = $at;
    	
		$at = array();
    	$at[1]['lungs_breath_sounds_normal_bilat'] 	=  "Breath Sounds Normal Bilat";
    	$a["Lungs"] = $at;
    	
    	$at = array();
    	$at[1]['abdomen_soft_no_masses_nl_liver_spleen'] 	=  "Soft. No Masses, NL Liver & Spleen";
    	$a["Abdomen"] = $at;
    	
    	$at = array();
    	$at["Male"]['genital_male_normal_appearance_circ_uncirc'] 	=  "Normal Appearance CIRC UNCIRC";
    	$at["Male"]['genital_male_testes_in_scrotum_rt_lt'] 	=  "Testes in Scrotum RT. LT.";
    	$at["Female"]['genital_female_no_lesions_nl_external_appearance'] = "No Lesions, NL External Appearance";
    	$a["Genital"] = $at;
    	    	   	
    	$at = array();
    	$at[1]['hips_good_abduction_no_clicks'] 	=  "Good Abduction, No Clicks";
    	$a["Hips"] = $at;
    	
    	$at = array();
    	$at[1]['extremeties_no_deformaties_full_rom'] 	=  "No Deformaties, Full ROM";
    	$a["Extremeties"] = $at;
    	
    	$at = array();
    	$at[1]['skin_clear_no_significant_lesions'] 	=  "Clear, No Significant Lesions";
    	$a["Skin"] = $at;
    	
    	$at = array();
    	$at[1]['nuerologic_alert_moves_extremeties_well'] 	=  "Alert, Moves Extremeties Well";
    	$a["Nuerologic"] = $at;
    	
    	$at = array();
    	$at[1]['feeding_breast'] 	=  "Breast";
    	$at[1]['feeding_normal_bowel_pattern'] 	=  "Normal Bowel Pattern";
    	$at[1]['feeding_normal_sleep_habits'] 	=  "Normal Sleep Habits";
    	$at[1]['feeding_vit_flouride'] 	=  "VIT./Flouride";
    	$a["Feeding"] = $at;
    	
    	$at = array();
    	$at[1]['advice_accident_poisoning_prev_car_safety'] 	=  "Accident/Poisoning Prev-Car Safety";
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
    	$at['Anticipatory Guidance']['1_month_auto_safety_seat'] 	=  "Auto Safety Seat";
    	$at['Anticipatory Guidance']['1_month_falls_ability_to_roll'] 	=  "Falls - ability to roll";
    	$at['Anticipatory Guidance']['1_month_safety_sheet_given'] 	=  "Safety sheet given";
    	
    	$at['Development']['1_month_hearing'] 	=  "Hearing";
    	$at['Development']['1_month_follows_to_midline'] 	=  "Follows to midline";
    	$at['Development']['1_month_lifts_head_when_prone'] 	=  "Lifts head when prone";
    	
    	$a["1 Month"] = $at;
    	
    	$at = array();
    	$at['Anticipatory Guidance']['2_month_auto_safety_seat'] 	=  "Auto Safety Seat";
		$at['Anticipatory Guidance']['2_month_burns'] 	=  "Burns (smoke detectors & hot water temp, 120 degrees F)";
    	$at['Anticipatory Guidance']['2_month_immunization_risks'] 	=  "Immunization Risks";
    	$at['Anticipatory Guidance']['2_month_falls'] 	=  "Falls";
    	
    	$at['Development']['2_month_social_responsive_smile'] 	=  "Social (responsive smile)";
    	$at['Development']['2_month_vocalizes'] 	=  "Vocalizes";
    	$at['Development']['2_month_head_raised_to_45_degrees_while_prone'] 	=  "Head raised to 45 degrees while prone";
    	$at['Development']['2_month_grasps'] 	=  "Grasps";
    	
    	$a["2 Months"] = $at;
    	
    	$at = array();
    	$at['Anticipatory Guidance']['4_month_auto_safety_seat'] 	=  "Auto Safety Seat";
		$at['Anticipatory Guidance']['4_month_choking'] 	=  "Choking - appropriate foods, small objects out of reach";
    	$at['Anticipatory Guidance']['4_month_burns_hot_liquids'] 	=  "Burns - hot liquids";
    	
    	$at['Development']['4_month_sits_with_head_steady'] 	=  "Sits with head steady";
    	$at['Development']['4_month_follows_180_degrees'] 	=  "Follows 180 degrees";
    	$at['Development']['4_months_orients_to_voices'] 	=  "Orients to voices";
    	$at['Development']['4_month_goos'] 	=  "Goos (ohh, ooh, ahh, ahh)";
    	$at['Development']['4_month_laughs'] 	=  "Laughs";
    	
    	$a["4 Months"] = $at; 
    	
    	$at = array();
    	$at['Anticipatory Guidance']['6_month_falls_stairs_gates'] 	=  "Falls - stais/gates, walkers, furniture";
    	$at['Anticipatory Guidance']['6_month_burns_hot_liquids'] 	=  "Burns - hot liquids, kitchen safety";
    	$at['Anticipatory Guidance']['6_month_poison'] 	=  "Poison - Poison Center Phone #, Ipecac, Drugs/household";
    	$at['Anticipatory Guidance']['6_month_safety_sheet_given'] 	=  "Safety sheet given";
    	
    	$at['Development']['6_month_orients_to_bell'] 	= "Orients to bell";
    	$at['Development']['6_month_rolls_over'] 	=  "Rolls Over";
    	$at['Development']['6_month_sits_briefly_leaning_forward'] 	=  "Sits briefly, leaning forward";
    	$at['Development']['6_month_reaches_for_objects'] 	=  "Reaches for objects";
    	$at['Development']['6_month_babbles'] 	=  "Babbles (repetitive strings of consonants, i.e. bababababa)";
    	
    	$a["6 Months"] = $at;
    	
    	$at = array();
    	$at['Anticipatory Guidance']['9_12_month_auto_safety_seat'] 	=  "Auto Safety Seat";
    	$at['Anticipatory Guidance']['9_12_month_poisoning'] 	=  "Poisoning";
    	$at['Anticipatory Guidance']['9_12_month_drowning_water_safety'] 	=  "Drowning/water safety";
    	$at['Anticipatory Guidance']['9_12_month_safety_sheet_given'] 	=  "Safety sheet given";
    	
    	$at['Development']['9_12_month_works_for_toys_out_of_reach'] 	= "Works for toys out of reach";
    	$at['Development']['9_12_month_peek_a_boo'] 	=  "Peek-a-Boo";
    	$at['Development']['9_12_month_sits_alone'] 	=  "Sits alone";
    	$at['Development']['9_12_month_pull_self_up'] 	=  "Pull self up";
    	$at['Development']['9_12_month_says_mama_dada_baba'] 	=  "Says mama/dada/baba (paired consonants)";
    	$at['Development']['9_12_month_looks_directly_at_ringing_bell'] 	=  "Looks directly at ringing bell";
    	
    	$a["9 to 12 Months"] = $at;
    	
    	$at = array();
    	$at['Anticipatory Guidance']['15_month_falls_climbing'] 	=  "Falls - climbing";
    	$at['Anticipatory Guidance']['15_month_burns_hot_objects_matches'] 	=  "Burns - hot objects, matches";
    	$at['Anticipatory Guidance']['15_month_street_safety'] 	=  "Street Safety";
    	$at['Anticipatory Guidance']['15_month_dental_care'] 	=  "Dental Care, nursing caries";
    	
    	$at['Development']['15_month_neat_pincer_grasp'] 	= "Neat pincer grasp";
    	$at['Development']['15_month_mama_dada_correct_specific'] 	=  "Mama or dada, correct/ specific";
    	$at['Development']['15_month_walks_alone_well'] 	=  "Walks alone, well";
    	$at['Development']['15_month_stroops_and_recovers'] 	=  "Stroops and recovers";
    	$at['Development']['15_month_indicates_wants'] = "Indicates wants";
    	$at['Development']['15_month_3_word_vocabulary'] 	=  "3 word vocabulary";
    	
    	$a["15 Months"] = $at;
    	
    	$at = array();
    	$at['Anticipatory Guidance']['18_2_month_auto_safety_seat'] 	=  "Auto Safety Seat";
    	$at['Anticipatory Guidance']['18_2_month_poisoning'] 	=  "Poisoning";
    	$at['Anticipatory Guidance']['18_2_month_water_safety_drowning'] 	=  "Water safety/drowning";
    	$at['Anticipatory Guidance']['18_2_month_dental_care'] 	=  "Dental Care, brushing, flouride";
    	$at['Anticipatory Guidance']['18_2_month_falls_play_equip_tricycles'] 	=  "Falls - play equip./tricycles";
    	$at['Anticipatory Guidance']['18_2_month_water_safety_drowning'] 	=  "Water safety/drowning";
    	$at['Anticipatory Guidance']['18_2_month_auto_predestrian'] 	=  "Auto - pedestrian";
    	$at['Anticipatory Guidance']['18_2_month_safety_sheet_given'] 	=  "Safety sheet given";
    	
    	$at['Development (18 months)']['18_2_seven_twenty_word_vocabulary'] 	= "7-20 words vocabulary";
    	$at['Development (18 months)']['18_2_month_walks_fast'] 	=  "Walks fast";
    	
    	$at['Development (24 months)']['18_2_month_uses_spoon_well'] 	=  "Uses spoon well";
    	$at['Development (24 months)']['18_2_month_helps in house'] 	=  "Helps in house";
    	$at['Development (24 months)']['18_2_month_identifies_one_body_part'] = "Identifies one body part";
    	$at['Development (24 months)']['18_2_month_combines_2_different_words'] = "Combines 2 different words";
    	$at['Development (24 months)']['18_2_month_kicks_a_ball'] = "Kicks a ball";
    	$at['Development (24 months)']['18_2_month_scribbles'] = "Scribbles";
    	$at['Development (24 months)']['18_2_month_tower_of_4_blocks'] = "Tower of 4 blocks";
    	$at['Development (24 months)']['18_2_month_throws_a_ball'] = "Throws a ball";
    	
    	$a["15 Months"] = $at;
    	        	    		
		return $a;	
	}
	

}	// end of Form
?>
