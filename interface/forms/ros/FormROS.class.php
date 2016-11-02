<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */
 
require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");


/**
 * class 
 *
 */
class FormROS extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	/**
	 *
	 * static
	 */

	/**
	 *
	 * @access private
	 */

	public $id;
	public $date;
	public $pid;
	public $weight_change = "N/A";
	public $weakness = "N/A";
	public $fatigue = "N/A";
	public $anorexia = "N/A";
	public $fever = "N/A";
	public $chills = "N/A";
	public $night_sweats = "N/A";
	public $insomnia = "N/A";
	public $irritability = "N/A";
	public $heat_or_cold = "N/A";
	public $intolerance = "N/A";
	public $change_in_vision = "N/A";
	public $glaucoma_history = "N/A";
	public $eye_pain = "N/A";
	public $irritation = "N/A";
	public $redness = "N/A";
	public $excessive_tearing = "N/A";
	public $double_vision = "N/A";
	public $blind_spots = "N/A";
	public $photophobia = "N/A";
	public $hearing_loss = "N/A";
    public $discharge = "N/A";
    public $pain = "N/A";
    public $vertigo = "N/A";
    public $tinnitus = "N/A";
    public $frequent_colds = "N/A";
    public $sore_throat = "N/A";
    public $sinus_problems = "N/A";
    public $post_nasal_drip = "N/A";
    public $nosebleed = "N/A";
    public $snoring = "N/A";
    public $apnea = "N/A";
    public $breast_mass = "N/A";
	public $breast_discharge = "N/A";
	public $biopsy = "N/A";
	public $abnormal_mammogram = "N/A";
	public $cough = "N/A";
	public $sputum = "N/A";
	public $shortness_of_breath = "N/A";
	public $wheezing = "N/A";
	public $hemoptsyis = "N/A";
	public $asthma = "N/A";
	public $copd = "N/A";
	public $chest_pain = "N/A";
    public $palpitation = "N/A";
    public $syncope = "N/A";
    public $pnd = "N/A";
    public $doe = "N/A";
    public $orthopnea = "N/A";
    public $peripheal = "N/A";
    public $edema = "N/A";
    public $legpain_cramping = "N/A";
    public $history_murmur = "N/A";
    public $arryhmia = "N/A";
    public $heart_problem = "N/A";
    public $dysphagia = "N/A";
	public $heartburn = "N/A";
	public $bloating = "N/A";
	public $belching = "N/A";
	public $flatulence = "N/A";
	public $nausea = "N/A";
	public $vomiting = "N/A";
	public $hematemesis = "N/A";
	public $gastro_pain = "N/A";
	public $food_intolerance = "N/A";
	public $hepatitis = "N/A";
	public $jaundice = "N/A";
	public $hematochezia = "N/A";
	public $changed_bowel = "N/A";
	public $diarrhea = "N/A";
	public $constipation = "N/A";
	public $polyuria = "N/A";
	public $polydypsia = "N/A";
	public $dysuria = "N/A";
	public $hematuria = "N/A";
	public $frequency = "N/A";
	public $urgency = "N/A";
	public $incontinence = "N/A";
	public $renal_stones = "N/A";
	public $utis = "N/A";
	public $hesitancy = "N/A";
	public $dribbling = "N/A";
	public $stream = "N/A";
	public $nocturia = "N/A";
	public $erections = "N/A";
	public $ejaculations = "N/A";
	public $g = "N/A";
	public $p = "N/A";
	public $ap = "N/A";
	public $lc = "N/A";
	public $mearche = "N/A";
	public $menopause = "N/A";
	public $lmp = "N/A";
	public $f_frequency = "N/A";
	public $f_flow = "N/A";
	public $f_symptoms = "N/A";
	public $abnormal_hair_growth = "N/A";
	public $f_hirsutism = "N/A";
	public $joint_pain = "N/A";
	public $swelling = "N/A";
	public $m_redness = "N/A";
	public $m_warm = "N/A";
	public $m_stiffness = "N/A";
	public $muscle = "N/A";
	public $m_aches = "N/A";
	public $fms = "N/A";
	public $arthritis = "N/A";
	public $loc = "N/A";
	public $seizures = "N/A";
	public $stroke = "N/A";
	public $tia = "N/A";
	public $n_numbness = "N/A";
	public $n_weakness = "N/A";
	public $paralysis = "N/A";
	public $intellectual_decline = "N/A";
	public $memory_problems = "N/A";
	public $dementia = "N/A";
	public $n_headache = "N/A";
	public $s_cancer = "N/A";
	public $psoriasis = "N/A";
	public $s_acne = "N/A";
	public $s_other = "N/A";
	public $s_disease = "N/A";
	public $p_diagnosis = "N/A";
	public $p_medication = "N/A";
	public $depression = "N/A";
	public $anxiety = "N/A";
	public $social_difficulties = "N/A";
	public $thyroid_problems = "N/A";
	public $diabetes = "N/A";
	public $abnormal_blood = "N/A";
	public $anemia = "N/A";
	public $fh_blood_problems = "N/A";
	public $bleeding_problems = "N/A";
	public $allergies = "N/A";
	public $frequent_illness = "N/A";
	public $hiv = "N/A";
	public $hai_status = "N/A";
	
	/**
	 * Constructor sets all Form attributes to their default value
	 */

	public function __construct($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			$id = "";	
		}
		$this->date = date("Y-m-d H:i:s");
		$this->date_of_onset = date("Y-m-d");
		$this->_table = "form_ros";
		
		$this->pid = $GLOBALS['pid'];
		if ($id != "") {
		
			$this->populate();
		}
	}
	public function populate() {
		parent::populate();
	}

	public function set_id($id) {
		if (!empty($id) && is_numeric($id)) {
			$this->id = $id;
		}
	}
	public function get_id() {
		return $this->id;
	}
	public function set_pid($pid) {
		if (!empty($pid) && is_numeric($pid)) {
			$this->pid = $pid;
		}
	}
	public function get_pid() {
		return $this->pid;
	}

	public function get_date() {
		return $this->date;
	}
	
	public function set_date($date) {
		if(!empty($date)){
			$this->date = $date;
		}	
	}
	
	public function get_weight_change(){
		return $this->weight_change;
	}
	public function set_weight_change($data){
		if(!empty($data)){
			$this->weight_change = $data;
		}
	}
	
	public function get_weakness(){
		return $this->weakness;
	}
	public function set_weakness($data){
		if(!empty($data)){
			$this->weakness = $data;
		}
	}
	
	public function get_fatigue(){
		return $this->fatigue;
	}
	public function set_fatigue($data){
		if(!empty($data)){
			$this->fatigue = $data;
		}
	}
	
	public function get_anorexia(){
		return $this->anorexia;
	}
	public function set_anorexia($data){
		if(!empty($data)){
			$this->anorexia = $data;
		}
	}
	
	public function get_fever(){
		return $this->fever;
	}
	public function set_fever($data){
		if(!empty($data)){
			$this->fever = $data;
		}
	}
	
	public function get_chills(){
		return $this->chills;
	}
	public function set_chills($data){
		if(!empty($data)){
			$this->chills = $data;
		}
	}
	
	public function get_night_sweats(){
		return $this->night_sweats;
	}
	public function set_night_sweats($data){
		if(!empty($data)){
			$this->night_sweats = $data;
		}
	}
	
	public function get_insomnia(){
		return $this->insomnia;
	}
	public function set_insomnia($data){
		if(!empty($data)){
			$this->insomnia = $data;
		}
	}
	
	public function get_irritability(){
		return $this->irritability;
	}
	public function set_irritability($data){
		if(!empty($data)){
			$this->irritability = $data;
		}
	}
	
	public function get_heat_or_cold(){
		return $this->heat_or_cold;
	}
	public function set_heat_or_cold($data){
		if(!empty($data)){
			$this->heat_or_cold = $data;
		}
	}
	
	public function get_intolerance(){
		return $this->intolerance;
	}
	public function set_intolerance($data){
		if(!empty($data)){
			$this->intolerance = $data;
		}
	}
	
	public function get_change_in_vision(){
		return $this->change_in_vision;
	}
	public function set_change_in_vision($data){
		if(!empty($data)){
			$this->change_in_vision = $data;
		}
	}
	public function get_glaucoma_history(){
		return $this->glaucoma_history;
	}
	public function set_glaucoma_history($data){
		if(!empty($data)){
			$this->glaucoma_history = $data;
		}
	}
	public function get_eye_pain(){
		return $this->eye_pain;
	}
	public function set_eye_pain($data){
		if(!empty($data)){
			$this->eye_pain = $data;
		}
	}
	public function get_irritation(){
		return $this->irritation;
	}
	public function set_irritation($data){
		if(!empty($data)){
			$this->irritation = $data;
		}
	}
	public function get_redness(){
		return $this->redness;
	}
	public function set_redness($data){
		if(!empty($data)){
			$this->redness = $data;
		}
	}
	public function get_excessive_tearing(){
		return $this->excessive_tearing;
	}
	public function set_excessive_tearing($data){
		if(!empty($data)){
			$this->excessive_tearing = $data;
		}
	}
	public function get_double_vision(){
		return $this->double_vision;
	}
	public function set_double_vision($data){
		if(!empty($data)){
			$this->double_vision = $data;
		}
	}
	public function get_blind_spots(){
		return $this->blind_spots;
	}
	public function set_blind_spots($data){
		if(!empty($data)){
			$this->blind_spots = $data;
		}
	}
	public function get_photophobia(){
		return $this->photophobia;
	}
	public function set_photophobia($data){
		if(!empty($data)){
			$this->photophobia = $data;
		}
	}
	
	public function get_hearing_loss(){
		return $this->hearing_loss;
	}
	public function set_hearing_loss($data){
		if(!empty($data)){
			$this->hearing_loss = $data;
		}
	}
	public function get_discharge(){
		return $this->discharge;
	}
	public function set_discharge($data){
		if(!empty($data)){
			$this->discharge = $data;
		}
	}
	public function get_pain(){
		return $this->pain;
	}
	public function set_pain($data){
		if(!empty($data)){
			$this->pain = $data;
		}
	}
	public function get_vertigo(){
		return $this->vertigo;
	}
	public function set_vertigo($data){
		if(!empty($data)){
			$this->vertigo = $data;
		}
	}
	public function get_tinnitus(){
		return $this->tinnitus;
	}
	public function set_tinnitus($data){
		if(!empty($data)){
			$this->tinnitus = $data;
		}
	}
	public function get_frequent_colds(){
		return $this->frequent_colds;
	}
	public function set_frequent_colds($data){
		if(!empty($data)){
			$this->frequent_colds = $data;
		}
	}
	public function get_sore_throat(){
		return $this->sore_throat;
	}
	public function set_sore_throat($data){
		if(!empty($data)){
			$this->sore_throat = $data;
		}
	}
	public function get_sinus_problems(){
		return $this->sinus_problems;
	}
	public function set_sinus_problems($data){
		if(!empty($data)){
			$this->sinus_problems = $data;
		}
	}
	public function get_post_nasal_drip(){
		return $this->post_nasal_drip;
	}
	public function set_post_nasal_drip($data){
		if(!empty($data)){
			$this->post_nasal_drip = $data;
		}
	}
	public function get_nosebleed(){
		return $this->nosebleed;
	}
	public function set_nosebleed($data){
		if(!empty($data)){
			$this->nosebleed = $data;
		}
	}
	public function get_snoring(){
		return $this->snoring;
	}
	public function set_snoring($data){
		if(!empty($data)){
			$this->snoring = $data;
		}
	}
	public function get_apnea(){
		return $this->apnea;
	}
	public function set_apnea($data){
		if(!empty($data)){
			$this->apnea = $data;
		}
	}
	public function get_breast_mass(){
		return $this->breast_mass;
	}
	public function set_breast_mass($data){
		if(!empty($data)){
			$this->breast_mass = $data;
		}
	}
	public function get_breast_discharge(){
		return $this->breast_discharge;
	}
	public function set_breast_discharge($data){
		if(!empty($data)){
			$this->breast_discharge = $data;
		}
	}
	public function get_biopsy(){
		return $this->breast_discharge;
	}
	public function set_biopsy($data){
		if(!empty($data)){
			$this->biopsy = $data;
		}
	}
	public function get_abnormal_mammogram(){
		return $this->abnormal_mammogram;
	}
	public function set_abnormal_mammogram($data){
		if(!empty($data)){
			$this->abnormal_mammogram = $data;
		}
	}
	public function get_cough(){
		return $this->cough;
	}
	public function set_cough($data){
		if(!empty($data)){
			$this->cough = $data;
		}
	}
	public function set_sputum($data){
		if(!empty($data)){
			$this->sputum = $data;
		}
	}
	public function get_sputum(){
		return $this->sputum;
	}
	public function get_shortness_of_breath(){
		return $this->shortness_of_breath;
	}
	public function set_shortness_of_breath($data){
		if(!empty($data)){
			$this->shortness_of_breath = $data;
		}
	}
	public function get_wheezing(){
		return $this->wheezing;
	}
	public function set_wheezing($data){
		if(!empty($data)){
			$this->wheezing = $data;
		}
	}
	public function get_hemoptsyis(){
		return $this->hemoptsyis;
	}
	public function set_hemoptsyis($data){
		if(!empty($data)){
			$this->hemoptsyis = $data;
		}
	}
	public function get_asthma(){
		return $this->asthma;
	}
	public function set_asthma($data){
		if(!empty($data)){
			$this->asthma = $data;
		}
	}
	public function get_copd(){
		return $this->copd;
	}
	public function set_copd($data){
		if(!empty($data)){
			$this->copd = $data;
		}
	}
		  
    public function get_chest_pain(){
		return $this->chest_pain;
	}
	public function set_chest_pain($data){
		if(!empty($data)){
			$this->chest_pain = $data;
		}
	}
	public function get_palpitation(){
		return $this->palpitation;
	}
	public function set_palpitation($data){
		if(!empty($data)){
			$this->palpitation = $data;
		}
	}
	public function get_syncope(){
		return $this->syncope;
	}
	public function set_syncope($data){
		if(!empty($data)){
			$this->syncope = $data;
		}
	}
	public function get_pnd(){
		return $this->pnd;
	}
	public function set_pnd($data){
		if(!empty($data)){
			$this->pnd = $data;
		}
	}
	public function get_doe(){
		return $this->doe;
	}
	public function set_doe($data){
		if(!empty($data)){
			$this->doe = $data;
		}
	}
	public function get_orthopnea(){
		return $this->orthopnea;
	}
	public function set_orthopnea($data){
		if(!empty($data)){
			$this->orthopnea = $data;
		}
	}
	public function get_peripheal(){
		return $this->peripheal;
	}
	public function set_peripheal($data){
		if(!empty($data)){
			$this->peripheal = $data;
		}
	}
	public function get_edema(){
		return $this->edema;
	}
	public function set_edema($data){
		if(!empty($data)){
			$this->edema = $data;
		}
	}
	public function get_legpain_cramping(){
		return $this->legpain_cramping;
	}
	public function set_legpain_cramping($data){
		if(!empty($data)){
			$this->legpain_cramping = $data;
		}
	}
	public function get_history_murmur(){
		return $this->history_murmur;
	}
	public function set_history_murmur($data){
		if(!empty($data)){
			$this->history_murmur = $data;
		}
	}
	public function get_arrythmia(){
		return $this->arrythmia;
	}
	public function set_arrythmia($data){
		if(!empty($data)){
			$this->arrythmia = $data;
		}
	}
	public function get_heart_problem(){
		return $this->heart_problem;
	}
	public function set_heart_problem($data){
		if(!empty($data)){
			$this->heart_problem = $data;
		}
	}
	
	public function get_polyuria(){
		return $this->polyuria;
	}
	public function set_polyuria($data){
		if(!empty($data)){
			$this->polyuria = $data;
		}
	}
	public function get_polydypsia(){
		return $this->polydypsia;
	}
	public function set_polydypsia($data){
		if(!empty($data)){
			$this->polydypsia = $data;
		}
	}
	public function get_dysuria(){
		return $this->dysuria;
	}
	public function set_dysuria($data){
		if(!empty($data)){
			$this->dysuria = $data;
		}
	}
	public function get_hematuria(){
		return $this->hematuria;
	}
	public function set_hematuria($data){
		if(!empty($data)){
			$this->hematuria = $data;
		}
	}
	public function get_frequency(){
		return $this->frequency;
	}
	public function set_frequency($data){
		if(!empty($data)){
			$this->frequency = $data;
		}
	}
	public function get_urgency(){
		return $this->urgency;
	}
	public function set_urgency($data){
		if(!empty($data)){
			$this->urgency = $data;
		}
	}
	public function get_incontinence(){
		return $this->incontinence;
	}
	public function set_incontinence($data){
		if(!empty($data)){
			$this->incontinence = $data;
		}
	}
	public function get_renal_stones(){
		return $this->renal_stones;
	}
	public function set_renal_stones($data){
		if(!empty($data)){
			$this->renal_stones = $data;
		}
	}
	public function get_utis(){
		return $this->utis;
	}
	public function set_utis($data){
		if(!empty($data)){
			$this->utis = $data;
		}
	}
	
	public function get_hesitancy(){
		return $this->hesitancy;
	}
	public function set_hesitancy($data){
		if(!empty($data)){
			$this->hesitancy = $data;
		}
	}
	public function get_dribbling(){
		return $this->dribbling;
	}
	public function set_dribbling($data){
		if(!empty($data)){
			$this->dribbling = $data;
		}
	}
	public function get_stream(){
		return $this->stream;
	}
	public function set_stream($data){
		if(!empty($data)){
			$this->stream = $data;
		}
	}
	public function get_nocturia(){
		return $this->nocturia;
	}
	public function set_nocturia($data){
		if(!empty($data)){
			$this->nocturia = $data;
		}
	}
	public function get_erections(){
		return $this->erections;
	}
	public function set_erections($data){
		if(!empty($data)){
			$this->erections = $data;
		}
	}
	public function get_ejaculations(){
		return $this->ejaculations;
	}
	public function set_ejaculations($data){
		if(!empty($data)){
			$this->ejaculations = $data;
		}
	}
		
	public function get_g(){
		return $this->g;
	}
	public function set_g($data){
		if(!empty($data)){
			$this->g = $data;
		}
	}
	public function get_p(){
		return $this->p;
	}
	public function set_p($data){
		if(!empty($data)){
			$this->p = $data;
		}
	}
	public function get_ap(){
		return $this->ap;
	}
	public function set_ap($data){
		if(!empty($data)){
			$this->ap = $data;
		}
	}
	public function get_lc(){
		return $this->lc;
	}
	public function set_lc($data){
		if(!empty($data)){
			$this->lc = $data;
		}
	}
	public function get_mearche(){
		return $this->mearche;
	}
	public function set_mearche($data){
		if(!empty($data)){
			$this->mearche = $data;
		}
	}
	public function get_menopause(){
		return $this->menopause;
	}
	public function set_menopause($data){
		if(!empty($data)){
			$this->menopause = $data;
		}
	}
	public function get_lmp(){
		return $this->lmp;
	}
	public function set_lmp($data){
		if(!empty($data)){
			$this->lmp = $data;
		}
	}
	public function get_f_frequency(){
		return $this->f_frequency;
	}
	public function set_f_frequency($data){
		if(!empty($data)){
			$this->f_frequency = $data;
		}
	}
	public function get_f_flow(){
		return $this->f_flow;
	}
	public function set_f_flow($data){
		if(!empty($data)){
			$this->f_flow = $data;
		}
	}
	public function get_f_symptoms(){
		return $this->f_symptoms;
	}
	public function set_f_symptoms($data){
		if(!empty($data)){
			$this->f_symptoms = $data;
		}
	}
	public function get_abnormal_hair_growth(){
		return $this->abnormal_hair_growth;
	}
	public function set_abnormal_hair_growth($data){
		if(!empty($data)){
			$this->abnormal_hair_growth = $data;
		}
	}
	public function get_f_hirsutism(){
		return $this->f_hirsutism;
	}
	public function set_f_hirsutism($data){
		if(!empty($data)){
			$this->f_hirsutism = $data;
		}
	}
	
	public function get_joint_pain(){
		return $this->joint_pain;
	}
	public function set_joint_pain($data){
		if(!empty($data)){
			$this->joint_pain = $data;
		}
	}
	public function get_swelling(){
		return $this->swelling;
	}
	public function set_swelling($data){
		if(!empty($data)){
			$this->swelling = $data;
		}
	}
	public function get_m_redness(){
		return $this->m_redness;
	}
	public function set_m_redness($data){
		if(!empty($data)){
			$this->m_redness = $data;
		}
	}
	public function get_m_warm(){
		return $this->m_warm;
	}
	public function set_m_warm($data){
		if(!empty($data)){
			$this->m_warm = $data;
		}
	}
	public function get_m_stiffness(){
		return $this->m_stiffness;
	}
	public function set_m_stiffness($data){
		if(!empty($data)){
			$this->m_stiffness = $data;
		}
	}
	public function get_muscle(){
		return $this->muscle;
	}
	public function set_muscle($data){
		if(!empty($data)){
			$this->muscle = $data;
		}
	}
	public function get_m_aches(){
		return $this->m_aches;
	}
	public function set_m_aches($data){
		if(!empty($data)){
			$this->m_aches = $data;
		}
	}
	public function get_fms(){
		return $this->fms;
	}
	public function set_fms($data){
		if(!empty($data)){
			$this->fms = $data;
		}
	}
	public function get_arthritis(){
		return $this->arthritis;
	}
	public function set_arthritis($data){
		if(!empty($data)){
			$this->arthritis = $data;
		}
	}
	
	public function get_loc(){
		return $this->loc;
	}
	public function set_loc($data){
		if(!empty($data)){
			$this->loc = $data;
		}
	}
	public function get_seizures(){
		return $this->seizures;
	}
	public function set_seizures($data){
		if(!empty($data)){
			$this->seizures = $data;
		}
	}
	public function get_stroke(){
		return $this->stroke;
	}
	public function set_stroke($data){
		if(!empty($data)){
			$this->stroke = $data;
		}
	}
	public function get_tia(){
		return $this->tia;
	}
	public function set_tia($data){
		if(!empty($data)){
			$this->tia = $data;
		}
	}
	public function get_n_numbness(){
		return $this->n_numbness;
	}
	public function set_n_numbness($data){
		if(!empty($data)){
			$this->n_numbness = $data;
		}
	}
	public function get_n_weakness(){
		return $this->n_weakness;
	}
	public function set_n_weakness($data){
		if(!empty($data)){
			$this->n_weakness = $data;
		}
	}
	public function get_paralysis(){
		return $this->paralysis;
	}
	public function set_paralysis($data){
		if(!empty($data)){
			$this->paralysis = $data;
		}
	}
	public function get_intellectual_decline(){
		return $this->intellectual_decline;
	}
	public function set_intellectual_decline($data){
		if(!empty($data)){
			$this->intellectual_decline = $data;
		}
	}
	public function get_memory_problems(){
		return $this->memory_problems;
	}
	public function set_memory_problems($data){
		if(!empty($data)){
			$this->memory_problems = $data;
		}
	}
	public function get_dementia(){
		return $this->dementia;
	}
	public function set_dementia($data){
		if(!empty($data)){
			$this->dementia = $data;
		}
	}
	public function get_n_headache(){
		return $this->n_headache;
	}
	public function set_n_headache($data){
		if(!empty($data)){
			$this->n_headache = $data;
		}
	}
	
	public function get_s_cancer(){
		return $this->s_cancer;
	}
	public function set_s_cancer($data){
		if(!empty($data)){
			$this->s_cancer = $data;
		}
	}
	public function get_psoriasis(){
		return $this->psoriasis;
	}
	public function set_psoriasis($data){
		if(!empty($data)){
			$this->psoriasis = $data;
		}
	}
	public function get_s_acne(){
		return $this->s_acne;
	}
	public function set_s_acne($data){
		if(!empty($data)){
			$this->s_acne = $data;
		}
	}
	public function get_s_other(){
		return $this->s_other;
	}
	public function set_s_other($data){
		if(!empty($data)){
			$this->s_other = $data;
		}
	}
	public function get_s_disease(){
		return $this->s_disease;
	}
	public function set_s_disease($data){
		if(!empty($data)){
			$this->s_disease = $data;
		}
	}
	
	public function get_p_diagnosis(){
		return $this->p_diagnosis;
	}
	public function set_p_diagnosis($data){
		if(!empty($data)){
			$this->p_diagnosis = $data;
		}
	}
	public function get_p_medication(){
		return $this->p_medication;
	}
	public function set_p_medication($data){
		if(!empty($data)){
			$this->p_medication = $data;
		}
	}
	public function get_depression(){
		return $this->depression;
	}
	public function set_depression($data){
		if(!empty($data)){
			$this->depression = $data;
		}
	}
	public function get_anxiety(){
		return $this->anxiety;
	}
	public function set_anxiety($data){
		if(!empty($data)){
			$this->anxiety = $data;
		}
	}
	public function get_social_difficulties(){
		return $this->social_difficulties;
	}
	public function set_social_difficulties($data){
		if(!empty($data)){
			$this->social_difficulties = $data;
		}
	}
	
	public function get_thyroid_problems(){
		return $this->thyroid_problems;
	}
	public function set_thyroid_problems($data){
		if(!empty($data)){
			$this->thyroid_problems = $data;
		}
	}
	public function get_diabetes(){
		return $this->diabetes;
	}
	public function set_diabetes($data){
		if(!empty($data)){
			$this->diabetes = $data;
		}
	}
	public function get_abnormal_blood(){
		return $this->abnormal_blood;
	}
	public function set_abnormal_blood($data){
		if(!empty($data)){
			$this->abnormal_blood = $data;
		}
	}
	
	public function get_anemia(){
		return $this->anemia;
	}
	public function set_anemia($data){
		if(!empty($data)){
			$this->anemia = $data;
		}
	}
	public function get_fh_blood_problems(){
		return $this->fh_blood_problems;
	}
	public function set_fh_blood_problems($data){
		if(!empty($data)){
			$this->fh_blood_problems = $data;
		}
	}
	public function get_bleeding_problems(){
		return $this->bleeding_problems;
	}
	public function set_bleeding_problems($data){
		if(!empty($data)){
			$this->bleeding_problems = $data;
		}
	}
	public function get_allergies(){
		return $this->allergies;
	}
	public function set_allergies($data){
		if(!empty($data)){
			$this->allergies = $data;
		}
	}
	public function get_frequent_illness(){
		return $this->frequent_illness;
	}
	public function set_frequent_illness($data){
		if(!empty($data)){
			$this->frequent_illness = $data;
		}
	}
	public function get_hiv(){
		return $this->hiv;
	}
	public function set_hiv($data){
		if(!empty($data)){
			$this->hiv = $data;
		}
	}
	public function get_hai_status(){
		return $this->hai_status;
	}
	public function set_hai_status($data){
		if(!empty($data)){
			$this->hai_status = $data;
		}
	}
	
	public function get_options(){
		$ret = array("N/A" => xl('N/A'),"YES" => xl('YES'),"NO" => xl('NO'));
		return $ret;
	}
		
	public function get_dysphagia(){
		return $this->dysphagia;
	}
	public function set_dysphagia($data){
		if(!empty($data)){
			$this->dysphagia = $data;
		}
	}
	public function get_heartburn(){
		return $this->heartburn;
	}
	public function set_heartburn($data){
		if(!empty($data)){
			$this->heartburn = $data;
		}
	}
	public function get_bloating(){
		return $this->bloating;
	}
	public function set_bloating($data){
		if(!empty($data)){
			$this->bloating = $data;
		}
	}
	public function get_belching(){
		return $this->belching;
	}
	public function set_belching($data){
		if(!empty($data)){
			$this->belching = $data;
		}
	}
	public function get_flatulence(){
		return $this->flatulence;
	}
	public function set_flatulence($data){
		if(!empty($data)){
			$this->flatulence = $data;
		}
	}
	public function get_nausea(){
		return $this->nausea;
	}
	public function set_nausea($data){
		if(!empty($data)){
			$this->nausea = $data;
		}
	}
	public function get_vomiting(){
		return $this->vomiting;
	}
	public function set_vomiting($data){
		if(!empty($data)){
			$this->vomiting = $data;
		}
	}
	public function get_hematemesis(){
		return $this->hematemesis;
	}
	public function set_hematemesis($data){
		if(!empty($data)){
			$this->hematemesis = $data;
		}
	}
	public function get_gastro_pain(){
		return $this->gastro_pain;
	}
	public function set_gastro_pain($data){
		if(!empty($data)){
			$this->gastro_pain = $data;
		}
	}
	public function get_food_intolerance(){
		return $this->food_intolerance;
	}
	public function set_food_intolerance($data){
		if(!empty($data)){
			$this->food_intolerance = $data;
		}
	}
	public function get_hepatitis(){
		return $this->hepatitis;
	}
	public function set_hepatitis($data){
		if(!empty($data)){
			$this->hepatitis = $data;
		}
	}
	public function get_jaundice(){
		return $this->jaundice;
	}
	public function set_jaundice($data){
		if(!empty($data)){
			$this->jaundice = $data;
		}
	}
	public function get_hematochezia(){
		return $this->hematochezia;
	}
	public function set_hematochezia($data){
		if(!empty($data)){
			$this->hematochezia = $data;
		}
	}
	public function get_changed_bowel(){
		return $this->changed_bowel;
	}
	public function set_changed_bowel($data){
		if(!empty($data)){
			$this->changed_bowel = $data;
		}
	}
	public function get_diarrhea(){
		return $this->diarrhea;
	}
	public function set_diarrhea($data){
		if(!empty($data)){
			$this->diarrhea = $data;
		}
	}
	public function get_constipation(){
		return $this->constipation;
	}
	public function set_constipation($data){
		if(!empty($data)){
			$this->constipation = $data;
		}
	}
	public function toString($html = false) {
		$string .= "\n"
			."ID: " . $this->id . "\n";

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}
	public function persist() {
		parent::persist();
	}
	
	
}	// end of Form

?>
