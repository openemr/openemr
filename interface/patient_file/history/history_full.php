<?
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("history.inc.php");
 include_once("$srcdir/acl.inc");

 // Check authorization.
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if ($thisauth != 'write' && $thisauth != 'addonly')
  die("Not authorized.");
?>
<html>
<head>
<link rel=stylesheet href="<? echo $css_header ?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2' bottommargin='0' marginwidth='2' marginheight='0'>

<?
$result = getHistoryData($pid);
if (!is_array($result)) {
 newHistoryData($pid);
 $result = getHistoryData($pid);	
}
?>

<form action="history_save.php" name='history_form' method='post'>
<input type='hidden' name='mode' value='save'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="history.php">
<?php } else { ?>
<a href='patient_history.php' target='Main'>
<?php } ?>
<font class='title'><? xl('Patient History / Lifestyle','e'); ?></font>
<font class=back><?echo $tback;?></font></a><br>

<table border='0' cellpadding='5' width='100%'>

 <tr>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><? xl('Family History','e'); ?>:</td></tr>
    <tr><td class='text'><? xl('Father','e'); ?></td><td><input type='text' size='20' name='history_father' value="<?echo $result{"history_father"};?>"></tr>
    <tr><td class='text'><? xl('Mother','e'); ?></td><td><input type='text' size='20' name='history_mother' value="<?echo $result{"history_mother"};?>"></tr>
    <tr><td class='text'><? xl('Siblings','e'); ?></td><td><input type='text' size='20' name='history_siblings' value="<?echo $result{"history_siblings"};?>"></tr>
    <tr><td class='text'><? xl('Spouse','e'); ?></td><td><input type='text' size='20' name='history_spouse' value="<?echo $result{"history_spouse"};?>"></tr>
    <tr><td class='text'><? xl('Offspring','e'); ?>&nbsp;</td><td><input type='text' size='20' name='history_offspring' value="<?echo $result{"history_offspring"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><? xl('Relatives','e'); ?>:</td></tr>
    <tr><td class='text'><? xl('Cancer','e'); ?></td><td><input type='text' size='20' name='relatives_cancer' value="<?echo $result{"relatives_cancer"};?>"></tr>
    <tr><td class='text'><? xl('Tuberculosis','e'); ?></td><td><input type='text' size='20' name='relatives_tuberculosis' value="<?echo $result{"relatives_tuberculosis"};?>"></tr>
    <tr><td class='text'><? xl('Diabetes','e'); ?></td><td><input type='text' size='20' name='relatives_diabetes' value="<?echo $result{"relatives_diabetes"};?>"></tr>
    <tr><td class='text'><? xl('High Blood Pressure','e'); ?>&nbsp;</td><td><input type='text' size='20' name='relatives_high_blood_pressure' value="<?echo $result{"relatives_high_blood_pressure"};?>"></tr>
    <tr><td class='text'><? xl('Heart Problems','e'); ?></td><td><input type='text' size='20' name='relatives_heart_problems' value="<?echo $result{"relatives_heart_problems"};?>"></tr>
    <tr><td class='text'><? xl('Stroke','e'); ?></td><td><input type='text' size='20' name='relatives_stroke' value="<?echo $result{"relatives_stroke"};?>"></tr>
    <tr><td class='text'><? xl('Epilepsy','e'); ?></td><td><input type='text' size='20' name='relatives_epilepsy' value="<?echo $result{"relatives_epilepsy"};?>"></tr>
    <tr><td class='text'><? xl('Mental Illness','e'); ?></td><td><input type='text' size='20' name='relatives_mental_illness' value="<?echo $result{"relatives_mental_illness"};?>"></tr>
    <tr><td class='text'><? xl('Suicide','e'); ?></td><td><input type='text' size='20' name='relatives_suicide' value="<?echo $result{"relatives_suicide"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border=0 cellpadding=0 cellspacing=0>
    <tr><td colspan=2 class=bold><? xl('Lifestyle','e'); ?>:</td></tr>
    <tr><td class='text'><? xl('Coffee','e'); ?></td><td><input type='text' size='20' name='coffee' value="<?echo $result{"coffee"};?>"></tr>
    <tr><td class='text'><? xl('Tobacco','e'); ?></td><td><input type='text' size='20' name='tobacco' value="<?echo $result{"tobacco"};?>"></tr>
    <tr><td class='text'><? xl('Alcohol','e'); ?></td><td><input type='text' size='20' name='alcohol' value="<?echo $result{"alcohol"};?>"></tr>
    <tr><td class='text'><? xl('Sleep Patterns','e'); ?></td><td><input type='text' size='20' name='sleep_patterns' value="<?echo $result{"sleep_patterns"};?>"></tr>
    <tr><td class='text'><? xl('Exercise Patterns','e'); ?></td><td><input type='text' size='20' name='exercise_patterns' value="<?echo $result{"exercise_patterns"};?>"></tr>
    <tr><td class='text'><? xl('Seatbelt Use','e'); ?></td><td><input type='text' size='20' name='seatbelt_use' value="<?echo $result{"seatbelt_use"};?>"></tr>
    <tr><td class='text'><? xl('Counseling','e'); ?></td><td><input type='text' size='20' name='counseling' value="<?echo $result{"counseling"};?>"></tr>
    <tr><td class='text'><? xl('Hazardous Activities','e'); ?>&nbsp;</td><td><input type='text' size='20' name='hazardous_activities' value="<?echo $result{"hazardous_activities"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
  </td>
 </tr>
