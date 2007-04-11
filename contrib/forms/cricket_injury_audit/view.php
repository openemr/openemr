<?php
//////////////////////////////////////////////////////////////////////
// ------------------ DO NOT MODIFY VIEW.PHP !!! ---------------------
// View.php is an exact duplicate of new.php.  If you wish to make
// any changes, then change new.php and either (recommended) make
// view.php a symbolic link to new.php, or copy new.php to view.php.
//
// And if you check in a change to either module, be sure to check
// in the other (identical) module also.
//
// This nonsense will go away if we ever move to subversion.
//////////////////////////////////////////////////////////////////////

// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once('cia.inc.php');

$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname) {
 $tmp = $_POST[$rbname];
 if (! $tmp) $tmp = '0';
 return "'$tmp'";
}

function cbvalue($cbname) {
 return $_POST[$cbname] ? '1' : '0';
}

function invalue($inname) {
 return (int) trim($_POST[$inname]);
}

function txvalue($txname) {
 return "'" . trim($_POST[$txname]) . "'";
}

function rbinput($name, $value, $desc, $colname) {
 global $row;
 $ret  = "<input type='radio' name='$name' value='$value'";
 if ($row[$colname] == $value) $ret .= " checked";
 $ret .= " />$desc";
 return $ret;
}

function rbcell($name, $value, $desc, $colname) {
 return "     <td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname) {
 global $row;
 $ret  = "<input type='checkbox' name='$name' value='1'";
 if ($row[$colname]) $ret .= " checked";
 $ret .= " />";
 return $ret;
}

function cbcell($name, $desc, $colname) {
  return "     <td width='25%' nowrap>" . cbinput($name, $colname) .
    "$desc</td>\n";
}

// Generate HTML table entries for the designated set of radio buttons.
//
function genRadios($group, &$arr) {
  $i = 0;
  foreach ($arr as $key => $value) {
    if (($i % 4) == 0) {
      if ($i) echo "    </tr>\n";
      echo "    <tr>\n";
    }
    echo rbcell("form_$group", $key, $value, "ci$group");
    ++$i;
  }
  while ($i % 4) {
    echo "     <td width='25%'>&nbsp;</td>\n";
    ++$i;
  }
  if ($i) echo "    </tr>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
 $fiinjmin = (int) $_POST['form_injmin'];

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_cricket_injury_audit SET "     .
   "cicounty = "    . rbvalue('form_county')    . ", " .
   "citeam = "      . rbvalue('form_team')      . ", " .
   "ciduration = "  . rbvalue('form_duration')  . ", " .
   "cirole = "      . rbvalue('form_role')      . ", " .
   "cimatchtype = " . rbvalue('form_matchtype') . ", " .
   "cicause = "     . rbvalue('form_cause')     . ", " .
   "ciactivity = "  . rbvalue('form_activity')  . ", " .
   "cibatside = "   . rbvalue('form_batside')   . ", " .
   "cibowlside = "  . rbvalue('form_bowlside')  . ", " .
   "cibowltype = "  . rbvalue('form_bowltype')  . " "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_cricket_injury_audit ( " .
   "cicounty, citeam, ciduration, cirole, cimatchtype, cicause, " .
   "ciactivity, cibatside, cibowlside, cibowltype " .
   ") VALUES ( " .
   rbvalue('form_county')    . ", " .
   rbvalue('form_team')      . ", " .
   rbvalue('form_duration')  . ", " .
   rbvalue('form_role')      . ", " .
   rbvalue('form_matchtype') . ", " .
   rbvalue('form_cause')     . ", " .
   rbvalue('form_activity')  . ", " .
   rbvalue('form_batside')   . ", " .
   rbvalue('form_bowlside')  . ", " .
   rbvalue('form_bowltype')  . " "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Cricket Injury Audit", $newid, "cricket_injury_audit", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_cricket_injury_audit WHERE " .
  "id = '$formid' AND activity = '1'") ;
}
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

// Pop up the coding window.
function docoding() {
 var width = screen.width - 50;
 if (!isNaN(top.screenX)) {
  width -= top.screenX;
 } else if (!isNaN(top.screenLeft)) {
  width -= top.screenLeft;
 }
 if (width > 1000) width = 1000;
 dlgopen('../../patient_file/encounter/coding_popup.php', '_blank', width, 550);
}

</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/cricket_injury_audit/new.php?id=<? echo $formid ?>">

<center>

<p class='title' style='margin-top:8px;margin-bottom:8px'>Cricket Injury Statistics</p>

<table border='1' width='98%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Match Information</b></td>
 </tr>

 <tr>
  <td nowrap>County</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('county', $arr_county); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Team ATI</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('team', $arr_team); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Match Duration</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('duration', $arr_duration); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Main Role in Team</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('role', $arr_role); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Injured In</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('matchtype', $arr_matchtype); ?>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Mechanism of Injury</b></td>
 </tr>

 <tr>
  <td nowrap>Cause</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('cause', $arr_cause); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Activity ATI</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('activity', $arr_activity); ?>
   </table>
  </td>
 </tr>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Conditions</b></td>
 </tr>

 <tr>
  <td nowrap>Batting Side</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('batside', $arr_batside); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Bowling Side</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('bowlside', $arr_bowlside); ?>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Bowling Type</td>
  <td nowrap>
   <table width='100%'>
<?php genRadios('bowltype', $arr_bowltype); ?>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
&nbsp;
<input type='button' value='Add Injury Diagnosis...' onclick='docoding();'
 title='Add or change coding for this encounter'
 style='background-color:#ffff00;' />
</p>

</center>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
