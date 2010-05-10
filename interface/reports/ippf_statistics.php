<?php
// Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module creates statistical reports related to family planning
// and sexual and reproductive health.

include_once("../globals.php");
include_once("../../library/patient.inc");
include_once("../../library/acl.inc");

// Might want something different here.
//
if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$report_type = empty($_GET['t']) ? 'i' : $_GET['t'];

$from_date     = fixDate($_POST['form_from_date']);
$to_date       = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_by       = $_POST['form_by'];     // this is a scalar
$form_show     = $_POST['form_show'];   // this is an array
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_sexes    = isset($_POST['form_sexes']) ? $_POST['form_sexes'] : '3';
$form_cors     = isset($_POST['form_cors']) ? $_POST['form_cors'] : '1';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

if (empty($form_by))    $form_by = '1';
if (empty($form_show))  $form_show = array('1');

// One of these is chosen as the left column, or Y-axis, of the report.
//
if ($report_type == 'm') {
  $report_title = xl('Member Association Statistics Report');
  $arr_by = array(
    101 => xl('MA Category'),
    102 => xl('Specific Service'),
    104 => xl('Method and Specific Product'),
    105 => xl('Product Contraceptive Method'),
    17  => xl('Patient'),
    9   => xl('Internal Referrals'),
    10  => xl('External Referrals'),
    103 => xl('Referral Source'),
    2   => xl('Total'),
  );
  $arr_content = array(
    1 => xl('Services/Products'),
    2 => xl('Unique Clients'),
    4 => xl('Unique New Clients')
  );
  $arr_report = array(
    // Items are content|row|column|column|...
    /*****************************************************************
    '2|2|3|4|5|8|11' => xl('Client Profile - Unique Clients'),
    '4|2|3|4|5|8|11' => xl('Client Profile - New Clients'),
    *****************************************************************/
  );
}
else if ($report_type == 'g') {
  $report_title = xl('GCAC Statistics Report');
  $arr_by = array(
    13 => xl('Abortion-Related Categories'),
    1  => xl('Total SRH & Family Planning'),
    12 => xl('Pre-Abortion Counseling'),
    5  => xl('Abortion Method'), // includes surgical and drug-induced
    8  => xl('Post-Abortion Followup'),
    7  => xl('Post-Abortion Contraception'),
    11 => xl('Complications of Abortion'),
    10  => xl('External Referrals'),
    20  => xl('External Referral Followups'),
  );
  $arr_content = array(
    1 => xl('Services'),
    2 => xl('Unique Clients'),
    4 => xl('Unique New Clients'),
  );
  $arr_report = array(
    /*****************************************************************
    '1|11|13' => xl('Complications by Service Provider'),
    *****************************************************************/
  );
}
else {
  $report_title = xl('IPPF Statistics Report');
  $arr_by = array(
    3  => xl('General Service Category'),
    4  => xl('Specific Service'),
    104 => xl('Method and Specific Product'),
    105 => xl('Product Contraceptive Method'),
    6  => xl('Contraceptive Method'),
    9   => xl('Internal Referrals'),
    10  => xl('External Referrals'),
  );
  $arr_content = array(
    1 => xl('Services/Products'),
    3 => xl('New Acceptors'),
  );
  $arr_report = array(
  );
}

if ($report_type == 'm') {
}
else {
}

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

$arr_show   = array(
  '.total' => array('title' => 'Total'),
  '.age'   => array('title' => 'Age Category'),
); // info about selectable columns

$arr_titles = array(); // will contain column headers

// Query layout_options table to generate the $arr_show table.
// Table key is the field ID.
$lres = sqlStatement("SELECT field_id, title, data_type, list_id, description " .
  "FROM layout_options WHERE " .
  "form_id = 'DEM' AND uor > 0 AND field_id NOT LIKE 'em%' " .
  "ORDER BY group_name, seq, title");
while ($lrow = sqlFetchArray($lres)) {
  $fid = $lrow['field_id'];
  if ($fid == 'fname' || $fid == 'mname' || $fid == 'lname') continue;
  $arr_show[$fid] = $lrow;
  $arr_titles[$fid] = array();
}

// Compute age in years given a DOB and "as of" date.
//
function getAge($dob, $asof='') {
  if (empty($asof)) $asof = date('Y-m-d');
  $a1 = explode('-', substr($dob , 0, 10));
  $a2 = explode('-', substr($asof, 0, 10));
  $age = $a2[0] - $a1[0];
  if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) --$age;
  // echo "<!-- $dob $asof $age -->\n"; // debugging
  return $age;
}

$cellcount = 0;

function genStartRow($att) {
  global $cellcount, $form_output;
  if ($form_output != 3) echo " <tr $att>\n";
  $cellcount = 0;
}

function genEndRow() {
  global $form_output;
  if ($form_output == 3) {
    echo "\n";
  }
  else {
    echo " </tr>\n";
  }
}

/*********************************************************************
function genAnyCell($data, $right=false, $class='') {
  global $cellcount;
  if ($_POST['form_csvexport']) {
    if ($cellcount) echo ',';
    echo '"' . $data . '"';
  }
  else {
    echo "  <td";
    if ($class) echo " class='$class'";
    if ($right) echo " align='right'";
    echo ">$data</td>\n";
  }
  ++$cellcount;
}
*********************************************************************/

