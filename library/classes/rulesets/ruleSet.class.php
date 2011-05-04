<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/../../clinical_rules.php");
require_once(dirname(__FILE__) . "/../../forms.inc");
require_once(dirname(__FILE__) . "/../../patient.inc");
require_once( 'codes.php' );

class ruleSet
{
  // Main input variables:
  // $rule        -  array containing rule information
  // $dateTarget  - target date
  // $patientData - array of pertinent patients

  // These variables hold the results data elements, note they are all
  //   arrays that need to all contain the same number of elements.
  // $group_label     - array of titles for each group (usually only one group) TODO: get this working for when there are multiple groups
  // $total_patients  - array of total patients for each group
  // $pass_filter     - array of patients that pass filters for each group
  // $exclude_filter  - array of patients that are excluded for each group
  // $pass_target     - array of patients that pass target for each group
  // $percentage      - array of percentage of patients that pass target for each group

  // Main processed results variable:
  // $results - holds the result array used in reports
  
  private $codes; // code lookup class

  // Construction function
  public function __construct( $rule, $dateTarget, $patientData ) {
    $this->codes = new Code_Lookup();  
    $this->rule = $rule;
    $this->dateTarget = $dateTarget;
    $this->patientData = $patientData;
    $this->process_rule();
    $this->process_results();
  }

  // Function to return the results
  public function return_results() {
    return $this->results;
  }

  // Straightforward function to call the correct rule functions
  private function process_rule() {
    $rule_id=$this->rule['id'];
    // process specific rule
    switch ($rule_id) {
      case "rule_htn_bp_measure_cqm":
        // Hypertension: Blood Pressure Measurement
        // NQF 0013
        $this->rule_htn_bp_measure_cqm();
        break;
      case "rule_tob_use_assess_cqm":
        // Tobacco Use Assessment 
        // NQF 0028a
        $this->rule_tob_use_assess_cqm();
        break;
      case "rule_tob_cess_inter_cqm":
        // Tobacco Cessation Intervention
        // NQF 0028b
        $this->rule_tob_cess_inter_cqm();
        break;
      case "rule_adult_wt_screen_fu_cqm":
        // Adult Weight Screening and Follow-Up
        // NQF 0421
        // PQRI 128
        $this->rule_adult_wt_screen_fu_cqm();
        break;
      case "rule_wt_assess_couns_child_cqm":
        // Weight Assessment and Counseling for Children and Adolescents
        // NQF 0024
        $this->rule_wt_assess_couns_child_cqm();
        break;
      case "rule_influenza_ge_50_cqm":
        // Influenza Immunization for Patients >= 50 Years Old
        // NQF 0041
        // PQRI 110
        $this->rule_influenza_ge_50_cqm();
        break;
      case "rule_child_immun_stat_cqm":
        // Childhood immunization Status
        // NQF 0038
        $this->rule_child_immun_stat_cqm();
        break;
      case "rule_pneumovacc_ge_65_cqm":
        // Pneumonia Vaccination Status for Older Adults
        // NQF 0043
        // PQRI 111
        $this->rule_pneumovacc_ge_65_cqm();
        break;
      case "rule_dm_eye_cqm":
        // Diabetes: Eye Exam
        // NQF 0055
        // PQRI 117
        $this->rule_dm_eye_cqm();
        break;
      case "rule_dm_foot_cqm":
        // Diabetes: Foot Exam
        // NQF 0056
        // PQRI 163
        $this->rule_dm_foot_cqm();
        break;
      case "rule_dm_bp_control_cqm":
        // Diabetes: Blood Pressure Management
        // NQF 0061
        // PQRI 3
        $this->rule_dm_bp_control_cqm();
        break;
      case "rule_dm_a1c_cqm":
        // Diabetes: HbA1c Poor Control
        // NQF 0059
        // PQRI 1
        $this->rule_dm_a1c_cqm();
        break;
      case "rule_dm_ldl_cqm":
        // Diabetes: LDL Management & Control
        // NQF 0064
        // PQRI 2
        $this->rule_dm_ldl_cqm();
        break;
      case "problem_list_amc":
        // Maintain an up-to-date problem list of current and active diagnoses.
        // 170.302(c)
        $this->problem_list_amc();
        break;
      case "med_list_amc":
        // Maintain active medication list.
        // 170.302(d)
        $this->med_list_amc();
        break;
      case "med_allergy_list_amc":
        // Maintain active medication allergy list.
        // 170.302(e)
        $this->med_allergy_list_amc();
        break;
      case "record_vitals_amc":
        // Record and chart changes in vital signs.
        // 170.302(f)
        $this->record_vitals_amc();
        break;
      case "record_smoke_amc":
        // Record smoking status for patients 13 years old or older.
        // 170.302(g)
        $this->record_smoke_amc();
        break;
      case "lab_result_amc":
        // Incorporate clinical lab-test results into certified EHR technology as structured data.
        // 170.302(h)
        $this->lab_result_amc();
        break;
      case "med_reconc_amc":
        // The EP, eligible hospital or CAH who receives a patient from another setting of care or provider of care or believes an encounter is relevant should perform medication reconciliation.
        // 170.302(j)
        $this->med_reconc_amc();
        break;
      case "patient_edu_amc":
        // Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate.
        // 170.302(m)
        $this->patient_edu_amc();
        break;
      case "cpoe_med_amc":
        // Use CPOE for medication orders directly entered by any licensed healthcare professional who can enter orders into the medical record per state, local and professional guidelines.
        // 170.304(a)
        $this->cpoe_med_amc();
        break;
      case "e_prescribe_amc":
        // Generate and transmit permissible prescriptions electronically.
        // 170.304(b)
        $this->e_prescribe_amc();
        break;
      case "record_dem_amc":
        // Record demographics.
        // 170.304(c)
        $this->record_dem_amc();
        break;
      case "send_reminder_amc":
        // Send reminders to patients per patient preference for preventive/follow up care.
        // 170.304(d)
        $this->send_reminder_amc();
        break;
      case "provide_rec_pat_amc":
        // Provide patients with an electronic copy of their health information (including diagnostic test results, problem list, medication lists, medication allergies), upon request.
        // 170.304(f)
        $this->provide_rec_pat_amc();
        break;
      case "timely_access_amc":
        // Provide patients with timely electronic access to their health information (including lab results, problem list, medication lists, medication allergies) within four business days of the information being available to the EP.
        // 170.304(g)
        $this->timely_access_amc();
        break;
      case "provide_sum_pat_amc":
        // Provide clinical summaries for patients for each office visit.
        // 170.304(h)
        $this->provide_sum_pat_amc();
        break;
      case "send_sum_amc":
        // The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral.
        // 170.304(i)
        $this->send_sum_amc();
        break;
      default:
        break;
    }
  }

