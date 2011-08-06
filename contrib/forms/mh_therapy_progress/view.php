<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<?php html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");

$obj = formFetch("form_mh_therapy_progress", $_GET["id"]);

?>
<form method=post action="<?echo $rootdir?>/forms/mh_therapy_progress/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><center><b>Therapy Progress Note</b></center></span><br><br>
<center>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a></center>
<br></br>

<?php /* From New */ ?>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>
<b><u>A. Client and meeting Information</u></b><br><br>
<b>Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="125" height="1">
<b>Date:</b>&nbsp; <?php print date('m/d/y'); ?>
<img src="../../../images/space.gif" width="72" height="1">
<b>Time In:</b>&nbsp;<input type="text" name="time_in" value="<?php echo stripslashes($obj{"time_in"});?>"> 
<img src="../../../images/space.gif" width="14" height="1">
<b>Time Out:</b>&nbsp;<input type="text" name="time_out" value="<?php echo stripslashes($obj{"time_out"});?>"><br><br>

<b>Meeting was:</b>&nbsp;<input type=checkbox name='meeting_scheduled' <?php if ($obj{"meeting_scheduled"} == "on") {echo "checked";};?>><b>Scheduled</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='meeting_emergency' <?php if ($obj{"meeting_emergency"} == "on") {echo "checked";};?>><b>Emergency</b>
<img src="../../../images/space.gif" width="12" height="1">
<b>Others Present</b><img src="../../../images/space.gif" width="6" height="1">
<textarea cols=55 rows=1 wrap=virtual name="others_present" ><?php echo stripslashes($obj{"others_present"});?></textarea><br><br>

<b>Meeting lasted:</b>&nbsp;<input type="text" name="meeting_lasted" value="<?php echo stripslashes($obj{"meeting_lasted"});?>">&nbsp;<b>minutes</b>
<img src="../../../images/space.gif" width="18" height="1">
<b>Number of Units</b>&nbsp;<input type="text" name="number_of_units" value="<?php echo stripslashes($obj{"number_of_units"});?>">
<img src="../../../images/space.gif" width="18" height="1">
<b>ICD-9 Diagnosis</b>&nbsp;<input type="text" name="icd9" value="<?php echo stripslashes($obj{"icd9"});?>"><br><br>

<b>Client:</b>&nbsp;<input type=checkbox name='client_ontime' <?php if ($obj{"client_ontime"} == "on") {echo "checked";};?>><b>Was on time</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='client_late' <?php if ($obj{"client_late"} == "on") {echo "checked";};?>><b>Was late by</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="was_late_by" value="<?php echo stripslashes($obj{"was_late_by"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<b>min.</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='did_not_show' <?php if ($obj{"did_not_show"} == "on") {echo "checked";};?>>&nbsp;<b>Did not show</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='cancelled' <?php if ($obj{"cancelled"} == "on") {echo "checked";};?>>&nbsp;<b>Cancelled</b>
<img src="../../../images/space.gif" width="18" height="1">
<b>Next Meeting</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="next_meeting" value="<?php echo stripslashes($obj{"next_meeting"});?>"><br><br>

<b>Location:</b>&nbsp;
<input type=checkbox name='location_office' <?php if ($obj{"location_office"} == "on") {echo "checked";};?>>&nbsp;<b>Office</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_detention' <?php if ($obj{"location_detention"} == "on") {echo "checked";};?>>&nbsp;<b>Detention</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_home' <?php if ($obj{"location_home"} == "on") {echo "checked";};?>>&nbsp;<b>Home</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_school' <?php if ($obj{"location_school"} == "on") {echo "checked";};?>>&nbsp;<b>School</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_hosp' <?php if ($obj{"location_hosp"} == "on") {echo "checked";};?>>&nbsp;<b>Hosp.</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_other' <?php if ($obj{"location_other"} == "on") {echo "checked";};?>>&nbsp;<b>Other</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="location_other_block" value="<?php echo stripslashes($obj{"location_other_block"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<b>Facility Code:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="location_facility_code"value="<?php echo stripslashes($obj{"location_facility_code"});?>"><br><br>

