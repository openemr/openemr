<?php
// Copyright (C) 2008, 2009 Rod Roark <rod@sunsetsystems.com>
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

$out = "";
$indent = 0;

// Add a string to output with some basic sanitizing.
function Add($tag, $text) {
  global $out, $indent;
  $text = trim(str_replace(array("\r", "\n", "\t"), " ", $text));
  if ($text) {
    for ($i = 0; $i < $indent; ++$i) $out .= "\t";
    $out .= "<$tag>$text</$tag>\n";
  }
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
  $sex = strtoupper(substr(trim($field), 0, 1));
  if ($sex != "M" && $sex != "F") $sex = "U";
  return $sex;
}

// Translate a date.
function LWDate($field) {
  return fixDate($field);
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

function endClient($pid) {
  // Output issues.
  $ires = sqlStatement("SELECT " .
    "l.id, l.type, l.begdate, l.enddate, l.title, l.diagnosis, " .
    "c.prev_method, c.new_method, c.reason_chg, c.reason_term, c.risks, " .
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
    Add('IssueType'     , $irow['type']);
    Add('IssueStartDate', $irow['begdate']);
    Add('IssueEndDate'  , $irow['enddate']);
    Add('IssueTitle'    , $irow['title']);
    Add('IssueDiagnosis', $irow['diagnosis']);
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
        Add('IssueCodeValue', $tmp);
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
      Add('emrVisitId', $ierow['encounter']);
      CloseTag('IMS_eMRUpload_VisitIssue');
    }
    CloseTag('IMS_eMRUpload_Issue');
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

  $res = sqlStatement($query);

  $last_pid = -1;
  $last_facility = -1;

  while ($row = sqlFetchArray($res)) {
    if ($row['facility_id'] != $last_facility) {
      if ($last_facility >= 0) {
        if ($last_pid >= 0) endClient($last_pid);
        $last_pid = -1;
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
      Add('City'                     , $row['city']);
      Add('PostCode'                 , $row['postal_code']);
    }

    if ($row['pid'] != $last_pid) {
      if ($last_pid >= 0) endClient($last_pid);
      $last_pid = $row['pid'];

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
      Add('RegisteredOn'    , $row['regdate']);
      Add('LastUpdated'     , $row['last_update']);
      Add('NewAcceptorDate' , $row['contrastart']);
      if (!empty($crow['new_method'])) {
        $methods = explode('|', $crow['new_method']);
        foreach ($methods as $method) {
          Add('CurrentMethod', $method);
        }
      }
      Add('Dob'             , $row['DOB']);
//    Add('DobType'         , "TBD"); // TBD
      Add('Pregnancies', 0 + getTextListValue($hrow['genobshist'],'npreg')); // number of pregnancies
      Add('Children'   , 0 + getTextListValue($hrow['genobshist'],'nlc'));   // number of living children
      Add('Abortions'  , 0 + getTextListValue($hrow['genabohist'],'nia'));   // number of induced abortions
      Add('Education'  , $row['education']);
    }

    // Starting a new visit (encounter).
    OpenTag('IMS_eMRUpload_Visit');
    Add('VisitDate' , $row['date']);
    Add('emrVisitId', $row['encounter']);

    $query = "SELECT b.code_type, b.code, b.units, b.fee, c.related_code " .
      "FROM billing AS b, codes AS c WHERE " .
      "b.pid = '$last_pid' AND b.encounter = '" . $row['encounter'] . "' AND " .
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
          Add('Type'                , $codetype);
          Add('IppfServiceProductId', $code);
          Add('IppfQuantity'        , $brow['units']);
          Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
          Add('Amount'              , $brow['fee']);
          CloseTag('IMS_eMRUpload_Service');
        } // end foreach
      } // end if related code
    } // end while billing row found

    CloseTag('IMS_eMRUpload_Visit');
  }

  if ($last_pid >= 0) endClient($last_pid);
  if ($last_facility >= 0) endFacility();

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
