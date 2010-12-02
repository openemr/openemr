<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Functions are kept here that will support the clinical rules.

require_once(dirname(__FILE__) . "/patient.inc");
require_once(dirname(__FILE__) . "/forms.inc");
require_once(dirname(__FILE__) . "/formdata.inc.php");
require_once(dirname(__FILE__) . "/options.inc.php");

// Display the clinical summary widget.
// Parameters:
//   $patient_id - pid of selected patient
//   $dateTarget - target date. If blank then will test with current date as target.
function clinical_summary_widget($patient_id,$dateTarget='') {
  
  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect active actions
  $actions = test_rules_clinic('','passive_alert',$dateTarget,'reminders',$patient_id);

  // Display the actions
  foreach ($actions as $action) {

    if ($action['custom_flag']) {
      // Start link for reminders that use the custom rules input screen
      echo "<a href='../rules/patient_data.php?category=" .
        htmlspecialchars( $action['category'], ENT_QUOTES) . "&item=" . 
        htmlspecialchars( $action['item'], ENT_QUOTES) . 
        "' class='iframe  medium_modal' onclick='top.restoreSession()'>";
    }
    else if ($action['clin_rem_link']) {
      // Start link for reminders that use the custom rules input screen
      echo "<a href='../../../" . $action['reminder_message'] .
        "' class='iframe  medium_modal' onclick='top.restoreSession()'>";
    }
    else {
      // continue, since no link will be created
    }

    // Display Reminder Details
    echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$action['category']) .
      ": " . generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$action['item']);

    if ($action['custom_flag'] || $action['clin_rem_link']) {
      // End link for reminders that use an html link
      echo "</a>";
    }

    // Display due status
    if ($action['due_status']) {
      // Color code the status (red for past due, yellow for due, and black for soon due)
      if ($action['due_status'] == "past_due") {
        echo "&nbsp;&nbsp;(<span style='color:red'>";
      }
      else if ($action['due_status'] == "due") {
        echo "&nbsp;&nbsp;(<span style='color:green'>";
      }
      else {
        echo "&nbsp;&nbsp;(<span>";
      }
      echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_reminder_due_opt'),$action['due_status']) . "</span>)<br>";
    }
    else {
      echo "<br>";
    }

  }
}