  // Function to process the results
  private function process_results() {
    // if applicable, need to iterate through each group of data
    for($i=0;$i<count($this->group_label);$i++) {
      $newRow=array( 'is_main'=>TRUE, // TO DO: figure out way to do this when multiple groups.
                     'total_patients'=>($this->total_patients[$i]),
                     'excluded'=>($this->exclude_filter[$i]),
                     'pass_filter'=>($this->pass_filter[$i]),
                     'pass_target'=>($this->pass_target[$i]),
                     'percentage'=>($this->percentage[$i]) );
      $newRow=array_merge($newRow,$this->rule);
      $this->results[] = $newRow;
    }
  }

  // Function to set a results element
  // Param:
  //   $group_lab    - Label of element
  //   $total_pat    - Total number of patients considered
  //   $pass_filt    - Number of patients that pass filter
  //   $exclude_filt - Number of patients that are excluded
  //   $pass_targ    - Number of patients that pass target
  //   $perc         - Calculated percentage
  private function set_result($group_lab,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc) {
    $this->group_label[]    =  $group_lab;
    $this->total_patients[] = $total_pat;
    $this->pass_filter[]    =  $pass_filt;
    $this->exclude_filter[] = $exclude_filt;
    $this->pass_target[]    = $pass_targ;
    $this->percentage[]     = $perc;
  }

  // Function to see if encounters exist
  // Parameters:
  //   $patient_id      - patient id
  //   $encounter_label - encounter id from list_options
  //   $begin_date      - begin of date to search (if blank, then will not limit to a begin date)
  //   $end_date        - end of date to search
  //   $number          - number of encounters needed
  private function exist_encounter($patient_id,$encounter_label,$begin_date='',$end_date,$number) {
    // use the getEncounters() function from library/forms.inc
    $encounters = getEncounters($patient_id,$begin_date,$end_date,$encounter_label);
    (empty($encounters)) ? $totalNumberAppt = 0 : $totalNumberAppt = count($encounters);
    if ( $totalNumberAppt < $number ) {
      return false;
    }
    else {
      return true;
    }
  }
  
  
  // Function to get patient dob
  // Parameter:
  //   $patient_id - patient id
  // Return: DOB (string)
  private function get_DOB($patient_id) {
    $dob = getPatientData($patient_id, "DATE_FORMAT(DOB,'%Y %m %d') as TS_DOB");
    return $dob['TS_DOB'];
  }

  // Function to get patient id from the patient array
  // Paramter:
  //  $array_patient - array holding patient id in the 'id' column
  private function get_patient_id($array_patient) {
    return $array_patient['pid'];
  }