function getListTitle($list, $option) {
  $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = '$list' AND option_id = '$option'");
  if (empty($row['title'])) return $option;
  return $row['title'];
}

// Usually this generates one cell, but allows for two or more.
//
function genAnyCell($data, $right=false, $class='') {
  global $cellcount, $form_output;
  if (!is_array($data)) {
    $data = array(0 => $data);
  }
  foreach ($data as $datum) {
    if ($form_output == 3) {
      if ($cellcount) echo ',';
      echo '"' . $datum . '"';
    }
    else {
      echo "  <td";
      if ($class) echo " class='$class'";
      if ($right) echo " align='right'";
      echo ">$datum</td>\n";
    }
    ++$cellcount;
  }
}

function genHeadCell($data, $right=false) {
  genAnyCell($data, $right, 'dehead');
}

// Create an HTML table cell containing a numeric value, and track totals.
//
function genNumCell($num, $cnum) {
  global $atotals, $form_output;
  $atotals[$cnum] += $num;
  if (empty($num) && $form_output != 3) $num = '&nbsp;';
  genAnyCell($num, true, 'detail');
}

// Translate an IPPF code to the corresponding descriptive name of its
// contraceptive method, or to an empty string if none applies.
//
function getContraceptiveMethod($code) {
  $key = '';
  if (preg_match('/^111101/', $code)) {
    $key = xl('Pills');
  }
  else if (preg_match('/^11111[1-9]/', $code)) {
    $key = xl('Injectables');
  }
  else if (preg_match('/^11112[1-9]/', $code)) {
    $key = xl('Implants');
  }
  else if (preg_match('/^111132/', $code)) {
    $key = xl('Patch');
  }
  else if (preg_match('/^111133/', $code)) {
    $key = xl('Vaginal Ring');
  }
  else if (preg_match('/^112141/', $code)) {
    $key = xl('Male Condoms');
  }
  else if (preg_match('/^112142/', $code)) {
    $key = xl('Female Condoms');
  }
  else if (preg_match('/^11215[1-9]/', $code)) {
    $key = xl('Diaphragms/Caps');
  }
  else if (preg_match('/^11216[1-9]/', $code)) {
    $key = xl('Spermicides');
  }
  else if (preg_match('/^11317[1-9]/', $code)) {
    $key = xl('IUD');
  }
  else if (preg_match('/^145212/', $code)) {
    $key = xl('Emergency Contraception');
  }
  else if (preg_match('/^121181.13/', $code)) {
    $key = xl('Female VSC');
  }
  else if (preg_match('/^122182.13/', $code)) {
    $key = xl('Male VSC');
  }
  else if (preg_match('/^131191.10/', $code)) {
    $key = xl('Awareness-Based');
  }
  return $key;
}

// Translate an IPPF code to the corresponding descriptive name of its
// abortion method, or to an empty string if none applies.
//
function getAbortionMethod($code) {
  $key = '';
  if (preg_match('/^25222[34]/', $code)) {
    if (preg_match('/^2522231/', $code)) {
      $key = xl('D&C');
    }
    else if (preg_match('/^2522232/', $code)) {
      $key = xl('D&E');
    }
    else if (preg_match('/^2522233/', $code)) {
      $key = xl('MVA');
    }
    else if (preg_match('/^252224/', $code)) {
      $key = xl('Medical');
    }
    else {
      $key = xl('Other Surgical');
    }
  }
  return $key;
}

/*********************************************************************
// Helper function to look up the GCAC issue associated with a visit.
// Ideally this is the one and only GCAC issue linked to the encounter.
// However if there are multiple such issues, or if only unlinked issues
// are found, then we pick the one with its start date closest to the
// encounter date.
//
function getGcacData($row, $what, $morejoins="") {
  $patient_id = $row['pid'];
  $encounter_id = $row['encounter'];
  $encdate = substr($row['encdate'], 0, 10);
  $query = "SELECT $what " .
    "FROM lists AS l " .
    "JOIN lists_ippf_gcac AS lg ON l.type = 'ippf_gcac' AND lg.id = l.id " .
    "LEFT JOIN issue_encounter AS ie ON ie.pid = '$patient_id' AND " .
    "ie.encounter = '$encounter_id' AND ie.list_id = l.id " .
    "$morejoins " .
    "WHERE l.pid = '$patient_id' AND " .
    "l.activity = 1 AND l.type = 'ippf_gcac' " .
    "ORDER BY ie.pid DESC, ABS(DATEDIFF(l.begdate, '$encdate')) ASC " .
    "LIMIT 1";
  // Note that reverse-ordering by ie.pid is a trick for sorting
  // issues linked to the encounter (non-null values) first.
  return sqlQuery($query);
}

// Get the "client status" field from the related GCAC issue.
//
function getGcacClientStatus($row) {
  $irow = getGcacData($row, "lo.title", "LEFT JOIN list_options AS lo ON " .
    "lo.list_id = 'clientstatus' AND lo.option_id = lg.client_status");
  if (empty($irow['title'])) {
    $key = xl('Indeterminate');
  }
  else {
    // The client status description should be just fine for this.
    $key = $irow['title'];
  }
  return $key;
}
*********************************************************************/