// Test the clinic rules of entire clinic and create a report or patient reminders
//  (can also test on one patient or patients of one provider)
// Parameters:
//   $provider   - id of a selected provider. If blank, then will test entire clinic.
//   $type       - rule filter (active_alert,passive_alert,cqm,patient_reminder). If blank then will test all rules.
//   $dateTarget - target date. If blank then will test with current date as target.
//   $mode       - choose either 'report' or 'reminders' (required)
//   $patient_id - pid of patient. If blank then will check all patients.
// Return:
//   Returns a two-dimensional array of results that depends on the mode:
//     reminders mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
//     report mode    - returns an array of rows for the Clinical Quality Measures (CQM) report
function test_rules_clinic($provider='',$type='',$dateTarget='',$mode='',$patient_id='') {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Prepare the results array
  $results = array();

  // Collect all patient ids
  $patientData = array();
  if (!empty($patient_id)) {
    // only look at the selected patient
    array_push($patientData,$patient_id);
  }
  else {
    if (empty($provider)) {
      // Look at entire practice
      $rez = sqlStatement("SELECT `pid` FROM `patient_data`");
      for($iter=0; $row=sqlFetchArray($rez); $iter++) {
       $patientData[$iter]=$row;
      } 
    }
    else {
      // Look at one provider
      $rez = sqlStatement("SELECT `pid` FROM `patient_data` " .
        "WHERE providerID=?", array($provider) );
      for($iter=0; $row=sqlFetchArray($rez); $iter++) {
       $patientData[$iter]=$row;
      }
    }
  }
  // Go through each patient(s)
  //
  //  If in report mode, then tabulate for each rule:
  //    Total Patients
  //    Patients that pass the filter
  //    Patients that pass the target
  //  If in reminders mode, then create reminders for each rule:
  //    Reminder that action is due soon
  //    Reminder that action is due
  //    Reminder that action is post-due
  
  //Collect applicable rules  
  if ($mode == "reminders") {
    // Use per patient custom rules (if exist)
    $rules = resolve_rules_sql($type,$patient_id);
  }
  else { // $mode = "report"
    // Only use default rules (do not use patient custom rules
    $rules = resolve_rules_sql($type);
  }

  foreach( $rules as $rowRule ) {

    // If in reminder mode then need to collect the measurement dates
    //  from rule_reminder table

    $target_dates = array();
    if ($mode == "reminders") {
      // Calculate the dates to check for
      if ($type == "patient_reminder") {
        $reminder_interval_type = "patient_reminder";
      }
      else { // $type == "passive_alert" or $type == "active_alert"
        $reminder_interval_type = "clinical_reminder";
      }
      $target_dates = calculate_reminder_dates($rowRule['id'], $dateTarget, $reminder_interval_type);
    }
    else { // $mode == "reports"
      // Only use the target date in the report
      $target_dates[0] = $dateTarget;
    }

    //Reset the counters
    $total_patients = 0;
    $pass_filter = 0;
    $pass_target = 0;

    foreach( $patientData as $rowPatient ) {

      // Count the total patients
      $total_patients++;

      $dateCounter = 1; // for reminder mode to keep track of which date checking
      foreach ( $target_dates as $dateFocus ) {

        //Set date counter and reminder token (applicable for reminders only)
        if ($dateCounter == 1) {
          $reminder_due = "soon_due";
        }
        else if ($dateCounter == 2) {
          $reminder_due = "due";
        }
        else { // $dateCounter == 3
          $reminder_due = "past_due";
        }

        // Check if pass filter
        $passFilter = test_filter($rowPatient['pid'],$rowRule['id'],$dateFocus);
        if ($passFilter) {
          $pass_filter++;
        }
        else {
          continue;
        }

        // Check if pass target
        $passTarget = test_targets($rowPatient['pid'],$rowRule['id'],'',$dateFocus); 
        if ($passTarget) {
          $pass_target++;
          break;
        }
        else {
          if ($mode == "reminders") {
            // place the actions into the reminder return array
            $actionArray = resolve_action_sql($rowRule['id'],'1');
            foreach ($actionArray as $action) {
              $action_plus = $action;
              $action_plus['due_status'] = $reminder_due;
              $action_plus['pid'] = $rowPatient['pid'];
              $results = reminder_results_integrate($results, $action_plus);
            }
          }
        }
        $dateCounter++;
      }
    }

    // Calculate and save the data for the rule
    if ($pass_filter > 0) {
      $percentage = number_format(($pass_target/$pass_filter)*100) . xl('%');
    }
    else {
      $percentage = "0". xl('%');
    }
    if ($mode == "report") {
      $newRow=array("main", $rowRule['id'], $total_patients, $pass_filter, $pass_target, $percentage);
      array_push($results, $newRow);
    }

    // Find the number of target groups, and go through each one if more than one
    $targetGroups = numberTargetGroups($rowRule['id']);
    if ($targetGroups > 1) {
      if ($mode == "reminders") {
        $start_id = 2;
      }
      else { // $mode == "report"
        $start_id = 1;
      }
      for ($i = $start_id; $i <= $targetGroups; $i++){

        //Reset the target counter
        $pass_target = 0;

        foreach( $patientData as $rowPatient ) {

          $dateCounter = 1; // for reminder mode to keep track of which date checking
          foreach ( $target_dates as $dateFocus ) {

            //Set date counter and reminder token (applicable for reminders only)
            if ($dateCounter == 1) {
              $reminder_due = "soon_due";
            }
            else if ($dateCounter == 2) {
              $reminder_due = "due";
            }
            else { // $dateCounter == 3
              $reminder_due = "past_due";
            }    

            //Check if pass target
            $passTarget = test_targets($rowPatient['pid'],$rowRule['id'],$i,$dateFocus);
            if ($passTarget) {
              $pass_target++;
              break;
            }
            else {
              if ($mode == "reminders") {
                // place the actions into the reminder return array
                $actionArray = resolve_action_sql($rowRule['id'],$i);
                foreach ($actionArray as $action) {
                  $action_plus = $action;
                  $action_plus['due_status'] = $reminder_due;
                  $action_plus['pid'] = $rowPatient['pid'];
                  $results = reminder_results_integrate($results, $action_plus);
                }
              }
            }
            $dateCounter++;
          }
        }

        // Calculate and save the data for the rule
        if ($pass_filter > 0) {
          $percentage = number_format(($pass_target/$pass_filter)*100) . xl('%');
        }
        else {
          $percentage = "0". xl('%');
        }

        // Collect action for title (just use the first one, if more than one)
        $actionArray = resolve_action_sql($rowRule['id'],$i);
        $action = $actionArray[0];
        if ($mode == "report") {
          $newRow=array("sub", $action['category']."::".$action['item'], " ", " ", $pass_target, $percentage);
          array_push($results, $newRow);
        }
      }
    }
  }

  // Return the data
  return $results;
}