  //  Hypertension: Blood Pressure Measurement (NQF 0013)
  //
  //  (note it only needs to process one group)
  //  (Measurement for 12 months from 1/1/20?? to 12/31/20??)
  //  
  //  1) Calculate number that pass the filter
  //     -Age greater than 18 (before the beginning of the measurement period).
  //     -Diagnosis of HTN (before or during the measurement period)
  //     -At least two encounters (of a specified type and within the measurement
  //      period)
  //  2) Deal with exclusions:
  //     -No exclusions
  //  3) Then from patients that pass the filter (and are not excluded), calculate
  //     the number that have the target.
  //     -Measurement of a SBP and a DBP within the measurment period (needs
  //      to be on same day as specified encounter type)
  //  4) Then calculate the percentage, which is number that meet the target
  //     divided by the number of patients that pass the filter (and are not excluded).
  //  
  private function rule_htn_bp_measure_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData = $this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = 0;
    $perc = 0;
    foreach ( $patientData as $rowPatient ) { 
      // increment total patients counter
      $total_pat++;

      // get patient id
      $patient_id = $this->get_patient_id($rowPatient);

      // filter for age greater than 18
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      if (convertDobtoAgeYearDecimal( $this->get_DOB($patient_id), $begin_measurement ) < 18) continue;

      // filter for diagnosis of HTN
      // utlize the exist_lists_item() function from library/clinical_rules.php
      if (!( (exist_lists_item($patient_id,'medical_problem','CUSTOM::HTN',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::401.0',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::401.1',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::401.9',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.00',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.01',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.10',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.11',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.90',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::402.91',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.00',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.01',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.10',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.11',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.90',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::403.91',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.00',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.01',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.02',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.03',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.10',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.11',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.12',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.13',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.90',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.91',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.92',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::404.93',$end_measurement)) )) {
         continue;
       }

      // filter for 2 specified encounters
      // make a function for this and wrap in the encounter titles
      if (!( ($this->exist_encounter($patient_id,'enc_outpatient',$begin_measurement,$end_measurement,2)) ||
             ($this->exist_encounter($patient_id,'enc_nurs_fac',$begin_measurement,$end_measurement,2)) )) {

        continue;
      }

      // Filter has been passed
      $pass_filt++;

      // See if BP has been done within the measurement period (on a day of a specified encounter)
      $query = "SELECT form_vitals.bps, form_vitals.bpd " .
                 "FROM `form_vitals` " .
                 "LEFT JOIN `form_encounter` " .
                 "ON ( DATE(form_vitals.date) = DATE(form_encounter.date)) " .
                 "LEFT JOIN `enc_category_map` " .
                 "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                 "WHERE form_vitals.pid = ?" .
                 "AND form_vitals.bps IS NOT NULL " .
                 "AND form_vitals.bpd IS NOT NULL " .
                 "AND form_vitals.date >= ? " .
                 "AND form_vitals.date <= ? " .
                 "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' OR enc_category_map.rule_enc_id = 'enc_nurs_fac' )";
      $res = sqlStatement($query, array($patient_id,$begin_measurement,$end_measurement) );
      $number = sqlNumRows($res);
      if ($number < 1) continue;

      error_log("passed target",0);

      // Target has been passed
      $pass_targ++;
    }
    
    // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
    $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ);

    // Set results
    $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc);
  }

  // Tobacco Use Assessment (NQF 0028a)
  private function rule_tob_use_assess_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData =$this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = 0;
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id($rowPatient);
      // increment total patients counter
      $total_pat++;

