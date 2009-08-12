<?php
// Copyright (C) 2008-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
include_once("$srcdir/patient.inc");

$template_file = "$webserver_root/custom/referral_template.html";

$TEMPLATE_LABELS = array(
  'label_clinic_id'             => xl('Clinic ID'),
  'label_control_no'            => xl('Control No.'),
  'label_date'                  => xl('Date'),
  'label_webpage_title'         => xl('Referral Form'),
  'label_form1_title'           => xl('REFERRAL FORM'),
  'label_name'                  => xl('Name'),
  'label_age'                   => xl('Age'),
  'label_gender'                => xl('Gender'),
  'label_address'               => xl('Address'),
  'label_postal'                => xl('Postal'),
  'label_phone'                 => xl('Phone'),
  'label_ref_reason'            => xl('Reference Reason'),
  'label_diagnosis'             => xl('Diagnosis'),
  'label_ref_class'             => xl('Reference classification (risk level)'),
  'label_dr_name_sig'           => xl('Doctor\'s name and signature'),
  'label_refer_to'              => xl('Referred to'),
  'label_clinic'                => xl('Health centre/clinic'),
  'label_history_summary'       => xl('Client medical history summary'),
  'label_bp'                    => xl('Blood pressure'),
  'label_ht'                    => xl('Height'),
  'label_wt'                    => xl('Weight'),
  'label_ref_name_sig'          => xl('Referer name and signature'),
  'label_special_name_sig'      => xl('Specialist name and signature'),
  'label_form2_title'           => xl('COUNTER REFERRAL FORM'),
  'label_findings'              => xl('Findings'),
  'label_final_diagnosis'       => xl('Final Diagnosis'),
  'label_services_provided'     => xl('Services provided'),
  'label_recommendations'       => xl('Recommendations and treatment'),
  'label_scripts_and_referrals' => xl('Prescriptions and other referrals')
);

if (!is_file($template_file)) die("$template_file does not exist!");

$transid = empty($_REQUEST['transid']) ? 0 : $_REQUEST['transid'] + 0;

// if (!$transid) die("Transaction ID is missing!");

if ($transid) {
  $trow = getTransById($transid);
  $patient_id = $trow['pid'];
  $refer_date = empty($trow['refer_date']) ? date('Y-m-d') : $trow['refer_date'];
}
else {
  if (empty($_REQUEST['patient_id'])) {
    // If no transaction ID or patient ID, this will be a totally blank form.
    $patient_id = 0;
    $refer_date = '';
  } else {
    $patient_id = $_REQUEST['patient_id'] + 0;
    $refer_date = date('Y-m-d');
  }
  $trow = array('id' => '', 'pid' => $patient_id, 'refer_date' => $refer_date);
}

if ($patient_id) {
  $patdata = getPatientData($patient_id);
  $patient_age = getPatientAge(str_replace('-', '', $patdata['DOB']));
} else {
  $patdata = array('DOB' => '');
  $patient_age = '';
}

$frrow = sqlQuery("SELECT * FROM users WHERE id = '" . $trow['refer_from'] . "'");
if (empty($frrow)) $frrow = array();

$torow = sqlQuery("SELECT * FROM users WHERE id = '" . $trow['refer_to'] . "'");
if (empty($torow)) $torow = array(
  'organization' => '',
  'street' => '',
  'city' => '',
  'state' => '',
  'zip' => '',
  'phone' => '',
);

$vrow = sqlQuery("SELECT * FROM form_vitals WHERE " .
  "pid = '$patient_id' AND date <= '$refer_date 23:59:59' " .
  "ORDER BY date DESC LIMIT 1");
if (empty($vrow)) $vrow = array(
  'bps' => '',
  'bpd' => '',
  'weight' => '',
  'height' => '',
);

// $facrow = sqlQuery("SELECT name, facility_npi FROM facility ORDER BY " .
//   "service_location DESC, billing_location DESC, id ASC LIMIT 1");
$facrow = getFacility(-1);

// Make some items HTML-friendly if they are empty.
if (empty($trow['refer_date'])) $trow['refer_date'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
if (empty($trow['id'])) $trow['id'] = '&nbsp;';
if (empty($patient_id)) $patient_id = '&nbsp;';
if (empty($facrow['facility_npi'])) $facrow['facility_npi'] = '&nbsp;';

$s = '';
$fh = fopen($template_file, 'r');
while (!feof($fh)) $s .= fread($fh, 8192);
fclose($fh);

$s = str_replace("{header1}", genFacilityTitle($TEMPLATE_LABELS['label_form1_title'], -1), $s);
$s = str_replace("{header2}", genFacilityTitle($TEMPLATE_LABELS['label_form2_title'], -1), $s);

$s = str_replace("{fac_name}"        , $facrow['name']        , $s);
$s = str_replace("{fac_facility_npi}", $facrow['facility_npi'], $s);
$s = str_replace("{ref_id}"          , $trow['id']            , $s);
$s = str_replace("{ref_pid}"         , $patient_id            , $s);
$s = str_replace("{pt_age}"          , $patient_age           , $s);

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'REF' ORDER BY group_name, seq");
while ($frow = sqlFetchArray($fres)) {
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  $currvalue = '';
  if (isset($trow[$field_id])) $currvalue = $trow[$field_id];
  $s = str_replace("{ref_$field_id}",
    generate_display_field($frow, $currvalue), $s);
}

foreach ($patdata as $key => $value) {
  if ($key == "sex") {
   $s = str_replace("{pt_$key}", generate_display_field(array('data_type'=>'1','list_id'=>'sex'), $value), $s);
  }
  else {
   $s = str_replace("{pt_$key}", $value, $s);   
  }
}

foreach ($frrow as $key => $value) {
  $s = str_replace("{from_$key}", $value, $s);
}

foreach ($torow as $key => $value) {
  $s = str_replace("{to_$key}", $value, $s);
}

foreach ($vrow as $key => $value) {
  $s = str_replace("{v_$key}", $value, $s);
}

foreach ($TEMPLATE_LABELS as $key => $value) {
  $s = str_replace("{".$key."}", $value, $s);
}

// A final pass to clear any unmatched variables:
$s = preg_replace('/\{\S+\}/', '', $s);

echo $s;
?>