// Test filter of a selected rule on a selected patient
// Parameters:
//   $patient_id - pid of selected patient.
//   $rule       - id(string) of selected rule
//   $dateTarget - target date.
// Return:
//   boolean (if pass filter then true, otherwise false)
function test_filter($patient_id,$rule,$dateTarget) {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect patient information
  $patientData = getPatientData($patient_id, "sex, DATE_FORMAT(DOB,'%Y %m %d') as DOB_TS");

  // -------- Special Filters --------
  // Check for special flag required by many of the CQM rules, that uses a standard
  //   measurement year (Jan1-Dec31). This adjusted date would then be used for
  //   date to calculate patient age and as the start range if filtering for clinic
  //   appointments. The value (usually will be 1) of this
  //   contains how many years to include.
  $adjustedDate1 = '';
  $adjustedDate2 = '';
  $filter = resolve_filter_sql($rule,'filt_measure_period');
  if (!empty($filter)) {
    $row = $filter[0];
    if ($row['method_detail'] == "year") {
      $tempDateArray = explode("-",$dateTarget);
      $tempYear = $tempDateArray[0];
      // Set too one second before the measurement period
      $adjustedDate1 = ($tempYear - $row['value']) . "-12-31 23:59:59";
      // Set too the first second of the measurement period
      $adjustedDate2 = ($tempYear - ($row['value']-1)) . "-01-01 00:00:00";
      // Set target date to the last second of the measurement period
      $dateTarget = ($tempYear - ($row['value']-1)) . "-12-31 23:59:59";
    }
  }

  // -------- Age Filter ------------
  // Calculate patient age in years and months
  if (!empty($adjustedDate1)) {
    // See above Special Filters section in for details.
    $patientAgeYears = convertDobtoAgeYearDecimal($patientData['DOB_TS'],$adjustedDate1);
    $patientAgeMonths = convertDobtoAgeMonthDecimal($patientData['DOB_TS'],$adjustedDate1);
  }
  else {
    $patientAgeYears = convertDobtoAgeYearDecimal($patientData['DOB_TS'],$dateTarget);
    $patientAgeMonths = convertDobtoAgeMonthDecimal($patientData['DOB_TS'],$dateTarget);
  }
  // Min age (year) Filter (includes) (assume that there in not more than one of each)
  $filter = resolve_filter_sql($rule,'filt_age_min');
  if (!empty($filter)) {
    $row = $filter[0];
    if ($row ['method_detail'] == "year") {
      if ( $row['value'] && ($row['value'] > $patientAgeYears) ) {
        return false;
      }
    }
    if ($row ['method_detail'] == "month") {
      if ( $row['value'] && ($row['value'] > $patientAgeMonths) ) {
        return false;
      }
    }
  }
  // Max age (year) Filter (includes) (assume that there in not more than one of each)
  $filter = resolve_filter_sql($rule,'filt_age_max');
  if (!empty($filter)) {
    $row = $filter[0];
    if ($row ['method_detail'] == "year") {
      if ( $row['value'] && ($row['value'] < $patientAgeYears) ) {
        return false;
      }
    }
    if ($row ['method_detail'] == "month") {
      if ( $row['value'] && ($row['value'] < $patientAgeMonths) ) {
        return false;
      }
    }
  }

  // -------- Gender Filter ---------
  // Gender Filter (includes) (assume that there in not more than one of each)
  $filter = resolve_filter_sql($rule,'filt_sex');
  if (!empty($filter)) {
    $row = $filter[0];
    if ( $row['value'] && ($row['value'] != $patientData['sex']) ) {
      return false;
    }
  }

  // -------- Database Filter ------
  // Database Filter (includes)
  $filter = resolve_filter_sql($rule,'filt_database');
  if ((!empty($filter)) && !database_check($patient_id,$filter,'',$dateTarget)) return false;

  // -------- Lists Filter ----
  // Set up lists filter, which is fully customizable and currently includes diagnoses, meds,
  //   surgeries and allergies.
  $filter = resolve_filter_sql($rule,'filt_lists');
  if ((!empty($filter)) && !lists_check($patient_id,$filter,$dateTarget)) return false;
  // Set up exclusion filter
  $filter = resolve_filter_sql($rule,'filt_lists',0);
  if ((!empty($filter)) && lists_check($patient_id,$filter,$dateTarget)) return false;

  // -------- Clinic Visit(s) Filter --------
  $filter = resolve_filter_sql($rule,'filt_encounter_min');
  if (!empty($filter)) {
    $row = $filter[0];
    // For total number of appointments, simply get number of encounters
    if (!empty($adjustedDate2)) {
      // See above Special Filters section in for details.
      $encounters = getEncounters($patient_id,$adjustedDate2,$dateTarget);
    }
    else {
      $encounters = getEncounters($patient_id,'',$dateTarget);
    }
    (empty($encounters)) ? $totalNumberAppt = 0 : $totalNumberAppt = count($encounters);
    if ($row['value'] && $totalNumberAppt < $row['value']) return false;
  }

  // Passed all filters, so return true.
  return true;
}

