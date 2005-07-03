<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("history.inc.php");
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

<a href="patient_history.php" target=Main><font class='title'>Patient History / Lifestyle</font><font class=back><?echo $tback;?></font></a><br>

<table border='0' cellpadding='5' width='100%'>

 <tr>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'>Family History:</td></tr>
    <tr><td class='text'>Father</td><td><input type='text' size='20' name='history_father' value="<?echo $result{"history_father"};?>"></tr>
    <tr><td class='text'>Mother</td><td><input type='text' size='20' name='history_mother' value="<?echo $result{"history_mother"};?>"></tr>
    <tr><td class='text'>Siblings</td><td><input type='text' size='20' name='history_siblings' value="<?echo $result{"history_siblings"};?>"></tr>
    <tr><td class='text'>Spouse</td><td><input type='text' size='20' name='history_spouse' value="<?echo $result{"history_spouse"};?>"></tr>
    <tr><td class='text'>Offspring&nbsp;</td><td><input type='text' size='20' name='history_offspring' value="<?echo $result{"history_offspring"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'>Relatives:</td></tr>
    <tr><td class='text'>Cancer</td><td><input type='text' size='20' name='relatives_cancer' value="<?echo $result{"relatives_cancer"};?>"></tr>
    <tr><td class='text'>Tuberculosis</td><td><input type='text' size='20' name='relatives_tuberculosis' value="<?echo $result{"relatives_tuberculosis"};?>"></tr>
    <tr><td class='text'>Diabetes</td><td><input type='text' size='20' name='relatives_diabetes' value="<?echo $result{"relatives_diabetes"};?>"></tr>
    <tr><td class='text'>High Blood Pressure&nbsp;</td><td><input type='text' size='20' name='relatives_high_blood_pressure' value="<?echo $result{"relatives_high_blood_pressure"};?>"></tr>
    <tr><td class='text'>Heart Problems</td><td><input type='text' size='20' name='relatives_heart_problems' value="<?echo $result{"relatives_heart_problems"};?>"></tr>
    <tr><td class='text'>Stroke</td><td><input type='text' size='20' name='relatives_stroke' value="<?echo $result{"relatives_stroke"};?>"></tr>
    <tr><td class='text'>Epilepsy</td><td><input type='text' size='20' name='relatives_epilepsy' value="<?echo $result{"relatives_epilepsy"};?>"></tr>
    <tr><td class='text'>Mental Illness</td><td><input type='text' size='20' name='relatives_mental_illness' value="<?echo $result{"relatives_mental_illness"};?>"></tr>
    <tr><td class='text'>Suicide</td><td><input type='text' size='20' name='relatives_suicide' value="<?echo $result{"relatives_suicide"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border=0 cellpadding=0 cellspacing=0>
    <tr><td colspan=2 class=bold>Lifestyle:</td></tr>
    <tr><td class='text'>Coffee</td><td><input type='text' size='20' name='coffee' value="<?echo $result{"coffee"};?>"></tr>
    <tr><td class='text'>Tobacco</td><td><input type='text' size='20' name='tobacco' value="<?echo $result{"tobacco"};?>"></tr>
    <tr><td class='text'>Alcohol</td><td><input type='text' size='20' name='alcohol' value="<?echo $result{"alcohol"};?>"></tr>
    <tr><td class='text'>Sleep Patterns</td><td><input type='text' size='20' name='sleep_patterns' value="<?echo $result{"sleep_patterns"};?>"></tr>
    <tr><td class='text'>Exercise Patterns</td><td><input type='text' size='20' name='exercise_patterns' value="<?echo $result{"exercise_patterns"};?>"></tr>
    <tr><td class='text'>Seatbelt Use</td><td><input type='text' size='20' name='seatbelt_use' value="<?echo $result{"seatbelt_use"};?>"></tr>
    <tr><td class='text'>Counseling</td><td><input type='text' size='20' name='counseling' value="<?echo $result{"counseling"};?>"></tr>
    <tr><td class='text'>Hazardous Activities&nbsp;</td><td><input type='text' size='20' name='hazardous_activities' value="<?echo $result{"hazardous_activities"};?>"></tr>
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
     <td colspan='2' class='bold'>Date/Notes of Last:</td>
     <td class='bold'>Nor&nbsp;</td>
     <td class='bold'>Abn</td>
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
    <tr><td colspan='2' class='bold'>Additional History:</td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_1' value="<?=$result{"name_1"}?>">:</td><td><input type='text' size='20' name='value_1' value="<?=$result{"value_1"}?>"></td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_2' value="<?=$result{"name_2"}?>">:</td><td><input type='text' size='20' name='value_2' value="<?=$result{"value_2"}?>"></td></tr>
   </table><br>
   <textarea cols="50" rows="5" name="additional_history"><?=$result{"additional_history"}?></textarea>
   <p>
   <input type='submit' value='Save' />&nbsp;
   <input type='button' value='To Issues' onclick='location="../summary/stats_full.php"' />&nbsp;
   <input type='button' value='Back' onclick='location="patient_history.php"' />
   <!--
   <a href="javascript:document.history_form.submit();" target=Main class=link_submit>[Save Patient History]</a>
   -->
  </td>
 </tr>
</table>

</form>

</body>
</html>
