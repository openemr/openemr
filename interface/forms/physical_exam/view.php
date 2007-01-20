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
// This nonsense can go away when we move to subversion.
//////////////////////////////////////////////////////////////////////

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("lines.php");

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

function showExamLine($line_id, $description, &$linedbrow, $sysnamedisp) {
 $dres = sqlStatement("SELECT * FROM form_physical_exam_diagnoses " .
  "WHERE line_id = '$line_id' ORDER BY ordering, diagnosis");

 echo " <tr>\n";
 echo "  <td align='center'><input type='checkbox' name='form_obs[$line_id][wnl]' " .
  "value='1'" . ($linedbrow['wnl'] ? " checked" : "") . " /></td>\n";
 echo "  <td align='center'><input type='checkbox' name='form_obs[$line_id][abn]' " .
  "value='1'" . ($linedbrow['abn'] ? " checked" : "") . " /></td>\n";
 echo "  <td nowrap>$sysnamedisp</td>\n";
 echo "  <td nowrap>$description</td>\n";

 echo "  <td><select name='form_obs[$line_id][diagnosis]' onchange='seldiag(this, \"$line_id\")' style='width:100%'>\n";
 echo "   <option value=''></option>\n";
 $diagnosis = $linedbrow['diagnosis'];
 while ($drow = sqlFetchArray($dres)) {
  $sel = '';
  $diag = $drow['diagnosis'];
  if ($diagnosis && $diag == $diagnosis) {
   $sel = 'selected';
   $diagnosis = '';
  }
  echo "   <option value='$diag' $sel>$diag</option>\n";
 }
 // If the diagnosis was not in the standard list then it must have been
 // there before and then removed.  In that case show it in parentheses.
 if ($diagnosis) {
  echo "   <option value='$diagnosis' selected>($diagnosis)</option>\n";
 }
 echo "   <option value='*'>-- Edit --</option>\n";
 echo "   </select></td>\n";

 echo "  <td><input type='text' name='form_obs[$line_id][comments]' " .
  "size='20' maxlength='250' style='width:100%' " .
  "value='" . htmlentities($linedbrow['comments']) . "' /></td>\n";
 echo " </tr>\n";
}

function showTreatmentLine($line_id, $description, &$linedbrow) {
 echo " <tr>\n";
 echo "  <td align='center'><input type='checkbox' name='form_obs[$line_id][wnl]' " .
  "value='1'" . ($linedbrow['wnl'] ? " checked" : "") . " /></td>\n";
 echo "  <td></td>\n";
 echo "  <td colspan='2' nowrap>$description</td>\n";
 echo "  <td colspan='2'><input type='text' name='form_obs[$line_id][comments]' " .
  "size='20' maxlength='250' style='width:100%' " .
  "value='" . htmlentities($linedbrow['comments']) . "' /></td>\n";
 echo " </tr>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

 // We are to update/insert multiple table rows for the form.
 // Each has 2 checkboxes, a dropdown and a text input field.
 // Skip rows that have no entries.
 // There are also 3 special rows with just one checkbox and a text
 // input field.  Maybe also a diagnosis line, not clear.

 if ($formid) {
  $query = "DELETE FROM form_physical_exam WHERE forms_id = '$formid'";
  sqlStatement($query);
 }
 else {
  $formid = addForm($encounter, "Physical Exam", 0, "physical_exam", $pid, $userauthorized);
  $query = "UPDATE forms SET form_id = id WHERE id = '$formid' AND form_id = 0";
  sqlStatement($query);
 }

 $form_obs = $_POST['form_obs'];
 foreach ($form_obs as $line_id => $line_array) {
  $wnl = $line_array['wnl'] ? '1' : '0';
  $abn = $line_array['abn'] ? '1' : '0';
  $diagnosis = $line_array['diagnosis'] ? $line_array['diagnosis'] : '';
  $comments  = $line_array['comments']  ? $line_array['comments'] : '';
  if ($wnl || $abn || $diagnosis || $comments) {
   $query = "INSERT INTO form_physical_exam ( " .
    "forms_id, line_id, wnl, abn, diagnosis, comments " .
    ") VALUES ( " .
    "'$formid', '$line_id', '$wnl', '$abn', '$diagnosis', '$comments' " .
    ")";
   sqlInsert($query);
  }
 }

 if (! $_POST['form_refresh']) {
  formHeader("Redirecting....");
  formJump();
  formFooter();
  exit;
 }
}

// Load all existing rows for this form as a hash keyed on line_id.
//
$rows = array();
if ($formid) {
 $res = sqlStatement("SELECT * FROM form_physical_exam WHERE forms_id = '$formid'");
 while ($row = sqlFetchArray($res)) {
  $rows[$row['line_id']] = $row;
 }
}
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language="JavaScript">

 function seldiag(selobj, line_id) {
  var i = selobj.selectedIndex;
  var opt = selobj.options[i];
  if (opt.value == '*') {
   selobj.selectedIndex = 0;
   dlgopen('../../forms/physical_exam/edit_diagnoses.php?lineid=' + line_id, '_blank', 500, 400);
  }
 }

 function refreshme() {
  var f = document.forms[0];
  f.form_refresh.value = '1';
  f.submit();
 }

</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/physical_exam/new.php?id=<? echo $formid ?>">

<center>

<p>
<table border='0' width='98%'>

 <tr>
  <td align='center' width='1%' nowrap><b><?php xl('WNL','e'); ?></b></td>
  <td align='center' width='1%' nowrap><b><?php xl('ABN1','e'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php xl('System','e'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php xl('Specific','e'); ?></b></td>
  <td align='left'   width='1%' nowrap><b><?php xl('Diagnosis','e'); ?></b></td>
  <td align='left'  width='95%' nowrap><b><?php xl('Comments','e'); ?></b></td>
 </tr>

<?php
 foreach ($pelines as $sysname => $sysarray) {
  if ($sysname == '*') {
   // TBD: Show any remaining entries in $rows (should not be any).
   echo " <tr><td colspan='6'>\n";
   echo "   &nbsp;<br><b>" .xl('Treatment:'). "</b>\n";
   echo " </td></tr>\n";
  }
  $sysnamedisp = $sysname;
  foreach ($sysarray as $line_id => $description) {
   if ($sysname != '*') {
    showExamLine($line_id, $description, $rows[$line_id], $sysnamedisp);
   } else {
    showTreatmentLine($line_id, $description, $rows[$line_id]);
   }
   $sysnamedisp = '';
   // TBD: Delete $rows[$line_id] if it exists.
  } // end of line
 } // end of system name
?>

</table>

<p>
<input type='hidden' name='form_refresh' value='' />
<input type='submit' name='bn_save' value='<?php xl('Save','e'); ?>' />
&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick="location='<? echo "$rootdir/patient_file/encounter/$returnurl" ?>'" />
</p>

</center>

</form>
<?php
// TBD: If $alertmsg, display it with a JavaScript alert().
?>
</body>
</html>
