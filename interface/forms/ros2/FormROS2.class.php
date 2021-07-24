<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */
class FormROS2 extends ORDataObject
{

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
            $this->date = date("Y-m-d H:i:s");
        }

        $this->_table = "form_ros2";
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
        if ($id != "") {
            $this->populate();
            //$this->date = $this->get_date();
        }
    }
    function populate()
    {
        parent::populate();
        //$this->temp_methods = parent::_load_enum("temp_locations",false);
    }

    function __toString()
    {
        return "ID: " . $this->id . "\n";
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
    function set_date($dt)
    {
        if (!empty($dt)) {
            $this->date = $dt;
        }
    }
    function get_user()
    {
        return $this->user;
    }
    function set_user($u)
    {
        if (!empty($u)) {
            $this->user = $u;
        }
    }

    function persist()
    {
        parent::persist();
    }



    // ----- headache -----

    var $general_headache;
    var $general_headache_text;
    function get_general_headache()
    {
        return $this->general_headache;
    }
    function get_general_headache_yes()
    {
        return $this->general_headache == "Yes" ? "CHECKED" : "";
    }
    function get_general_headache_no()
    {
        return $this->general_headache == "No" ? "CHECKED" : "";
    }
    function set_general_headache($data)
    {
        if (!empty($data)) {
            $this->general_headache = $data;
        }
    }
    function get_general_headache_text()
    {
        return $this->general_headache_text;
    }
    function set_general_headache_text($data)
    {
        if (!empty($data)) {
            $this->general_headache_text = $data;
        }
    }


    var $general_fever;
    var $general_fever_text;
    function get_general_fever()
    {
        return $this->general_fever;
    }
    function get_general_fever_yes()
    {
        return $this->general_fever == "Yes" ? "CHECKED" : "";
    }
    function get_general_fever_no()
    {
        return $this->general_fever == "No" ? "CHECKED" : "";
    }
    function set_general_fever($data)
    {
        if (!empty($data)) {
            $this->general_fever = $data;
        }
    }
    function get_general_fever_text()
    {
        return $this->general_fever_text;
    }
    function set_general_fever_text($data)
    {
        if (!empty($data)) {
            $this->general_fever_text = $data;
        }
    }


    var $general_chills;
    var $general_chills_text;
    function get_general_chills()
    {
        return $this->general_chills;
    }
    function get_general_chills_yes()
    {
        return $this->general_chills == "Yes" ? "CHECKED" : "";
    }
    function get_general_chills_no()
    {
        return $this->general_chills == "No" ? "CHECKED" : "";
    }
    function set_general_chills($data)
    {
        if (!empty($data)) {
            $this->general_chills = $data;
        }
    }
    function get_general_chills_text()
    {
        return $this->general_chills_text;
    }
    function set_general_chills_text($data)
    {
        if (!empty($data)) {
            $this->general_chills_text = $data;
        }
    }


    var $general_body_aches;
    var $general_body_aches_text;
    function get_general_body_aches()
    {
        return $this->general_body_aches;
    }
    function get_general_body_aches_yes()
    {
        return $this->general_body_aches == "Yes" ? "CHECKED" : "";
    }
    function get_general_body_aches_no()
    {
        return $this->general_body_aches == "No" ? "CHECKED" : "";
    }
    function set_general_body_aches($data)
    {
        if (!empty($data)) {
            $this->general_body_aches = $data;
        }
    }
    function get_general_body_aches_text()
    {
        return $this->general_body_aches_text;
    }
    function set_general_body_aches_text($data)
    {
        if (!empty($data)) {
            $this->general_body_aches_text = $data;
        }
    }


    var $general_fatigue;
    var $general_fatigue_text;
    function get_general_fatigue()
    {
        return $this->general_fatigue;
    }
    function get_general_fatigue_yes()
    {
        return $this->general_fatigue == "Yes" ? "CHECKED" : "";
    }
    function get_general_fatigue_no()
    {
        return $this->general_fatigue == "No" ? "CHECKED" : "";
    }
    function set_general_fatigue($data)
    {
        if (!empty($data)) {
            $this->general_fatigue = $data;
        }
    }
    function get_general_fatigue_text()
    {
        return $this->general_fatigue_text;
    }
    function set_general_fatigue_text($data)
    {
        if (!empty($data)) {
            $this->general_fatigue_text = $data;
        }
    }


    var $general_loss_of_appetite;
    var $general_loss_of_appetite_text;
    function get_general_loss_of_appetite()
    {
        return $this->general_loss_of_appetite;
    }
    function get_general_loss_of_appetite_yes()
    {
        return $this->general_loss_of_appetite == "Yes" ? "CHECKED" : "";
    }
    function get_general_loss_of_appetite_no()
    {
        return $this->general_loss_of_appetite == "No" ? "CHECKED" : "";
    }
    function set_general_loss_of_appetite($data)
    {
        if (!empty($data)) {
            $this->general_loss_of_appetite = $data;
        }
    }
    function get_general_loss_of_appetite_text()
    {
        return $this->general_loss_of_appetite_text;
    }
    function set_general_loss_of_appetite_text($data)
    {
        if (!empty($data)) {
            $this->general_loss_of_appetite_text = $data;
        }
    }


    var $general_weight_loss;
    var $general_weight_loss_text;
    function get_general_weight_loss()
    {
        return $this->general_weight_loss;
    }
    function get_general_weight_loss_yes()
    {
        return $this->general_weight_loss == "Yes" ? "CHECKED" : "";
    }
    function get_general_weight_loss_no()
    {
        return $this->general_weight_loss == "No" ? "CHECKED" : "";
    }
    function set_general_weight_loss($data)
    {
        if (!empty($data)) {
            $this->general_weight_loss = $data;
        }
    }
    function get_general_weight_loss_text()
    {
        return $this->general_weight_loss_text;
    }
    function set_general_weight_loss_text($data)
    {
        if (!empty($data)) {
            $this->general_weight_loss_text = $data;
        }
    }


    var $general_daytime_drowsiness;
    var $general_daytime_drowsiness_text;
    function get_general_daytime_drowsiness()
    {
        return $this->general_daytime_drowsiness;
    }
    function get_general_daytime_drowsiness_yes()
    {
        return $this->general_daytime_drowsiness == "Yes" ? "CHECKED" : "";
    }
    function get_general_daytime_drowsiness_no()
    {
        return $this->general_daytime_drowsiness == "No" ? "CHECKED" : "";
    }
    function set_general_daytime_drowsiness($data)
    {
        if (!empty($data)) {
            $this->general_daytime_drowsiness = $data;
        }
    }
    function get_general_daytime_drowsiness_text()
    {
        return $this->general_daytime_drowsiness_text;
    }
    function set_general_daytime_drowsiness_text($data)
    {
        if (!empty($data)) {
            $this->general_daytime_drowsiness_text = $data;
        }
    }


    var $general_excessive_snoring;
    var $general_excessive_snoring_text;
    function get_general_excessive_snoring()
    {
        return $this->general_excessive_snoring;
    }
    function get_general_excessive_snoring_yes()
    {
        return $this->general_excessive_snoring == "Yes" ? "CHECKED" : "";
    }
    function get_general_excessive_snoring_no()
    {
        return $this->general_excessive_snoring == "No" ? "CHECKED" : "";
    }
    function set_general_excessive_snoring($data)
    {
        if (!empty($data)) {
            $this->general_excessive_snoring = $data;
        }
    }
    function get_general_excessive_snoring_text()
    {
        return $this->general_excessive_snoring_text;
    }
    function set_general_excessive_snoring_text($data)
    {
        if (!empty($data)) {
            $this->general_excessive_snoring_text = $data;
        }
    }

    // ----- disorientation -----

    var $neuro_disorientation;
    var $neuro_disorientation_text;
    function get_neuro_disorientation()
    {
        return $this->neuro_disorientation;
    }
    function get_neuro_disorientation_yes()
    {
        return $this->neuro_disorientation == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_disorientation_no()
    {
        return $this->neuro_disorientation == "No" ? "CHECKED" : "";
    }
    function set_neuro_disorientation($data)
    {
        if (!empty($data)) {
            $this->neuro_disorientation = $data;
        }
    }
    function get_neuro_disorientation_text()
    {
        return $this->neuro_disorientation_text;
    }
    function set_neuro_disorientation_text($data)
    {
        if (!empty($data)) {
            $this->neuro_disorientation_text = $data;
        }
    }


    var $neuro_loss_of_consciousness;
    var $neuro_loss_of_consciousness_text;
    function get_neuro_loss_of_consciousness()
    {
        return $this->neuro_loss_of_consciousness;
    }
    function get_neuro_loss_of_consciousness_yes()
    {
        return $this->neuro_loss_of_consciousness == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_loss_of_consciousness_no()
    {
        return $this->neuro_loss_of_consciousness == "No" ? "CHECKED" : "";
    }
    function set_neuro_loss_of_consciousness($data)
    {
        if (!empty($data)) {
            $this->neuro_loss_of_consciousness = $data;
        }
    }
    function get_neuro_loss_of_consciousness_text()
    {
        return $this->neuro_loss_of_consciousness_text;
    }
    function set_neuro_loss_of_consciousness_text($data)
    {
        if (!empty($data)) {
            $this->neuro_loss_of_consciousness_text = $data;
        }
    }


    var $neuro_numbness;
    var $neuro_numbness_text;
    function get_neuro_numbness()
    {
        return $this->neuro_numbness;
    }
    function get_neuro_numbness_yes()
    {
        return $this->neuro_numbness == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_numbness_no()
    {
        return $this->neuro_numbness == "No" ? "CHECKED" : "";
    }
    function set_neuro_numbness($data)
    {
        if (!empty($data)) {
            $this->neuro_numbness = $data;
        }
    }
    function get_neuro_numbness_text()
    {
        return $this->neuro_numbness_text;
    }
    function set_neuro_numbness_text($data)
    {
        if (!empty($data)) {
            $this->neuro_numbness_text = $data;
        }
    }


    var $neuro_tingling;
    var $neuro_tingling_text;
    function get_neuro_tingling()
    {
        return $this->neuro_tingling;
    }
    function get_neuro_tingling_yes()
    {
        return $this->neuro_tingling == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_tingling_no()
    {
        return $this->neuro_tingling == "No" ? "CHECKED" : "";
    }
    function set_neuro_tingling($data)
    {
        if (!empty($data)) {
            $this->neuro_tingling = $data;
        }
    }
    function get_neuro_tingling_text()
    {
        return $this->neuro_tingling_text;
    }
    function set_neuro_tingling_text($data)
    {
        if (!empty($data)) {
            $this->neuro_tingling_text = $data;
        }
    }


    var $neuro_restlessness;
    var $neuro_restlessness_text;
    function get_neuro_restlessness()
    {
        return $this->neuro_restlessness;
    }
    function get_neuro_restlessness_yes()
    {
        return $this->neuro_restlessness == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_restlessness_no()
    {
        return $this->neuro_restlessness == "No" ? "CHECKED" : "";
    }
    function set_neuro_restlessness($data)
    {
        if (!empty($data)) {
            $this->neuro_restlessness = $data;
        }
    }
    function get_neuro_restlessness_text()
    {
        return $this->neuro_restlessness_text;
    }
    function set_neuro_restlessness_text($data)
    {
        if (!empty($data)) {
            $this->neuro_restlessness_text = $data;
        }
    }


    var $neuro_dizziness;
    var $neuro_dizziness_text;
    function get_neuro_dizziness()
    {
        return $this->neuro_dizziness;
    }
    function get_neuro_dizziness_yes()
    {
        return $this->neuro_dizziness == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_dizziness_no()
    {
        return $this->neuro_dizziness == "No" ? "CHECKED" : "";
    }
    function set_neuro_dizziness($data)
    {
        if (!empty($data)) {
            $this->neuro_dizziness = $data;
        }
    }
    function get_neuro_dizziness_text()
    {
        return $this->neuro_dizziness_text;
    }
    function set_neuro_dizziness_text($data)
    {
        if (!empty($data)) {
            $this->neuro_dizziness_text = $data;
        }
    }


    var $neuro_vertigo;
    var $neuro_vertigo_text;
    function get_neuro_vertigo()
    {
        return $this->neuro_vertigo;
    }
    function get_neuro_vertigo_yes()
    {
        return $this->neuro_vertigo == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_vertigo_no()
    {
        return $this->neuro_vertigo == "No" ? "CHECKED" : "";
    }
    function set_neuro_vertigo($data)
    {
        if (!empty($data)) {
            $this->neuro_vertigo = $data;
        }
    }
    function get_neuro_vertigo_text()
    {
        return $this->neuro_vertigo_text;
    }
    function set_neuro_vertigo_text($data)
    {
        if (!empty($data)) {
            $this->neuro_vertigo_text = $data;
        }
    }


    var $neuro_amaurosis_fugax;
    var $neuro_amaurosis_fugax_text;
    function get_neuro_amaurosis_fugax()
    {
        return $this->neuro_amaurosis_fugax;
    }
    function get_neuro_amaurosis_fugax_yes()
    {
        return $this->neuro_amaurosis_fugax == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_amaurosis_fugax_no()
    {
        return $this->neuro_amaurosis_fugax == "No" ? "CHECKED" : "";
    }
    function set_neuro_amaurosis_fugax($data)
    {
        if (!empty($data)) {
            $this->neuro_amaurosis_fugax = $data;
        }
    }
    function get_neuro_amaurosis_fugax_text()
    {
        return $this->neuro_amaurosis_fugax_text;
    }
    function set_neuro_amaurosis_fugax_text($data)
    {
        if (!empty($data)) {
            $this->neuro_amaurosis_fugax_text = $data;
        }
    }


    var $neuro_stroke;
    var $neuro_stroke_text;
    function get_neuro_stroke()
    {
        return $this->neuro_stroke;
    }
    function get_neuro_stroke_yes()
    {
        return $this->neuro_stroke == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_stroke_no()
    {
        return $this->neuro_stroke == "No" ? "CHECKED" : "";
    }
    function set_neuro_stroke($data)
    {
        if (!empty($data)) {
            $this->neuro_stroke = $data;
        }
    }
    function get_neuro_stroke_text()
    {
        return $this->neuro_stroke_text;
    }
    function set_neuro_stroke_text($data)
    {
        if (!empty($data)) {
            $this->neuro_stroke_text = $data;
        }
    }


    var $neuro_gait_abnormality;
    var $neuro_gait_abnormality_text;
    function get_neuro_gait_abnormality()
    {
        return $this->neuro_gait_abnormality;
    }
    function get_neuro_gait_abnormality_yes()
    {
        return $this->neuro_gait_abnormality == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_gait_abnormality_no()
    {
        return $this->neuro_gait_abnormality == "No" ? "CHECKED" : "";
    }
    function set_neuro_gait_abnormality($data)
    {
        if (!empty($data)) {
            $this->neuro_gait_abnormality = $data;
        }
    }
    function get_neuro_gait_abnormality_text()
    {
        return $this->neuro_gait_abnormality_text;
    }
    function set_neuro_gait_abnormality_text($data)
    {
        if (!empty($data)) {
            $this->neuro_gait_abnormality_text = $data;
        }
    }


    var $neuro_frequent_headaches;
    var $neuro_frequent_headaches_text;
    function get_neuro_frequent_headaches()
    {
        return $this->neuro_frequent_headaches;
    }
    function get_neuro_frequent_headaches_yes()
    {
        return $this->neuro_frequent_headaches == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_frequent_headaches_no()
    {
        return $this->neuro_frequent_headaches == "No" ? "CHECKED" : "";
    }
    function set_neuro_frequent_headaches($data)
    {
        if (!empty($data)) {
            $this->neuro_frequent_headaches = $data;
        }
    }
    function get_neuro_frequent_headaches_text()
    {
        return $this->neuro_frequent_headaches_text;
    }
    function set_neuro_frequent_headaches_text($data)
    {
        if (!empty($data)) {
            $this->neuro_frequent_headaches_text = $data;
        }
    }


    var $neuro_parathesias;
    var $neuro_parathesias_text;
    function get_neuro_parathesias()
    {
        return $this->neuro_parathesias;
    }
    function get_neuro_parathesias_yes()
    {
        return $this->neuro_parathesias == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_parathesias_no()
    {
        return $this->neuro_parathesias == "No" ? "CHECKED" : "";
    }
    function set_neuro_parathesias($data)
    {
        if (!empty($data)) {
            $this->neuro_parathesias = $data;
        }
    }
    function get_neuro_parathesias_text()
    {
        return $this->neuro_parathesias_text;
    }
    function set_neuro_parathesias_text($data)
    {
        if (!empty($data)) {
            $this->neuro_parathesias_text = $data;
        }
    }


    var $neuro_seizures;
    var $neuro_seizures_text;
    function get_neuro_seizures()
    {
        return $this->neuro_seizures;
    }
    function get_neuro_seizures_yes()
    {
        return $this->neuro_seizures == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_seizures_no()
    {
        return $this->neuro_seizures == "No" ? "CHECKED" : "";
    }
    function set_neuro_seizures($data)
    {
        if (!empty($data)) {
            $this->neuro_seizures = $data;
        }
    }
    function get_neuro_seizures_text()
    {
        return $this->neuro_seizures_text;
    }
    function set_neuro_seizures_text($data)
    {
        if (!empty($data)) {
            $this->neuro_seizures_text = $data;
        }
    }


    var $neuro_trans_ischemic_attacks;
    var $neuro_trans_ischemic_attacks_text;
    function get_neuro_trans_ischemic_attacks()
    {
        return $this->neuro_trans_ischemic_attacks;
    }
    function get_neuro_trans_ischemic_attacks_yes()
    {
        return $this->neuro_trans_ischemic_attacks == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_trans_ischemic_attacks_no()
    {
        return $this->neuro_trans_ischemic_attacks == "No" ? "CHECKED" : "";
    }
    function set_neuro_trans_ischemic_attacks($data)
    {
        if (!empty($data)) {
            $this->neuro_trans_ischemic_attacks = $data;
        }
    }
    function get_neuro_trans_ischemic_attacks_text()
    {
        return $this->neuro_trans_ischemic_attacks_text;
    }
    function set_neuro_trans_ischemic_attacks_text($data)
    {
        if (!empty($data)) {
            $this->neuro_trans_ischemic_attacks_text = $data;
        }
    }


    var $neuro_significant_tremors;
    var $neuro_significant_tremors_text;
    function get_neuro_significant_tremors()
    {
        return $this->neuro_significant_tremors;
    }
    function get_neuro_significant_tremors_yes()
    {
        return $this->neuro_significant_tremors == "Yes" ? "CHECKED" : "";
    }
    function get_neuro_significant_tremors_no()
    {
        return $this->neuro_significant_tremors == "No" ? "CHECKED" : "";
    }
    function set_neuro_significant_tremors($data)
    {
        if (!empty($data)) {
            $this->neuro_significant_tremors = $data;
        }
    }
    function get_neuro_significant_tremors_text()
    {
        return $this->neuro_significant_tremors_text;
    }
    function set_neuro_significant_tremors_text($data)
    {
        if (!empty($data)) {
            $this->neuro_significant_tremors_text = $data;
        }
    }

    // ----- neck stiffness -----

    var $neck_neck_stiffness;
    var $neck_neck_stiffness_text;
    function get_neck_neck_stiffness()
    {
        return $this->neck_neck_stiffness;
    }
    function get_neck_neck_stiffness_yes()
    {
        return $this->neck_neck_stiffness == "Yes" ? "CHECKED" : "";
    }
    function get_neck_neck_stiffness_no()
    {
        return $this->neck_neck_stiffness == "No" ? "CHECKED" : "";
    }
    function set_neck_neck_stiffness($data)
    {
        if (!empty($data)) {
            $this->neck_neck_stiffness = $data;
        }
    }
    function get_neck_neck_stiffness_text()
    {
        return $this->neck_neck_stiffness_text;
    }
    function set_neck_neck_stiffness_text($data)
    {
        if (!empty($data)) {
            $this->neck_neck_stiffness_text = $data;
        }
    }


    var $neck_neck_pain;
    var $neck_neck_pain_text;
    function get_neck_neck_pain()
    {
        return $this->neck_neck_pain;
    }
    function get_neck_neck_pain_yes()
    {
        return $this->neck_neck_pain == "Yes" ? "CHECKED" : "";
    }
    function get_neck_neck_pain_no()
    {
        return $this->neck_neck_pain == "No" ? "CHECKED" : "";
    }
    function set_neck_neck_pain($data)
    {
        if (!empty($data)) {
            $this->neck_neck_pain = $data;
        }
    }
    function get_neck_neck_pain_text()
    {
        return $this->neck_neck_pain_text;
    }
    function set_neck_neck_pain_text($data)
    {
        if (!empty($data)) {
            $this->neck_neck_pain_text = $data;
        }
    }


    var $neck_neck_masses;
    var $neck_neck_masses_text;
    function get_neck_neck_masses()
    {
        return $this->neck_neck_masses;
    }
    function get_neck_neck_masses_yes()
    {
        return $this->neck_neck_masses == "Yes" ? "CHECKED" : "";
    }
    function get_neck_neck_masses_no()
    {
        return $this->neck_neck_masses == "No" ? "CHECKED" : "";
    }
    function set_neck_neck_masses($data)
    {
        if (!empty($data)) {
            $this->neck_neck_masses = $data;
        }
    }
    function get_neck_neck_masses_text()
    {
        return $this->neck_neck_masses_text;
    }
    function set_neck_neck_masses_text($data)
    {
        if (!empty($data)) {
            $this->neck_neck_masses_text = $data;
        }
    }


    var $neck_neck_tenderness;
    var $neck_neck_tenderness_text;
    function get_neck_neck_tenderness()
    {
        return $this->neck_neck_tenderness;
    }
    function get_neck_neck_tenderness_yes()
    {
        return $this->neck_neck_tenderness == "Yes" ? "CHECKED" : "";
    }
    function get_neck_neck_tenderness_no()
    {
        return $this->neck_neck_tenderness == "No" ? "CHECKED" : "";
    }
    function set_neck_neck_tenderness($data)
    {
        if (!empty($data)) {
            $this->neck_neck_tenderness = $data;
        }
    }
    function get_neck_neck_tenderness_text()
    {
        return $this->neck_neck_tenderness_text;
    }
    function set_neck_neck_tenderness_text($data)
    {
        if (!empty($data)) {
            $this->neck_neck_tenderness_text = $data;
        }
    }

    // ----- oral ulcers -----

    var $heent_oral_ulcers;
    var $heent_oral_ulcers_text;
    function get_heent_oral_ulcers()
    {
        return $this->heent_oral_ulcers;
    }
    function get_heent_oral_ulcers_yes()
    {
        return $this->heent_oral_ulcers == "Yes" ? "CHECKED" : "";
    }
    function get_heent_oral_ulcers_no()
    {
        return $this->heent_oral_ulcers == "No" ? "CHECKED" : "";
    }
    function set_heent_oral_ulcers($data)
    {
        if (!empty($data)) {
            $this->heent_oral_ulcers = $data;
        }
    }
    function get_heent_oral_ulcers_text()
    {
        return $this->heent_oral_ulcers_text;
    }
    function set_heent_oral_ulcers_text($data)
    {
        if (!empty($data)) {
            $this->heent_oral_ulcers_text = $data;
        }
    }


    var $heent_excessive_cavities;
    var $heent_excessive_cavities_text;
    function get_heent_excessive_cavities()
    {
        return $this->heent_excessive_cavities;
    }
    function get_heent_excessive_cavities_yes()
    {
        return $this->heent_excessive_cavities == "Yes" ? "CHECKED" : "";
    }
    function get_heent_excessive_cavities_no()
    {
        return $this->heent_excessive_cavities == "No" ? "CHECKED" : "";
    }
    function set_heent_excessive_cavities($data)
    {
        if (!empty($data)) {
            $this->heent_excessive_cavities = $data;
        }
    }
    function get_heent_excessive_cavities_text()
    {
        return $this->heent_excessive_cavities_text;
    }
    function set_heent_excessive_cavities_text($data)
    {
        if (!empty($data)) {
            $this->heent_excessive_cavities_text = $data;
        }
    }


    var $heent_gingival_disease;
    var $heent_gingival_disease_text;
    function get_heent_gingival_disease()
    {
        return $this->heent_gingival_disease;
    }
    function get_heent_gingival_disease_yes()
    {
        return $this->heent_gingival_disease == "Yes" ? "CHECKED" : "";
    }
    function get_heent_gingival_disease_no()
    {
        return $this->heent_gingival_disease == "No" ? "CHECKED" : "";
    }
    function set_heent_gingival_disease($data)
    {
        if (!empty($data)) {
            $this->heent_gingival_disease = $data;
        }
    }
    function get_heent_gingival_disease_text()
    {
        return $this->heent_gingival_disease_text;
    }
    function set_heent_gingival_disease_text($data)
    {
        if (!empty($data)) {
            $this->heent_gingival_disease_text = $data;
        }
    }


    var $heent_persistent_hoarseness;
    var $heent_persistent_hoarseness_text;
    function get_heent_persistent_hoarseness()
    {
        return $this->heent_persistent_hoarseness;
    }
    function get_heent_persistent_hoarseness_yes()
    {
        return $this->heent_persistent_hoarseness == "Yes" ? "CHECKED" : "";
    }
    function get_heent_persistent_hoarseness_no()
    {
        return $this->heent_persistent_hoarseness == "No" ? "CHECKED" : "";
    }
    function set_heent_persistent_hoarseness($data)
    {
        if (!empty($data)) {
            $this->heent_persistent_hoarseness = $data;
        }
    }
    function get_heent_persistent_hoarseness_text()
    {
        return $this->heent_persistent_hoarseness_text;
    }
    function set_heent_persistent_hoarseness_text($data)
    {
        if (!empty($data)) {
            $this->heent_persistent_hoarseness_text = $data;
        }
    }


    var $heent_mouth_lesions;
    var $heent_mouth_lesions_text;
    function get_heent_mouth_lesions()
    {
        return $this->heent_mouth_lesions;
    }
    function get_heent_mouth_lesions_yes()
    {
        return $this->heent_mouth_lesions == "Yes" ? "CHECKED" : "";
    }
    function get_heent_mouth_lesions_no()
    {
        return $this->heent_mouth_lesions == "No" ? "CHECKED" : "";
    }
    function set_heent_mouth_lesions($data)
    {
        if (!empty($data)) {
            $this->heent_mouth_lesions = $data;
        }
    }
    function get_heent_mouth_lesions_text()
    {
        return $this->heent_mouth_lesions_text;
    }
    function set_heent_mouth_lesions_text($data)
    {
        if (!empty($data)) {
            $this->heent_mouth_lesions_text = $data;
        }
    }


    var $heent_dysphagia;
    var $heent_dysphagia_text;
    function get_heent_dysphagia()
    {
        return $this->heent_dysphagia;
    }
    function get_heent_dysphagia_yes()
    {
        return $this->heent_dysphagia == "Yes" ? "CHECKED" : "";
    }
    function get_heent_dysphagia_no()
    {
        return $this->heent_dysphagia == "No" ? "CHECKED" : "";
    }
    function set_heent_dysphagia($data)
    {
        if (!empty($data)) {
            $this->heent_dysphagia = $data;
        }
    }
    function get_heent_dysphagia_text()
    {
        return $this->heent_dysphagia_text;
    }
    function set_heent_dysphagia_text($data)
    {
        if (!empty($data)) {
            $this->heent_dysphagia_text = $data;
        }
    }


    var $heent_odynophagia;
    var $heent_odynophagia_text;
    function get_heent_odynophagia()
    {
        return $this->heent_odynophagia;
    }
    function get_heent_odynophagia_yes()
    {
        return $this->heent_odynophagia == "Yes" ? "CHECKED" : "";
    }
    function get_heent_odynophagia_no()
    {
        return $this->heent_odynophagia == "No" ? "CHECKED" : "";
    }
    function set_heent_odynophagia($data)
    {
        if (!empty($data)) {
            $this->heent_odynophagia = $data;
        }
    }
    function get_heent_odynophagia_text()
    {
        return $this->heent_odynophagia_text;
    }
    function set_heent_odynophagia_text($data)
    {
        if (!empty($data)) {
            $this->heent_odynophagia_text = $data;
        }
    }


    var $heent_dental_pain;
    var $heent_dental_pain_text;
    function get_heent_dental_pain()
    {
        return $this->heent_dental_pain;
    }
    function get_heent_dental_pain_yes()
    {
        return $this->heent_dental_pain == "Yes" ? "CHECKED" : "";
    }
    function get_heent_dental_pain_no()
    {
        return $this->heent_dental_pain == "No" ? "CHECKED" : "";
    }
    function set_heent_dental_pain($data)
    {
        if (!empty($data)) {
            $this->heent_dental_pain = $data;
        }
    }
    function get_heent_dental_pain_text()
    {
        return $this->heent_dental_pain_text;
    }
    function set_heent_dental_pain_text($data)
    {
        if (!empty($data)) {
            $this->heent_dental_pain_text = $data;
        }
    }


    var $heent_sore_throat;
    var $heent_sore_throat_text;
    function get_heent_sore_throat()
    {
        return $this->heent_sore_throat;
    }
    function get_heent_sore_throat_yes()
    {
        return $this->heent_sore_throat == "Yes" ? "CHECKED" : "";
    }
    function get_heent_sore_throat_no()
    {
        return $this->heent_sore_throat == "No" ? "CHECKED" : "";
    }
    function set_heent_sore_throat($data)
    {
        if (!empty($data)) {
            $this->heent_sore_throat = $data;
        }
    }
    function get_heent_sore_throat_text()
    {
        return $this->heent_sore_throat_text;
    }
    function set_heent_sore_throat_text($data)
    {
        if (!empty($data)) {
            $this->heent_sore_throat_text = $data;
        }
    }


    var $heent_ear_pain;
    var $heent_ear_pain_text;
    function get_heent_ear_pain()
    {
        return $this->heent_ear_pain;
    }
    function get_heent_ear_pain_yes()
    {
        return $this->heent_ear_pain == "Yes" ? "CHECKED" : "";
    }
    function get_heent_ear_pain_no()
    {
        return $this->heent_ear_pain == "No" ? "CHECKED" : "";
    }
    function set_heent_ear_pain($data)
    {
        if (!empty($data)) {
            $this->heent_ear_pain = $data;
        }
    }
    function get_heent_ear_pain_text()
    {
        return $this->heent_ear_pain_text;
    }
    function set_heent_ear_pain_text($data)
    {
        if (!empty($data)) {
            $this->heent_ear_pain_text = $data;
        }
    }


    var $heent_ear_discharge;
    var $heent_ear_discharge_text;
    function get_heent_ear_discharge()
    {
        return $this->heent_ear_discharge;
    }
    function get_heent_ear_discharge_yes()
    {
        return $this->heent_ear_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_heent_ear_discharge_no()
    {
        return $this->heent_ear_discharge == "No" ? "CHECKED" : "";
    }
    function set_heent_ear_discharge($data)
    {
        if (!empty($data)) {
            $this->heent_ear_discharge = $data;
        }
    }
    function get_heent_ear_discharge_text()
    {
        return $this->heent_ear_discharge_text;
    }
    function set_heent_ear_discharge_text($data)
    {
        if (!empty($data)) {
            $this->heent_ear_discharge_text = $data;
        }
    }


    var $heent_tinnitus;
    var $heent_tinnitus_text;
    function get_heent_tinnitus()
    {
        return $this->heent_tinnitus;
    }
    function get_heent_tinnitus_yes()
    {
        return $this->heent_tinnitus == "Yes" ? "CHECKED" : "";
    }
    function get_heent_tinnitus_no()
    {
        return $this->heent_tinnitus == "No" ? "CHECKED" : "";
    }
    function set_heent_tinnitus($data)
    {
        if (!empty($data)) {
            $this->heent_tinnitus = $data;
        }
    }
    function get_heent_tinnitus_text()
    {
        return $this->heent_tinnitus_text;
    }
    function set_heent_tinnitus_text($data)
    {
        if (!empty($data)) {
            $this->heent_tinnitus_text = $data;
        }
    }


    var $heent_hearing_loss;
    var $heent_hearing_loss_text;
    function get_heent_hearing_loss()
    {
        return $this->heent_hearing_loss;
    }
    function get_heent_hearing_loss_yes()
    {
        return $this->heent_hearing_loss == "Yes" ? "CHECKED" : "";
    }
    function get_heent_hearing_loss_no()
    {
        return $this->heent_hearing_loss == "No" ? "CHECKED" : "";
    }
    function set_heent_hearing_loss($data)
    {
        if (!empty($data)) {
            $this->heent_hearing_loss = $data;
        }
    }
    function get_heent_hearing_loss_text()
    {
        return $this->heent_hearing_loss_text;
    }
    function set_heent_hearing_loss_text($data)
    {
        if (!empty($data)) {
            $this->heent_hearing_loss_text = $data;
        }
    }


    var $heent_allergic_rhinitis;
    var $heent_allergic_rhinitis_text;
    function get_heent_allergic_rhinitis()
    {
        return $this->heent_allergic_rhinitis;
    }
    function get_heent_allergic_rhinitis_yes()
    {
        return $this->heent_allergic_rhinitis == "Yes" ? "CHECKED" : "";
    }
    function get_heent_allergic_rhinitis_no()
    {
        return $this->heent_allergic_rhinitis == "No" ? "CHECKED" : "";
    }
    function set_heent_allergic_rhinitis($data)
    {
        if (!empty($data)) {
            $this->heent_allergic_rhinitis = $data;
        }
    }
    function get_heent_allergic_rhinitis_text()
    {
        return $this->heent_allergic_rhinitis_text;
    }
    function set_heent_allergic_rhinitis_text($data)
    {
        if (!empty($data)) {
            $this->heent_allergic_rhinitis_text = $data;
        }
    }


    var $heent_nasal_congestion;
    var $heent_nasal_congestion_text;
    function get_heent_nasal_congestion()
    {
        return $this->heent_nasal_congestion;
    }
    function get_heent_nasal_congestion_yes()
    {
        return $this->heent_nasal_congestion == "Yes" ? "CHECKED" : "";
    }
    function get_heent_nasal_congestion_no()
    {
        return $this->heent_nasal_congestion == "No" ? "CHECKED" : "";
    }
    function set_heent_nasal_congestion($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_congestion = $data;
        }
    }
    function get_heent_nasal_congestion_text()
    {
        return $this->heent_nasal_congestion_text;
    }
    function set_heent_nasal_congestion_text($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_congestion_text = $data;
        }
    }


    var $heent_nasal_discharge;
    var $heent_nasal_discharge_text;
    function get_heent_nasal_discharge()
    {
        return $this->heent_nasal_discharge;
    }
    function get_heent_nasal_discharge_yes()
    {
        return $this->heent_nasal_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_heent_nasal_discharge_no()
    {
        return $this->heent_nasal_discharge == "No" ? "CHECKED" : "";
    }
    function set_heent_nasal_discharge($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_discharge = $data;
        }
    }
    function get_heent_nasal_discharge_text()
    {
        return $this->heent_nasal_discharge_text;
    }
    function set_heent_nasal_discharge_text($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_discharge_text = $data;
        }
    }


    var $heent_nasal_injury;
    var $heent_nasal_injury_text;
    function get_heent_nasal_injury()
    {
        return $this->heent_nasal_injury;
    }
    function get_heent_nasal_injury_yes()
    {
        return $this->heent_nasal_injury == "Yes" ? "CHECKED" : "";
    }
    function get_heent_nasal_injury_no()
    {
        return $this->heent_nasal_injury == "No" ? "CHECKED" : "";
    }
    function set_heent_nasal_injury($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_injury = $data;
        }
    }
    function get_heent_nasal_injury_text()
    {
        return $this->heent_nasal_injury_text;
    }
    function set_heent_nasal_injury_text($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_injury_text = $data;
        }
    }


    var $heent_nasal_surgery;
    var $heent_nasal_surgery_text;
    function get_heent_nasal_surgery()
    {
        return $this->heent_nasal_surgery;
    }
    function get_heent_nasal_surgery_yes()
    {
        return $this->heent_nasal_surgery == "Yes" ? "CHECKED" : "";
    }
    function get_heent_nasal_surgery_no()
    {
        return $this->heent_nasal_surgery == "No" ? "CHECKED" : "";
    }
    function set_heent_nasal_surgery($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_surgery = $data;
        }
    }
    function get_heent_nasal_surgery_text()
    {
        return $this->heent_nasal_surgery_text;
    }
    function set_heent_nasal_surgery_text($data)
    {
        if (!empty($data)) {
            $this->heent_nasal_surgery_text = $data;
        }
    }


    var $heent_nose_bleeds;
    var $heent_nose_bleeds_text;
    function get_heent_nose_bleeds()
    {
        return $this->heent_nose_bleeds;
    }
    function get_heent_nose_bleeds_yes()
    {
        return $this->heent_nose_bleeds == "Yes" ? "CHECKED" : "";
    }
    function get_heent_nose_bleeds_no()
    {
        return $this->heent_nose_bleeds == "No" ? "CHECKED" : "";
    }
    function set_heent_nose_bleeds($data)
    {
        if (!empty($data)) {
            $this->heent_nose_bleeds = $data;
        }
    }
    function get_heent_nose_bleeds_text()
    {
        return $this->heent_nose_bleeds_text;
    }
    function set_heent_nose_bleeds_text($data)
    {
        if (!empty($data)) {
            $this->heent_nose_bleeds_text = $data;
        }
    }


    var $heent_post_nasal_drip;
    var $heent_post_nasal_drip_text;
    function get_heent_post_nasal_drip()
    {
        return $this->heent_post_nasal_drip;
    }
    function get_heent_post_nasal_drip_yes()
    {
        return $this->heent_post_nasal_drip == "Yes" ? "CHECKED" : "";
    }
    function get_heent_post_nasal_drip_no()
    {
        return $this->heent_post_nasal_drip == "No" ? "CHECKED" : "";
    }
    function set_heent_post_nasal_drip($data)
    {
        if (!empty($data)) {
            $this->heent_post_nasal_drip = $data;
        }
    }
    function get_heent_post_nasal_drip_text()
    {
        return $this->heent_post_nasal_drip_text;
    }
    function set_heent_post_nasal_drip_text($data)
    {
        if (!empty($data)) {
            $this->heent_post_nasal_drip_text = $data;
        }
    }


    var $heent_sinus_pressure;
    var $heent_sinus_pressure_text;
    function get_heent_sinus_pressure()
    {
        return $this->heent_sinus_pressure;
    }
    function get_heent_sinus_pressure_yes()
    {
        return $this->heent_sinus_pressure == "Yes" ? "CHECKED" : "";
    }
    function get_heent_sinus_pressure_no()
    {
        return $this->heent_sinus_pressure == "No" ? "CHECKED" : "";
    }
    function set_heent_sinus_pressure($data)
    {
        if (!empty($data)) {
            $this->heent_sinus_pressure = $data;
        }
    }
    function get_heent_sinus_pressure_text()
    {
        return $this->heent_sinus_pressure_text;
    }
    function set_heent_sinus_pressure_text($data)
    {
        if (!empty($data)) {
            $this->heent_sinus_pressure_text = $data;
        }
    }


    var $heent_sinus_pain;
    var $heent_sinus_pain_text;
    function get_heent_sinus_pain()
    {
        return $this->heent_sinus_pain;
    }
    function get_heent_sinus_pain_yes()
    {
        return $this->heent_sinus_pain == "Yes" ? "CHECKED" : "";
    }
    function get_heent_sinus_pain_no()
    {
        return $this->heent_sinus_pain == "No" ? "CHECKED" : "";
    }
    function set_heent_sinus_pain($data)
    {
        if (!empty($data)) {
            $this->heent_sinus_pain = $data;
        }
    }
    function get_heent_sinus_pain_text()
    {
        return $this->heent_sinus_pain_text;
    }
    function set_heent_sinus_pain_text($data)
    {
        if (!empty($data)) {
            $this->heent_sinus_pain_text = $data;
        }
    }


    var $heent_headache;
    var $heent_headache_text;
    function get_heent_headache()
    {
        return $this->heent_headache;
    }
    function get_heent_headache_yes()
    {
        return $this->heent_headache == "Yes" ? "CHECKED" : "";
    }
    function get_heent_headache_no()
    {
        return $this->heent_headache == "No" ? "CHECKED" : "";
    }
    function set_heent_headache($data)
    {
        if (!empty($data)) {
            $this->heent_headache = $data;
        }
    }
    function get_heent_headache_text()
    {
        return $this->heent_headache_text;
    }
    function set_heent_headache_text($data)
    {
        if (!empty($data)) {
            $this->heent_headache_text = $data;
        }
    }


    var $heent_eye_pain;
    var $heent_eye_pain_text;
    function get_heent_eye_pain()
    {
        return $this->heent_eye_pain;
    }
    function get_heent_eye_pain_yes()
    {
        return $this->heent_eye_pain == "Yes" ? "CHECKED" : "";
    }
    function get_heent_eye_pain_no()
    {
        return $this->heent_eye_pain == "No" ? "CHECKED" : "";
    }
    function set_heent_eye_pain($data)
    {
        if (!empty($data)) {
            $this->heent_eye_pain = $data;
        }
    }
    function get_heent_eye_pain_text()
    {
        return $this->heent_eye_pain_text;
    }
    function set_heent_eye_pain_text($data)
    {
        if (!empty($data)) {
            $this->heent_eye_pain_text = $data;
        }
    }


    var $heent_eye_redness;
    var $heent_eye_redness_text;
    function get_heent_eye_redness()
    {
        return $this->heent_eye_redness;
    }
    function get_heent_eye_redness_yes()
    {
        return $this->heent_eye_redness == "Yes" ? "CHECKED" : "";
    }
    function get_heent_eye_redness_no()
    {
        return $this->heent_eye_redness == "No" ? "CHECKED" : "";
    }
    function set_heent_eye_redness($data)
    {
        if (!empty($data)) {
            $this->heent_eye_redness = $data;
        }
    }
    function get_heent_eye_redness_text()
    {
        return $this->heent_eye_redness_text;
    }
    function set_heent_eye_redness_text($data)
    {
        if (!empty($data)) {
            $this->heent_eye_redness_text = $data;
        }
    }


    var $heent_visual_changes;
    var $heent_visual_changes_text;
    function get_heent_visual_changes()
    {
        return $this->heent_visual_changes;
    }
    function get_heent_visual_changes_yes()
    {
        return $this->heent_visual_changes == "Yes" ? "CHECKED" : "";
    }
    function get_heent_visual_changes_no()
    {
        return $this->heent_visual_changes == "No" ? "CHECKED" : "";
    }
    function set_heent_visual_changes($data)
    {
        if (!empty($data)) {
            $this->heent_visual_changes = $data;
        }
    }
    function get_heent_visual_changes_text()
    {
        return $this->heent_visual_changes_text;
    }
    function set_heent_visual_changes_text($data)
    {
        if (!empty($data)) {
            $this->heent_visual_changes_text = $data;
        }
    }


    var $heent_blurry_vision;
    var $heent_blurry_vision_text;
    function get_heent_blurry_vision()
    {
        return $this->heent_blurry_vision;
    }
    function get_heent_blurry_vision_yes()
    {
        return $this->heent_blurry_vision == "Yes" ? "CHECKED" : "";
    }
    function get_heent_blurry_vision_no()
    {
        return $this->heent_blurry_vision == "No" ? "CHECKED" : "";
    }
    function set_heent_blurry_vision($data)
    {
        if (!empty($data)) {
            $this->heent_blurry_vision = $data;
        }
    }
    function get_heent_blurry_vision_text()
    {
        return $this->heent_blurry_vision_text;
    }
    function set_heent_blurry_vision_text($data)
    {
        if (!empty($data)) {
            $this->heent_blurry_vision_text = $data;
        }
    }


    var $heent_eye_discharge;
    var $heent_eye_discharge_text;
    function get_heent_eye_discharge()
    {
        return $this->heent_eye_discharge;
    }
    function get_heent_eye_discharge_yes()
    {
        return $this->heent_eye_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_heent_eye_discharge_no()
    {
        return $this->heent_eye_discharge == "No" ? "CHECKED" : "";
    }
    function set_heent_eye_discharge($data)
    {
        if (!empty($data)) {
            $this->heent_eye_discharge = $data;
        }
    }
    function get_heent_eye_discharge_text()
    {
        return $this->heent_eye_discharge_text;
    }
    function set_heent_eye_discharge_text($data)
    {
        if (!empty($data)) {
            $this->heent_eye_discharge_text = $data;
        }
    }


    var $heent_eye_glasses_contacts;
    var $heent_eye_glasses_contacts_text;
    function get_heent_eye_glasses_contacts()
    {
        return $this->heent_eye_glasses_contacts;
    }
    function get_heent_eye_glasses_contacts_yes()
    {
        return $this->heent_eye_glasses_contacts == "Yes" ? "CHECKED" : "";
    }
    function get_heent_eye_glasses_contacts_no()
    {
        return $this->heent_eye_glasses_contacts == "No" ? "CHECKED" : "";
    }
    function set_heent_eye_glasses_contacts($data)
    {
        if (!empty($data)) {
            $this->heent_eye_glasses_contacts = $data;
        }
    }
    function get_heent_eye_glasses_contacts_text()
    {
        return $this->heent_eye_glasses_contacts_text;
    }
    function set_heent_eye_glasses_contacts_text($data)
    {
        if (!empty($data)) {
            $this->heent_eye_glasses_contacts_text = $data;
        }
    }


    var $heent_excess_tearing;
    var $heent_excess_tearing_text;
    function get_heent_excess_tearing()
    {
        return $this->heent_excess_tearing;
    }
    function get_heent_excess_tearing_yes()
    {
        return $this->heent_excess_tearing == "Yes" ? "CHECKED" : "";
    }
    function get_heent_excess_tearing_no()
    {
        return $this->heent_excess_tearing == "No" ? "CHECKED" : "";
    }
    function set_heent_excess_tearing($data)
    {
        if (!empty($data)) {
            $this->heent_excess_tearing = $data;
        }
    }
    function get_heent_excess_tearing_text()
    {
        return $this->heent_excess_tearing_text;
    }
    function set_heent_excess_tearing_text($data)
    {
        if (!empty($data)) {
            $this->heent_excess_tearing_text = $data;
        }
    }


    var $heent_photophobia;
    var $heent_photophobia_text;
    function get_heent_photophobia()
    {
        return $this->heent_photophobia;
    }
    function get_heent_photophobia_yes()
    {
        return $this->heent_photophobia == "Yes" ? "CHECKED" : "";
    }
    function get_heent_photophobia_no()
    {
        return $this->heent_photophobia == "No" ? "CHECKED" : "";
    }
    function set_heent_photophobia($data)
    {
        if (!empty($data)) {
            $this->heent_photophobia = $data;
        }
    }
    function get_heent_photophobia_text()
    {
        return $this->heent_photophobia_text;
    }
    function set_heent_photophobia_text($data)
    {
        if (!empty($data)) {
            $this->heent_photophobia_text = $data;
        }
    }


    var $heent_scotomata;
    var $heent_scotomata_text;
    function get_heent_scotomata()
    {
        return $this->heent_scotomata;
    }
    function get_heent_scotomata_yes()
    {
        return $this->heent_scotomata == "Yes" ? "CHECKED" : "";
    }
    function get_heent_scotomata_no()
    {
        return $this->heent_scotomata == "No" ? "CHECKED" : "";
    }
    function set_heent_scotomata($data)
    {
        if (!empty($data)) {
            $this->heent_scotomata = $data;
        }
    }
    function get_heent_scotomata_text()
    {
        return $this->heent_scotomata_text;
    }
    function set_heent_scotomata_text($data)
    {
        if (!empty($data)) {
            $this->heent_scotomata_text = $data;
        }
    }


    var $heent_tunnel_vision;
    var $heent_tunnel_vision_text;
    function get_heent_tunnel_vision()
    {
        return $this->heent_tunnel_vision;
    }
    function get_heent_tunnel_vision_yes()
    {
        return $this->heent_tunnel_vision == "Yes" ? "CHECKED" : "";
    }
    function get_heent_tunnel_vision_no()
    {
        return $this->heent_tunnel_vision == "No" ? "CHECKED" : "";
    }
    function set_heent_tunnel_vision($data)
    {
        if (!empty($data)) {
            $this->heent_tunnel_vision = $data;
        }
    }
    function get_heent_tunnel_vision_text()
    {
        return $this->heent_tunnel_vision_text;
    }
    function set_heent_tunnel_vision_text($data)
    {
        if (!empty($data)) {
            $this->heent_tunnel_vision_text = $data;
        }
    }


    var $heent_glaucoma;
    var $heent_glaucoma_text;
    function get_heent_glaucoma()
    {
        return $this->heent_glaucoma;
    }
    function get_heent_glaucoma_yes()
    {
        return $this->heent_glaucoma == "Yes" ? "CHECKED" : "";
    }
    function get_heent_glaucoma_no()
    {
        return $this->heent_glaucoma == "No" ? "CHECKED" : "";
    }
    function set_heent_glaucoma($data)
    {
        if (!empty($data)) {
            $this->heent_glaucoma = $data;
        }
    }
    function get_heent_glaucoma_text()
    {
        return $this->heent_glaucoma_text;
    }
    function set_heent_glaucoma_text($data)
    {
        if (!empty($data)) {
            $this->heent_glaucoma_text = $data;
        }
    }

    // ----- sub sternal or left chest pain -----

    var $cardiovascular_sub_sternal_or_left_chest_pain;
    var $cardiovascular_sub_sternal_or_left_chest_pain_text;
    function get_cardiovascular_sub_sternal_or_left_chest_pain()
    {
        return $this->cardiovascular_sub_sternal_or_left_chest_pain;
    }
    function get_cardiovascular_sub_sternal_or_left_chest_pain_yes()
    {
        return $this->cardiovascular_sub_sternal_or_left_chest_pain == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_sub_sternal_or_left_chest_pain_no()
    {
        return $this->cardiovascular_sub_sternal_or_left_chest_pain == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_sub_sternal_or_left_chest_pain($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_sub_sternal_or_left_chest_pain = $data;
        }
    }
    function get_cardiovascular_sub_sternal_or_left_chest_pain_text()
    {
        return $this->cardiovascular_sub_sternal_or_left_chest_pain_text;
    }
    function set_cardiovascular_sub_sternal_or_left_chest_pain_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_sub_sternal_or_left_chest_pain_text = $data;
        }
    }


    var $cardiovascular_other_chest_pain;
    var $cardiovascular_other_chest_pain_text;
    function get_cardiovascular_other_chest_pain()
    {
        return $this->cardiovascular_other_chest_pain;
    }
    function get_cardiovascular_other_chest_pain_yes()
    {
        return $this->cardiovascular_other_chest_pain == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_other_chest_pain_no()
    {
        return $this->cardiovascular_other_chest_pain == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_other_chest_pain($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_other_chest_pain = $data;
        }
    }
    function get_cardiovascular_other_chest_pain_text()
    {
        return $this->cardiovascular_other_chest_pain_text;
    }
    function set_cardiovascular_other_chest_pain_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_other_chest_pain_text = $data;
        }
    }


    var $cardiovascular_palpitations;
    var $cardiovascular_palpitations_text;
    function get_cardiovascular_palpitations()
    {
        return $this->cardiovascular_palpitations;
    }
    function get_cardiovascular_palpitations_yes()
    {
        return $this->cardiovascular_palpitations == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_palpitations_no()
    {
        return $this->cardiovascular_palpitations == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_palpitations($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_palpitations = $data;
        }
    }
    function get_cardiovascular_palpitations_text()
    {
        return $this->cardiovascular_palpitations_text;
    }
    function set_cardiovascular_palpitations_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_palpitations_text = $data;
        }
    }


    var $cardiovascular_irregular_rhythm;
    var $cardiovascular_irregular_rhythm_text;
    function get_cardiovascular_irregular_rhythm()
    {
        return $this->cardiovascular_irregular_rhythm;
    }
    function get_cardiovascular_irregular_rhythm_yes()
    {
        return $this->cardiovascular_irregular_rhythm == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_irregular_rhythm_no()
    {
        return $this->cardiovascular_irregular_rhythm == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_irregular_rhythm($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_irregular_rhythm = $data;
        }
    }
    function get_cardiovascular_irregular_rhythm_text()
    {
        return $this->cardiovascular_irregular_rhythm_text;
    }
    function set_cardiovascular_irregular_rhythm_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_irregular_rhythm_text = $data;
        }
    }


    var $cardiovascular_jugular_vein_distention;
    var $cardiovascular_jugular_vein_distention_text;
    function get_cardiovascular_jugular_vein_distention()
    {
        return $this->cardiovascular_jugular_vein_distention;
    }
    function get_cardiovascular_jugular_vein_distention_yes()
    {
        return $this->cardiovascular_jugular_vein_distention == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_jugular_vein_distention_no()
    {
        return $this->cardiovascular_jugular_vein_distention == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_jugular_vein_distention($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_jugular_vein_distention = $data;
        }
    }
    function get_cardiovascular_jugular_vein_distention_text()
    {
        return $this->cardiovascular_jugular_vein_distention_text;
    }
    function set_cardiovascular_jugular_vein_distention_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_jugular_vein_distention_text = $data;
        }
    }


    var $cardiovascular_claudication;
    var $cardiovascular_claudication_text;
    function get_cardiovascular_claudication()
    {
        return $this->cardiovascular_claudication;
    }
    function get_cardiovascular_claudication_yes()
    {
        return $this->cardiovascular_claudication == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_claudication_no()
    {
        return $this->cardiovascular_claudication == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_claudication($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_claudication = $data;
        }
    }
    function get_cardiovascular_claudication_text()
    {
        return $this->cardiovascular_claudication_text;
    }
    function set_cardiovascular_claudication_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_claudication_text = $data;
        }
    }


    var $cardiovascular_dizziness;
    var $cardiovascular_dizziness_text;
    function get_cardiovascular_dizziness()
    {
        return $this->cardiovascular_dizziness;
    }
    function get_cardiovascular_dizziness_yes()
    {
        return $this->cardiovascular_dizziness == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_dizziness_no()
    {
        return $this->cardiovascular_dizziness == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_dizziness($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_dizziness = $data;
        }
    }
    function get_cardiovascular_dizziness_text()
    {
        return $this->cardiovascular_dizziness_text;
    }
    function set_cardiovascular_dizziness_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_dizziness_text = $data;
        }
    }


    var $cardiovascular_dyspnea_on_exertion;
    var $cardiovascular_dyspnea_on_exertion_text;
    function get_cardiovascular_dyspnea_on_exertion()
    {
        return $this->cardiovascular_dyspnea_on_exertion;
    }
    function get_cardiovascular_dyspnea_on_exertion_yes()
    {
        return $this->cardiovascular_dyspnea_on_exertion == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_dyspnea_on_exertion_no()
    {
        return $this->cardiovascular_dyspnea_on_exertion == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_dyspnea_on_exertion($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_dyspnea_on_exertion = $data;
        }
    }
    function get_cardiovascular_dyspnea_on_exertion_text()
    {
        return $this->cardiovascular_dyspnea_on_exertion_text;
    }
    function set_cardiovascular_dyspnea_on_exertion_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_dyspnea_on_exertion_text = $data;
        }
    }


    var $cardiovascular_orthopnea;
    var $cardiovascular_orthopnea_text;
    function get_cardiovascular_orthopnea()
    {
        return $this->cardiovascular_orthopnea;
    }
    function get_cardiovascular_orthopnea_yes()
    {
        return $this->cardiovascular_orthopnea == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_orthopnea_no()
    {
        return $this->cardiovascular_orthopnea == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_orthopnea($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_orthopnea = $data;
        }
    }
    function get_cardiovascular_orthopnea_text()
    {
        return $this->cardiovascular_orthopnea_text;
    }
    function set_cardiovascular_orthopnea_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_orthopnea_text = $data;
        }
    }


    var $cardiovascular_noctural_dyspnea;
    var $cardiovascular_noctural_dyspnea_text;
    function get_cardiovascular_noctural_dyspnea()
    {
        return $this->cardiovascular_noctural_dyspnea;
    }
    function get_cardiovascular_noctural_dyspnea_yes()
    {
        return $this->cardiovascular_noctural_dyspnea == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_noctural_dyspnea_no()
    {
        return $this->cardiovascular_noctural_dyspnea == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_noctural_dyspnea($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_noctural_dyspnea = $data;
        }
    }
    function get_cardiovascular_noctural_dyspnea_text()
    {
        return $this->cardiovascular_noctural_dyspnea_text;
    }
    function set_cardiovascular_noctural_dyspnea_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_noctural_dyspnea_text = $data;
        }
    }


    var $cardiovascular_edema;
    var $cardiovascular_edema_text;
    function get_cardiovascular_edema()
    {
        return $this->cardiovascular_edema;
    }
    function get_cardiovascular_edema_yes()
    {
        return $this->cardiovascular_edema == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_edema_no()
    {
        return $this->cardiovascular_edema == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_edema($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_edema = $data;
        }
    }
    function get_cardiovascular_edema_text()
    {
        return $this->cardiovascular_edema_text;
    }
    function set_cardiovascular_edema_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_edema_text = $data;
        }
    }


    var $cardiovascular_presyncope;
    var $cardiovascular_presyncope_text;
    function get_cardiovascular_presyncope()
    {
        return $this->cardiovascular_presyncope;
    }
    function get_cardiovascular_presyncope_yes()
    {
        return $this->cardiovascular_presyncope == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_presyncope_no()
    {
        return $this->cardiovascular_presyncope == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_presyncope($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_presyncope = $data;
        }
    }
    function get_cardiovascular_presyncope_text()
    {
        return $this->cardiovascular_presyncope_text;
    }
    function set_cardiovascular_presyncope_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_presyncope_text = $data;
        }
    }


    var $cardiovascular_syncope;
    var $cardiovascular_syncope_text;
    function get_cardiovascular_syncope()
    {
        return $this->cardiovascular_syncope;
    }
    function get_cardiovascular_syncope_yes()
    {
        return $this->cardiovascular_syncope == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_syncope_no()
    {
        return $this->cardiovascular_syncope == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_syncope($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_syncope = $data;
        }
    }
    function get_cardiovascular_syncope_text()
    {
        return $this->cardiovascular_syncope_text;
    }
    function set_cardiovascular_syncope_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_syncope_text = $data;
        }
    }


    var $cardiovascular_heart_murmur;
    var $cardiovascular_heart_murmur_text;
    function get_cardiovascular_heart_murmur()
    {
        return $this->cardiovascular_heart_murmur;
    }
    function get_cardiovascular_heart_murmur_yes()
    {
        return $this->cardiovascular_heart_murmur == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_heart_murmur_no()
    {
        return $this->cardiovascular_heart_murmur == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_heart_murmur($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_heart_murmur = $data;
        }
    }
    function get_cardiovascular_heart_murmur_text()
    {
        return $this->cardiovascular_heart_murmur_text;
    }
    function set_cardiovascular_heart_murmur_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_heart_murmur_text = $data;
        }
    }


    var $cardiovascular_raynauds;
    var $cardiovascular_raynauds_text;
    function get_cardiovascular_raynauds()
    {
        return $this->cardiovascular_raynauds;
    }
    function get_cardiovascular_raynauds_yes()
    {
        return $this->cardiovascular_raynauds == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_raynauds_no()
    {
        return $this->cardiovascular_raynauds == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_raynauds($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_raynauds = $data;
        }
    }
    function get_cardiovascular_raynauds_text()
    {
        return $this->cardiovascular_raynauds_text;
    }
    function set_cardiovascular_raynauds_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_raynauds_text = $data;
        }
    }


    var $cardiovascular_severe_varicose_veins;
    var $cardiovascular_severe_varicose_veins_text;
    function get_cardiovascular_severe_varicose_veins()
    {
        return $this->cardiovascular_severe_varicose_veins;
    }
    function get_cardiovascular_severe_varicose_veins_yes()
    {
        return $this->cardiovascular_severe_varicose_veins == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_severe_varicose_veins_no()
    {
        return $this->cardiovascular_severe_varicose_veins == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_severe_varicose_veins($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_severe_varicose_veins = $data;
        }
    }
    function get_cardiovascular_severe_varicose_veins_text()
    {
        return $this->cardiovascular_severe_varicose_veins_text;
    }
    function set_cardiovascular_severe_varicose_veins_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_severe_varicose_veins_text = $data;
        }
    }


    var $cardiovascular_deep_vein_thrombosis;
    var $cardiovascular_deep_vein_thrombosis_text;
    function get_cardiovascular_deep_vein_thrombosis()
    {
        return $this->cardiovascular_deep_vein_thrombosis;
    }
    function get_cardiovascular_deep_vein_thrombosis_yes()
    {
        return $this->cardiovascular_deep_vein_thrombosis == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_deep_vein_thrombosis_no()
    {
        return $this->cardiovascular_deep_vein_thrombosis == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_deep_vein_thrombosis($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_deep_vein_thrombosis = $data;
        }
    }
    function get_cardiovascular_deep_vein_thrombosis_text()
    {
        return $this->cardiovascular_deep_vein_thrombosis_text;
    }
    function set_cardiovascular_deep_vein_thrombosis_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_deep_vein_thrombosis_text = $data;
        }
    }


    var $cardiovascular_thrombophlebitis;
    var $cardiovascular_thrombophlebitis_text;
    function get_cardiovascular_thrombophlebitis()
    {
        return $this->cardiovascular_thrombophlebitis;
    }
    function get_cardiovascular_thrombophlebitis_yes()
    {
        return $this->cardiovascular_thrombophlebitis == "Yes" ? "CHECKED" : "";
    }
    function get_cardiovascular_thrombophlebitis_no()
    {
        return $this->cardiovascular_thrombophlebitis == "No" ? "CHECKED" : "";
    }
    function set_cardiovascular_thrombophlebitis($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_thrombophlebitis = $data;
        }
    }
    function get_cardiovascular_thrombophlebitis_text()
    {
        return $this->cardiovascular_thrombophlebitis_text;
    }
    function set_cardiovascular_thrombophlebitis_text($data)
    {
        if (!empty($data)) {
            $this->cardiovascular_thrombophlebitis_text = $data;
        }
    }

    // ----- cough -----

    var $respirations_cough;
    var $respirations_cough_text;
    function get_respirations_cough()
    {
        return $this->respirations_cough;
    }
    function get_respirations_cough_yes()
    {
        return $this->respirations_cough == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_cough_no()
    {
        return $this->respirations_cough == "No" ? "CHECKED" : "";
    }
    function set_respirations_cough($data)
    {
        if (!empty($data)) {
            $this->respirations_cough = $data;
        }
    }
    function get_respirations_cough_text()
    {
        return $this->respirations_cough_text;
    }
    function set_respirations_cough_text($data)
    {
        if (!empty($data)) {
            $this->respirations_cough_text = $data;
        }
    }


    var $respirations_sputum;
    var $respirations_sputum_text;
    function get_respirations_sputum()
    {
        return $this->respirations_sputum;
    }
    function get_respirations_sputum_yes()
    {
        return $this->respirations_sputum == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_sputum_no()
    {
        return $this->respirations_sputum == "No" ? "CHECKED" : "";
    }
    function set_respirations_sputum($data)
    {
        if (!empty($data)) {
            $this->respirations_sputum = $data;
        }
    }
    function get_respirations_sputum_text()
    {
        return $this->respirations_sputum_text;
    }
    function set_respirations_sputum_text($data)
    {
        if (!empty($data)) {
            $this->respirations_sputum_text = $data;
        }
    }


    var $respirations_dyspnea;
    var $respirations_dyspnea_text;
    function get_respirations_dyspnea()
    {
        return $this->respirations_dyspnea;
    }
    function get_respirations_dyspnea_yes()
    {
        return $this->respirations_dyspnea == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_dyspnea_no()
    {
        return $this->respirations_dyspnea == "No" ? "CHECKED" : "";
    }
    function set_respirations_dyspnea($data)
    {
        if (!empty($data)) {
            $this->respirations_dyspnea = $data;
        }
    }
    function get_respirations_dyspnea_text()
    {
        return $this->respirations_dyspnea_text;
    }
    function set_respirations_dyspnea_text($data)
    {
        if (!empty($data)) {
            $this->respirations_dyspnea_text = $data;
        }
    }


    var $respirations_wheezes;
    var $respirations_wheezes_text;
    function get_respirations_wheezes()
    {
        return $this->respirations_wheezes;
    }
    function get_respirations_wheezes_yes()
    {
        return $this->respirations_wheezes == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_wheezes_no()
    {
        return $this->respirations_wheezes == "No" ? "CHECKED" : "";
    }
    function set_respirations_wheezes($data)
    {
        if (!empty($data)) {
            $this->respirations_wheezes = $data;
        }
    }
    function get_respirations_wheezes_text()
    {
        return $this->respirations_wheezes_text;
    }
    function set_respirations_wheezes_text($data)
    {
        if (!empty($data)) {
            $this->respirations_wheezes_text = $data;
        }
    }


    var $respirations_rales;
    var $respirations_rales_text;
    function get_respirations_rales()
    {
        return $this->respirations_rales;
    }
    function get_respirations_rales_yes()
    {
        return $this->respirations_rales == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_rales_no()
    {
        return $this->respirations_rales == "No" ? "CHECKED" : "";
    }
    function set_respirations_rales($data)
    {
        if (!empty($data)) {
            $this->respirations_rales = $data;
        }
    }
    function get_respirations_rales_text()
    {
        return $this->respirations_rales_text;
    }
    function set_respirations_rales_text($data)
    {
        if (!empty($data)) {
            $this->respirations_rales_text = $data;
        }
    }


    var $respirations_labored_breathing;
    var $respirations_labored_breathing_text;
    function get_respirations_labored_breathing()
    {
        return $this->respirations_labored_breathing;
    }
    function get_respirations_labored_breathing_yes()
    {
        return $this->respirations_labored_breathing == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_labored_breathing_no()
    {
        return $this->respirations_labored_breathing == "No" ? "CHECKED" : "";
    }
    function set_respirations_labored_breathing($data)
    {
        if (!empty($data)) {
            $this->respirations_labored_breathing = $data;
        }
    }
    function get_respirations_labored_breathing_text()
    {
        return $this->respirations_labored_breathing_text;
    }
    function set_respirations_labored_breathing_text($data)
    {
        if (!empty($data)) {
            $this->respirations_labored_breathing_text = $data;
        }
    }


    var $respirations_hemoptysis;
    var $respirations_hemoptysis_text;
    function get_respirations_hemoptysis()
    {
        return $this->respirations_hemoptysis;
    }
    function get_respirations_hemoptysis_yes()
    {
        return $this->respirations_hemoptysis == "Yes" ? "CHECKED" : "";
    }
    function get_respirations_hemoptysis_no()
    {
        return $this->respirations_hemoptysis == "No" ? "CHECKED" : "";
    }
    function set_respirations_hemoptysis($data)
    {
        if (!empty($data)) {
            $this->respirations_hemoptysis = $data;
        }
    }
    function get_respirations_hemoptysis_text()
    {
        return $this->respirations_hemoptysis_text;
    }
    function set_respirations_hemoptysis_text($data)
    {
        if (!empty($data)) {
            $this->respirations_hemoptysis_text = $data;
        }
    }

    // ----- frequent urination -----

    var $gu_frequent_urination;
    var $gu_frequent_urination_text;
    function get_gu_frequent_urination()
    {
        return $this->gu_frequent_urination;
    }
    function get_gu_frequent_urination_yes()
    {
        return $this->gu_frequent_urination == "Yes" ? "CHECKED" : "";
    }
    function get_gu_frequent_urination_no()
    {
        return $this->gu_frequent_urination == "No" ? "CHECKED" : "";
    }
    function set_gu_frequent_urination($data)
    {
        if (!empty($data)) {
            $this->gu_frequent_urination = $data;
        }
    }
    function get_gu_frequent_urination_text()
    {
        return $this->gu_frequent_urination_text;
    }
    function set_gu_frequent_urination_text($data)
    {
        if (!empty($data)) {
            $this->gu_frequent_urination_text = $data;
        }
    }


    var $gu_dysuria;
    var $gu_dysuria_text;
    function get_gu_dysuria()
    {
        return $this->gu_dysuria;
    }
    function get_gu_dysuria_yes()
    {
        return $this->gu_dysuria == "Yes" ? "CHECKED" : "";
    }
    function get_gu_dysuria_no()
    {
        return $this->gu_dysuria == "No" ? "CHECKED" : "";
    }
    function set_gu_dysuria($data)
    {
        if (!empty($data)) {
            $this->gu_dysuria = $data;
        }
    }
    function get_gu_dysuria_text()
    {
        return $this->gu_dysuria_text;
    }
    function set_gu_dysuria_text($data)
    {
        if (!empty($data)) {
            $this->gu_dysuria_text = $data;
        }
    }


    var $gu_dyspareunia;
    var $gu_dyspareunia_text;
    function get_gu_dyspareunia()
    {
        return $this->gu_dyspareunia;
    }
    function get_gu_dyspareunia_yes()
    {
        return $this->gu_dyspareunia == "Yes" ? "CHECKED" : "";
    }
    function get_gu_dyspareunia_no()
    {
        return $this->gu_dyspareunia == "No" ? "CHECKED" : "";
    }
    function set_gu_dyspareunia($data)
    {
        if (!empty($data)) {
            $this->gu_dyspareunia = $data;
        }
    }
    function get_gu_dyspareunia_text()
    {
        return $this->gu_dyspareunia_text;
    }
    function set_gu_dyspareunia_text($data)
    {
        if (!empty($data)) {
            $this->gu_dyspareunia_text = $data;
        }
    }


    var $gu_discharge;
    var $gu_discharge_text;
    function get_gu_discharge()
    {
        return $this->gu_discharge;
    }
    function get_gu_discharge_yes()
    {
        return $this->gu_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_gu_discharge_no()
    {
        return $this->gu_discharge == "No" ? "CHECKED" : "";
    }
    function set_gu_discharge($data)
    {
        if (!empty($data)) {
            $this->gu_discharge = $data;
        }
    }
    function get_gu_discharge_text()
    {
        return $this->gu_discharge_text;
    }
    function set_gu_discharge_text($data)
    {
        if (!empty($data)) {
            $this->gu_discharge_text = $data;
        }
    }


    var $gu_odor;
    var $gu_odor_text;
    function get_gu_odor()
    {
        return $this->gu_odor;
    }
    function get_gu_odor_yes()
    {
        return $this->gu_odor == "Yes" ? "CHECKED" : "";
    }
    function get_gu_odor_no()
    {
        return $this->gu_odor == "No" ? "CHECKED" : "";
    }
    function set_gu_odor($data)
    {
        if (!empty($data)) {
            $this->gu_odor = $data;
        }
    }
    function get_gu_odor_text()
    {
        return $this->gu_odor_text;
    }
    function set_gu_odor_text($data)
    {
        if (!empty($data)) {
            $this->gu_odor_text = $data;
        }
    }


    var $gu_fertility_problems;
    var $gu_fertility_problems_text;
    function get_gu_fertility_problems()
    {
        return $this->gu_fertility_problems;
    }
    function get_gu_fertility_problems_yes()
    {
        return $this->gu_fertility_problems == "Yes" ? "CHECKED" : "";
    }
    function get_gu_fertility_problems_no()
    {
        return $this->gu_fertility_problems == "No" ? "CHECKED" : "";
    }
    function set_gu_fertility_problems($data)
    {
        if (!empty($data)) {
            $this->gu_fertility_problems = $data;
        }
    }
    function get_gu_fertility_problems_text()
    {
        return $this->gu_fertility_problems_text;
    }
    function set_gu_fertility_problems_text($data)
    {
        if (!empty($data)) {
            $this->gu_fertility_problems_text = $data;
        }
    }


    var $gu_flank_pain_kidney_stone;
    var $gu_flank_pain_kidney_stone_text;
    function get_gu_flank_pain_kidney_stone()
    {
        return $this->gu_flank_pain_kidney_stone;
    }
    function get_gu_flank_pain_kidney_stone_yes()
    {
        return $this->gu_flank_pain_kidney_stone == "Yes" ? "CHECKED" : "";
    }
    function get_gu_flank_pain_kidney_stone_no()
    {
        return $this->gu_flank_pain_kidney_stone == "No" ? "CHECKED" : "";
    }
    function set_gu_flank_pain_kidney_stone($data)
    {
        if (!empty($data)) {
            $this->gu_flank_pain_kidney_stone = $data;
        }
    }
    function get_gu_flank_pain_kidney_stone_text()
    {
        return $this->gu_flank_pain_kidney_stone_text;
    }
    function set_gu_flank_pain_kidney_stone_text($data)
    {
        if (!empty($data)) {
            $this->gu_flank_pain_kidney_stone_text = $data;
        }
    }


    var $gu_polyuria;
    var $gu_polyuria_text;
    function get_gu_polyuria()
    {
        return $this->gu_polyuria;
    }
    function get_gu_polyuria_yes()
    {
        return $this->gu_polyuria == "Yes" ? "CHECKED" : "";
    }
    function get_gu_polyuria_no()
    {
        return $this->gu_polyuria == "No" ? "CHECKED" : "";
    }
    function set_gu_polyuria($data)
    {
        if (!empty($data)) {
            $this->gu_polyuria = $data;
        }
    }
    function get_gu_polyuria_text()
    {
        return $this->gu_polyuria_text;
    }
    function set_gu_polyuria_text($data)
    {
        if (!empty($data)) {
            $this->gu_polyuria_text = $data;
        }
    }


    var $gu_hematuria;
    var $gu_hematuria_text;
    function get_gu_hematuria()
    {
        return $this->gu_hematuria;
    }
    function get_gu_hematuria_yes()
    {
        return $this->gu_hematuria == "Yes" ? "CHECKED" : "";
    }
    function get_gu_hematuria_no()
    {
        return $this->gu_hematuria == "No" ? "CHECKED" : "";
    }
    function set_gu_hematuria($data)
    {
        if (!empty($data)) {
            $this->gu_hematuria = $data;
        }
    }
    function get_gu_hematuria_text()
    {
        return $this->gu_hematuria_text;
    }
    function set_gu_hematuria_text($data)
    {
        if (!empty($data)) {
            $this->gu_hematuria_text = $data;
        }
    }


    var $gu_pyuria;
    var $gu_pyuria_text;
    function get_gu_pyuria()
    {
        return $this->gu_pyuria;
    }
    function get_gu_pyuria_yes()
    {
        return $this->gu_pyuria == "Yes" ? "CHECKED" : "";
    }
    function get_gu_pyuria_no()
    {
        return $this->gu_pyuria == "No" ? "CHECKED" : "";
    }
    function set_gu_pyuria($data)
    {
        if (!empty($data)) {
            $this->gu_pyuria = $data;
        }
    }
    function get_gu_pyuria_text()
    {
        return $this->gu_pyuria_text;
    }
    function set_gu_pyuria_text($data)
    {
        if (!empty($data)) {
            $this->gu_pyuria_text = $data;
        }
    }


    var $gu_umbilical_hernia;
    var $gu_umbilical_hernia_text;
    function get_gu_umbilical_hernia()
    {
        return $this->gu_umbilical_hernia;
    }
    function get_gu_umbilical_hernia_yes()
    {
        return $this->gu_umbilical_hernia == "Yes" ? "CHECKED" : "";
    }
    function get_gu_umbilical_hernia_no()
    {
        return $this->gu_umbilical_hernia == "No" ? "CHECKED" : "";
    }
    function set_gu_umbilical_hernia($data)
    {
        if (!empty($data)) {
            $this->gu_umbilical_hernia = $data;
        }
    }
    function get_gu_umbilical_hernia_text()
    {
        return $this->gu_umbilical_hernia_text;
    }
    function set_gu_umbilical_hernia_text($data)
    {
        if (!empty($data)) {
            $this->gu_umbilical_hernia_text = $data;
        }
    }


    var $gu_incontinence;
    var $gu_incontinence_text;
    function get_gu_incontinence()
    {
        return $this->gu_incontinence;
    }
    function get_gu_incontinence_yes()
    {
        return $this->gu_incontinence == "Yes" ? "CHECKED" : "";
    }
    function get_gu_incontinence_no()
    {
        return $this->gu_incontinence == "No" ? "CHECKED" : "";
    }
    function set_gu_incontinence($data)
    {
        if (!empty($data)) {
            $this->gu_incontinence = $data;
        }
    }
    function get_gu_incontinence_text()
    {
        return $this->gu_incontinence_text;
    }
    function set_gu_incontinence_text($data)
    {
        if (!empty($data)) {
            $this->gu_incontinence_text = $data;
        }
    }


    var $gu_nocturia;
    var $gu_nocturia_text;
    function get_gu_nocturia()
    {
        return $this->gu_nocturia;
    }
    function get_gu_nocturia_yes()
    {
        return $this->gu_nocturia == "Yes" ? "CHECKED" : "";
    }
    function get_gu_nocturia_no()
    {
        return $this->gu_nocturia == "No" ? "CHECKED" : "";
    }
    function set_gu_nocturia($data)
    {
        if (!empty($data)) {
            $this->gu_nocturia = $data;
        }
    }
    function get_gu_nocturia_text()
    {
        return $this->gu_nocturia_text;
    }
    function set_gu_nocturia_text($data)
    {
        if (!empty($data)) {
            $this->gu_nocturia_text = $data;
        }
    }


    var $gu_urinary_urgency;
    var $gu_urinary_urgency_text;
    function get_gu_urinary_urgency()
    {
        return $this->gu_urinary_urgency;
    }
    function get_gu_urinary_urgency_yes()
    {
        return $this->gu_urinary_urgency == "Yes" ? "CHECKED" : "";
    }
    function get_gu_urinary_urgency_no()
    {
        return $this->gu_urinary_urgency == "No" ? "CHECKED" : "";
    }
    function set_gu_urinary_urgency($data)
    {
        if (!empty($data)) {
            $this->gu_urinary_urgency = $data;
        }
    }
    function get_gu_urinary_urgency_text()
    {
        return $this->gu_urinary_urgency_text;
    }
    function set_gu_urinary_urgency_text($data)
    {
        if (!empty($data)) {
            $this->gu_urinary_urgency_text = $data;
        }
    }


    var $gu_recurrent_utis;
    var $gu_recurrent_utis_text;
    function get_gu_recurrent_utis()
    {
        return $this->gu_recurrent_utis;
    }
    function get_gu_recurrent_utis_yes()
    {
        return $this->gu_recurrent_utis == "Yes" ? "CHECKED" : "";
    }
    function get_gu_recurrent_utis_no()
    {
        return $this->gu_recurrent_utis == "No" ? "CHECKED" : "";
    }
    function set_gu_recurrent_utis($data)
    {
        if (!empty($data)) {
            $this->gu_recurrent_utis = $data;
        }
    }
    function get_gu_recurrent_utis_text()
    {
        return $this->gu_recurrent_utis_text;
    }
    function set_gu_recurrent_utis_text($data)
    {
        if (!empty($data)) {
            $this->gu_recurrent_utis_text = $data;
        }
    }


    var $gu_venereal_disease;
    var $gu_venereal_disease_text;
    function get_gu_venereal_disease()
    {
        return $this->gu_venereal_disease;
    }
    function get_gu_venereal_disease_yes()
    {
        return $this->gu_venereal_disease == "Yes" ? "CHECKED" : "";
    }
    function get_gu_venereal_disease_no()
    {
        return $this->gu_venereal_disease == "No" ? "CHECKED" : "";
    }
    function set_gu_venereal_disease($data)
    {
        if (!empty($data)) {
            $this->gu_venereal_disease = $data;
        }
    }
    function get_gu_venereal_disease_text()
    {
        return $this->gu_venereal_disease_text;
    }
    function set_gu_venereal_disease_text($data)
    {
        if (!empty($data)) {
            $this->gu_venereal_disease_text = $data;
        }
    }

    // ----- Erectile Dysfunction -----

    var $male_gu_erectile_dysfunction;
    var $male_gu_erectile_dysfunction_text;
    function get_male_gu_erectile_dysfunction()
    {
        return $this->male_gu_erectile_dysfunction;
    }
    function get_male_gu_erectile_dysfunction_yes()
    {
        return $this->male_gu_erectile_dysfunction == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_erectile_dysfunction_no()
    {
        return $this->male_gu_erectile_dysfunction == "No" ? "CHECKED" : "";
    }
    function set_male_gu_erectile_dysfunction($data)
    {
        if (!empty($data)) {
            $this->male_gu_erectile_dysfunction = $data;
        }
    }
    function get_male_gu_erectile_dysfunction_text()
    {
        return $this->male_gu_erectile_dysfunction_text;
    }
    function set_male_gu_erectile_dysfunction_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_erectile_dysfunction_text = $data;
        }
    }


    var $male_gu_inguinal_hernia;
    var $male_gu_inguinal_hernia_text;
    function get_male_gu_inguinal_hernia()
    {
        return $this->male_gu_inguinal_hernia;
    }
    function get_male_gu_inguinal_hernia_yes()
    {
        return $this->male_gu_inguinal_hernia == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_inguinal_hernia_no()
    {
        return $this->male_gu_inguinal_hernia == "No" ? "CHECKED" : "";
    }
    function set_male_gu_inguinal_hernia($data)
    {
        if (!empty($data)) {
            $this->male_gu_inguinal_hernia = $data;
        }
    }
    function get_male_gu_inguinal_hernia_text()
    {
        return $this->male_gu_inguinal_hernia_text;
    }
    function set_male_gu_inguinal_hernia_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_inguinal_hernia_text = $data;
        }
    }


    var $male_gu_penile_lesions;
    var $male_gu_penile_lesions_text;
    function get_male_gu_penile_lesions()
    {
        return $this->male_gu_penile_lesions;
    }
    function get_male_gu_penile_lesions_yes()
    {
        return $this->male_gu_penile_lesions == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_penile_lesions_no()
    {
        return $this->male_gu_penile_lesions == "No" ? "CHECKED" : "";
    }
    function set_male_gu_penile_lesions($data)
    {
        if (!empty($data)) {
            $this->male_gu_penile_lesions = $data;
        }
    }
    function get_male_gu_penile_lesions_text()
    {
        return $this->male_gu_penile_lesions_text;
    }
    function set_male_gu_penile_lesions_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_penile_lesions_text = $data;
        }
    }


    var $male_gu_scrotal_mass;
    var $male_gu_scrotal_mass_text;
    function get_male_gu_scrotal_mass()
    {
        return $this->male_gu_scrotal_mass;
    }
    function get_male_gu_scrotal_mass_yes()
    {
        return $this->male_gu_scrotal_mass == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_scrotal_mass_no()
    {
        return $this->male_gu_scrotal_mass == "No" ? "CHECKED" : "";
    }
    function set_male_gu_scrotal_mass($data)
    {
        if (!empty($data)) {
            $this->male_gu_scrotal_mass = $data;
        }
    }
    function get_male_gu_scrotal_mass_text()
    {
        return $this->male_gu_scrotal_mass_text;
    }
    function set_male_gu_scrotal_mass_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_scrotal_mass_text = $data;
        }
    }


    var $male_gu_testicular_pain;
    var $male_gu_testicular_pain_text;
    function get_male_gu_testicular_pain()
    {
        return $this->male_gu_testicular_pain;
    }
    function get_male_gu_testicular_pain_yes()
    {
        return $this->male_gu_testicular_pain == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_testicular_pain_no()
    {
        return $this->male_gu_testicular_pain == "No" ? "CHECKED" : "";
    }
    function set_male_gu_testicular_pain($data)
    {
        if (!empty($data)) {
            $this->male_gu_testicular_pain = $data;
        }
    }
    function get_male_gu_testicular_pain_text()
    {
        return $this->male_gu_testicular_pain_text;
    }
    function set_male_gu_testicular_pain_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_testicular_pain_text = $data;
        }
    }


    var $male_gu_urethral_discharge;
    var $male_gu_urethral_discharge_text;
    function get_male_gu_urethral_discharge()
    {
        return $this->male_gu_urethral_discharge;
    }
    function get_male_gu_urethral_discharge_yes()
    {
        return $this->male_gu_urethral_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_urethral_discharge_no()
    {
        return $this->male_gu_urethral_discharge == "No" ? "CHECKED" : "";
    }
    function set_male_gu_urethral_discharge($data)
    {
        if (!empty($data)) {
            $this->male_gu_urethral_discharge = $data;
        }
    }
    function get_male_gu_urethral_discharge_text()
    {
        return $this->male_gu_urethral_discharge_text;
    }
    function set_male_gu_urethral_discharge_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_urethral_discharge_text = $data;
        }
    }


    var $male_gu_weak_urinary_stream;
    var $male_gu_weak_urinary_stream_text;
    function get_male_gu_weak_urinary_stream()
    {
        return $this->male_gu_weak_urinary_stream;
    }
    function get_male_gu_weak_urinary_stream_yes()
    {
        return $this->male_gu_weak_urinary_stream == "Yes" ? "CHECKED" : "";
    }
    function get_male_gu_weak_urinary_stream_no()
    {
        return $this->male_gu_weak_urinary_stream == "No" ? "CHECKED" : "";
    }
    function set_male_gu_weak_urinary_stream($data)
    {
        if (!empty($data)) {
            $this->male_gu_weak_urinary_stream = $data;
        }
    }
    function get_male_gu_weak_urinary_stream_text()
    {
        return $this->male_gu_weak_urinary_stream_text;
    }
    function set_male_gu_weak_urinary_stream_text($data)
    {
        if (!empty($data)) {
            $this->male_gu_weak_urinary_stream_text = $data;
        }
    }

    // ----- Abnormal Menses -----

    var $female_gu_abnormal_menses;
    var $female_gu_abnormal_menses_text;
    function get_female_gu_abnormal_menses()
    {
        return $this->female_gu_abnormal_menses;
    }
    function get_female_gu_abnormal_menses_yes()
    {
        return $this->female_gu_abnormal_menses == "Yes" ? "CHECKED" : "";
    }
    function get_female_gu_abnormal_menses_no()
    {
        return $this->female_gu_abnormal_menses == "No" ? "CHECKED" : "";
    }
    function set_female_gu_abnormal_menses($data)
    {
        if (!empty($data)) {
            $this->female_gu_abnormal_menses = $data;
        }
    }
    function get_female_gu_abnormal_menses_text()
    {
        return $this->female_gu_abnormal_menses_text;
    }
    function set_female_gu_abnormal_menses_text($data)
    {
        if (!empty($data)) {
            $this->female_gu_abnormal_menses_text = $data;
        }
    }


    var $female_gu_abnormal_vaginal_bleeding;
    var $female_gu_abnormal_vaginal_bleeding_text;
    function get_female_gu_abnormal_vaginal_bleeding()
    {
        return $this->female_gu_abnormal_vaginal_bleeding;
    }
    function get_female_gu_abnormal_vaginal_bleeding_yes()
    {
        return $this->female_gu_abnormal_vaginal_bleeding == "Yes" ? "CHECKED" : "";
    }
    function get_female_gu_abnormal_vaginal_bleeding_no()
    {
        return $this->female_gu_abnormal_vaginal_bleeding == "No" ? "CHECKED" : "";
    }
    function set_female_gu_abnormal_vaginal_bleeding($data)
    {
        if (!empty($data)) {
            $this->female_gu_abnormal_vaginal_bleeding = $data;
        }
    }
    function get_female_gu_abnormal_vaginal_bleeding_text()
    {
        return $this->female_gu_abnormal_vaginal_bleeding_text;
    }
    function set_female_gu_abnormal_vaginal_bleeding_text($data)
    {
        if (!empty($data)) {
            $this->female_gu_abnormal_vaginal_bleeding_text = $data;
        }
    }


    var $female_gu_vaginal_discharge;
    var $female_gu_vaginal_discharge_text;
    function get_female_gu_vaginal_discharge()
    {
        return $this->female_gu_vaginal_discharge;
    }
    function get_female_gu_vaginal_discharge_yes()
    {
        return $this->female_gu_vaginal_discharge == "Yes" ? "CHECKED" : "";
    }
    function get_female_gu_vaginal_discharge_no()
    {
        return $this->female_gu_vaginal_discharge == "No" ? "CHECKED" : "";
    }
    function set_female_gu_vaginal_discharge($data)
    {
        if (!empty($data)) {
            $this->female_gu_vaginal_discharge = $data;
        }
    }
    function get_female_gu_vaginal_discharge_text()
    {
        return $this->female_gu_vaginal_discharge_text;
    }
    function set_female_gu_vaginal_discharge_text($data)
    {
        if (!empty($data)) {
            $this->female_gu_vaginal_discharge_text = $data;
        }
    }

    // ----- abdominal pain -----

    var $gi_abdominal_pain;
    var $gi_abdominal_pain_text;
    function get_gi_abdominal_pain()
    {
        return $this->gi_abdominal_pain;
    }
    function get_gi_abdominal_pain_yes()
    {
        return $this->gi_abdominal_pain == "Yes" ? "CHECKED" : "";
    }
    function get_gi_abdominal_pain_no()
    {
        return $this->gi_abdominal_pain == "No" ? "CHECKED" : "";
    }
    function set_gi_abdominal_pain($data)
    {
        if (!empty($data)) {
            $this->gi_abdominal_pain = $data;
        }
    }
    function get_gi_abdominal_pain_text()
    {
        return $this->gi_abdominal_pain_text;
    }
    function set_gi_abdominal_pain_text($data)
    {
        if (!empty($data)) {
            $this->gi_abdominal_pain_text = $data;
        }
    }


    var $gi_cramps;
    var $gi_cramps_text;
    function get_gi_cramps()
    {
        return $this->gi_cramps;
    }
    function get_gi_cramps_yes()
    {
        return $this->gi_cramps == "Yes" ? "CHECKED" : "";
    }
    function get_gi_cramps_no()
    {
        return $this->gi_cramps == "No" ? "CHECKED" : "";
    }
    function set_gi_cramps($data)
    {
        if (!empty($data)) {
            $this->gi_cramps = $data;
        }
    }
    function get_gi_cramps_text()
    {
        return $this->gi_cramps_text;
    }
    function set_gi_cramps_text($data)
    {
        if (!empty($data)) {
            $this->gi_cramps_text = $data;
        }
    }


    var $gi_tenderness;
    var $gi_tenderness_text;
    function get_gi_tenderness()
    {
        return $this->gi_tenderness;
    }
    function get_gi_tenderness_yes()
    {
        return $this->gi_tenderness == "Yes" ? "CHECKED" : "";
    }
    function get_gi_tenderness_no()
    {
        return $this->gi_tenderness == "No" ? "CHECKED" : "";
    }
    function set_gi_tenderness($data)
    {
        if (!empty($data)) {
            $this->gi_tenderness = $data;
        }
    }
    function get_gi_tenderness_text()
    {
        return $this->gi_tenderness_text;
    }
    function set_gi_tenderness_text($data)
    {
        if (!empty($data)) {
            $this->gi_tenderness_text = $data;
        }
    }


    var $gi_vomiting;
    var $gi_vomiting_text;
    function get_gi_vomiting()
    {
        return $this->gi_vomiting;
    }
    function get_gi_vomiting_yes()
    {
        return $this->gi_vomiting == "Yes" ? "CHECKED" : "";
    }
    function get_gi_vomiting_no()
    {
        return $this->gi_vomiting == "No" ? "CHECKED" : "";
    }
    function set_gi_vomiting($data)
    {
        if (!empty($data)) {
            $this->gi_vomiting = $data;
        }
    }
    function get_gi_vomiting_text()
    {
        return $this->gi_vomiting_text;
    }
    function set_gi_vomiting_text($data)
    {
        if (!empty($data)) {
            $this->gi_vomiting_text = $data;
        }
    }


    var $gi_frequent_diarrhea;
    var $gi_frequent_diarrhea_text;
    function get_gi_frequent_diarrhea()
    {
        return $this->gi_frequent_diarrhea;
    }
    function get_gi_frequent_diarrhea_yes()
    {
        return $this->gi_frequent_diarrhea == "Yes" ? "CHECKED" : "";
    }
    function get_gi_frequent_diarrhea_no()
    {
        return $this->gi_frequent_diarrhea == "No" ? "CHECKED" : "";
    }
    function set_gi_frequent_diarrhea($data)
    {
        if (!empty($data)) {
            $this->gi_frequent_diarrhea = $data;
        }
    }
    function get_gi_frequent_diarrhea_text()
    {
        return $this->gi_frequent_diarrhea_text;
    }
    function set_gi_frequent_diarrhea_text($data)
    {
        if (!empty($data)) {
            $this->gi_frequent_diarrhea_text = $data;
        }
    }


    var $gi_significant_constipation;
    var $gi_significant_constipation_text;
    function get_gi_significant_constipation()
    {
        return $this->gi_significant_constipation;
    }
    function get_gi_significant_constipation_yes()
    {
        return $this->gi_significant_constipation == "Yes" ? "CHECKED" : "";
    }
    function get_gi_significant_constipation_no()
    {
        return $this->gi_significant_constipation == "No" ? "CHECKED" : "";
    }
    function set_gi_significant_constipation($data)
    {
        if (!empty($data)) {
            $this->gi_significant_constipation = $data;
        }
    }
    function get_gi_significant_constipation_text()
    {
        return $this->gi_significant_constipation_text;
    }
    function set_gi_significant_constipation_text($data)
    {
        if (!empty($data)) {
            $this->gi_significant_constipation_text = $data;
        }
    }


    var $gi_excessive_belching;
    var $gi_excessive_belching_text;
    function get_gi_excessive_belching()
    {
        return $this->gi_excessive_belching;
    }
    function get_gi_excessive_belching_yes()
    {
        return $this->gi_excessive_belching == "Yes" ? "CHECKED" : "";
    }
    function get_gi_excessive_belching_no()
    {
        return $this->gi_excessive_belching == "No" ? "CHECKED" : "";
    }
    function set_gi_excessive_belching($data)
    {
        if (!empty($data)) {
            $this->gi_excessive_belching = $data;
        }
    }
    function get_gi_excessive_belching_text()
    {
        return $this->gi_excessive_belching_text;
    }
    function set_gi_excessive_belching_text($data)
    {
        if (!empty($data)) {
            $this->gi_excessive_belching_text = $data;
        }
    }


    var $gi_changed_bowel_habits;
    var $gi_changed_bowel_habits_text;
    function get_gi_changed_bowel_habits()
    {
        return $this->gi_changed_bowel_habits;
    }
    function get_gi_changed_bowel_habits_yes()
    {
        return $this->gi_changed_bowel_habits == "Yes" ? "CHECKED" : "";
    }
    function get_gi_changed_bowel_habits_no()
    {
        return $this->gi_changed_bowel_habits == "No" ? "CHECKED" : "";
    }
    function set_gi_changed_bowel_habits($data)
    {
        if (!empty($data)) {
            $this->gi_changed_bowel_habits = $data;
        }
    }
    function get_gi_changed_bowel_habits_text()
    {
        return $this->gi_changed_bowel_habits_text;
    }
    function set_gi_changed_bowel_habits_text($data)
    {
        if (!empty($data)) {
            $this->gi_changed_bowel_habits_text = $data;
        }
    }


    var $gi_excessive_flatulence;
    var $gi_excessive_flatulence_text;
    function get_gi_excessive_flatulence()
    {
        return $this->gi_excessive_flatulence;
    }
    function get_gi_excessive_flatulence_yes()
    {
        return $this->gi_excessive_flatulence == "Yes" ? "CHECKED" : "";
    }
    function get_gi_excessive_flatulence_no()
    {
        return $this->gi_excessive_flatulence == "No" ? "CHECKED" : "";
    }
    function set_gi_excessive_flatulence($data)
    {
        if (!empty($data)) {
            $this->gi_excessive_flatulence = $data;
        }
    }
    function get_gi_excessive_flatulence_text()
    {
        return $this->gi_excessive_flatulence_text;
    }
    function set_gi_excessive_flatulence_text($data)
    {
        if (!empty($data)) {
            $this->gi_excessive_flatulence_text = $data;
        }
    }


    var $gi_hematemesis;
    var $gi_hematemesis_text;
    function get_gi_hematemesis()
    {
        return $this->gi_hematemesis;
    }
    function get_gi_hematemesis_yes()
    {
        return $this->gi_hematemesis == "Yes" ? "CHECKED" : "";
    }
    function get_gi_hematemesis_no()
    {
        return $this->gi_hematemesis == "No" ? "CHECKED" : "";
    }
    function set_gi_hematemesis($data)
    {
        if (!empty($data)) {
            $this->gi_hematemesis = $data;
        }
    }
    function get_gi_hematemesis_text()
    {
        return $this->gi_hematemesis_text;
    }
    function set_gi_hematemesis_text($data)
    {
        if (!empty($data)) {
            $this->gi_hematemesis_text = $data;
        }
    }


    var $gi_hemorrhoids;
    var $gi_hemorrhoids_text;
    function get_gi_hemorrhoids()
    {
        return $this->gi_hemorrhoids;
    }
    function get_gi_hemorrhoids_yes()
    {
        return $this->gi_hemorrhoids == "Yes" ? "CHECKED" : "";
    }
    function get_gi_hemorrhoids_no()
    {
        return $this->gi_hemorrhoids == "No" ? "CHECKED" : "";
    }
    function set_gi_hemorrhoids($data)
    {
        if (!empty($data)) {
            $this->gi_hemorrhoids = $data;
        }
    }
    function get_gi_hemorrhoids_text()
    {
        return $this->gi_hemorrhoids_text;
    }
    function set_gi_hemorrhoids_text($data)
    {
        if (!empty($data)) {
            $this->gi_hemorrhoids_text = $data;
        }
    }


    var $gi_hepatitis;
    var $gi_hepatitis_text;
    function get_gi_hepatitis()
    {
        return $this->gi_hepatitis;
    }
    function get_gi_hepatitis_yes()
    {
        return $this->gi_hepatitis == "Yes" ? "CHECKED" : "";
    }
    function get_gi_hepatitis_no()
    {
        return $this->gi_hepatitis == "No" ? "CHECKED" : "";
    }
    function set_gi_hepatitis($data)
    {
        if (!empty($data)) {
            $this->gi_hepatitis = $data;
        }
    }
    function get_gi_hepatitis_text()
    {
        return $this->gi_hepatitis_text;
    }
    function set_gi_hepatitis_text($data)
    {
        if (!empty($data)) {
            $this->gi_hepatitis_text = $data;
        }
    }


    var $gi_jaundice;
    var $gi_jaundice_text;
    function get_gi_jaundice()
    {
        return $this->gi_jaundice;
    }
    function get_gi_jaundice_yes()
    {
        return $this->gi_jaundice == "Yes" ? "CHECKED" : "";
    }
    function get_gi_jaundice_no()
    {
        return $this->gi_jaundice == "No" ? "CHECKED" : "";
    }
    function set_gi_jaundice($data)
    {
        if (!empty($data)) {
            $this->gi_jaundice = $data;
        }
    }
    function get_gi_jaundice_text()
    {
        return $this->gi_jaundice_text;
    }
    function set_gi_jaundice_text($data)
    {
        if (!empty($data)) {
            $this->gi_jaundice_text = $data;
        }
    }


    var $gi_lactose_intolerance;
    var $gi_lactose_intolerance_text;
    function get_gi_lactose_intolerance()
    {
        return $this->gi_lactose_intolerance;
    }
    function get_gi_lactose_intolerance_yes()
    {
        return $this->gi_lactose_intolerance == "Yes" ? "CHECKED" : "";
    }
    function get_gi_lactose_intolerance_no()
    {
        return $this->gi_lactose_intolerance == "No" ? "CHECKED" : "";
    }
    function set_gi_lactose_intolerance($data)
    {
        if (!empty($data)) {
            $this->gi_lactose_intolerance = $data;
        }
    }
    function get_gi_lactose_intolerance_text()
    {
        return $this->gi_lactose_intolerance_text;
    }
    function set_gi_lactose_intolerance_text($data)
    {
        if (!empty($data)) {
            $this->gi_lactose_intolerance_text = $data;
        }
    }


    var $gi_chronic_laxative_use;
    var $gi_chronic_laxative_use_text;
    function get_gi_chronic_laxative_use()
    {
        return $this->gi_chronic_laxative_use;
    }
    function get_gi_chronic_laxative_use_yes()
    {
        return $this->gi_chronic_laxative_use == "Yes" ? "CHECKED" : "";
    }
    function get_gi_chronic_laxative_use_no()
    {
        return $this->gi_chronic_laxative_use == "No" ? "CHECKED" : "";
    }
    function set_gi_chronic_laxative_use($data)
    {
        if (!empty($data)) {
            $this->gi_chronic_laxative_use = $data;
        }
    }
    function get_gi_chronic_laxative_use_text()
    {
        return $this->gi_chronic_laxative_use_text;
    }
    function set_gi_chronic_laxative_use_text($data)
    {
        if (!empty($data)) {
            $this->gi_chronic_laxative_use_text = $data;
        }
    }


    var $gi_melena;
    var $gi_melena_text;
    function get_gi_melena()
    {
        return $this->gi_melena;
    }
    function get_gi_melena_yes()
    {
        return $this->gi_melena == "Yes" ? "CHECKED" : "";
    }
    function get_gi_melena_no()
    {
        return $this->gi_melena == "No" ? "CHECKED" : "";
    }
    function set_gi_melena($data)
    {
        if (!empty($data)) {
            $this->gi_melena = $data;
        }
    }
    function get_gi_melena_text()
    {
        return $this->gi_melena_text;
    }
    function set_gi_melena_text($data)
    {
        if (!empty($data)) {
            $this->gi_melena_text = $data;
        }
    }


    var $gi_frequent_nausea;
    var $gi_frequent_nausea_text;
    function get_gi_frequent_nausea()
    {
        return $this->gi_frequent_nausea;
    }
    function get_gi_frequent_nausea_yes()
    {
        return $this->gi_frequent_nausea == "Yes" ? "CHECKED" : "";
    }
    function get_gi_frequent_nausea_no()
    {
        return $this->gi_frequent_nausea == "No" ? "CHECKED" : "";
    }
    function set_gi_frequent_nausea($data)
    {
        if (!empty($data)) {
            $this->gi_frequent_nausea = $data;
        }
    }
    function get_gi_frequent_nausea_text()
    {
        return $this->gi_frequent_nausea_text;
    }
    function set_gi_frequent_nausea_text($data)
    {
        if (!empty($data)) {
            $this->gi_frequent_nausea_text = $data;
        }
    }


    var $gi_rectal_bleeding;
    var $gi_rectal_bleeding_text;
    function get_gi_rectal_bleeding()
    {
        return $this->gi_rectal_bleeding;
    }
    function get_gi_rectal_bleeding_yes()
    {
        return $this->gi_rectal_bleeding == "Yes" ? "CHECKED" : "";
    }
    function get_gi_rectal_bleeding_no()
    {
        return $this->gi_rectal_bleeding == "No" ? "CHECKED" : "";
    }
    function set_gi_rectal_bleeding($data)
    {
        if (!empty($data)) {
            $this->gi_rectal_bleeding = $data;
        }
    }
    function get_gi_rectal_bleeding_text()
    {
        return $this->gi_rectal_bleeding_text;
    }
    function set_gi_rectal_bleeding_text($data)
    {
        if (!empty($data)) {
            $this->gi_rectal_bleeding_text = $data;
        }
    }


    var $gi_rectal_pain;
    var $gi_rectal_pain_text;
    function get_gi_rectal_pain()
    {
        return $this->gi_rectal_pain;
    }
    function get_gi_rectal_pain_yes()
    {
        return $this->gi_rectal_pain == "Yes" ? "CHECKED" : "";
    }
    function get_gi_rectal_pain_no()
    {
        return $this->gi_rectal_pain == "No" ? "CHECKED" : "";
    }
    function set_gi_rectal_pain($data)
    {
        if (!empty($data)) {
            $this->gi_rectal_pain = $data;
        }
    }
    function get_gi_rectal_pain_text()
    {
        return $this->gi_rectal_pain_text;
    }
    function set_gi_rectal_pain_text($data)
    {
        if (!empty($data)) {
            $this->gi_rectal_pain_text = $data;
        }
    }


    var $gi_stool_caliber_change;
    var $gi_stool_caliber_change_text;
    function get_gi_stool_caliber_change()
    {
        return $this->gi_stool_caliber_change;
    }
    function get_gi_stool_caliber_change_yes()
    {
        return $this->gi_stool_caliber_change == "Yes" ? "CHECKED" : "";
    }
    function get_gi_stool_caliber_change_no()
    {
        return $this->gi_stool_caliber_change == "No" ? "CHECKED" : "";
    }
    function set_gi_stool_caliber_change($data)
    {
        if (!empty($data)) {
            $this->gi_stool_caliber_change = $data;
        }
    }
    function get_gi_stool_caliber_change_text()
    {
        return $this->gi_stool_caliber_change_text;
    }
    function set_gi_stool_caliber_change_text($data)
    {
        if (!empty($data)) {
            $this->gi_stool_caliber_change_text = $data;
        }
    }

    // ----- pallor -----

    var $integument_pallor;
    var $integument_pallor_text;
    function get_integument_pallor()
    {
        return $this->integument_pallor;
    }
    function get_integument_pallor_yes()
    {
        return $this->integument_pallor == "Yes" ? "CHECKED" : "";
    }
    function get_integument_pallor_no()
    {
        return $this->integument_pallor == "No" ? "CHECKED" : "";
    }
    function set_integument_pallor($data)
    {
        if (!empty($data)) {
            $this->integument_pallor = $data;
        }
    }
    function get_integument_pallor_text()
    {
        return $this->integument_pallor_text;
    }
    function set_integument_pallor_text($data)
    {
        if (!empty($data)) {
            $this->integument_pallor_text = $data;
        }
    }


    var $integument_diaphoresis;
    var $integument_diaphoresis_text;
    function get_integument_diaphoresis()
    {
        return $this->integument_diaphoresis;
    }
    function get_integument_diaphoresis_yes()
    {
        return $this->integument_diaphoresis == "Yes" ? "CHECKED" : "";
    }
    function get_integument_diaphoresis_no()
    {
        return $this->integument_diaphoresis == "No" ? "CHECKED" : "";
    }
    function set_integument_diaphoresis($data)
    {
        if (!empty($data)) {
            $this->integument_diaphoresis = $data;
        }
    }
    function get_integument_diaphoresis_text()
    {
        return $this->integument_diaphoresis_text;
    }
    function set_integument_diaphoresis_text($data)
    {
        if (!empty($data)) {
            $this->integument_diaphoresis_text = $data;
        }
    }


    var $integument_rash;
    var $integument_rash_text;
    function get_integument_rash()
    {
        return $this->integument_rash;
    }
    function get_integument_rash_yes()
    {
        return $this->integument_rash == "Yes" ? "CHECKED" : "";
    }
    function get_integument_rash_no()
    {
        return $this->integument_rash == "No" ? "CHECKED" : "";
    }
    function set_integument_rash($data)
    {
        if (!empty($data)) {
            $this->integument_rash = $data;
        }
    }
    function get_integument_rash_text()
    {
        return $this->integument_rash_text;
    }
    function set_integument_rash_text($data)
    {
        if (!empty($data)) {
            $this->integument_rash_text = $data;
        }
    }


    var $integument_itching;
    var $integument_itching_text;
    function get_integument_itching()
    {
        return $this->integument_itching;
    }
    function get_integument_itching_yes()
    {
        return $this->integument_itching == "Yes" ? "CHECKED" : "";
    }
    function get_integument_itching_no()
    {
        return $this->integument_itching == "No" ? "CHECKED" : "";
    }
    function set_integument_itching($data)
    {
        if (!empty($data)) {
            $this->integument_itching = $data;
        }
    }
    function get_integument_itching_text()
    {
        return $this->integument_itching_text;
    }
    function set_integument_itching_text($data)
    {
        if (!empty($data)) {
            $this->integument_itching_text = $data;
        }
    }


    var $integument_ulcers;
    var $integument_ulcers_text;
    function get_integument_ulcers()
    {
        return $this->integument_ulcers;
    }
    function get_integument_ulcers_yes()
    {
        return $this->integument_ulcers == "Yes" ? "CHECKED" : "";
    }
    function get_integument_ulcers_no()
    {
        return $this->integument_ulcers == "No" ? "CHECKED" : "";
    }
    function set_integument_ulcers($data)
    {
        if (!empty($data)) {
            $this->integument_ulcers = $data;
        }
    }
    function get_integument_ulcers_text()
    {
        return $this->integument_ulcers_text;
    }
    function set_integument_ulcers_text($data)
    {
        if (!empty($data)) {
            $this->integument_ulcers_text = $data;
        }
    }


    var $integument_abscess;
    var $integument_abscess_text;
    function get_integument_abscess()
    {
        return $this->integument_abscess;
    }
    function get_integument_abscess_yes()
    {
        return $this->integument_abscess == "Yes" ? "CHECKED" : "";
    }
    function get_integument_abscess_no()
    {
        return $this->integument_abscess == "No" ? "CHECKED" : "";
    }
    function set_integument_abscess($data)
    {
        if (!empty($data)) {
            $this->integument_abscess = $data;
        }
    }
    function get_integument_abscess_text()
    {
        return $this->integument_abscess_text;
    }
    function set_integument_abscess_text($data)
    {
        if (!empty($data)) {
            $this->integument_abscess_text = $data;
        }
    }


    var $integument_nodules;
    var $integument_nodules_text;
    function get_integument_nodules()
    {
        return $this->integument_nodules;
    }
    function get_integument_nodules_yes()
    {
        return $this->integument_nodules == "Yes" ? "CHECKED" : "";
    }
    function get_integument_nodules_no()
    {
        return $this->integument_nodules == "No" ? "CHECKED" : "";
    }
    function set_integument_nodules($data)
    {
        if (!empty($data)) {
            $this->integument_nodules = $data;
        }
    }
    function get_integument_nodules_text()
    {
        return $this->integument_nodules_text;
    }
    function set_integument_nodules_text($data)
    {
        if (!empty($data)) {
            $this->integument_nodules_text = $data;
        }
    }


    var $integument_acne;
    var $integument_acne_text;
    function get_integument_acne()
    {
        return $this->integument_acne;
    }
    function get_integument_acne_yes()
    {
        return $this->integument_acne == "Yes" ? "CHECKED" : "";
    }
    function get_integument_acne_no()
    {
        return $this->integument_acne == "No" ? "CHECKED" : "";
    }
    function set_integument_acne($data)
    {
        if (!empty($data)) {
            $this->integument_acne = $data;
        }
    }
    function get_integument_acne_text()
    {
        return $this->integument_acne_text;
    }
    function set_integument_acne_text($data)
    {
        if (!empty($data)) {
            $this->integument_acne_text = $data;
        }
    }


    var $integument_recurrent_boils;
    var $integument_recurrent_boils_text;
    function get_integument_recurrent_boils()
    {
        return $this->integument_recurrent_boils;
    }
    function get_integument_recurrent_boils_yes()
    {
        return $this->integument_recurrent_boils == "Yes" ? "CHECKED" : "";
    }
    function get_integument_recurrent_boils_no()
    {
        return $this->integument_recurrent_boils == "No" ? "CHECKED" : "";
    }
    function set_integument_recurrent_boils($data)
    {
        if (!empty($data)) {
            $this->integument_recurrent_boils = $data;
        }
    }
    function get_integument_recurrent_boils_text()
    {
        return $this->integument_recurrent_boils_text;
    }
    function set_integument_recurrent_boils_text($data)
    {
        if (!empty($data)) {
            $this->integument_recurrent_boils_text = $data;
        }
    }


    var $integument_chronic_eczema;
    var $integument_chronic_eczema_text;
    function get_integument_chronic_eczema()
    {
        return $this->integument_chronic_eczema;
    }
    function get_integument_chronic_eczema_yes()
    {
        return $this->integument_chronic_eczema == "Yes" ? "CHECKED" : "";
    }
    function get_integument_chronic_eczema_no()
    {
        return $this->integument_chronic_eczema == "No" ? "CHECKED" : "";
    }
    function set_integument_chronic_eczema($data)
    {
        if (!empty($data)) {
            $this->integument_chronic_eczema = $data;
        }
    }
    function get_integument_chronic_eczema_text()
    {
        return $this->integument_chronic_eczema_text;
    }
    function set_integument_chronic_eczema_text($data)
    {
        if (!empty($data)) {
            $this->integument_chronic_eczema_text = $data;
        }
    }


    var $integument_changing_moles;
    var $integument_changing_moles_text;
    function get_integument_changing_moles()
    {
        return $this->integument_changing_moles;
    }
    function get_integument_changing_moles_yes()
    {
        return $this->integument_changing_moles == "Yes" ? "CHECKED" : "";
    }
    function get_integument_changing_moles_no()
    {
        return $this->integument_changing_moles == "No" ? "CHECKED" : "";
    }
    function set_integument_changing_moles($data)
    {
        if (!empty($data)) {
            $this->integument_changing_moles = $data;
        }
    }
    function get_integument_changing_moles_text()
    {
        return $this->integument_changing_moles_text;
    }
    function set_integument_changing_moles_text($data)
    {
        if (!empty($data)) {
            $this->integument_changing_moles_text = $data;
        }
    }


    var $integument_nail_abnormalities;
    var $integument_nail_abnormalities_text;
    function get_integument_nail_abnormalities()
    {
        return $this->integument_nail_abnormalities;
    }
    function get_integument_nail_abnormalities_yes()
    {
        return $this->integument_nail_abnormalities == "Yes" ? "CHECKED" : "";
    }
    function get_integument_nail_abnormalities_no()
    {
        return $this->integument_nail_abnormalities == "No" ? "CHECKED" : "";
    }
    function set_integument_nail_abnormalities($data)
    {
        if (!empty($data)) {
            $this->integument_nail_abnormalities = $data;
        }
    }
    function get_integument_nail_abnormalities_text()
    {
        return $this->integument_nail_abnormalities_text;
    }
    function set_integument_nail_abnormalities_text($data)
    {
        if (!empty($data)) {
            $this->integument_nail_abnormalities_text = $data;
        }
    }


    var $integument_psoriasis;
    var $integument_psoriasis_text;
    function get_integument_psoriasis()
    {
        return $this->integument_psoriasis;
    }
    function get_integument_psoriasis_yes()
    {
        return $this->integument_psoriasis == "Yes" ? "CHECKED" : "";
    }
    function get_integument_psoriasis_no()
    {
        return $this->integument_psoriasis == "No" ? "CHECKED" : "";
    }
    function set_integument_psoriasis($data)
    {
        if (!empty($data)) {
            $this->integument_psoriasis = $data;
        }
    }
    function get_integument_psoriasis_text()
    {
        return $this->integument_psoriasis_text;
    }
    function set_integument_psoriasis_text($data)
    {
        if (!empty($data)) {
            $this->integument_psoriasis_text = $data;
        }
    }


    var $integument_recurrent_hives;
    var $integument_recurrent_hives_text;
    function get_integument_recurrent_hives()
    {
        return $this->integument_recurrent_hives;
    }
    function get_integument_recurrent_hives_yes()
    {
        return $this->integument_recurrent_hives == "Yes" ? "CHECKED" : "";
    }
    function get_integument_recurrent_hives_no()
    {
        return $this->integument_recurrent_hives == "No" ? "CHECKED" : "";
    }
    function set_integument_recurrent_hives($data)
    {
        if (!empty($data)) {
            $this->integument_recurrent_hives = $data;
        }
    }
    function get_integument_recurrent_hives_text()
    {
        return $this->integument_recurrent_hives_text;
    }
    function set_integument_recurrent_hives_text($data)
    {
        if (!empty($data)) {
            $this->integument_recurrent_hives_text = $data;
        }
    }

    // ----- deformity -----

    var $musculoskeletal_deformity;
    var $musculoskeletal_deformity_text;
    function get_musculoskeletal_deformity()
    {
        return $this->musculoskeletal_deformity;
    }
    function get_musculoskeletal_deformity_yes()
    {
        return $this->musculoskeletal_deformity == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_deformity_no()
    {
        return $this->musculoskeletal_deformity == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_deformity($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_deformity = $data;
        }
    }
    function get_musculoskeletal_deformity_text()
    {
        return $this->musculoskeletal_deformity_text;
    }
    function set_musculoskeletal_deformity_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_deformity_text = $data;
        }
    }


    var $musculoskeletal_edema;
    var $musculoskeletal_edema_text;
    function get_musculoskeletal_edema()
    {
        return $this->musculoskeletal_edema;
    }
    function get_musculoskeletal_edema_yes()
    {
        return $this->musculoskeletal_edema == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_edema_no()
    {
        return $this->musculoskeletal_edema == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_edema($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_edema = $data;
        }
    }
    function get_musculoskeletal_edema_text()
    {
        return $this->musculoskeletal_edema_text;
    }
    function set_musculoskeletal_edema_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_edema_text = $data;
        }
    }


    var $musculoskeletal_pain;
    var $musculoskeletal_pain_text;
    function get_musculoskeletal_pain()
    {
        return $this->musculoskeletal_pain;
    }
    function get_musculoskeletal_pain_yes()
    {
        return $this->musculoskeletal_pain == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_pain_no()
    {
        return $this->musculoskeletal_pain == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_pain($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_pain = $data;
        }
    }
    function get_musculoskeletal_pain_text()
    {
        return $this->musculoskeletal_pain_text;
    }
    function set_musculoskeletal_pain_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_pain_text = $data;
        }
    }


    var $musculoskeletal_limited_rom;
    var $musculoskeletal_limited_rom_text;
    function get_musculoskeletal_limited_rom()
    {
        return $this->musculoskeletal_limited_rom;
    }
    function get_musculoskeletal_limited_rom_yes()
    {
        return $this->musculoskeletal_limited_rom == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_limited_rom_no()
    {
        return $this->musculoskeletal_limited_rom == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_limited_rom($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_limited_rom = $data;
        }
    }
    function get_musculoskeletal_limited_rom_text()
    {
        return $this->musculoskeletal_limited_rom_text;
    }
    function set_musculoskeletal_limited_rom_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_limited_rom_text = $data;
        }
    }


    var $musculoskeletal_gait;
    var $musculoskeletal_gait_text;
    function get_musculoskeletal_gait()
    {
        return $this->musculoskeletal_gait;
    }
    function get_musculoskeletal_gait_yes()
    {
        return $this->musculoskeletal_gait == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_gait_no()
    {
        return $this->musculoskeletal_gait == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_gait($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_gait = $data;
        }
    }
    function get_musculoskeletal_gait_text()
    {
        return $this->musculoskeletal_gait_text;
    }
    function set_musculoskeletal_gait_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_gait_text = $data;
        }
    }


    var $musculoskeletal_arthritis;
    var $musculoskeletal_arthritis_text;
    function get_musculoskeletal_arthritis()
    {
        return $this->musculoskeletal_arthritis;
    }
    function get_musculoskeletal_arthritis_yes()
    {
        return $this->musculoskeletal_arthritis == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_arthritis_no()
    {
        return $this->musculoskeletal_arthritis == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_arthritis($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_arthritis = $data;
        }
    }
    function get_musculoskeletal_arthritis_text()
    {
        return $this->musculoskeletal_arthritis_text;
    }
    function set_musculoskeletal_arthritis_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_arthritis_text = $data;
        }
    }


    var $musculoskeletal_neck_pain;
    var $musculoskeletal_neck_pain_text;
    function get_musculoskeletal_neck_pain()
    {
        return $this->musculoskeletal_neck_pain;
    }
    function get_musculoskeletal_neck_pain_yes()
    {
        return $this->musculoskeletal_neck_pain == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_neck_pain_no()
    {
        return $this->musculoskeletal_neck_pain == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_neck_pain($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_neck_pain = $data;
        }
    }
    function get_musculoskeletal_neck_pain_text()
    {
        return $this->musculoskeletal_neck_pain_text;
    }
    function set_musculoskeletal_neck_pain_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_neck_pain_text = $data;
        }
    }


    var $musculoskeletal_mid_back_pain;
    var $musculoskeletal_mid_back_pain_text;
    function get_musculoskeletal_mid_back_pain()
    {
        return $this->musculoskeletal_mid_back_pain;
    }
    function get_musculoskeletal_mid_back_pain_yes()
    {
        return $this->musculoskeletal_mid_back_pain == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_mid_back_pain_no()
    {
        return $this->musculoskeletal_mid_back_pain == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_mid_back_pain($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_mid_back_pain = $data;
        }
    }
    function get_musculoskeletal_mid_back_pain_text()
    {
        return $this->musculoskeletal_mid_back_pain_text;
    }
    function set_musculoskeletal_mid_back_pain_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_mid_back_pain_text = $data;
        }
    }


    var $musculoskeletal_low_back_pain;
    var $musculoskeletal_low_back_pain_text;
    function get_musculoskeletal_low_back_pain()
    {
        return $this->musculoskeletal_low_back_pain;
    }
    function get_musculoskeletal_low_back_pain_yes()
    {
        return $this->musculoskeletal_low_back_pain == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_low_back_pain_no()
    {
        return $this->musculoskeletal_low_back_pain == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_low_back_pain($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_low_back_pain = $data;
        }
    }
    function get_musculoskeletal_low_back_pain_text()
    {
        return $this->musculoskeletal_low_back_pain_text;
    }
    function set_musculoskeletal_low_back_pain_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_low_back_pain_text = $data;
        }
    }


    var $musculoskeletal_bursitis;
    var $musculoskeletal_bursitis_text;
    function get_musculoskeletal_bursitis()
    {
        return $this->musculoskeletal_bursitis;
    }
    function get_musculoskeletal_bursitis_yes()
    {
        return $this->musculoskeletal_bursitis == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_bursitis_no()
    {
        return $this->musculoskeletal_bursitis == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_bursitis($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_bursitis = $data;
        }
    }
    function get_musculoskeletal_bursitis_text()
    {
        return $this->musculoskeletal_bursitis_text;
    }
    function set_musculoskeletal_bursitis_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_bursitis_text = $data;
        }
    }


    var $musculoskeletal_gout;
    var $musculoskeletal_gout_text;
    function get_musculoskeletal_gout()
    {
        return $this->musculoskeletal_gout;
    }
    function get_musculoskeletal_gout_yes()
    {
        return $this->musculoskeletal_gout == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_gout_no()
    {
        return $this->musculoskeletal_gout == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_gout($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_gout = $data;
        }
    }
    function get_musculoskeletal_gout_text()
    {
        return $this->musculoskeletal_gout_text;
    }
    function set_musculoskeletal_gout_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_gout_text = $data;
        }
    }


    var $musculoskeletal_joint_injury;
    var $musculoskeletal_joint_injury_text;
    function get_musculoskeletal_joint_injury()
    {
        return $this->musculoskeletal_joint_injury;
    }
    function get_musculoskeletal_joint_injury_yes()
    {
        return $this->musculoskeletal_joint_injury == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_joint_injury_no()
    {
        return $this->musculoskeletal_joint_injury == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_joint_injury($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_injury = $data;
        }
    }
    function get_musculoskeletal_joint_injury_text()
    {
        return $this->musculoskeletal_joint_injury_text;
    }
    function set_musculoskeletal_joint_injury_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_injury_text = $data;
        }
    }


    var $musculoskeletal_joint_pain;
    var $musculoskeletal_joint_pain_text;
    function get_musculoskeletal_joint_pain()
    {
        return $this->musculoskeletal_joint_pain;
    }
    function get_musculoskeletal_joint_pain_yes()
    {
        return $this->musculoskeletal_joint_pain == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_joint_pain_no()
    {
        return $this->musculoskeletal_joint_pain == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_joint_pain($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_pain = $data;
        }
    }
    function get_musculoskeletal_joint_pain_text()
    {
        return $this->musculoskeletal_joint_pain_text;
    }
    function set_musculoskeletal_joint_pain_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_pain_text = $data;
        }
    }


    var $musculoskeletal_joint_swelling;
    var $musculoskeletal_joint_swelling_text;
    function get_musculoskeletal_joint_swelling()
    {
        return $this->musculoskeletal_joint_swelling;
    }
    function get_musculoskeletal_joint_swelling_yes()
    {
        return $this->musculoskeletal_joint_swelling == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_joint_swelling_no()
    {
        return $this->musculoskeletal_joint_swelling == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_joint_swelling($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_swelling = $data;
        }
    }
    function get_musculoskeletal_joint_swelling_text()
    {
        return $this->musculoskeletal_joint_swelling_text;
    }
    function set_musculoskeletal_joint_swelling_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_joint_swelling_text = $data;
        }
    }


    var $musculoskeletal_myalgias;
    var $musculoskeletal_myalgias_text;
    function get_musculoskeletal_myalgias()
    {
        return $this->musculoskeletal_myalgias;
    }
    function get_musculoskeletal_myalgias_yes()
    {
        return $this->musculoskeletal_myalgias == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_myalgias_no()
    {
        return $this->musculoskeletal_myalgias == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_myalgias($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_myalgias = $data;
        }
    }
    function get_musculoskeletal_myalgias_text()
    {
        return $this->musculoskeletal_myalgias_text;
    }
    function set_musculoskeletal_myalgias_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_myalgias_text = $data;
        }
    }


    var $musculoskeletal_sciatica;
    var $musculoskeletal_sciatica_text;
    function get_musculoskeletal_sciatica()
    {
        return $this->musculoskeletal_sciatica;
    }
    function get_musculoskeletal_sciatica_yes()
    {
        return $this->musculoskeletal_sciatica == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_sciatica_no()
    {
        return $this->musculoskeletal_sciatica == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_sciatica($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_sciatica = $data;
        }
    }
    function get_musculoskeletal_sciatica_text()
    {
        return $this->musculoskeletal_sciatica_text;
    }
    function set_musculoskeletal_sciatica_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_sciatica_text = $data;
        }
    }


    var $musculoskeletal_scoliosis;
    var $musculoskeletal_scoliosis_text;
    function get_musculoskeletal_scoliosis()
    {
        return $this->musculoskeletal_scoliosis;
    }
    function get_musculoskeletal_scoliosis_yes()
    {
        return $this->musculoskeletal_scoliosis == "Yes" ? "CHECKED" : "";
    }
    function get_musculoskeletal_scoliosis_no()
    {
        return $this->musculoskeletal_scoliosis == "No" ? "CHECKED" : "";
    }
    function set_musculoskeletal_scoliosis($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_scoliosis = $data;
        }
    }
    function get_musculoskeletal_scoliosis_text()
    {
        return $this->musculoskeletal_scoliosis_text;
    }
    function set_musculoskeletal_scoliosis_text($data)
    {
        if (!empty($data)) {
            $this->musculoskeletal_scoliosis_text = $data;
        }
    }

    // ----- Anemia -----

    var $hematological_anemia;
    var $hematological_anemia_text;
    function get_hematological_anemia()
    {
        return $this->hematological_anemia;
    }
    function get_hematological_anemia_yes()
    {
        return $this->hematological_anemia == "Yes" ? "CHECKED" : "";
    }
    function get_hematological_anemia_no()
    {
        return $this->hematological_anemia == "No" ? "CHECKED" : "";
    }
    function set_hematological_anemia($data)
    {
        if (!empty($data)) {
            $this->hematological_anemia = $data;
        }
    }
    function get_hematological_anemia_text()
    {
        return $this->hematological_anemia_text;
    }
    function set_hematological_anemia_text($data)
    {
        if (!empty($data)) {
            $this->hematological_anemia_text = $data;
        }
    }


    var $hematological_pallor;
    var $hematological_pallor_text;
    function get_hematological_pallor()
    {
        return $this->hematological_pallor;
    }
    function get_hematological_pallor_yes()
    {
        return $this->hematological_pallor == "Yes" ? "CHECKED" : "";
    }
    function get_hematological_pallor_no()
    {
        return $this->hematological_pallor == "No" ? "CHECKED" : "";
    }
    function set_hematological_pallor($data)
    {
        if (!empty($data)) {
            $this->hematological_pallor = $data;
        }
    }
    function get_hematological_pallor_text()
    {
        return $this->hematological_pallor_text;
    }
    function set_hematological_pallor_text($data)
    {
        if (!empty($data)) {
            $this->hematological_pallor_text = $data;
        }
    }


    var $hematological_bleeding_tendencies;
    var $hematological_bleeding_tendencies_text;
    function get_hematological_bleeding_tendencies()
    {
        return $this->hematological_bleeding_tendencies;
    }
    function get_hematological_bleeding_tendencies_yes()
    {
        return $this->hematological_bleeding_tendencies == "Yes" ? "CHECKED" : "";
    }
    function get_hematological_bleeding_tendencies_no()
    {
        return $this->hematological_bleeding_tendencies == "No" ? "CHECKED" : "";
    }
    function set_hematological_bleeding_tendencies($data)
    {
        if (!empty($data)) {
            $this->hematological_bleeding_tendencies = $data;
        }
    }
    function get_hematological_bleeding_tendencies_text()
    {
        return $this->hematological_bleeding_tendencies_text;
    }
    function set_hematological_bleeding_tendencies_text($data)
    {
        if (!empty($data)) {
            $this->hematological_bleeding_tendencies_text = $data;
        }
    }


    var $hematological_bruising;
    var $hematological_bruising_text;
    function get_hematological_bruising()
    {
        return $this->hematological_bruising;
    }
    function get_hematological_bruising_yes()
    {
        return $this->hematological_bruising == "Yes" ? "CHECKED" : "";
    }
    function get_hematological_bruising_no()
    {
        return $this->hematological_bruising == "No" ? "CHECKED" : "";
    }
    function set_hematological_bruising($data)
    {
        if (!empty($data)) {
            $this->hematological_bruising = $data;
        }
    }
    function get_hematological_bruising_text()
    {
        return $this->hematological_bruising_text;
    }
    function set_hematological_bruising_text($data)
    {
        if (!empty($data)) {
            $this->hematological_bruising_text = $data;
        }
    }

    // ----- Thyroid Problems -----

    var $endocrine_thyroid_problems;
    var $endocrine_thyroid_problems_text;
    function get_endocrine_thyroid_problems()
    {
        return $this->endocrine_thyroid_problems;
    }
    function get_endocrine_thyroid_problems_yes()
    {
        return $this->endocrine_thyroid_problems == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_thyroid_problems_no()
    {
        return $this->endocrine_thyroid_problems == "No" ? "CHECKED" : "";
    }
    function set_endocrine_thyroid_problems($data)
    {
        if (!empty($data)) {
            $this->endocrine_thyroid_problems = $data;
        }
    }
    function get_endocrine_thyroid_problems_text()
    {
        return $this->endocrine_thyroid_problems_text;
    }
    function set_endocrine_thyroid_problems_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_thyroid_problems_text = $data;
        }
    }


    var $endocrine_enlarged_thyroid;
    var $endocrine_enlarged_thyroid_text;
    function get_endocrine_enlarged_thyroid()
    {
        return $this->endocrine_enlarged_thyroid;
    }
    function get_endocrine_enlarged_thyroid_yes()
    {
        return $this->endocrine_enlarged_thyroid == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_enlarged_thyroid_no()
    {
        return $this->endocrine_enlarged_thyroid == "No" ? "CHECKED" : "";
    }
    function set_endocrine_enlarged_thyroid($data)
    {
        if (!empty($data)) {
            $this->endocrine_enlarged_thyroid = $data;
        }
    }
    function get_endocrine_enlarged_thyroid_text()
    {
        return $this->endocrine_enlarged_thyroid_text;
    }
    function set_endocrine_enlarged_thyroid_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_enlarged_thyroid_text = $data;
        }
    }


    var $endocrine_hyperglycemia;
    var $endocrine_hyperglycemia_text;
    function get_endocrine_hyperglycemia()
    {
        return $this->endocrine_hyperglycemia;
    }
    function get_endocrine_hyperglycemia_yes()
    {
        return $this->endocrine_hyperglycemia == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_hyperglycemia_no()
    {
        return $this->endocrine_hyperglycemia == "No" ? "CHECKED" : "";
    }
    function set_endocrine_hyperglycemia($data)
    {
        if (!empty($data)) {
            $this->endocrine_hyperglycemia = $data;
        }
    }
    function get_endocrine_hyperglycemia_text()
    {
        return $this->endocrine_hyperglycemia_text;
    }
    function set_endocrine_hyperglycemia_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_hyperglycemia_text = $data;
        }
    }


    var $endocrine_hypoglycemia;
    var $endocrine_hypoglycemia_text;
    function get_endocrine_hypoglycemia()
    {
        return $this->endocrine_hypoglycemia;
    }
    function get_endocrine_hypoglycemia_yes()
    {
        return $this->endocrine_hypoglycemia == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_hypoglycemia_no()
    {
        return $this->endocrine_hypoglycemia == "No" ? "CHECKED" : "";
    }
    function set_endocrine_hypoglycemia($data)
    {
        if (!empty($data)) {
            $this->endocrine_hypoglycemia = $data;
        }
    }
    function get_endocrine_hypoglycemia_text()
    {
        return $this->endocrine_hypoglycemia_text;
    }
    function set_endocrine_hypoglycemia_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_hypoglycemia_text = $data;
        }
    }


    var $endocrine_cold_intolerance;
    var $endocrine_cold_intolerance_text;
    function get_endocrine_cold_intolerance()
    {
        return $this->endocrine_cold_intolerance;
    }
    function get_endocrine_cold_intolerance_yes()
    {
        return $this->endocrine_cold_intolerance == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_cold_intolerance_no()
    {
        return $this->endocrine_cold_intolerance == "No" ? "CHECKED" : "";
    }
    function set_endocrine_cold_intolerance($data)
    {
        if (!empty($data)) {
            $this->endocrine_cold_intolerance = $data;
        }
    }
    function get_endocrine_cold_intolerance_text()
    {
        return $this->endocrine_cold_intolerance_text;
    }
    function set_endocrine_cold_intolerance_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_cold_intolerance_text = $data;
        }
    }


    var $endocrine_heat_intolerance;
    var $endocrine_heat_intolerance_text;
    function get_endocrine_heat_intolerance()
    {
        return $this->endocrine_heat_intolerance;
    }
    function get_endocrine_heat_intolerance_yes()
    {
        return $this->endocrine_heat_intolerance == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_heat_intolerance_no()
    {
        return $this->endocrine_heat_intolerance == "No" ? "CHECKED" : "";
    }
    function set_endocrine_heat_intolerance($data)
    {
        if (!empty($data)) {
            $this->endocrine_heat_intolerance = $data;
        }
    }
    function get_endocrine_heat_intolerance_text()
    {
        return $this->endocrine_heat_intolerance_text;
    }
    function set_endocrine_heat_intolerance_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_heat_intolerance_text = $data;
        }
    }


    var $endocrine_early_awakening;
    var $endocrine_early_awakening_text;
    function get_endocrine_early_awakening()
    {
        return $this->endocrine_early_awakening;
    }
    function get_endocrine_early_awakening_yes()
    {
        return $this->endocrine_early_awakening == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_early_awakening_no()
    {
        return $this->endocrine_early_awakening == "No" ? "CHECKED" : "";
    }
    function set_endocrine_early_awakening($data)
    {
        if (!empty($data)) {
            $this->endocrine_early_awakening = $data;
        }
    }
    function get_endocrine_early_awakening_text()
    {
        return $this->endocrine_early_awakening_text;
    }
    function set_endocrine_early_awakening_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_early_awakening_text = $data;
        }
    }


    var $endocrine_fatigue_unexplained;
    var $endocrine_fatigue_unexplained_text;
    function get_endocrine_fatigue_unexplained()
    {
        return $this->endocrine_fatigue_unexplained;
    }
    function get_endocrine_fatigue_unexplained_yes()
    {
        return $this->endocrine_fatigue_unexplained == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_fatigue_unexplained_no()
    {
        return $this->endocrine_fatigue_unexplained == "No" ? "CHECKED" : "";
    }
    function set_endocrine_fatigue_unexplained($data)
    {
        if (!empty($data)) {
            $this->endocrine_fatigue_unexplained = $data;
        }
    }
    function get_endocrine_fatigue_unexplained_text()
    {
        return $this->endocrine_fatigue_unexplained_text;
    }
    function set_endocrine_fatigue_unexplained_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_fatigue_unexplained_text = $data;
        }
    }


    var $endocrine_weight_gain;
    var $endocrine_weight_gain_text;
    function get_endocrine_weight_gain()
    {
        return $this->endocrine_weight_gain;
    }
    function get_endocrine_weight_gain_yes()
    {
        return $this->endocrine_weight_gain == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_weight_gain_no()
    {
        return $this->endocrine_weight_gain == "No" ? "CHECKED" : "";
    }
    function set_endocrine_weight_gain($data)
    {
        if (!empty($data)) {
            $this->endocrine_weight_gain = $data;
        }
    }
    function get_endocrine_weight_gain_text()
    {
        return $this->endocrine_weight_gain_text;
    }
    function set_endocrine_weight_gain_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_weight_gain_text = $data;
        }
    }


    var $endocrine_weight_loss;
    var $endocrine_weight_loss_text;
    function get_endocrine_weight_loss()
    {
        return $this->endocrine_weight_loss;
    }
    function get_endocrine_weight_loss_yes()
    {
        return $this->endocrine_weight_loss == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_weight_loss_no()
    {
        return $this->endocrine_weight_loss == "No" ? "CHECKED" : "";
    }
    function set_endocrine_weight_loss($data)
    {
        if (!empty($data)) {
            $this->endocrine_weight_loss = $data;
        }
    }
    function get_endocrine_weight_loss_text()
    {
        return $this->endocrine_weight_loss_text;
    }
    function set_endocrine_weight_loss_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_weight_loss_text = $data;
        }
    }


    var $endocrine_premenstrual_symptoms;
    var $endocrine_premenstrual_symptoms_text;
    function get_endocrine_premenstrual_symptoms()
    {
        return $this->endocrine_premenstrual_symptoms;
    }
    function get_endocrine_premenstrual_symptoms_yes()
    {
        return $this->endocrine_premenstrual_symptoms == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_premenstrual_symptoms_no()
    {
        return $this->endocrine_premenstrual_symptoms == "No" ? "CHECKED" : "";
    }
    function set_endocrine_premenstrual_symptoms($data)
    {
        if (!empty($data)) {
            $this->endocrine_premenstrual_symptoms = $data;
        }
    }
    function get_endocrine_premenstrual_symptoms_text()
    {
        return $this->endocrine_premenstrual_symptoms_text;
    }
    function set_endocrine_premenstrual_symptoms_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_premenstrual_symptoms_text = $data;
        }
    }


    var $endocrine_hair_no_change_or_no_loss;
    var $endocrine_hair_no_change_or_no_loss_text;
    function get_endocrine_hair_no_change_or_no_loss()
    {
        return $this->endocrine_hair_no_change_or_no_loss;
    }
    function get_endocrine_hair_no_change_or_no_loss_yes()
    {
        return $this->endocrine_hair_no_change_or_no_loss == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_hair_no_change_or_no_loss_no()
    {
        return $this->endocrine_hair_no_change_or_no_loss == "No" ? "CHECKED" : "";
    }
    function set_endocrine_hair_no_change_or_no_loss($data)
    {
        if (!empty($data)) {
            $this->endocrine_hair_no_change_or_no_loss = $data;
        }
    }
    function get_endocrine_hair_no_change_or_no_loss_text()
    {
        return $this->endocrine_hair_no_change_or_no_loss_text;
    }
    function set_endocrine_hair_no_change_or_no_loss_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_hair_no_change_or_no_loss_text = $data;
        }
    }


    var $endocrine_hot_flashes;
    var $endocrine_hot_flashes_text;
    function get_endocrine_hot_flashes()
    {
        return $this->endocrine_hot_flashes;
    }
    function get_endocrine_hot_flashes_yes()
    {
        return $this->endocrine_hot_flashes == "Yes" ? "CHECKED" : "";
    }
    function get_endocrine_hot_flashes_no()
    {
        return $this->endocrine_hot_flashes == "No" ? "CHECKED" : "";
    }
    function set_endocrine_hot_flashes($data)
    {
        if (!empty($data)) {
            $this->endocrine_hot_flashes = $data;
        }
    }
    function get_endocrine_hot_flashes_text()
    {
        return $this->endocrine_hot_flashes_text;
    }
    function set_endocrine_hot_flashes_text($data)
    {
        if (!empty($data)) {
            $this->endocrine_hot_flashes_text = $data;
        }
    }

    // ----- Swollen lymph nodes -----

    var $lymphatic_swollen_lymph_nodes;
    var $lymphatic_swollen_lymph_nodes_text;
    function get_lymphatic_swollen_lymph_nodes()
    {
        return $this->lymphatic_swollen_lymph_nodes;
    }
    function get_lymphatic_swollen_lymph_nodes_yes()
    {
        return $this->lymphatic_swollen_lymph_nodes == "Yes" ? "CHECKED" : "";
    }
    function get_lymphatic_swollen_lymph_nodes_no()
    {
        return $this->lymphatic_swollen_lymph_nodes == "No" ? "CHECKED" : "";
    }
    function set_lymphatic_swollen_lymph_nodes($data)
    {
        if (!empty($data)) {
            $this->lymphatic_swollen_lymph_nodes = $data;
        }
    }
    function get_lymphatic_swollen_lymph_nodes_text()
    {
        return $this->lymphatic_swollen_lymph_nodes_text;
    }
    function set_lymphatic_swollen_lymph_nodes_text($data)
    {
        if (!empty($data)) {
            $this->lymphatic_swollen_lymph_nodes_text = $data;
        }
    }


    var $lymphatic_swollen_extremities;
    var $lymphatic_swollen_extremities_text;
    function get_lymphatic_swollen_extremities()
    {
        return $this->lymphatic_swollen_extremities;
    }
    function get_lymphatic_swollen_extremities_yes()
    {
        return $this->lymphatic_swollen_extremities == "Yes" ? "CHECKED" : "";
    }
    function get_lymphatic_swollen_extremities_no()
    {
        return $this->lymphatic_swollen_extremities == "No" ? "CHECKED" : "";
    }
    function set_lymphatic_swollen_extremities($data)
    {
        if (!empty($data)) {
            $this->lymphatic_swollen_extremities = $data;
        }
    }
    function get_lymphatic_swollen_extremities_text()
    {
        return $this->lymphatic_swollen_extremities_text;
    }
    function set_lymphatic_swollen_extremities_text($data)
    {
        if (!empty($data)) {
            $this->lymphatic_swollen_extremities_text = $data;
        }
    }

    // ----- Compulsions -----

    var $psychiatric_compulsions;
    var $psychiatric_compulsions_text;
    function get_psychiatric_compulsions()
    {
        return $this->psychiatric_compulsions;
    }
    function get_psychiatric_compulsions_yes()
    {
        return $this->psychiatric_compulsions == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_compulsions_no()
    {
        return $this->psychiatric_compulsions == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_compulsions($data)
    {
        if (!empty($data)) {
            $this->psychiatric_compulsions = $data;
        }
    }
    function get_psychiatric_compulsions_text()
    {
        return $this->psychiatric_compulsions_text;
    }
    function set_psychiatric_compulsions_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_compulsions_text = $data;
        }
    }


    var $psychiatric_depression;
    var $psychiatric_depression_text;
    function get_psychiatric_depression()
    {
        return $this->psychiatric_depression;
    }
    function get_psychiatric_depression_yes()
    {
        return $this->psychiatric_depression == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_depression_no()
    {
        return $this->psychiatric_depression == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_depression($data)
    {
        if (!empty($data)) {
            $this->psychiatric_depression = $data;
        }
    }
    function get_psychiatric_depression_text()
    {
        return $this->psychiatric_depression_text;
    }
    function set_psychiatric_depression_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_depression_text = $data;
        }
    }


    var $psychiatric_fear;
    var $psychiatric_fear_text;
    function get_psychiatric_fear()
    {
        return $this->psychiatric_fear;
    }
    function get_psychiatric_fear_yes()
    {
        return $this->psychiatric_fear == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_fear_no()
    {
        return $this->psychiatric_fear == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_fear($data)
    {
        if (!empty($data)) {
            $this->psychiatric_fear = $data;
        }
    }
    function get_psychiatric_fear_text()
    {
        return $this->psychiatric_fear_text;
    }
    function set_psychiatric_fear_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_fear_text = $data;
        }
    }


    var $psychiatric_anxiety;
    var $psychiatric_anxiety_text;
    function get_psychiatric_anxiety()
    {
        return $this->psychiatric_anxiety;
    }
    function get_psychiatric_anxiety_yes()
    {
        return $this->psychiatric_anxiety == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_anxiety_no()
    {
        return $this->psychiatric_anxiety == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_anxiety($data)
    {
        if (!empty($data)) {
            $this->psychiatric_anxiety = $data;
        }
    }
    function get_psychiatric_anxiety_text()
    {
        return $this->psychiatric_anxiety_text;
    }
    function set_psychiatric_anxiety_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_anxiety_text = $data;
        }
    }


    var $psychiatric_hallucinations;
    var $psychiatric_hallucinations_text;
    function get_psychiatric_hallucinations()
    {
        return $this->psychiatric_hallucinations;
    }
    function get_psychiatric_hallucinations_yes()
    {
        return $this->psychiatric_hallucinations == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_hallucinations_no()
    {
        return $this->psychiatric_hallucinations == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_hallucinations($data)
    {
        if (!empty($data)) {
            $this->psychiatric_hallucinations = $data;
        }
    }
    function get_psychiatric_hallucinations_text()
    {
        return $this->psychiatric_hallucinations_text;
    }
    function set_psychiatric_hallucinations_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_hallucinations_text = $data;
        }
    }


    var $psychiatric_loss_of_interest;
    var $psychiatric_loss_of_interest_text;
    function get_psychiatric_loss_of_interest()
    {
        return $this->psychiatric_loss_of_interest;
    }
    function get_psychiatric_loss_of_interest_yes()
    {
        return $this->psychiatric_loss_of_interest == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_loss_of_interest_no()
    {
        return $this->psychiatric_loss_of_interest == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_loss_of_interest($data)
    {
        if (!empty($data)) {
            $this->psychiatric_loss_of_interest = $data;
        }
    }
    function get_psychiatric_loss_of_interest_text()
    {
        return $this->psychiatric_loss_of_interest_text;
    }
    function set_psychiatric_loss_of_interest_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_loss_of_interest_text = $data;
        }
    }


    var $psychiatric_memory_loss;
    var $psychiatric_memory_loss_text;
    function get_psychiatric_memory_loss()
    {
        return $this->psychiatric_memory_loss;
    }
    function get_psychiatric_memory_loss_yes()
    {
        return $this->psychiatric_memory_loss == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_memory_loss_no()
    {
        return $this->psychiatric_memory_loss == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_memory_loss($data)
    {
        if (!empty($data)) {
            $this->psychiatric_memory_loss = $data;
        }
    }
    function get_psychiatric_memory_loss_text()
    {
        return $this->psychiatric_memory_loss_text;
    }
    function set_psychiatric_memory_loss_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_memory_loss_text = $data;
        }
    }


    var $psychiatric_mood_swings;
    var $psychiatric_mood_swings_text;
    function get_psychiatric_mood_swings()
    {
        return $this->psychiatric_mood_swings;
    }
    function get_psychiatric_mood_swings_yes()
    {
        return $this->psychiatric_mood_swings == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_mood_swings_no()
    {
        return $this->psychiatric_mood_swings == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_mood_swings($data)
    {
        if (!empty($data)) {
            $this->psychiatric_mood_swings = $data;
        }
    }
    function get_psychiatric_mood_swings_text()
    {
        return $this->psychiatric_mood_swings_text;
    }
    function set_psychiatric_mood_swings_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_mood_swings_text = $data;
        }
    }


    var $psychiatric_pananoia;
    var $psychiatric_pananoia_text;
    function get_psychiatric_pananoia()
    {
        return $this->psychiatric_pananoia;
    }
    function get_psychiatric_pananoia_yes()
    {
        return $this->psychiatric_pananoia == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_pananoia_no()
    {
        return $this->psychiatric_pananoia == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_pananoia($data)
    {
        if (!empty($data)) {
            $this->psychiatric_pananoia = $data;
        }
    }
    function get_psychiatric_pananoia_text()
    {
        return $this->psychiatric_pananoia_text;
    }
    function set_psychiatric_pananoia_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_pananoia_text = $data;
        }
    }


    var $psychiatric_insomnia;
    var $psychiatric_insomnia_text;
    function get_psychiatric_insomnia()
    {
        return $this->psychiatric_insomnia;
    }
    function get_psychiatric_insomnia_yes()
    {
        return $this->psychiatric_insomnia == "Yes" ? "CHECKED" : "";
    }
    function get_psychiatric_insomnia_no()
    {
        return $this->psychiatric_insomnia == "No" ? "CHECKED" : "";
    }
    function set_psychiatric_insomnia($data)
    {
        if (!empty($data)) {
            $this->psychiatric_insomnia = $data;
        }
    }
    function get_psychiatric_insomnia_text()
    {
        return $this->psychiatric_insomnia_text;
    }
    function set_psychiatric_insomnia_text($data)
    {
        if (!empty($data)) {
            $this->psychiatric_insomnia_text = $data;
        }
    }
}   // end of Form