<b>Treatment:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_individual_therapy' <?php if ($obj{"treatment_individual_therapy"} == "on") {echo "checked";};?>>&nbsp;<b>Individual therapy</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_family' <?php if ($obj{"treatment_family"} == "on") {echo "checked";};?>>&nbsp;<b>Family</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_group' <?php if ($obj{"treatment_group"} == "on") {echo "checked";};?>>&nbsp;<b>Group</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_couple' <?php if ($obj{"treatment_couple"} == "on") {echo "checked";};?>>&nbsp;<b>Couple</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_assessment' <?php if ($obj{"treatment_assessment"} == "on") {echo "checked";};?>>&nbsp;<b>Assessment</b>
<img src="../../../images/space.gif" width="6" height="1">
<b>Service Code:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="treatment_service_code" value="<?php echo stripslashes($obj{"treatment_service_code"});?>"><br><br>

<b>Pay Source:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_medicaid' <?php if ($obj{"paysource_medicaid"} == "on") {echo "checked";};?>>&nbsp;<b>Medicaid</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_dfs' <?php if ($obj{"paysource_dfs"} == "on") {echo "checked";};?>>&nbsp;<b>DFS</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_private' <?php if ($obj{"paysource_private"} == "on") {echo "checked";};?>>&nbsp;<b>Private</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_other' <?php if ($obj{"paysource_other"} == "on") {echo "checked";};?>>&nbsp;<b>Other</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="paysource_other_block" value="<?php echo stripslashes($obj{"paysource_other_block"});?>">
<img src="../../../images/space.gif" width="18" height="1">
<b>County:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="paysource_county" value="<?php echo stripslashes($obj{"paysource_county"});?>"><br><br>

<br>
<b><u>B. Topic of Discussion (Content)</u></b><br>
<textarea cols=120 rows=7 wrap=virtual name="topics_of_discussion" ><?php echo stripslashes($obj{"topics_of_discussion"});?></textarea><br><br>

<b><u>C. Progress Toward Goals (Process)</u></b><br>
<textarea cols=120 rows=7 wrap=virtual name="progress_towards_goals" ><?php echo stripslashes($obj{"progress_towards_goals"});?></textarea><br><br>

<b><u>D. Client Description and Assessment</u></b><br><br>

<b>Medications:</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=107 rows=1 wrap=virtual name="medications" ><?php echo stripslashes($obj{"medications"});?></textarea><br><br>

<b>Report of Functioning since last session:</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=84 rows=1 wrap=virtual name="functioning_since_session" ><?php echo stripslashes($obj{"functioning_since_session"});?></textarea><br><br>

<b>Mood:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_normal' <?php if ($obj{"mood_normal"} == "on") {echo "checked";};?>>&nbsp;<b>Normal/euthymic</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_anxious' <?php if ($obj{"mood_anxious"} == "on") {echo "checked";};?>>&nbsp;<b>Anxious</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_depressed' <?php if ($obj{"mood_depressed"} == "on") {echo "checked";};?>>&nbsp;<b>Depressed</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_angry' <?php if ($obj{"mood_angry"} == "on") {echo "checked";};?>>&nbsp;<b>Angry</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_euphoric' <?php if ($obj{"mood_euphoric"} == "on") {echo "checked";};?>>&nbsp;<b>Euphoric</b><br><br>

<b>Affect:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_normal' <?php if ($obj{"affect_normal"} == "on") {echo "checked";};?>>&nbsp;<b>Normal/appropriate</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_intense' <?php if ($obj{"affect_intense"} == "on") {echo "checked";};?>>&nbsp;<b>Intense</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_blunted' <?php if ($obj{"affect_blunted"} == "on") {echo "checked";};?>>&nbsp;<b>Blunted</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_inappropriate' <?php if ($obj{"affect_inappropriate"} == "on") {echo "checked";};?>>&nbsp;<b>Inappropriate</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_labile' <?php if ($obj{"affect_labile"} == "on") {echo "checked";};?>>&nbsp;<b>Labile</b><br><br>

