<?php
// Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This script creates an export file and sends it to the users's
// browser for download.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");

if (!acl_check('admin', 'super')) die("Not authorized!");

//////////////////////////////////////////////////////////////////////
//                            XML Stuff                             //
//////////////////////////////////////////////////////////////////////

$out = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
$indent = 0;

// Add a string to output with some basic sanitizing.
function Add($tag, $text) {
  global $out, $indent;
  $text = trim(str_replace(array("\r", "\n", "\t"), " ", $text));
  $text = substr(htmlspecialchars($text, ENT_NOQUOTES), 0, 50);
  if (/* $text */ true) {
    if ($text === 'NULL') $text = '';
    for ($i = 0; $i < $indent; ++$i) $out .= "\t";
    $out .= "<$tag>$text</$tag>\n";
  }
}

function AddIfPresent($tag, $text) {
  if (isset($text) && $text !== '') Add($tag, $text);
}

function OpenTag($tag) {
  global $out, $indent;
  for ($i = 0; $i < $indent; ++$i) $out .= "\t";
  ++$indent;
  $out .= "<$tag>\n";
}

function CloseTag($tag) {
  global $out, $indent;
  --$indent;
  for ($i = 0; $i < $indent; ++$i) $out .= "\t";
  $out .= "</$tag>\n";
}

// Remove all non-digits from a string.
function Digits($field) {
  return preg_replace("/\D/", "", $field);
}

// Translate sex.
function Sex($field) {
  /*******************************************************************
  $sex = strtoupper(substr(trim($field), 0, 1));
  if ($sex != "M" && $sex != "F") $sex = "U";
  return $sex;
  *******************************************************************/
  return mappedOption('sex', $field);
}

// Translate a date.
function LWDate($field) {
  return fixDate($field);
}

function xmlTime($str, $default='9999-12-31T23:59:59') {
  if (empty($default)) $default = '1800-01-01T00:00:00';
  if (strlen($str) < 10 || substr($str, 0, 4) == '0000')
    $str = $default;
  else if (strlen($str) > 10)
    $str = substr($str, 0, 10) . 'T' . substr($str, 11);
  else
    $str .= 'T00:00:00';
  // Per discussion with Daniel 2009-05-12, replace zero day or month with 01.
  $str = preg_replace('/-00/', '-01', $str);
  return $str;
}

//////////////////////////////////////////////////////////////////////

// Utility function to get the value for a specified key from a string
// whose format is key:value|key:value|...
//
function getTextListValue($string, $key) {
  $tmp = explode('|', $string);
  foreach ($tmp as $value) {
    if (preg_match('/^(\w+?):(.*)$/', $value, $matches)) {
      if ($matches[1] == $key) return $matches[2];
    }
  }
  return '';
}

// Return the mapped list item ID if there is one, else the option_id.
// Or return 9 if the option_id is empty (unspecified).
//
function mappedOption($list_id, $option_id, $default='9') {
  if ($option_id === '') return $default;
  $row = sqlQuery("SELECT mapping FROM list_options WHERE " .
    "list_id = '$list_id' AND option_id = '$option_id' LIMIT 1");
  if (empty($row)) return $option_id; // should not happen
  // return ($row['mapping'] === '') ? $option_id : $row['mapping'];
  $maparr = explode(':', $row['mapping']);
  return ($maparr[0] === '') ? $option_id : $maparr[0];
}

// Like the above but given a layout item form and field name.
// Or return 9 for a list whose id is empty (unspecified).
//
function mappedFieldOption($form_id, $field_id, $option_id) {
  $row = sqlQuery("SELECT list_id FROM " .
    "layout_options WHERE " .
    "form_id = '$form_id' AND " .
    "field_id = '$field_id' " .
    "LIMIT 1");
  if (empty($row)) return $option_id; // should not happen
  $list_id = $row['list_id'];
  if ($list_id === '') return $option_id;
  if ($option_id === '') return '9';
  $row = sqlQuery("SELECT mapping FROM " .
    "list_options WHERE " .
    "list_id = '$list_id' AND " .
    "option_id = '$option_id' " .
    "LIMIT 1");
  if (empty($row)) return $option_id; // should not happen
  // return ($row['mapping'] === '') ? $option_id : $row['mapping'];
  $maparr = explode(':', $row['mapping']);
  return ($maparr[0] === '') ? $option_id : $maparr[0];
}

