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

// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

function cbvalue($cbname) {
 return $_POST[$cbname] ? '1' : '0';
}

function cbinput($name, $colname) {
 global $row;
 $ret  = "<input type='checkbox' name='$name' value='1'";
 if ($row[$colname]) $ret .= " checked";
 $ret .= " />";
 return $ret;
}

function cbcell($name, $desc, $colname) {
 return "<td width='25%' nowrap>" . cbinput($name, $colname) . "$desc</td>\n";
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

 $fu_timing   = $_POST['fu_timing'];
 $fu_location = $_POST['fu_location'];

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_specialist_notes SET " .
   "notes = '"            . $_POST['form_notes']       . "', " .
   "followup_required = " . cbvalue('fu_required')     . ", "  .
   "followup_timing = '$fu_timing'"                    . ", "  .
   "followup_location = '$fu_location'"                . " "   .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_specialist_notes ( " .
   "notes, followup_required, followup_timing, followup_location " .
   ") VALUES ( " .
   "'" . $_POST['form_notes']       . "', " .
   cbvalue('fu_required')           . ", "  .
   "'$fu_timing'"                   . ", "  .
   "'$fu_location'"                 . " "   .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Specialist Notes", $newid, "specialist_notes", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_specialist_notes WHERE " .
  "id = '$formid' AND activity = '1'") ;
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language='JavaScript'>
 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<?php echo $pid ?>',
   '_blank', 550, 270);
  return false;
 }
</script>
</head>

<body class="body_top">
<form method="post" action="<?php echo $rootdir ?>/forms/specialist_notes/new.php?id=<?php echo $formid ?>" onsubmit="return top.restoreSession()">

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Notes from Specialist</b></td>
 </tr>

 <tr>
  <td width='5%'  nowrap> Notes </td>
  <td width='95%' nowrap>
   <textarea name='form_notes' rows='18' style='width:100%'><?php echo $row['notes'] ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap>Follow Up</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='1%' nowrap>
      <?php echo cbinput('fu_required', 'followup_required') ?>Required on&nbsp;
     </td>
     <td width='49%' nowrap>
      <input type='text' name='fu_timing' size='10' style='width:100%'
       title='When to follow up'
       value='<?php echo addslashes($row['followup_timing']) ?>' />
     </td>
     <td width='1%' nowrap>
      &nbsp;at&nbsp;
     </td>
     <td width='49%' nowrap>
      <input type='text' name='fu_location' size='10' style='width:100%'
       title='Where to follow up'
       value='<?php echo addslashes($row['followup_location']) ?>' />
     </td>
    </tr>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Add Appointment' onclick='newEvt()' />
&nbsp;
<input type='button' value='Cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
</p>

</center>

</form>
</body>
</html>