<b>Mental status:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_normal' <?php if ($obj{"mentalstatus_normal"} == "on") {echo "checked";};?>>&nbsp;<b>Normal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_lessened_awareness' <?php if ($obj{"mentalstatus_lessened_awareness"} == "on") {echo "checked";};?>>&nbsp;<b>Lessened awareness</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_memory_deficiencies' <?php if ($obj{"mentalstatus_memory_deficiencies"} == "on") {echo "checked";};?>>&nbsp;<b>Memory Deficiencies</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_disorientated' <?php if ($obj{"mentalstatus_disorientated"} == "on") {echo "checked";};?>>&nbsp;<b>Disoriented</b>
<img src="../../../images/space.gif" width="6" height="1">
<br><input type=checkbox name='mentalstatus_disorganized' <?php if ($obj{"mentalstatus_disorganized"} == "on") {echo "checked";};?>>&nbsp;<b>Disorganized</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_vigilant' <?php if ($obj{"mentalstatus_vigilant"} == "on") {echo "checked";};?>>&nbsp;<b>Vigilant</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_delusional' <?php if ($obj{"mentalstatus_delusional"} == "on") {echo "checked";};?>>&nbsp;<b>Delusional</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_hallucinating' <?php if ($obj{"mentalstatus_hallucinating"} == "on") {echo "checked";};?>>&nbsp;<b>Hallucinating</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_other' <?php if ($obj{"mentalstatus_other"} == "on") {echo "checked";};?>>&nbsp;<b>Other:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="mentalstatus_other_block" value="<?php echo stripslashes($obj{"mentalstatus_other_block"});?>" ><br><br>

<b>Suicide/violence risk:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_none'<?php if ($obj{"suicide_violance_risk_none"} == "on") {echo "checked";};?>>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_ideation_only' <?php if ($obj{"suicide_violance_risk_ideation_only"} == "on") {echo "checked";};?>>&nbsp;<b>Ideation only</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_threat' <?php if ($obj{"suicide_violance_risk_threat"} == "on") {echo "checked";};?>>&nbsp;<b>Threat</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_gesture' <?php if ($obj{"suicide_violance_risk_gesture"} == "on") {echo "checked";};?>>&nbsp;<b>Gesture</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_rehearsal' <?php if ($obj{"suicide_violance_risk_rehearsal"} == "on") {echo "checked";};?>>&nbsp;<b>Rehearsal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_attempt' <?php if ($obj{"suicide_violance_risk_attempt"} == "on") {echo "checked";};?>>&nbsp;<b>Attempt</b><br><br>

<b>Sleep quality:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_normal' <?php if ($obj{"sleep_quality_normal"} == "on") {echo "checked";};?>>&nbsp;<b>Normal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_restless' <?php if ($obj{"sleep_quality_restless"} == "on") {echo "checked";};?>>&nbsp;<b>Restless</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_insomnia' <?php if ($obj{"sleep_quality_insomnia"} == "on") {echo "checked";};?>>&nbsp;<b>Insomnia</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_nightmares' <?php if ($obj{"sleep_quality_nightmares"} == "on") {echo "checked";};?>>&nbsp;<b>Nightmares</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_oversleeps' <?php if ($obj{"sleep_quality_oversleeps"} == "on") {echo "checked";};?>>&nbsp;<b>Oversleeps</b><br><br>

<b>Participation level:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_active' <?php if ($obj{"participation_level_active"} == "on") {echo "checked";};?>>&nbsp;<b>Active/eager</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_variable'<?php if ($obj{"participation_level_variable"} == "on") {echo "checked";};?>>&nbsp;<b>Variable</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_only_responsive' <?php if ($obj{"participation_level_responsive"} == "on") {echo "checked";};?>>&nbsp;<b>Only responsive</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_minimal' <?php if ($obj{"participation_level_minimal"} == "on") {echo "checked";};?>>&nbsp;<b>Minimal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_none' <?php if ($obj{"participation_level_none"} == "on") {echo "checked";};?>>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_resistant' <?php if ($obj{"participation_level_resistant"} == "on") {echo "checked";};?>>&nbsp;<b>Resistant</b>
<br><br>

