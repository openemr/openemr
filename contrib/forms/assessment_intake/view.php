<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");


$obj = formFetch("form_assessment_intake", $_GET["id"]);


?>
<form method=post action="<?echo $rootdir?>/forms/assessment_intake/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><center><b>Assessment and Intake</b></center></span><br><br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>
<br></br>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>
<b>Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="572" height="1">
<b>Date:</b>&nbsp; <?php print date('m/d/y'); ?><br><br>
<b>SSN:</b>&nbsp;<?php echo $result['ss'];?><img src="../../../images/space.gif" width="172" height="1">
<b>DCN:</b>&nbsp;<input type="entry" name="dcn" value="<? echo stripslashes($obj{"dcn"});?>"><img src="../../../images/space.gif" width="125" height="1">
<label><b>Location:</b>&nbsp;<input type="entry" name="location" value="<? echo stripslashes($obj{"location"});?>"></label><br><br>
<b>Address:</b>&nbsp; <?php echo $result['street'] . ',&nbsp' . $result['city']  . ',&nbsp' . $result['state'] . '&nbsp;' . $result['postal_code'];?><br><br>
<b>Telephone Number:</b>&nbsp; <?php echo $result['phone_home'];?><img src="../../../images/space.gif" width="400" height="1"> 
<b>Date of Birth:</b>&nbsp;<?php echo $result['DOB'];?><br><br>
<label><b>Time In:</b>&nbsp;<input type="entry" name="time_in" value="<? echo stripslashes($obj{"time_in"});?>"></label><img src="../../../images/space.gif" width="65" height="1">
<label><b>Time Out:</b>&nbsp;<input type="entry" name="time_out" value="<? echo stripslashes($obj{"time_out"});?>"></label><img src="../../../images/space.gif" width="65" height="1">
<label><b>Referral Source:</b>&nbsp;<input type="entry" name="referral_source" value="<? echo stripslashes($obj{"referral_source"});?>"></label><br><br>
<b>Purpose:</b>&nbsp; <input type=checkbox name='new_client_eval' <? if ($obj{"new_client_eval"} == "on") {echo "checked";};?>  ><b>New client evaluation</b><img src="../../../images/space.gif" width="10" height="1">
<input type=checkbox name='readmission' <? if ($obj{"readmission"} == "on") {echo "checked";};?>  ><b>Readmission</b><img src="../../../images/space.gif" width="35" height="1">
<input type=checkbox name='consultation' <? if ($obj{"consultation"} == "on") {echo "checked";};?> ><b>Consultation</b><br><br> 
<label><b>Copy sent to:</b>&nbsp;<input type="entry" name="copy_sent_to" value="<?echo stripslashes($obj{"copy_sent_to"});?>"></label><br><br>
<b>Why is Assessment being requested (Goals and treatment expectations of the individual requesting services):</b><br>
<textarea cols=100 rows=3 name="reason_why" ><? echo stripslashes($obj{"reason_why"});?></textarea><br>
<b>Behavior that led to Assessment:</b><br>
<textarea cols=100 rows=5 wrap=virtual name="behavior_led_to" ><? echo stripslashes($obj{"behavior_led_to"});?></textarea><br><br>
<b><u></u>Areas of Functioning:</b><br><br>
<b>School/Work:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="school_work" ><? echo stripslashes($obj{"school_work"});?></textarea><br><br>
<b>Personal Relationships (Intimate):</b>&nbsp;
<textarea cols=100 rows=4 wrap=virtual name="personal_relationships" ><? echo stripslashes($obj{"personal_relationships"});?></textarea><br><br>
<b>Family Relationships:</b>&nbsp; &nbsp;
<input type=checkbox name='fatherc' <? if ($obj{"fatherc"} == "on") {echo "checked";};?>  >&nbsp;<b>Father involved/present/absent (Describe relationship)</b><br>
<textarea cols=100 rows=3 wrap=virtual name="father_involved" ><? echo stripslashes($obj{"father_involved"});?></textarea><br>
<input type=checkbox name='motherc' <? if ($obj{"motherc"} == "on") {echo "checked";};?>  >&nbsp;<b>Mother involved/present/absent (Describe relationship)</b><br>
<textarea cols=100 rows=3 wrap=virtual name="mother_involved" ><? echo stripslashes($obj{"mother_involved"});?></textarea><br><br>
<b>Number of children:</b>&nbsp;<input type="entry" name="number_children"value="<? echo stripslashes($obj{"number_children"});?>"><br><b>Names, ages, quality of relationship(s):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="siblings" ><? echo stripslashes($obj{"siblings"});?></textarea><br><br>
<b>Other family relationships:</b><br>
<textarea cols=100 rows=2 wrap=virtual name="other_relationships" ><? echo stripslashes($obj{"other_relationships"});?></textarea><br><br>
<b>Social Relationships (Peers/Friends):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="social_relationships" ><? echo stripslashes($obj{"social_relationships"});?></textarea><br><br>
<b>Psychological/Personal Functioning (Current symptons):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="current_symptoms" ><? echo stripslashes($obj{"current_symptoms"});?></textarea><br><br>
<b>Personal resources and strengths (including the availability & use of family and peers):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="personal_strengths" ><? echo stripslashes($obj{"personal_strengths"});?></textarea><br><br>
<b>Spiritual:</b>&nbsp;<input type="entry" name="spiritual" value="<?echo stripslashes($obj{"spiritual"});?>">&nbsp;<img src="../../../images/space.gif" width="35" height="1">
<b>Legal:</b>&nbsp;<input type="entry" name="legal" value="<?echo stripslashes($obj{"legal"});?>"><br><br>
<b>Prior Mental Health History/Treatment:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="prior_history" ><? echo stripslashes($obj{"prior_history"});?></textarea><br><br>
<b>Number of admissions:</b>&nbsp;<input type="entry" name="number_admitt" value="<?echo stripslashes($obj{"number_admitt"});?>">&nbsp;<img src="../../../images/space.gif" width="35" height="1">
<b>Types of admissions:</b>&nbsp;<input type="entry" name="type_admitt" value="<?echo stripslashes($obj{"type_admitt"});?>"><br><br>
<b>Alcohol and substance use for the past 30 days:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="substance_use" ><? echo stripslashes($obj{"substance_use"});?></textarea><br><br>
<b>Substance abuse history (Include duration, patterns, and consequences of use):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="substance_abuse" ><? echo stripslashes($obj{"substance_abuse"});?></textarea><br><br>
<b><u>Diagnoses</u></b><br><br>
<b>Axis I:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis1" ><? echo stripslashes($obj{"axis1"});?></textarea><br><br>
<b>Axis II:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis2" ><? echo stripslashes($obj{"axis2"});?></textarea><br><br>
<b>Axis III:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis3" ><? echo stripslashes($obj{"axis3"});?></textarea><br><br>
<b><u>Allergies/Adverse reactions to medications:</u></b>&nbsp;<input type="entry" name="allergies" value="<?echo stripslashes($obj{"allergies"});?>"><br><br>
<b>Axis IV Psychosocial and environmental problems in the last year:</b><br>
<input type=checkbox name='ax4_prob_support_group' <? if ($obj{"ax4_prob_support_group"} == "on") {echo "checked";};?>  >&nbsp;<b>Problems with primary support group</b>
<img src="../../../images/space.gif" width="35" height="1">
<input type=checkbox name='ax4_prob_soc_env' <? if ($obj{"ax4_prob_soc_env"} == "on") {echo "checked";};?>  >&nbsp;<b>Problems related to the social environment</b><br>

