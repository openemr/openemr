<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: mh_therapy_progress");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/mh_therapy_progress/save.php?mode=new" name="my_form">
<br>
<span class="title"><center>Therapy Progress Note</center></span><br><br>
<center><a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit"
 onclick="top.restoreSession()">[Don't Save]</a></center>
<br>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>
<b><u>A. Client and meeting Information</u></b><br><br>
<b>Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="125" height="1">
<b>Date:</b>&nbsp; <?php print date('m/d/y'); ?>
<img src="../../../images/space.gif" width="72" height="1">
<b>Time In:</b>&nbsp;<input type="text" name="time_in"> 
<img src="../../../images/space.gif" width="14" height="1">
<b>Time Out:</b>&nbsp;<input type="text" name="time_out"><br><br>

<b>Meeting was:</b>&nbsp;<input type=checkbox name='meeting_scheduled'><b>Scheduled</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='meeting_emergency'><b>Emergency</b>
<img src="../../../images/space.gif" width="12" height="1">
<b>Others Present</b><img src="../../../images/space.gif" width="6" height="1">
<textarea cols=55 rows=1 wrap=virtual name="others_present" ></textarea><br><br>

<b>Meeting lasted:</b>&nbsp;<input type="text" name="meeting_lasted">&nbsp;<b>minutes</b>
<img src="../../../images/space.gif" width="18" height="1">
<b>Number of Units</b>&nbsp;<input type="text" name="number_of_units">
<img src="../../../images/space.gif" width="18" height="1">
<b>ICD-9 Diagnosis</b>&nbsp;<input type="text" name="icd9"><br><br>

<b>Client:</b>&nbsp;<input type=checkbox name='client_ontime'><b>Was on time</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='client_late'><b>Was late by</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="was_late_by">
<img src="../../../images/space.gif" width="6" height="1">
<b>min.</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='did_not_show'>&nbsp;<b>Did not show</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='cancelled'>&nbsp;<b>Cancelled</b>
<img src="../../../images/space.gif" width="18" height="1">
<b>Next Meeting</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="next_meeting"><br><br>

<b>Location:</b>&nbsp;
<input type=checkbox name='location_office'>&nbsp;<b>Office</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_detention'>&nbsp;<b>Detention</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_home'>&nbsp;<b>Home</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_school'>&nbsp;<b>School</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_hosp'>&nbsp;<b>Hosp.</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='location_other'>&nbsp;<b>Other</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="location_other_block">
<img src="../../../images/space.gif" width="6" height="1">
<b>Facility Code:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="location_facility_code"><br><br>

<b>Treatment:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_individual_therapy'>&nbsp;<b>Individual therapy</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_family'>&nbsp;<b>Family</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_group'>&nbsp;<b>Group</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_couple'>&nbsp;<b>Couple</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_assessment'>&nbsp;<b>Assessment</b>
<img src="../../../images/space.gif" width="6" height="1">
<b>Service Code:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="treatment_service_code"><br><br>

<b>Pay Source:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_medicaid'>&nbsp;<b>Medicaid</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_dfs'>&nbsp;<b>DFS</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_private'>&nbsp;<b>Private</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='paysource_other'>&nbsp;<b>Other</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="paysource_other_block">
<img src="../../../images/space.gif" width="18" height="1">
<b>County:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="paysource_county"><br><br>

<br>
<b><u>B. Topic of Discussion (Content)</u></b><br>
<textarea cols=120 rows=7 wrap=virtual name="topics_of_discussion" ></textarea><br><br>

<b><u>C. Progress Toward Goals (Process)</u></b><br>
<textarea cols=120 rows=7 wrap=virtual name="progress_towards_goals" ></textarea><br><br>

<b><u>D. Client Description and Assessment</u></b><br><br>

<b>Medications:</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=107 rows=1 wrap=virtual name="medications" ></textarea><br><br>

<b>Report of Functioning since last session:</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=84 rows=1 wrap=virtual name="functioning_since_session" ></textarea><br><br>

<b>Mood:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_normal'>&nbsp;<b>Normal/euthymic</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_anxious'>&nbsp;<b>Anxious</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_depressed'>&nbsp;<b>Depressed</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_angry'>&nbsp;<b>Angry</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mood_euphoric'>&nbsp;<b>Euphoric</b><br><br>

<b>Affect:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_normal'>&nbsp;<b>Normal/appropriate</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_intense'>&nbsp;<b>Intense</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_blunted'>&nbsp;<b>Blunted</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_inappropriate'>&nbsp;<b>Inappropriate</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='affect_labile'>&nbsp;<b>Labile</b><br><br>

<b>Mental status:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_normal'>&nbsp;<b>Normal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_lessened_awareness'>&nbsp;<b>Lessened awareness</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_memory_deficiencies'>&nbsp;<b>Memory Deficiencies</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_disorientated'>&nbsp;<b>Disoriented</b>
<img src="../../../images/space.gif" width="6" height="1">
<br><input type=checkbox name='mentalstatus_disorganized'>&nbsp;<b>Disorganized</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_vigilant'>&nbsp;<b>Vigilant</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_delusional'>&nbsp;<b>Delusional</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_hallucinating'>&nbsp;<b>Hallucinating</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='mentalstatus_other'>&nbsp;<b>Other:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="mentalstatus_other_block"><br><br>

<b>Suicide/violence risk:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_none'>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_ideation_only'>&nbsp;<b>Ideation only</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_threat'>&nbsp;<b>Threat</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_gesture'>&nbsp;<b>Gesture</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_rehearsal'>&nbsp;<b>Rehearsal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='suicide_violance_risk_attempt'>&nbsp;<b>Attempt</b><br><br>

<b>Sleep quality:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_normal'>&nbsp;<b>Normal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_restless'>&nbsp;<b>Restless</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_insomnia'>&nbsp;<b>Insomnia</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_nightmares'>&nbsp;<b>Nightmares</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='sleep_quality_oversleeps'>&nbsp;<b>Oversleeps</b><br><br>

<b>Participation level:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_active'>&nbsp;<b>Active/eager</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_variable'>&nbsp;<b>Variable</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_only_responsive'>&nbsp;<b>Only responsive</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_minimal'>&nbsp;<b>Minimal</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_none'>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='participation_level_resistant'>&nbsp;<b>Resistant</b>
<br><br>

<b>Treatment_compliance:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_full'>&nbsp;<b>Full</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_partial'>&nbsp;<b>Partial</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='treatment_compliance_low'>&nbsp;<b>Low</b><br><br>

<b>Response to treatment:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_as_expected'>&nbsp;<b>As expected</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_better'>&nbsp;<b>Better than expected</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_much'>&nbsp;<b>Much</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_poorer'>&nbsp;<b>Poorer</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='response_to_treatment_very_poor'>&nbsp;<b>Very poor</b><br><br>

<b>GAF (Global Assessment of Functioning) from 100 to 0 is currently:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="gaf"><br><br>

<b>Other observations/evaluations:</b>
<img src="../../../images/space.gif" width="12" height="1">
<textarea cols=83 rows=1 wrap=virtual name="other_observations" ></textarea><br><br>

<b>Changes to diagnosis:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='diagnosis_changes_none'>&nbsp;<b>None</b>
<img src="../../../images/space.gif" width="6" height="1">
<textarea cols=83 rows=1 wrap=virtual name="diagnosis_changes" ></textarea><br><br>

<h5><b>If treatment was changed, Indicate rational, alternatives considered/rejected/selected in notes.</b></h5>

<b>G. Follow-Ups</b><br><br>

<input type=checkbox name='followups_next'>
<img src="../../../images/space.gif" width="6" height="1">
<b>Next appointment is scheduled for:</b>
<input type="text" name='followups_next_date'>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_week'>&nbsp;<b>Week</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_month'>&nbsp;<b>Month</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_2_months'>&nbsp;<b>2 Months</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_3_months'>&nbsp;<b>3 Months</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type=checkbox name='followups_next_as_needed'>&nbsp;<b>as needed</b><br><br>

<input type=checkbox name='followups_referral'>
<img src="../../../images/space.gif" width="6" height="1">
<b>Referral/consultation to:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_referral_to'>
<img src="../../../images/space.gif" width="6" height="1">
<b>For:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_referral_for'><br><br>

<input type=checkbox name='followups_call'>
<img src="../../../images/space.gif" width="6" height="1">
<b>Call/write to:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_call_to'>
<img src="../../../images/space.gif" width="6" height="1">
<b>For:</b>
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name='followups_call_for'><br>






<br><br>
<center><a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit"
 onclick="top.restoreSession()">[Don't Save]</a></center>
<br>
</form>
<?php
formFooter();
?>
