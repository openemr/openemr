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

$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

function rbvalue($rbname) {
 $tmp = $_POST[$rbname];
 if (! $tmp) return "NULL";
 return "'$tmp'";
}

function rbinput($name, $value, $desc, $colname) {
 global $row;
 $ret  = "<input type='radio' name='$name' value='$value'";
 if ($row[$colname] == $value) $ret .= " checked";
 $ret .= " />$desc";
 return $ret;
}

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

 // If updating an existing form...
 //
 if ($formid) {
  $query = "UPDATE form_body_composition SET "      .
   "body_type = "             . rbvalue('form_body_type') . ", "  .
   "height = '"               . $_POST['form_height']     . "', " .
   "weight = '"               . $_POST['form_weight']     . "', " .
   "bmi = '"                  . $_POST['form_bmi']        . "', " .
   "bmr = '"                  . $_POST['form_bmr']        . "', " .
   "impedance = '"            . $_POST['form_impedance']  . "', " .
   "fat_pct = '"              . $_POST['form_fat_pct']    . "', " .
   "fat_mass = '"             . $_POST['form_fat_mass']   . "', " .
   "ffm = '"                  . $_POST['form_ffm']        . "', " .
   "tbw = '"                  . $_POST['form_tbw']        . "', " .
   "other = '"                . $_POST['form_other']                . "' "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_body_composition ( " .
   "body_type, height, weight, bmi, bmr, impedance, fat_pct, " .
   "fat_mass, ffm, tbw, other " .
   ") VALUES ( " .
   rbvalue('form_body_type')      . ", "  .
   "'" . $_POST['form_height']    . "', " .
   "'" . $_POST['form_weight']    . "', " .
   "'" . $_POST['form_bmi']       . "', " .
   "'" . $_POST['form_bmr']       . "', " .
   "'" . $_POST['form_impedance'] . "', " .
   "'" . $_POST['form_fat_pct']   . "', " .
   "'" . $_POST['form_fat_mass']  . "', " .
   "'" . $_POST['form_ffm']       . "', " .
   "'" . $_POST['form_tbw']       . "', " .
   "'" . $_POST['form_other']     . "' "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Body Composition", $newid, "body_composition", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_body_composition WHERE " .
  "id = '$formid' AND activity = '1'") ;
}
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script language="JavaScript">
</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/body_composition/new.php?id=<? echo $formid ?>">

<center>

<p>
<table border='0' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Body Composition</b></td>
 </tr>

 <tr>
  <td width='5%' nowrap>Body Type</td>
  <td nowrap>
   <? echo rbinput('form_body_type', 'Standard', 'Standard', 'body_type') ?>&nbsp;
   <? echo rbinput('form_body_type', 'Athletic', 'Athletic', 'body_type') ?>&nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Height in inches</td>
  <td nowrap>
   <input type='text' name='form_height' size='6'
    value='<? echo addslashes($row['height']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Weight in pounds</td>
  <td nowrap>
   <input type='text' name='form_weight' size='6'
    value='<? echo addslashes($row['weight']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>BMI</td>
  <td nowrap>
   <input type='text' name='form_bmi' size='6'
    value='<? echo addslashes($row['bmi']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>BMR in kj</td>
  <td nowrap>
   <input type='text' name='form_bmr' size='6'
    value='<? echo addslashes($row['bmr']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Impedance in ohms</td>
  <td nowrap>
   <input type='text' name='form_impedance' size='6'
    value='<? echo addslashes($row['impedance']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat %</td>
  <td nowrap>
   <input type='text' name='form_fat_pct' size='6'
    value='<? echo addslashes($row['fat_pct']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat Mass in pounds</td>
  <td nowrap>
   <input type='text' name='form_fat_mass' size='6'
    value='<? echo addslashes($row['fat_mass']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>FFM in pounds</td>
  <td nowrap>
   <input type='text' name='form_ffm' size='6'
    value='<? echo addslashes($row['ffm']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>TBW in pounds</td>
  <td nowrap>
   <input type='text' name='form_tbw' size='6'
    value='<? echo addslashes($row['tbw']) ?>' /> &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Notes</td>
  <td nowrap>
   <textarea name='form_other' rows='8' style='width:100%'><? echo $row['other'] ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="location='<? echo "$rootdir/patient_file/encounter/patient_encounter.php" ?>'" />
</p>

</center>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