function exportEncounter($pid, $encounter, $date) {
  // Starting a new visit (encounter).
  OpenTag('IMS_eMRUpload_Visit');
  Add('VisitDate' , xmlTime($date));
  Add('emrVisitId', $encounter);

  // Dump IPPF services.
  $query = "SELECT b.code_type, b.code, b.units, b.fee, c.related_code " .
    "FROM billing AS b, codes AS c WHERE " .
    "b.pid = '$pid' AND b.encounter = '$encounter' AND " .
    "b.activity = 1 AND " .
    "c.code_type = '12' AND c.code = b.code AND c.modifier = b.modifier ";
  $bres = sqlStatement($query);
  while ($brow = sqlFetchArray($bres)) {
    if (!empty($brow['related_code'])) {
      $relcodes = explode(';', $brow['related_code']);
      foreach ($relcodes as $codestring) {
        if ($codestring === '') continue;
        list($codetype, $code) = explode(':', $codestring);
        if ($codetype !== 'IPPF') continue;
        // Starting a new service (IPPF code).
        OpenTag('IMS_eMRUpload_Service');
        Add('IppfServiceProductId', $code);
        Add('Type'                , '0'); // 0=service, 1=product, 2=diagnosis, 3=referral
        Add('IppfQuantity'        , $brow['units']);
        Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
        Add('Amount'              , $brow['fee']);
        CloseTag('IMS_eMRUpload_Service');
      } // end foreach
    } // end if related code
  } // end while billing row found

  // Dump products.
  $query = "SELECT drug_id, quantity, fee FROM drug_sales WHERE " .
    "pid = '$pid' AND encounter = '$encounter' " .
    "ORDER BY drug_id, sale_id";
  $pres = sqlStatement($query);
  while ($prow = sqlFetchArray($pres)) {
    OpenTag('IMS_eMRUpload_Service');
    Add('IppfServiceProductId', $prow['drug_id']);
    Add('Type'                , '1'); // 0=service, 1=product, 2=diagnosis, 3=referral
    Add('IppfQuantity'        , $prow['quantity']);
    Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
    Add('Amount'              , $prow['fee']);
    CloseTag('IMS_eMRUpload_Service');
  } // end while drug_sales row found

  // Dump diagnoses.
  $query = "SELECT code FROM billing WHERE " .
    "pid = '$pid' AND encounter = '$encounter' AND " .
    "code_type = 'ICD9' AND activity = 1 ORDER BY code, id";
  $dres = sqlStatement($query);
  while ($drow = sqlFetchArray($dres)) {
    OpenTag('IMS_eMRUpload_Service');
    Add('IppfServiceProductId', $drow['code']);
    Add('Type'                , '2'); // 0=service, 1=product, 2=diagnosis, 3=referral
    Add('IppfQuantity'        , '1');
    Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
    Add('Amount'              , '0');
    CloseTag('IMS_eMRUpload_Service');
  } // end while billing row found

  // Export referrals.  Match by date.  Export code type 3 and
  // the Requested Service which should be an IPPF code.
  $query = "SELECT refer_related_code FROM transactions WHERE " .
    "pid = '$pid' AND refer_date = '$date' AND " .
    "refer_related_code != '' " .
    "ORDER BY id";
  $tres = sqlStatement($query);
  while ($trow = sqlFetchArray($tres)) {
    $relcodes = explode(';', $trow['refer_related_code']);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      if ($codetype == 'REF') {
        // This is the expected case; a direct IPPF code is obsolete.
        $rrow = sqlQuery("SELECT related_code FROM codes WHERE " .
          "code_type = '16' AND code = '$code' AND active = 1 " .
          "ORDER BY id LIMIT 1");
        if (!empty($rrow['related_code'])) {
          list($codetype, $code) = explode(':', $rrow['related_code']);
        }
      }
      if ($codetype !== 'IPPF') continue;
      OpenTag('IMS_eMRUpload_Service');
      Add('IppfServiceProductId', $code);
      Add('Type'                , '3'); // 0=service, 1=product, 2=diagnosis, 3=referral
      Add('IppfQuantity'        , '1');
      Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
      Add('Amount'              , '0');
      CloseTag('IMS_eMRUpload_Service');
    } // end foreach
  } // end referral

  CloseTag('IMS_eMRUpload_Visit');
}

