<?
include_once("../../globals.php");

include_once("$srcdir/patient.inc");
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?
$result = getHistoryData($pid);

if (!is_array($result)) {
	newHistoryData($pid);
	$result = getHistoryData($pid);	
}

?>

<a href="history_full.php" target=Main><font class=title>Patient History / Lifestyle</font><font class=more><?echo $tmore;?></font></a><br>


<table border=0 cellpadding=2>

<tr>
<td valign=top>
<span class=bold>Family History:</span><br>
<?if ($result{"history_father"} != "") {?><span class=text>Father: </span><span class=text><?echo $result{"history_father"};?></span><br><?}?>
<?if ($result{"history_mother"} != "") {?><span class=text>Mother: </span><span class=text><?echo $result{"history_mother"};?></span><br><?}?>
<?if ($result{"history_siblings"} != "") {?><span class=text>Siblings: </span><span class=text><?echo $result{"history_siblings"};?></span><br><?}?>
<?if ($result{"history_spouse"} != "") {?><span class=text>Spouse: </span><span class=text><?echo $result{"history_spouse"};?></span><br><?}?>
<?if ($result{"history_offspring"} != "") {?><span class=text>Offspring: </span><span class=text><?echo $result{"history_offspring"};?></span><br><?}?>
</td>

<td valign=top>
<span class=bold>Patient Had:</span><br>
<?if ($result{"cataract_surgery"} != "0000-00-00 00:00:00") {?><span class=text>Cataract Surgery: </span><span class=text><?echo date("n/j/Y",strtotime($result{"cataract_surgery"}));?></span><br><?}?>
<?if ($result{"tonsillectomy"} != "0000-00-00 00:00:00") {?><span class=text>Tonsillectomy: </span><span class=text><?echo date("n/j/Y",strtotime($result{"tonsillectomy"}));?></span><br><?}?>
<?if ($result{"appendectomy"} != "0000-00-00 00:00:00") {?><span class=text>Appendectomy: </span><span class=text><?echo date("n/j/Y",strtotime($result{"appendectomy"}));?></span><br><?}?>
<?if ($result{"cholecystestomy"} != "0000-00-00 00:00:00") {?><span class=text>Cholecystestomy: </span><span class=text><?echo date("n/j/Y",strtotime($result{"cholecystestomy"}));?></span><br><?}?>
<?if ($result{"heart_surgery"} != "0000-00-00 00:00:00") {?><span class=text>Heart Surgery: </span><span class=text><?echo date("n/j/Y",strtotime($result{"heart_surgery"}));?></span><br><?}?>
<?if ($result{"hysterectomy"} != "0000-00-00 00:00:00") {?><span class=text>Hysterectomy: </span><span class=text><?echo date("n/j/Y",strtotime($result{"hysterectomy"}));?></span><br><?}?>
<?if ($result{"hernia_repair"} != "0000-00-00 00:00:00") {?><span class=text>Hernia Repair: </span><span class=text><?echo date("n/j/Y",strtotime($result{"hernia_repair"}));?></span><br><?}?>
<?if ($result{"hip_replacement"} != "0000-00-00 00:00:00") {?><span class=text>Hip Replacement: </span><span class=text><?echo date("n/j/Y",strtotime($result{"hip_replacement"}));?></span><br><?}?>
<?if ($result{"knee_replacement"} != "0000-00-00 00:00:00") {?><span class=text>Knee Replacement: </span><span class=text><?echo date("n/j/Y",strtotime($result{"knee_replacement"}));?></span><br><?}?>
</td>

<td valign=top>
<span class=bold>Lifestyle:</span><br>
<?if ($result{"coffee"} != "") {?><span class=text>Coffee: </span><span class=text><?echo $result{"coffee"};?></span><br><?}?>
<?if ($result{"tobacco"} != "") {?><span class=text>Tobacco: </span><span class=text><?echo $result{"tobacco"};?></span><br><?}?>
<?if ($result{"alcohol"} != "") {?><span class=text>Alcohol: </span><span class=text><?echo $result{"alcohol"};?></span><br><?}?>
<?if ($result{"sleep_patterns"} != "") {?><span class=text>Sleep Patterns: </span><span class=text><?echo $result{"sleep_patterns"};?></span><br><?}?>
<?if ($result{"exercise_patterns"} != "") {?><span class=text>Exercise Patterns: </span><span class=text><?echo $result{"exercise_patterns"};?></span><br><?}?>
<?if ($result{"seatbelt_use"} != "") {?><span class=text>Seatbelt Use: </span><span class=text><?echo $result{"seatbelt_use"};?></span><br><?}?>
<?if ($result{"counseling"} != "") {?><span class=text>Counseling: </span><span class=text><?echo $result{"counseling"};?></span><br><?}?>
<?if ($result{"hazardous_activities"} != "") {?><span class=text>Hazardous Activities: </span><span class=text><?echo $result{"hazardous_activities"};?></span><br><?}?>