// Return the number of target groups of a selected rule
// Parameters:
//   $rule - id(string) of rule
// Return:
//   integer, number of target groups associated with rule
function numberTargetGroups($rule) {
  $numberGroups = 1;

  $sql = sqlQuery("SELECT max(`group_id`) as numberGroups FROM `rule_target` " .
    "WHERE `id`=?", array($rule) );

  if ($sql['numberGroups']) $numberGroups = $sql['numberGroups'];

  return $numberGroups;
}

// Test targets of a selected rule on a selected patient
// Parameters:
//   $patient_id - pid of selected patient.
//   $rule       - id(string) of selected rule (if blank, then will ignore grouping)
//   $group_id   - group id of target group
//   $dateTarget - target date.
// Return:
//   boolean (if target passes then true, otherwise false)
function test_targets($patient_id,$rule,$group_id='',$dateTarget) {

  // -------- Interval Target ----
  $interval = resolve_target_sql($rule,$group_id,'target_interval');

  // -------- Database Target ----
  // Database Target (includes)
  $target = resolve_target_sql($rule,$group_id,'target_database');
  if ((!empty($target)) && !database_check($patient_id,$target,$interval,$dateTarget)) return false;

  // Passed all target tests, so return true.
  return true;
}

// Function to return active rules
// Parameters:
//   $type       - rule filter (active_alert,passive_alert,cqm,patient_reminder)
//   $patient_id - pid of selected patient. (if custom rule does not exist then
//                 will use the default rule.
// Return: array containing rules
function resolve_rules_sql($type='',$patient_id='0') {

  // Collect all default rules into an array
  $sql = sqlStatement("SELECT * FROM `clinical_rules` WHERE `pid`=0 ORDER BY `id`");
  $returnArray= array();
  for($iter=0; $row=sqlFetchArray($sql); $iter++) {
    array_push($returnArray,$row);
  }

  // Now collect the pertinent rules
  $newReturnArray = array();

  // Need to select rules (use custom if exist)
  foreach ($returnArray as $rule) {
    $customRule = sqlQuery("SELECT * FROM `clinical_rules` WHERE `id`=? AND `pid`=?", array($rule['id'],$patient_id) );

    // Decide if use default vs custom rule (preference given to custom rule)
    if (!empty($customRule)) {
      $goRule = $customRule;
    }
    else {
      $goRule = $rule;
    }

    // Use the chosen rule if set
    if (!empty($type)) {
      if ($goRule['active'] == 1 && $goRule["${type}_flag"] == 1) {
        // active, so use the rule
        array_push($newReturnArray,$goRule);
      }
    }
    else {
      if ($goRule['active'] == 1) {
        // active, so use the rule
        array_push($newReturnArray,$goRule);
      }
    }
  }
  $returnArray = $newReturnArray;

  return $returnArray;
}

// Function to return applicable reminder dates (relative)
// Parameters:
//   $rule            - id(string) of selected rule
//   $reminder_method - string label of filter type
// Return: array containing reminder features
function resolve_reminder_sql($rule,$reminder_method) {
  $sql = sqlStatement("SELECT `method_detail`, `value` FROM `rule_reminder` " .
    "WHERE `id`=? AND `method`=?", array($rule, $reminder_method) );

  $returnArray= array();
  for($iter=0; $row=sqlFetchArray($sql); $iter++) {
    array_push($returnArray,$row);
  }
  return $returnArray;
}

// Function to return applicable filters
// Parameters:
//   $rule          - id(string) of selected rule
//   $filter_method - string label of filter type
//   $include_flag  - to allow selection for included or excluded filters
// Return: array containing filters
function resolve_filter_sql($rule,$filter_method,$include_flag=1) {
  $sql = sqlStatement("SELECT `method_detail`, `value`, `required_flag` FROM `rule_filter` " .
    "WHERE `id`=? AND `method`=? AND `include_flag`=?", array($rule, $filter_method, $include_flag) );

  $returnArray= array();
  for($iter=0; $row=sqlFetchArray($sql); $iter++) {
    array_push($returnArray,$row);
  }
  return $returnArray;
}

// Function to return applicable targets
// Parameters:
//   $rule          - id(string) of selected rule
//   $group_id      - group id of target group (if blank, then will ignore grouping)
//   $target_method - string label of target type
//   $include_flag  - to allow selection for included or excluded targets
// Return: array containing targets
function resolve_target_sql($rule,$group_id='',$target_method,$include_flag=1) {

  if ($group_id) {
    $sql = sqlStatement("SELECT `value`, `required_flag`, `interval` FROM `rule_target` " .
      "WHERE `id`=? AND `group_id`=? AND `method`=? AND `include_flag`=?", array($rule, $group_id, $target_method, $include_flag) );
  }
  else {
    $sql = sqlStatement("SELECT `value`, `required_flag`, `interval` FROM `rule_target` " .
      "WHERE `id`=? AND `method`=? AND `include_flag`=?", array($rule, $target_method, $include_flag) );
  }

  $returnArray= array();
  for($iter=0; $row=sqlFetchArray($sql); $iter++) {
    array_push($returnArray,$row);
  }
  return $returnArray;
}