function endClient($pid, &$encarray) {
  // Output issues.
  $ires = sqlStatement("SELECT " .
    "l.id, l.type, l.begdate, l.enddate, l.title, l.diagnosis, " .
    "c.prev_method, c.new_method, c.reason_chg, c.reason_term, " .
    "c.hor_history, c.hor_lmp, c.hor_flow, c.hor_bleeding, c.hor_contra, " .
    "c.iud_history, c.iud_lmp, c.iud_pain, c.iud_upos, c.iud_contra, " .
    "c.sur_screen, c.sur_anes, c.sur_type, c.sur_post_ins, c.sur_contra, " .
    "c.nat_reason, c.nat_method, c.emg_reason, c.emg_method, " .
    "g.client_status, g.in_ab_proc, g.ab_types, g.ab_location, g.pr_status, " .
    "g.gest_age_by, g.sti, g.prep_procs, g.reason, g.exp_p_i, g.ab_contraind, " .
    "g.screening, g.pre_op, g.anesthesia, g.side_eff, g.rec_compl, g.post_op, " .
    "g.qc_ind, g.contrameth, g.fol_compl " .
    "FROM lists AS l " .
    "LEFT JOIN lists_ippf_con  AS c ON l.type = 'contraceptive' AND c.id = l.id " .
    "LEFT JOIN lists_ippf_gcac AS g ON l.type = 'ippf_gcac' AND g.id = l.id " .
    "WHERE l.pid = '$pid' " .
    "ORDER BY l.begdate");

  while ($irow = sqlFetchArray($ires)) {
    OpenTag('IMS_eMRUpload_Issue');
    Add('IssueType'     , substr($irow['type'], 0, 15)); // per email 2009-03-20
    Add('emrIssueId'    , $irow['id']);
    Add('IssueStartDate', xmlTime($irow['begdate'], 0));
    Add('IssueEndDate'  , xmlTime($irow['enddate']));
    Add('IssueTitle'    , $irow['title']);
    Add('IssueDiagnosis', $irow['diagnosis']);
    $form_id = ($irow['type'] == 'ippf_gcac') ? 'GCA' : 'CON';
    foreach ($irow AS $key => $value) {
      if (empty($value)) continue;
      if ($key == 'id' || $key == 'type' || $key == 'begdate' ||
        $key == 'enddate' || $key == 'title' || $key == 'diagnosis')
        continue;
      $avalues = explode('|', $value);
      foreach ($avalues as $tmp) {
        OpenTag('IMS_eMRUpload_IssueData');
        // TBD: Add IssueCodeGroup to identify the list, if any???
        Add('IssueCodeGroup', '?');
        Add('IssueCode', $key);
        Add('IssueCodeValue', mappedFieldOption($form_id, $key, $tmp));
        CloseTag('IMS_eMRUpload_IssueData');
      }
    }
    // List the encounters linked to this issue.  We include pid
    // to speed up the search, as it begins the primary key.
    $ieres = sqlStatement("SELECT encounter FROM issue_encounter " .
      "WHERE pid = '$pid' AND list_id = '" . $irow['id'] . "' " .
      "ORDER BY encounter");
    while ($ierow = sqlFetchArray($ieres)) {
      OpenTag('IMS_eMRUpload_VisitIssue');
      Add('emrIssueId', $irow['id']);
      Add('emrVisitId', $ierow['encounter']);
      CloseTag('IMS_eMRUpload_VisitIssue');
    }
    CloseTag('IMS_eMRUpload_Issue');
  }

  // Loop on $encarray and generate an "issue" for each GCAC visit form,
  // similarly to the above.
  foreach ($encarray as $erow) {
    $fres = sqlStatement("SELECT form_id FROM forms WHERE " .
      "pid = '$pid' AND " .
      "encounter = '" . $erow['encounter'] . "' AND " .
      "formdir = 'LBFgcac' AND " .
      "deleted = 0 " .
      "ORDER BY id");
    // For each GCAC form in this encounter...
    while ($frow = sqlFetchArray($fres)) {
      $form_id = $frow['form_id'];
      OpenTag('IMS_eMRUpload_Issue');
      Add('IssueType'     , 'ippf_gcac');
      Add('emrIssueId'    , 10000000 + $form_id);
      Add('IssueStartDate', xmlTime($erow['date'], 0));
      Add('IssueEndDate'  , xmlTime(''));
      Add('IssueTitle'    , 'GCAC Visit Form');
      Add('IssueDiagnosis', '');
      $gres = sqlStatement("SELECT field_id, field_value FROM lbf_data WHERE " .
        "form_id = '$form_id' ORDER BY field_id");
      // For each data item in the form...
      while ($grow = sqlFetchArray($gres)) {
        $key = $grow['field_id'];
        $value = $grow['field_value'];
        if (empty($value)) continue;
        $avalues = explode('|', $value);
        foreach ($avalues as $tmp) {
          OpenTag('IMS_eMRUpload_IssueData');
          Add('IssueCodeGroup', '?');
          Add('IssueCode', $key);
          Add('IssueCodeValue', mappedFieldOption('LBFgcac', $key, $tmp));
          CloseTag('IMS_eMRUpload_IssueData');
        }
      }
      OpenTag('IMS_eMRUpload_VisitIssue');
      Add('emrIssueId', 10000000 + $form_id);
      Add('emrVisitId', $erow['encounter']);
      CloseTag('IMS_eMRUpload_VisitIssue');
      CloseTag('IMS_eMRUpload_Issue');
    }
  }

  CloseTag('IMS_eMRUpload_Client');
}

