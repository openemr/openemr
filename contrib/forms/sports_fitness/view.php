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
  $query = "UPDATE form_sports_fitness SET "      .
   "height_meters = '"        . $_POST['form_height_meters']        . "', " .
   "weight_kg = '"            . $_POST['form_weight_kg']            . "', " .
   "skin_folds_9x = '"        . $_POST['form_skin_folds_9x']        . "', " .
   "skin_folds_5x = '"        . $_POST['form_skin_folds_5x']        . "', " .
   "pct_body_fat = '"         . $_POST['form_pct_body_fat']         . "', " .
   "method_body_fat = "       . rbvalue('form_method_body_fat')     . ", "  .
   "pulse = '"                . $_POST['form_pulse']                . "', " .
   "bps = '"                  . $_POST['form_bps']                  . "', " .
   "bpd = '"                  . $_POST['form_bpd']                  . "', " .
   "beep_level = '"           . $_POST['form_beep_level']           . "', " .
   "beep_shuttles = '"        . $_POST['form_beep_shuttles']        . "', " .
   "beep_vo2_max = '"         . $_POST['form_beep_vo2_max']         . "', " .
   "vertical_jump_meters = '" . $_POST['form_vertical_jump_meters'] . "', " .
   "agility_505 = '"          . $_POST['form_agility_505']          . "', " .
   "sit_and_reach_cm = '"     . $_POST['form_sit_and_reach_cm']     . "', " .
   "other = '"                . $_POST['form_other']                . "' "  .
   "WHERE id = '$formid'";
  sqlStatement($query);
 }

 // If adding a new form...
 //
 else {
  $query = "INSERT INTO form_sports_fitness ( " .
   "height_meters, weight_kg, skin_folds_9x, skin_folds_5x, " .
   "pct_body_fat, method_body_fat, pulse, bps, bpd, " .
   "beep_level, beep_shuttles, beep_vo2_max, " .
   "vertical_jump_meters, agility_505, sit_and_reach_cm, other " .
   ") VALUES ( " .
   "'" . $_POST['form_height_meters']        . "', " .
   "'" . $_POST['form_weight_kg']            . "', " .
   "'" . $_POST['form_skin_folds_9x']        . "', " .
   "'" . $_POST['form_skin_folds_5x']        . "', " .
   "'" . $_POST['form_pct_body_fat']         . "', " .
   rbvalue('form_method_body_fat')           . ", "  .
   "'" . $_POST['form_pulse']                . "', " .
   "'" . $_POST['form_bps']                  . "', " .
   "'" . $_POST['form_bpd']                  . "', " .
   "'" . $_POST['form_beep_level']           . "', " .
   "'" . $_POST['form_beep_shuttles']        . "', " .
   "'" . $_POST['form_beep_vo2_max']         . "', " .
   "'" . $_POST['form_vertical_jump_meters'] . "', " .
   "'" . $_POST['form_agility_505']          . "', " .
   "'" . $_POST['form_sit_and_reach_cm']     . "', " .
   "'" . $_POST['form_other']                . "' "  .
   ")";
  $newid = sqlInsert($query);
  addForm($encounter, "Sports Fitness", $newid, "sports_fitness", $pid, $userauthorized);
 }

 formHeader("Redirecting....");
 formJump();
 formFooter();
 exit;
}

if ($formid) {
 $row = sqlQuery ("SELECT * FROM form_sports_fitness WHERE " .
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

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<? echo $rootdir ?>/forms/sports_fitness/new.php?id=<? echo $formid ?>"
 onsubmit="return top.restoreSession()">

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd'>
  <td colspan='2' align='center'><b>Physical + Fitness Tests</b></td>
 </tr>

 <tr>
  <td nowrap>Vitals</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='20%' nowrap>
      Height (meters):
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_height_meters' size='6'
       title='Height in meters'
       value='<? echo addslashes($row['height_meters']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      Weight (kg):
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_weight_kg' size='6'
       title='Weight in kilograms'
       value='<? echo addslashes($row['weight_kg']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      &nbsp;
     </td>
     <td nowrap>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td nowrap>
      Resting Pulse:
     </td>
     <td nowrap>
      <input type='text' name='form_pulse' size='6'
       title='Resting pulse rate per minute'
       value='<? echo addslashes($row['pulse']) ?>' /> &nbsp;
     </td>
     <td nowrap>
      Systolic BP:
     </td>
     <td nowrap>
      <input type='text' name='form_bps' size='6'
       title='mm Hg'
       value='<? echo addslashes($row['bps']) ?>' /> &nbsp;
     </td>
     <td nowrap>
      Dyastolic BP:
     </td>
     <td nowrap>
      <input type='text' name='form_bpd' size='6'
       title='mm Hg'
       value='<? echo addslashes($row['bps']) ?>' /> &nbsp;
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td>Body<br>Composition</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='20%' nowrap>
      Skin Folds 9x:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_skin_folds_9x' size='6'
       title='Total of 9 skin fold readings in cm'
       value='<? echo addslashes($row['skin_folds_9x']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      Skin Folds 5x:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_skin_folds_5x' size='6'
       title='Total of 5 skin fold readings in cm'
       value='<? echo addslashes($row['skin_folds_5x']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      % Body Fat:
     </td>
     <td nowrap>
      <input type='text' name='form_pct_body_fat' size='6'
       title='Percent body fat'
       value='<? echo addslashes($row['pct_body_fat']) ?>' /> &nbsp;
     </td>
    </tr>
    <tr>
     <td colspan='6' nowrap>
      B.F. Method Used:&nbsp;
      <? echo rbinput('form_method_body_fat', 'Caliper'    , 'Caliper'    , 'method_body_fat') ?>&nbsp;
      <? echo rbinput('form_method_body_fat', 'Electronic' , 'Electronic' , 'method_body_fat') ?>&nbsp;
      <? echo rbinput('form_method_body_fat', 'Hydrostatic', 'Hydrostatic', 'method_body_fat') ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Beep Test</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='20%' nowrap>
      Level:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_beep_level' size='6'
       title='Level Reached'
       value='<? echo addslashes($row['beep_level']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      Shuttles:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_beep_shuttles' size='6'
       title='Number of shuttles at this level'
       value='<? echo addslashes($row['beep_shuttles']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      VO2 Max:
     </td>
     <td nowrap>
      <input type='text' name='form_beep_vo2_max' size='6'
       title='ml/kg/min'
       value='<? echo addslashes($row['beep_vo2_max']) ?>' /> &nbsp;
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Other Tests</td>
  <td nowrap>
   <table width='100%'>
    <tr>
     <td width='20%' nowrap>
      Vertical Jump:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_vertical_jump_meters' size='6'
       title='Vertical Jump Test in Meters'
       value='<? echo addslashes($row['vertical_jump_meters']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      505 Agility:
     </td>
     <td width='13%' nowrap>
      <input type='text' name='form_agility_505' size='6'
       title='505 Agility Test in Seconds'
       value='<? echo addslashes($row['agility_505']) ?>' /> &nbsp;
     </td>
     <td width='20%' nowrap>
      Sit &amp; Reach:
     </td>
     <td nowrap>
      <input type='text' name='form_sit_and_reach_cm' size='6'
       title='Sit and Reach Test in cm + or - ve'
       value='<? echo addslashes($row['sit_and_reach_cm']) ?>' /> &nbsp;
     </td>
    </tr>
   </table>
  </td>
 </tr>

 <tr>
  <td nowrap>Still More</td>
  <td nowrap>
   <textarea name='form_other' rows='8' style='width:100%'><? echo $row['other'] ?></textarea>
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
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