// Function to return applicable actions
// Parameters:
//   $rule          - id(string) of selected rule
//   $group_id      - group id of target group (if blank, then will ignore grouping)
// Return: array containing actions
function resolve_action_sql($rule,$group_id='') {

  if ($group_id) {
    $sql = sqlStatement("SELECT b.category, b.item, b.clin_rem_link, b.reminder_message, b.custom_flag " .
      "FROM `rule_action` as a " .
      "JOIN `rule_action_item` as b " .
      "ON a.category = b.category AND a.item = b.item " .
      "WHERE a.id=? AND a.group_id=?", array($rule,$group_id) );
  }
  else {
    $sql = sqlStatement("SELECT b.category, b.item, b.value, b.custom_flag " .
      "FROM `rule_action` as a " .
      "JOIN `rule_action_item` as b " .
      "ON a.category = b.category AND a.item = b.item " .
      "WHERE a.id=?", array($rule) );
  }

  $returnArray= array();
  for($iter=0; $row=sqlFetchArray($sql); $iter++) {
    array_push($returnArray,$row);
  }
  return $returnArray;
}

// Function to check database filters and targets
// Parameters:
//   $patient_id - pid of selected patient.
//   $filter     - array containing filter/target elements
//   $interval   - used for the interval elements
//   $dateTarget - target date. blank is current date.
// Return: boolean if check passed, otherwise false
function database_check($patient_id,$filter,$interval='',$dateTarget='') {
  $isMatch = false; //matching flag

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Unpackage interval information
  // (Assume only one for now and only pertinent for targets)
  $intervalType = '';
  $intervalValue = '';
  if (!empty($interval)) {
    $intervalType = $interval[0]['value'];
    $intervalValue = $interval[0]['interval'];
  }

  foreach( $filter as $row ) {
    $temp_df = explode("::",$row['value']);
    if ($temp_df[3] == "EXIST" || $temp_df[3] == "lt") {
      if (exist_database_item($patient_id, $temp_df[0], $temp_df[1], $temp_df[2], $temp_df[4], $intervalType, $intervalValue, $dateTarget, $temp_df[3])) {
        // Record the match
        $isMatch = true;
      }
      else {
       // If this is a required entry then return false
       if ($row['required_flag']) return false;
      }
    }
    else if ($temp_df[3] == "CUSTOM") {
      if (exist_custom_item($patient_id, $temp_df[0], $temp_df[1], $temp_df[2], $temp_df[4], $intervalType, $intervalValue, $dateTarget)) {
        // Record the match
        $isMatch = true;
      }
      else {
       // If this is a required entry then return false
       if ($row['required_flag']) return false;
      }
    }
    else if ($temp_df[3] == "LIFESTYLE") {
      if (exist_lifestyle_item($patient_id, $temp_df[1], $temp_df[2], $dateTarget)) {
        // Record the match
        $isMatch = true;
      }
      else {
       // If this is a required entry then return false
       if ($row['required_flag']) return false;
      }  
    }
    else {

    }
  }

  // return results of check
  return $isMatch;
}

// Function to check lists filters and targets
//  Customizable and currently includes diagnoses, medications,
//    allergies and surgeries.
// Parameters:
//   $patient_id - pid of selected patient.
//   $filter     - array containing lists filter/target elements
//   $dateTarget - target date. blank is current date.
// Return: boolean if check passed, otherwise false
function lists_check($patient_id,$filter,$dateTarget) {
  $isMatch = false; //matching flag

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  foreach ( $filter as $row ) {
    if (exist_lists_item($patient_id, $row['method_detail'], $row['value'], $dateTarget)) {
      // Record the match
      $isMatch = true;
    }
    else {
     // If this is a required entry then return false
     if ($row['required_flag']) return false;
    }
  }

  // return results of check
  return $isMatch;
}