function endFacility() {
  global $beg_year, $beg_month;
  OpenTag('IMS_eMRUpload_Version');
  Add('XMLversionNumber', '1');
  Add('Period', sprintf('%04u-%02u-01T00:00:00', $beg_year, $beg_month));
  CloseTag('IMS_eMRUpload_Version');
  CloseTag('IMS_eMRUpload_Point');
}

if (!empty($form_submit)) {

  $beg_year  = $_POST['form_year'];
  $beg_month = $_POST['form_month'];
  $end_year = $beg_year;
  $end_month = $beg_month + 1;
  if ($end_month > 12) {
    $end_month = 1;
    ++$end_year;
  }

  /*******************************************************************
  $query = "SELECT " .
    "fe.facility_id, fe.pid, fe.encounter, fe.date, " .
    "f.name, f.street, f.city, f.state, f.postal_code, f.country_code, " .
    "f.federal_ein, " .
    "p.regdate, p.date AS last_update, p.contrastart, p.DOB, " .
    "p.userlist2 AS education " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN facility AS f ON f.id = fe.facility_id " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid WHERE " .
    sprintf("fe.date >= '%04u-%02u-01 00:00:00' AND ", $beg_year, $beg_month) .
    sprintf("fe.date < '%04u-%02u-01 00:00:00' ", $end_year, $end_month) .
    "ORDER BY fe.facility_id, fe.pid, fe.encounter";

  $query = "SELECT DISTINCT " .
    "fe.facility_id, fe.pid, " .
    "f.name, f.street, f.city, f.state, f.postal_code, f.country_code, " .
    "f.federal_ein, " .
    "p.regdate, p.date AS last_update, p.contrastart, p.DOB, " .
    "p.userlist2 AS education " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN facility AS f ON f.id = fe.facility_id " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid WHERE " .
    sprintf("fe.date >= '%04u-%02u-01 00:00:00' AND ", $beg_year, $beg_month) .
    sprintf("fe.date < '%04u-%02u-01 00:00:00' ", $end_year, $end_month) .
    "ORDER BY fe.facility_id, fe.pid";
  *******************************************************************/

  // $last_pid = -1;
  // $last_facility = -1;

  // Dump info for the main facility.
  $facrow = sqlQuery("SELECT * FROM facility ORDER BY " .
    "billing_location DESC, id ASC LIMIT 1");
  OpenTag('IMS_eMRUpload_Point');
  Add('ServiceDeliveryPointName' , $facrow['name']);
  // Add('EmrServiceDeliveryPointId', $facrow['id']);
  Add('EmrServiceDeliveryPointId', $facrow['facility_npi']);
  Add('Channel'                  , '01');
  Add('Latitude'                 , '222222'); // TBD: Add this to facility attributes
  Add('Longitude'                , '433333'); // TBD: Add this to facility attributes
  Add('Address'                  , $facrow['street']);
  Add('Address2'                 , '');
  Add('City'                     , $facrow['city']);
  Add('PostCode'                 , $facrow['postal_code']);

  $query = "SELECT DISTINCT " .
    "fe.pid, " .
    "p.regdate, p.date AS last_update, p.contrastart, p.DOB, p.sex, " .
    "p.city, p.state, p.occupation, p.status, p.ethnoracial, " .
    "p.interpretter, p.monthly_income, p.referral_source, p.pricelevel, " .
    "p.userlist1, p.userlist3, p.userlist4, p.userlist5, " .
    "p.usertext11, p.usertext12, p.usertext13, p.usertext14, p.usertext15, " .
    "p.usertext16, p.usertext17, p.usertext18, p.usertext19, p.usertext20, " .
    "p.userlist2 AS education " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid WHERE " .
    sprintf("fe.date >= '%04u-%02u-01 00:00:00' AND ", $beg_year, $beg_month) .
    sprintf("fe.date < '%04u-%02u-01 00:00:00' ", $end_year, $end_month) .
    "ORDER BY fe.pid";
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {

    /*****************************************************************
    if ($row['facility_id'] != $last_facility) {
      if ($last_facility >= 0) {
        endFacility();
      }
      $last_facility = $row['facility_id'];
      // Starting a new facility.
      OpenTag('IMS_eMRUpload_Point');
      Add('ServiceDeliveryPointName' , $row['name']);
      Add('EmrServiceDeliveryPointId', $row['facility_id']);
//    Add('EntityId'                 , $row['federal_ein']);
      Add('Channel'                  , '01');
      Add('Latitude'                 , '222222'); // TBD: Add this to facility attributes
      Add('Longitude'                , '433333'); // TBD: Add this to facility attributes
      Add('Address'                  , $row['street']);
      Add('Address2'                 , '');
      Add('City'                     , $row['city']);
      Add('PostCode'                 , $row['postal_code']);
    }
    *****************************************************************/

    $last_pid = $row['pid'];

    /*****************************************************************
    // Compute education: 0 = none, 1 = some, 9 = unassigned.
    // The MAs should be told to use "none" for no education.
    $education = 9;
    if (!empty($row['education'])) {
      if (preg_match('/^il/i', $row['education']) ||
          preg_match('/^no/i', $row['education']))
        $education = 0;
      else
        $education = 1;
    }
    *****************************************************************/
    $education = mappedOption('userlist2', $row['education']);

    // Get most recent contraceptive issue.
    $crow = sqlQuery("SELECT l.begdate, c.new_method " .
      "FROM lists AS l, lists_ippf_con AS c WHERE " .
      "l.pid = '$last_pid' AND c.id = l.id " .
      "ORDER BY l.begdate DESC LIMIT 1");

    // Get obstetric and abortion data from most recent static history.
    $hrow = sqlQuery("SELECT date, " .
      "usertext16 AS genobshist, " .
      "usertext17 AS genabohist " .
      "FROM history_data WHERE pid = '$last_pid' " .
      "ORDER BY date DESC LIMIT 1");

    // Starting a new client (patient).
    OpenTag('IMS_eMRUpload_Client');
    Add('emrClientId'     , $row['pid']);
    Add('RegisteredOn'    , xmlTime($row['regdate']));
    Add('LastUpdated'     , xmlTime($row['last_update']));
    Add('NewAcceptorDate' , xmlTime($row['contrastart']));

    // Get the current contraceptive method with greatest effectiveness.
    $methodid = '';
    $methodvalue = -999;
    if (!empty($crow['new_method'])) {
      $methods = explode('|', $crow['new_method']);
      /***************************************************************
      foreach ($methods as $method) {
        $lorow = sqlQuery("SELECT option_value FROM list_options WHERE " .
          "list_id = 'contrameth' AND option_id = '$method' LIMIT 1");
        $value = empty($lorow) ? 0 : (0 + $lorow['option_value']);
        if ($value > $methodvalue) {
          $methodid = $method;
          $methodvalue = $value;
        }
      }
      ***************************************************************/
      $methodid = mappedOption('contrameth', $methods[0]);
    }
    Add('CurrentMethod', $methodid);

    Add('Dob'        , xmlTime($row['DOB']));
    Add('DobType'    , "rel"); // rel=real, est=estimated
    Add('Pregnancies', 0 + getTextListValue($hrow['genobshist'],'npreg')); // number of pregnancies
    Add('Children'   , 0 + getTextListValue($hrow['genobshist'],'nlc'));   // number of living children
    Add('Abortions'  , 0 + getTextListValue($hrow['genabohist'],'nia'));   // number of induced abortions
    Add('Education'  , $education);
    Add('Demo5'      , Sex($row['sex']));

    // Things included if they are present (July 2010)
    AddIfPresent('City', $row['city']);
    AddIfPresent('State', mappedOption('state', $row['state'], ''));
    AddIfPresent('Occupation', mappedOption('occupations', $row['occupation'], ''));
    AddIfPresent('MaritalStatus', mappedOption('marital', $row['status'], ''));
    AddIfPresent('Ethnoracial', mappedOption('ethrace', $row['ethnoracial'], ''));
    AddIfPresent('Interpreter', $row['interpretter']);
    AddIfPresent('MonthlyIncome', $row['monthly_income']);
    AddIfPresent('ReferralSource', mappedOption('refsource', $row['referral_source'], ''));
    AddIfPresent('PriceLevel', mappedOption('pricelevel', $row['pricelevel'], ''));
    AddIfPresent('UserList1', mappedOption('userlist1', $row['userlist1'], ''));
    AddIfPresent('UserList3', mappedOption('userlist3', $row['userlist3'], ''));
    AddIfPresent('UserList4', mappedOption('userlist4', $row['userlist4'], ''));
    AddIfPresent('UserList5', mappedOption('userlist5', $row['userlist5'], ''));
    AddIfPresent('UserText11', $row['usertext11']);
    AddIfPresent('UserText12', $row['usertext12']);
    AddIfPresent('UserText13', $row['usertext13']);
    AddIfPresent('UserText14', $row['usertext14']);
    AddIfPresent('UserText15', $row['usertext15']);
    AddIfPresent('UserText16', $row['usertext16']);
    AddIfPresent('UserText17', $row['usertext17']);
    AddIfPresent('UserText18', $row['usertext18']);
    AddIfPresent('UserText19', $row['usertext19']);
    AddIfPresent('UserText20', $row['usertext20']);

    // Dump the visits for this patient.
    $query = "SELECT " .
      "encounter, date " .
      "FROM form_encounter WHERE " .
      // "pid = '$last_pid' AND facility_id = '$last_facility' " .
      "pid = '$last_pid' ";
    if (true) {
      // The new logic here is to restrict to the given date range.
      // Set the above to false if all visits are wanted.
      $query .= "AND " .
      sprintf("date >= '%04u-%02u-01 00:00:00' AND ", $beg_year, $beg_month) .
      sprintf("date < '%04u-%02u-01 00:00:00' ", $end_year, $end_month);
    }
    $query .= "ORDER BY encounter";

    // Add('Debug', $query); // debugging

    $eres = sqlStatement($query);
    $encarray = array();
    while ($erow = sqlFetchArray($eres)) {
      exportEncounter($last_pid, $erow['encounter'], $erow['date']);
      $encarray[] = $erow;
    }

    endClient($last_pid, $encarray);
  }

  // if ($last_facility >= 0) endFacility();
  endFacility();

  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Length: " . strlen($out));
  header("Content-Disposition: attachment; filename=export.xml");
  header("Content-Description: File Transfer");
  echo $out;

  exit(0);
}

$months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
  5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September',
 10 => 'October', 11 => 'November', 12 => 'December');

$selmonth = date('m') - 1;
$selyear  = date('Y') + 0;
if ($selmonth < 1) {
  $selmonth = 12;
  --$selyear;
}
?>
<html>

<head>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php xl('Backup','e'); ?></title>
</head>

<body class="body_top">
<center>
&nbsp;<br />
<form method='post' action='ippf_export.php'>

<table style='width:30em'>
 <tr>
  <td align='center'>
   <?php echo xl('Month'); ?>:
   <select name='form_month'>
<?php
foreach ($months as $key => $value) {
  echo "    <option value='$key'";
  if ($key == $selmonth) echo " selected";
  echo ">" . xl($value) . "</option>\n";
}
?>
   </select>
   <input type='text' name='form_year' size='4' value='<?php echo $selyear; ?>' />
   &nbsp;
   <input type='submit' name='form_submit' value='Generate XML' />
  </td>
 </tr>
</table>

</form>

</center>

</body>
</html>
