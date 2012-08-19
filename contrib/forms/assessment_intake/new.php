<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: assessment_intake");
?>
<html><head>
<?php html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/assessment_intake/save.php?mode=new" name="my_form">
<br>
<span class="title"><center>Assessment and Intake</center></span><br><br>
<center><a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save]</a></center>
<br>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>
<b>Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="572" height="1">
<b>Date:</b>&nbsp; <?php print date('m/d/y'); ?><br><br>
<b>SSN:</b>&nbsp;<?php echo $result['ss'];?><img src="../../../images/space.gif" width="172" height="1">
<label><b>DCN:</b>&nbsp;<input type="text" name="dcn"></label><img src="../../../images/space.gif" width="125" height="1">
<label><b>Location:</b>&nbsp;<input type="text" name="location"></label><br><br>
<b>Address:</b>&nbsp; <?php echo $result['street'] . ',&nbsp' . $result['city']  . ',&nbsp' . $result['state'] . '&nbsp;' . $result['postal_code'];?><br><br>
<b>Telephone Number:</b>&nbsp; <?php echo $result['phone_home'];?><img src="../../../images/space.gif" width="400" height="1"> 
<b>Date of Birth:</b>&nbsp;<?php echo $result['DOB'];?><br><br>
<label><b>Time In:</b>&nbsp;<input type="text" name="time_in"></label><img src="../../../images/space.gif" width="65" height="1">
<label><b>Time Out:</b>&nbsp;<input type="text" name="time_out"></label><img src="../../../images/space.gif" width="65" height="1">
<label><b>Referral Source:</b>&nbsp;<input type="text" name="referral_source"></label><br><br>
<b>Purpose:</b>&nbsp; <input type=checkbox name='new_client_eval'  ><b>New client evaluation</b><img src="../../../images/space.gif" width="10" height="1">
<input type=checkbox name='readmission'  ><b>Readmission</b><img src="../../../images/space.gif" width="35" height="1">
<input type=checkbox name='consultation' ><b>Consultation</b><br><br> 
<label><b>Copy sent to:</b>&nbsp;<input type="text" name="copy_sent_to"></label><br><br>
<b>Why is Assessment being requested (Goals and treatment expectations of the individual requesting services):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="reason_why" ></textarea><br>
<b>Behavior that led to Assessment:</b><br>
<textarea cols=100 rows=5 wrap=virtual name="behavior_led_to" ></textarea><br><br>
<b><u></u>Areas of Functioning:</b><br><br>
<b>School/Work:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="school_work" ></textarea><br><br>
<b>Personal Relationships (Intimate):</b>&nbsp;
<textarea cols=100 rows=4 wrap=virtual name="personal_relationships" ></textarea><br><br>
<b>Family Relationships:</b>&nbsp; &nbsp;
<input type=checkbox name='fatherc'  >&nbsp;<b>Father involved/present/absent (Describe relationship)</b><br>
<textarea cols=100 rows=3 wrap=virtual name="father_involved" ></textarea><br>
<input type=checkbox name='motherc'  >&nbsp;<b>Mother involved/present/absent (Describe relationship)</b><br>
<textarea cols=100 rows=3 wrap=virtual name="mother_involved" ></textarea><br><br>
<b>Number of children:</b>&nbsp;<input type="text" name="number_children"><br><b>Names, ages, quality of relationship(s):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="siblings" ></textarea><br><br>
<b>Other family relationships:</b><br>
<textarea cols=100 rows=2 wrap=virtual name="other_relationships" ></textarea><br><br>
<b>Social Relationships (Peers/Friends):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="social_relationships" ></textarea><br><br>
<b>Psychological/Personal Functioning (Current symptons):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="current_symptoms" ></textarea><br><br>
<b>Personal resources and strengths (including the availability & use of family and peers):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="personal_strengths" ></textarea><br><br>
<b>Spiritual:</b>&nbsp;<input type="text" name="spiritual">&nbsp;<img src="../../../images/space.gif" width="35" height="1">
<b>Legal:</b>&nbsp;<input type="text" name="legal"><br><br>
<b>Prior Mental Health History/Treatment:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="prior_history" ></textarea><br><br>
<b>Number of admissions:</b>&nbsp;<input type="text" name="number_admitt">&nbsp;<img src="../../../images/space.gif" width="35" height="1">
<b>Types of admissions:</b>&nbsp;<input type="text" name="type_admitt"><br><br>
<b>Alcohol and substance use for the past 30 days:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="substance_use" ></textarea><br><br>
<b>Substance abuse history (Include duration, patterns, and consequences of use):</b><br>
<textarea cols=100 rows=3 wrap=virtual name="substance_abuse" ></textarea><br><br>
<b><u>Diagnoses</u></b><br><br>
<b>Axis I:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis1" ></textarea><br><br>
<b>Axis II:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis2" ></textarea><br><br>
<b>Axis III:</b><br>
<textarea cols=100 rows=3 wrap=virtual name="axis3" ></textarea><br><br>
<b><u>Allergies/Adverse reactions to medications:</u></b>&nbsp;<input type="text" name="allergies"><br><br>
<b>Axis IV Psychosocial and environmental problems in the last year:</b><br>
<input type=checkbox name='ax4_prob_support_group'  >&nbsp;<b>Problems with primary support group</b>
<img src="../../../images/space.gif" width="35" height="1">
<input type=checkbox name='ax4_prob_soc_env'  >&nbsp;<b>Problems related to the social environment</b><br>

