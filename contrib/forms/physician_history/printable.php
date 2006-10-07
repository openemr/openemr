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
<title>Physician history</title>

<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
 
   $fres=sqlStatement("select * from form_physician_history where id='".$id."'");
   if ($fres){
     $fdata = sqlFetchArray($fres);
   }
?>
<? include("../../acog_printable_v.css"); ?>
</head>

<body>
<table width="70%"  border="0" cellspacing="0" cellpadding="4" style="">
  <tr>
    <td width="120" align="left" valign="top" class="srvCaption">Patient name:</td>
    <td align="left" valign="top" class="fibody5"><?  echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};?></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="top" class="srvCaption">Birth date: </td>
    <td align="left" valign="top" class="fibody5"><?          echo $patient{'DOB'};          ?></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="top" class="srvCaption">ID No:</td>
    <td align="left" valign="top" class="fibody5"><?          echo $patient{'id'};          ?></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="top" class="srvCaption">Date</td>
    <td align="left" valign="top" class="fibody5"><?        echo $fdata['date'];        ?></td>
  </tr>
</table>
<div class="srvChapter">Physician history</div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="baseline">
        <td width="25%" class="fibody2" id="bordR"><? echo (($fdata{'established'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          New patient </td>
        <td width="25%" class="fibody2" id="bordR"><? echo (($fdata{'established'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          Established patient </td>
        <td width="20%"   class="fibody2" id="bordR"><? echo (($fdata{'consultation'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          Consultation</td>
        <td width="30%" valign="top" class="fibody2"><? echo (($fdata{'report_sent'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> Report sent <? echo $fdata{'report_sent_date'}; ?></td>
        </tr>
      <tr align="left" valign="top">
        <td colspan="2" class="fibody2" id="bordR">Primary care physician:<br> 
          <? echo $fdata{'primary_care'}; ?></td>
        <td colspan="2" class="fibody2">Who sent patient:<br>
          <? echo $fdata{'who_sent'}; ?></td>
      </tr>
      <tr align="left" valign="top">
        <td colspan="2" class="fibody2" id="bordR"> Other physician(s):<br> <? echo $fdata{'other_physician'}; ?> </td>
        <td colspan="2" class="fibody2">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="baseline">
        <td width="50%" class="ficaption2" id="bordR">Chief complaint (CC) (<small>Required for all visits except preventive</small>):</td>
        <td width="50%" class="ficaption2">Current prescription medications:          </td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="50%"   class="fibody5" id="bordR"><? echo $fdata{'chief_complaint'}; ?> &nbsp;</td>
        <td width="50%"   class="fibody5"><? echo $fdata{'current_prescription'}; ?> &nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="50%" valign="top" class="ficaption2" id="bordR">History of present ilness (HPI): <br> </td>
        <td width="50%" valign="top" class="ficaption2">Current nonpresription, complementary, and alternative medications:          </td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="50%" valign="top"   class="fibody5" id="bordR"><? echo $fdata{'hpi'}; ?>&nbsp;</td>
        <td width="50%" valign="top"   class="fibody5"><? echo $fdata{'current_nonprescription'}; ?>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="fibody2">&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="top">
        <td width="200" class="ficaption2">Changes since last visit </td>
        <td width="40" align="center" class="ficaption2">yes</td>
        <td width="40" align="center" class="ficaption2" id="bordR">no</td>
        <td colspan="2" align="center" class="ficaption2">Notes</td>
        </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">Illnesses</td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_ill'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_ill'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td colspan="2" rowspan="7" valign="top" class="fibody5"><? echo $fdata{'ph_lvch_notes'}; ?>&nbsp;</td>
        </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">Surgery</td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_surg'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_surg'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">New medications </td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_newmed'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_newmed'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">Change in family history </td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_famhist'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_famhist'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">New allergies </td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_newallerg'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_newallerg'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">Change in gynecologic history </td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_gynhist'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_gynhist'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="200" class="fibody2">Change in obstetric history </td>
        <td width="40" align="center" class="fibody2"><? echo (($fdata{'ph_lvch_obsthist'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
        <td width="40" align="center" class="fibody2" id="bordR"><? echo (($fdata{'ph_lvch_obsthist'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="fibody2">&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="top">
        <td width="225" class="fibody2"><a name="allergies"></a>Allergies (describe reaction):
          <? echo (($fdata{'ph_allergies_none'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
none</td>
        <td class="fibody5"><? echo $fdata{'ph_allergies_data'}; ?></td>
      </tr>
    </table></td>
    </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="top">
        <td width="70%" class="fibody2" id="bordR">Last cervical cancer screening: 
          <? echo (($fdata{'cancer_scr_cytology'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          Cytology
          <? echo $fdata{'cancer_scr_cytology_date'}; ?>
          <? echo (($fdata{'cancer_scr_hpv'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          HPV test 
          <? echo $fdata{'cancer_scr_hpv_date'}; ?></td>
        <td width="30%" class="fibody5"><? echo $fdata{'cancer_scr_notes'}; ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="70%" class="fibody2" id="bordR">last mammogram: 
          <? echo $fdata{'last_mammogram'}; ?></td>
        <td class="fibody5"><? echo $fdata{'last_mammogram_notes'}; ?></td>
      </tr>
      <tr align="left" valign="top">
        <td width="70%" class="fibody2" id="bordR">Last colorectal screening: 
          <? echo $fdata{'last_colorectal'}; ?></td>
        <td class="fibody5"><? echo $fdata{'last_colorectal_notes'}; ?></td>
      </tr>
    </table></td>
  </tr>
</table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="gh"></a>Gynecologic history (PH)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr align="left" valign="top">
      <td colspan="4"><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="top">
          <td   class="fibody2">Imp</td>
          <td   class="fibody2"><? echo $fdata{'gh_imp'}; ?></td>
          <td   class="fibody2">Age at menarche </td>
          <td   class="fibody2"><? echo $fdata{'gh_age_at_menarche'}; ?></td>
          <td   class="fibody2">Length of flow </td>
          <td   class="fibody2"><? echo $fdata{'gh_length_of_flow'}; ?></td>
          <td   class="fibody2">Interval between periods </td>
          <td   class="fibody2"><? echo $fdata{'gh_interval_periods'}; ?></td>
          <td   class="fibody2">Recent changes </td>
          <td   class="fibody2"><? echo $fdata{'gh_recent_changes'}; ?></td>
        </tr>
      </table></td>
    </tr>
    <tr align="left" valign="top">
      <td colspan="4"   class="fibody2">Sexually active: 
        <? echo (($fdata{'gh_sexually_active'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        Yes
        <? echo (($fdata{'gh_sexually_active'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ever had sex: 
        <? echo (($fdata{'gh_had_sex'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Yes
<? echo (($fdata{'gh_had_sex'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Number of partners (Lifetime) 
<? echo $fdata{'gh_partners'}; ?></td>
      </tr>
    <tr align="left" valign="top">
      <td colspan="4"   class="fibody2">Partners are: 
        <? echo (($fdata{'gh_partners_are'} == 'men')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        men 
        <? echo (($fdata{'gh_partners_are'} == 'women')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        women
        <? echo (($fdata{'ph_gh_partners_are'} == 'both')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        both</td>
      </tr>
    <tr align="left" valign="top">
      <td width="202" nowrap class="fibody2">Current method of contraception: </td>
      <td width="30%"   class="fibody5"><? echo $fdata{'gh_method_contraception'}; ?>&nbsp;</td>
      <td width="161" nowrap class="fibody2">Past contraceptive history:</td>
      <td width="34%"   class="fibody5"><? echo $fdata{'gh_contraceptive_history'}; ?>&nbsp;</td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="oh"></a>Obstetric history (PH)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF; page-break-after: always;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="top">
          <td width="30%"   class="fibody2" id="bordR">&nbsp;</td>
          <td width="50" align="center"   class="ficaption2" id="bordR">Number</td>
          <td width="30%" align="center"   class="fibody2" id="bordR">&nbsp;</td>
          <td width="50" align="center"   class="ficaption2" id="bordR">Number</td>
          <td width="30%" align="center"   class="fibody2" id="bordR">&nbsp;</td>
          <td width="50" align="center"   class="ficaption2">Number</td>
        </tr>
        <tr align="left" valign="top">
          <td width="30%"   class="fibody2" id="bordR">Pregnancies</td>
          <td width="50" align="center"   class="fibody2" id="bordR"><? echo $fdata{'oh_pregnancies'}; ?></td>
          <td width="30%"   class="fibody2" id="bordR">abortions</td>
          <td width="50" align="center"   class="fibody2" id="bordR"><? echo $fdata{'oh_abortions'}; ?></td>
          <td width="30%"   class="fibody2" id="bordR">miscarriages</td>
          <td width="50" align="center"   class="fibody2"><? echo $fdata{'oh_miscarriages'}; ?></td>
        </tr>
        <tr align="left" valign="top">
          <td width="30%"   class="fibody2" id="bordR">premature births(&lt;37 weeks) </td>
          <td width="50" align="center"   class="fibody2" id="bordR"><? echo $fdata{'oh_premature_births'}; ?></td>
          <td width="30%"   class="fibody2" id="bordR">live births </td>
          <td width="50" align="center"   class="fibody2" id="bordR"><? echo $fdata{'oh_live_births'}; ?></td>
          <td width="30%"   class="fibody2" id="bordR">living children </td>
          <td width="50" align="center"   class="fibody2"><? echo $fdata{'oh_living_children'}; ?></td>
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
        <tr align="left" valign="top">		
          <td   class="fibody2" id="bordR">$n.</td>
          <td   class="fibody2" id="bordR">${oh_ch_date}&nbsp;</td>
          <td   class="fibody2" id="bordR">${oh_ch_width}&nbsp;</td>
          <td   class="fibody2" id="bordR">${oh_ch_sex}&nbsp;</td>
          <td   class="fibody2" id="bordR">${oh_ch_weeks}&nbsp;</td>
          <td   class="fibody2" id="bordR">${oh_ch_delivery}&nbsp;</td>
          <td   class="fibody2">${oh_ch_notes}&nbsp;</td>
        </tr>
EOL;
     $bi++;
  }
?>			
      </table></td>
    </tr>
    <tr>
      <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="top">
          <td width="23%" nowrap   class="fibody2">Any pregnancy complications? </td>
          <td class="fibody5"><? echo $fdata{'oh_complications'}; ?></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="top">
          <td colspan="2" class="fibody2"><? echo (($fdata{'oh_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
            diabetes 
              <? echo (($fdata{'oh_hipertension'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
              hypertension/high blood pressure 
              <? echo (($fdata{'oh_preemclampsia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
              preeclampsia/foxemia 
              <? echo (($fdata{'oh_complic_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
              other </td>
        </tr>
        <tr align="left" valign="top">
          <td width="472"   class="fibody2">any history of depression before or after pregnancy? 
            <? echo (($fdata{'oh_depression'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
            no 
            <? echo (($fdata{'oh_depression'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
            yes, How treated </td>
          <td class="fibody5"><? echo $fdata{'oh_depression_treated'}; ?>&nbsp;</td>
        </tr>
      </table></td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="ph"></a>Past history (PH)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td class="fibody2"><? echo (($fdata{'ph_noncontrib'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        Noncontributory 
        <? echo (($fdata{'ph_nochange_since'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        no interval change since 
        <? echo $fdata{'ph_nochange_since_date'}; ?></td>
    </tr>
    <tr>
      <td class="fibody5"><strong>Surgeries:</strong>
        <? echo $fdata{'ph_surgeries'}; ?></td>
    </tr>
    <tr>
      <td class="fibody5"><strong>Illnesses (Physical and mental):</strong>
        <? echo $fdata{'ph_illnesses'}; ?></td>
    </tr>
    <tr>
      <td class="fibody5"><strong>Injuries:</strong>
        <? echo $fdata{'ph_injuries'}; ?></td>
    </tr>
    <tr>
      <td class="fibody5"><strong>Immunizations/Tuberculosis test: </strong>
        <? echo $fdata{'ph_immunizations_tuberculosis'}; ?></td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="fh"></a>Family history (FH) </h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr align="left" valign="top">
      <td colspan="3" class="fibody2"><? echo (($fdata{'fh_noncontrib'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Noncontributory
  <? echo (($fdata{'fh_nochange_since'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
no interval change since
<? echo $fdata{'fh_nochange_since_date'}; ?></td>
    </tr>
    <tr align="left" valign="top">
      <td colspan="3" class="fibody2">Mother:
        <? echo (($fdata{'fh_mother'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> living':'<img src="../../pic/mrkcheck.png" width="12" height="11"> deceased'); ?>  
        <? if ($fdata{'fh_mother'} != '0') { echo (($fdata{'fh_mother'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        cause: 
        <? echo $fdata{'fh_mother_dec_cause'}; ?>
        , age: 
        <? echo $fdata{'fh_mother_dec_age'}; }?>
&nbsp;&nbsp;&nbsp;        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Father:
        <? echo (($fdata{'fh_father'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> Living':'<img src="../../pic/mrkcheck.png" width="12" height="11"> Deceased'); ?>
<? if ($fdata{'fh_father'} != '0') {  ?>
deceased &mdash;  cause:
<? echo $fdata{'fh_father_dec_cause'}; ?>
, age:
<? echo $fdata{'fh_father_dec_age'}; }?></td>
    </tr>
    <tr align="left" valign="top">
      <td colspan="3" class="fibody2">Siblings: number living:
        <? echo $fdata{'fh_sibl_living'}; ?> 
        &nbsp;&nbsp;Number deceased: 
        <? echo $fdata{'fh_sibl_deceased'}; ?>
        &nbsp;&nbsp;Cause(s) / Age(s) :<br>
        <? echo $fdata{'fh_sibl_cause'}; ?></td>
    </tr>
    <tr align="left" valign="top">
      <td colspan="3" class="fibody2">Children: number living:
        <? echo $fdata{'fh_children_living'}; ?>
&nbsp;&nbsp;Number deceased:
<? echo $fdata{'fh_children_deceased'}; ?>
&nbsp;&nbsp;Cause(s) / Age(s) :<br>
<? echo $fdata{'fh_children_cause'}; ?></td>
    </tr>
    <tr align="left" valign="top">
      <td colspan="3" class="fibody2" style="border: none">(IF YES, indicate whom, and age of diagnosis) </td>
    </tr>
    <tr align="left" valign="top">
      <td width="33%" class="fibody2" id="bordR"><? echo (($fdata{'fhd_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        diabetes 
        <? echo $fdata{'fhd_diabetes_who'}; ?></td>
      <td width="33%" class="fibody2" id="bordR"><? echo (($fdata{'fhd_heart'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        heart disease 
        <? echo $fdata{'fhd_heart_who'}; ?></td>
      <td width="33%" class="fibody2"> <? echo (($fdata{'fhd_hyperlipidemia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        hyperlipidemia 
        <? echo $fdata{'fhd_hyperlipidemia_who'}; ?></td>
    </tr>
    <tr align="left" valign="top">
      <td class="fibody2" id="bordR"><? echo (($fdata{'fhd_cancer'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        cancer
        <? echo $fdata{'fhd_cancer_who'}; ?></td>
      <td class="fibody2" id="bordR"><? echo (($fdata{'fhd_hipertension'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        Hypertension 
        <? echo $fdata{'fhd_hipertension_who'}; ?></td>
      <td rowspan="2" valign="middle" class="fibody2"><? echo (($fdata{'fhd_deepvenous'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        deep venous tromboembolIsm / Pulmonary embolism 
        <? echo $fdata{'fhd_deepvenous_who'}; ?></td>
    </tr>
    <tr align="left" valign="top">
      <td class="fibody2" id="bordR"><? echo (($fdata{'fhd_osteoporosis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        osteoporosis 
        <? echo $fdata{'fhd_osteoporosis_who'}; ?></td>
      <td class="fibody2" id="bordR"><? echo (($fdata{'fhd_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        other illnesses 
        <? echo $fdata{'fhd_other_who'}; ?></td>
      </tr>
  </table>
</div>
<p></p>
<p>&nbsp;</p>
<h2 align="center"><a name="sh"></a>Social history (SH) </h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="left" valign="top" class="fibody2"> <? echo (($fdata{'sh_noncontrib'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;
Noncontributory
<? echo (($fdata{'sh_nochange_since'} == 'checkbox')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;
no interval change since
<? echo $fdata{'sh_nochange_since_date'}; ?>&nbsp; </td>
    </tr>
    <tr>
      <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="center" valign="top">
          <td width="190" class="ficaption2">&nbsp;</td>
          <td width="30" class="ficaption2">yes</td>
          <td width="30" class="ficaption2" id="bordR">no</td>
          <td class="ficaption2" id="bordR">notes</td>
          <td width="190" class="ficaption2">&nbsp;</td>
          <td width="30" class="ficaption2">yes</td>
          <td width="30" class="ficaption2" id="bordR">no</td>
          <td class="ficaption2">notes</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">Tobacco use </td>
          <td class="fibody2"><? echo (($fdata{'sh_tobacco'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_tobacco'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_1'}; ?>&nbsp;</td>
          <td   class="fibody2">diet discussed </td>
          <td class="fibody2"><? echo (($fdata{'sh_diet'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_diet'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2">
            <? echo $fdata{'sh_notes_9'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td rowspan="2"   class="fibody2">Alcohol use<br>
            specify amount and type<br>
            <small style="font-size:6pt;">12 OZ beer = 5 oz wine = 1 1/2 oz liquor</small> </td>
          <td rowspan="2" class="fibody2"><? echo (($fdata{'sh_alcohol'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td rowspan="2" class="fibody2" id="bordR"><? echo (($fdata{'sh_alcohol'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td rowspan="2" class="fibody2" id="bordR"><? echo $fdata{'sh_notes_2'}; ?>&nbsp;</td>
          <td   class="fibody2">folic acid intake </td>
          <td class="fibody2"><? echo (($fdata{'sh_folic_acid'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_folic_acid'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_10'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">calcium intake </td>
          <td class="fibody2"><? echo (($fdata{'sh_calcium'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_calcium'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_11'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">Illegal/Street drug use </td>
          <td class="fibody2"><? echo (($fdata{'sh_drugs'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_drugs'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_3'}; ?>&nbsp;</td>
          <td   class="fibody2">regular exercise </td>
          <td class="fibody2"><? echo (($fdata{'sh_reg_exercise'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_reg_exercise'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_12'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">misuse of prescription drugs </td>
          <td class="fibody2"><? echo (($fdata{'sh_misuse'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_misuse'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_4'}; ?>&nbsp;</td>
          <td   class="fibody2">caffeine intake </td>
          <td class="fibody2"><? echo (($fdata{'sh_caffeine'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_caffeine'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_13'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">intimate partner violence </td>
          <td class="fibody2"><? echo (($fdata{'sh_partner_violence'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_partner_violence'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_5'}; ?>&nbsp;</td>
          <td   class="fibody2">advance directive (living will) </td>
          <td class="fibody2"><? echo (($fdata{'sh_advance'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_advance'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_14'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">sexual abuse </td>
          <td class="fibody2"><? echo (($fdata{'sh_sexual_abuse'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_sexual_abuse'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_6'}; ?>&nbsp;</td>
          <td   class="fibody2">organ donation </td>
          <td class="fibody2"><? echo (($fdata{'sh_organ_donation'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_organ_donation'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_15'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">health hazards at home/work </td>
          <td class="fibody2"><? echo (($fdata{'sh_health_hazards'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_health_hazards'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_7'}; ?>&nbsp;</td>
          <td   class="fibody2">other</td>
          <td class="fibody2"><? echo (($fdata{'sh_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_other'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2"><? echo $fdata{'sh_notes_16'}; ?>&nbsp;</td>
        </tr>
        <tr align="left" valign="top">
          <td   class="fibody2">seat belt use </td>
          <td class="fibody2"><? echo (($fdata{'sh_seat_belt'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo (($fdata{'sh_seat_belt'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;</td>
          <td class="fibody2" id="bordR"><? echo $fdata{'sh_notes_8'}; ?>&nbsp;</td>
          <td   class="fibody2"><? echo (($fdata{'sh_nochanges_since2'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>&nbsp;
            no changes since              <? echo $fdata{'sh_nochanges_since2_date'}; ?>&nbsp; </td>
          <td class="fibody2">&nbsp;</td>
          <td class="fibody2">&nbsp;</td>
          <td class="fibody2">&nbsp;</td>
        </tr>
      </table></td>
    </tr>
  </table>
</div>
<p>&nbsp;  </p>
<h2 align="center"><a name="ros"></a>Review of systems (ROS)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
<table width="100%"  border="0" cellspacing="0" cellpadding="2" class="fitable">
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">1. Constitutional </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_const_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_const_weight_loss'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          weight loss</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_const_weight_gain'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          weight gain
</td>
        <td>&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_const_fever'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          fever</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_const_fatigue'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          fatigue</td>
        <td nowrap><? echo (($fdata{'ros_const_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          other</td>
        <td align="right" nowrap>tallest height&nbsp; </td>
        <td><? echo $fdata{'ros_const_tallest_height'}; ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">2. Eyes </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_eyes_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_eyes_vision_change'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Vision change </td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_eyes_glasses'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Glasses/contacts</td>
        </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_eyes_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Other</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td align="right" nowrap>&nbsp; </td>
        <td width="20%">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">3. Ear, nose and throat </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_ear_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_ear_ulcers'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Ulcers</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_ear_sinusitis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      sinusitis</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_ear_headache'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Headache</td>
        <td nowrap><? echo (($fdata{'ros_ear_hearing_loss'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hearing loss </td>
        <td nowrap><? echo (($fdata{'ros_ear_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      other</td>
        <td width="20%" align="right" nowrap>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">4. Cardiovascular </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_cv_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_cv_orthopnea'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Orthopnea</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_cv_chest_pain'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Chest pain </td>
        <td colspan="2" rowspan="2"><? echo (($fdata{'ros_cv_difficulty_breathing'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Difficulty breathing on exertion
</td>
        </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_cv_edema'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Edema</td>
        <td nowrap><? echo (($fdata{'ros_cv_palpitation'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Palpitation</td>
        <td nowrap><? echo (($fdata{'ros_cv_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      other</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">5. Respiratory </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_resp_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_resp_wheezing'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Wheezing</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_hemoptysis'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hemoptysis</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><? echo (($fdata{'ros_resp_shortness'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Shortness of breath </td>
        <td nowrap><? echo (($fdata{'ros_resp_cough'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Cough</td>
        <td colspan="2" align="left" nowrap><? echo (($fdata{'ros_resp_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Other
</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">6. Gastrointestinal </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_gastr_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_gastr_diarrhea'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Diarrhea</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_gastr_bloody_stool'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Bloody stool </td>
        <td colspan="2"><? echo (($fdata{'ros_gastr_nausea'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Nausea/Vomiting/Indigestion
</td>
        </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_gastr_constipation'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Constipation</td>
        <td nowrap><? echo (($fdata{'ros_gastr_flatulence'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Flatulence</td>
        <td nowrap><? echo (($fdata{'ros_gastr_pain'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      pain</td>
        <td align="left" nowrap><? echo (($fdata{'ros_gastr_fecal'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Fecal incontinence</td>
        <td nowrap><? echo (($fdata{'ros_gastr_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Other</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">7. Genitourinary </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_genit_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_genit_hematuria'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hematuria</td>
        <td nowrap><? echo (($fdata{'ros_genit_dysuria'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Dysuria</td>
        <td align="left" nowrap><? echo (($fdata{'ros_genit_urgency'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Urgency</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_genit_frequency'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Frequency</td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_genit_incomplete_emptying'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Incomplete emptying </td>
        <td align="left" nowrap><? echo (($fdata{'ros_genit_incontinence'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Incontinence</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_genit_dyspareunia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Dyspareunia</td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_genit_abnormal_periods'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
          Abnormal or painful periods </td>
        <td nowrap><? echo (($fdata{'ros_genit_pms'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  PMS</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><? echo (($fdata{'ros_genit_abnormal_bleeding'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Abnormal vaginal bleeding </td>
        <td nowrap><? echo (($fdata{'ros_genit_abnormal_discharge'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Abnormal vaginal discharge </td>
        <td nowrap><? echo (($fdata{'ros_genit_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  other</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">8. Musculoskeletal </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="2">
      <tr align="left" valign="baseline">
        <td width="40%" nowrap><? echo (($fdata{'ros_muscul_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="40%" nowrap><? echo (($fdata{'ros_muscul_weakness'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Muscle weakness </td>
        <td nowrap>&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_muscul_pain'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Muscle or joint pain </td>
        <td width="40%" nowrap><? echo (($fdata{'ros_muscul_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
other</td>
        <td nowrap>&nbsp;</td>
        <td width="10%" align="left" nowrap>&nbsp;</td>
        <td width="10%">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">9a. Skin </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_skin_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_skin_rash'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Rash</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_skin_ulcers'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Ulcers</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_skin_dry'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Dry skin </td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_skin_pigmented'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Pigmented lesions </td>
        <td align="left" nowrap><? echo (($fdata{'ros_skin_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
other</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">9b. Breast </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_breast_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_breast_mastalgia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Mastalgia</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_breast_discharge'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Discharge</td>
        <td nowrap><? echo (($fdata{'ros_breast_masses'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Masses</td>
        <td nowrap><? echo (($fdata{'ros_breast_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      other</td>
        <td align="right" nowrap>&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">10. Neurologic </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="2">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_neuro_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_neuro_syncope'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Syncope</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_neuro_seizures'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Seizures</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_neuro_numbness'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Numbness</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><? echo (($fdata{'ros_neuro_trouble_walking'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Trouble walking </td>
        <td colspan="2" nowrap><? echo (($fdata{'ros_neuro_memory'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Severe memory problems </td>
        <td><? echo (($fdata{'ros_neuro_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
other</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">11. Psychiatric</td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_psych_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_psych_depression'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Depression</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_psych_crying'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Crying</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><? echo (($fdata{'ros_psych_anxiety'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Severe anxiety </td>
        <td width="20%" nowrap><? echo (($fdata{'ros_psych_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Other</td>
        <td align="right" nowrap>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">12. Endocrine </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_endo_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_endo_diabetes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
        Diabetes</td>
        <td nowrap><? echo (($fdata{'ros_endo_hipothyroid'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hypothyroid</td>
        <td nowrap><? echo (($fdata{'ros_endo_hiperthyroid'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Hyperthyroid</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_endo_flashes'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hot flashes </td>
        <td nowrap><? echo (($fdata{'ros_endo_hair_loss'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Hair loss </td>
        <td nowrap><? echo (($fdata{'ros_endo_intolerance'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Heat/cold intolerance </td>
        <td><? echo (($fdata{'ros_endo_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
          Other
</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">13. Hematologic/Lymphatic</td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><? echo (($fdata{'ros_hemato_negative'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Negative</td>
        <td width="20%" nowrap><? echo (($fdata{'ros_hemato_bruises'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Bruises</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><? echo (($fdata{'ros_hemato_bleeding'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Bleeding</td>
        <td nowrap><? echo (($fdata{'ros_hemato_adenopathy'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Adenopathy</td>
        <td nowrap><? echo (($fdata{'ros_hemato_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      other</td>
        <td align="right" nowrap>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">14. Allergic/Immunologic </td>
    <td align="center" valign="middle" class="fibody"><a href="#allergies">See above (Page 1 of PH) </a></td>
  </tr>
</table>
</div>
</body>
</html>