// Get the "client status" as descriptive text.
// This comes from the most recent GCAC visit form for visits within
// the past 2 weeks, although there really should be such a form
// attached to the visit associated with $row.
//
function getGcacClientStatus($row) {
  $pid = $row['pid'];
  $encdate = $row['encdate'];
  $query = "SELECT lo.title " .
    "FROM forms AS f, form_encounter AS fe, lbf_data AS d, list_options AS lo " .
    "WHERE f.pid = '$pid' AND " .
    "f.formdir = 'LBFgcac' AND " .
    "f.deleted = 0 AND " .
    "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
    "fe.date <= '$encdate' AND " .
    "DATE_ADD(fe.date, INTERVAL 14 DAY) > '$encdate' AND " .
    "d.form_id = f.form_id AND " .
    "d.field_id = 'client_status' AND " .
    "lo.list_id = 'clientstatus' AND " .
    "lo.option_id = d.field_value " .
    "ORDER BY d.form_id DESC LIMIT 1";
  $irow = sqlQuery($query);
  // echo "<!-- $query -->\n"; // debugging
  return empty($irow['title']) ? xl('Indeterminate') : $irow['title'];
}

// Helper function called after the reporting key is determined for a row.
//
function loadColumnData($key, $row, $quantity=1) {
  global $areport, $arr_titles, $form_cors, $from_date, $to_date, $arr_show;

  // Quantity is not meaningful if we are counting clients.
  if ($form_cors != 1) $quantity = 1;

  // If first instance of this key, initialize its arrays.
  if (empty($areport[$key])) {
    $areport[$key] = array();
    $areport[$key]['.prp'] = 0;       // previous pid
    $areport[$key]['.wom'] = 0;       // number of services for women
    $areport[$key]['.men'] = 0;       // number of services for men
    $areport[$key]['.age'] = array(0,0,0,0,0,0,0,0,0); // age array
    foreach ($arr_show as $askey => $dummy) {
      if (substr($askey, 0, 1) == '.') continue;
      $areport[$key][$askey] = array();
    }
  }

  // Skip this key if we are counting unique patients and the key
  // has already seen this patient.
  if ($form_cors == '2' && $row['pid'] == $areport[$key]['.prp']) return;

  // If we are counting new acceptors, then require a unique patient
  // whose contraceptive start date is within the reporting period.
  if ($form_cors == '3') {
    // if ($row['pid'] == $areport[$key]['prp']) return;
    if ($row['pid'] == $areport[$key]['.prp']) return;
    // Check contraceptive start date.
    if (!$row['contrastart'] || $row['contrastart'] < $from_date ||
      $row['contrastart'] > $to_date) return;
  }

  // If we are counting new clients, then require a unique patient
  // whose registration date is within the reporting period.
  if ($form_cors == '4') {
    if ($row['pid'] == $areport[$key]['.prp']) return;
    // Check registration date.
    if (!$row['regdate'] || $row['regdate'] < $from_date ||
      $row['regdate'] > $to_date) return;
  }

  // Flag this patient as having been encountered for this report row.
  // $areport[$key]['prp'] = $row['pid'];
  $areport[$key]['.prp'] = $row['pid'];

  // Increment the correct sex category.
  if (strcasecmp($row['sex'], 'Male') == 0)
    $areport[$key]['.men'] += $quantity;
  else
    $areport[$key]['.wom'] += $quantity;

  // Increment the correct age category.
  $age = getAge(fixDate($row['DOB']), $row['encdate']);
  $i = min(intval(($age - 5) / 5), 8);
  if ($age < 11) $i = 0;
  $areport[$key]['.age'][$i] += $quantity;

  foreach ($arr_show as $askey => $dummy) {
    if (substr($askey, 0, 1) == '.') continue;
    $status = empty($row[$askey]) ? 'Unspecified' : $row[$askey];
    $areport[$key][$askey][$status] += $quantity;
    $arr_titles[$askey][$status] += $quantity;
  }
}

