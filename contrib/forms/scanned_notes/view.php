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

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/acl.inc");

$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

$formid = $_GET['id'];
$imagedir = "$webserver_root/documents/$pid/encounters";

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_scanned_notes SET " .
   "notes = '" . $_POST['form_notes'] . "' " .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_scanned_notes ( " .
   "notes " .
   ") VALUES ( " .
   "'" . $_POST['form_notes'] . "' " .
   ")";
  $formid = sqlInsert($query);
  addForm($encounter, "Scanned Notes", $formid, "scanned_notes", $pid, $userauthorized);
 }

 $imagepath = "$imagedir/${encounter}_$formid.jpg";

 // Upload new or replacement document.
 // Always convert it to jpeg.
 if ($_FILES['form_image']['size']) {
  // If the patient's encounter image directory does not yet exist, create it.
  if (! is_dir($imagedir)) {
   $tmp0 = exec("mkdir -p '$imagedir'", $tmp1, $tmp2);
   if ($tmp2) die("mkdir returned $tmp2: $tmp0");
   exec("touch '$imagedir/index.html'");
  }
  if (is_file($imagepath)) unlink($imagepath);
  $tmp_name = $_FILES['form_image']['tmp_name'];
  // $cmd = "convert '$tmp_name' '$imagepath'"; // default density is 72 dpi
  $cmd = "convert -density 96 '$tmp_name' -append '$imagepath'";
  $tmp0 = exec($cmd, $tmp1, $tmp2);
  if ($tmp2) die("\"$cmd\" returned $tmp2: $tmp0");
 }

 // formHeader("Redirecting....");
 // formJump();
 // formFooter();
 // exit;
}

$imagepath = "$imagedir/${encounter}_$formid.jpg";
$imageurl = "$web_root/documents/$pid/encounters/${encounter}_$formid.jpg";

if ($formid) {
 $row = sqlQuery("SELECT * FROM form_scanned_notes WHERE " .
  "id = '$formid' AND activity = '1'");
 $formrow = sqlQuery("SELECT id FROM forms WHERE " .
  "form_id = '$formid' AND formdir = 'scanned_notes'");
}
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language='JavaScript'>

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<? echo $pid ?>',
   '_blank', 550, 270);
  return false;
 }

 // Process click on Delete button.
 function deleteme() {
  dlgopen('../../patient_file/deleter.php?formid=<?php echo $formrow['id'] ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  top.restoreSession();
  location = '<?php echo $GLOBALS['form_exit_url']; ?>';
 }

</script>

</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">

<form method="post" enctype="multipart/form-data"
 action="<? echo $rootdir ?>/forms/scanned_notes/new.php?id=<? echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'>Scanned Encounter Notes</td>
 </tr>

 <tr>
  <td width='5%'  class='dehead' nowrap>&nbsp;Comments&nbsp;</td>
  <td width='95%' class='detail' nowrap>
   <textarea name='form_notes' rows='4' style='width:100%'><? echo $row['notes'] ?></textarea>
  </td>
 </tr>

 <tr>
  <td class='dehead' nowrap>&nbsp;Document&nbsp;</td>
  <td class='detail' nowrap>
<?php
if ($formid && is_file($imagepath)) {
 echo "   <img src='$imageurl' />\n";
}
?>
   <p>&nbsp;
   <?php xl('Upload this file:','e') ?>
   <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
   <input name="form_image" type="file" />
   <br />&nbsp;</p>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Add Appointment' onclick='newEvt()' />
&nbsp;
<input type='button' value='Back' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
<?php if ($formrow['id'] && acl_check('admin', 'super')) { ?>
&nbsp;
<input type='button' value='Delete' onclick='deleteme()' style='color:red' />
<?php } ?>
</p>

</center>

</form>
</body>
</html>