</table>

<table border='0' cellpadding='5' width='100%'>
 <tr>
  <td valign='top' width='10%'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr>
     <td colspan='2' class='bold'><? xl('Date/Notes of Last','e'); ?>:</td>
     <td class='bold'><? xl('Nor','e'); ?>&nbsp;</td>
     <td class='bold'><? xl('Abn','e'); ?></td>
    </tr>
<?
 foreach ($exams as $key => $value) {
  $testresult = substr($result['last_exam_results'], substr($value, 0, 2), 1);
  echo "    <tr>\n";
  echo "     <td class='text' nowrap>" . substr($value, 3) . "&nbsp;</td>\n";
  echo "     <td nowrap><input type='text' size='30' name='$key' value='" .
       addslashes($result[$key]) . "'>&nbsp;</td>\n";
  echo "     <td nowrap><input type='radio' name='rb_$key' value='1'";
  if ($testresult == '1') echo " checked";
  echo " /></td>\n";
  echo "     <td nowrap><input type='radio' name='rb_$key' value='2'";
  if ($testresult == '2') echo " checked";
  echo " /></td>\n";
  echo "    </tr>\n";
 }

 $needwarning = false;
 foreach ($obsoletes as $key => $value) {
  if ($result[$key] && $result[$key] != '0000-00-00 00:00:00') {
   $needwarning = true;
   echo "    <tr>\n";
   echo "     <td class='text' nowrap><font color='red'>$value&nbsp;</font></td>\n";
   echo "     <td class='bold' colspan='3' nowrap><input type='text' size='10' name='$key' value='" .
        substr($result[$key], 0, 10) . "'>&nbsp;<font color='red'>**</font></td>\n";
   echo "    </tr>\n";
  }
 }
 if ($needwarning) {
  echo "    <tr>\n";
  echo "     <td class='text' colspan='4' nowrap><font color='red'>** Please move surgeries to Issues!</font></td>\n";
  echo "    </tr>\n";
 }
?>
   </table>
  </td>
  <td align='center' valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><? xl('Additional History','e'); ?>:</td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_1' value="<?=$result{"name_1"}?>">:</td><td><input type='text' size='20' name='value_1' value="<?=$result{"value_1"}?>"></td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_2' value="<?=$result{"name_2"}?>">:</td><td><input type='text' size='20' name='value_2' value="<?=$result{"value_2"}?>"></td></tr>
   </table><br>
   <textarea cols="50" rows="5" name="additional_history"><?=$result{"additional_history"}?></textarea>
   <p>
   <input type='submit' value='<?xl('Save','e'); ?>.' />&nbsp;
   <input type='button' value='<?xl('To Issues','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="parent.left_nav.setRadio(window.name,'iss');location='../summary/stats_full.php';" />&nbsp;
<?php } else { ?>
    onclick="location='../summary/stats_full.php';" />&nbsp;
<?php } ?>
   <input type='button' value='<?php xl('Back','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="location='history.php';" />
<?php } else { ?>
    onclick="location='patient_history.php';" />
<?php } ?>
  </td>
 </tr>
</table>

</form>

</body>
</html>
