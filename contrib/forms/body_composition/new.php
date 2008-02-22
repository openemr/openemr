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

$scale_file_name = '/tmp/tanita_scale.txt';
$scale_file_age = -1;
$row = array();

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}
// encode a string from a form field for database writing.
function form2db($fldval) {
 $fldval = trim($fldval);
 if (!get_magic_quotes_gpc()) $fldval = addslashes($fldval);
 return $fldval;
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
   "height = '"               . form2db($_POST['form_height'])     . "', " .
   "weight = '"               . form2db($_POST['form_weight'])     . "', " .
   "bmi = '"                  . form2db($_POST['form_bmi'])        . "', " .
   "bmr = '"                  . form2db($_POST['form_bmr'])        . "', " .
   "impedance = '"            . form2db($_POST['form_impedance'])  . "', " .
   "fat_pct = '"              . form2db($_POST['form_fat_pct'])    . "', " .
   "fat_mass = '"             . form2db($_POST['form_fat_mass'])   . "', " .
   "ffm = '"                  . form2db($_POST['form_ffm'])        . "', " .
   "tbw = '"                  . form2db($_POST['form_tbw'])        . "', " .
   "other = '"                . form2db($_POST['form_other'])      . "' "  .
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
   "'" . form2db($_POST['form_height'])    . "', " .
   "'" . form2db($_POST['form_weight'])    . "', " .
   "'" . form2db($_POST['form_bmi'])       . "', " .
   "'" . form2db($_POST['form_bmr'])       . "', " .
   "'" . form2db($_POST['form_impedance']) . "', " .
   "'" . form2db($_POST['form_fat_pct'])   . "', " .
   "'" . form2db($_POST['form_fat_mass'])  . "', " .
   "'" . form2db($_POST['form_ffm'])       . "', " .
   "'" . form2db($_POST['form_tbw'])       . "', " .
   "'" . form2db($_POST['form_other'])     . "' "  .
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
else {
 // Get the most recent scale reading.
 $items = explode(',', trim(file_get_contents($scale_file_name)));
 if ($items && count($items) > 11) {
  $scale_file_age = round((time() - filemtime($scale_file_name)) / 60);
  $row['body_type'] = $items[0] ? 'Athletic' : 'Standard';
  $row['height']    = $items[2];
  $row['weight']    = $items[3];
  $row['bmi']       = $items[10];
  $row['bmr']       = $items[11];
  $row['impedance'] = $items[4];
  $row['fat_pct']   = $items[5];
  $row['fat_mass']  = $items[6];
  $row['ffm']       = $items[7];
  $row['tbw']       = $items[8];
 }
}
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script language="JavaScript">
</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/body_composition/new.php?id=<?php echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p>
<table border='0' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='3' align='center'><b>Body Composition</b></td>
 </tr>

 <tr>
  <td width='5%' nowrap>Body Type</td>
  <td colspan='2' nowrap>
   <?php echo rbinput('form_body_type', 'Standard', 'Standard', 'body_type') ?>&nbsp;
   <?php echo rbinput('form_body_type', 'Athletic', 'Athletic', 'body_type') ?>&nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Height in inches</td>
  <td nowrap>
   <input type='text' name='form_height' size='6'
    value='<? echo addslashes($row['height']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Weight in pounds</td>
  <td nowrap>
   <input type='text' name='form_weight' size='6'
    value='<? echo addslashes($row['weight']) ?>' /> &nbsp;
  </td>
  <td align='center' nowrap>
<?php
 if ($scale_file_age >= 0) {
  echo "<font color='blue'>This reading was taken $scale_file_age minutes ago.</font>\n";
 } else {
  echo "&nbsp;\n";
 }
?>
  </td>
 </tr>

 <tr>
  <td nowrap>BMI</td>
  <td nowrap>
   <input type='text' name='form_bmi' size='6'
    value='<?php echo addslashes($row['bmi']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>BMR in kj</td>
  <td nowrap>
   <input type='text' name='form_bmr' size='6'
    value='<?php echo addslashes($row['bmr']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Impedance in ohms</td>
  <td nowrap>
   <input type='text' name='form_impedance' size='6'
    value='<?php echo addslashes($row['impedance']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat %</td>
  <td nowrap>
   <input type='text' name='form_fat_pct' size='6'
    value='<?php echo addslashes($row['fat_pct']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Fat Mass in pounds</td>
  <td nowrap>
   <input type='text' name='form_fat_mass' size='6'
    value='<?php echo addslashes($row['fat_mass']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>FFM in pounds</td>
  <td nowrap>
   <input type='text' name='form_ffm' size='6'
    value='<?php echo addslashes($row['ffm']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>TBW in pounds</td>
  <td nowrap>
   <input type='text' name='form_tbw' size='6'
    value='<?php echo addslashes($row['tbw']) ?>' /> &nbsp;
  </td>
  <td nowrap>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td nowrap>Notes</td>
  <td colspan='2' nowrap>
   <textarea name='form_other' rows='8' style='width:100%'><?php echo $row['other'] ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url'] ?>'" />
</p>

</center>

</form>
</body>
</html>
