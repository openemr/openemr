<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");


$obj = formFetch("form_individual_treatment_plan", $_GET["id"]);


?>
<form method=post action="<?echo $rootdir?>/forms/individual_treatment_plan/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><center><b>Individual Treatment Plan</b></center></span><br><br>
<center>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a></center>
<br></br>

<? /* From New */ ?>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>

<b>Date of Referral:</b>&nbsp;<input type="text" name="date_of_referal" value="<? echo stripslashes($obj{"date_of_referal"});?>"> 
<img src="../../../images/space.gif" width="260" height="1">
<b>Date of Plan:</b>&nbsp; <?php print date('m/d/y'); ?><br><br>

<img src="../../../images/space.gif" width="28" height="1">
<b>Client Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="292" height="1">
<b>DCN:</b>
<img src="../../../images/space.gif" width="1" height="1">
<input type="text" name="dcn" value="<? echo stripslashes($obj{"dcn"});?>"> <br><br>

<b>ICD/9/CM Code:</b>&nbsp;<input type="text" name="icd9" value="<? echo stripslashes($obj{"icd9"});?>">
<img src="../../../images/space.gif" width="200" height="1">
<b>Prognosis:</b>&nbsp;<input type="text" name="prognosis" value="<? echo stripslashes($obj{"prognosis"});?>"><br><br>

<b>Diagnosis Description:</b><br>
<textarea cols=85 rows=2 wrap=virtual name="diagnosis_description" ><? echo stripslashes($obj{"diagnosis_description"});?></textarea><br><br>

<b>Presenting Problem Description and Psychosocial Information:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="presenting_problem" ><? echo stripslashes($obj{"presenting_problem"});?></textarea><br><br>

<b>Frequency:</b>&nbsp;<input type="text" name="frequency" size="12" maxlength="10" value="<? echo stripslashes($obj{"frequency"});?>">
<img src="../../../images/space.gif" width="40" height="1">
<b>Duration:</b>&nbsp;<input type="text" name="duration" size="12" maxlength="10" value="<? echo stripslashes($obj{"duration"});?>">
<img src="../../../images/space.gif" width="40" height="1">
<b>Scope:</b>&nbsp;<input type="text" name="scope" size="12" maxlength="10" value="<? echo stripslashes($obj{"scope"});?>"><br><br>

<b>Short Term Goals:</b>
<img src="../../../images/space.gif" width="162" height="1">
<b>Time Frame:</b><br>
<input type="text" name="short_term_goals_1" size="42" maxlength="40" value="<? echo stripslashes($obj{"short_term_goals_1"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_1" size="16" maxlength="15" value="<? echo stripslashes($obj{"time_frame_1"});?>"><br>

<input type="text" name="short_term_goals_2" size="42" maxlength="40" value="<? echo stripslashes($obj{"short_term_goals_2"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_2" size="16" maxlength="15" value="<? echo stripslashes($obj{"time_frame_2"});?>"><br>

<input type="text" name="short_term_goals_3" size="42" maxlength="40" value="<? echo stripslashes($obj{"short_term_goals_3"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_3" size="16" maxlength="15" value="<? echo stripslashes($obj{"time_frame_3"});?>"><br><br>

<b>Long Term Goals:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="long_term_goals" ><? echo stripslashes($obj{"long_term_goals"});?></textarea><br><br>

<b>Discharge Criteria:</b><br>
<textarea cols=85 rows=2 wrap=virtual name="discharge_criteria" ><? echo stripslashes($obj{"discharge_criteria"});?></textarea><br><br>

<b>Recommendations:</b><br>
<input type="checkbox" name="individual_family_therapy" <? if ($obj{"individual_family_therapy"} == "on") {echo "checked";};?>>&nbsp;<b>Individual and / or Family Therapy</b></input>
<img src="../../../images/space.gif" width="6" height="1">
<input type="checkbox" name="substance_abuse" <? if ($obj{"substance_abuse"} == "on") {echo "checked";};?>>&nbsp;<b>Substance Abuse</b></input><br>

<input type="checkbox" name="group_therapy" <? if ($obj{"group_therapy"} == "on") {echo "checked";};?>>&nbsp;<b>Group Therapy - psychoeducational group</b></input>
<img src="../../../images/space.gif" width="6" height="1">
<input type="checkbox" name="parenting" <? if ($obj{"parenting"} == "on") {echo "checked";};?>>&nbsp;<b>Parenting</b></input><br><br>

<b>Action Steps by supports - family:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="action_steps_by_supports" ><? echo stripslashes($obj{"action_steps_by_supports"});?></textarea><br><br>

<b>Other supports - agencies</b>
<b>Name:</b>
<img src="../../../images/space.gif" width="38" height="1">
<b>Contact Information</b><br>
<input type="text" name="other_supports_name_1" size="37" maxlength="35" value="<? echo stripslashes($obj{"other_supports_name_1"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="other_supports_contact_1" size="37" maxlength="35" value="<? echo stripslashes($obj{"other_supports_contact_1"});?>"><br>

<input type="text" name="other_supports_name_2" size="37" maxlength="35" value="<? echo stripslashes($obj{"other_supports_name_2"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="other_supports_contact_2" size="37" maxlength="35" value="<? echo stripslashes($obj{"other_supports_contact_2"});?>"><br><br>

<b>Medications</b>
<img src="../../../images/space.gif" width="204" height="1">
<b>Referrals</b><br>

<input type="text" name="medications_1" size="42" maxlength="40" value="<? echo stripslashes($obj{"medications_1"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="referrals_1" size="42" maxlength="40" value="<? echo stripslashes($obj{"referrals_1"});?>"><br>

<input type="text" name="medications_2" size="42" maxlength="40" value="<? echo stripslashes($obj{"medications_2"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="referrals_2" size="42" maxlength="40" value="<? echo stripslashes($obj{"referrals_2"});?>"><br><br>

<? /* From New */ ?>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
