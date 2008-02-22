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

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_hist_exam_plan SET "      .
   "history = '"     . $_POST['form_history']     . "', " .
   "examination = '" . $_POST['form_examination'] . "', " .
   "plan = '"        . $_POST['form_plan']        . "' "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_hist_exam_plan ( " .
   "history, examination, plan " .
   ") VALUES ( " .
   "'" . $_POST['form_history']     . "', " .
   "'" . $_POST['form_examination'] . "', " .
   "'" . $_POST['form_plan']        . "' "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Hist/Exam/Plan", $newid, "hist_exam_plan", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_hist_exam_plan WHERE " .
  "id = '$formid' AND activity = '1'") ;
}
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script language="JavaScript">
</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/hist_exam_plan/new.php?id=<? echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>History, Examination and Plan</b></td>
 </tr>

 <tr>
  <td width='5%'  nowrap> History </td>
  <td width='95%' nowrap>
   <textarea name='form_history' rows='8' style='width:100%'><? echo $row['history'] ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Examination </td>
  <td nowrap>
   <textarea name='form_examination' rows='8' style='width:100%'><? echo $row['examination'] ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Plan </td>
  <td nowrap>
   <textarea name='form_plan' rows='8' style='width:100%'><? echo $row['plan'] ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
</p>

</center>

</form>
</body>
</html>
