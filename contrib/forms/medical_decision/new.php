<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
?>

<html>
<head>
<title>Medical decision making</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
window.onload = initialize;
</script>
</head>
<body <?echo $top_bg_line;?>>

<div class="srvChapter">Medical decision making <a href="#" onMouseOver="toolTip('The <b>Medical Decision Making</b> section provides space to document minutes counseled, total encounter time, and other services needed to determine the correct level of medical decision making. <br><br><b>Amount and complexility of data reviewed</b><br>Minimal/none = 1 box, limited = 2 boxes, moderate = 3 boxes, extensive = 4+ boxes<br><br>The following items (if checked) count as 2 boxes:<li>OLD RECORDS REVIEWED AND SUMMARIZED</li><li>HISTORY OBTAINED FROM OTHER SOURCE</li><li>INDEPENDENT REVIEW OF IMAGE/SPECIMEN</li>', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>
<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
?>
<form action="<?echo $rootdir;?>/forms/medical_decision/save.php?mode=new" method="post" enctype="multipart/form-data" name="my_form">
<? include("../../acog_menu.inc"); ?>
<div style="border: solid 2px black; background-color: white;">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="left" valign="top" class="fibody2"><table width="100%"  border="0" cellspacing="0" cellpadding="5">
      <tr align="left" valign="bottom" class="fibody">
        <td width="40%" class="bordR">Patient name <br>          
          <input name="pname" type="text" class="fullin" id="pname" value="<?
          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};
          ?>"></td>
        <td width="20%" class="bordR">birth date
          <br>          
          <input name="pbdate" type="text" class="fullin" id="pbdate" value="<?
          echo $patient{'DOB'};
          ?>" size="12"> </td>
        <td width="20%" class="bordR">ID No<br>          
          <input name="md_pid" type="text" class="fullin" id="md_pid" size="12" value="<?
          echo $patient{'id'};
          ?>"></td>
        <td width="20%">date<br>
        <input name="md_date" type="text" class="fullin" id="md_date" value="<?
        echo date('Y-m-d');
        ?>" size="12"></td>
      </tr>
    </table>      </td>
  </tr>
  <tr>
    <td align="center" valign="middle" class="fibody2"><h2>Amount and complexility of data reviewed </h2></td>
  </tr>
  <tr>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
</table>  
<table width="100%"  border="0" cellspacing="0" cellpadding="2">
  <tr align="left" valign="top">
    <td width="29%" class="ficaption3">Tests ordered </td>
    <td class="ficaption3">review of records </td>
  </tr>
  <tr align="left" valign="top">
    <td class="fibody3"><p>
      <input name="test_lab" type="checkbox" id="test_lab" value="1">
      Laboratory</p>
      <blockquote>
        <p>
          <input name="test_lab_cervical" type="checkbox" id="test_lab_cervical" value="1">
          cervical cytology</p>
        <p>
          <input name="test_lab_hpv" type="checkbox" id="test_lab_hpv" value="1">
          HPV test</p>
        <p>
          <input name="test_lab_wet_mount" type="checkbox" id="test_lab_wet_mount" value="1">
          wet mount</p>
        <p>
          <input name="test_lab_chlamydia" type="checkbox" id="test_lab_chlamydia" value="checkbox">
          chlamydia</p>
        <p>
          <input name="test_lab_gonorrhea" type="checkbox" id="test_lab_gonorrhea" value="1">
          gonorrhea</p>
        <p>
          <input name="test_lab_other" type="checkbox" id="test_lab_other" value="1">
          other <span class="ficaption2">
          <input name="test_lab_other_data" type="text" class="fullin" id="test_lab_other_data" style="width:60px">
          </span></p>
        </blockquote>
      <p>
        <input name="test_rad" type="checkbox" id="test_rad" value="1">
        Radiology / Ultrasound</p>
      <blockquote>
        <p>
          <input name="test_rad_mammogram" type="checkbox" id="test_rad_mammogram" value="1">
          mammogram</p>
        <p>
          <input name="test_rad_other" type="checkbox" id="test_rad_other" value="checkbox">
          other <span class="ficaption2">
          <input name="test_rad_other_data" type="text" class="fullin" id="test_rad_other_data" style="width:60px">
          </span>  </p>
      </blockquote></td>
    <td class="fibody3"><p>
      <input name="previous_test_results" type="checkbox" id="previous_test_results" value="1">
      previous test results:</p>
      <p>
        <input name="previous_test_results_data" type="text" class="fullin2" id="previous_test_results_data">
      </p>      <p>
        <input name="test_results_discussion" type="checkbox" id="test_results_discussion" value="1">
        discussion of test results with performing physician:</p>
      <p>
        <input name="test_results_discussion_data" type="text" class="fullin2" id="test_results_discussion_data">
      </p>      <p>
        <input name="old_records_reviewed" type="checkbox" id="old_records_reviewed" value="1">
        old records reviewed and summarized:</p>
      <p>
        <input name="old_records_reviewed_data" type="text" class="fullin2" id="old_records_reviewed_data">
      </p>      <p>
        <input name="history_other_source" type="checkbox" id="history_other_source" value="1">
        history obtained from other source:</p>
      <p>
        <input name="history_other_source_data" type="text" class="fullin2" id="history_other_source_data">
      </p>      <p>
        <input name="independent_review" type="checkbox" id="independent_review" value="1">
        independent review of image/specimen:</p>
      <p>
        <input name="independent_review_data" type="text" class="fullin2" id="independent_review_data">
      </p></td>
  </tr>
