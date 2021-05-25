<?php

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE",1);
define("EVENT_WORK_RELATED",2);
define("EVENT_SLIP_FALL",3);
define("EVENT_OTHER",4);


/**
 * class FormHpTjePrimary
 *
 */
class FormAssessmentForm extends ORDataObject {

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
	var $chief_complaint;
	var $history_of_present_illnes;
	var $review_of_systems;
	var $past_medical_history;
	var $social_history;
	var $social_history_pks_day;
	var $social_history_yrs_smkd;
	var $social_history_desc;
	var $family_history_desc;
	var $family_history;
	var $allergies;
	var $current_medications;
	var $vital_weight;
	var $vital_wt;
	var $vital_height;
	var $vital_ht;
	var $vital_temp;
	var $vital_tp;
	var $vital_bp1;
	var $vital_bp2;
	var $vital_pulse;
	var $vital_rr;
	var $vital_bmi;
	var $vital_sat;
	var $vital_on02;
	var $physical_exam_desc;
	var $diagnosis_desc;
	var $assessment;
	var $plan;
	var $surgical_procedure;
	var $in_clinic_tests;
	var $laborders;
	var $feecodes;
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	function __construct($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";
			$this->date = date("Y-m-d H:i:s");
		}

		$this->_table = "form_assessment_form";
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

	function get_chief_complaint(){
		return $this->chief_complaint;
	}
	function set_chief_complaint($data){
		if(!empty($data)){
			$this->chief_complaint = $data;
		}
	}

	function get_history_of_present_illnes(){
		return $this->history_of_present_illnes;
	}
	function set_history_of_present_illnes($data){
		if(!empty($data)){
			$this->history_of_present_illnes = $data;
		}
	}

	function get_review_of_systems(){
		return $this->review_of_systems;
	}
	function set_review_of_systems($data){
		if(!empty($data)){
			$this->review_of_systems = $data;
		}
	}

	function get_past_medical_history(){
		return $this->past_medical_history;
	}
	function set_past_medical_history($data){
		if(!empty($data)){
			$this->past_medical_history = $data;
		}
	}

	function get_social_history(){
		return $this->social_history;
	}
	function set_social_history($data){
		if(!empty($data)){
			$this->social_history = $data;
		}
	}


	function get_sh_options(){
		$sql = sqlStatement("select * from list_options WHERE list_id='Social_History'");
		$ret = array();
		while($row = sqlFetchArray($sql)){
			$ret[$row['option_id']] = $row["title"];
		}
		return $ret;
	}

	function get_pl_options(){
		$sql = sqlStatement("select id, title, diagnosis from lists where type='medical_problem' and pid='".$_SESSION['pid']."'");
		$ret = array();
		while($row = sqlFetchArray($sql)){
			$label = $row['title']." (".$row['diagnosis'].")";
			$ret[$row['id']] = $label;
		}
		return $ret;
	}

	function get_wt_options(){
		$ret = array();
		$ret['lb'] = "Lb";
		$ret['kg'] = "Kg";
		return $ret;
	}

	function get_ht_options(){
		$ret = array();
		$ret['in'] = "In";
		$ret['mtr'] = "M";
		return $ret;
	}

	function get_tp_options(){
		$ret = array();
		$ret['cs'] = "C";
		$ret['fh'] = "F";
		return $ret;
	}

	function get_social_history_pks_day(){
		return $this->social_history_pks_day;
	}
	function set_social_history_pks_day($data){
		if(!empty($data)){
			$this->social_history_pks_day = $data;
		}
	}

	function get_social_history_yrs_smkd(){
		return $this->social_history_yrs_smkd;
	}
	function set_social_history_yrs_smkd($data){
		if(!empty($data)){
			$this->social_history_yrs_smkd = $data;
		}
	}

	function get_social_history_desc(){
		return $this->social_history_desc;
	}
	function set_social_history_desc($data){
		if(!empty($data)){
			$this->social_history_desc = $data;
		}
	}

	function get_family_history_desc(){
		return $this->family_history_desc;
	}
	function set_family_history_desc($data){
		if(!empty($data)){
			$this->family_history_desc = $data;
		}
	}

	function get_family_history(){
		return $this->family_history;
	}
	function set_family_history($data){
		if(!empty($data)){
			$this->family_history = $data;
		}
	}

	function get_allergies(){
		return $this->allergies;
	}
	function set_allergies($data){
		if(!empty($data)){
			$this->allergies = $data;
		}
	}

	function get_current_medications(){
		return $this->current_medications;
	}
	function set_current_medications($data){
		if(!empty($data)){
			$this->current_medications = $data;
		}
	}

	function get_vital_weight(){
		return $this->vital_weight;
	}
	function set_vital_weight($data){
		if(!empty($data)){
			$this->vital_weight = $data;
		}
	}

	function get_vital_wt(){
		return $this->vital_wt;
	}
	function set_vital_wt($data){
		if(!empty($data)){
			$this->vital_wt = $data;
		}
	}

