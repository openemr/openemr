<?php

/**
 * print_referral.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/transactions.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc.php");

$template_file = $GLOBALS['OE_SITE_DIR'] . "/referral_template.html";

$TEMPLATE_LABELS = array(
  'label_clinic_id'             => xlt('Clinic ID'),
  'label_client_id'             => xlt('Client ID'),
  'label_control_no'            => xlt('Control No.'),
  'label_date'                  => xlt('Date'),
  'label_webpage_title'         => xlt('Referral Form'),
  'label_form1_title'           => xlt('Referral Form'),
  'label_name'                  => xlt('Name'),
  'label_age'                   => xlt('Age'),
  'label_gender'                => xlt('Gender'),
  'label_address'               => xlt('Address'),
  'label_postal'                => xlt('Postal'),
  'label_phone'                 => xlt('Phone'),
  'label_ref_reason'            => xlt('Reference Reason'),
  'label_diagnosis'             => xlt('Diagnosis'),
  'label_ref_class'             => xlt('Reference classification (risk level)'),
  'label_dr_name_sig'           => xlt('Doctor\'s name and signature'),
  'label_refer_to'              => xlt('Referred to'),
  'label_clinic'                => xlt('Health centre/clinic'),
  'label_history_summary'       => xlt('Client medical history summary'),
  'label_bp'                    => xlt('Blood pressure'),
  'label_ht'                    => xlt('Height'),
  'label_wt'                    => xlt('Weight'),
  'label_ref_name_sig'          => xlt('Referer name and signature'),
  'label_special_name_sig'      => xlt('Specialist name and signature'),
  'label_form2_title'           => xlt('Counter Referral Form'),
  'label_findings'              => xlt('Findings'),
  'label_final_diagnosis'       => xlt('Final Diagnosis'),
  'label_services_provided'     => xlt('Services provided'),
  'label_recommendations'       => xlt('Recommendations and treatment'),
  'label_scripts_and_referrals' => xlt('Prescriptions and other referrals'),
  'label_subhead_clinic'        => xlt('Clinic Copy'),
  'label_subhead_patient'       => xlt('Client Copy'),
  'label_subhead_referred'      => xlt('For Referred Organization/Practitioner'),
  'label_ins_name'              => xlt('Insurance'),
  'label_ins_plan_name'         => xlt('Plan'),
  'label_ins_policy'            => xlt('Policy'),
  'label_ins_group'             => xlt('Group'),
  'label_ins_date'              => xlt('Effective Date')
);

if (!is_file($template_file)) {
    die(text($template_file) . " does not exist!");
}

$transid = empty($_REQUEST['transid']) ? 0 : $_REQUEST['transid'] + 0;

// if (!$transid) die("Transaction ID is missing!");

if ($transid) {
    $trow = getTransById($transid);
    $patient_id = $trow['pid'];
    $refer_date = empty($trow['refer_date']) ? date('Y-m-d') : $trow['refer_date'];
} else {
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
    $insurancedata = getInsuranceData($patient_id);
} else {
    $patdata = array('DOB' => '');
    $patient_age = '';
    $ins_name = '';
}

if (empty($trow['refer_from'])) {
    $trow['refer_from'] = 0;
}

if (empty($trow['refer_to'  ])) {
    $trow['refer_to'  ] = 0;
}

$frrow = sqlQuery("SELECT * FROM users WHERE id = ?", array($trow['refer_from']));
if (empty($frrow)) {
    $frrow = array();
}

$torow = sqlQuery("SELECT * FROM users WHERE id = ?", array($trow['refer_to']));
if (empty($torow)) {
    $torow = array(
    'organization' => '',
    'street' => '',
    'city' => '',
    'state' => '',
    'zip' => '',
    'phone' => '',
    );
}

$vrow = sqlQuery("SELECT * FROM form_vitals WHERE " .
  "pid = ? AND date <= ? " .
  "ORDER BY date DESC LIMIT 1", array($patient_id, $refer_date . " 23:59:59"));
if (empty($vrow)) {
    $vrow = array(
    'bps' => '',
    'bpd' => '',
    'weight' => '',
    'height' => '',
    );
}

// $facrow = sqlQuery("SELECT name, facility_npi FROM facility ORDER BY " .
//   "service_location DESC, billing_location DESC, id ASC LIMIT 1");
$facrow = getFacility(-1);

// Make some items HTML-friendly if they are empty.
if (empty($trow['id'])) {
    $trow['id'] = '&nbsp;';
}

if (empty($patient_id)) {
    $patient_id = '&nbsp;';
}

if (empty($facrow['facility_npi'])) {
    $facrow['facility_npi'] = '&nbsp;';
}

// Generate link to MA logo if it exists.
$logo = "";
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "$web_root/$ma_logo_path";
}

$s = '';
$fh = fopen($template_file, 'r');
while (!feof($fh)) {
    $s .= fread($fh, 8192);
}

fclose($fh);

$s = str_replace("{header1}", genFacilityTitle($TEMPLATE_LABELS['label_form1_title'], -1, $logo), $s);
$s = str_replace("{header2}", genFacilityTitle($TEMPLATE_LABELS['label_form2_title'], -1, $logo), $s);
$s = str_replace("{fac_name}", text($facrow['name'] ?? ''), $s);
$s = str_replace("{fac_facility_npi}", text($facrow['facility_npi']), $s);
$s = str_replace("{ref_id}", text($trow['id']), $s);
$s = str_replace("{ref_pid}", text($patient_id), $s);
$s = str_replace("{pt_age}", text($patient_age), $s);


$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'LBTref' ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    $currvalue = '';
    if (isset($trow[$field_id])) {
        $currvalue = $trow[$field_id];
    }

    $s = str_replace(
        "{ref_$field_id}",
        generate_display_field($frow, $currvalue),
        $s
    );
}

foreach ($patdata as $key => $value) {
    if ($key == "sex") {
        $s = str_replace("{pt_$key}", generate_display_field(array('data_type' => '1','list_id' => 'sex'), $value), $s);
    } else {
        $s = str_replace("{pt_$key}", text($value), $s);
    }
}

foreach ($frrow as $key => $value) {
    $s = str_replace("{from_$key}", text($value), $s);
}

foreach ($torow as $key => $value) {
    $s = str_replace("{to_$key}", text($value), $s);
}

foreach ($vrow as $key => $value) {
    $s = str_replace("{v_$key}", text($value), $s);
}

foreach ($TEMPLATE_LABELS as $key => $value) {
    $s = str_replace("{" . $key . "}", $value, $s);
}

if (!empty($insurancedata)) {
    foreach ($insurancedata as $key => $value) {
        $s = str_replace("{insurance_$key}", text($value), $s);
    }
}

// A final pass to clear any unmatched variables:
$s = preg_replace('/\{\S+\}/', '', $s);

echo $s;
