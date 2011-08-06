<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"

"http://www.w3.org/TR/html4/loose.dtd">

<?php

include_once("../../globals.php");

include_once("$srcdir/api.inc");

include_once("$srcdir/forms.inc");

include_once("$srcdir/calendar.inc");

?>

<?php 

   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");

   if ($fres){

     $patient = sqlFetchArray($fres);

   }

   $fres=sqlStatement("select * from form_medical_decision where id='".$id."'");

   if ($fres){

     $fdata = sqlFetchArray($fres);

   }

?>



<html>

<head>
<?php html_header_show();?>

<title>Medical decision making</title>

<?php include("../../acog_printable_v.css"); ?>

</head>

<body>

<div class="srvChapter">Medical decision making</div>

<div style="border: solid 2px black; background-color: white;">

<table width="100%"  border="0" cellspacing="0" cellpadding="0">

  <tr>

    <td align="left" valign="top" class="fibody2"><table width="100%"  border="0" cellspacing="0" cellpadding="5">

      <tr align="left" valign="bottom">

        <td width="40%" class="fibody2" id="bordR">Patient name:  <?php echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};          ?></td>

        <td width="20%" class="fibody2" id="bordR">Birth date: <?php echo $patient{'DOB'};         ?></td>

        <td width="20%" class="fibody2" id="bordR">ID No: <?php  echo $patient{'id'};  ?></td>

        <td width="20%" class="fibody2">date: <?php echo date('Y-m-d'); ?></td>

      </tr>

    </table> </td>

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

      <?php echo (($fdata{'test_lab'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      Laboratory</p>

      <blockquote>

        <p>

          <?php echo (($fdata{'test_lab_cervical'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          cervical cytology</p>

        <p>

          <?php echo (($fdata{'test_lab_hpv'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          HPV test</p>

        <p>

          <?php echo (($fdata{'test_lab_wet_mount'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          wet mount</p>

        <p>

          <?php echo (($fdata{'test_lab_chlamydia'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          chlamydia</p>

        <p>

          <?php echo (($fdata{'test_lab_gonorrhea'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          gonorrhea</p>

        <p>

          <?php echo (($fdata{'test_lab_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          other <span class="ficaption2">

          <?php echo $fdata{'test_lab_other_data'}; ?>

          </span></p>

        </blockquote>

      <p>

        <?php echo (($fdata{'test_rad'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        Radiology / Ultrasound</p>

      <blockquote>

        <p>

          <?php echo (($fdata{'test_rad_mammogram'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          mammogram</p>

        <p>

          <?php echo (($fdata{'test_rad_other'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          other <span class="ficaption2">

          <?php echo $fdata{'test_rad_other_data'}; ?>

          </span> </p>

      </blockquote></td>

    <td class="fibody3"><p>

      <?php echo (($fdata{'previous_test_results'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      previous test results:</p>

      <p>

        <?php echo $fdata{'previous_test_results_data'}; ?>

      </p> <p>

        <?php echo (($fdata{'test_results_discussion'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        discussion of test results with performing physician:</p>

      <p>

        <?php echo $fdata{'test_results_discussion_data'}; ?>

      </p> <p>

        <?php echo (($fdata{'old_records_reviewed'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        old records reviewed and summarized:</p>

      <p>

        <?php echo $fdata{'old_records_reviewed_data'}; ?>

      </p> <p>

        <?php echo (($fdata{'history_other_source'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        history obtained from other source:</p>

      <p>

        <?php echo $fdata{'history_other_source_data'}; ?>

      </p> <p>

        <?php echo (($fdata{'independent_review'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        independent review of image/specimen:</p>

      <p>

        <?php echo $fdata{'independent_review_data'}; ?>

      </p></td>

  </tr>

</table>

</div>

  <p align="center">&nbsp;</p>

  <h2 align="center">Diagnoses / Management option </h2>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td colspan="2" align="left" valign="top" class="fibody2"> <?php echo (($fdata{'established_problem'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          Established problem&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

          <?php echo (($fdata{'established_problem'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 

        New problem </td>

      </tr>

      <tr>

        <td colspan="2" align="left" valign="top" class="ficaption2">Assessment and plan:</td>

      </tr>

      <tr>

        <td colspan="2" align="left" valign="top" class="fibody5"><?php echo $fdata{'assessment_and_plan'}; ?></td>

      </tr>

      <tr>

        <td colspan="2" align="left" valign="top" class="fibody2"><p>Risk of complications and/or morbidity/mortality:</p>

          <blockquote>

            <p>

            <?php echo (($fdata{'md_risk'} == 'minimal')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

            Minimal (EG, cold, aches and pains, over-the-counter medications)</p>

            <p>

            <?php echo (($fdata{'md_risk'} == 'low')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

            low (EG, cystitis, vaginitis, prescription renewal, minor surgery without risk factors) </p>

            <p>

              <?php echo (($fdata{'md_risk'} == 'moderate')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

            moderate (EG, breast mass, irregular bleeding, headaches, minor surgery with risk factors, major surgery without risk factors, new prescription)</p>

            <p>

              <?php echo (($fdata{'md_risk'} == 'high')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

            high (EG, pelvic pain, rectal bleeding, multiple complaints, major surgery with risk factors, chemotherapy, emergency surgery)  </p>

        </blockquote></td>

      </tr>

      <tr>

        <td colspan="2" align="left" valign="top" class="fibody2"><table width="100%"  border="0" cellspacing="0" cellpadding="0">

          <tr>

            <td width="20%" rowspan="3" align="left" valign="top" class="ficaption3">Patient counseled about:</td>

            <td width="15%" align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_smoking'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              smoking cessation </td>

            <td width="15%" align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_contraception'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              contraception</td>

            <td width="15%" align="left" valign="bottom" nowrap>&nbsp;</td>

            <td width="15%" align="left" valign="bottom" nowrap>&nbsp;</td>

          </tr>

          <tr>

            <td align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_weight'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              weight management </td>

            <td align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_safe_sex'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              safe sex </td>

            <td align="left" valign="bottom" nowrap>&nbsp;</td>

            <td align="left" valign="bottom" nowrap>&nbsp;</td>

          </tr>

          <tr>

            <td align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_exercise'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              exercise</td>

            <td align="left" valign="baseline" nowrap class="fibody3"><?php echo (($fdata{'pc_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              other</td>

            <td align="left" valign="bottom" nowrap>&nbsp;</td>

            <td align="left" valign="bottom" nowrap>&nbsp;</td>

          </tr>

        </table></td>

      </tr>

      <tr>

        <td width="50%" align="left" valign="top" class="ficaption2" id="bordR"><?php echo (($fdata{'patient_education'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        Patient education materials provided</td>

        <td width="50%" align="left" valign="top" class="fibody2">&nbsp;</td>

      </tr>

      <tr>

        <td align="left" valign="top" class="fibody2" id="bordR">&nbsp;</td>

        <td align="left" valign="top" class="fibody2">&nbsp;</td>

      </tr>

      <tr>

        <td align="left" valign="top" class="ficaption2" id="bordR">Minutes counseled: 

        <?php echo $fdata{'minutes_counseled'}; ?></td>

        <td align="left" valign="top" class="ficaption2">Total encounter time: 

        <?php echo $fdata{'total_encounter_time'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td height="50" align="left" class="ficaption2" id="bordR">Signature:</td>

        <td align="left" class="ficaption2">Date: <?php echo $fdata['date']; ?></td>

      </tr>

    </table>

  </div>

</body>

</html>