<b>Treatment_compliance:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_full' <?php if ($obj{"treatment_compliance_full"} == "on") {echo "checked";};?>>&nbsp;<b>Full</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_partial' <?php if ($obj{"treatment_compliance_partial"} == "on") {echo "checked";};?>>&nbsp;<b>Partial</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_low' <?php if ($obj{"treatment_compliance_low"} == "on") {echo "checked";};?>>&nbsp;<b>Low</b><br><br>

<b>Response to treatment:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_as_expected' <?php if ($obj{"response_to_treatment_as_expected"} == "on") {echo "checked";};?>>&nbsp;<b>As expected</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_better' <?php if ($obj{"response_to_treatment_better"} == "on") {echo "checked";};?>>&nbsp;<b>Better than expected</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_much' <?php if ($obj{"response_to_treatment_much"} == "on") {echo "checked";};?>>&nbsp;<b>Much</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_poorer' <?php if ($obj{"response_to_treatment_poorer"} == "on") {echo "checked";};?>>&nbsp;<b>Poorer</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_very_poor' <?php if ($obj{"response_to_treatment_very_poor"} == "on") {echo "checked";};?>>&nbsp;<b>Very poor</b><br><br>

<b>GAF (Global Assessment of Functioning) from 100 to 0 is currently:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="gaf" value="<?php echo stripslashes($obj{"gaf"});?>"><br><br>

<b>Other observations/evaluations:</b>
<img src="../../../images/space.gif" width="12" height="1">
<textarea cols=83 rows=1 wrap=virtual name="other_observations" ><?php echo stripslashes($obj{"other_observations"});?></textarea><br><br>

<b>Changes to diagnosis:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='diagnosis_changes_none' <?php if ($obj{"diagnosis_changes_none"} == "on") {echo "checked";};?>>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=83 rows=1 wrap=virtual name="diagnosis_changes" ><?php echo stripslashes($obj{"diagnosis_changes"});?></textarea><br><br>

<h5><b>If treatment was changed, Indicate rational, alternatives considered/rejected/selected in notes.</b></h5>

<b>G. Follow-Ups</b><br><br>

<input type=checkbox name='followups_next' <?php if ($obj{"followups_next"} == "on") {echo "checked";};?>>
<img src="../../../images/space.gif" width="6" height="1">
<b>Next appointment is scheduled for:</b>
<input type="text" name='followups_next_date'  value="<?php echo stripslashes($obj{"followups_next_date"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_week' <?php if ($obj{"followups_next_week"} == "on") {echo "checked";};?>>&nbsp;<b>Week</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_month' <?php if ($obj{"followups_next_month"} == "on") {echo "checked";};?>>&nbsp;<b>Month</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_2_months' <?php if ($obj{"followups_next_2_months"} == "on") {echo "checked";};?>>&nbsp;<b>2 Months</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_3_months' <?php if ($obj{"followups_next_3_months"} == "on") {echo "checked";};?>>&nbsp;<b>3 Months</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_as_needed' <?php if ($obj{"followups_next_as_needed"} == "on") {echo "checked";};?>>&nbsp;<b>as needed</b><br><br>

<input type=checkbox name='followups_referral' <?php if ($obj{"followups_referral"} == "on") {echo "checked";};?>>
<img src="../../../images/space.gif" width="6" height="1">
<b>Referral/consultation to:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_referral_to' value="<?php echo stripslashes($obj{"followups_referral_to"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<b>For:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_referral_for'  value="<?php echo stripslashes($obj{"followups_referral_for"});?>"><br><br>

<input type=checkbox name='followups_call' <?php if ($obj{"followups_call"} == "on") {echo "checked";};?>>
<img src="../../../images/space.gif" width="6" height="1">
<b>Call/write to:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_call_to'  value="<?php echo stripslashes($obj{"followups_call_to"});?>">
<img src="../../../images/space.gif" width="6" height="1">
<b>For:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_call_for'  value="<?php echo stripslashes($obj{"followups_call_for"});?>"><br>

<?php /* From New */ ?>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
