<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../../globals.php");
 include_once("$srcdir/lists.inc");
 include_once("$srcdir/acl.inc");

 $arroccur = array(
  0   => 'Unknown or N/A',
  1   => 'First',
  2   => 'Second',
  3   => 'Third',
  4   => 'Chronic/Recurrent',
  5   => 'Acute on Chronic'
 );

 // Check authorization.
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) die("Not authorized.");

 // get issues
 $pres = sqlStatement("SELECT * FROM lists WHERE pid = $pid " .
  "ORDER BY type, begdate");
?>
<html>

<head>

<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>
<title><? xl('Patient Issues','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
 location.reload();
}

// Process click on issue title.
function dopclick(id) {
 dlgopen('add_edit_issue.php?issue=' + id, '_blank', 600, 475);
}

// Process click on number of encounters.
function doeclick(id) {
 dlgopen('../problem_encounter.php?issue=' + id, '_blank', 700, 500);
 // window.open('../problem_encounter.php?issue=' + id, '_blank',
 //  'menubar=1,resizable=1,scrollbars=1');
 return false;
}

// Add Encounter button is clicked.
function newEncounter() {
 var f = document.forms[0];
<?php if ($GLOBALS['concurrent_layout']) { ?>
 parent.left_nav.setRadio(window.name, 'nen');
 location.href='../../forms/newpatient/new.php?autoloaded=1&calenc=';
<?php } else { ?>
 top.Title.location.href='../encounter/encounter_title.php';
 top.Main.location.href='../encounter/patient_encounter.php?mode=new';
<?php } ?>
}

</script>

</head>

<body <?echo $top_bg_line;?>>
<form method='post' action='stats_full.php'>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <td><? xl('Type','e'); ?></td>
  <td><? xl('Title','e'); ?></td>
  <td><? xl('Begin','e'); ?></td>
  <td><? xl('End','e'); ?></td>
  <td><? xl('Diag','e'); ?></td>
  <td><? xl('Occurrence','e'); ?></td>
<? if ($GLOBALS['athletic_team']) { ?>
  <td><? xl('Missed','e'); ?></td>
<? } else { ?>
  <td><? xl('RefBy','e'); ?></td>
<? } ?>
  <td><? xl('Comments','e'); ?></td>
  <td><? xl('Enc','e'); ?></td>
 </tr>
<?
 $encount = 0;
 $lasttype = "";
 while ($row = sqlFetchArray($pres)) {
  if ($lasttype != $row['type']) {
   $encount = 0;
   $lasttype = $row['type'];

   /****
   $disptype = $lasttype;
   switch ($lasttype) {
    case "allergy"        : $disptype = "Allergies"       ; break;
    case "problem"        :
    case "medical_problem": $disptype = "Medical Problems"; break;
    case "medication"     : $disptype = "Medications"     ; break;
    case "surgery"        : $disptype = "Surgeries"       ; break;
   }
   ****/
   $disptype = $ISSUE_TYPES[$lasttype][0];

   echo " <tr class='detail'>\n";
   echo "  <td valign='top' colspan='8'><b>$disptype</b></td>\n";
   echo " </tr>\n";
  }

  $rowid = $row['id'];

  $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

  $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
   "list_id = $rowid");

  ++$encount;
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

  echo " <tr class='detail'>\n";
  echo "  <td valign='top'>&nbsp;</td>\n";
  if ($thisauth == 'write') {
   echo "  <td valign='top' id='p_$rowid' onclick='dopclick($rowid)' bgcolor='$bgcolor'>";
   echo "<a href='' onclick='return false'>$disptitle</a></td>\n";
  } else {
   echo "  <td valign='top' id='p_$rowid' bgcolor='$bgcolor'>";
   echo "$disptitle</td>\n";
  }
  echo "  <td valign='top' bgcolor='$bgcolor'>" . $row['begdate'] . "&nbsp;</td>\n";
  echo "  <td valign='top' bgcolor='$bgcolor'>" . $row['enddate'] . "&nbsp;</td>\n";
  echo "  <td valign='top' bgcolor='$bgcolor' nowrap>" . $row['diagnosis'] . "</td>\n";
  echo "  <td valign='top' bgcolor='$bgcolor' nowrap>" . $arroccur[$row['occurrence']] . "</td>\n";
  if ($GLOBALS['athletic_team'])
   echo "  <td valign='top' align='center' bgcolor='$bgcolor'>" . $row['extrainfo'] . "</td>\n"; // games missed
  else
   echo "  <td valign='top' bgcolor='$bgcolor'>" . $row['referredby'] . "</td>\n";
  echo "  <td valign='top' bgcolor='$bgcolor'>" . $row['comments'] . "</td>\n";
  echo "  <td valign='top' align='center' id='e_$rowid' onclick='doeclick($rowid)' bgcolor='$bgcolor'>" .
       "<a href='' onclick='return false'>&nbsp;" . $ierow['count'] . "&nbsp;</a></td>\n";
  echo " </tr>\n";
 }
?>
</table>

<center><p>
 <input type='button' value='<?xl('Add Issue','e'); ?>'
  onclick='dopclick(0)'
  style='background-color:transparent' /> &nbsp;
 <input type='button' value='<?xl('Add Encounter','e'); ?>'
  onclick='newEncounter()'
  style='background-color:transparent' /> &nbsp;
 <input type='button' value='<?xl('To History','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
  onclick="parent.left_nav.setRadio(window.name,'his');location='../history/history_full.php';"
<?php } else { ?>
  onclick="location='../history/history_full.php';"
<?php } ?>
  style='background-color:transparent' /> &nbsp;
 <input type='button' value='<?xl('Back','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
  onclick="parent.left_nav.setRadio(window.name,'dem');location='demographics.php';"
<?php } else { ?>
  onclick='location="patient_summary.php"'
<?php } ?>

  style='background-color:transparent' />
</p></center>

</form>
</body>
</html>
