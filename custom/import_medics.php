<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

/////////////////////////////////////////////////////////////////////
// This script imports encounters from a custom XML format.  It
// implements a form, and the processing of that form.
//
// When initially presented to the user, the form contains just a file
// upload field and its associated Browse button, and an OK button.
//
// Selecting a file to upload and clicking OK will upload the file,
// and redisplay a superset of the initial form which contains one or
// more uploaded encounters.  Each encounter uploaded will appear in
// its own section of the form, and each such section will display the
// imported data for its encounter.  Some of the displayed data items
// will be editable at this time.  In addition some error messages
// might appear indicating that some fields must be corrected.
//
// Once the user is satisfied with the contents of the form, clicking
// OK again will cause the data items to be recorded into the
// appropriate database areas of OpenEMR.  In addition if a new file
// to upload was also selected, then the form will redisplay as before
// but for the new file.
/////////////////////////////////////////////////////////////////////

include_once("../interface/globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/acl.inc");

$x = array(
  'patient'    => array('Patient', array(
    'pid'        => array('Patient ID'    , '2'),
    'lname'      => array('Last Name'     , '2'),
    'mname'      => array('Middle Name'   , '2'),
    'fname'      => array('First Name'    , '2'),
    'sex'        => array('Sex'           , '2'),
    'dob'        => array('Birth Date'    , '2'),
    'ethnicity'  => array('Ethnicity'     , '2'),
    'street'     => array('Street Address', '2'),
    'street2'    => array('Street Address', '2'),
    'city'       => array('City'          , '2'),
    'state'      => array('State'         , '2'),
    'zip'        => array('Zip Code'      , '2'),
    'phone_home' => array('Home Phone'    , '2'),
    'phone_alternate' => array('Other Phone', '2'),
    'pcp'        => array('Primary Physician', array(
      'id'         => array('Billing ID', '2'),
      'lname'      => array('Last Name'   , '2'),
      'fname'      => array('First Name'  , '2'),
    )), // end pcp
    'xcp'        => array('Rendering Provider', array(
      'id'         => array('Billing ID', '2'),
      'lname'      => array('Last Name' , '2'),
      'fname'      => array('First Name', '2'),
      'notes'      => array('Notes'     , '2'),
    )), // end xcp
    'familyinformation'   => array('Family Information', array(
      'father'              => array('Father'   , '2'),
      'mother'              => array('Mother'   , '2'),
      'spouse'              => array('Spouse'   , '2'),
      'siblings'            => array('Siblings' , '2'),
      'offspring'           => array('Offspring', '2'),
    )), // end familyinformation
    'medical'             => array('Medical Information', array(
      'relativesexperience' => array('Relatives Experience', array(
        'cancer'              => array('Cancer'        , '2'),
        'tuberculosis'        => array('Tuberculosis'  , '2'),
        'diabetes'            => array('Diabetes'      , '2'),
        'highbloodpressure'   => array('Hypertension'  , '2'),
        'heartproblems'       => array('Heart Problems', '2'),
        'stroke'              => array('Stroke'        , '2'),
        'epilepsy'            => array('Epilepsy'      , '2'),
        'mentalillness'       => array('Mental Illness', '2'),
        'suicide'             => array('Suicide'       , '2'),
      )),
      'lifestyleusage'      => array('Lifestyle/Usage', array(
        'coffee'              => array('Coffee'              , '2'),
        'tobacco'             => array('Tobacco'             , '2'),
        'alcohol'             => array('Alcohol'             , '2'),
        'sleep'               => array('Sleep'               , '2'),
        'exercise'            => array('Exercise'            , '2'),
        'seatbelt'            => array('Seat Belt'           , '2'),
        'counseling'          => array('Counseling'          , '2'),
        'hazardactivities'    => array('Hazardous Activities', '2'),
      )),
      'medications'         => array('Medications', array(
        'medication*'         => array('Medication', array(
          'name'                => array('Name'     , '2'),
          'dosage'              => array('Dosage'   , '2'),
          'frequency'           => array('Frequency', '2'),
          'duration'            => array('Duration' , '2'),
        )),
      )),
      'medicalhistory'      => array('Medical History', array(
        'medicationnotes'     => array('Medication Notes', '2'),
        'allergies'           => array('Allergies'       , '2'),
        'history'             => array('History'         , '2'),
        'surgicalhistory'     => array('Surgical History', '2'),
      )),
      'preventatives'       => array('Preventatives', array(
        'breastexamination'   => array('Breast Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'cardiacecho'         => array('Cardiac Echo', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'ecg'                 => array('ECG', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'gyn'                 => array('Gyn Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'mammogram'           => array('Mammogram', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'physicalexam'        => array('Physical Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'prostateexam'        => array('Prostate Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'rectalexam'          => array('Rectal Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'sigmoid'             => array('Sigmoid', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'retinal'             => array('Retinal Exam', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'fluvax'              => array('Flu Vaccination', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'pneuvax'             => array('Pnuemonia Vaccination', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'ldl'                 => array('LDL', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'hemoglobin'          => array('Hemoglobin', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'psa'                 => array('PSA', array(
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
        'other'               => array('Other', array(
          'name'                => array('Name'  , '2'),
          'date'                => array('Date'  , '2'),
          'result'              => array('Result', '2'),
        )),
      )), // end preventatives
      'subjective'          => array('Subjective', array(
        'general'             => array('General'     , '2'),
        'neurological'        => array('Neurological', '2'),
        'heent'               => array('HEENT'       , '2'),
        'respiratory'         => array('Respiratory' , '2'),
        'cardio'              => array('Cardio'      , '2'),
        'gastro'              => array('Gastro'      , '2'),
        'skin'                => array('Skin'        , '2'),
        'extremities'         => array('Extremities' , '2'),
      )), // end subjective
      'physicalexamsvitals' => array('Physical Exams/Vitals', array(
        'mentalstatus'        => array('Mental Status', '2'),
        'bps'                 => array('BPS'          , '2'),
        'bpd'                 => array('BPD'          , '2'),
        'weight'              => array('Weight'       , '2'),
        'height'              => array('Height'       , '2'),
        'temperature'         => array('Temperature'  , '2'),
        'tempmethods'         => array('Temp Method'  , '2'),
        'pulse'               => array('Pulse'        , '2'),
        'respiration'         => array('Respiration'  , '2'),
        'bmi'                 => array('BMI'          , '2'),
        'waistcirc'           => array('Waist Circ'   , '2'),
        'headcirc'            => array('Head Circ'    , '2'),
        'o2'                  => array('Oxygen'       , '2'),
      )), // end physicalexamsvitals
      'systems'             => array('Systems', array(
        'assessment'          => array('Assessment'    , '2'),
        'treatmentplan'       => array('Treatment Plan', '2'),
        'diagnosis*'          => array('Diagnosis', array(
          'code'                => array('Diagnosis Code', '2'),
          'codestatus'          => array('Code Status'   , '2'),
          'codenote'            => array('Code Note'     , '2'),
        )),
      )), // end systems
      'billable'            => array('Billing', array(
        'fromdate'            => array('From Date', '2'),
        'thrudate'            => array('Thru Date', '2'),
        'notes'               => array('Notes', '2'),
        'service*'            => array('Service', array(
          'code'                => array('Service Code', '2'),
          'codenote'            => array('Code Note'   , '2'),
        )),
        'clinic'              => array('Clinic', array(
          'id'                  => array('Billing ID'    , '2'),
          'name'                => array('Name'          , '2'),
          'street'              => array('Street Address', '2'),
          'street2'             => array('Street Address', '2'),
          'city'                => array('City'          , '2'),
          'state'               => array('State'         , '2'),
          'zip'                 => array('Zip Code'      , '2'),
          'phone'               => array('Phone'         , '2'),
        )),
      )), // end billable
    )), // end medical
  )), // end patient
);  // end $x

// This recursively initializes the array that will hold imported data values
// for one patient.
//
function init_patient(&$sarr, &$darr) {
  foreach ($sarr as $key => $value) {
    if (is_array($value[1])) {
      // Tags ending in * are those that are repeated.  They will be
      // inserted elsewhere.
      if (substr($key, -1) == '*') continue;
      $darr[$key] = array();
      init_patient($value[1], $darr[$key]);
    }
  }
}

$ptsequence = 0;
$probearr = array();

// This recursively writes the tree of HTML data for one patient.
// $sarr contains logical keys, verbose names and field types.
// $darr contains physical keys and data values.
// $probearr maintains the heirarchy of physical key names.
//
function write_html(&$sarr, &$darr) {
  global $probearr, $ptsequence;

  // echo '<p>$darr = '; print_r($darr); // debugging

  foreach ($darr as $dkey => $dvalue) {
    if (!is_array($dvalue)) {
      // Write leaf name and value.  We are copying our same physical
      // keys into the HTML field names so that PHP will parse them back
      // into the same familiar format.
      echo "<li>" . $sarr[$dkey][0] . ": ";
      echo "<input type='text' name='pt$ptsequence";
      foreach ($probearr as $pvalue) echo "[$pvalue]";
      echo "[$dkey]' class='indata' value='" . $dvalue . "'></li>\n";
    }
  } // end foreach

  foreach ($darr as $dkey => $dvalue) {
    if (is_array($dvalue)) {
      $array_depth = array_push($probearr, $dkey);
      $skey = $dkey;
      $i = strpos($skey, '*');
      if ($i) {
        // If the physical key has an asterisk then the logical key is only
        // the part up to and including the asterisk.
        $skey = substr($skey, 0, $i+1);
      }
      // Write the verbose name corresponding to this logical key.
      if ($array_depth > 1) echo "<li>" . $sarr[$skey][0] . "</li>\n";
      // Recursively process this array's contents.
      echo "<ul>\n";
      write_html($sarr[$skey][1], $dvalue);
      echo "</ul>";
      array_pop($probearr);
    }
  } // end foreach
}

// Encode a string from a form field for database writing.
//
function form2db($fldval) {
 $fldval = trim($fldval);
 if (!get_magic_quotes_gpc()) $fldval = addslashes($fldval);
 return $fldval;
}

// Encode sex for OpenEMR compatibility.
//
function sex($insex) {
  if (!empty($insex)) {
    $insex = strtoupper(substr($insex, 0, 1));
    if ($insex == 'M') return 'Male';
    if ($insex == 'F') return 'Female';
  }
  return '';
}

// Compute the digit corresponding to a test/exam result.
// 0 = unassigned, 1 = normal, 2 = abnormal.
//
function exam_result(&$pta, $key) {
  if (!empty($pta['medical']['preventatives'][$key])) {
    if (trim($pta['medical']['preventatives'][$key]['date'])) {
      $res = strtoupper(trim($pta['medical']['preventatives'][$key]['result']));
      if (strpos($res, 'ABN'   ) !== false) return '2';
      if (strpos($res, 'WNL'   ) !== false) return '1';
      if (strpos($res, 'NORMAL') !== false) return '1';
      return '2';
    }
  }
  return '0';
}

// Create a new issue in the lists table.
//
function create_issue($pid, $type, $title) {
  sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, user, groupname " .
    ") VALUES ( " .
    "NOW(), "     .
    "'$pid', "    .
    "'$type', "   .
    "'$title', "  .
    "1, "         .
    "'" . $$_SESSION['authUser']     . "', " .
    "'" . $$_SESSION['authProvider'] . "' "  .
   ")");
}

// Write a row to the billing table.
//
function add_billing($pid, $encounter, $provider, $codetype, $code,
  $description, $justify='', $modifier='', $units=1)
{
  global $insurance_company_id;

  // Get the fee from the codes table.
  $fee = 0;
  if ($codetype == 'CPT4') {
    $query = "SELECT fee FROM codes WHERE code_type = 1 AND code = '$code' AND ";
    if (empty($modifier))
      $query .= "( modifier IS NULL OR modifier = '')";
    else
      $query .= "modifier = '$modifier'";
    $row = sqlQuery($query);
    if ($row['fee']) $fee = $row['fee'] * $units;
  }

  sqlInsert("INSERT INTO billing ( " .
    "date, code_type, code, pid, provider_id, user, groupname, authorized, " .
    "encounter, code_text, billed, activity, payer_id, modifier, units, " .
    "fee, justify " .
    ") VALUES ( "   .
    "NOW(), "       .
    "'$codetype', " .
    "'$code', "     .
    "'$pid', "      .
    "'$provider', " .
    "'" . $$_SESSION['authUser']     . "', " .
    "'" . $$_SESSION['authProvider'] . "', " .
    "1, "                       .
    "'$encounter', "            .
    "'$description', "          .
    "0, "                       .
    "1, "                       .
    "'$insurance_company_id', " .
    "'$modifier', "             .
    "'$units', "                .
    "'$fee', "                  .
    "'$justify'"                .
   ")");
}

// Check permission to run this.  We might want more here.
//
$thisauth = acl_check('patients', 'demo');
if ($thisauth != 'write')
  die("Updating demographics is not authorized.");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<style type="text/css">
 body {
  font-family:sans-serif;
 }
.indata {
 font-family:monospace;
 font-size:11pt;
 font-weight:normal;
 color:#ff0000;
 border-style:solid;
 border-top-width:0px;
 border-bottom-width:0px;
 border-left-width:0px;
 border-right-width:0px;
 border-color: #aaaaaa;
 background-color:transparent;
}

</style>

<title>Import Encounters</title>
</head>
<body <? echo $top_bg_line ?>>
<form method='post' action='import_medics.php' enctype='multipart/form-data'>
<?php

// echo '<p>$_POST = '; print_r($_POST); // debugging

// Should be just one insurance company, assume the first one we find is it.
//
$row = sqlQuery("SELECT id FROM insurance_companies ORDER BY id LIMIT 1");
$insurance_company_id = $row['id'];
if (!$insurance_company_id) die('No insurance company found!');

// For each set of POSTed patient data, store it to the database.
//
while (!empty($_POST['pt' . ++$ptsequence])) {
  $pta = &$_POST["pt$ptsequence"]['patient'];

  // echo '<p>$pta = '; print_r($pta); // debugging

  // Check if $pta[pid] matches any pubpid.  If so, we will skip everything
  // except the encounter.
  //
  $pubpid = $pta['pid'];
  if (empty($pubpid)) {
    $alertmsg .= "Patient ID missing, patient skipped! ";
    continue;
  }
  $patient_pid = 0;
  $query = "SELECT pid FROM patient_data WHERE pubpid LIKE '$pubpid'";
  $res = sqlStatement($query);
  $row = sqlFetchArray($res);
  if ($row) {
    $patient_pid = $row['pid'];
    if (sqlFetchArray($res)) {
      $alertmsg .= "Patient ID \\'$pubpid\\' is ambiguous, patient skipped! ";
      continue;
    } else {
      $alertmsg .= "Patient ID \\'$pubpid\\' already exists, will create encounter only. ";
    }
  }

  // Find xcp (rendering provider), failure is an error.
  // $pta[xcp][id] matches up with the insurance number.
  //
  $tmp = $pta['xcp']['id'];
  /****
  $query = "SELECT n.provider_id, u.username " .
    "FROM insurance_numbers AS n, users AS u WHERE " .
    "n.provider_number LIKE '$tmp' AND " .
    "n.insurance_company_id IS NOT NULL AND " .
    "n.insurance_company_id = $insurance_company_id AND " .
    "u.id = n.provider_id";
  $row = sqlQuery($query);
  if (!$row['provider_id']) {
    $alertmsg .= "Provider \\'$tmp\\' not found, patient skipped! ";
    continue;
  }
  $patient_provider_id = $row['provider_id'];
  $patient_provider_name = $row['username'];
  ****/
  $query = "SELECT id, username FROM users WHERE npi like '$tmp' LIMIT 1";
  $row = sqlQuery($query);
  if (!$row['id']) {
    $alertmsg .= "Provider \\'$tmp\\' not found, patient skipped! ";
    continue;
  }
  $patient_provider_id = $row['id'];
  $patient_provider_name = $row['username'];

  // Find facility, failure is an error.
  // $pta[medical][billable][clinic][id] matches up with ... npi?
  //
  $tmp = $pta['medical']['billable']['clinic']['id'];
  $row = sqlQuery("SELECT id, name FROM facility WHERE " .
    "facility_npi LIKE '$tmp'");
  if (!$row['id']) {
    $alertmsg .= "Facility \\'$tmp\\' not found, patient skipped! ";
    continue;
  }
  $patient_facility_name = $row['name'];

  if (!$patient_pid) {

    // Insert into patient_data.
    //
    $row = sqlQuery("SELECT max(pid)+1 AS pid FROM patient_data");
    $patient_pid = $row['pid'] ? $row['pid'] : 1;
    //
    newPatientData(
      '',                           // id
      '',                           // title
      form2db($pta['fname']),       // fname
      form2db($pta['lname']),       // lname
      form2db($pta['mname']),       // mname
      sex($pta['sex']),             // sex
      form2db($pta['dob']),         // dob
      form2db($pta['street']),      // street
      form2db($pta['zip']),         // zip
      form2db($pta['city']),        // city
      form2db($pta['state']),       // state
      '',                           // country
      form2db($pta['ssn']),         // ss
      '',                           // occupation
      form2db($pta['phone_home']),  // phone_home
      form2db($pta['phone_alternate']), // phone_biz
      '',                           // phone_contact
      '',                           // status
      '',                           // contact_relationship
      form2db($pta['pcp']['id'] . ',' . $pta['pcp']['lname'] . ',' . $pta['pcp']['fname']), // referrer
      '',                           // referrerID
      '',                           // email
      '',                           // language
      form2db($pta['ethnicity']),   // ethnoracial
      '',                           // interpreter
      '',                           // migrantseasonal
      '',                           // family_size
      '',                           // monthly_income
      '',                           // homeless
      '0000-00-00 00:00:00',        // financial_review
      $pubpid,                      // pubpid
      $patient_pid,                 // pid
      '',                           // providerID
      '',                           // genericname1
      '',                           // genericval1
      '',                           // genericname2
      '',                           // genericval2
      '',                           // phone_cell
      '',                           // hipaa_mail
      '',                           // hipaa_voice
      ''                            // squad
    );

    // Insert dummy row for employer_data.
    newEmployerData($patient_pid);

    // Encode exam results as needed for history_data.
    $last_exam_results =
      exam_result($pta, 'breastexamination') .
      exam_result($pta, 'mammogram'        ) .
      exam_result($pta, 'gyn'              ) .
      exam_result($pta, 'rectalexam'       ) .
      exam_result($pta, 'prostateexam'     ) .
      exam_result($pta, 'physicalexam'     ) .
      exam_result($pta, 'sigmoid'          ) .
      exam_result($pta, 'ecg'              ) .
      exam_result($pta, 'cardiacecho'      ) .
      exam_result($pta, 'retinal'          ) .
      exam_result($pta, 'fluvax'           ) .
      exam_result($pta, 'pneuvax'          ) .
      exam_result($pta, 'ldl'              ) .
      exam_result($pta, 'hemoglobin'       ) .
      exam_result($pta, 'psa'              ) .
      '0';

    // Insert into history_data.
    newHistoryData($patient_pid, array(
      'history_father'                => form2db($pta['familyinformation']['father']),
      'history_mother'                => form2db($pta['familyinformation']['mother']),
      'history_spouse'                => form2db($pta['familyinformation']['spouse']),
      'history_siblings'              => form2db($pta['familyinformation']['siblings']),
      'history_offspring'             => form2db($pta['familyinformation']['offspring']),
      'relatives_cancer'              => form2db($pta['medical']['relativesexperience']['cancer']),
      'relatives_tuberculosis'        => form2db($pta['medical']['relativesexperience']['tuberculosis']),
      'relatives_diabetes'            => form2db($pta['medical']['relativesexperience']['diabetes']),
      'relatives_high_blood_pressure' => form2db($pta['medical']['relativesexperience']['highbloodpressure']),
      'relatives_heart_problems'      => form2db($pta['medical']['relativesexperience']['heartproblems']),
      'relatives_stroke'              => form2db($pta['medical']['relativesexperience']['stroke']),
      'relatives_epilepsy'            => form2db($pta['medical']['relativesexperience']['epilepsy']),
      'relatives_mental_illness'      => form2db($pta['medical']['relativesexperience']['mentalillness']),
      'relatives_suicide'             => form2db($pta['medical']['relativesexperience']['suicide']),
      'coffee'                        => form2db($pta['medical']['lifestyleusage']['coffee']),
      'tobacco'                       => form2db($pta['medical']['lifestyleusage']['tobacco']),
      'alcohol'                       => form2db($pta['medical']['lifestyleusage']['alcohol']),
      'sleep_patterns'                => form2db($pta['medical']['lifestyleusage']['sleep']),
      'exercise_patterns'             => form2db($pta['medical']['lifestyleusage']['exercise']),
      'seatbelt_use'                  => form2db($pta['medical']['lifestyleusage']['seatbelt']),
      'counseling'                    => form2db($pta['medical']['lifestyleusage']['counseling']),
      'hazardous_activities'          => form2db($pta['medical']['lifestyleusage']['hazardactivities']),
      'last_breast_exam'              => form2db($pta['medical']['preventatives']['breastexamination']['date']),
      'last_cardiac_echo'             => form2db($pta['medical']['preventatives']['cardiacecho']['date']),
      'last_ecg'                      => form2db($pta['medical']['preventatives']['ecg']['date']),
      'last_gynocological_exam'       => form2db($pta['medical']['preventatives']['gyn']['date']),
      'last_mammogram'                => form2db($pta['medical']['preventatives']['mammogram']['date']),
      'last_physical_exam'            => form2db($pta['medical']['preventatives']['physicalexam']['date']),
      'last_prostate_exam'            => form2db($pta['medical']['preventatives']['prostateexam']['date']),
      'last_rectal_exam'              => form2db($pta['medical']['preventatives']['rectalexam']['date']),
      'last_sigmoidoscopy_colonoscopy'=> form2db($pta['medical']['preventatives']['sigmoid']['date']),
      'last_retinal'                  => form2db($pta['medical']['preventatives']['retinal']['date']),
      'last_fluvax'                   => form2db($pta['medical']['preventatives']['fluvax']['date']),
      'last_pneuvax'                  => form2db($pta['medical']['preventatives']['pneuvax']['date']),
      'last_ldl'                      => form2db($pta['medical']['preventatives']['ldl']['date']),
      'last_hemoglobin'               => form2db($pta['medical']['preventatives']['hemoglobin']['date']),
      'last_psa'                      => form2db($pta['medical']['preventatives']['psa']['date']),
      'name_1'                        => form2db($pta['medical']['preventatives']['other']['name']),
      'value_1'                       => form2db($pta['medical']['preventatives']['other']['date']) . ':' .
                                         form2db($pta['medical']['preventatives']['other']['result']),
      'last_exam_results'             => $last_exam_results,
    ));

    newInsuranceData(
      $patient_pid,
      'primary',
      $insurance_company_id,        // (insurance) provider
      $pubpid,                      // policy_number - same as pt identifier?
      '',                           // group_number - anything special here?
      '',                           // plan_name - anything special here?
      form2db($pta['lname']),       // subscriber_lname
      form2db($pta['mname']),       // subscriber_mname
      form2db($pta['fname']),       // subscriber_fname
      'self',                       // subscriber_relationship
      form2db($pta['ssn']),         // subscriber_ss
      fixDate($pta['dob']),         // subscriber_DOB
      form2db($pta['street']),      // subscriber_street
      form2db($pta['zip']),         // subscriber_postal_code
      form2db($pta['city']),        // subscriber_city
      form2db($pta['state']),       // subscriber_state
      '',                           // subscriber_country
      form2db($pta['phone_home']),  // subscriber_phone
      '',                           // subscriber_employer
      '',                           // subscriber_employer_street
      '',                           // subscriber_employer_city
      '',                           // subscriber_employer_postal_code
      '',                           // subscriber_employer_state
      '',                           // subscriber_employer_country
      '',                           // copay
      sex($pta['sex'])              // subscriber_sex
    );
    newInsuranceData($patient_pid, 'secondary');
    newInsuranceData($patient_pid, 'tertiary');

    // Create an issue for each medication.  Cram details into the title.
    //
    if (!empty($pta['medical']['medications'])) {
      foreach ($pta['medical']['medications'] as $key => $value) {
        if (empty($value['name'])) continue;
        create_issue($patient_pid, 'medication',
          form2db($value['name']     ) . '/' .
          form2db($value['dosage']   ) . '/' .
          form2db($value['frequency']) . '/' .
          form2db($value['duration'] )
        );
      }
    }

    if (!empty($pta['medical']['medicalhistory']['medicationnotes'])) {
      create_issue($patient_pid, 'medication',
        form2db($pta['medical']['medicalhistory']['medicationnotes']));
    }

    if (!empty($pta['medical']['medicalhistory']['allergies'])) {
      create_issue($patient_pid, 'allergy',
        form2db($pta['medical']['medicalhistory']['allergies']));
    }

    if (!empty($pta['medical']['medicalhistory']['history'])) {
      create_issue($patient_pid, 'medical_problem',
        form2db($pta['medical']['medicalhistory']['history']));
    }

    if (!empty($pta['medical']['medicalhistory']['surgicalhistory'])) {
      create_issue($patient_pid, 'surgery',
        form2db($pta['medical']['medicalhistory']['surgicalhistory']));
    }
  } // end if ($patient_is_new)

  // TBD: Check encounter DOS.  If it already exists, generate error message
  // and continue (this can happen if they rerun an input file).

  // TBD: Create new encounter.
  $dos = fixDate($pta['medical']['billable']['fromdate']);
  $encounter_id = $GLOBALS['adodb']['db']->GenID('sequences');
  $encounter_reason = form2db($pta['medical']['billable']['notes']);
  addForm($encounter_id, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET " .
      "date = '$dos', " .
      "onset_date = '$dos', " .
      "reason = '$encounter_reason', " .
      "facility = '$patient_facility_name', " .
      "sensitivity = 'normal', " .
      "pid = '$patient_pid', " .
      "encounter = '$encounter_id'"
    ),
    "newpatient", $patient_pid, 1, $dos
  );

  // Create SOAP2 form.
  addForm($encounter_id, "SOAP",
    sqlInsert("INSERT INTO form_soap2 SET " .
      "date = '$dos', "        .
      "pid = '$patient_pid', " .
      "authorized = 1, "       .
      "activity = 1, "         .
      "general = '"      . form2db($pta['medical']['subjective']['general'])               . "', " .
      "neurological = '" . form2db($pta['medical']['subjective']['neurological'])          . "', " .
      "heent = '"        . form2db($pta['medical']['subjective']['heent'])                 . "', " .
      "respiratory = '"  . form2db($pta['medical']['subjective']['respiratory'])           . "', " .
      "cardio = '"       . form2db($pta['medical']['subjective']['cardio'])                . "', " .
      "gastro = '"       . form2db($pta['medical']['subjective']['gastro'])                . "', " .
      "skin = '"         . form2db($pta['medical']['subjective']['skin'])                  . "', " .
      "extremities = '"  . form2db($pta['medical']['subjective']['extremities'])           . "', " .
      "mentalstatus = '" . form2db($pta['medical']['physicalexamsvitals']['mentalstatus']) . "', " .
      "assessment = '"   . form2db($pta['medical']['systems']['assessment'])               . "', " .
      "plan = '"         . form2db($pta['medical']['systems']['treatmentplan'])            . "'"
    ),
    "soap2", $patient_pid, 1, $dos
  );

  // Create Vitals form.
  if (!empty($pta['medical']['physicalexamsvitals'])) {
    addForm($encounter_id, "Vitals",
      sqlInsert("INSERT INTO form_vitals SET " .
        "date = '$dos', "        .
        "pid = '$patient_pid', " .
        "authorized = 1, "       .
        "activity = 1, "         .
        "bps = '"               . form2db($pta['medical']['physicalexamsvitals']['bps'])         . "', " .
        "bpd = '"               . form2db($pta['medical']['physicalexamsvitals']['bpd'])         . "', " .
        "weight = '"            . form2db($pta['medical']['physicalexamsvitals']['weight'])      . "', " .
        "height = '"            . form2db($pta['medical']['physicalexamsvitals']['height'])      . "', " .
        "temperature = '"       . form2db($pta['medical']['physicalexamsvitals']['temperature']) . "', " .
        "temp_method = '"       . form2db($pta['medical']['physicalexamsvitals']['tempmethods']) . "', " .
        "pulse = '"             . form2db($pta['medical']['physicalexamsvitals']['pulse'])       . "', " .
        "respiration = '"       . form2db($pta['medical']['physicalexamsvitals']['respiration']) . "', " .
        "BMI = '"               . form2db($pta['medical']['physicalexamsvitals']['bmi'])         . "', " .
        "waist_circ = '"        . form2db($pta['medical']['physicalexamsvitals']['waistcirc'])   . "', " .
        "head_circ = '"         . form2db($pta['medical']['physicalexamsvitals']['headcirc'])    . "', " .
        "oxygen_saturation = '" . form2db($pta['medical']['physicalexamsvitals']['o2'])          . "'"
      ),
      "vitals", $patient_pid, 1, $dos
    );
  }

  $diags = array();
  if (!empty($pta['medical']['systems'])) {
    foreach ($pta['medical']['systems'] as $key => $value) {
      if (strpos($key, 'diagnosis*') === 0) {
        if (empty($value['code'])) continue;
        $diags[] = $value['code'];
        add_billing($patient_pid, $encounter_id, $patient_provider_id,
          'ICD9', $value['code'],
          $value['codenote'] . ' (' . $value['codestatus'] . ')');
      }
    }
  }

  if (!empty($pta['medical']['billable'])) {
    $i = 0;
    foreach ($pta['medical']['billable'] as $key => $value) {
      if (strpos($key, 'service*') === 0) {
        if (empty($value['code'])) continue;
        add_billing($patient_pid, $encounter_id, $patient_provider_id,
          'CPT4', $value['code'], $value['codenote'], $diags[$i++] . ':');
      }
    }
  }

}

$ptsequence = 0;

// echo "<p>Upload file size = " . $_FILES['form_xmlfile']['size'] . "</p>\n"; // debugging

if ($_FILES['form_xmlfile']['size']) {
  $tmp_name = $_FILES['form_xmlfile']['tmp_name'];

  // Handle .zip extension if present.  Probably won't work on Windows.
  if (strtolower(substr($_FILES['form_xmlfile']['name'], -4)) == '.zip') {
    rename($tmp_name, "$tmp_name.zip");
    exec("unzip -p $tmp_name.zip > $tmp_name");
    unlink("$tmp_name.zip");
  }

  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  $xml = array();

  $indata = trim(file_get_contents($tmp_name));
  if (strpos($indata, '<?xml') === 0) {
    $indata = substr($indata, strpos($indata, '?>') + 2);
  }

  if (xml_parse_into_struct($parser, '<stuff>' . $indata . '</stuff>', $xml)) {
    // echo '<p>$xml = '; print_r($xml); // debugging

    $a = false;

    foreach ($xml as $taginfo) {
      $tag      = strtolower($taginfo['tag']);
      $tagtype  = $taginfo['type'];
      $taglevel = $taginfo['level'] - 2;
      $tagval   = addslashes($taginfo['value']);

      if ($taglevel < 0) continue; // ignoring the top level

      if ($tagtype == 'open') {
        if ($taglevel == 0) {
          if ($ptsequence) {
            $probearr = array();
            write_html($x, $a);
          }
          ++$ptsequence;
          $probearr = array();
          $a = array();
          $medication_index = 0;
          $diagnosis_index = 0;
          $service_index = 0;
          init_patient($x, $a);
        }
        else if ($taglevel == 3 && $tag == 'medication') {
          $tag .= '*' . $medication_index;
          $a['patient']['medical']['medications'][$tag] = array();
          ++$medication_index;
        }
        else if ($taglevel == 3 && $tag == 'diagnosis') {
          $tag .= '*' . $diagnosis_index;
          $a['patient']['medical']['systems'][$tag] = array();
          ++$diagnosis_index;
        }
        else if ($taglevel == 3 && $tag == 'service') {
          $tag .= '*' . $service_index;
          $a['patient']['medical']['billable'][$tag] = array();
          ++$service_index;
        }
        $probearr[$taglevel] = $tag;
        continue;
      }
      if ($tagtype == 'close') {
        continue;
      }
      if ($tagtype != 'complete') die("Unhandled tag type '$tagtype'");

      // Create key/value pair for this leaf item.
      // Note that init_patient() already created the branch nodes.
      $aref = &$a;
      for ($i = 0; $i < $taglevel; ++$i) $aref = &$aref[$probearr[$i]];
      $aref[$tag] = $tagval;
    } // end foreach

    if ($ptsequence) {
      $probearr = array();
      write_html($x, $a);
    }
  }
  else {
    $alertmsg = "Invalid import data!";
  }
  xml_parser_free($parser);
  unlink($tmp_name);
}
?>

<center>
<?php xl('Upload import file:','e') ?>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<input name="form_xmlfile" type="file" />
&nbsp; &nbsp; &nbsp;
<input type='submit' name='form_import' value='Save and/or Upload' /> &nbsp;
</center>
</form>
<?php
if ($alertmsg) {
  echo "<script language='JavaScript'>\n";
  echo " alert('$alertmsg');\n";
  echo "</script>\n";
}
?>
</body>
</html>
