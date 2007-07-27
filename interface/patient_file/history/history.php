<?php
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("history.inc.php");
 include_once("$srcdir/acl.inc");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(History not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 $result = getHistoryData($pid);
 if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);	
 }
?>

<? if ($thisauth == 'write' || $thisauth == 'addonly') { ?>
<a href="history_full.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>
 onclick="top.restoreSession()">
<font class=title><? xl('Patient History / Lifestyle','e'); ?></font>
<font class=more><?echo $tmore;?></font></a><br>
<? } ?>

<table border='0' cellpadding='2' width='100%'>
 <tr>
  <td valign='top' width='34%'>
   <span class='bold'><? xl('Family History','e'); ?>:</span><br>
   <?if ($result{"history_father"} != "") {?><span class='text'><? xl('Father','e'); ?>: </span><span class='text'><?echo $result{"history_father"};?></span><br><?}?>
   <?if ($result{"history_mother"} != "") {?><span class='text'><? xl('Mother','e'); ?>: </span><span class='text'><?echo $result{"history_mother"};?></span><br><?}?>
   <?if ($result{"history_siblings"} != "") {?><span class='text'><? xl('Siblings','e'); ?>: </span><span class='text'><?echo $result{"history_siblings"};?></span><br><?}?>
   <?if ($result{"history_spouse"} != "") {?><span class='text'><? xl('Spouse','e'); ?>: </span><span class='text'><?echo $result{"history_spouse"};?></span><br><?}?>
   <?if ($result{"history_offspring"} != "") {?><span class='text'><? xl('Offspring','e'); ?>: </span><span class='text'><?echo $result{"history_offspring"};?></span><br><?}?>
  </td>

  <td valign='top' width='33%'>
   <span class='bold'><? xl('Relatives','e'); ?>:</span><br>
   <?if ($result{"relatives_cancer"} != "") {?><span class='text'><? xl('Cancer','e'); ?>: </span><span class='text'><?echo $result{"relatives_cancer"};?></span><br><?}?>
   <?if ($result{"relatives_tuberculosis"} != "") {?><span class='text'><? xl('Tuberculosis','e'); ?>: </span><span class='text'><?echo $result{"relatives_tuberculosis"};?></span><br><?}?>
   <?if ($result{"relatives_diabetes"} != "") {?><span class='text'><? xl('Diabetes','e'); ?>: </span><span class='text'><?echo $result{"relatives_diabetes"};?></span><br><?}?>
   <?if ($result{"relatives_high_blood_pressure"} != "") {?><span class='text'><? xl('High Blood Pressure','e'); ?>: </span><span class='text'><?echo $result{"relatives_high_blood_pressure"};?></span><br><?}?>
   <?if ($result{"relatives_heart_problems"} != "") {?><span class='text'><? xl('Heart Problems','e'); ?>: </span><span class='text'><?echo $result{"relatives_heart_problems"};?></span><br><?}?>
   <?if ($result{"relatives_stroke"} != "") {?><span class='text'><? xl('Stroke','e'); ?>: </span><span class='text'><?echo $result{"relatives_stroke"};?></span><br><?}?>
   <?if ($result{"relatives_epilepsy"} != "") {?><span class='text'><? xl('Epilepsy','e'); ?>: </span><span class='text'><?echo $result{"relatives_epilepsy"};?></span><br><?}?>
   <?if ($result{"relatives_mental_illness"} != "") {?><span class='text'><? xl('Mental Illness','e'); ?>: </span><span class='text'><?echo $result{"relatives_mental_illness"};?></span><br><?}?>
   <?if ($result{"relatives_suicide"} != "") {?><span class='text'><? xl('Suicide','e'); ?>: </span><span class='text'><?echo $result{"relatives_suicide"};?></span><br><?}?>
  </td>

  <td valign='top' width='33%'>
   <span class='bold'><? xl('Lifestyle','e'); ?>:</span><br>
   <?if ($result{"coffee"} != "") {?><span class='text'><? xl('Coffee','e'); ?>: </span><span class='text'><?echo $result{"coffee"};?></span><br><?}?>
   <?if ($result{"tobacco"} != "") {?><span class='text'><? xl('Tobacco','e'); ?>: </span><span class='text'><?echo $result{"tobacco"};?></span><br><?}?>
   <?if ($result{"alcohol"} != "") {?><span class='text'><? xl('Alcohol','e'); ?>: </span><span class='text'><?echo $result{"alcohol"};?></span><br><?}?>
   <?if ($result{"sleep_patterns"} != "") {?><span class='text'><? xl('Sleep Patterns','e'); ?>: </span><span class='text'><?echo $result{"sleep_patterns"};?></span><br><?}?>
   <?if ($result{"exercise_patterns"} != "") {?><span class='text'><? xl('Exercise Patterns','e'); ?>: </span><span class='text'><?echo $result{"exercise_patterns"};?></span><br><?}?>
   <?if ($result{"seatbelt_use"} != "") {?><span class='text'><? xl('Seatbelt Use','e'); ?>: </span><span class='text'><?echo $result{"seatbelt_use"};?></span><br><?}?>
   <?if ($result{"counseling"} != "") {?><span class='text'><? xl('Counseling','e'); ?>: </span><span class='text'><?echo $result{"counseling"};?></span><br><?}?>
   <?if ($result{"hazardous_activities"} != "") {?><span class='text'><? xl('Hazardous Activities','e'); ?>: </span><span class='text'><?echo $result{"hazardous_activities"};?></span><br><?}?>
  </td>
 </tr>

 <tr>
  <td colspan='3'>&nbsp;</td>
 </tr>

 <tr>
  <td valign='top' class='text'>
   <span class='bold'><? xl('Date of Last','e'); ?>:</span>
<?
 foreach ($exams as $key => $value) {
  if ($result[$key]) {
   $testresult = substr($result['last_exam_results'], substr($value, 0, 2), 1);
   echo "   <br>";
   if ($testresult == '2') echo "<font color='red'>";
   echo substr($value, 3) . ": " . $result[$key];
   if ($testresult == '2') echo " (abn)</font>";
   echo "\n";
  }
 }

 foreach ($obsoletes as $key => $value) {
  if ($result[$key] && $result[$key] != '0000-00-00 00:00:00') {
   echo "   <br>$value: " . substr($result[$key], 0, 10) . "\n";
  }
 }
?>
  </td>

  <td valign='top' class='text' colspan='2'>
   <span class='bold'><? xl('Additional History','e'); ?>:</span><br>
   <?if (!empty($result{"name_1"})) {?><span class='text'><b><?=$result{"name_1"}?></b>: </span><span class='text'><?echo $result{"value_1"}?></span><br><?}?>
   <?if (!empty($result{"name_2"})) {?><span class='text'><b><?=$result{"name_2"}?></b>: </span><span class='text'><?echo $result{"value_2"}?></span><br><?}?>

   <?if (!empty($result{"additional_history"})) {?><span class='text'><?echo $result{"additional_history"}?></span><br><?}?>
  </td>
 </tr>
</table>

</body>
</html>
