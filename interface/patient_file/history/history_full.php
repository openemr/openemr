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

<form action="history_save.php" name=history_form method=post>
<input type=hidden name=mode value=save>


<a href="patient_history.php" target=Main><font class=title>Patient History / Lifestyle</font><font class=back><?echo $tback;?></font></a><br>


<table border=0 cellpadding=5>

<tr>
<td valign=top>

<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Family History:</span></td></tr>
<tr><td><span class=text>Father:</span></td><td><input type=entry size=20 name=history_father value="<?echo $result{"history_father"};?>"></tr>
<tr><td><span class=text>Mother:</span></td><td><input type=entry size=20 name=history_mother value="<?echo $result{"history_mother"};?>"></tr>
<tr><td><span class=text>Siblings:</span></td><td><input type=entry size=20 name=history_siblings value="<?echo $result{"history_siblings"};?>"></tr>
<tr><td><span class=text>Spouse:</span></td><td><input type=entry size=20 name=history_spouse value="<?echo $result{"history_spouse"};?>"></tr>
<tr><td><span class=text>Offspring:</span></td><td><input type=entry size=20 name=history_offspring value="<?echo $result{"history_offspring"};?>"></tr>
</table>
</td>


<td valign=top>

<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Relatives:</span></td></tr>
<tr><td><span class=text>Cancer:</span></td><td><input type=entry size=20 name=relatives_cancer value="<?echo $result{"relatives_cancer"};?>"></tr>
<tr><td><span class=text>Tuberculosis:</span></td><td><input type=entry size=20 name=relatives_tuberculosis value="<?echo $result{"relatives_tuberculosis"};?>"></tr>
<tr><td><span class=text>Diabetes:</span></td><td><input type=entry size=20 name=relatives_diabetes value="<?echo $result{"relatives_diabetes"};?>"></tr>
<tr><td><span class=text>High Blood Pressure:</span></td><td><input type=entry size=20 name=relatives_high_blood_pressure value="<?echo $result{"relatives_high_blood_pressure"};?>"></tr>
<tr><td><span class=text>Heart Problems: </span></td><td><input type=entry size=20 name=relatives_heart_problems value="<?echo $result{"relatives_heart_problems"};?>"></tr>
<tr><td><span class=text>Stroke:</span></td><td><input type=entry size=20 name=relatives_stroke value="<?echo $result{"relatives_stroke"};?>"></tr>
<tr><td><span class=text>Epilepsy:</span></td><td><input type=entry size=20 name=relatives_epilepsy value="<?echo $result{"relatives_epilepsy"};?>"></tr>
<tr><td><span class=text>Mental Illness:</span></td><td><input type=entry size=20 name=relatives_mental_illness value="<?echo $result{"relatives_mental_illness"};?>"></tr>
<tr><td><span class=text>Suicide:</span></td><td><input type=entry size=20 name=relatives_suicide value="<?echo $result{"relatives_suicide"};?>"></tr>

</table>
</td>


<td valign=top>
<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Lifestyle:</span></td></tr>
<tr><td><span class=text>Coffee:</span></td><td><input type=entry size=20 name=coffee value="<?echo $result{"coffee"};?>"></tr>
<tr><td><span class=text>Tobacco:</span></td><td><input type=entry size=20 name=tobacco value="<?echo $result{"tobacco"};?>"></tr>
<tr><td><span class=text>Alcohol:</span></td><td><input type=entry size=20 name=alcohol value="<?echo $result{"alcohol"};?>"></tr>
<tr><td><span class=text>Sleep Patterns:</span></td><td><input type=entry size=20 name=sleep_patterns value="<?echo $result{"sleep_patterns"};?>"></tr>
<tr><td><span class=text>Exercise Patterns:</span></td><td><input type=entry size=20 name=exercise_patterns value="<?echo $result{"exercise_patterns"};?>"></tr>
<tr><td><span class=text>Seatbelt Use:</span></td><td><input type=entry size=20 name=seatbelt_use value="<?echo $result{"seatbelt_use"};?>"></tr>
<tr><td><span class=text>Counseling:</span></td><td><input type=entry size=20 name=counseling value="<?echo $result{"counseling"};?>"></tr>
<tr><td><span class=text>Hazardous Activities:</span></td><td><input type=entry size=20 name=hazardous_activities value="<?echo $result{"hazardous_activities"};?>"></tr>
</table>
</td>


