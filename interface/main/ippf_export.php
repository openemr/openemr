<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
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
        if ($last_pid >= 0) CloseTag('IMS_eMRUpload_ServiceDeliveryPointClients');
        $last_pid = -1;
        CloseTag('IMS_eMRUpload_ServiceDeliveryPoints');
      }
      $last_facility = $row['facility_id'];
      // Starting a new facility.
      OpenTag('IMS_eMRUpload_ServiceDeliveryPoints');
      Add('ServiceDeliveryPointName' , $row['name']);
      Add('EmrServiceDeliveryPointId', $row['facility_id']);
      Add('EntityId'                 , $row['federal_ein']);
      Add('Address'                  , $row['street']);
      Add('City'                     , $row['city']);
      Add('PostCode'                 , $row['postal_code']);
    }

    if ($row['pid'] != $last_pid) {
      if ($last_pid >= 0) CloseTag('IMS_eMRUpload_ServiceDeliveryPointClients');
      $last_pid = $row['pid'];

      // Get most recent contraceptive issue.
      $crow = sqlQuery("SELECT l.begdate, c.new_method " .
        "FROM lists AS l, lists_ippf_con AS c WHERE " .
        "l.pid = '$last_pid' " .
        "ORDER BY l.begdate DESC LIMIT 1");

      // Get most recent static history.
      $hrow = sqlQuery("SELECT date, " .
        "usertext12 AS pregnancies, " .
        "usertext13 AS children, " .
        "usertext14 AS abortions " .
        "FROM history_data WHERE pid = '$last_pid' " .
        "ORDER BY date DESC LIMIT 1");

      // Starting a new client (patient).
      OpenTag('IMS_eMRUpload_ServiceDeliveryPointClients');
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
//    Add('isNew'           , "TBD"); // TBD - what is this?
      Add('Pregnancies', 0 + $hrow['pregnancies']); // History support TBD
      Add('Children'   , 0 + $hrow['children']);    // History support TBD
      Add('Abortions'  , 0 + $hrow['abortions']);   // History support TBD
      Add('Education'  , $row['education']);
    }

    // Starting a new visit (encounter).
    OpenTag('IMS_eMRUpload_ServiceDeliveryPointStatistics');
    Add('VisitDate' , $row['date']);
    Add('emrVisitId', $row['encounter']);
    Add('Type'      , "TBD"); // TBD

    $query = "SELECT b.code, b.units, b.fee, c.related_code " .
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
          OpenTag('IMS_eMRUpload_ServiceDeliveryPointService');
          Add('IppfServiceProductId', $code);
          Add('IppfQuantity'        , $brow['units']);
          Add('CurrID'              , "TBD"); // TBD: Currency e.g. USD
          Add('Amount'              , $brow['fee']);
//        Add('IssueID'             , "TBD"); // TBD ?
//        Add('IssueQuantity'       , "TBD"); // TBD ?
          CloseTag('IMS_eMRUpload_ServiceDeliveryPointService');
        } // end foreach
      } // end if related code
    } // end while billing row found

    CloseTag('IMS_eMRUpload_ServiceDeliveryPointStatistics');
  }

  if ($last_pid >= 0) CloseTag('IMS_eMRUpload_ServiceDeliveryPointClients');
  if ($last_facility >= 0) CloseTag('IMS_eMRUpload_ServiceDeliveryPoints');

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
   <input type='text' name='form_year' value='<?php echo $selyear; ?>' />
   &nbsp;
   <input type='submit' name='form_submit' value='Generate XML' />
  </td>
 </tr>
</table>

</form>

</center>

</body>
</html>