	function get_vital_height(){
		return $this->vital_height;
	}
	function set_vital_height($data){
		if(!empty($data)){
			$this->vital_height = $data;
		}
	}

	function get_vital_ht(){
		return $this->vital_ht;
	}
	function set_vital_ht($data){
		if(!empty($data)){
			$this->vital_ht = $data;
		}
	}

	function get_vital_temp(){
		return $this->vital_temp;
	}
	function set_vital_temp($data){
		if(!empty($data)){
			$this->vital_temp = $data;
		}
	}

	function get_vital_tp(){
		return $this->vital_tp;
	}
	function set_vital_tp($data){
		if(!empty($data)){
			$this->vital_tp = $data;
		}
	}

	function get_vital_bp1(){
		return $this->vital_bp1;
	}
	function set_vital_bp1($data){
		if(!empty($data)){
			$this->vital_bp1 = $data;
		}
	}
	function get_vital_bp2(){
		return $this->vital_bp2;
	}
	function set_vital_bp2($data){
		if(!empty($data)){
			$this->vital_bp2 = $data;
		}
	}
	function get_vital_pulse(){
		return $this->vital_pulse;
	}
	function set_vital_pulse($data){
		if(!empty($data)){
			$this->vital_pulse = $data;
		}
	}
	function get_vital_rr(){
		return $this->vital_rr;
	}
	function set_vital_rr($data){
		if(!empty($data)){
			$this->vital_rr = $data;
		}
	}
	function get_vital_bmi(){
		return $this->vital_bmi;
	}
	function set_vital_bmi($data){
		if(!empty($data)){
			$this->vital_bmi = $data;
		}
	}
	function get_vital_sat(){
		return $this->vital_sat;
	}
	function set_vital_sat($data){
		if(!empty($data)){
			$this->vital_sat = $data;
		}
	}
	function get_vital_on02(){
		return $this->vital_on02;
	}
	function set_vital_on02($data){
		if(!empty($data)){
			$this->vital_on02 = $data;
		}
	}
	function get_physical_exam_desc(){
		return $this->physical_exam_desc;
	}
	function set_physical_exam_desc($data){
		if(!empty($data)){
			$this->physical_exam_desc = $data;
		}
	}

	function get_dianosis_desc(){
		return $this->diagnosis_desc;
	}
	function set_dianosis_desc($data){
		if(!empty($data)){
			$this->diagnosis_desc = $data;
		}
	}

	function get_assessment(){
		return $this->assessment;
	}
	function set_assessment($data){
		if(!empty($data)){
			$this->assessment = $data;
		}
	}

	function get_plan(){
		return $this->plan;
	}
	function set_plan($data){
		if(!empty($data)){
			$this->plan = $data;
		}
	}

	function get_surgical_procedure(){
		return $this->surgical_procedure;
	}
	function set_surgical_procedure($data){
		if(!empty($data)){
			$this->surgical_procedure = $data;
		}
	}

	function get_in_clinic_tests(){
			return $this->in_clinic_tests;
	}
	function set_in_clinic_tests($data){
		if(!empty($data)){
			$this->in_clinic_tests = $data;
		}
	}

	function get_feecodes(){
		return $this->feecodes;
	}
	function set_feecodes($data){
		if(!empty($data)){
			$this->feecode = $data;
			$codesall = explode("#",$data);
			//$code_array = $allorder->icdcodes;
			$formProviderId = $_SESSION['authUserID'] + 0;
			$pid = $_SESSION['pid'];
			$encounter = $_SESSION['encounter'];
			//print_r($codesall);exit;
			foreach($codesall as $codes){
				//$temp = sqlQuery("SELECT c.code_text, p.pr_price FROM codes c JOIN prices p ON p.pr_id = c.id WHERE c.code='$cpt[1]' AND p.pr_level='{$priceSpeciality['price_speciality']}'");
				$tcode = explode("~",$codes);
				$code = $tcode[0];
				//$temp = sqlQuery("SELECT short_desc FROM icd10_dx_order_code WHERE dx_code='$codes'");
				$code_text = $tcode[1];
				if (strpos($code_text, '[CPT4]') !== false) {
					$code_type = "CPT4";
					$code_text = str_replace("[CPT4]","",$code_text);
				}
				else{
					$code_type = "ICD10";
				}
				$exist = sqlQuery("SELECT count(*) as count FROM billing WHERE pid='$pid' AND encounter='$encounter' AND code_type='$code_type' AND code='$codes' AND activity=1");
				if(empty($exist['count'])){
					addBilling($encounter, $code_type, $code, $code_text, $pid,"1",$_SESSION['authUserID'],'','1','0');
				}
			}
		}
	}

	function get_laborders(){
		return $this->laborders;
	}
	function set_laborders($data){
		if(!empty($data)){
			$this->laborders = $data;
		}
	}

	function persist() {
		parent::persist();
	}
}	// end of Form

?>