// This is called for each IPPF service code that is selected.
//
function process_ippf_code($row, $code) {
  global $areport, $arr_titles, $form_by;

  $key = 'Unspecified';

  // SRH including Family Planning
  //
  if ($form_by === '1') {
    if (preg_match('/^1/', $code)) {
      $key = xl('SRH - Family Planning');
    }
    else if (preg_match('/^2/', $code)) {
      $key = xl('SRH Non Family Planning');
    }
    else {
      return;
    }
  }

  // General Service Category
  //
  else if ($form_by === '3') {
    if (preg_match('/^1/', $code)) {
      $key = xl('SRH - Family Planning');
    }
    else if (preg_match('/^2/', $code)) {
      $key = xl('SRH Non Family Planning');
    }
    else if (preg_match('/^3/', $code)) {
      $key = xl('Non-SRH Medical');
    }
    else if (preg_match('/^4/', $code)) {
      $key = xl('Non-SRH Non-Medical');
    }
    else {
      $key = xl('Invalid Service Codes');
    }
  }

  // Abortion-Related Category
  //
  else if ($form_by === '13') {
    if (preg_match('/^252221/', $code)) {
      $key = xl('Pre-Abortion Counseling');
    }
    else if (preg_match('/^252222/', $code)) {
      $key = xl('Pre-Abortion Consultation');
    }
    else if (preg_match('/^252223/', $code)) {
      $key = xl('Induced Abortion');
    }
    else if (preg_match('/^252224/', $code)) {
      $key = xl('Medical Abortion');
    }
    else if (preg_match('/^252225/', $code)) {
      $key = xl('Incomplete Abortion Treatment');
    }
    else if (preg_match('/^252226/', $code)) {
      $key = xl('Post-Abortion Care');
    }
    else if (preg_match('/^252227/', $code)) {
      $key = xl('Post-Abortion Counseling');
    }
    else if (preg_match('/^25222/', $code)) {
      $key = xl('Other/Generic Abortion-Related');
    }
    else {
      return;
    }
  }

  // Specific Services. One row for each IPPF code.
  //
  else if ($form_by === '4') {
    $key = $code;
  }

  // Abortion Method.
  //
  else if ($form_by === '5') {
    $key = getAbortionMethod($code);
    if (empty($key)) return;
  }

  // Contraceptive Method.
  //
  else if ($form_by === '6') {
    $key = getContraceptiveMethod($code);
    if (empty($key)) return;
  }

  /*******************************************************************
  // Contraceptive method for new contraceptive adoption following abortion.
  // Get it from the IPPF code if an abortion issue is linked to the visit.
  // Note we are handling this during processing of services rather than
  // by enumerating issues, because we need the service date.
  //
  else if ($form_by === '7') {
    $key = getContraceptiveMethod($code);
    if (empty($key)) return;
    $patient_id = $row['pid'];
    $encounter_id = $row['encounter'];
    $query = "SELECT COUNT(*) AS count " .
      "FROM lists AS l " .
      "JOIN issue_encounter AS ie ON ie.pid = '$patient_id' AND " .
      "ie.encounter = '$encounter_id' AND ie.list_id = l.id " .
      "WHERE l.pid = '$patient_id' AND " .
      "l.activity = 1 AND l.type = 'ippf_gcac'";
    // echo "<!-- $key: $query -->\n"; // debugging
    $irow = sqlQuery($query);
    if (empty($irow['count'])) return;
  }
  *******************************************************************/

  // Contraceptive method for new contraceptive adoption following abortion.
  // Get it from the IPPF code if there is a suitable recent GCAC form.
  //
  else if ($form_by === '7') {
    $key = getContraceptiveMethod($code);
    if (empty($key)) return;
    $patient_id = $row['pid'];
    $encdate = $row['encdate'];
    $query = "SELECT COUNT(*) AS count " .
      "FROM forms AS f, form_encounter AS fe, lbf_data AS d " .
      "WHERE f.pid = '$patient_id' AND " .
      "f.formdir = 'LBFgcac' AND " .
      "f.deleted = 0 AND " .
      "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
      "fe.date <= '$encdate' AND " .
      "DATE_ADD(fe.date, INTERVAL 14 DAY) > '$encdate' AND " .
      "d.form_id = f.form_id AND " .
      "d.field_id = 'client_status' AND " .
      "( d.field_value = 'maaa' OR d.field_value = 'refout' )";
    // echo "<!-- $key: $query -->\n"; // debugging
    $irow = sqlQuery($query);
    if (empty($irow['count'])) return;
  }

  // Post-Abortion Care and Followup by Source.
  // Requirements just call for counting sessions, but this way the columns
  // can be anything - age category, religion, whatever.
  //
  else if ($form_by === '8') {
    if (preg_match('/^25222[567]/', $code)) { // care, followup and incomplete abortion treatment
      $key = getGcacClientStatus($row);
    } else {
      return;
    }
  }

  /*******************************************************************
  // Complications of abortion by abortion method and complication type.
  // These may be noted either during recovery or during a followup visit.
  // Again, driven by services in order to report by service date.
  // Note: If there are multiple complications, they will all be reported.
  //
  else if ($form_by === '11') {
    $compl_type = '';
    if (preg_match('/^25222[345]/', $code)) { // all abortions including incomplete
      $compl_type = 'rec_compl';
    }
    else if (preg_match('/^25222[67]/', $code)) { // all post-abortion care and followup
      $compl_type = 'fol_compl';
    }
    else {
      return;
    }
    $irow = getGcacData($row, "lg.$compl_type, lo.title",
      "LEFT JOIN list_options AS lo ON lo.list_id = 'in_ab_proc' AND " .
      "lo.option_id = lg.in_ab_proc");
    if (empty($irow)) return; // this should not happen
    if (empty($irow[$compl_type])) return; // ok, no complications
    // We have one or more complications.
    $abtype = empty($irow['title']) ? xl('Indeterminate') : $irow['title'];
    $acompl = explode('|', $irow[$compl_type]);
    foreach ($acompl as $compl) {
      $crow = sqlQuery("SELECT title FROM list_options WHERE " .
        "list_id = 'complication' AND option_id = '$compl'");
      $key = "$abtype / " . $crow['title'];
      loadColumnData($key, $row);
    }
    return; // because loadColumnData() is already done.
  }
  *******************************************************************/

  // Pre-Abortion Counseling.  Three possible situations:
  //   Provided abortion in the MA clinics
  //   Referred to other service providers (govt,private clinics)
  //   Decided not to have the abortion
  //
  else if ($form_by === '12') {
    if (preg_match('/^252221/', $code)) { // all pre-abortion counseling
      $key = getGcacClientStatus($row);
    } else {
      return;
    }
  }

  // Patient Name.
  //
  else if ($form_by === '17') {
    $key = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
  }

  else {
    return; // no match, so do nothing
  }

  // OK we now have the reporting key for this issue.
  loadColumnData($key, $row);

} // end function process_ippf_code()