      // filter for age greater than 18
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 18) continue;   

      // filter for 2 specified encounters of some types, and 1 for others
      // make a function for this and wrap in the encounter titles
      if (!( ($this->exist_encounter($patient_id,'enc_off_vis',$begin_measurement,$end_measurement,2)) ||
             ($this->exist_encounter($patient_id,'enc_hea_and_beh',$begin_measurement,$end_measurement,2)) ||
             ($this->exist_encounter($patient_id,'enc_occ_ther',$begin_measurement,$end_measurement,2)) || 
             ($this->exist_encounter($patient_id,'enc_psych_and_psych',$begin_measurement,$end_measurement,2)) ||
             ($this->exist_encounter($patient_id,'enc_pre_med_ser_18_older',$begin_measurement,$end_measurement,1)) || 
             ($this->exist_encounter($patient_id,'enc_pre_ind_counsel',$begin_measurement,$end_measurement,1)) ||
             ($this->exist_encounter($patient_id,'enc_pre_med_group_counsel',$begin_measurement,$end_measurement,1)) ||
             ($this->exist_encounter($patient_id,'enc_pre_med_other_serv',$begin_measurement,$end_measurement,1)) )) {
        continue;
      }
      
      // Filter has been passed
      $pass_filt++;
      
      // See if user has been a tobacco user before or sumultaneosly to the encounter within two years (24 months)
      $begin_24_months_before_time = strtotime( '-24 month' , strtotime ( $begin_measurement ) );
      $begin_24_months_before = date( 'Y-m-d 00:00:00' , $begin_24_months_before_time );
      $tobaccoHistory = getHistoryData( $patient_id, "tobacco", $begin_24_months_before, $end_measurement );
      if ( !isset( $tobaccoHistory['tobacco'] ) ) {
          continue;
      }

      // Target has been passed
      $pass_targ++;
    }
    
    // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
    $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ);

    // Set results
    $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc);
  }

  // TODO
  // Tobacco Cessation Intervention (NQF 0028b)
  private function rule_tob_cess_inter_cqm() {
    
  }

  // Adult Weight Screening and Follow-Up (NQF 0421) (PQRI 128)
  private function rule_adult_wt_screen_fu_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData =$this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = 0;
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for age greater than 65
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 65 ) continue;
      
      // filter for 1 specified encounters
      // make a function for this and wrap in the encounter titles
      // doesn't say anything about encounter date, so check for any encounter
      if (!( ( $this->exist_encounter($patient_id,'enc_outpatient',$begin_measurement,$end_measurement,1)) )) {
          continue;
      }
      
      $pass_filt++;
      
      // Flow of control loop
      $bContinue = true;
      do {
          // See if BMI has been recorded between >=22kg/m2 and <30kg/m2 6 months before, or simultanious to the encounter
          $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
          			 "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 22 AND form_vitals.BMI < 30 " .
                     "AND DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) " .
                     "AND DATE( form_vitals.date ) <= DATE( form_encounter.date ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
          $res = sqlStatement( $query, array( $patient_id ) );
          $number = sqlNumRows($res);
          if ( $number >= 1 ) {
              $bContinue = false;
              break;
          }
          
          // See if BMI has been recorded >=30kg/m2 6 months before, or simultanious to the encounter
          // TODO AND “Care goal: follow-up plan BMI management” OR “Communication provider to provider: dietary consultation order”
          $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 30 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
          $res = sqlStatement( $query, array( $patient_id ) );
          $number = sqlNumRows($res);
          if ( $number >= 1 ) {
              $bContinue = false;
              break;
          }
          
          // See if BMI has been recorded <22kg/m2 6 months before, or simultanious to the encounter
          // TODO AND “Care goal: follow-up plan BMI management” OR “Communication provider to provider: dietary consultation order”
          $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI < 22 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
          $res = sqlStatement( $query, array( $patient_id ) );
          $number = sqlNumRows($res);
          if ( $number >= 1 ) {
              $bContinue = false;
              break;
          }
      } while( false );
      
      if ( $bContinue ) {
          continue;
      }
      
      // TODO Exclusions
      // OR:“Patient characteristic: Terminal illness”<=6 months before or simultaneously to “Encounter: encounter outpatient”;
      // OR:“Physical exam not done: patient reason”; 
      // OR:“Physical exam not done: medical reason”; 
      // OR:“Physical rationale physical exam not done: system reason”;      
      if ( $this->codes->check_for_pregnancy( $patient_id, $end_measurement ) ) {
          $exclude_filt++;
      }

      $pass_targ++;
    }
    
    // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
    $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ);

    // Set results
    $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc);
    
    // *** Population criteria 2 ***
    // reset counters
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = 0;
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for >= 18 and <= 64
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 18 || $age > 65 ) continue;
      
      // Denominator=
      // AND: “All patients in the initial patient population”;
      // AND: >=1 count(s) of “Encounter: encounter outpatient”;
      if (!( ( $this->exist_encounter($patient_id,'enc_outpatient',$begin_measurement,$end_measurement,1)) )) {
          continue;
      }
      
      $pass_filt++;
      
    // Flow of control loop
      $bContinue = true;
      do {
          // See if BMI has been recorded between >=18.5kg/m2 and <25kg/m2 6 months before, or simultanious to the encounter
          $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
          			 "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 18.5 AND form_vitals.BMI < 25 " .
                     "AND DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) " .
                     "AND DATE( form_vitals.date ) <= DATE( form_encounter.date ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
          $res = sqlStatement( $query, array( $patient_id ) );
          $number = sqlNumRows($res);
          if ( $number >= 1 ) {
              $bContinue = false;
              break;
          }
          
          // See if BMI has been recorded >=25kg/m2 6 months before, or simultanious to the encounter
          // TODO AND “Care goal: follow-up plan BMI management” OR “Communication provider to provider: dietary consultation order”
          $query = "SELECT form_vitals.BMI " .
                     "FROM `form_vitals` " .
                     "LEFT JOIN `form_encounter` " .
                     "ON ( form_vitals.pid = form_encounter.pid ) " .
                     "LEFT JOIN `enc_category_map` " .
                     "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                     "WHERE form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.BMI IS NOT NULL " .
                     "AND form_vitals.pid = ? AND form_vitals.BMI >= 25 " .
                     "AND ( DATE( form_vitals.date ) >= DATE_ADD( form_encounter.date, INTERVAL -6 MONTH ) ) " .
                     "AND ( DATE( form_vitals.date ) <= DATE( form_encounter.date ) ) " .
                     "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' )";
          $res = sqlStatement( $query, array( $patient_id ) );
          $number = sqlNumRows($res);
          if ( $number >= 1 ) {
              $bContinue = false;
              break;
          }

      } while( false );
      
      if ( $bContinue ) {
          continue;
      }
      
      // TODO Exclusions
      // OR:“Patient characteristic: Terminal illness”<=6 months before or simultaneously to “Encounter: encounter outpatient”;
      // OR:“Physical exam not done: patient reason”; 
      // OR:“Physical exam not done: medical reason”; 
      // OR:“Physical rationale physical exam not done: system reason”;
      if ( $this->codes->check_for_pregnancy( $patient_id, $end_measurement ) ) {
          $exclude_filt++;
      }
      
      $pass_targ++;
    }
    
    // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
    $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ);

    // Set results
    $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc);
    
  }

  // Weight Assessment and Counseling for Children and Adolescents (NQF 0024)
  private function rule_wt_assess_couns_child_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData =$this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // *** Patient Criteria 1 ***
    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = array( 1 => 0, 2 => 0, 3 => 0 );
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for Patient characteristic: birth date” (age) >=2 and <=16 years
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 2 || $age > 17 ) continue;
      
      // filter for 1 specified encounters
      // make a function for this and wrap in the encounter titles
      if (  ( !( $this->exist_encounter($patient_id,'enc_out_pcp_obgyn',$begin_measurement,$end_measurement,1) ) ) ||
               ( $this->codes->check_for_pregnancy( $patient_id, $end_measurement ) ||
               ( $this->exist_encounter($patient_id,'enc_pregnancy',$begin_measurement,$end_measurement,1) ) ) ) {
          continue;
      }
      
      $pass_filt++;
      
      // numerator 1: Physical exam finding: BMI percentile
      if ( ( (exist_lists_item($patient_id,'medical_problem','CUSTOM::BMI',$end_measurement)) || // TODO where should this come from?
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.5',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.51',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.52',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.53',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.54',$end_measurement)) )) {
         $pass_targ[1]++;
      }
      
      // numerator 2: Communication to patient: counseling for nutrition
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.3',$end_measurement )  ) { // TODO where should this come from?
         $pass_targ[2]++;
      }
      
      // numerator 3: Communication to patient: counseling for physical activity
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.41',$end_measurement ) ) { // TODO where should this come from?
         $pass_targ[3]++;
      } 
    }
    
    foreach ( $pass_targ as $pass_targ_count ) {
        // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
        $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ_count);
        // Set results
        $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ_count,$perc);
    }
    
    // *** Patient Criteria 2 ***
    // reset counters
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = array( 1 => 0, 2 => 0, 3 => 0 );
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for Patient characteristic: birth date” (age) >=2 and <=16 years
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 2 || $age > 11 ) continue;
      
      // filter for 1 specified encounters
      // make a function for this and wrap in the encounter titles
      if (  ( !( $this->exist_encounter($patient_id,'enc_out_pcp_obgyn',$begin_measurement,$end_measurement,1) ) ) ||
               ( $this->codes->check_for_pregnancy( $patient_id, $end_measurement ) ||
               ( $this->exist_encounter($patient_id,'enc_pregnancy',$begin_measurement,$end_measurement,1) ) ) ) {
          continue;
      }
      
      $pass_filt++;
      
      // numerator 1: Physical exam finding: BMI percentile
      if ( ( (exist_lists_item($patient_id,'medical_problem','CUSTOM::BMI',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.5',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.51',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.52',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.53',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.54',$end_measurement)) )) {
         $pass_targ[1]++;
       }
      
      // numerator 2: Communication to patient: counseling for nutrition
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.3',$end_measurement )  ) {
         $pass_targ[2]++;
       }
      
      // numerator 3: Communication to patient: counseling for physical activity
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.41',$end_measurement ) ) {
         $pass_targ[3]++;
       } 
    }
    
    foreach ( $pass_targ as $pass_targ_count ) {
        // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
        $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ_count);
        // Set results
        $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ_count,$perc);
    }
    
  // *** Patient Criteria 3 ***
    // reset counters
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = array( 1 => 0, 2 => 0, 3 => 0 );
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for Patient characteristic: birth date” (age) >=2 and <=16 years
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age < 12 || $age > 17 ) continue;
      
      // filter for 1 specified encounters
      // make a function for this and wrap in the encounter titles
      if (  ( !( $this->exist_encounter($patient_id,'enc_out_pcp_obgyn',$begin_measurement,$end_measurement,1) ) ) ||
               ( $this->codes->check_for_pregnancy( $patient_id, $end_measurement ) ||
               ( $this->exist_encounter($patient_id,'enc_pregnancy',$begin_measurement,$end_measurement,1) ) ) ) {
          continue;
      }
      
      $pass_filt++;
      
      // numerator 1: Physical exam finding: BMI percentile
      if ( ( (exist_lists_item($patient_id,'medical_problem','CUSTOM::BMI',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.5',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.51',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.52',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.53',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::V85.54',$end_measurement)) )) {
         $pass_targ[1]++;
       }
      
      // numerator 2: Communication to patient: counseling for nutrition
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.3',$end_measurement )  ) {
         $pass_targ[2]++;
       }
      
      // numerator 3: Communication to patient: counseling for physical activity
      if ( exist_lists_item( $patient_id,'medical_problem','ICD9::V65.41',$end_measurement ) ) {
         $pass_targ[3]++;
       } 
    }
    
    foreach ( $pass_targ as $pass_targ_count ) {
        // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
        $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ_count);
        // Set results
        $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ_count,$perc);
    }
    
  }

  // TODO
  // Influenza Immunization for Patients >= 50 Years Old (NQF 0041) (PQRI 110)
  private function rule_influenza_ge_50_cqm() {

  }

  // TODO
  // Childhood immunization Status (NQF 0038)
  private function rule_child_immun_stat_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData =$this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0 );
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      $patient_id = $this->get_patient_id( $rowPatient ); 
      // increment total patients counter
      $total_pat++;

      // filter for “Patient characteristic: birth date” (age) >=1 year and <2 years
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      $age = convertDobtoAgeYearDecimal( $this->get_DOB( $patient_id ), $begin_measurement );
      if ( $age >= 2 || $age < 1 ) continue;
      
      if (  ( !( $this->exist_encounter($patient_id,'enc_out_pcp_obgyn',$begin_measurement,$end_measurement,1) ) ) ) {
          continue;
      }
      
      $pass_filt++;
      
      // Numerator 1
      $query = "SELECT immunizations.administered_date, immunizations.patient_id, immunizations.immunization_id, list_options.title, patient_data.pid, patient_data.DOB " .
    	"FROM immunizations " .
    	"LEFT JOIN list_options " .
        "ON immunizations.immunization_id = list_options.option_id AND list_id = immunizations" .
        "LEFT JOIN patient_data " .
        "ON immunizations.patient_id = patient_data.pid " .
    	"WHERE immunizations.patient_id = ? " .
        "AND ( list_options.option_id = 1 ". // Check for DTap list option ids (1-5)
        	"OR list_options.option_id = 2 ".
        	"OR list_options.option_id = 3 ".
        	"OR list_options.option_id = 4 ".
        	"OR list_options.option_id = 5 ) " . 
        "AND DATE( immunizations.administered_date ) >= DATE_ADD( patient_data.DOB, INTERVAL 42 DAY ) " .
        "AND DATE( immunizations.administered_date ) < DATE_ADD( patient_data.DOB, INTERVAL 2 YEAR ) ";
    
      $result = sqlStatement( $query, array( $patient_id ) );
      if ( count( $result ) >= 4 && 
          !( $this->codes->check_for_dtap_allergy( $patient_id, $end_measurement ) ) &&
          !( exist_lists_item( $patient_id, 'medical_problem', 'ICD9::323.51', $end_measurement ) ) &&
          !( $this->codes->check_for_progressive_neurological_disorder( $patient_id, $end_measurement ) ) ) {
         $pass_targ[1]++;
      }
      
      // Numerator 2
    $query = "SELECT immunizations.administered_date, immunizations.patient_id, immunizations.immunization_id, list_options.title, patient_data.pid, patient_data.DOB " .
    	"FROM immunizations " .
    	"LEFT JOIN list_options " .
        "ON immunizations.immunization_id = list_options.option_id AND list_id = immunizations" .
        "LEFT JOIN patient_data " .
        "ON immunizations.patient_id = patient_data.pid " .
    	"WHERE immunizations.patient_id = ? " .
        "AND ( list_options.option_id = 13 ". // Check for IPV list option ids (11-14)
        	"OR list_options.option_id = 11 ".
        	"OR list_options.option_id = 12 ".
        	"OR list_options.option_id = 14 ".
        "AND DATE( immunizations.administered_date ) >= DATE_ADD( patient_data.DOB, INTERVAL 42 DAY ) " .
        "AND DATE( immunizations.administered_date ) < DATE_ADD( patient_data.DOB, INTERVAL 2 YEAR ) ";
    
      $result = sqlStatement( $query, array( $patient_id ) );
      if ( count( $result ) >= 3 && 
          !( $this->codes->check_for_ipv_allergy( $patient_id, $end_measurement ) ) &&
          !( $this->codes->check_for_neomycin_allergy( $patient_id, $end_measurement ) ) &&
          !( $this->codes->check_for_streptomycin_allergy( $patient_id, $end_measurement ) ) &&
          !( $this->codes->check_for_polymyxin_allergy( $patient_id, $end_measurement ) ) ) {
         $pass_targ[2]++;
      }  
    }
    
    // Numerator 3
    
    // Numerator 4
    $query = "SELECT immunizations.administered_date, immunizations.patient_id, immunizations.immunization_id, list_options.title, patient_data.pid, patient_data.DOB " .
    	"FROM immunizations " .
    	"LEFT JOIN list_options " .
        "ON immunizations.immunization_id = list_options.option_id AND list_id = immunizations" .
        "LEFT JOIN patient_data " .
        "ON immunizations.patient_id = patient_data.pid " .
    	"WHERE immunizations.patient_id = ? " .
        "AND ( list_options.option_id = 80 ". // Check for HiB list option ids 
        	"OR list_options.option_id = 85 ".
        	"OR list_options.option_id = 90 ".
        "AND DATE( immunizations.administered_date ) >= DATE_ADD( patient_data.DOB, INTERVAL 42 DAY ) " .
        "AND DATE( immunizations.administered_date ) < DATE_ADD( patient_data.DOB, INTERVAL 2 YEAR ) ";
    
      $result = sqlStatement( $query, array( $patient_id ) );
      if ( count( $result ) >= 2 && 
          !( $this->codes->check_for_hib_allergy( $patient_id, $end_measurement ) ) ) {
         $pass_targ[4]++;
      }
    
    foreach ( $pass_targ as $pass_targ_count ) {
        // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
        $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ_count);
        // Set results
        $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ_count,$perc);
    }
  }

  // TODO
  // Pneumonia Vaccination Status for Older Adults (NQF 0043) (PQRI 111)
  private function rule_pneumovacc_ge_65_cqm() {

  }

  // TODO
  // Diabetes: Eye Exam (NQF 0055) (PQRI 117)
  private function rule_dm_eye_cqm() {

  }

  // TODO
  // Diabetes: Foot Exam (NQF 0056) (PQRI 163)
  private function rule_dm_foot_cqm() {

  }

  // TODO
  // Diabetes: Blood Pressure Management (NQF 0061) (PQRI 3)
  private function rule_dm_bp_control_cqm() {

  }
  
  // Diabetes: HbA1c Poor Control (NQF 0059) (PQRI 1) 
  private function rule_dm_a1c_cqm() {
    $rule_id=$this->rule['id'];
    $dateTarget = $this->dateTarget;
    $patientData =$this->patientData;

    // Calculate measurement period
    $tempDateArray = explode("-",$dateTarget);
    $tempYear = $tempDateArray[0];
    $begin_measurement = $tempDateArray[0] . "-01-01 00:00:00";
    $end_measurement = $tempDateArray[0] . "-12-31 23:59:59";

    // Collect results
    $total_pat = 0;
    $pass_filt = 0;
    $exclude_filt = 0;
    $pass_targ = 0;
    $perc = 0;
    foreach ( $patientData as $rowPatient ) {
      // increment total patients counter
      $total_pat++;

      // get patient id
      $patient_id = $this->get_patient_id($rowPatient);

      // filter for age less than 18 AND greater than 75
      // utilize the convertDobtoAgeYearDecimal() function from library/clinical_rules.php
      if ( (convertDobtoAgeYearDecimal( $this->get_DOB($patient_id), $begin_measurement ) < 18) ||
            (convertDobtoAgeYearDecimal( $this->get_DOB($patient_id), $begin_measurement ) > 75) ) {
            	continue;
      }

      // filter for diagnosis of Diabetes
      // utlize the exist_lists_item() function from library/clinical_rules.php
      if (!( (exist_lists_item($patient_id,'medical_problem','CUSTOM::diabetes',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.0',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.00',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.02',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.03',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.10',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.11',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.13',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.20',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.21',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.22',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.23',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.30',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.31',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.32',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.33',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.40',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.41',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.42',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.43',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.50',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.51',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.52',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.53',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.60',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.61',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.62',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.31',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.7',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.70',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.71',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.72',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.73',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.80',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.81',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.82',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.83',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.9',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.90',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.91',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.92',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::250.93',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::357.2',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.0',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.01',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.02',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.03',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.04',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.05',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::362.05',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::366.41',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.0',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.00',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.01',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.02',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.03',$end_measurement)) ||
             (exist_lists_item($patient_id,'medical_problem','ICD9::648.04',$end_measurement)) )) {
         continue;
       }

      // Filter has been passed
      $pass_filt++;
       
	  // collect specific items that fulfill request(Hemoglobin A1C >9.0%)
	  $proc_code = "CPT4:83036";// CPT Code for Hemoglobin A1C
	  $sql = sqlStatement("SELECT procedure_result.result " .
	         "FROM `procedure_type`, " .
	         "`procedure_order`, " .
	         "`procedure_report`, " .
	         "`procedure_result` " .
	         "WHERE procedure_type.procedure_type_id = procedure_order.procedure_type_id " .
	         "AND procedure_order.procedure_order_id = procedure_report.procedure_order_id " .
	  		 "AND procedure_report.procedure_report_id = procedure_result.procedure_report_id " .
	         "AND procedure_type.standard_code = ? " .
	         "AND procedure_result.result <= 9 " .
	         "AND procedure_order.patient_id = ? ", array($proc_code,$patient_id) );
      $number = sqlNumRows($sql);
      if ($number < 1) continue;
	  

      error_log("passed target",0);

      // Target has been passed
      $pass_targ++;
    }
    
    // Calculate Percentage (use calculate_percentage() function from library/clinical_rules.php
    $perc = calculate_percentage($pass_filt,$exclude_filt,$pass_targ);

    // Set results
    $this->set_result($rule_id,$total_pat,$pass_filt,$exclude_filt,$pass_targ,$perc);
  }

  // TODO
  // Diabetes: LDL Management & Control (NQF 0064) (PQRI 2)
  private function rule_dm_ldl_cqm() {

  }

  // TODO
  // Maintain an up-to-date problem list of current and active diagnoses.
  // 170.302(c)
  private function problem_list_amc() {

  }

  // TODO
  // Maintain active medication list.
  // 170.302(d)
  private function med_list_amc() {

  }

  // TODO
  // Maintain active medication allergy list.
  // 170.302(e)
  private function med_allergy_list_amc() {

  }

  // TODO
  // Record and chart changes in vital signs.
  // 170.302(f)
  private function record_vitals_amc() {

  }

  // TODO
  // Record smoking status for patients 13 years old or older.
  // 170.302(g)
  private function record_smoke_amc() {

  }

  // TODO
  // Incorporate clinical lab-test results into certified EHR technology as structured data.
  // 170.302(h)
  private function lab_result_amc() {

  }

  // TODO
  // The EP, eligible hospital or CAH who receives a patient from another setting of care or provider of care or believes an encounter is relevant should perform medication reconciliation.
  // 170.302(j)
  private function med_reconc_amc() {

  }

  // TODO
  // Use certified EHR technology to identify patient-specific education resources and provide those resources to the patient if appropriate.
  // 170.302(m)
  private function patient_edu_amc() {

  }

  // TODO
  // Use CPOE for medication orders directly entered by any licensed healthcare professional who can enter orders into the medical record per state, local and professional guidelines.
  // 170.304(a)
  private function cpoe_med_amc() {

  }

  // TODO
  // Generate and transmit permissible prescriptions electronically.
  // 170.304(b)
  private function e_prescribe_amc() {

  }

  // TODO
  // Record demographics.
  // 170.304(c)
  private function record_dem_amc() {

  }

  // TODO
  // Send reminders to patients per patient preference for preventive/follow up care.
  // 170.304(d)
  private function send_reminder_amc() {

  }

  // TODO
  // Provide patients with an electronic copy of their health information (including diagnostic test results, problem list, medication lists, medication allergies), upon request.
  // 170.304(f)
  private function provide_rec_pat_amc() {

  }

  // TODO
  // Provide patients with timely electronic access to their health information (including lab results, problem list, medication lists, medication allergies) within four business days of the information being available to the EP.
  // 170.304(g)
  private function timely_access_amc() {

  }

  // TODO
  // Provide clinical summaries for patients for each office visit.
  // 170.304(h)
  private function provide_sum_pat_amc() {

  }

  // TODO
  // The EP, eligible hospital or CAH who transitions their patient to another setting of care or provider of care or refers their patient to another provider of care should provide summary of care record for each transition of care or referral.
  // 170.304(i)
  private function send_sum_amc() {

  }

}
?>

