<?php

require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

/**
 * class FormAdultProgressNote
 *
 */
class FormAdultProgressNote extends ORDataObject {

	/**
	 *
	 * @access private
	 */

	var $id;
	var $med_allergies;
	var $wt;
	var $ht;
	var $t;
	var $current_meds;
	var $bp;
	var $hr;
	var $rr;
	var $cc1;
	var $cc2;
	var $hpi;
	var $assesment;
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function FormAdultProgressNote($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		
		$this->_table = "form_adult_progress_note";
		$this->date = date("Y-m-d H:i:s");
		$this->checks = array();
		$this->checks2 = array();
		$this->activity = 1;
		$this->pid = $GLOBALS['pid'];
		
		if ($id != "") {
			$this->populate();
		}
	}
	
	function populate() {
		parent::populate();
		
		$sql = "SELECT name from form_adult_progress_note_checks where foreign_id = '" . mysql_real_escape_string($this->id) . "'";
		$results = sqlQ($sql);

		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
			$this->checks[] = $row['name'];	
		}
		$this->checks2 = $this->checks;
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

	function set_med_allergies($string) {
		$this->med_allergies = $string;
	}
	
	function get_med_allergies() {
		return $this->med_allergies;
	}
	
	function set_wt($string) {
		$this->wt = $string;
	}
	
	function get_wt() {
		return $this->wt;
	}
	
	function set_ht($string) {
		$this->ht = $string;
	}
	
	function get_ht() {
		return $this->ht;
	}
	
	function set_t($string) {
		$this->t = $string;
	}
	
	function get_t() {
		return $this->t;
	}
	
	function set_current_meds($string) {
		$this->current_meds = $string;
	}
	
	function get_current_meds() {
		return $this->current_meds;
	}
	
	function set_bp($string) {
		$this->bp = $string;
	}
	
	function get_bp() {
		return $this->bp;
	}
	
	function set_hr($string) {
		$this->hr = $string;
	}
	
	function get_hr() {
		return $this->hr;
	}
	
	function set_rr($string) {
		$this->rr = $string;
	}
	
	function get_rr() {
		return $this->rr;
	}
	
	function set_cc1($string) {
		$this->cc1 = $string;
	}
	
	function get_cc1() {
		return $this->cc1;
	}
		
	function set_cc2($string) {
		$this->cc2 = $string;
	}
	
	function get_cc2() {
		return $this->cc2;
	}
	
	function set_hpi($string) {
		$this->hpi = $string;
	}
	
	function get_hpi() {
		return $this->hpi;
	}
	
	function set_assesment($string) {
		$this->assesment = $string;
	}
	