// This is called for each MA service code that is selected.
//
function process_ma_code($row) {
  global $form_by, $arr_content, $form_cors;

  $key = 'Unspecified';

  // One row for each service category.
  //
  if ($form_by === '101') {
    if (!empty($row['lo_title'])) $key = xl($row['lo_title']);
  }

  // Specific Services. One row for each MA code.
  //
  else if ($form_by === '102') {
    $key = $row['code'];
  }

  // One row for each referral source.
  //
  else if ($form_by === '103') {
    $key = $row['referral_source'];
  }

  // Just one row.
  //
  else if ($form_by === '2') {
    $key = $arr_content[$form_cors];
  }

  else {
    return;
  }

  loadColumnData($key, $row);
}

function LBFgcac_query($pid, $encounter, $name) {
  $query = "SELECT d.form_id, d.field_value " .
    "FROM forms AS f, form_encounter AS fe, lbf_data AS d " .
    "WHERE f.pid = '$pid' AND " .
    "f.encounter = '$encounter' AND " .
    "f.formdir = 'LBFgcac' AND " .
    "f.deleted = 0 AND " .
    "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
    "d.form_id = f.form_id AND " .
    "d.field_id = '$name'";
  return sqlStatement($query);
}

function LBFgcac_title($form_id, $field_id, $list_id) {
  $query = "SELECT lo.title " .
    "FROM lbf_data AS d, list_options AS lo WHERE " .
    "d.form_id = '$form_id' AND " .
    "d.field_id = '$field_id' AND " .
    "lo.list_id = '$list_id' AND " .
    "lo.option_id = d.field_value " .
    "LIMIT 1";
  $row = sqlQuery($query);
  return empty($row['title']) ? '' : $row['title'];
}

// This is called for each encounter that is selected.
//
function process_visit($row) {
  global $form_by;

  if ($form_by !== '7' && $form_by !== '11') return;

  // New contraceptive method following abortion.  These should only be
  // present for inbound referrals.
  //
  if ($form_by === '7') {
    // We think this case goes away, but not sure yet.
    /*****************************************************************
    $dres = LBFgcac_query($row['pid'], $row['encounter'], 'contrameth');
    while ($drow = sqlFetchArray($dres)) {
      $a = explode('|', $drow['field_value']);
      foreach ($a as $methid) {
        if (empty($methid)) continue;
        $crow = sqlQuery("SELECT title FROM list_options WHERE " .
          "list_id = 'contrameth' AND option_id = '$methid'");
        $key = $crow['title'];
        if (empty($key)) $key = xl('Indeterminate');
        loadColumnData($key, $row);
      }
    }
    *****************************************************************/
  }

  // Complications of abortion by abortion method and complication type.
  // These may be noted either during recovery or during a followup visit.
  // Note: If there are multiple complications, they will all be reported.
  //
  else if ($form_by === '11') {
    $dres = LBFgcac_query($row['pid'], $row['encounter'], 'complications');
    while ($drow = sqlFetchArray($dres)) {
      $a = explode('|', $drow['field_value']);
      foreach ($a as $complid) {
        if (empty($complid)) continue;
        $crow = sqlQuery("SELECT title FROM list_options WHERE " .
          "list_id = 'complication' AND option_id = '$complid'");
        $abtype = LBFgcac_title($drow['form_id'], 'in_ab_proc', 'in_ab_proc');
        if (empty($abtype)) $abtype = xl('Indeterminate');
        $key = "$abtype / " . $crow['title'];
        loadColumnData($key, $row);
      }
    }
  }

  // loadColumnData() already done as needed.
}

/*********************************************************************
// This is called for each issue that is selected.
//
function process_issue($row) {
  global $form_by;

  $key = 'Unspecified';

  // Pre-Abortion Counseling.  Three possible rows:
  //   Provided abortion in the MA clinics
  //   Referred to other service providers (govt,private clinics)
  //   Decided not to have the abortion
  //
  if ($form_by === '12') {

    // TBD: Assign one of the 3 keys, or just return.

  }

  // Others TBD

  else {
    return;
  }

  // TBD: Load column data from the issue.
  // loadColumnData($key, $row);
}
*********************************************************************/

