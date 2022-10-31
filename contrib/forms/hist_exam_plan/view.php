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

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Core\Header;

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
        $query = "UPDATE form_hist_exam_plan SET
         history = ?,
         examination = ?,
         plan = ?,
         WHERE id = ?";
        sqlStatement($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'], $formid ));
    } else { // If adding a new form...
        $query = "INSERT INTO form_hist_exam_plan (
         history, examination, plan
         ) VALUES ( ?, ?, ? )";

        $newid = sqlInsert($query, array($_POST['form_history'], $_POST['form_examination'], $_POST['form_plan'] ));
        addForm($encounter, "Hist/Exam/Plan", $newid, "hist_exam_plan", $pid, $userauthorized);
    }

    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

if ($formid) {
    $row = sqlQuery("SELECT * FROM form_hist_exam_plan WHERE " .
    "id = ? AND activity = '1'", [$formid]) ;
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
</head>

<body <?php echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/hist_exam_plan/new.php?id=<?php echo attr_url($formid); ?>"
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
   <textarea name='form_history' rows='8' style='width:100%'><?php echo text($row['history']); ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Examination </td>
  <td nowrap>
   <textarea name='form_examination' rows='8' style='width:100%'><?php echo text($row['examination']); ?></textarea>
  </td>
 </tr>

 <tr>
  <td nowrap> Plan </td>
  <td nowrap>
   <textarea name='form_plan' rows='8' style='width:100%'><?php echo text($row['plan']); ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="parent.closeTab(window.name, false)" />
</p>

</center>

</form>
</body>
</html>