<td valign=top>

</td>

</tr>


<tr>




<td valign=top>
<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Date of Last:</span></td></tr>
<tr><td><span class=text>Cataract Surgery:</span></td><td><input type=entry size=20 name=cataract_surgery value="<?if ($result{"cataract_surgery"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"cataract_surgery"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Tonsillectomy:</span></td><td><input type=entry size=20 name=tonsillectomy value="<?if ($result{"tonsillectomy"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"tonsillectomy"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Appendectomy:</span></td><td><input type=entry size=20 name=appendectomy value="<?if ($result{"appendectomy"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"appendectomy"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Cholecystestomy:</span></td><td><input type=entry size=20 name=cholecystestomy value="<?if ($result{"cholecystestomy"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"cholecystestomy"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Heart Surgery:</span></td><td><input type=entry size=20 name=heart_surgery value="<?if ($result{"heart_surgery"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"heart_surgery"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Hysterectomy:</span></td><td><input type=entry size=20 name=hysterectomy value="<?if ($result{"hysterectomy"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"hysterectomy"}));} else {echo "YYYY-MM-DD";}?>"></tr>
</table>
</td>


<td valign=top>
<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Date of Last:</span></td></tr>
<tr><td><span class=text>Hernia Repair:</span></td><td><input type=entry size=20 name=hernia_repair value="<?if ($result{"hernia_repair"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"hernia_repair"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Hip Replacement:</span></td><td><input type=entry size=20 name=hip_replacement value="<?if ($result{"hip_replacement"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"hip_replacement"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Knee Replacement:</span></td><td><input type=entry size=20 name=knee_replacement value="<?if ($result{"knee_replacement"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"knee_replacement"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Breast Exam:</span></td><td><input type=entry size=20 name=last_breast_exam value="<?if ($result{"last_breast_exam"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_breast_exam"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Mammogram:</span></td><td><input type=entry size=20 name=last_mammogram value="<?if ($result{"last_mammogram"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_mammogram"}));} else {echo "YYYY-MM-DD";}?>"></tr>

</table>
</td>


<td valign=top>
<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Date of Last:</span></td></tr>
<tr><td><span class=text>Gynocological Exam:</span></td><td><input type=entry size=20 name=last_gynocological_exam value="<?if ($result{"last_gynocological_exam"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_gynocological_exam"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Rectal Exam:</span></td><td><input type=entry size=20 name=last_rectal_exam value="<?if ($result{"last_rectal_exam"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_rectal_exam"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Prostate Exam:</span></td><td><input type=entry size=20 name=last_prostate_exam value="<?if ($result{"last_prostate_exam"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_prostate_exam"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Physical Exam:</span></td><td><input type=entry size=20 name=last_physical_exam value="<?if ($result{"last_physical_exam"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_physical_exam"}));} else {echo "YYYY-MM-DD";}?>"></tr>
<tr><td><span class=text>Sigmoidoscopy/Colonoscopy:</span></td><td><input type=entry size=20 name=last_sigmoidoscopy_colonoscopy value="<?if ($result{"last_sigmoidoscopy_colonoscopy"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($result{"last_sigmoidoscopy_colonoscopy"}));} else {echo "YYYY-MM-DD";}?>"></tr>
</table>
</td>
</tr>
<tr>
<td colspan="2">
<table border=0 cellpadding=0 cellspacing=0>
<tr><td colspan=2><span class=bold>Additional History:</span></td></tr>
<tr><td><span class=text><input type=entry size=20 name=name_1 value="<?=$result{"name_1"}?>">:</span></td><td><input type=entry size=20 name=value_1 value="<?=$result{"value_1"}?>"></td></tr>
<tr><td><span class=text><input type=entry size=20 name=name_2 value="<?=$result{"name_2"}?>">:</span></td><td><input type=entry size=20 name=value_2 value="<?=$result{"value_2"}?>"></td></tr>
</table><br>
<textarea cols="50" rows="5" name="additional_history"><?=$result{"additional_history"}?></textarea>
</td>
</tr>
</table>
<a href="javascript:document.history_form.submit();" target=Main class=link_submit>[Save Patient History]</a>

</form>


</body>
</html>