<input type=checkbox name='ax4_educational_prob' <? if ($obj{"ax4_educational_prob"} == "on") {echo "checked";};?>  >&nbsp;<b>Educational problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_occ_prob' <? if ($obj{"ax4_occ_prob"} == "on") {echo "checked";};?>  >&nbsp;<b>Occupational problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_housing' <? if ($obj{"ax4_housing"} == "on") {echo "checked";};?>  >&nbsp;<b>Housing problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_economic' <? if ($obj{"ax4_economic"} == "on") {echo "checked";};?>  >&nbsp;<b>Economic problems</b><br>
<input type=checkbox name='ax4_access_hc' <? if ($obj{"ax4_access_hc"} == "on") {echo "checked";};?>  >&nbsp;<b>Problems with access to health care services</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_legal' <? if ($obj{"ax4_legal"} == "on") {echo "checked";};?>  >&nbsp;<b>Problems related to interaction with the legal system/crime</b><br>
<input type=checkbox name='ax4_other_cb' <? if ($obj{"ax4_other_cb"} == "on") {echo "checked";};?>  >&nbsp;<b>Other (specify):</b><br>
<textarea cols=100 rows=2 wrap=virtual name="ax4_other" ><? echo stripslashes($obj{"ax4_other"});?></textarea><br><br>
<b>Axis V Global Assessment of Functioning (GAF) Scale (100 down to 0):</b>
<img src="../../../images/space.gif" width="5" height="1"><br>
<b>Currently</b><input type="entry" name="ax5_current" value="<?echo stripslashes($obj{"ax5_current"});?>">
<img src="../../../images/space.gif" width="5" height="1">
<b>Past Year</b><input type="entry" name="ax5_past" value="<?echo stripslashes($obj{"ax5_current"});?>"><br><br>
<b><u>Assessment of Currently Known Risk Factors:</u></b><br><br>
<b>Suicide:</b><br><input type=checkbox name='risk_suicide_na' <? if ($obj{"risk_suicide_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Behaviors:</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_nk' <? if ($obj{"risk_suicide_nk"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Known</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_io' <? if ($obj{"risk_suicide_io"} == "on") {echo "checked";};?>  >&nbsp;<b>Ideation only</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_plan' <? if ($obj{"risk_suicide_plan"} == "on") {echo "checked";};?>  >&nbsp;<b>Plan</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_suicide_iwom' <? if ($obj{"risk_suicide_iwom"} == "on") {echo "checked";};?>  >&nbsp;<b>Intent without means</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_iwm' <? if ($obj{"risk_suicide_iwm"} == "on") {echo "checked";};?>  >&nbsp;<b>Intent with means</b><br>
<br>
<b>Homocide:</b><br><input type=checkbox name='risk_homocide_na' <? if ($obj{"risk_homocide_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Behaviors:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_nk' <? if ($obj{"risk_homocide_nk"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Known</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_io' <? if ($obj{"risk_homocide_io"} == "on") {echo "checked";};?>  >&nbsp;<b>Ideation only</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_plan' <? if ($obj{"risk_homocide_plan"} == "on") {echo "checked";};?>  >&nbsp;<b>Plan</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_homocide_iwom' <? if ($obj{"risk_homocide_iwom"} == "on") {echo "checked";};?>  >&nbsp;<b>Intent without means</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_iwm' <? if ($obj{"risk_homocide_iwm"} == "on") {echo "checked";};?>  >&nbsp;<b>Intent with means</b><br>
<br>	
<b>Compliance with treatment:</b><br><input type=checkbox name='risk_compliance_na' <? if ($obj{"risk_compliance_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_fc' <? if ($obj{"risk_compliance_fc"} == "on") {echo "checked";};?>  >&nbsp;<b>Full compliance</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_mc' <? if ($obj{"risk_compliance_mc"} == "on") {echo "checked";};?>  >&nbsp;<b>Minimal compliance</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_moc' <? if ($obj{"risk_compliance_moc"} == "on") {echo "checked";};?>  >&nbsp;<b>Moderate compliance</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_compliance_var' <? if ($obj{"risk_compliance_var"} == "on") {echo "checked";};?>  >&nbsp;<b>Variable</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_no' <? if ($obj{"risk_compliance_no"} == "on") {echo "checked";};?>  >&nbsp;<b>Little or no compliance</b><br>
<br>	
<b>Substance Abuse:</b><br><input type=checkbox name='risk_substance_na' <? if ($obj{"risk_substance_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_none' <? if ($obj{"risk_substance_none"} == "on") {echo "checked";};?>  >&nbsp;<b>None/normal use:</b><br>
    <textarea cols=100 rows=1 wrap=virtual name="risk_normal_use" ><? echo stripslashes($obj{"risk_normal_use"});?></textarea><br>
	<input type=checkbox name='risk_substance_ou' <? if ($obj{"risk_substance_ou"} == "on") {echo "checked";};?>  >&nbsp;<b>Overuse</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_dp' <? if ($obj{"risk_substance_dp"} == "on") {echo "checked";};?>  >&nbsp;<b>Dependence</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_ur' <? if ($obj{"risk_substance_ur"} == "on") {echo "checked";};?>  >&nbsp;<b>Unstable remission of abuse</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_ab' <? if ($obj{"risk_substance_ab"} == "on") {echo "checked";};?>  >&nbsp;<b>Abuse</b><br>
<br>	
<b>Current physical or sexual abuse:</b><br><input type=checkbox name='risk_sexual_na' <? if ($obj{"risk_sexual_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_y' <? if ($obj{"risk_sexual_y"} == "on") {echo "checked";};?>>&nbsp;<b>Yes</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_n' <? if ($obj{"risk_sexual_n"} == "on") {echo "checked";};?>>&nbsp;<b>No</b><br>
	<b>Legally reportable?</b>&nbsp;<input type=checkbox name='risk_sexual_ry' <? if ($obj{"risk_sexual_ry"} == "on") {echo "checked";};?>>&nbsp;<b>Yes</b>
   	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_rn' <? if ($obj{"risk_sexual_rn"} == "on") {echo "checked";};?>>&nbsp;<b>No</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>If yes, client is </b>&nbsp;<input type=checkbox name='risk_sexual_cv' <? if ($obj{"risk_sexual_cv"} == "on") {echo "checked";};?>>&nbsp;<b>victum</b>
	&nbsp;<input type=checkbox name='risk_sexual_cp' <? if ($obj{"risk_sexual_cp"} == "on") {echo "checked";};?>>&nbsp;<b>perpetrator</b><br>
	<input type=checkbox name='risk_sexual_b' <? if ($obj{"risk_sexual_b"} == "on") {echo "checked";};?>>&nbsp;<b>Both</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_nf' <? if ($obj{"risk_sexual_nf"} == "on") {echo "checked";};?>>&nbsp;<b>neither, but abuse exists in family</b>&nbsp;<br>
<br>
<b>Current child/elder abuse:</b><br><input type=checkbox name='risk_neglect_na' <? if ($obj{"risk_neglect_na"} == "on") {echo "checked";};?>  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_y' <? if ($obj{"risk_neglect_y"} == "on") {echo "checked";};?>>&nbsp;<b>Yes</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_n' <? if ($obj{"risk_neglect_n"} == "on") {echo "checked";};?>>&nbsp;<b>No</b><br>
	<b>Legally reportable?</b>&nbsp;<input type=checkbox name='risk_neglect_ry' <? if ($obj{"risk_neglect_ry"} == "on") {echo "checked";};?>>&nbsp;<b>Yes</b>
   	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_rn' <? if ($obj{"risk_neglect_rn"} == "on") {echo "checked";};?>>&nbsp;<b>No</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>If yes, client is </b>&nbsp;<input type=checkbox name='risk_neglect_cv' <? if ($obj{"risk_neglect_cv"} == "on") {echo "checked";};?>>&nbsp;<b>victum</b>
	&nbsp;<input type=checkbox name='risk_neglect_cp' <? if ($obj{"risk_neglect_cp"} == "on") {echo "checked";};?>>&nbsp;<b>perpetrator</b><br>
	<input type=checkbox name='risk_neglect_cb' <? if ($obj{"risk_neglect_cb"} == "on") {echo "checked";};?>>&nbsp;<b>Both</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_cn' <? if ($obj{"risk_neglect_cn"} == "on") {echo "checked";};?>>&nbsp;<b>neither, but abuse exists in family</b>&nbsp;<br>
<br>

	<b>If risk exists:</b>&nbsp;client&nbsp;<input type=checkbox name='risk_exists_c' <? if ($obj{"risk_exists_c"} == "on") {echo "checked";};?>><b>can</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_cn' <? if ($obj{"risk_exists_cn"} == "on") {echo "checked";};?>>&nbsp;<b>cannot</b>&nbsp;
	<b>meaningfully agree to a contract not to harm</b><br>
	<input type=checkbox name='risk_exists_s' <? if ($obj{"risk_exists_s"} == "on") {echo "checked";};?>>&nbsp;<b>self</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_o' <? if ($obj{"risk_exists_o"} == "on") {echo "checked";};?>>&nbsp;<b>others</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_b' <? if ($obj{"risk_exists_b"} == "on") {echo "checked";};?>>&nbsp;<b>both</b><br><br>
	
    <b>Risk to community (criminal):</b><br>
    <textarea cols=100 rows=3 wrap=virtual name="risk_community" ><? echo stripslashes($obj{"risk_community"});?></textarea><br>
	
<b><u>Assessment Recommendations:</u></b><br><br>

<b>Outpatient Psychotherapy:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_i' <? if ($obj{"recommendations_psy_i"} == "on") {echo "checked";};?>>&nbsp;<b>Individual</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_f' <? if ($obj{"recommendations_psy_f"} == "on") {echo "checked";};?>>&nbsp;<b>Family</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_m' <? if ($obj{"recommendations_psy_m"} == "on") {echo "checked";};?>>&nbsp;<b>Marital/relational</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_o' <? if ($obj{"recommendations_psy_o"} == "on") {echo "checked";};?>>&nbsp;<b>Other</b><br>
    <textarea cols=100 rows=3 wrap=virtual name="recommendations_psy_notes" ><? echo stripslashes($obj{"recommendations_psy_notes"});?></textarea><br>

<b>Date report sent to referral source:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type="text" name='refer_date' value="<?echo stripslashes($obj{"refer_date"});?>">
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Parent/Guardian:</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type="text" name='parent' value="<?echo stripslashes($obj{"parent"});?>">
<br>

<b>Level of supervision needed:</b>
	<br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_level" ><? echo stripslashes($obj{"supervision_level"});?></textarea><br>
	<b>Type of program:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_type" ><? echo stripslashes($obj{"supervision_type"});?></textarea><br>

<b>Residential or long-term placement recommended:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<textarea cols=100 rows=1 wrap=virtual name="supervision_res" ><? echo stripslashes($obj{"supervision_res"});?></textarea><br>
	<b>Support services needed:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_services" ><? echo stripslashes($obj{"supervision_services"});?></textarea><br>
		
	<input type=checkbox name='support_ps' <? if ($obj{"support_ps"} == "on") {echo "checked";};?>>&nbsp;<b>Parenting skills/child management</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_cs' <? if ($obj{"support_cs"} == "on") {echo "checked";};?>>&nbsp;<b>Communication skills</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_sm' <? if ($obj{"support_sm"} == "on") {echo "checked";};?>>&nbsp;<b>Stress management</b><br>

	<input type=checkbox name='support_a' <? if ($obj{"support_a"} == "on") {echo "checked";};?>>&nbsp;<b>Assertiveness</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_o' <? if ($obj{"support_o"} == "on") {echo "checked";};?>>&nbsp;<b>Other</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="support_ol" ><? echo stripslashes($obj{"support_ol"});?></textarea><br><br>
	
<b>Legal Services:</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_op' <? if ($obj{"legal_op"} == "on") {echo "checked";};?>>&nbsp;<b>Offender program</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_so' <? if ($obj{"legal_so"} == "on") {echo "checked";};?>>&nbsp;<b>Sex Offender Groups</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_sa' <? if ($obj{"legal_sa"} == "on") {echo "checked";};?>>&nbsp;<b>Substance abuse</b><br>
	
	<input type=checkbox name='legal_ve' <? if ($obj{"legal_ve"} == "on") {echo "checked";};?>>&nbsp;<b>Victum empathy group</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_ad' <? if ($obj{"legal_ad"} == "on") {echo "checked";};?>>&nbsp;<b>Referral to advocate</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type="text" name='legal_adl' value="<?echo stripslashes($obj{"legal_adl"});?>">
	<img src="../../../images/space.gif" width="5" height="1"><br>
    <input type=checkbox name='legal_o' <? if ($obj{"legal_o"} == "on") {echo "checked";};?>>&nbsp;<b>Other:</b>
	
	<br>

    <b>Other:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="legal_ol" ><? echo stripslashes($obj{"legal_ol"});?></textarea><br><br>
	
<b><u>Referrals for Continuing Services</u></b><br><br>

<b>Psychiatric Evaluation Psychotropic Medications:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_pepm" ><? echo stripslashes($obj{"referrals_pepm"});?></textarea><br><br>

<b>Medical Care:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_mc" ><? echo stripslashes($obj{"referrals_mc"});?></textarea><br><br>
	
<b>Educational/vocational services:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_vt" ><? echo stripslashes($obj{"referrals_vt"});?></textarea><br><br>
	
<b>Other:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_o" ><? echo stripslashes($obj{"referrals_o"});?></textarea><br><br>

<b>Current use of resources/services from other community agencies:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_cu" ><? echo stripslashes($obj{"referrals_cu"});?></textarea><br><br>
	
<b>Documents to be obtainded (Release of Information Required):</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_docs" ><? echo stripslashes($obj{"referrals_docs"});?></textarea><br><br>
	
<b>Other needed resources and services:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_or" ><? echo stripslashes($obj{"referrals_or"});?></textarea><br><br>










<? /* From New */ ?>











<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