// Function to check for existance of data in database for a patient
// Parameters:
//   $patient_id    - pid of selected patient.
//   $table         - selected mysql table
//   $column        - selected mysql column
//   $data          - selected data in the mysql database
//   $min_items     - mininum number of times the data element is recorded
//   $intervalType  - type of interval (ie. year)
//   $intervalValue - searched for within this many times of the interval type
//   $dateTarget    - target date.
//   $method        - method of database check (EXIST or lt)
// Return: boolean if check passed, otherwise false
function exist_database_item($patient_id,$table,$column,$data,$min_items,$intervalType='',$intervalValue='',$dateTarget='',$method='EXIST') {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect the correct column label for patient id in the table
  $patient_id_label = collect_database_label('pid',$table);

  // Get the interval sql query string
  $dateSql = sql_interval_string($table,$intervalType,$intervalValue,$dateTarget);

  // check for items
  if (empty($column)) {
    // simple search for any table entries
    $sql = sqlStatement("SELECT * " .
      "FROM `" . add_escape_custom($table)  . "` " .
      "WHERE `" . add_escape_custom($patient_id_label)  . "`=?", array($patient_id) );
  }
  else if (empty($data)) {
    // search for number of non blank items
    $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
      "FROM `" . add_escape_custom($table)  . "` " .
      "WHERE `" . add_escape_custom($column) ."`!='' " . 
      "AND `" . add_escape_custom($patient_id_label)  . "`=?", array($patient_id) );
  }
  else {
    if ($method == "EXIST") {
      // search for number of specific items
      $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
        "FROM `" . add_escape_custom($table)  . "` " .
        "WHERE `" . add_escape_custom($column) ."`=? " .
        "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
        $dateSql, array($data,$patient_id) );
    }
    else if ($method == "lt") {
      // search for number of specific items less than $data
      $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
        "FROM `" . add_escape_custom($table)  . "` " .
        "WHERE `" . add_escape_custom($column) ."`<? " .
        "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
        $dateSql, array($data,$patient_id) );
    }
    else if ($method == "le") {
      // search for number of specific items less than or equal to $data
      $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
        "FROM `" . add_escape_custom($table)  . "` " .
        "WHERE `" . add_escape_custom($column) ."`<=? " .
        "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
        $dateSql, array($data,$patient_id) );
    }
    else if ($method == "gt") {
      // search for number of specific items greater than $data
      $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
        "FROM `" . add_escape_custom($table)  . "` " .
        "WHERE `" . add_escape_custom($column) ."`>? " .
        "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
        $dateSql, array($data,$patient_id) );
    }
    else { //$method == "ge"
      // search for number of specific items greater than or equal to $data
      $sql = sqlStatement("SELECT `" . add_escape_custom($column) . "` " .
        "FROM `" . add_escape_custom($table)  . "` " .
        "WHERE `" . add_escape_custom($column) ."`>=? " .
        "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
        $dateSql, array($data,$patient_id) );
    }
  }

  // return whether the mininum number of items exist
  if (sqlNumRows($sql) >= $min_items) {
    return true;
  }
  else {
    return false;
  }
}

// Function to check for existance of data for a patient in the rule_patient_data table
// Parameters:
//   $patient_id    - pid of selected patient.
//   $category      - label in category column
//   $item          - label in item column
//   $complete       - label in complete column
//   $min_items     - mininum number of times the data element is recorded
//   $intervalType  - type of interval (ie. year)
//   $intervalValue - searched for within this many times of the interval type
//   $dateTarget    - target date.
// Return: boolean if check passed, otherwise false
function exist_custom_item($patient_id,$category,$item,$complete,$min_items,$intervalType='',$intervalValue='',$dateTarget) {

  // Set the table
  $table = 'rule_patient_data';

  // Collect the correct column label for patient id in the table
  $patient_id_label = collect_database_label('pid',$table);

  // Get the interval sql query string
  $dateSql = sql_interval_string($table,$intervalType,$intervalValue,$dateTarget);

  // search for number of specific items
  $sql = sqlStatement("SELECT `result` " .
    "FROM `" . add_escape_custom($table)  . "` " .
    "WHERE `category`=? " .
    "AND `item`=? " .
    "AND `complete`=? " .
    "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
    $dateSql, array($category,$item,$complete,$patient_id) );

  // return whether the mininum number of items exist
  if (sqlNumRows($sql) >= $min_items) {
    return true;
  }
  else {
    return false;
  }
}

// Function to check for existance of data for a patient in lifestyle section
// Parameters:
//   $patient_id - pid of selected patient.
//   $lifestyle  - selected label of mysql column of patient history
//   $status     - specific status of selected lifestyle element
//   $dateTarget - target date. blank is current date.
// Return: boolean if check passed, otherwise false
function exist_lifestyle_item($patient_id,$lifestyle,$status,$dateTarget) {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect pertinent history data
  $history = getHistoryData($patient_id, $lifestyle,'',$dateTarget);
 
  // See if match
  $stringFlag = strstr($history[$lifestyle], "|".$status);
  if (empty($status)) {
    // Only ensuring any data has been entered into the field
    $stringFlag = true;
  }
  if ( $history[$lifestyle] &&
       $history[$lifestyle] != '|0|' &&
       $stringFlag ) {
    return true;
  }
  else {
    return false;
  }
}