	function get_assesment() {
		return $this->assesment;	
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
	
	function persist() {
		
		parent::persist();
		if (is_numeric($this->id) and !empty($this->checks)) {
			$sql = "delete FROM form_adult_progress_note_checks where foreign_id = '" . $this->id . "'";
			sqlQuery($sql);
			foreach ($this->checks as $check) {
				if (!empty($check)) {
					$sql = "INSERT INTO form_adult_progress_note_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
					sqlQuery($sql);
					//echo "$sql<br>";
				}
			}
			foreach ($this->checks2 as $check) {
				if (!empty($check)) {
					$sql = "INSERT INTO form_adult_progress_note_checks set foreign_id='"  . mysql_real_escape_string($this->id) . "', name = '" . mysql_real_escape_string($check) . "'";
					sqlQuery($sql);
					//echo "$sql<br>";
				}
			}
		}		
		
	}
	
	function _form_layout() {
		$a = array();
		
		//at is array temp
		//a is array
		//a_bottom is the textually identified rows of a checkbox group
		
		//$at["General"]['general_not_examined'] 	=  "Not Examined";
		$at["General"]['general_wnl'] 	=  "WNL";
		$at["General"]['general_abnormal'] 	=  "Abnormal";
		
		//$at["HEENT"]['heent_not_examined'] 	=  "Not Examined";
		$at["HEENT"]['heent_wnl'] 	=  "WNL";
		$at["HEENT"]['heent_abnormal'] 	=  "Abnormal";
		
		//$at["Heart"]['heart_not_examined'] 	=  "Not Examined";
		$at["Heart"]['heart_wnl'] 	=  "WNL";
		$at["Heart"]['heart_abnormal'] 	=  "Abnormal";
		
		//$at["Lungs"]['lungs_not_examined'] 	=  "Not Examined";
		$at["Lungs"]['lungs_wnl'] 	=  "WNL";
		$at["Lungs"]['lungs_abnormal'] 	=  "Abnormal";
		 	
		//$at["Back"]['back_not_examined'] 	=  "Not Examined";
		$at["Back"]['back_wnl'] 	=  "WNL";
		$at["Back"]['back_abnormal'] 	=  "Abnormal";
		 	
		//$at["Breast"]['breast_not_examined'] 	=  "Not Examined";
		$at["Breast"]['breast_wnl'] 	=  "WNL";
		$at["Breast"]['breast_abnormal'] 	=  "Abnormal";
		
		//$at["ABD"]['abd_not_examined'] 	=  "Not Examined";
		$at["ABD"]['abd_wnl'] 	=  "WNL";
		$at["ABD"]['abd_abnormal'] 	=  "Abnormal";

		//$at["Genital/URO"]['genital_uro_not_examined'] 	=  "Not Examined";
		$at["Genital/URO"]['genital_uro_wnl'] 	=  "WNL";
		$at["Genital/URO"]['genital_uro_abnormal'] 	=  "Abnormal";
    	
		//$at["Rectal"]['rectal_not_examined'] 	=  "Not Examined";
		$at["Rectal"]['rectal_wnl'] 	=  "WNL";
		$at["Rectal"]['rectal_abnormal'] 	=  "Abnormal";
		
		//$at["Extremeties"]['extremeties_not_examined'] 	=  "Not Examined";
		$at["Extremeties"]['extremeties_wnl'] 	=  "WNL";
		$at["Extremeties"]['extremeties_abnormal'] 	=  "Abnormal";
    	
    	//$at["Skin"]['skin_not_examined'] 	=  "Not Examined";
		$at["Skin"]['skin_wnl'] 	=  "WNL";
		$at["Skin"]['skin_abnormal'] 	=  "Abnormal";
    	
    	//$at["Neuro"]['neuro_not_examined'] 	=  "Not Examined";
		$at["Neuro"]['neuro_wnl'] 	=  "WNL";
		$at["Neuro"]['neuro_abnormal'] 	=  "Abnormal";
    	    	 	
    	$a['Physical Exam'] = $at; 
    	    	
		return $a;	
	}
	
	function _form_layout2() {
		$a = array();
		
		//at is array temp
		//a is array
		//a_bottom is the textually identified rows of a checkbox group
		
		$at[1]['education_discussed'] 	=  "Patient Education Discussed";
		
		$at[2]['education_obesity'] 	=  "Obesity";
		$at[2]['education_diabetes'] 	=  "Diabetes";
		$at[2]['education_family_planning'] 	=  "Family Planning";
		
		$at[3]['education_diet'] 	=  "Diet";
		$at[3]['education_std_s'] 	=  "STD's";
		$at[3]['education_prenatal_care'] 	=  "Prenatal Care";
		
		$at[4]['education_exercise'] 	=  "Exercise";
		$at[4]['education_pid_s'] 	=  "PID's";
		$at[4]['education_self_breast_exam'] 	=  "Self Breast Exam";
		
		$at[5]['education_smoking'] 	=  "Smoking";
		$at[5]['education_htn'] 	=  "HTN";
		$at[5]['education_immunization'] 	=  "Immunization";
		
		$at[6]['education_cholesterol'] 	=  "Cholesterol";
		$at[6]['education_meds'] 	=  "Meds";
		$at[6]['education_pediatric_topics'] 	=  "Pediatric Topics";
    	 	
    	$a['Patient Education'] = $at; 
    	    	
    	    		
		return $a;	
	}
	

}	// end of Form
?>