<input type=checkbox name='ax4_educational_prob'  >&nbsp;<b>Educational problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_occ_prob'  >&nbsp;<b>Occupational problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_housing'  >&nbsp;<b>Housing problems</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_economic'  >&nbsp;<b>Economic problems</b><br>
<input type=checkbox name='ax4_access_hc'  >&nbsp;<b>Problems with access to health care services</b>
<img src="../../../images/space.gif" width="5" height="1">
<input type=checkbox name='ax4_legal'  >&nbsp;<b>Problems related to interaction with the legal system/crime</b><br>
<input type=checkbox name='ax4_other_cb'  >&nbsp;<b>Other (specify):</b><br>
<textarea cols=100 rows=2 wrap=virtual name="ax4_other" ></textarea><br><br>
<b>Axis V Global Assessment of Functioning (GAF) Scale (100 down to 0):</b>
<img src="../../../images/space.gif" width="5" height="1"><br>
<b>Currently</b><input type="text" name="ax5_current">
<img src="../../../images/space.gif" width="5" height="1">
<b>Past Year</b><input type="text" name="ax5_past"><br><br>
<b><u>Assessment of Currently Known Risk Factors:</u></b><br><br>
<b>Suicide:</b><br><input type=checkbox name='risk_suicide_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Behaviors:</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_nk'  >&nbsp;<b>Not Known</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_io'  >&nbsp;<b>Ideation only</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_plan'  >&nbsp;<b>Plan</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_suicide_iwom'  >&nbsp;<b>Intent without means</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_suicide_iwm'  >&nbsp;<b>Intent with means</b><br>
<br>
<b>Homocide:</b><br><input type=checkbox name='risk_homocide_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Behaviors:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_nk'  >&nbsp;<b>Not Known</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_io'  >&nbsp;<b>Ideation only</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_plan'  >&nbsp;<b>Plan</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_homocide_iwom'  >&nbsp;<b>Intent without means</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_homocide_iwm'  >&nbsp;<b>Intent with means</b><br>
<br>	
<b>Compliance with treatment:</b><br><input type=checkbox name='risk_compliance_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_fc'  >&nbsp;<b>Full compliance</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_mc'  >&nbsp;<b>Minimal compliance</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_moc'  >&nbsp;<b>Moderate compliance</b><br>
	<img src="../../../images/space.gif" width="100" height="1">
	<input type=checkbox name='risk_compliance_var'  >&nbsp;<b>Variable</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_compliance_no'  >&nbsp;<b>Little or no compliance</b><br>
<br>	
<b>Substance Abuse:</b><br><input type=checkbox name='risk_substance_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_none'  >&nbsp;<b>None/normal use:</b><br>
    <textarea cols=100 rows=1 wrap=virtual name="risk_normal_use" ></textarea><br>
	<input type=checkbox name='risk_substance_ou'  >&nbsp;<b>Overuse</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_dp'  >&nbsp;<b>Dependence</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_ur'  >&nbsp;<b>Unstable remission of abuse</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_substance_ab'  >&nbsp;<b>Abuse</b><br>
<br>	
<b>Current physical or sexual abuse:</b><br><input type=checkbox name='risk_sexual_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_y'>&nbsp;<b>Yes</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_n'>&nbsp;<b>No</b><br>
	<b>Legally reportable?</b>&nbsp;<input type=checkbox name='risk_sexual_ry'>&nbsp;<b>Yes</b>
   	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_rn'>&nbsp;<b>No</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>If yes, client is </b>&nbsp;<input type=checkbox name='risk_sexual_cv'>&nbsp;<b>victum</b>
	&nbsp;<input type=checkbox name='risk_sexual_cp'>&nbsp;<b>perpetrator</b><br>
	<input type=checkbox name='risk_sexual_b'>&nbsp;<b>Both</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_sexual_nf'>&nbsp;<b>neither, but abuse exists in family</b>&nbsp;<br>