</td>

</tr>


<tr>
<td valign=top>
<span class=bold>Relatives:</span><br>
<?if ($result{"relatives_cancer"} != "") {?><span class=text>Cancer: </span><span class=text><?echo $result{"relatives_cancer"};?></span><br><?}?>
<?if ($result{"relatives_tuberculosis"} != "") {?><span class=text>Tuberculosis: </span><span class=text><?echo $result{"relatives_tuberculosis"};?></span><br><?}?>
<?if ($result{"relatives_diabetes"} != "") {?><span class=text>Diabetes: </span><span class=text><?echo $result{"relatives_diabetes"};?></span><br><?}?>
<?if ($result{"relatives_high_blood_pressure"} != "") {?><span class=text>High Blood Pressure: </span><span class=text><?echo $result{"relatives_high_blood_pressure"};?></span><br><?}?>
<?if ($result{"relatives_heart_problems"} != "") {?><span class=text>Heart Problems: </span><span class=text><?echo $result{"relatives_heart_problems"};?></span><br><?}?>
<?if ($result{"relatives_stroke"} != "") {?><span class=text>Stroke: </span><span class=text><?echo $result{"relatives_stroke"};?></span><br><?}?>
<?if ($result{"relatives_epilepsy"} != "") {?><span class=text>Epilepsy: </span><span class=text><?echo $result{"relatives_epilepsy"};?></span><br><?}?>
<?if ($result{"relatives_mental_illness"} != "") {?><span class=text>Mental Illness: </span><span class=text><?echo $result{"relatives_mental_illness"};?></span><br><?}?>
<?if ($result{"relatives_suicide"} != "") {?><span class=text>Suicide: </span><span class=text><?echo $result{"relatives_suicide"};?></span><br><?}?>


</td>

<td valign=top>
<span class=bold>Date of Last:</span><br>
<?if ($result{"last_breast_exam"} != "0000-00-00 00:00:00") {?><span class=text>Breast Exam: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_breast_exam"}));?></span><br><?}?>
<?if ($result{"last_mammogram"} != "0000-00-00 00:00:00") {?><span class=text>Mammogram: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_mammogram"}));?></span><br><?}?>
<?if ($result{"last_gynocological_exam"} != "0000-00-00 00:00:00") {?><span class=text>Gynocological Exam: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_gynocological_exam"}));?></span><br><?}?>
<?if ($result{"last_rectal_exam"} != "0000-00-00 00:00:00") {?><span class=text>Rectal Exam: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_rectal_exam"}));?></span><br><?}?>
<?if ($result{"last_prostate_exam"} != "0000-00-00 00:00:00") {?><span class=text>Prostate Exam: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_prostate_exam"}));?></span><br><?}?>
<?if ($result{"last_physical_exam"} != "0000-00-00 00:00:00") {?><span class=text>Physical Exam: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_physical_exam"}));?></span><br><?}?>
<?if ($result{"last_sigmoidoscopy_colonoscopy"} != "0000-00-00 00:00:00") {?><span class=text>Sigmoidoscopy/Colonoscopy: </span><span class=text><?echo date("n/j/Y",strtotime($result{"last_sigmoidoscopy_colonoscopy"}));?></span><br><?}?>

</td>
</tr>
<tr>
<td valign=top>
<?if (!empty($result{"name_1"})) {?><span class=text><b><?=$result{"name_1"}?></b>: </span><span class=text><?echo $result{"value_1"}?></span><br><?}?>
<?if (!empty($result{"name_2"})) {?><span class=text><b><?=$result{"name_2"}?></b>: </span><span class=text><?echo $result{"value_2"}?></span><br><?}?>
</td>
</tr>
<tr>
<td>
<?if (!empty($result{"additional_history"})) {?><span class=text><?echo $result{"additional_history"}?></span><br><?}?>
</td>
</tr>

</table>






</body>
</html>