// This is called for each selected referral.
// Row keys are the first specified MA code, if any.
//
function process_referral($row) {
  global $form_by;
  $key = 'Unspecified';

  if (!empty($row['refer_related_code'])) {
    $relcodes = explode(';', $row['refer_related_code']);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);

      if ($codetype !== 'IPPF') continue;

      if ($form_by === '1') {
        if (preg_match('/^[12]/', $code)) {
          $key = xl('SRH Referrals');
          loadColumnData($key, $row);
          break;
        }
      }
      else { // $form_by is 9 (internal) or 10 or 20 (external) referrals
        $key = $code;
        break;
      }
    } // end foreach
  }

  if ($form_by !== '1') loadColumnData($key, $row);
}

  // If we are doing the CSV export then generate the needed HTTP headers.
  // Otherwise generate HTML.
  //
  if ($form_output == 3) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=service_statistics_report.csv");
    header("Content-Description: File Transfer");
  }
  else {
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo $report_title; ?></title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // Begin experimental code

 function selectByValue(sel, val) {
  for (var i = 0; i < sel.options.length; ++i) {
   if (sel.options[i].value == val) sel.options[i].selected = true;
  }
 }

 function selreport() {
  var f = document.forms[0];
  var isdis = 'visible';
  var s = f.form_report;
  var v = (s.selectedIndex < 0) ? '' : s.options[s.selectedIndex].value;
  if (v.length > 0) {
   isdis = 'hidden';
   var a = v.split("|");
   f.form_cors.selectedIndex = -1;
   f.form_by.selectedIndex = -1;
   f['form_show[]'].selectedIndex = -1;
   selectByValue(f.form_cors, a[0]);
   selectByValue(f.form_by, a[1]);
   for (var i = 2; i < a.length; ++i) {
    selectByValue(f['form_show[]'], a[i]);
   }
  }
  f.form_by.style.visibility = isdis;
  f.form_cors.style.visibility = isdis;
  f['form_show[]'].style.visibility = isdis;
 }

 // End experimental code

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php echo $report_title; ?></h2>

<form name='theform' method='post'
 action='ippf_statistics.php?t=<?php echo $report_type ?>'>

<table border='0' cellspacing='5' cellpadding='1'>

 <!-- Begin experimental code -->
 <tr<?php if (empty($arr_report)) echo " style='display:none'"; ?>>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Report','e'); ?>:
  </td>
  <td valign='top' class='detail' colspan='3'>
   <select name='form_report' title='Predefined reports' onchange='selreport()'>
<?php
  echo "    <option value=''>" . xl('Custom') . "</option>\n";
  foreach ($arr_report as $key => $value) {
    echo "    <option value='$key'";
    if ($key == $form_report) echo " selected";
    echo ">" . $value . "</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' class='detail'>
   &nbsp;
  </td>
 </tr>
 <!-- End experimental code -->

 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Rows','e'); ?>:
  </td>
  <td valign='top' class='detail'>
   <select name='form_by' title='Left column of report'>
<?php
  foreach ($arr_by as $key => $value) {
    echo "    <option value='$key'";
    if ($key == $form_by) echo " selected";
    echo ">" . $value . "</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Content','e'); ?>:
  </td>
  <td valign='top' class='detail'>
   <select name='form_cors' title='<?php xl('What is to be counted?','e'); ?>'>
<?php
  foreach ($arr_content as $key => $value) {
    echo "    <option value='$key'";
    if ($key == $form_cors) echo " selected";
    echo ">$value</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' class='detail'>
   &nbsp;
  </td>
 </tr>
 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Columns','e'); ?>:
  </td>
  <td valign='top' class='detail'>
   <select name='form_show[]' size='4' multiple
    title='<?php xl('Hold down Ctrl to select multiple items','e'); ?>'>
<?php
  foreach ($arr_show as $key => $value) {
    $title = $value['title'];
    if (empty($title) || $key == 'title') $title = $value['description'];
    echo "    <option value='$key'";
    if (is_array($form_show) && in_array($key, $form_show)) echo " selected";
    echo ">$title</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Filters','e'); ?>:
  </td>
  <td colspan='2' class='detail' style='border-style:solid;border-width:1px;border-color:#cccccc'>
   <table>
    <tr>
     <td valign='top' class='detail' nowrap>
      <?php xl('Sex','e'); ?>:
     </td>
     <td class='detail' valign='top'>
      <select name='form_sexes' title='<?php xl('To filter by sex','e'); ?>'>
<?php
  foreach (array(3 => xl('Men and Women'), 1 => xl('Women Only'), 2 => xl('Men Only')) as $key => $value) {
    echo "       <option value='$key'";
    if ($key == $form_sexes) echo " selected";
    echo ">$value</option>\n";
  }
?>
      </select>
     </td>
    </tr>
    <tr>
     <td valign='top' class='detail' nowrap>
      <?php xl('Facility','e'); ?>:
     </td>
     <td valign='top' class='detail'>
<?php
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility ORDER BY name";
 $fres = sqlStatement($query);
 echo "      <select name='form_facility'>\n";
 echo "       <option value=''>-- All Facilities --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "       <option value='$facid'";
  if ($facid == $_POST['form_facility']) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "      </select>\n";
?>
     </td>
    </tr>
    <tr>
     <td colspan='2' class='detail' nowrap>
      <?php xl('From','e'); ?>
      <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
      <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
       id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
       title='<?php xl('Click here to choose a date','e'); ?>'>
      <?php xl('To','e'); ?>
      <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $to_date ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
      <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
       id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
       title='<?php xl('Click here to choose a date','e'); ?>'>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('To','e'); ?>:
  </td>
  <td colspan='3' valign='top' class='detail' nowrap>
<?php
foreach (array(1 => 'Screen', 2 => 'Printer', 3 => 'Export File') as $key => $value) {
  echo "   <input type='radio' name='form_output' value='$key'";
  if ($key == $form_output) echo ' checked';
  echo " />$value &nbsp;";
}
?>
  </td>
  <td align='right' valign='top' class='detail' nowrap>
   <input type='submit' name='form_submit' value='<?php xl('Submit','e'); ?>'
    title='<?php xl('Click to generate the report','e'); ?>' />
  </td>
 </tr>
 <tr>
  <td colspan='5' height="1">
  </td>
 </tr>
</table>
<?php
  } // end not export

  if ($_POST['form_submit']) {
    $pd_fields = '';
    foreach ($arr_show as $askey => $asval) {
      if (substr($askey, 0, 1) == '.') continue;
      if ($askey == 'regdate' || $askey == 'sex' || $askey == 'DOB' ||
        $askey == 'lname' || $askey == 'fname' || $askey == 'mname' ||
        $askey == 'contrastart' || $askey == 'referral_source') continue;
      $pd_fields .= ', pd.' . $askey;
    }

    $sexcond = '';
    if ($form_sexes == '1') $sexcond = "AND pd.sex NOT LIKE 'Male' ";
    else if ($form_sexes == '2') $sexcond = "AND pd.sex LIKE 'Male' ";

    // Get referrals and related patient data.
    if ($form_by === '9' || $form_by === '10' || $form_by === '20' || $form_by === '1') {

      $exttest = "t.refer_external = '1'";
      $datefld = "t.refer_date";

      if ($form_by === '9') {
        $exttest = "t.refer_external = '0'";
      }
      else if ($form_by === '20') {
        $datefld = "t.reply_date";
      }

      $query = "SELECT " .
        "t.refer_related_code, t.pid, pd.regdate, pd.referral_source, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart$pd_fields " .
        "FROM transactions AS t " .
        "JOIN patient_data AS pd ON pd.pid = t.pid $sexcond" .
        "WHERE t.title = 'Referral' AND $datefld IS NOT NULL AND " .
        "$datefld >= '$from_date' AND $datefld <= '$to_date' AND $exttest " .
        "ORDER BY t.pid, t.id";
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        process_referral($row);
      }
    }
    /*****************************************************************
    else if ($form_by === '12') {
      // We are reporting on a date range, and assume the applicable date is
      // the issue start date which is presumably also the date of pre-
      // abortion counseling.  The issue end date and the surgery date are
      // not of interest here.
      $query = "SELECT " .
        "l.type, l.begdate, l.pid, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, pd.userlist5, " .
        "pd.country_code, pd.status, pd.state, pd.occupation, " .
        "lg.client_status, lg.ab_location " .
        "FROM lists AS l " .
        "JOIN patient_data AS pd ON pd.pid = l.pid $sexcond" .
        "LEFT OUTER JOIN lists_ippf_gcac AS lg ON l.type = 'ippf_gcac' AND lg.id = l.id " .
        // "LEFT OUTER JOIN lists_ippf_con  AS lc ON l.type = 'contraceptive' AND lc.id = l.id " .
        "WHERE l.begdate >= '$from_date' AND l.begdate <= '$to_date' AND " .
        "l.activity = 1 AND l.type = 'ippf_gcac' " .
        "ORDER BY l.pid, l.id";
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        process_issue($row);
      }
    }
    *****************************************************************/

    // else {

    if ($form_by === '104' || $form_by === '105') {
      $query = "SELECT " .
        "d.name, d.related_code, ds.pid, ds.quantity, " . 
        "pd.regdate, pd.referral_source, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart$pd_fields " .
        "FROM drug_sales AS ds " .
        "JOIN drugs AS d ON d.drug_id = ds.drug_id " .
        "JOIN patient_data AS pd ON pd.pid = ds.pid $sexcond" .
        "WHERE ds.sale_date IS NOT NULL AND ds.pid != 0 AND " .
        "ds.sale_date >= '$from_date' AND ds.sale_date <= '$to_date' " .
        "ORDER BY ds.pid, ds.sale_id";
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        $key = "(Unspecified)";
        if (!empty($row['related_code'])) {
          $relcodes = explode(';', $row['related_code']);
          foreach ($relcodes as $codestring) {
            if ($codestring === '') continue;
            list($codetype, $code) = explode(':', $codestring);
            if ($codetype !== 'IPPF') continue;
            $key = getContraceptiveMethod($code);
            if (!empty($key)) break;
            $key = "(No Method)";
          }
        }
        if ($form_by === '104') $key .= " / " . $row['name'];
        loadColumnData($key, $row, $row['quantity']);
      }
    }

    if ($form_by !== '9' && $form_by !== '10' && $form_by !== '20' &&
      $form_by !== '104' && $form_by !== '105')
    {
      // This gets us all MA codes, with encounter and patient
      // info attached and grouped by patient and encounter.
      $query = "SELECT " .
        "fe.pid, fe.encounter, fe.date AS encdate, pd.regdate, " .
        "f.user AS provider, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart, pd.referral_source$pd_fields, " .
        "b.code_type, b.code, c.related_code, lo.title AS lo_title " .
        "FROM form_encounter AS fe " .
        "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
        "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
        "JOIN patient_data AS pd ON pd.pid = fe.pid $sexcond" .
        "LEFT OUTER JOIN billing AS b ON " .
        "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
        "AND b.code_type = 'MA' " .
        "LEFT OUTER JOIN codes AS c ON b.code_type = 'MA' AND c.code_type = '12' AND " .
        "c.code = b.code AND c.modifier = b.modifier " .
        "LEFT OUTER JOIN list_options AS lo ON " .
        "lo.list_id = 'superbill' AND lo.option_id = c.superbill " .
        "WHERE fe.date >= '$from_date 00:00:00' AND " .
        "fe.date <= '$to_date 23:59:59' ";

      if ($form_facility) {
        $query .= "AND fe.facility_id = '$form_facility' ";
      }
      $query .= "ORDER BY fe.pid, fe.encounter, b.code";
      $res = sqlStatement($query);

      $prev_encounter = 0;

      while ($row = sqlFetchArray($res)) {
        if ($row['encounter'] != $prev_encounter) {
          $prev_encounter = $row['encounter'];
          process_visit($row);
        }
        if ($row['code_type'] === 'MA') {
          process_ma_code($row);
          if (!empty($row['related_code'])) {
            $relcodes = explode(';', $row['related_code']);
            foreach ($relcodes as $codestring) {
              if ($codestring === '') continue;
              list($codetype, $code) = explode(':', $codestring);
              if ($codetype !== 'IPPF') continue;
              process_ippf_code($row, $code);
            }
          }
        }
      } // end while
    } // end if

    // Sort everything by key for reporting.
    ksort($areport);
    foreach ($arr_titles as $atkey => $dummy) ksort($arr_titles[$atkey]);

    if ($form_output != 3) {
      echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

    genStartRow("bgcolor='#dddddd'");

    // If the key is an MA or IPPF code, then add a column for its description.
    if ($form_by === '4'  || $form_by === '102' || $form_by === '9' ||
        $form_by === '10' || $form_by === '20')
    {
      genHeadCell(array($arr_by[$form_by], xl('Description')));
    } else {
      genHeadCell($arr_by[$form_by]);
    }

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
      // if ($value == '1') { // Total Services
      if ($value == '.total') { // Total Services
        genHeadCell(xl('Total'));
      }
      // else if ($value == '2') { // Age
      else if ($value == '.age') { // Age
        genHeadCell(xl('0-10' ), true);
        genHeadCell(xl('11-14'), true);
        genHeadCell(xl('15-19'), true);
        genHeadCell(xl('20-24'), true);
        genHeadCell(xl('25-29'), true);
        genHeadCell(xl('30-34'), true);
        genHeadCell(xl('35-39'), true);
        genHeadCell(xl('40-44'), true);
        genHeadCell(xl('45+'  ), true);
      }

      else if ($arr_show[$value]['list_id']) {
        foreach ($arr_titles[$value] as $key => $dummy) {
          genHeadCell(getListTitle($arr_show[$value]['list_id'],$key), true);
        }
      }
      else if (!empty($arr_titles[$value])) {
        foreach ($arr_titles[$value] as $key => $dummy) {
          genHeadCell($key, true);
        }
      }
    }

    if ($form_output != 3) {
      genHeadCell(xl('Total'), true);
    }

    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
      $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

      $dispkey = $key;

      // If the key is an MA or IPPF code, then add a column for its description.
      if ($form_by === '4' || $form_by === '102' || $form_by === '9' ||
          $form_by === '10' || $form_by === '20')
      {
        $dispkey = array($key, '');
        $type = $form_by === '102' ? 12 : 11; // MA or IPPF
        $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
          "code_type = '$type' AND code = '$key' ORDER BY id LIMIT 1");
        if (!empty($crow['code_text'])) $dispkey[1] = $crow['code_text'];
      }

      genStartRow("bgcolor='$bgcolor'");

      genAnyCell($dispkey, false, 'detail');

      // This is the column index for accumulating column totals.
      $cnum = 0;
      $totalsvcs = $areport[$key]['.wom'] + $areport[$key]['.men'];

      // Generate data for this row.
      foreach ($form_show as $value) {
        // if ($value == '1') { // Total Services
        if ($value == '.total') { // Total Services
          genNumCell($totalsvcs, $cnum++);
        }
        else if ($value == '.age') { // Age
          for ($i = 0; $i < 9; ++$i) {
            genNumCell($areport[$key]['.age'][$i], $cnum++);
          }
        }
        else if (!empty($arr_titles[$value])) {
          foreach ($arr_titles[$value] as $title => $dummy) {
            genNumCell($areport[$key][$value][$title], $cnum++);
          }
        }
      }

      // Write the Total column data.
      if ($form_output != 3) {
        $atotals[$cnum] += $totalsvcs;
        genAnyCell($totalsvcs, true, 'dehead');
      }

      genEndRow();
    } // end foreach

    if ($form_output != 3) {
      // Generate the line of totals.
      genStartRow("bgcolor='#dddddd'");

      // If the key is an MA or IPPF code, then add a column for its description.
      if ($form_by === '4' || $form_by === '102' || $form_by === '9' ||
          $form_by === '10' || $form_by === '20')
      {
        genHeadCell(array(xl('Totals'), ''));
      } else {
        genHeadCell(xl('Totals'));
      }

      for ($cnum = 0; $cnum < count($atotals); ++$cnum) {
        genHeadCell($atotals[$cnum], true);
      }
      genEndRow();
      // End of table.
      echo "</table>\n";
    }

  } // end of if refresh or export

  if ($form_output != 3) {
?>
</form>
</center>

<script language='JavaScript'>
 selreport();
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php if ($form_output == 2) { ?>
 window.print();
<?php } ?>
</script>

</body>
</html>
<?php
  } // end not export
?>