<br>
<b>Current child/elder abuse:</b><br><input type=checkbox name='risk_neglect_na'  >&nbsp;<b>Not Assessed</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_y'>&nbsp;<b>Yes</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_n'>&nbsp;<b>No</b><br>
	<b>Legally reportable?</b>&nbsp;<input type=checkbox name='risk_neglect_ry'>&nbsp;<b>Yes</b>
   	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_rn'>&nbsp;<b>No</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>If yes, client is </b>&nbsp;<input type=checkbox name='risk_neglect_cv'>&nbsp;<b>victum</b>
	&nbsp;<input type=checkbox name='risk_neglect_cp'>&nbsp;<b>perpetrator</b><br>
	<input type=checkbox name='risk_neglect_cb'>&nbsp;<b>Both</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_neglect_cn'>&nbsp;<b>neither, but abuse exists in family</b>&nbsp;<br>
<br>

	<b>If risk exists:</b>&nbsp;client&nbsp;<input type=checkbox name='risk_exists_c'><b>can</b>&nbsp;
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_cn'>&nbsp;<b>cannot</b>&nbsp;
	<b>meaningfully agree to a contract not to harm</b><br>
	<input type=checkbox name='risk_exists_s'>&nbsp;<b>self</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_o'>&nbsp;<b>others</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='risk_exists_b'>&nbsp;<b>both</b><br><br>
	
    <b>Risk to community (criminal):</b><br>
    <textarea cols=100 rows=3 wrap=virtual name="risk_community" ></textarea><br>
	
<b><u>Assessment Recommendations:</u></b><br><br>

<b>Outpatient Psychotherapy:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_i'>&nbsp;<b>Individual</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_f'>&nbsp;<b>Family</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_m'>&nbsp;<b>Marital/relational</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=checkbox name='recommendations_psy_o'>&nbsp;<b>Other</b><br>
    <textarea cols=100 rows=3 wrap=virtual name="recommendations_psy_notes" ></textarea><br>

<b>Date report sent to referral source:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<input type=text name='refer_date'>
	<img src="../../../images/space.gif" width="5" height="1">
	<b>Parent/Guardian:</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=text name='parent'>
<br>

<b>Level of supervision needed:</b>
	<br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_level" ></textarea><br>
	<b>Type of program:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_type" ></textarea><br>

<b>Residential or long-term placement recommended:</b>
	<img src="../../../images/space.gif" width="5" height="1">
	<textarea cols=100 rows=1 wrap=virtual name="supervision_res" ></textarea><br>
	<b>Support services needed:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="supervision_services" ></textarea><br>
		
	<input type=checkbox name='support_ps'>&nbsp;<b>Parenting skills/child management</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_cs'>&nbsp;<b>Communication skills</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_sm'>&nbsp;<b>Stress management</b><br>

	<input type=checkbox name='support_a'>&nbsp;<b>Assertiveness</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='support_o'>&nbsp;<b>Other</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="support_ol" ></textarea><br><br>
	
<b>Legal Services:</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_op'>&nbsp;<b>Offender program</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_so'>&nbsp;<b>Sex Offender Groups</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_sa'>&nbsp;<b>Substance abuse</b><br>
	
	<input type=checkbox name='legal_ve'>&nbsp;<b>Victum empathy group</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=checkbox name='legal_ad'>&nbsp;<b>Referral to advocate</b>
	<img src="../../../images/space.gif" width="5" height="1">
    <input type=text name='legal_adl'>
	<img src="../../../images/space.gif" width="5" height="1"><br>
    <input type=checkbox name='legal_o'>&nbsp;<b>Other:</b>
	
	<br>

    <b>Other:</b><br>
	<textarea cols=100 rows=1 wrap=virtual name="legal_ol" ></textarea><br><br>
	
<b><u>Referrals for Continuing Services</u></b><br><br>

<b>Psychiatric Evaluation Psychotropic Medications:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_pepm" ></textarea><br><br>

<b>Medical Care:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_mc" ></textarea><br><br>
	
<b>Educational/vocational services:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_vt" ></textarea><br><br>
	
<b>Other:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_o" ></textarea><br><br>

<b>Current use of resources/services from other community agencies:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_cu" ></textarea><br><br>
	
<b>Documents to be obtainded (Release of Information Required):</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_docs" ></textarea><br><br>
	
<b>Other needed resources and services:</b><br>
	<textarea cols=100 rows=2 wrap=virtual name="referrals_or" ></textarea><br><br>


<center><a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save]</a></center>
<br>
</form>
<?php
formFooter();
?>
