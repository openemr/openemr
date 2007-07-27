<!-- Ankle Injury View Form created by Nikolai Vitsyn by 2004/02/19 -->
<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_ankleinjury", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/ankleinjury/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl('Ankle Evaluation Form','e');?></span><br></br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e');?>]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e');?>]</a>
<br></br>

<span class=text>Date of Injury: </span><input type=entry name="ankle_date_of_injuary" value="<?echo stripslashes($obj{"ankle_date_of_injuary"});?>" >
<td align="right"><?php xl('Work related?','e');?>:</td>
<td><input type=checkbox name="ankle_work_related" <?if ($obj{"ankle_work_related"} == "on")
echo "checked";;?>><span class=text></span><br></td>

<table >
<tr>
<td align="right"><?php xl('Foot:','e');?></td>
<td><input type=radio name="ankle_foot" value="Left" <?if ($obj{"ankle_foot"} == "Left")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Left:','e');?></td>
<td><input type=radio name="ankle_foot" value="Right" <?if ($obj{"ankle_foot"} == "Right")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Right:','e');?></td>
</tr>
</table>

<table >
<tr>
<td align="right"><?php xl('Severity of Pain:','e');?></td>
<td align="right">1:</td>
<td><input type=radio name="ankle_severity_of_pain" value="1" <?if ($obj{"ankle_severity_of_pain"} == "1")
{echo "checked";};?>><span class=text></span><br></td>

<td align="right">2:</td>
<td><input type=radio name="ankle_severity_of_pain" value="2" <?if ($obj{"ankle_severity_of_pain"} == "2")
{echo "checked";};?>><span class=text></span><br></td>

<td align="right">3:</td>
<td><input type=radio name="ankle_severity_of_pain" value="3" <?if ($obj{"ankle_severity_of_pain"} == "3")
{echo "checked";};?>><span class=text></span><br></td>
</tr>
</table>

<table><tr>
<td align="right"><?php xl('Significant Swelling:','e');?></td>
<td><input type=checkbox name="ankle_significant_swelling" <?if ($obj{"ankle_significant_swelling"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>


<table >
<tr>
<td align="right"><?php xl('Onset of Swelling:','e');?></td>
<td><input type=radio name="ankle_onset_of_swelling" value="within minutes" <?if ($obj{"ankle_onset_of_swelling"} == "within minutes")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('within minutes:','e');?></td>
<td><input type=radio name="ankle_onset_of_swelling" value="within hours" <?if ($obj{"ankle_onset_of_swelling"} == "within hours")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('within hours:','e');?></td>
</tr>
</table>

<span class="text"><?php xl('How did Injury Occur?:','e');?></span></br>
<textarea name="ankle_how_did_injury_occur" cols ="67" rows="4"  wrap="virtual name">
<?echo stripslashes($obj{"ankle_how_did_injury_occur"});?></textarea>
<br>

<table><th colspan="5"><?php xl('Ottawa Ankle Rules','e');?></th>
<tr>
<td align="right"><?php xl('Bone Tenderness:','e');?></td>
<td align="right"><?php xl('Medial malleolus:','e');?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="Medial malleolus" <?if ($obj{"ankle_ottawa_bone_tenderness"} == "Medial malleolus")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Lateral malleolus:','e');?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness"  value="Lateral malleolus" <?if ($obj{"ankle_ottawa_bone_tenderness"} == "Lateral malleolus")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Base of fifth (5th) Metarsal:','e');?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="Base of fifth (5th) Metarsal" <?if ($obj{"ankle_ottawa_bone_tenderness"} == "Base of fifth (5th) Metarsal")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('At the Navicular:','e');?></td>
<td><input type=radio name="ankle_ottawa_bone_tenderness" value="At the Navicular" <?if ($obj{"ankle_ottawa_bone_tenderness"} == "At the Navicular")
{echo "checked";};?>><span class=text></span><br></td>
</tr>
</table>

<table >
<tr>
<td align="right"><?php xl('Able to Bear Weight four (4) steps:','e');?></td>
<td align="right"><?php xl('Yes:','e');?></td>
<td><input type=radio name="ankle_able_to_bear_weight_steps" value="Yes" <?if ($obj{"ankle_able_to_bear_weight_steps"} == "Yes")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('No:','e');?></td>
<td><input type=radio name="ankle_able_to_bear_weight_steps" value="No" <?if ($obj{"ankle_able_to_bear_weight_steps"} == "No")
{echo "checked";};?>><span class=text></span><br></td>
</tr>
</table>

<table>
<tr><th><?php xl('X-Ray Interpretation:','e');?></th> <th><?php xl('Additional X-RAY Notes:','e');?></th></tr>
<tr>
<td>
<input type=entry name="ankle_x_ray_interpretation" value="<?echo
stripslashes($obj{"ankle_x_ray_interpretation"});?>" size="50"> 
</td>
<td rowspan=2>
<textarea name="ankle_additional_x_ray_notes" cols ="30" rows="1" wrap="virtual name">
<?echo stripslashes($obj{"ankle_additional_x_ray_notes"});?></textarea>
<td>
</tr>
</table>

<table>
<tr><th><?php xl('Diagnosis:','e');?></th><th><?php xl('Additional Diagnosis:','e');?></th></tr>
<tr>
<td><input type=entry name="ankle_diagnosis1" value="<?echo
stripslashes($obj{"ankle_diagnosis1"});?>" size="50">
</td>
<td rowspan=2>
<textarea name="ankle_additional_diagnisis" rows="2" cols="30" wrap="virtual name">
<?echo stripslashes($obj{"ankle_additional_diagnisis"});?></textarea>
</td>

<tr>
<td><input type=entry name="ankle_diagnosis2" value="<?echo
stripslashes($obj{"ankle_diagnosis2"});?>" size="50"></td>
</tr> 
<td><input type=entry name="ankle_diagnosis3" value="<?echo
stripslashes($obj{"ankle_diagnosis3"});?>" size="50"></td>
</tr>
<td><input type=entry name="ankle_diagnosis4" value="<?echo
stripslashes($obj{"ankle_diagnosis4"});?>" size="50"></td>
</tr>
</table>

<table><tr><th><?php xl('Plan:','e');?></th><tr>
<tr><td>
<textarea name="ankle_plan" rows="4" cols="67" wrap="virtual name">
<?echo stripslashes($obj{"ankle_plan"});?></textarea>
</td></tr></table>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e');?>]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e');?>]</a>
</form>
<?php
formFooter();
?>
