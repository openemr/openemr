<?php

/**
 * class FormEvaluation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class FormEvaluation extends ORDataObject
{
    /**
     *
     * @access private
     */

    var $id;
    var $temp;
    var $p;
    var $r;
    var $bp;
    var $ht;
    var $wt;
    var $bmi;
    var $lmp;
    var $complaint;
    var $hpi;
    var $eyes_od;
    var $eyes_os;
    var $eyes_ou;
    var $comments;
    var $assesment;
    var $pid;
    var $activity;
    var $date;
    var $checks;

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

        $this->_table = "form_evaluation";
        $this->date = date("Y-m-d H:i:s");
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

        $sql = "SELECT name from form_evaluation_checks where foreign_id = ?";
        $results = sqlQ($sql, array($this->id));

        while ($row = sqlFetchArray($results)) {
            $this->checks[] = $row['name'];
        }
    }

    /**
     * @param bool $html
     * @return string
     */
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

    function get_date()
    {
        return $this->date;
    }

    function set_temp($string)
    {
        $this->temp = $string;
    }

    function get_temp()
    {
        return $this->temp;
    }

    function set_p($string)
    {
        $this->p = $string;
    }

    function get_p()
    {
        return $this->p;
    }

    function set_r($string)
    {
        $this->r = $string;
    }

    function get_r()
    {
        return $this->r;
    }

    function set_bp($string)
    {
        $this->bp = $string;
    }

    function get_bp()
    {
        return $this->bp;
    }

    function set_ht($string)
    {
        $this->ht = $string;
    }

    function get_ht()
    {
        return $this->ht;
    }

    function set_wt($string)
    {
        $this->wt = $string;
    }

    function get_wt()
    {
        return $this->wt;
    }

    function set_bmi($string)
    {
        $this->bmi = $string;
    }

    function get_bmi()
    {
        return $this->bmi;
    }

    function set_lmp($string)
    {
        $this->lmp = $string;
    }

    function get_lmp()
    {
        return $this->lmp;
    }

    function set_complaint($string)
    {
        $this->complaint = $string;
    }

    function get_complaint()
    {
        return $this->complaint;
    }

    function set_hpi($string)
    {
        $this->hpi = $string;
    }

    function get_hpi()
    {

        return $this->hpi;
    }

    function set_eyes_od($string)
    {
        $this->eyes_od = $string;
    }

    function get_eyes_od()
    {
        return $this->eyes_od;
    }

    function set_eyes_os($string)
    {
        $this->eyes_os = $string;
    }

    function get_eyes_os()
    {
        return $this->eyes_os;
    }

    function set_eyes_ou($string)
    {
        $this->eyes_ou = $string;
    }

    function get_eyes_ou()
    {
        return $this->eyes_ou;
    }

    function set_comments($string)
    {
        $this->comments = $string;
    }

    function get_comments()
    {
        return $this->comments;
    }

    function set_assesment($string)
    {
        $this->assesment = $string;
    }

    function get_assesment()
    {
        return $this->assesment;
    }

    function get_checks()
    {
        return $this->checks;
    }

    function set_checks($check_array)
    {
        $this->checks = $check_array;
    }

    function persist()
    {

        parent::persist();
        if (is_numeric($this->id) and !empty($this->checks)) {
            $sql = "delete FROM form_evaluation_checks WHERE foreign_id = ?";
            sqlQuery($sql, array($this->id));
            foreach ($this->checks as $check) {
                if (!empty($check)) {
                    $sql = "INSERT INTO form_evaluation_checks set foreign_id= ?, name = ?";
                    sqlQuery($sql, array($this->id, $check));
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
        //a_bottom is the textually identified rows of a checkbox group, removed from code since not used yet

        $at[1]['appearance_normal_development']     =  "Normal Developement";
        $at[1]['appearance_normal_body_habitus']    =  "Normal Body Habitus";
        $at[1]['appearance_well_groomed']   =  "Well Groomed";

        $a['General Appearance'] = $at;

        $at = array();
        $at[1]['eyes_conjunctiva_lids_nl']  =  "Conjunctiva and Lids NL";
        $at[1]['eyes_pupils_iris_nl']   =  "Pupils and Iris' NL";
        $at[1]['eyes_optic_disks_nl']   =  "Optic Disks NL";
        $at[1]['eyes_visual_fields_full_to_confrontation']  =  "Visual Fields Full to Confrontation";

        $at[2]['eyes_extra_occular_movement_intact']    =  "Eyes Extra Occular Movement Intact";
        $at[2]['eyes_with_corrective_lenses']   =  "With Corrective Lenses";
        $at[2]['eyes_without_corrective_lenses']    =  "Without Corrective Lenses";

        $a['Eyes'] = $at;

        $at = array();
        $at[1]['ent_external_ears_nose_nl']     =  "External Ears and Nose NL";
        $at[1]['ent_external_auditory_canals_nl']   =  "External Auditory Canals NL";
        $at[1]['ent_tm_nl']     =  "TM's NL";
        $at[1]['ent_hearing_nl_to_confrontation']   =  "Hearing NL to confrontation";

        $a['ENT'] = $at;

        $at = array();
        $at[1]['neck_no_neck_masses']   =  "No neck masses";
        $at[1]['neck_symetrical']   =  "Symetrical";
        $at[1]['neck_trachea_midline']  =  "Trachea Midline";
        $at[1]['neck_thyroid_nl']   =  "Thyroid NL";

        $a['Neck'] = $at;

        $at = array();
        $at[1]['respiratory_nl_effort']     =  "NL Effort";
        $at[1]['respiratory_no_dullness_to_percussion']     =  "No Dullness to Percussion";
        $at[1]['respiratory_no_rhonci_or_rails']    =  "No Rhonci or Rails";
        $at[1]['respiratory_no_weezing']    =  "No Wheezing";

        $a['Respiratory'] = $at;

        $at = array();
        $at[1]['cv_no_lifts_or_thrills']    =  "No Lifts or Thrills";
        $at[1]['cv_pmi_fifth_ic_mc_line']   =  "PMI 5th IC MC Line";
        $at[1]['cv_nl_s1_s2']   =  "NL S1/S2";
        $at[1]['cv_regular_rhythm']     =  "Regular Rhythm";

        $at[2]['cv_carotids_without_bruits']    =  "Carotids without bruits";
        $at[2]['cv_no_jvd']                     =  "No JVD";
        $at[2]['cv_no_lower_extremity_edema']   =  "No Lower Extremity Edema";

        $a['CV'] = $at;

        $at = array();
        $at[1]['gastrointestinal_abdomen_soft_nontender']   =  "Abdomen soft, nontender";
        $at[1]['gastrointestinal_no_fluid']     =  "No Fluid";
        $at[1]['gastrointestinal_no_hepatosplenomegally']   =  "No HepatoSplenomegally";
        $at[1]['gastrointestinal_no_hernia']    =  "No Hernia";
        $at[1]['gastrointestinal_guiac_negative']   =  "Guiac Negative";

        $a['GastroIntestinal'] = $at;

        $at = array();
        $at[1]['gu_male_scrotum_testis_nl']     =  "Scrotum and Testis NL";
        $at[1]['gu_male_penis_nl_without_legions']  =  "Penis NL, Without legions";
        $at[1]['gu_male_prostate_nl']   =  "Prostate NL";
        $at[1]['gu_male_no_penile_disharge']    =  "No Penile Discharge";

        $a['GU (male)'] = $at;

        $at = array();
        $at[1]['gu_female_external_genitalia_nl']   =  "External Genitalia NL";
        $at[1]['gu_female_urethra_nl']  =  "Urethra NL";
        $at[1]['gu_female_bladder_nl']  =  "Bladder NL";
        $at[1]['gu_female_cervix_without_discharge']    =  "Cervix without discharge";

        $at[2]['gu_female_no_cervical_motion_tenderness']   =  "No Cervical Motion Tenderness";
        $at[2]['gu_female_uterus_nl_size']  =  "Uterus NL size";
        $at[2]['gu_female_no_adnexal_masses']   =  "No Adnexal Masses";

        $a['GU (female)'] = $at;

        $at = array();
        $at[1]['lymphatics_no_cervical_nodes_palpable']     =  "No Cervical nodes Palpable";
        $at[1]['lymphatics_no_axillary_nodes_palpable']     =  "No Axillary Nodes Palpable";
        $at[1]['lymphatics_no_inguinal_nodes_palpable']     =  "No Inguinal Nodes Palpable";

        $a['Lymphatics'] = $at;

        $at = array();
        $at[1]['musculoskeletal_gait_normal']   =  "Gait Normal";
        $at[1]['musculoskeletal_nl_symmetry']   =  "NL Symmetry";
        $at[1]['musculoskeletal_nl_tone']   =  "NL Tone";
        $at[1]['musculoskeletal_nl_rom']    =  "NL ROM";

        $at[2]['musculoskeletal_no_instability']    =  "No Instability";
        $at[2]['musculoskeletal_normal_strength_five_five']     =  "Normal Strength 5/5";

        $a['Musculoskeletal'] = $at;

        $at = array();
        $at[1]['neurologic_alert_oriented_xthree']  =  "Alert and Oriented x3";
        $at[1]['neurologic_normal_memory']  =  "Normal Memory";
        $at[1]['neurologic_cn_ii_xii_intact_to_confrontation']  =  "CN II-XII Intact to Confrontation";

        $at[2]['neurologic_reflexes_five_five_bicipital_tendon']    =  "Reflexes 5/5 Bicipital Tendon";
        $at[2]['neurologic_reflexes_five_five_tricipital_tendon']   =  "Reflexes 5/5 Tricipital Tendon";
        $at[2]['neurologic_reflexes_five_five_patellar_tendon']     =  "Reflexes 5/5 Patellar Tendon";

        $at[3]['neurologic_reflexes_five_five_achillies_tendon']    =  "Reflexes 5/5 Achillies Tendon";
        $at[3]['neurologic_toes_downgoing_bilaterally']     =  "Toes Downgoing Bilaterally";
        $at[3]['neurologic_sensation_nl_to_light_touch']    =  "Sensation NL to light touch";

        $at[4]['neurologic_sensation_nl_to_hot_cold']   =  "Sensation NL to Hot/Cold";
        $at[4]['neurologic_sensation_nl_to_one_one_point_discrimination']   =  "Sensation NL to 1-1 point discrimination";
        $at[4]['neurologic_speech_is_appropriate']  =  "Speech is appropriate";

        $a['Neurologic'] = $at;

        $at = array();
        $at[1]['psychiatric_nl_judgement_insight']  =  "NL Judgement/Insight";
        $at[1]['psychiatric_nl_mood']   =  "NL Mood";
        $at[1]['psychiatric_nl_affect']     =  "NL Affect";
        $at[1]['psychiatric_no_suicidal_ideation']  =  "No Suicidal Ideation";
        $at[1]['psychiatric_normal_eye_contact']    =  "Normal Eye Contact";

        $a['Psychiatric'] = $at;


        return $a;
    }
}   // end of Form
