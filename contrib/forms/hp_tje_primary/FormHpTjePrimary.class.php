<?php

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */
class FormHpTjePrimary extends ORDataObject
{
    /**
     *
     * @access public
     */


    /**
     *
     * static
     */
    var $event_array = array("","Vehicular Accident","Work Related Accident","Slip & Fall","Other");

    /**
     *
     * @access private
     */

    var $id;
    var $referred_by;
    var $complaints;
    var $date_of_onset;
    var $event;
    var $event_description;
    var $prior_symptoms;
    var $aggravated_symptoms;
    var $comments;
    var $date;
    var $teeth_sore_number;
    var $teeth_mobile_number;
    var $teeth_fractured_number;
    var $teeth_avulsed_number;
    var $precipitating_factors_other_text;
    var $checks;
    var $pid;
    var $activity;
    var $history;
    var $previous_accidents;

    /**
     * Constructor sets all Form attributes to their default value
     */

    function __construct($id = "", $_prefix = "")
    {
        parent::__construct();

        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
        }

        $this->date = date("Y-m-d H:i:s");
        $this->date_of_onset = date("Y-m-d");
        $this->_table = "form_hp_tje_primary";
        $this->checks = array();
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
        if ($id != "") {
            $this->populate();
        }
    }
    function populate()
    {
        parent::populate();

        $sql = "SELECT name from form_hp_tje_checks where foreign_id = ?";
        $results = sqlQ($sql, [$this->id]);

        while ($row = sqlFetchArray($results)) {
            $this->checks[] = $row['name'];
        }


        $sql = "SELECT doctor,specialty,tx_rendered,effectiveness,date from form_hp_tje_history where foreign_id = ?";
        $results = sqlQ($sql, [$this->id]);

        while ($row = sqlFetchArray($results)) {
            $this->history[] = $row;
        }

        $sql = "SELECT nature_of_accident,injuries,date from form_hp_tje_previous_accidents where foreign_id = ?";
        $results = sqlQ($sql, [$this->id]);

        while ($row = sqlFetchArray($results)) {
            $this->previous_accidents[] = $row;
        }
    }

    function toString($html = false)
    {
        $string = "\n" . "ID: " . $this->id . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }
    function set_id($id)
    {
        if (!empty($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }
    function get_id()
    {
        return $this->id;
    }
    function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        }
    }
    function get_pid()
    {
        return $this->pid;
    }
    function set_activity($tf)
    {
        if (!empty($tf) && is_numeric($tf)) {
            $this->activity = $tf;
        }
    }
    function get_activity()
    {
        return $this->activity;
    }

    function get_date_of_onset_y()
    {
        $ymd = explode("-", $this->date_of_onset);
        return $ymd[0];
    }

    function set_date_of_onset_y($year)
    {
        if (is_numeric($year)) {
            $ymd = explode("-", $this->date_of_onset);
            $ymd[0] = $year;
            $this->date_of_onset = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }

    function get_date_of_onset_m()
    {
        $ymd = explode("-", $this->date_of_onset);
        return $ymd[1];
    }

    function set_date_of_onset_m($month)
    {
        if (is_numeric($month)) {
            $ymd = explode("-", $this->date_of_onset);
            $ymd[1] = $month;
            $this->date_of_onset = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }

    function get_date_of_onset_d()
    {
        $ymd = explode("-", $this->date_of_onset);
        return $ymd[2];
    }

    function set_date_of_onset_d($day)
    {
        if (is_numeric($day)) {
            $ymd = explode("-", $this->date_of_onset);
            $ymd[2] = $day;
            $this->date_of_onset = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }

    function get_date_of_onset()
    {
        return $this->date_of_onset;
    }

    function set_date_of_onset($date)
    {
        return $this->date_of_onset = $date;
    }

    function set_event($event)
    {
        if (!is_numeric) {
            return;
        }

        $this->event = $event;
    }

    function get_event()
    {
        return $this->event;
    }

    function set_referred_by($string)
    {
        $this->referred_by = $string;
    }

    function get_referred_by()
    {
        return $this->referred_by;
    }

    function set_complaints($string)
    {
        $this->complaints = $string;
    }

    function get_complaints()
    {
        return $this->complaints;
    }

    function set_prior_symptoms($string)
    {
        $this->prior_symptoms = $string;
    }

    function get_prior_symptoms()
    {
        return $this->prior_symptoms;
    }

    function set_aggravated_symptoms($string)
    {
        $this->aggravated_symptoms = $string;
    }

    function get_aggravated_symptoms()
    {
        return $this->aggravated_symptoms;
    }

    function set_comments($string)
    {
        $this->comments = $string;
    }

    function get_comments()
    {
        return $this->comments;
    }

    function set_event_description($description)
    {
        $this->event_description = $description;
    }

    function get_event_description()
    {
        return $this->event_description;
    }
    function get_teeth_sore_number()
    {
        return $this->teeth_sore_number;
    }

    function set_teeth_sore_number($num)
    {
        $this->teeth_sore_number = $num;
    }

    function get_teeth_mobile_number()
    {
        return $this->teeth_mobile_number;
    }

    function set_teeth_mobile_number($num)
    {
        $this->teeth_mobile_number = $num;
    }

    function get_teeth_fractured_number()
    {
        return $this->teeth_fractured_number;
    }

    function set_teeth_fractured_number($num)
    {
        $this->teeth_fractured_number = $num;
    }

    function get_teeth_avulsed_number()
    {
        return $this->teeth_avulsed_number;
    }

    function set_teeth_avulsed_number($num)
    {
        $this->teeth_avulsed_number = $num;
    }

    function get_precipitating_factors_other_text()
    {
        return $this->precipitating_factors_other_text;
    }

    function set_precipitating_factors_other_text($string)
    {
        $this->precipitating_factors_other_text = $string;
    }

    function get_checks()
    {
        return $this->checks;
    }

    function set_checks($check_array)
    {
        $this->checks = $check_array;
    }

    function get_history()
    {
        return $this->history;
    }

    function set_history($array)
    {
        $this->history = $array;
    }

    function get_previous_accidents()
    {
        return $this->previous_accidents;
    }

    function set_previous_accidents($array)
    {
        $this->previous_accidents = $array;
    }

    function get_date()
    {
        return $this->date;
    }


    function persist()
    {

        parent::persist();
        if (is_numeric($this->id) and !empty($this->checks)) {
            $sql = "delete FROM form_hp_tje_checks where foreign_id = ?";
            sqlQuery($sql, [$this->id]);
            foreach ($this->checks as $check) {
                if (!empty($check)) {
                    $sql = "INSERT INTO form_hp_tje_checks set foreign_id=?, name = ?";
                    sqlQuery($sql, [$this->id, $check]);
                    //echo "$sql<br />";
                }
            }
        }

        if (is_numeric($this->id) and !empty($this->history)) {
            $sql = "delete FROM form_hp_tje_history where foreign_id = ?";
            sqlQuery($sql, [$this->id]);
            foreach ($this->history as $history) {
                if (!empty($history)) {
                    $sql = "INSERT INTO form_hp_tje_history set foreign_id=?"
                    . ", doctor = ?"
                    . ", specialty = ?"
                    . ", tx_rendered = ?"
                    . ", effectiveness = ?"
                    . ", date = ?";
                    sqlQuery(
                        $sql,
                        [
                            $this->id,
                            $history['doctor'],
                            $history['specialty'],
                            $history['tx_rendered'],
                            $history['effectiveness'],
                            $history['date']
                        ]
                    );
                    //echo "$sql<br />";
                }
            }
        }

        if (is_numeric($this->id) and !empty($this->previous_accidents)) {
            $sql = "delete FROM form_hp_tje_previous_accidents where foreign_id = ?";
            sqlQuery($sql, [$this->id]);

            foreach ($this->previous_accidents as $pa) {
                if (!empty($pa)) {
                    $sql = "INSERT INTO form_hp_tje_previous_accidents set foreign_id=?" .
                    ", nature_of_accident = ?"
                    . ", injuries = ?"
                    . ", date = ?";

                    sqlQuery(
                        $sql,
                        [
                            $this->id,
                            $pa['nature_of_accident'],
                            $pa['injuries'],
                            $pa['date']
                        ]
                    );
                    //echo "$sql<br />";
                }
            }
        }
    }

    function _form_layout()
    {
        $a = array();

        //at is array temp
        //a is array
        //a_bottom is the textually identified rows of a checkbox group

        $at[1]['headache_facial_pain_frontal']  =  "Frontal";
        $at[1]['headache_facial_pain_frontal_l']    =  "L";
        $at[1]['headache_facial_pain_frontal_r']    =  "R";
        $at[1]['headache_facial_pain_temporal']     =  "Temporal";
        $at[1]['headache_facial_pain_temporal_l']   =  "L";
        $at[1]['headache_facial_pain_temporal_r']   =  "R";
        $at[1]['headache_facial_pain_retro_orbtal']     =  "Retro-Orbital";
        $at[1]['headache_facial_pain_retro_orbtal_l']   =  "L";
        $at[1]['headache_facial_pain_retro_orbtal_r']   =  "R";
        $at[1]['headache_facial_pain_zygoma']       =  "Zygoma";
        $at[1]['headache_facial_pain_zygoma_l']     =  "L";
        $at[1]['headache_facial_pain_zygoma_r']     =  "R";

        $at[2]['headache_facial_pain_crown']        =  "Crown";
        $at[2]['headache_facial_pain_crown_l']  =  "L";
        $at[2]['headache_facial_pain_crown_r']  =  "R";
        $at[2]['headache_facial_pain_occipital']    =  "Occipital";
        $at[2]['headache_facial_pain_occipital_l'] =  "L";
        $at[2]['headache_facial_pain_occipital_r'] =  "R";
        $at[2]['headache_facial_pain_mastoid']  =  "Mastoid";
        $at[2]['headache_facial_pain_mastoid_l']    =  "L";
        $at[2]['headache_facial_pain_mastoid_r']    =  "R";
        $at[2]['headache_facial_pain_jaw_muscles']      =  "Jaw Muscles";
        $at[2]['headache_facial_pain_jaw_muscles_l']    =  "L";
        $at[2]['headache_facial_pain_jaw_muscles_r']    =  "R";

        $a_bottom = $this->_name_rows("headache_facial_pain", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Headaches / Facial Pain'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['neck_pain_anterior']    =  "Anterior";
        $at[1]['neck_pain_anterior_l']  =  "L";
        $at[1]['neck_pain_anterior_r']  =  "R";
        $at[1]['neck_pain_posterior']   =  "Posterior";
        $at[1]['neck_pain_posterior_l']     =  "L";
        $at[1]['neck_pain_posterior_r']     =  "R";
        $at[1]['neck_pain_radiating_to_head']   =  "Radiating to Head";
        $at[1]['neck_pain_radiating_to_head_l']     =  "L";
        $at[1]['neck_pain_radiating_to_head_r']     =  "R";

        $a_bottom = $this->_name_rows("neck_pain", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Neck Pain'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['shoulder_back_or_chest_shoulder']   =  "Shoulder";
        $at[1]['shoulder_back_or_chest_shoulder_l']     =  "L";
        $at[1]['shoulder_back_or_chest_shoulder_r']     =  "R";
        $at[1]['shoulder_back_or_chest_back_upper']     =  "Back/Upper";
        $at[1]['shoulder_back_or_chest_back_upper_l']   =  "L";
        $at[1]['shoulder_back_or_chest_back_upper_r']   =  "R";
        $at[1]['shoulder_back_or_chest_back_lower']     =  "Back/Lower";
        $at[1]['shoulder_back_or_chest_back_lower_l']   =  "L";
        $at[1]['shoulder_back_or_chest_back_lower_r']   =  "R";
        $at[1]['shoulder_back_or_chest_chest']  =  "Chest";
        $at[1]['shoulder_back_or_chest_chest_l']    =  "L";
        $at[1]['shoulder_back_or_chest_chest_r']    =  "R";
        $at[1]['shoulder_back_or_radiating_to_arm_hand']    =  "Radiating to arm/hand";
        $at[1]['shoulder_back_or_radiating_to_arm_hand_l']  =  "L";
        $at[1]['shoulder_back_or_radiating_to_arm_hand_r']  =  "R";

        $a_bottom = $this->_name_rows("shoulder_back_or_chest", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Shoulder, Back or Chest Pain'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['ear_symptoms_pain']     =  "Pain";
        $at[1]['ear_symptoms_pain_l']   =  "L";
        $at[1]['ear_symptoms_pain_r']   =  "R";
        $at[1]['ear_symptoms_tinnitus']     =  "Tinnitus";
        $at[1]['ear_symptoms_tinnitus_l']   =  "L";
        $at[1]['ear_symptoms_tinnitus_r']   =  "R";
        $at[1]['ear_symptoms_stuffiness']   =  "Stuffiness";
        $at[1]['ear_symptoms_stuffiness_l']     =  "L";
        $at[1]['ear_symptoms_stuffiness_r']     =  "R";
        $at[1]['ear_symptoms_hearing_loss']     =  "Hearing Loss";
        $at[1]['ear_symptoms_hearing_loss_l']   =  "L";
        $at[1]['ear_symptoms_hearing_loss_r']   =  "R";

        $a_bottom = $this->_name_rows("ear_symptoms", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Ear Symptoms'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['eye_symptoms_pain']     =  "Pain";
        $at[1]['eye_symptoms_pain_l']   =  "L";
        $at[1]['eye_symptoms_pain_r']   =  "R";
        $at[1]['eye_symptoms_burning']  =  "Burning";
        $at[1]['eye_symptoms_burning_l']    =  "L";
        $at[1]['eye_symptoms_burning_r']    =  "R";
        $at[1]['eye_symptoms_tearing']  =  "Tearing";
        $at[1]['eye_symptoms_tearing_l']    =  "L";
        $at[1]['eye_symptoms_tearing_r']    =  "R";
        $at[1]['eye_symptoms_change_in_vision']     =  "Change in Vision";
        $at[1]['eye_symptoms_change_in_vision_l']   =  "L";
        $at[1]['eye_symptoms_change_in_vision_r']   =  "R";
        $at[1]['eye_symptoms_bluriness']    =  "Bluriness";
        $at[1]['eye_symptoms_bluriness_l']  =  "L";
        $at[1]['eye_symptoms_bluriness_r']  =  "R";

        $a_bottom = $this->_name_rows("eye_symptoms", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Eye Symptoms'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['teeth_sore']    =  "Sore";
        $at[1]['teeth_mobile']  =  "Mobile";
        $at[1]['teeth_fractured']   =  "Fractured";
        $at[1]['teeth_avulsed']     =  "Avulsed";
        //special actions are included for teeth in the template

        $a_bottom = $this->_name_rows("teeth", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['Teeth'] = array_merge($at, $a_bottom);

        $a['Change in Bite'] = $this->_name_rows("change_in_bite", array("onset","intensity"));

        $at = array();
        $a_bottom = array();
        $at[1]['tmj_pain_l']    =  "L";
        $at[1]['tmp_pain_r']    =  "R";

        $a_bottom = $this->_name_rows("tmj_pain", array("onset","intensity","duration","frequency","quality of pain","aggravation","occurance"));
        $a['TMJ Pain'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['tmj_clicking_crepitation_clicking']     =  "Clicking";
        $at[1]['tmj_clicking_crepitation_clicking_l']   =  "L";
        $at[1]['tmj_clicking_crepitation_clicking_r']   =  "R";
        $at[1]['tmj_clicking_crepitation_crepitation']  =  "Crepitation";
        $at[1]['tmj_clicking_crepitation_crepitation_l']    =  "L";
        $at[1]['tmj_clicking_crepitation_crepitation_r']    =  "R";

        $a_bottom = $this->_name_rows("tmj_clicking_crepitation", array("onset","intensity","frequency","aggravation"));
        $a['TMJ Clicking / Crepitation'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['tmj_catching_locking_catching']     =  "Catching";
        $at[1]['tmj_catching_locking_catching_l']   =  "L";
        $at[1]['tmj_catching_locking_catching_r']   =  "R";
        $at[1]['tmj_catching_locking_locking_closed']   =  "Locking Closed";
        $at[1]['tmj_catching_locking_locking_closed_l']     =  "L";
        $at[1]['tmj_catching_locking_locking_closed_r']     =  "R";
        $at[1]['tmj_catching_locking_locking_open']     =  "Locking Open";
        $at[1]['tmj_catching_locking_locking_open_l']   =  "L";
        $at[1]['tmj_catching_locking_locking_open_r']   =  "R";

        $a_bottom = $this->_name_rows("tmj_catching_locking", array("onset","intensity","frequency","aggravation"));
        $a['TMJ Catching / Locking'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['tmj_chewing_swallowing_difficult']  =  "Difficult";
        $at[1]['tmj_chewing_swallowing_painful']    =  "Painful";

        $a_bottom = $this->_name_rows("tmj_chewing_swallowing", array("onset","intensity"));
        $a['TMJ Chewing / Swallowing'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['sinus_pain']    =  "Pain";
        $at[1]['sinus_pressure']    =  "Pressure";
        $at[1]['sinus_drainage']    =  "Drainage";
        $at[1]['sinus_infection']   =  "Infection";

        $a_bottom = $this->_name_rows("sinus", array("onset"));
        $a['Sinus'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['migraine_headache_aura']    =  "Aura";
        $at[1]['migraine_headache_nausea']  =  "Nausea";
        $at[1]['migraine_headache_relieved_by_vascular_drugs']  =  "Relieved by Vascular Drugs";
        $at[1]['migraine_headache_vertigo']     =  "Vertigo";

        $a_bottom = $this->_name_rows("migraine_headache", array("onset","intensity","duration","frequency","aggravation"));
        $a['Migraine Headache'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['dizziness_loss_of_balance']     =  "Loss of Balance";
        $at[1]['dizziness_vertigo']     =  "Vertigo";
        $at[1]['dizziness_spatial_distortion']  =  "Spatial Distortion";
        $at[1]['dizziness_syncope']     =  "Syncope";
        $at[1]['dizziness_nausea']  =  "Nausea";

        $a_bottom = $this->_name_rows("dizziness", array("onset","intensity","duration","frequency","aggravation"));
        $a['Dizziness'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['neuralgia_tic_doloreau']    =  "Tic Doloreau";
        $at[1]['neuralgia_tic_doloreau_l']  =  "L";
        $at[1]['neuralgia_tic_doloreau_r']  =  "R";
        $at[1]['neuralgia_parasthesis']     =  "Parasthesis";
        $at[1]['neuralgia_parasthesis_l']   =  "L";
        $at[1]['neuralgia_parasthesis_r']   =  "R";
        $at[1]['neuralgia_numbness']    =  "Numbness";
        $at[1]['neuralgia_numbness_l']  =  "L";
        $at[1]['neuralgia_numbness_r']  =  "R";

        $at[2]['neuralgia_cold_spots']  =  "\"Cold Spots\"";
        $at[2]['neuralgia_cold_spots_l']    =  "L";
        $at[2]['neuralgia_cold_spots_r']    =  "R";
        $at[2]['neuralgia_burning_tungue_lips_mouth']   =  "Burning Lips/Tongue/Mouth";
        $at[2]['neuralgia_burning_tungue_lips_mouth_l']     =  "L";
        $at[2]['neuralgia_burning_tungue_lips_mouth_r']     =  "R";
        $at[2]['neuralgia_hyperalgesia']    =  "Hyperalgesia";
        $at[2]['neuralgia_hyperalgesia_l']  =  "L";
        $at[2]['neuralgia_hyperalgesia_r']  =  "R";

        $a_bottom = $this->_name_rows("neuralgia", array("onset","intensity","duration","frequency","aggravation"));
        $a['Neuralgia'] = array_merge($at, $a_bottom);

        $at = array();
        $a_bottom = array();
        $at[1]['history_digenerative_joint_disease']    =  "Degenerative Joint Disease";
        $at[1]['history_rheumatoid_arthritis']  =  "Rheumatoid Arthritis";
        $at[1]['history_psioratic_arthritis']   =  "Psioratic Arthiritis";

        $at[2]['history_lupus_erythmatosis']    =  "Lupus Erythmatosis";
        $at[2]['history_scleroderma']   =  "Scleroderma";
        $at[2]['history_other']     =  "Other";

        $a['History'] = $at;

        $at = array();
        $a_bottom = array();
        $at[1]['precipitating_factors_direct_trauma']   =  "Direct Trauma";
        $at[1]['precipitating_factors_airbag']  =  "Airbag";
        $at[1]['precipitating_factors_whiplash']    =  "Whiplash";
        $at[1]['precipitating_factors_biting_on_foreign_object']    =  "Biting on Foreign Object";

        $at[2]['precipitating_factors_intubation']  =  "Intubation";
        $at[2]['precipitating_factors_forced_hypertranslation']     =  "Forced Hypertranslation";
        $at[2]['precipitating_factors_medication']  =  "Medication (Phenothiazines,etc.)";
        $at[2]['precipitating_factors_other']   =  "Other";

        $a['Precipitating Factors'] = $at;

        $at = array();
        $a_bottom = array();
        $at[1]['predisposing_factors_previous_injury_problem']  =  "Previous Injury/Problem";
        $at[1]['predisposing_factors_ligament_laxity']  =  "Ligament Laxity";
        $at[1]['predisposing_factors_deep_bite']    =  "Deep Bite";
        $at[1]['predisposing_factors_midline_division']     =  "Midline Division";

        $at[2]['predisposing_factors_loss_of_posterior_support']    =  "Loss of Posterior Support";
        $at[2]['predisposing_factors_mandibular_retrusion']     =  "Mandibular Retrusion";
        $at[2]['predisposing_factors_occlusal_alterations']     =  "Occlusal Alterations";
        $at[2]['predisposing_factors_clenching_bruxing']    =  "Clenching/Bruxing";

        $a['Predisposing Factors'] = $at;

        $at = array();
        $a_bottom = array();
        $at[1]['perpetuating_factors_previous_injury_problem']  =  "Previous Injury/Problem";
        $at[1]['perpetuating_factors_ligament_laxity']  =  "Ligament Laxity";
        $at[1]['perpetuating_factors_deep_bite']    =  "Deep Bite";
        $at[1]['perpetuating_factors_midline_division']     =  "Midline Division";

        $at[2]['perpetuating_factors_loss_of_posterior_support']    =  "Loss of Posterior Support";
        $at[2]['perpetuating_factors_mandibular_retrusion']     =  "Mandibular Retrusion";
        $at[2]['perpetuating_factors_occlusal_alterations']     =  "Occlusal Alterations";
        $at[2]['perpetuating_factors_clenching_bruxing']    =  "Clenching/Bruxing";

        $a['Perpetuating Factors'] = $at;

        return $a;
    }

    function _name_rows($name, $row_array)
    {
        $a = array();
        foreach ($row_array as $row) {
            switch (strtolower($row)) {
                case "onset":
                    $a["Onset"][$name . '_onset_precipitated_by_accident']      =  "Precipitated By Accident";
                    $a["Onset"][$name . '_onset_aggravated_by_accident']    =  "Aggravated By Accident";
                    $a["Onset"][$name . '_onset_pre_existing']  =  "Pre-existing";
                    $a["Onset"][$name . '_onset_other']     =  "Other";
                    break;
                case "intensity":
                    $a["Intensity"][$name . '_intensity_mild'] =  "Mild";
                    $a["Intensity"][$name . '_intensity_moderate'] =  "Moderate";
                    $a["Intensity"][$name . '_intensity_moderately_severe'] =  "Moderately Severe";
                    $a["Intensity"][$name . '_intensity_severe'] =  "Severe";
                    break;
                case "duration":
                    $a["Duration"][$name . '_duration_minutes'] =  "Minutes";
                    $a["Duration"][$name . '_duration_hours'] =  "Hours";
                    $a["Duration"][$name . '_duration_days'] =  "Days";
                    $a["Duration"][$name . '_duration_constant'] =  "Constant";
                    break;
                case "frequency":
                    $a["Frequency"][$name . '_frequency_no_pattern'] =  "No Pattern";
                    $a["Frequency"][$name . '_frequency_1_week'] =  "1/Week";
                    $a["Frequency"][$name . '_frequency_2_3_week'] =  "2-3/Week";
                    $a["Frequency"][$name . '_frequency_daily'] =  "Daily";
                    break;
                case "quality of pain":
                    $a["Quality of Pain"][$name . '_quality_of_pain_dull'] =  "Dull";
                    $a["Quality of Pain"][$name . '_quality_of_pain_deep'] =  "Deep";
                    $a["Quality of Pain"][$name . '_quality_of_pain_aching'] =  "Aching";
                    $a["Quality of Pain"][$name . '_quality_of_pain_triggered'] =  "Triggered";
                    break;
                case "aggravation":
                    $a["Aggravation"][$name . '_aggravation_chewing'] =  "Chewing";
                    $a["Aggravation"][$name . '_aggravation_speaking'] =  "Speaking";
                    $a["Aggravation"][$name . '_aggravation_clenching'] =  "Clenching";
                    $a["Aggravation"][$name . '_aggravation_physical_activity'] =  "Physical Activity";
                    break;
                case "occurance":
                    $a["Occurance"][$name . '_occurance_at_walking'] =  "At Walking";
                    $a["Occurance"][$name . '_occurance_mid_day'] =  "Mid Day";
                    $a["Occurance"][$name . '_occurance_evening'] =  "Evening";
                    $a["Occurance"][$name . '_occurance_variable'] =  "Variable";
                    break;
            }
        }

        return $a;
    }
}   // end of Form