</table>
</div>
  <p align="center">&nbsp;</p>
  <h2 align="center">Diagnoses / Management option <a href="#" onMouseOver="toolTip('<b>Diagnoses/management options</b><p><b>MINIMAL:</b> Minor problem; established problem, stable/improved<br><b>MULTIPLE:</b> New problem, no additional workup planned<br><b>LIMITED:</b> Established problem, worsening<br><b>EXTENSIVE:</b> New problem, additional workup planned</p>', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></h2>
  <div style="border: solid 2px black; background-color: white;">
    <table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td colspan="2" align="left" valign="top" class="fibody2">          <input name="established_problem" type="radio" value="1">
          Established problem&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <input name="established_problem" type="radio" value="0">        
        New problem </td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="top" class="ficaption2">Assessment and plan:</td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="top" class="fibody2"><textarea name="assessment_and_plan" rows="7" wrap="PHYSICAL" class="fullin2" id="assessment_and_plan"></textarea></td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="top" class="fibody2"><p>Risk of complications and/or morbidity/mortality:</p>
          <blockquote>
            <p>
            <input name="md_risk" type="radio" value="minimal">
            Minimal (EG, cold, aches and pains, over-the-counter medications)</p>
            <p>
            <input name="md_risk" type="radio" value="low">
            low (EG, cystitis, vaginitis, prescription renewal, minor surgery without risk factors) </p>
            <p>
              <input name="md_risk" type="radio" value="moderate">
            moderate (EG, breast mass, irregular bleeding, headaches, minor surgery with risk factors, major surgery without risk factors, new prescription)</p>
            <p>
              <input name="md_risk" type="radio" value="high">
            high (EG, pelvic pain, rectal bleeding, multiple complaints, major surgery with risk factors, chemotherapy, emergency surgery)  </p>
        </blockquote></td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="top" class="fibody2"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="20%" rowspan="3" align="left" valign="top" class="ficaption3">Patient counseled about:</td>
            <td width="15%" align="left" valign="baseline" nowrap class="fibody3"><input name="pc_smoking" type="checkbox" id="pc_smoking" value="1">
              smoking cessation </td>
            <td width="15%" align="left" valign="baseline" nowrap class="fibody3"><input name="pc_contraception" type="checkbox" id="pc_contraception" value="1">
              contraception</td>
            <td width="15%" align="left" valign="bottom" nowrap>&nbsp;</td>
            <td width="15%" align="left" valign="bottom" nowrap>&nbsp;</td>
          </tr>
          <tr>
            <td align="left" valign="baseline" nowrap class="fibody3"><input name="pc_weight" type="checkbox" id="pc_weight" value="1">
              weight management </td>
            <td align="left" valign="baseline" nowrap class="fibody3"><input name="pc_safe_sex" type="checkbox" id="pc_safe_sex" value="1">
              safe sex </td>
            <td align="left" valign="bottom" nowrap>&nbsp;</td>
            <td align="left" valign="bottom" nowrap>&nbsp;</td>
          </tr>
          <tr>
            <td align="left" valign="baseline" nowrap class="fibody3"><input name="pc_exercise" type="checkbox" id="pc_exercise" value="1">
              exercise</td>
            <td align="left" valign="baseline" nowrap class="fibody3"><input name="pc_other" type="checkbox" id="pc_other" value="1">
              other</td>
            <td align="left" valign="bottom" nowrap>&nbsp;</td>
            <td align="left" valign="bottom" nowrap>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td width="50%" align="left" valign="top" class="ficaption2" id="bordR"><input name="patient_education" type="checkbox" id="patient_education" value="1">
        Patient education materials provided</td>
        <td width="50%" align="left" valign="top" class="fibody2">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="top" class="fibody2" id="bordR">&nbsp;</td>
        <td align="left" valign="top" class="fibody2">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="top" class="ficaption2" id="bordR">Minutes counseled: 
        <input name="minutes_counseled" type="text" class="fullin" id="minutes_counseled" style="width:60px"></td>
        <td align="left" valign="top" class="ficaption2">Total encounter time: 
        <input name="total_encounter_time" type="text" class="fullin" id="total_encounter_time" style="width:60px"></td>
      </tr>
      <tr valign="bottom">
        <td height="50" align="left" class="ficaption2" id="bordR">Signature:</td>
        <td align="left" class="ficaption2">Date: 
        <input name="md_date" type="text" class="fullin" id="md_date" style="width:80px" value="<?
        echo date('Y-m-d');
        ?>"></td>
      </tr>
    </table>
  </div>
  <table width="100%" border="0">
    <tr>
      <td align="left"> <a href="javascript:document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
      <td align="right"> <a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link_submit">[Don't Save]</a> </td>
    </tr>
  </table>
</form>
<?php
formFooter();
?>
</body>
</html>