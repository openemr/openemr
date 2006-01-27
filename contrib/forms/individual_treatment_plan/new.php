
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: individual_treatment_plan");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/individual_treatment_plan/save.php?mode=new" name="my_form">
<br>
<span class="title"><center>Individual Treatment Plan</center></span><br><br>
<center><a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link_submit">[Don't Save]</a></center>
<br>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); ?>

<b>Date of Referral:</b>&nbsp;<input type="text" name="date_of_referal"> 
<img src="../../../images/space.gif" width="260" height="1">
<b>Date of Plan:</b>&nbsp; <?php print date('m/d/y'); ?><br><br>

<img src="../../../images/space.gif" width="28" height="1">
<b>Client Name:</b>&nbsp; <?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="292" height="1">
<b>DCN:</b>
<img src="../../../images/space.gif" width="1" height="1">
<input type="text" name="dcn"> <br><br>

<b>ICD/9/CM Code:</b>&nbsp;<input type="text" name="icd9">
<img src="../../../images/space.gif" width="200" height="1">
<b>Prognosis:</b>&nbsp;<input type="text" name="prognosis"><br><br>

<b>Diagnosis Description:</b><br>
<textarea cols=85 rows=2 wrap=virtual name="diagnosis_description" ></textarea><br><br>

<b>Presenting Problem Description and Psychosocial Information:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="presenting_problem" ></textarea><br><br>

<b>Frequency:</b>&nbsp;<input type="text" name="frequency" size="12" maxlength="10">
<img src="../../../images/space.gif" width="40" height="1">
<b>Duration:</b>&nbsp;<input type="text" name="duration" size="12" maxlength="10">
<img src="../../../images/space.gif" width="40" height="1">
<b>Scope:</b>&nbsp;<input type="text" name="scope" size="12" maxlength="10"><br><br>

<b>Short Term Goals:</b>
<img src="../../../images/space.gif" width="162" height="1">
<b>Time Frame:</b><br>
<input type="text" name="short_term_goals_1" size="42" maxlength="40">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_1" size="16" maxlength="15"><br>

<input type="text" name="short_term_goals_2" size="42" maxlength="40">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_2" size="16" maxlength="15"><br>

<input type="text" name="short_term_goals_3" size="42" maxlength="40">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="time_frame_3" size="16" maxlength="15"><br><br>

<b>Long Term Goals:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="long_term_goals" ></textarea><br><br>

<b>Discharge Criteria:</b><br>
<textarea cols=85 rows=2 wrap=virtual name="discharge_criteria" ></textarea><br><br>

<b>Recommendations:</b><br>
<input type="checkbox" name="individual_family_therapy">&nbsp;<b>Individual and / or Family Therapy</b></input>
<img src="../../../images/space.gif" width="6" height="1">
<input type="checkbox" name="substance_abuse">&nbsp;<b>Substance Abuse</b></input><br>

<input type="checkbox" name="group_therapy">&nbsp;<b>Group Therapy - psychoeducational group</b></input>
<img src="../../../images/space.gif" width="6" height="1">
<input type="checkbox" name="parenting">&nbsp;<b>Parenting</b></input><br><br>

<b>Action Steps by supports - family:</b><br>
<textarea cols=85 rows=3 wrap=virtual name="action_steps_by_supports" ></textarea><br><br>

<b>Other supports - agencies</b>
<b>Name:</b>
<img src="../../../images/space.gif" width="38" height="1">
<b>Contact Information</b><br>
<input type="text" name="other_supports_name_1" size="37" maxlength="35">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="other_supports_contact_1" size="37" maxlength="35"><br>

<input type="text" name="other_supports_name_2" size="37" maxlength="35">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="other_supports_contact_2" size="37" maxlength="35"><br><br>

<b>Medications</b>
<img src="../../../images/space.gif" width="204" height="1">
<b>Referrals</b><br>

<input type="text" name="medications_1" size="42" maxlength="40">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="referrals_1" size="42" maxlength="40"><br>

<input type="text" name="medications_2" size="42" maxlength="40">
<img src="../../../images/space.gif" width="6" height="1">
<input type="text" name="referrals_2" size="42" maxlength="40"><br><br>


<br><br>
<center><a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<img src="../../../images/space.gif" width="5" height="1">
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link_submit">[Don't Save]</a></center>
<br>
</form>
<?php
formFooter();
?>