// Function to check for lists item of a patient
//  Fully customizable and includes diagnoses, medications,
//    allergies, and surgeries.
// Parameters:
//   $patient_id - pid of selected patient.
//   $type  - type (medical_problem, allergy, medication, etc)
//   $value  - value searching for
//   $dateTarget - target date. blank is current date.
// Return: boolean if check passed, otherwise false
function exist_lists_item($patient_id,$type,$value,$dateTarget) {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  if ($type == "medical_problem") {
    // Specific search for diagnoses
    // Explode the value into diagnosis code type and code
    $temp_diag_array = explode("::",$value);
    $code_type = $temp_diag_array[0];
    $diagnosis = $temp_diag_array[1];
    if ($code_type=='CUSTOM') {
      // Deal with custom code first (title column in lists table)
      $response = sqlQuery("SELECT * FROM `lists` " .
        "WHERE `type`=? " .
        "AND `pid`=? " .
        "AND `title`=? " .
        "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
        "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,$diagnosis,$dateTarget,$dateTarget,$dateTarget) );
      if (!empty($response)) return true;
    }
    else {
      // Deal with the set code types (diagnosis column in lists table)
      $response = sqlQuery("SELECT * FROM `lists` " .
        "WHERE `type`=? " .
        "AND `pid`=? " .
        "AND `diagnosis` LIKE ? " .
        "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
        "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,"%".$code_type.":".$diagnosis."%",$dateTarget,$dateTarget,$dateTarget) );
      if (!empty($response)) return true;
    }
  }
  else { // generic lists item that requires no customization
    $response = sqlQuery("SELECT * FROM `lists` " .
      "WHERE `type`=? " .
      "AND `pid`=? " .
      "AND `title`=? ".
      "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
      "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,$value,$dateTarget,$dateTarget,$dateTarget) );
    if (!empty($response)) return true;
  }

  return false;
}

// Function to return part of sql query to deal with interval
// Parameters:
//   $table         - selected mysql table
//   $intervalType  - type of interval (ie. year)
//   $intervalValue - searched for within this many times of the interval type
//   $dateTarget    - target date.
// Return: string containing pertinent date interval filter for mysql query
function sql_interval_string($table,$intervalType,$intervalValue,$dateTarget) {

  $dateSql="";

  // Collect the correct column label for date in the table
  $date_label = collect_database_label('date',$table);

  // Deal with interval
  if (!empty($intervalType)) {
    switch($intervalType) {
      case "year":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " YEAR) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "month":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " MONTH) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "week":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " WEEK) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "day":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " DAY) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "hour":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " HOUR) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "minute":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " MINUTE) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "second":
        $dateSql = "AND (`" . add_escape_custom($date_label) .
          "` BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
          "', INTERVAL " . add_escape_custom($intervalValue) .
          " SECOND) AND '" . add_escape_custom($dateTarget) . "') ";
        break;
      case "flu_season":
        // Flu season to be hard-coded as September thru February
        //  (Should make this modifiable in the future)
        //  ($intervalValue is not used)
        $dateArray = explode("-",$dateTarget);
        $Year = $dateArray[0];
        $dateThisYear = $Year . "-09-01";
        $dateLastYear = ($Year-1) . "-09-01";
        $dateSql =" " .
          "AND ((" .
              "MONTH('" . add_escape_custom($dateTarget) . "') < 9 " .
              "AND `" . add_escape_custom($date_label) . "` >= '" . $dateLastYear . "' ) " .
            "OR (" .
              "MONTH('" . add_escape_custom($dateTarget) . "') >= 9 " .
              "AND `" . add_escape_custom($date_label) . "` >= '" . $dateThisYear . "' ))" .
          "AND `" . add_escape_custom($date_label) . "` <= '" . add_escape_custom($dateTarget) . "' ";
        break;
    }
  }
  else {
    $dateSql = "AND `" . add_escape_custom($date_label) .
      "` <= '" . add_escape_custom($dateTarget)  . "' ";
  }

 // return the sql interval string
 return $dateSql;
}

// Function to collect generic column labels from tables.
//  It currently works for date and pid.
//  Will need to expand this as algorithm grows.
// Parameters:
//   $label - element (pid or date)
//   $table - selected mysql table
// Return: string containing official label of selected element
function collect_database_label($label,$table) {
 
  if ($table == 'immunizations') {
    // return requested label for immunization table
    if ($label == "pid") {
      $returnedLabel = "patient_id";
    }
    else if ($label == "date") {
      $returnedLabel = "administered_date";
    }
    else {
      // unknown label, so return the original label
      $returnedLabel = $label;
    }
  }
  else {
    // return requested label for default tables
    if ($label == "pid") {
      $returnedLabel = "pid";
    }
    else if ($label == "date") {
      $returnedLabel = "date";
    }
    else {
      // unknown label, so return the original label
      $returnedLabel = $label;
    }
  }

  return $returnedLabel;
}

