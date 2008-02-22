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
<? html_header_show();?>

<title>Patient intake history</title>

<? include("../../acog_printable_v.css"); ?>

</head>



<body>

<? 

   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");

   if ($fres){

     $patient = sqlFetchArray($fres);

   }

   $fres=sqlStatement("select * from form_patient_intake_history where id=$id");

   if ($fres){

     $fdata = sqlFetchArray($fres);

   }

   $fres=sqlStatement("select * from form_patient_intake_history_ros where id=".$fdata['linked_ros_id']);

   if ($fres){

     $fros = sqlFetchArray($fres);

   }

?>

  <table width="50%"  border="0" cellspacing="0" cellpadding="2">

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3">For office use only </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"> <? echo (($fdata{'pih_patient'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      New patient </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><? echo (($fdata{'pih_patient'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      Established patient </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><? echo (($fdata{'pih_consultation'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      Consultation</td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><? echo (($fdata{'pih_report_sent'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

      Report sent: <? echo $fdata{'pih_report_sent_date'}; ?>&nbsp</td>

    </tr>

</table>

<div class="srvChapter">Patient Intake history</div>

<div style="border: solid 1.5pt black; background-color: white;">

<table width="100%"  border="0" cellspacing="0" cellpadding="0">

  <tr>

    <td align="left" valign="top" class="fibody2" style="border-bottom: 2px solid black"><table width="100%"  border="0" cellspacing="0" cellpadding="5">

      <tr align="left" valign="bottom" class="fibody">

        <td width="40%" class="bordR">Patient name:<?

          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};

          ?>&nbsp</td>

        <td width="20%" class="bordR">Birth date: <?

          echo $patient{'DOB'};

          ?>&nbsp</td>

        <td width="20%" class="bordR">ID No: <?

          echo $patient{'id'};

          ?>&nbsp</td>

        <td width="20%">Date: <?

        echo date('Y-m-d');

        ?>&nbsp</td>

      </tr>

    </table> 

</td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr align="left" valign="bottom">

        <td colspan="3" class="fibody2">Address:<? echo $fdata{'address'}; ?>&nbsp</td></tr>

      <tr align="left" valign="bottom">

        <td width="50%" class="fibody2" id="bordR">City: <? echo $fdata{'city'}; ?>&nbsp</td>

        <td width="50%" colspan="2" class="fibody2">State/ZIP: <? echo $fdata{'state'}; ?>&nbsp</td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Home telephone: <? echo $fdata{'home_phone'}; ?>&nbsp</td>

        <td colspan="2" class="fibody2">Work telephone: <? echo $fdata{'work_phone'}; ?>&nbsp</td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Employer: <? echo $fdata{'employer'}; ?>&nbsp</td>

        <td width="25%" class="fibody2" id="bordR">Insurance: <? echo $fdata{'insurance'}; ?>&nbsp</td>

        <td width="25%" class="fibody2">Policy No: <? echo $fdata{'policy_no'}; ?>&nbsp</td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Name you would like us to use: <? echo $fdata{'name_to_use'}; ?> </td>

        <td colspan="2" class="fibody2">Primary language: <? echo $fdata{'primary_language'}; ?>&nbsp</td>

      </tr>

    </table></td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr align="left" valign="bottom">

        <td width="40%" class="fibody2" id="bordR">Name of spouse/partner: </td>

        <td colspan="2" class="fibody2">Emergency contact: <? echo $fdata{'partner_emergency_contact'}; ?>&nbsp</td>

        </tr>

      <tr align="left" valign="bottom">

        <td rowspan="2" valign="top" class="fibody2" id="bordR"><? echo $fdata{'partner_name'}; ?>&nbsp</td>

        <td colspan="2" class="fibody2">Relationship: <? echo $fdata{'relationship'}; ?>&nbsp</td>

        </tr>

      <tr align="left" valign="bottom">

        <td width="30%" class="fibody2" id="bordR">Home telephone: 

          <? echo $fdata{'partner_home_phone'}; ?>&nbsp</td>

        <td width="30%" class="fibody2">Work telephone: 

          <? echo $fdata{'partner_work_phone'}; ?>&nbsp</td>

      </tr>

    </table></td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="bottom" class="fibody2">Referred by: 

          <? echo $fdata{'referred_by'}; ?>&nbsp</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Why have you come to the office today? 

          <? echo $fdata{'why_come_to_office'}; ?>&nbsp</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">If you are here for the annual examination is this a 

          <? echo (($fdata{'primary_care_visit'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          Primary care visit or 

          <? echo (($fdata{'primary_care_visit'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          Gynecology only </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Is this a new problem? 

          <? echo (($fdata{'new_problem'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          yes &nbsp;&nbsp;&nbsp;&nbsp;

          <? echo (($fdata{'new_problem'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          no</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Please, describe your problem, including, where it is, how severe it is, and how long it has lasted <br>

          <? echo $fdata{'problem_description'}; ?>&nbsp</td>

      </tr>

    </table></td>

  </tr>

</table> 

</div>

<h2 align="center"><small>If you are uncomfortable answering any questions, leave them blank; you can discuss them with your doctor or nurse.</small></h2>

<p align="center">&nbsp;</p>

<h2 align="center"><a name="gh"></a>Gynecologic history <br>

  </h2>

<div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr align="left" valign="bottom">

            <td width="50%" nowrap class="fibody2" id="bordR">&nbsp;</td>

            <td width="50%" align="center" class="ficaption2">Physicians notes </td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Last normal menstrual period (first day) 

              <? echo $fdata{'last_period_date'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_1'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Age periods began: 

              <? echo $fdata{'periods_began'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_2'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Length of periods (number of days of bleeding): 

              <? echo $fdata{'period_lenght'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_3'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Number of days between periods: 

              <? echo $fdata{'period_days_between'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_4'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Any recent changes in periods? 

              <? echo (($fdata{'period_changes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

Yes

<? echo (($fdata{'pih_gh_recent_changes_periods'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

No</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_5'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Are you currently sexually active? 

              <? echo (($fdata{'sexually_active'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

Yes

<? echo (($fdata{'sexually_active'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

No</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_6'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">have you ever had sex? 

              <? echo (($fdata{'ever_had_sex'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

Yes

<? echo (($fdata{'ever_had_sex'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

No</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_7'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Number of sexual partners (Lifetime): 

              <? echo $fdata{'number_of_partners'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_8'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Sexual partners are 

              <? echo (($fdata{'partners'} == 'men')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              Men 

              <? echo (($fdata{'partners'} == 'women')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              Women 

              <? echo (($fdata{'partners'} == 'both')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              Both </td>

            <td class="fibody2"><? echo $fdata{'gh_notes_9'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Present method of birth control: 

              <? echo $fdata{'present_birth_control'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_10'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Have you ever used an intrauterine device (IUD) or birth control pills ?

              <? echo (($fdata{'pills_iud'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

Yes

<? echo (($fdata{'pills_iud'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

No </td>

            <td class="fibody2"><? echo $fdata{'gh_notes_11'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">if yes, for how long? 

              <? echo $fdata{'pills_how_long'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_12'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">When was your last PAP test? 

              <? echo $fdata{'pap_test'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_13'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Do you do breast self examinations? 

              <? echo (($fdata{'breast_self_exam'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              Yes

              <? echo (($fdata{'breast_self_exam'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              No</td>

            <td class="fibody2"><? echo $fdata{'gh_notes_14'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Have you been exposed to diethylstilbestrol (DES)? 

              <? echo (($fdata{'des'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

Yes

<? echo (($fdata{'des'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

No </td>

            <td class="fibody2"><? echo $fdata{'gh_notes_15'}; ?>&nbsp</td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="oh"></a>Obstetric history <br>

  </h2>

  <div style="border: solid 1.5pt black; background-color:#FFFFFF;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">

            <tr align="left" valign="bottom">

              <td width="30%" nowrap class="fibody2" id="bordR">&nbsp;</td>

              <td width="50" align="center" nowrap class="ficaption2" id="bordR">Number</td>

              <td width="30%" align="center" nowrap class="fibody2" id="bordR">&nbsp;</td>

              <td width="50" align="center" nowrap class="ficaption2" id="bordR">Number</td>

              <td width="30%" align="center" nowrap class="fibody2" id="bordR">&nbsp;</td>

              <td width="50" align="center" nowrap class="ficaption2">Number</td>

            </tr>

            <tr align="left" valign="bottom">

              <td width="30%" nowrap class="fibody2" id="bordR">Pregnancies</td>

              <td width="50" nowrap class="fibody2" id="bordR"><? echo $fdata{'oh_pregnancies'}; ?>&nbsp</td>

              <td width="30%" nowrap class="fibody2" id="bordR">abortions</td>

              <td width="50" nowrap class="fibody2" id="bordR"><? echo $fdata{'oh_abortions'}; ?>&nbsp</td>

              <td width="30%" nowrap class="fibody2" id="bordR">miscarriages</td>

              <td width="50" nowrap class="fibody2"><? echo $fdata{'oh_miscarriages'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td width="30%" nowrap class="fibody2" id="bordR">premature births(&lt;37 weeks) </td>

              <td width="50" nowrap class="fibody2" id="bordR"><? echo $fdata{'oh_premature_births'}; ?>&nbsp</td>

              <td width="30%" nowrap class="fibody2" id="bordR">live births </td>

              <td width="50" nowrap class="fibody2" id="bordR"><? echo $fdata{'oh_live_births'}; ?>&nbsp</td>

              <td width="30%" nowrap class="fibody2" id="bordR">living children </td>

              <td width="50" nowrap class="fibody2"><? echo $fdata{'oh_living_children'}; ?>&nbsp</td>

            </tr>

        </table></td>

      </tr>

      <tr>

        <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">

            <tr align="center" valign="middle">

              <td class="ficaption2" id="bordR">No</td>

              <td class="ficaption2" id="bordR">birth date </td>

              <td class="ficaption2" id="bordR">weight at birth </td>

              <td class="ficaption2" id="bordR">baby's sex </td>

              <td class="ficaption2" id="bordR">weeks pregnant </td>

              <td class="ficaption2" id="bordR">type of delivery (<small>vaginal, cesarian etc.</small>) </td>

              <td class="ficaption2">physician's notes</td>

            </tr>

<?

  $bi = 0;

  while ($bi<4) {

    $n = $bi+1;

    list ($oh_ch_date, $oh_ch_width, $oh_ch_sex, $oh_ch_weeks, $oh_ch_delivery, $oh_ch_notes) = explode('|~', $fdata["oh_ch_rec_".$bi] );

    print <<<EOL

        <tr align="left" valign="bottom">		

          <td nowrap class="fibody2" id="bordR">$n.</td>

          <td nowrap class="fibody2" id="bordR">${oh_ch_date}&nbsp;</td>

          <td nowrap class="fibody2" id="bordR">${oh_ch_width}&nbsp;</td>

          <td nowrap class="fibody2" id="bordR">${oh_ch_sex}&nbsp;</td>

          <td nowrap class="fibody2" id="bordR">${oh_ch_weeks}&nbsp;</td>

          <td nowrap class="fibody2" id="bordR">${oh_ch_delivery}&nbsp;</td>

          <td nowrap class="fibody2">${oh_ch_notes}&nbsp;</td>

        </tr>

EOL;

     $bi++;

  }

?>

        </table></td>

      </tr>

      <tr>

        <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">

            <tr align="left" valign="bottom">

              <td width="23%" nowrap class="fibody2">Any pregnancy complications? </td>

              <td class="fibody2"><? echo $fdata{'oh_complications'}; ?>&nbsp</td>

            </tr>

        </table></td>

      </tr>

      <tr>

        <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">

            <tr align="left" valign="bottom">

              <td colspan="2" class="fibody2"><? echo (($fdata{'oh_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              diabetes

                <? echo (($fdata{'oh_hipertension'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              hypertension/high blood pressure

              <? echo (($fdata{'oh_preemclampsia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              preeclampsia/foxemia

              <? echo (($fdata{'oh_complic_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              other </td>

            </tr>

            <tr align="left" valign="bottom">

              <td width="472" nowrap class="fibody2">any history of depression before or after pregnancy?

                  <? echo (($fdata{'oh_depression'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              no

              <? echo (($fdata{'oh_depression'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              yes, How treated </td>

              <td class="fibody2"><? echo $fdata{'oh_depression_treated'}; ?>&nbsp</td>

            </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="cm"></a>Current medications <br>

    <small>(Including hormones, vitamins, herbs, nonprescription medications) </small><br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr align="center">

        <td valign="top" class="ficaption2" id="bordR">Drug name </td>

        <td valign="top" class="ficaption2" id="bordR">Dosage</td>

        <td valign="top" class="ficaption2" id="bordR">Who prescribed </td>

        <td valign="top" class="ficaption2" id="bordR">Drug name </td>

        <td valign="top" class="ficaption2" id="bordR">Dosage</td>

        <td valign="top" class="ficaption2">Who prescribed</td>

      </tr>

<?

  $bi = 0;

  while ($bi<5) {

  $bi2 = $bi+5;

      list ($pres_drug, $pres_dosage, $pres_who) = explode('|~', $fdata["pres_drug_rec_".$bi] );

      list ($pres_drug1, $pres_dosage1, $pres_who1) = explode('|~', $fdata["pres_drug_rec_".$bi2] );

    print <<<EOL

      <tr>

        <td align="left" valign="top" class="fibody2" id="bordR">${pres_dosage}&nbsp;</td>

        <td align="left" valign="top" class="fibody2" id="bordR">${pres_drug}&nbsp;</td>

        <td align="left" valign="top" class="fibody2" id="bordR">${pres_who}&nbsp;</td>

        <td align="left" valign="top" class="fibody2" id="bordR">${pres_drug1}&nbsp;</td>

        <td align="left" valign="top" class="fibody2" id="bordR">${pres_dosage1}&nbsp;</td>

        <td align="left" valign="top" class="fibody2">${pres_who1}&nbsp;</td>

      </tr>

EOL;

     $bi++;

  }

?>	   

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="fh"></a>Family history <br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr align="left" valign="bottom">

            <td width="50%" nowrap class="fibody2" id="bordR">Mother: 

              <? echo (($fdata{'fh_mother'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              living 

              <? echo (($fdata{'fh_mother'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

              deceased - cause: 

              <? echo $fdata{'fh_mother_dec_cause'}; ?>

              Age: 

              <? echo $fdata{'fh_mother_dec_age'}; ?>&nbsp</td>

            <td width="50%" nowrap class="fibody2">father:

              <? echo (($fdata{'fh_father'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

living

<? echo (($fdata{'fh_father'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

deceased - cause:

<? echo $fdata{'fh_father_dec_cause'}; ?>

Age:

<? echo $fdata{'fh_father_dec_age'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2">Siblings: Num.living: 

              <? echo $fdata{'fh_sibl_living'}; ?>

              , num.deceased:

              <? echo $fdata{'fh_sib_deceased'}; ?>

              , cause(s)/age(s): </td>

            <td nowrap class="fibody2"><? echo $fdata{'fh_sib_dec_cause'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2">Children: Num.living:

              <? echo $fdata{'fh_children_living'}; ?>

, num.deceased:

<? echo $fdata{'fh_children_deceased'}; ?>

, cause(s)/age(s):</td>

            <td nowrap class="fibody2"><? echo $fdata{'fh_children_dec_cause'}; ?>&nbsp</td>

          </tr>

        </table></td>

      </tr>

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr valign="bottom">

            <td width="120" align="left" nowrap class="ficaption2" id="bordR">Illness</td>

            <td width="30" align="center" class="ficaption2" id="bordR">yes</td>

            <td width="250" align="center" class="ficaption2" id="bordR">which relative(s) and age of onset </td>

            <td align="center" class="ficaption2">Physician's notes </td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">diabetes</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_diabetes_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_1'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Stroke</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_stroke'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_stroke_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_2'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Heart dIsease </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_heart_disease'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"> <? echo $fdata{'fh_heart_disease_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_3'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Blood clots in lungs or legs </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fhbllod_clots'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fhbllod_clots_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_4'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">High blood pressure </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_high_pressure'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_high_pressure_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_5'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">High cholesterol</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_high_cholesterol'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_high_cholesterol_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_6'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Osteoporosis (weak bones) </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_osteoporosis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_osteoporosis_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_7'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Hepatitis</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_hepatitis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_hepatitis_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_8'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">HIV / AIDS</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_hiv'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_hiv_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_9'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Tuberculosis</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_tuberculosis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_tuberculosis_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_10'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Birth defects </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'dh_birth_defects'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'dh_birth_defects_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_11'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Alcohol or drug problems </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_alcohol_drugs'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_alcohol_drugs_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_12'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Breast cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_breast_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_breast_cancer_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_13'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Colon cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_colon_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_colon_cancer_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_14'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Ovarian cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_ovarian_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_ovarian_cancer'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_15'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Uterine cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_uterine_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_uterine_cancer_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_16'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Mental illness/Depression </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_mental_illness'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_mental_illness_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_17'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Alzheimer's disease </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_alzheimer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_alzheimer_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_18'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Other</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'fh_other_illness'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2" id="bordR"><? echo $fdata{'fh_other_illness_info'}; ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'fh_notes_19'}; ?>&nbsp</td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="sh"></a>Social history <br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white; page-break-after: always;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr align="left" valign="bottom">

            <td width="400" class="ficaption2" id="bordR">&nbsp;</td>

            <td width="30" align="center" class="ficaption2" id="bordR">yes</td>

            <td width="30" align="center" class="ficaption2" id="bordR">no</td>

            <td align="center" class="ficaption2">physician's notes                           </td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Ever smoked? Current smoking: packs/day:

              <? echo $fdata{'sh_smoked_packs'}; ?> 

              , years: 

              <? echo $fdata{'sh_smoked_years'}; ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_smoked'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_smoked'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_1'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">alcohol: drinks/day:

              <? echo $fdata{'sh_alcohol_drinks_day'}; ?>

              , 

              drinks/week:

              <? echo $fdata{'sh_alcohol_drinks_week'}; ?>

              , 

              type of drink:

              <? echo $fdata{'sh_alcohol_drinks_type'}; ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_alcohol'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_alcohol'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_2'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Drug use </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_drug'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_drug'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_3'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">seat belt use </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_seat_belt'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_seat_belt'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_4'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">regular exercise: how long and how often? 

              <? echo $fdata{'sh_exercise_info'}; ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_exercise'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_exercise'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td width="400" class="fibody2"><? echo $fdata{'sh_notes_5'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Dairy product intake and/or calcium supplements: daily intake: 

              <? echo $fdata{'sh_dairy_daily'}; ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_dairy'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_dairy'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td width="400" class="fibody2"><? echo $fdata{'sh_notes_6'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">health hazards at home or work? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_hazards'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_hazards'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td width="400" class="fibody2"><? echo $fdata{'sh_notes_7'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">have you been sexually abused, threatened or hurt by anyone? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_abuse'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_abuse'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td width="400" class="fibody2"><? echo $fdata{'sh_notes_8'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">do you have an advance directive (living will)?</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_living_will'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_living_will'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_9'}; ?>&nbsp</td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Are you an organ donor? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_donor'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><? echo (($fdata{'pih_donor'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

            <td class="fibody2"><? echo $fdata{'sh_notes_10'}; ?>&nbsp</td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="pp"></a>Personal profile <br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="bottom" class="fibody2">Sexual orientation: 

          <? echo (($fdata{'pih_pp_orientation'} == 'hetero')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        heterosexual 

        <? echo (($fdata{'pih_pp_orientation'} == 'homo')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        homosexual 

        <? echo (($fdata{'pih_pp_orientation'} == 'bi')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        bisexual </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Marital status: 

          <? echo (($fdata{'pih_pp_status'} == 'married')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        married

        &nbsp;&nbsp;

        <? echo (($fdata{'pih_pp_status'} == 'partner')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 

        living with partner&nbsp;&nbsp;        <? echo (($fdata{'pih_pp_status'} == 'single')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        single

        &nbsp;&nbsp;

        <? echo (($fdata{'pih_pp_status'} == 'widowed')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 

        widowed&nbsp;&nbsp;        <? echo (($fdata{'pih_pp_status'} == 'divorced')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        divorced </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Number of living children:        

        <? echo $fdata{'pp_living_children'}; ?>&nbsp</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Number of people in household: 

        <? echo $fdata{'pp_number_household'}; ?>&nbsp</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">School completed: 

          <? echo (($fdata{'pih_pp_education'} == 'highschool')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        high school 

        <? echo (($fdata{'pih_pp_education'} == 'aadegree')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        some college/AA degree 

        <? echo (($fdata{'pih_pp_education'} == 'college')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        college 

        <? echo (($fdata{'pih_pp_education'} == 'gdegree')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        graduate degree 

        <? echo (($fdata{'pih_pp_education'} == 'other')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        other </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Current or most recent job: 

        <? echo $fdata{'pp_current_job'}; ?>&nbsp</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody3">Travel outside the United States? 

          <? echo (($fdata{'pp_travel_outside_us'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          yes 

          <? echo (($fdata{'pp_travel_outside_us'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

          no.&nbsp;&nbsp;&nbsp;Location(s): <span class="fibody2">

          <? echo $fdata{'pp_travel_outside_locations'}; ?>

        </span></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="ih"></a>Personal past history of illnesses <br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white; page-break-after: always;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

            <tr align="left" valign="bottom">

              <td width="200" nowrap class="ficaption2" id="bordR">major illnesses </td>

              <td width="100" align="center" class="ficaption2" id="bordR">yes (date) </td>

              <td width="30" align="center" class="ficaption2" id="bordR">no</td>

              <td width="58" align="center" class="ficaption2" id="bordR">Not sure </td>

              <td align="center" class="ficaption2">Physician's notes </td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Asthma</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_asthma'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_asthma_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_asthma'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_asthma'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_1'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Pneumonia/lungs disease </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_pneumonia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_pneumonia_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_pneumonia'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_pneumonia'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_2'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Kidney infections/stones </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_kidney'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_kidney_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_kidney'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_kidney'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_3'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Tuberculosis</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_tuber'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_tuber_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_tuber'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_tuber'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_4'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Fibroids</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_fibroids'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_fibroids_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_fibroids'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_fibroids'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_5'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Sexually transmitted disease/chlamydia </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_sexually'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_sexually_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_sexually'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_sexually'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_6'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Infertility</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_infertil'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_infertil_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_infertil'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_infertil'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_7'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">HIV / AIDS </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hiv'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_hiv_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hiv'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hiv'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_8'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Heart attack / Disease </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_heart'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_heart_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_heart'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_heart'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_9'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Diabetes</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_diabetes_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_diabetes'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_diabetes'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_10'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">High blood pressure </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_high_pressure'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_high_pressure_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_high_pressure'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_high_pressure'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_11'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Stroke</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_stroke'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_stroke_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_stroke'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_stroke'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_12'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Rheumatic fever </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_rheumatic'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_rheumatic_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_rheumatic'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_rheumatic'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_13'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Blood clots in lungs or legs </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_clots'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_blood_clots_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_clots'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_clots'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_14'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Eating disorders </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_eating_disorder'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_eating_disorder_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_eating_disorder'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_eating_disorder'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_15'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Autoimmune disease (Lupus)</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_autoimmune'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_autoimmune_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_autoimmune'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_autoimmune'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_16'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Chickenpox</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_chickenpox'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_chickenpox_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_chickenpox'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_chickenpox'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_17'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Cancer</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_cancer_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cancer'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cancer'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_18'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Reflux / Hiatal hernia / Ulcers </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_reflux'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_reflux_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_reflux'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_reflux'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_19'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Depression / Anxiety </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_depression'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_depression_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_depression'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_depression'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_20'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Anemia</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_anemia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_anemia_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_anemia'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_anemia'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_21'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Blood transfusions </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_transf'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_blood_transf_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_transf'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_blood_transf'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_22'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Seizures / Convulsions /Epilepsy </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_seizures'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_seizures_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_seizures'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_seizures'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_23'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Bowel problems </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bowel_problems'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_bowel_problems_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bowel_problems'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bowel_problems'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_24'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Glaucoma</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_glaucoma'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_glaucoma_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_glaucoma'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_glaucoma'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_25'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Cataracts</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cataracts'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_cataracts_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cataracts'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_cataracts'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_26'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Arthritis / Joint pain / Back problems </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_joint_pain'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_joint_pain_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_joint_pain'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_joint_pain'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_27'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Broken bones </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_broken_bones'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_broken_bones_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_broken_bones'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_broken_bones'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_28'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Hepatitis / Yellow jaundice / Liver disease </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hepatitis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_hepatitis_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hepatitis'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_hepatitis'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_29'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Thyroid disease </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_thyroid'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_thyroid_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_thyroid'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_thyroid'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_30'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Gallbladder disease </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_galibladder'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_galibladder_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_galibladder'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_galibladder'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_31'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Headaches</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_headaches'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_headaches_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_headaches'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_headaches'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_32'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">DES Exposure </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_des'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_des_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_des'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_des'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_33'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Bleeding disorders </td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bleeding_disorders'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_bleeding_disorders_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bleeding_disorders'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_bleeding_disorders'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_34'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">other</td>

              <td class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

                  <? echo $fdata{'pih_ih_other_date'}; ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_other'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td align="center" class="fibody2" id="bordR"><? echo (($fdata{'pih_ih_other'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp</td>

              <td class="fibody2"><? echo $fdata{'ih_notes_35'}; ?>&nbsp</td>

            </tr>

            <tr align="left" valign="bottom">

              <td colspan="5" nowrap class="fibody3"><? echo $fdata{'pih_ih_extended_info'}; ?>&nbsp</td>

            </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="op"></a>Operations/Hospitalizations<br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td width="50%" align="left" valign="bottom" class="ficaption2" id="bordR">Reason</td>

        <td width="90" align="center" valign="bottom" class="ficaption2" id="bordR">Date</td>

        <td align="center" valign="bottom" class="ficaption2">Hospital</td>

      </tr>

<?	

$ii = 0;

while ($ii<6){

list ($op_reason, $op_date, $op_hospital)= explode('|~', $fdata["op_rec_".$ii] );

print <<<EOL

      <tr>

        <td align="left" valign="bottom" class="fibody2" id="bordR">${op_reason}&nbsp;</td>

        <td align="left" valign="bottom" class="fibody2" id="bordR">${op_date}&nbsp;</td>

        <td align="left" valign="bottom" class="fibody2">${op_hospital}&nbsp;</td>

      </tr>

EOL;

$ii++;

}

?>	  

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="ii"></a>Injuries/Illnesses<br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr valign="bottom">

        <td align="left" class="ficaption2" id="bordR">Type</td>

        <td width="90" align="center" nowrap class="ficaption2" id="bordR">date</td>

        <td align="left" class="ficaption2" id="bordR">Type</td>

        <td width="90" align="center" nowrap class="ficaption2">date</td>

      </tr>

<?	

$ii = 0;

while ($ii<6){

$ij = $ii+6;

list ($ii_type, $ii_date)= explode('|~', $fdata["ii_rec_".$ii] );

list ($ii_type1, $ii_date1)= explode('|~', $fdata["ii_rec_".$ij] );



print <<<EOL

   <tr valign="bottom">

     <td align="left" class="fibody2" id="bordR">${ii_type}&nbsp;</td>

     <td align="left" nowrap class="fibody2" id="bordR">${ii_date}&nbsp;</td>

     <td align="left" class="fibody2" id="bordR">${ii_type1}&nbsp;</td>

     <td align="left" nowrap class="fibody2">${ii_date1}&nbsp;</td>

   </tr>

EOL;

$ii++;

}

?>		  

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="im"></a>Immunizations/Test<br>

  </h2>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr valign="bottom">

        <td align="left" nowrap class="ficaption2" id="bordR">Type</td>

        <td width="90" align="center" class="ficaption2" id="bordR">date</td>

        <td align="left" nowrap class="ficaption2" id="bordR">type</td>

        <td width="90" align="center" class="ficaption2">date</td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">Tetanus-Diphteria booster </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <? echo $fdata{'imm_tetanus'}; ?>&nbsp;</td>

        <td align="left" nowrap class="fibody2" id="bordR">Influenza vaccine (Flu shot) </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <? echo $fdata{'imm_influenza'}; ?>&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">hepatitis a vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <? echo $fdata{'imm_hepatitis_a'}; ?>&nbsp;</td>

        <td align="left" nowrap class="fibody2" id="bordR">Hepatitis B vaccine </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <? echo $fdata{'imm_hepatitis_b'}; ?>&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">varicella (Chickenpox) vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <? echo $fdata{'imm_varicella'}; ?>&nbsp;</td>

        <td align="left" nowrap class="fibody2" id="bordR">pneumococcal (pneumonia) vaccine </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <? echo $fdata{'imm_pneumococcal'}; ?>&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">Measles-Mumps-Rubella (MMR) Vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <? echo $fdata{'imm_mmr'}; ?>&nbsp;</td>

        <td align="left" nowrap class="fibody2" id="bordR">Tuberculosis (TB) Skin test:

        <? echo $fdata{'imm_tuberculosis_skin'}; ?>&nbsp;

        , result: 

        <? echo $fdata{'imm_tuberculosis_result'}; ?>&nbsp</td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <? echo $fdata{'imm_tuberculosis'}; ?>&nbsp;</td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <div style="border: solid 1.5pt black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="top" class="fibody3">Physician's notes: <br>

        <? echo $fdata{'imm_extended_info'}; ?>&nbsp</td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a ></a>Review of systems<br>

    <small>Please check (x), if any of the following symptoms

apply to you now or since adulthood</small> </h2>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td width="300" align="left" valign="top" class="fibody4"  id="bordR" >&nbsp;</td>

        <td width="58" align="center" valign="top" class="ficaption2"  id="bordR" >now</td>

        <td width="58" align="center" valign="top" class="ficaption2"  id="bordR" >past</td>

        <td width="58" align="center" valign="top" class="ficaption2"  id="bordR" >not sure </td>

        <td align="center" valign="top" class="ficaption2">physician's notes </td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >1. Constitutional </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Weight loss </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_loss_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_loss_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_loss_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_1'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Weight gain </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_gain_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_gain_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_weight_gain_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_2'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Fever</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fever_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fever_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fever_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_3'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Fatigue</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fatigue_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fatigue_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_fatigue_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_4'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Change in height </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_height_change_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_height_change_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_height_change_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_5'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >2. Eyes </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Double vision </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dvision_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dvision_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dvision_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_6'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Spots before eyes </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spots_eyes_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spots_eyes_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spots_eyes_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_7'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Vision changes </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vis_changes_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vis_changes_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vis_changes_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_8'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Glasses/contacts</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_glasses_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_glasses_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_glasses_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_9'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >3. Ear, nose and throat </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Earaches</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_earaches_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_earaches_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_earaches_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_10'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Ringing in ears </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_ringing_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_ringing_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_ringing_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_11'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Hearing problems</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hearing_problems_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hearing_problems_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hearing_problems_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_12'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sinus problems </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sinus_problems_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sinus_problems_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sinus_problems_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_13'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sore throat </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sore_throat_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sore_throat_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sore_throat_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_14'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Mouth sores </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_mouth_sores_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_mouth_sores_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_mouth_sores_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_15'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Dental problems </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dental_problems_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dental_problems_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dental_problems_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_16'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >4. Cardiovascular </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Chest pain on pressure </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chest_pain_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chest_pain_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chest_pain_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_17'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Difficulty breathing on exertion </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_difficulty_breathing_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_difficulty_breathing_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_difficulty_breathing_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_18'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Swelling on legs </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_swelling_legs_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_swelling_legs_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_swelling_legs_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_19'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Rapid or irregular heartbeat </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rapid_heartbeat_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rapid_heartbeat_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rapid_heartbeat_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_20'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >5. Respiratory </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Painful breathing </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_breathing_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_breathing_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_breathing_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_21'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Wheezing</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_wheezing_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_wheezing_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_wheezing_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_22'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Spitting up blood </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spitting_blood_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spitting_blood_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_spitting_blood_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_23'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Shortness of breath </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_breath_shortness_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_breath_shortness_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_breath_shortness_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_24'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Chronic cough </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chronic_cough_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chronic_cough_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_chronic_cough_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_25'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >6. Gastrointestinal </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent diarrhea </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_diarrhea_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_diarrhea_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_diarrhea_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_26'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Bloody stool </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_bloody_stool_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_bloody_stool_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_bloody_stool_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_27'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Nausea / vomiting indigestion </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nausea_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nausea_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nausea_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_28'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Constipation</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_constipation_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_constipation_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_constipation_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_29'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Involuntary loss of gas or stool </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_gas_loss_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_gas_loss_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_gas_loss_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_30'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >7. Genitourinary </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Blood in urine </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_blood_urine_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_blood_urine_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_blood_urine_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_31'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Pain with urination </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_urination_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_urination_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_urination_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_32'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Strong urgency to urinate </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_urgency_urinate_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_urgency_urinate_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_urgency_urinate_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_33'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent urination </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_urination_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_urination_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_urination_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_34'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Incomplete emtying </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_incomplete_emptying_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_incomplete_emptying_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_incomplete_emptying_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_35'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Involuntary/Unintended urine loss </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_unint_urine_loss_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_unint_urine_loss_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_unint_urine_loss_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_36'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Urine loss when coughing or lifting </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lifting_urine_loss_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lifting_urine_loss_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lifting_urine_loss_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_37'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal bleeding</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_bleeding_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_bleeding_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_bleeding_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_38'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Painful periods </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_periods_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_periods_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_periods_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_39'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Premenstrual Syndrome (PMS) </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pms_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pms_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pms_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_40'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Painful intercourse </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_intercourse_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_intercourse_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_painful_intercourse_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_41'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal vaginal discharge </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vaginal_discharge_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vaginal_discharge_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_vaginal_discharge_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_42'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >8. Musculoskeletal </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Muscle weakness </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_weakness_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_weakness_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_weakness_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_43'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Muscle or joint pain </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_pain_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_pain_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_muscle_pain_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_44'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >9a. Skin </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Rash</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rash_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rash_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_rash_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_45'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sores</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sores_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sores_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_sores_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_46'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Dry skin </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dry_skin_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dry_skin_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dry_skin_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_47'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Moles (growth or changes) </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_moles_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_moles_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_moles_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_48'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >9b. Breasts </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Pain in breast </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_breast_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_breast_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_pain_breast_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_49'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Nipple discharge </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nipple_discharge_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nipple_discharge_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_nipple_discharge_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_50'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Lumps</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lumps_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lumps_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_lumps_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_51'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >10. Neurologic </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Dizziness</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dizziness_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dizziness_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_dizziness_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_52'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Seizures</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_seizures_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_seizures_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_seizures_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_53'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Numbness</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_numbness_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_numbness_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_numbness_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_54'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Trouble walking </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_trouble_walking_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_trouble_walking_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_trouble_walking_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_55'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Memory problems </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_memory_problems_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_memory_problems_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_memory_problems_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_56'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent headaches </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_freq_headaches_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_freq_headaches_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_freq_headaches_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_57'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >11. Psychiatric </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Depression or frequent crying </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_depression_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_depression_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_depression_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_58'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Anxiety</td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_anxiety_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_anxiety_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_anxiety_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_59'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >12. Endocrine </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Hair loss </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hair_loss_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hair_loss_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hair_loss_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_60'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Heat/cold intolerance </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_heat_cold_intolerance_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_heat_cold_intolerance_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_heat_cold_intolerance_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_61'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal thirst </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_thirst_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_thirst_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_abnormal_thirst_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_62'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Hot flashes </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hot_flashes_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hot_flashes_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_hot_flashes_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_63'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >13. Hematologic/Lymphatic </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent bruises </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_bruises_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_bruises_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_frequent_bruises_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_64'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Cuts do not stop bleeding </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_cuts_bleeding_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_cuts_bleeding_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_cuts_bleeding_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_65'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Enlarged Lymph nodes (glands) </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_enlarged_nodes_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_enlarged_nodes_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_enlarged_nodes_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_66'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="ficaption2"  id="bordR" >14. Allergic/immunologic </td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="center" class="fibody2"  id="bordR" >&nbsp;</td>

        <td align="left" class="fibody2">&nbsp;</td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Medication allergies </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_med_allergy_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_med_allergy_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_med_allergy_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_68'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >If any, please list allergy and type of reaction: </td>

        <td colspan="4" align="left" class="fibody2"><? echo $fros{'ros_med_allergy_reaction'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Latex allergy </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_latex_allergy_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_latex_allergy_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_latex_allergy_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_69'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Other allergies </td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_other_allergy_now'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_other_allergy_now'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="center" class="fibody2"  id="bordR" ><? echo (($fros{'ros_other_allergy_now'} == '3')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>

        <td align="left" class="fibody2"><? echo $fros{'ros_notes_70'}; ?></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Please list allergy and type of reaction: </td>

        <td colspan="4" align="left" class="fibody2"><? echo $fros{'ros_other_allergy_reaction_'}; ?></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr valign="bottom">

        <td colspan="2" align="left" class="fibody2">Form completed by 

          <? echo (($fdata{'pih_completed_by'} == 'patient')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        patient 

        <? echo (($fdata{'pih_completed_by'} == 'nurse')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        office nurse 

        <? echo (($fdata{'pih_completed_by'} == 'physician')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        physician 

        <? echo (($fdata{'pih_completed_by'} == 'other')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>

        other: 

        <? echo $fdata{'pih_completed_by_other'}; ?>&nbsp</td>

      </tr>

      <tr valign="bottom">

        <td height="46" colspan="2" align="left" class="fibody2">Signature of patient:</td>

      </tr>

      <tr valign="bottom">

        <td width="39%" height="46" align="left" class="fibody3" id="bordR">Date reviewed by physician with patient 

        <? echo $fdata{'pih_date_reviewed_1'}; ?>&nbsp</td>

        <td width="61%" height="46" align="left" class="fibody3">Physician signature: </td>

      </tr>

      <tr valign="bottom">

        <td colspan="2" align="left" class="ficaption3" style="border-top: 2px solid black; border-bottom: 2px solid black;">Annual review of history </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <? echo $fdata{'pih_date_reviewed_2'}; ?>

        </span></td>

        <td height="46" align="left" class="fibody2">Physician signature: </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <? echo $fdata{'pih_date_reviewed_3'}; ?>

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <? echo $fdata{'pih_date_reviewed_4'}; ?>

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <? echo $fdata{'pih_date_reviewed_5'}; ?>

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <? echo $fdata{'pih_date_reviewed_6'}; ?>

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

</body>

</html>