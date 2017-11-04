<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
// Copyright (C) 2017 Roland Wick <ronhen_at_yandex_com>
// version 0.9
// TO DO : vitals weight and temperature vars from today's vitals set if taken by clin. staff
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2



require_once(dirname(__FILE__) . "/../../../library/classes/ORDataObject.class.php");

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


class Formvet_genphys_exam extends ORDataObject {

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
    var $u;
    var $species;
    var $vitals_weight;
    var $vitals_temperature;
    
        
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

        $this->_table = "form_vet_genphys_exam";
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
	       if ($id != "") {
            $this->populate();
         }
    }
    function populate()
    {
        parent::populate();
        		$this->temp_methods = parent::_load_enum("temp_locations",false);
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
	 function get_species()
	 {
	 	return $this->species;
	 }
	 function get_weight()
    {
        return $this->vitals_weight;
	 }
	 
	 function get_temperature()
    {
        return $this->vitals_temperature;
	 }
	 
	 function persist()
    {
        parent::persist();
    }

	
  // ------ presenting_complaint -----

    var $presenting_complaint;
    function get_presenting_complaint()
    {
        return $this->presenting_complaint;
    }
    
    function set_presenting_complaint($data)
    {
        if (!empty($data)) {
            $this->presenting_complaint = $data;
        }
    }

// ------ consciousness -----

    var $consciousness;
    function get_consciousness()
    {
        return $this->consciousness;
    }
    
    function set_consciousness($data)
    {
        if (!empty($data)) {
            $this->consciousness = $data;
        }
    }

// ------ behaviour -----

    var $behaviour;
    function get_behaviour()
    {
        return $this->behaviour;
    }
    
    function set_behaviour($data)
    {
        if (!empty($data)) {
            $this->behaviour = $data;
        }
    }

// ------ gait -----

    var $gait;
    function get_gait()
    {
        return $this->gait;
    }
    
    function set_gait($data)
    {
        if (!empty($data)) {
            $this->gait = $data;
        }
    }

 // ------ general_notes -----

    var $general_notes;
    function get_general_notes()
    {
        return $this->general_notes;
    }
    
    function set_general_notes($data)
    {
        if (!empty($data)) {
            $this->general_notes = $data;
        }
    }

// ------ anamnesis -----

 	 var $anamnesis;
    function get_anamnesis()
    {
        return $this->anamnesis;
    }
    
    function set_anamnesis($data)
    {
        if (!empty($data)) {
            $this->anamnesis = $data;
        }
    }

 // ------ general_condition -----

    var $general_condition;
    function get_general_condition()
    {
        return $this->general_condition;
    }
    
    function set_general_condition($data)
    {
        if (!empty($data)) {
            $this->general_condition = $data;
        }
    }

 // ------ bws_bodyscore -----

    var $bws_bodyscore;
    function get_bws_bodyscore()
    {
        return $this->bws_bodyscore;
    }
    
    function set_bws_bodyscore($data)
    {
        if (!empty($data)) {
            $this->bws_bodyscore = $data;
        }
    }

// ------ body_posture -----

    var $body_posture;
    function get_body_posture()
    {
        return $this->body_posture;
    }
    
    function set_body_posture($data)
    {
        if (!empty($data)) {
            $this->body_posture = $data;
        }
    }

 // ------ care_condition -----

    var $care_condition;
    function get_care_condition()
    {
        return $this->care_condition;
    }
    
    function set_care_condition($data)
    {
        if (!empty($data)) {
            $this->care_condition = $data;
        }
    }

 // ------ heart_frequency -----

    var $heart_frequency;
    function get_heart_frequency()
    {
        return $this->heart_frequency;
    }
    
    function set_heart_frequency($data)
    {
        if (!empty($data)) {
            $this->heart_frequency = $data;
        }
    }
    
// ------ heart_tones -----

    var $heart_tones;
    function get_heart_tones()
    {
        return $this->heart_tones;
    }
    
    function set_heart_tones($data)
    {
        if (!empty($data)) {
            $this->heart_tones = $data;
        }
    }
    
 // ------ heart_murmur -----

    var $heart_murmur;
    function get_heart_murmur()
    {
        return $this->heart_murmur;
    }
    
    function set_heart_murmur($data)
    {
        if (!empty($data)) {
            $this->heart_murmur = $data;
        }
    }
     
 // ------ heart_murmur_phase -----

    var $heart_murmur_phase;
    function get_heart_murmur_phase()
    {
        return $this->heart_murmur_phase;
    }
    
    function set_heart_murmur_phase($data)
    {
        if (!empty($data)) {
            $this->heart_murmur_phase = $data;
        }
    }
    
 // ------ pulse_frequency -----

    var $pulse_frequency;
    function get_pulse_frequency()
    {
        return $this->pulse_frequency;
    }
    
    function set_pulse_frequency($data)
    {
        if (!empty($data)) {
            $this->pulse_frequency = $data;
        }
    }   

 // ------ pulse_intensity -----

    var $pulse_intensity;
    function get_pulse_intensity()
    {
        return $this->pulse_intensity;
    }
    
    function set_pulse_intensity($data)
    {
        if (!empty($data)) {
            $this->pulse_intensity = $data;
        }
    }
    
// ------ pulse_regularity -----

    var $pulse_regularity;
    function get_pulse_regularity()
    {
        return $this->pulse_regularity;
    }
    
    function set_pulse_regularity($data)
    {
        if (!empty($data)) {
            $this->pulse_regularity = $data;
        }
    }
    
// ------ pulse_equality -----

    var $pulse_equality;
    function get_pulse_equality()
    {
        return $this->pulse_equality;
    }
    
    function set_pulse_equality($data)
    {
        if (!empty($data)) {
            $this->pulse_equality = $data;
        }
    }
    
// ------ mucosae_colour -----

    var $mucosae_colour;
    function get_mucosae_colour()
    {
        return $this->mucosae_colour;
    }
    
    function set_mucosae_colour($data)
    {
        if (!empty($data)) {
            $this->mucosae_colour = $data;
        }
    }
    
 // ------ mucosae_status -----

    var $mucosae_status;
    function get_mucosae_status()
    {
        return $this->mucosae_status;
    }
    
    function set_mucosae_status($data)
    {
        if (!empty($data)) {
            $this->mucosae_status = $data;
        }
    }
    
  // ------ mucosae_CRT -----

    var $mucosae_CRT;
    function get_mucosae_CRT()
    {
        return $this->mucosae_CRT;
    }
    
    function set_mucosae_CRT($data)
    {
        if (!empty($data)) {
            $this->mucosae_CRT = $data;
        }
    }
     
  // ------ respiration_frequency -----

    var $respiration_frequency;
    function get_respiration_frequency()
    {
        return $this->respiration_frequency;
    }
    
    function set_respiration_frequency($data)
    {
        if (!empty($data)) {
            $this->respiration_frequency = $data;
        }
    }
    
  // ------ respiration_type -----

    var $respiration_type;
    function get_respiration_type()
    {
        return $this->respiration_type;
    }
    
    function set_respiration_type($data)
    {
        if (!empty($data)) {
            $this->respiration_type = $data;
        }
    }
    
 // ------ respiration_character -----

    var $respiration_character;
    function get_respiration_character()
    {
        return $this->respiration_character;
    }
    
    function set_respiration_character($data)
    {
        if (!empty($data)) {
            $this->respiration_character = $data;
        }
    }
    
 // ------ lungfield_auscultation -----

    var $lungfield_ausc_1;
    function get_lungf_ausc_1()
    {  
      	return $this->lungf_ausc_1;
    }
    
    function set_lungf_ausc_1($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_1 = $data;
        }
    }
   
    var $lungfield_ausc_2;
    function get_lungf_ausc_2()
    {  
      	return $this->lungf_ausc_2;
    }
    
    function set_lungf_ausc_2($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_2 = $data;
        }
    } 
   
    var $lungfield_ausc_3;
    function get_lungf_ausc_3()
    {  
      	return $this->lungf_ausc_3;
    }
    
    function set_lungf_ausc_3($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_3 = $data;
        }
    }
   
    var $lungfield_ausc_4;
    function get_lungf_ausc_4()
    {  
      	return $this->lungf_ausc_4;
    }
    
    function set_lungf_ausc_4($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_4 = $data;
        }
    }
   
    var $lungfield_ausc_5;
    function get_lungf_ausc_5()
    {  
      	return $this->lungf_ausc_5;
    }
    
    function set_lungf_ausc_5($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_5 = $data;
        }
    }
   
    var $lungfield_ausc_6;
    function get_lungf_ausc_6()
    {  
      	return $this->lungf_ausc_6;
    }
    
    function set_lungf_ausc_6($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_6 = $data;
        }
    }
    
    var $lungfield_ausc_7;
    function get_lungf_ausc_7()
    {  
      	return $this->lungf_ausc_7;
    }
    
    function set_lungf_ausc_7($data)
    {
     	  if (!empty($data)) {
     			$this->lungf_ausc_7 = $data;
        }
    }
     
  // ------ lungfield_percussion -----

    var $lungfield_percussion;
    function get_lungfield_percussion()
    {
        return $this->lungfield_percussion;
    }
    
    function set_lungfield_percussion($data)
    {
        if (!empty($data)) {
            $this->lungfield_percussion = $data;
        }
    }
    
  // ------ up_airways_1 -----

    var $up_airways_1;
    function get_up_airways_1()
    {
        return $this->up_airways_1;
    }
    
    function set_up_airways_1($data)
    {
        if (!empty($data)) {
            $this->up_airways_1 = $data;
        }
    }
    
    // ------ up_airways_2 -----

    var $up_airways_2;
    function get_up_airways_2()
    {
        return $this->up_airways_2;
    }
    
    function set_up_airways_2($data)
    {
        if (!empty($data)) {
            $this->up_airways_2 = $data;
        }
    }  
    
     // ------ up_airways_3 -----

    var $up_airways_3;
    function get_up_airways_3()
    {
        return $this->up_airways_3;
    }
    
    function set_up_airways_3($data)
    {
        if (!empty($data)) {
            $this->up_airways_3 = $data;
        }
    } 
    
      // ------ up_airways_4 -----

    var $up_airways_4;
    function get_up_airways_4()
    {
        return $this->up_airways_4;
    }
    
    function set_up_airways_4($data)
    {
        if (!empty($data)) {
            $this->up_airways_4 = $data;
        }
    }
    
      // ------ up_airways_5 -----

    var $up_airways_5;
    function get_up_airways_5()
    {
        return $this->up_airways_5;
    }
    
    function set_up_airways_5($data)
    {
        if (!empty($data)) {
            $this->up_airways_5 = $data;
        }
    }
    
    
 // ------ upper_airways_2descr -----

    var $upper_airways_2descr;
    function get_upper_airways_2descr()
    {
        return $this->upper_airways_2descr;
    }
    
    function set_upper_airways_2descr($data)
    {
        if (!empty($data)) {
            $this->upper_airways_2descr = $data;
        }
    }
    
 // ------ elasticity_abdomen -----

    var $elasticity_abdomen;
    function get_elasticity_abdomen()
    {
        return $this->elasticity_abdomen;
    }
    
    function set_elasticity_abdomen($data)
    {
        if (!empty($data)) {
            $this->elasticity_abdomen = $data;
        }
    }
    
 // ------ sensibility_abdomen -----

    var $sensibility_abdomen;
    function get_sensibility_abdomen()
    {
        return $this->sensibility_abdomen;
    }
    
    function set_sensibility_abdomen($data)
    {
        if (!empty($data)) {
            $this->sensibility_abdomen = $data;
        }
    }
    
 // ------ palp_abdomen_other -----

    var $palp_abdomen_other;
    function get_palp_abdomen_other()
    {
        return $this->palp_abdomen_other;
    }
    
    function set_palp_abdomen_other($data)
    {
        if (!empty($data)) {
            $this->palp_abdomen_other = $data;
        }
    }
    
 // ------ skin_turgor -----

    var $skin_turgor;
    function get_skin_turgor()
    {
        return $this->skin_turgor;
    }
    
    function set_skin_turgor($data)
    {
        if (!empty($data)) {
            $this->skin_turgor = $data;
        }
    }
    
 // ------ skin_colour -----

    var $skin_colour;
    function get_skin_colour()
    {
        return $this->skin_colour;
    }
    
    function set_skin_colour($data)
    {
        if (!empty($data)) {
            $this->skin_colour = $data;
        }
    }
    
 // ------ skin_thickness -----

    var $skin_thickness;
    function get_skin_thickness()
    {
        return $this->skin_thickness;
    }
    
    function set_skin_thickness($data)
    {
        if (!empty($data)) {
            $this->skin_thickness = $data;
        }
    }
    
 // ------ skin_mobility -----

    var $skin_mobility;
    function get_skin_mobility()
    {
        return $this->skin_mobility;
    }
    
    function set_skin_mobility($data)
    {
        if (!empty($data)) {
            $this->skin_mobility = $data;
        }
    }
    
    // ------ integ_general -----

    var $integ_general;
    function get_integ_general()
    {
        return $this->integ_general;
    }
    
    function set_integ_general($data)
    {
        if (!empty($data)) {
            $this->integ_general = $data;
        }
    }
    
    // ------ integ_local -----

    var $integ_local;
    function get_integ_local()
    {
        return $this->integ_local;
    }
    
    function set_integ_local($data)
    {
        if (!empty($data)) {
            $this->integ_local = $data;
        }
    }
    
    
    
 // ------ lnn_mandibulares -----

    var $lnn_mandibulares;
    function get_lnn_mandibulares()
    {
        return $this->lnn_mandibulares;
    }
    
    function set_lnn_mandibulares($data)
    {
        if (!empty($data)) {
            $this->lnn_mandibulares = $data;
        }
    }
    
  // ------ lnn_parotidei -----

    var $lnn_parotidei;
    function get_lnn_parotidei()
    {
        return $this->lnn_parotidei;
    }
    
    function set_lnn_parotidei($data)
    {
        if (!empty($data)) {
            $this->lnn_parotidei = $data;
        }
    }
    
 // ------ lnn_retropharyng -----

    var $lnn_retropharyng;
    function get_lnn_retropharyng()
    {
        return $this->lnn_retropharyng;
    }
    
    function set_lnn_retropharyng($data)
    {
        if (!empty($data)) {
            $this->lnn_retropharyng = $data;
        }
    }
    
 // ------ lnn_cervicales -----

    var $lnn_cervicales;
    function get_lnn_cervicales()
    {
        return $this->lnn_cervicales;
    }
    
    function set_lnn_cervicales($data)
    {
        if (!empty($data)) {
            $this->lnn_cervicales = $data;
        }
    }
    
 // ------ lnn_axillares -----

    var $lnn_axillares;
    function get_lnn_axillares()
    {
        return $this->lnn_axillares;
    }
    
    function set_lnn_axillares($data)
    {
        if (!empty($data)) {
            $this->lnn_axillares = $data;
        }
    }
    
 // ------ lnn_axill_access -----

    var $lnn_axill_access;
    function get_lnn_axill_access()
    {
        return $this->lnn_axill_access;
    }
    
    function set_lnn_axill_access($data)
    {
        if (!empty($data)) {
            $this->lnn_axill_access = $data;
        }
    }
    
 // ------ lnn_inguinales -----

    var $lnn_inguinales;
    function get_lnn_inguinales()
    {
        return $this->lnn_inguinales;
    }
    
    function set_lnn_inguinales($data)
    {
        if (!empty($data)) {
            $this->lnn_inguinales = $data;
        }
    }
    
 // ------ lnn_poplitei -----

    var $lnn_poplitei;
    function get_lnn_poplitei()
    {
        return $this->lnn_poplitei;
    }
    
    function set_lnn_poplitei($data)
    {
        if (!empty($data)) {
            $this->lnn_poplitei = $data;
        }
    }
    
  // ------ genphys_other_findings -----

    var $genphys_other_findings;
    function get_genphys_other_findings()
    {
        return $this->genphys_other_findings;
    }
    
    function set_genphys_other_findings($data)
    {
        if (!empty($data)) {
            $this->genphys_other_findings = $data;
        }
    }
    
  // ------ presumptive_diagnosis -----

    var $presumptive_diagnosis;
    function get_presumptive_diagnosis()
    {
        return $this->presumptive_diagnosis;
    }
    
    function set_presumptive_diagnosis($data)
    {
        if (!empty($data)) {
            $this->presumptive_diagnosis = $data;
        }
    }
    
 // ------ differential_diagnosis -----

    var $differential_diagnosis;
    function get_differential_diagnosis()
    {
        return $this->differential_diagnosis;
    }
    
    function set_differential_diagnosis($data)
    {
        if (!empty($data)) {
            $this->differential_diagnosis = $data;
        }
    }
    

}   // end of Form

?>