// Simple function to avoid processing of duplicate actions
// Parameters:
//   $actions - 2-dimensional array with all current active targets
//   $action  - array of selected target to test for duplicate
// Return: boolean, true if duplicate, false if not duplicate
function is_duplicate_action($actions,$action) {
  foreach ($actions as $row) {
    if ($row['category'] == $action['category'] &&
        $row['item'] == $action['item'] &&
        $row['value'] == $action['value']) {
      // Is a duplicate
      return true;
    }
  }

  // Not a duplicate
  return false;
}

// Calculate the reminder dates.
// Parameters:
//   $rule       - id(string) of selected rule
//   $dateTarget - target date. If blank then will test with current date as target.
//   $type       - either 'patient_reminder' or 'clinical_reminder'
// For now, will always return an array of 3 dates:
//   first date is before the target date (past_due) (default of 1 month)
//   second date is the target date (due)
//   third date is after the target date (soon_due) (default of 2 weeks)
function calculate_reminder_dates($rule, $dateTarget='',$type) {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect the past_due date
  $past_due_date == "";
  $res = resolve_reminder_sql($rule, $type.'_post');
  if (!empty($res)) {
    $row = $res[0];
    if ($row ['method_detail'] == "week") {
      $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " week"));
    }
    if ($row ['method_detail'] == "month") {
      $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " month"));
    }
  }
  else {
    // empty settings, so use default of one month
    $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -1 month"));
  }

  // Collect the soon_due date
  $soon_due_date == "";
  $res = resolve_reminder_sql($rule, $type.'_pre');
  if (!empty($res)) {
    $row = $res[0];
    if ($row ['method_detail'] == "week") {
      $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +" . $row ['value'] . " week"));
    }
    if ($row ['method_detail'] == "month") {
      $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +" . $row ['value'] . " month"));
    }
  }
  else {
    // empty settings, so use default of one month
    $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +2 week"));
  }

  // Return the array of three dates
  return array($soon_due_date,$dateTarget,$past_due_date);
}

// Adds an action into the reminder array
// Parameters:
//   $reminderOldArray - Contains the current array of reminders
//   $reminderNew      - Array of a new reminder
// Return:
//   An array of reminders
function reminder_results_integrate($reminderOldArray, $reminderNew) {

  $results = array();

  // If reminderArray is empty, then insert new reminder
  if (empty($reminderOldArray)) {
    array_push($results, $reminderNew);
    return $results;
  }

  // If duplicate reminder, then replace the old one
  $duplicate = false;
  foreach ($reminderOldArray as $reminderOld) {
    if (  $reminderOld['pid'] == $reminderNew['pid'] &&
          $reminderOld['category'] == $reminderNew['category'] &&
          $reminderOld['item'] == $reminderNew['item']) {
      array_push($results, $reminderNew);
      $duplicate = true;
    }
    else {
      array_push($results, $reminderOld);
    }
  }

  // If a new reminder, then insert the new reminder
  if (!$duplicate) {
    array_push($results, $reminderNew);
  }

  return $results;
}

// Function to find age in years (with decimal) on the target date
// Parameters:
//   $dob        - date of birth
//   $target     - date to calculate age on
// Return: decimal, years(decimal) from dob to target(date)
function convertDobtoAgeYearDecimal($dob,$target) { 

    // Grab year, month, and day from dob and dateTarget
    $dateDOB = explode(" ",$dob);
    $dateTarget = explode(" ",$target);

    // Collect differences 
    $iDiffYear  = $dateTarget[0] - $dateDOB[0]; 
    $iDiffMonth = $dateTarget[1] - $dateDOB[1]; 
    $iDiffDay   = $dateTarget[2] - $dateDOB[2]; 
     
    // If birthday has not happen yet for this year, subtract 1. 
    if ($iDiffMonth < 0 || ($iDiffMonth == 0 && $iDiffDay < 0)) 
    { 
        $iDiffYear--; 
    } 
         
    return $iDiffYear; 
}  

// Function to find age in months (with decimal) on the target date
// Parameters:
//   $dob        - date of birth
//   $target     - date to calculate age on
// Return: decimal, months(decimal) from dob to target(date)
function convertDobtoAgeMonthDecimal($dob,$target) {

    // Grab year, month, and day from dob and dateTarget
    $dateDOB = explode(" ",$dob);
    $dateTarget = explode(" ",$target);

    // Collect differences
    $iDiffYear  = $dateTarget[0] - $dateDOB[0];
    $iDiffMonth = $dateTarget[1] - $dateDOB[1];
    $iDiffDay   = $dateTarget[2] - $dateDOB[2];

    // If birthday has not happen yet for this year, subtract 1.
    if ($iDiffMonth < 0 || ($iDiffMonth == 0 && $iDiffDay < 0))
    {
        $iDiffYear--;
    }

    return (12 * $iDiffYear) + $iDiffMonth;
}

?>

