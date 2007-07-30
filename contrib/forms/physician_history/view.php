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
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" src="../../acogros.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
window.onload = initialize;
</script>
</head>

<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   $fres=sqlStatement("select * from form_physician_history where id=$id");
   if ($fres){ $fdata = sqlFetchArray($fres); }
?>
<body <?echo $top_bg_line;?>>

<form action="<?echo $rootdir;?>/forms/physician_history/save.php?mode=update&id=<? echo $id; ?>" method="post" enctype="multipart/form-data" name="my_form">
<?
$addmenu = <<<EOL
<blockquote>
<small><strong>Local sections:</strong><br>
<a href="#gh">Gynecologic history</a> | <a href="#oh">Obstetric history</a> | 
<a href="#ph">Past history</a> | <a href="#fh">Family history</a> | 
<a href="#sh">Social history</a> | <a href="#ros">Review of systems</a>
</small>
</blockquote>
EOL;
?> 
<? include("../../acog_menu.inc"); ?>
<table width="70%"  border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Patient name:</td>
    <td align="left" valign="bottom"><input name="pname" type="text" class="fullin" id="pname" value="<?
          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};
          ?>"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Birth date: </td>
    <td align="left" valign="bottom"><input name="pbdate" type="text" class="fullin" id="pbdate" value="<?
          echo $patient{'DOB'};
          ?>"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">ID No:</td>
    <td align="left" valign="bottom"><input name="ph_pid" type="text" class="fullin" id="ph_pid" value="<?
          echo $patient{'id'};
          ?>"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Date</td>
    <td align="left" valign="bottom"><input name="ph_date" type="text" class="fullin" id="ph_date" value="<?
        echo date('Y-m-d');
        ?>"></td>
  </tr>
</table>
<?
$tip1 = <<<EOL
The <strong>Physician History</strong> can be used record the history for every type of outpatient encounter, including consultations. A new Physician History should be completed by the physician at each visit when clinically indicated.
EOL;
?>
<div class="srvChapter">Physician history <a href="#" onMouseOver="toolTip('<? echo $tip1; ?>', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="baseline">
        <td width="25%" class="fibody2" id="bordR"> <input name="established" type="radio" value="0"  <? echo (($fdata{'established'} == '0')?' checked ':''); ?>>
          New patient </td>
        <td width="25%" class="fibody2" id="bordR"> <input name="established" type="radio" value="1" <? echo (($fdata{'established'} == '1')?' checked ':''); ?>>
          Established patient </td>
        <td width="20%" nowrap class="fibody2" id="bordR"><input name="consultation" type="checkbox"  value="1" <? echo (($fdata{'consultation'} == '1')?' checked ':''); ?>>
          Consultation</td>
        <td width="30%" valign="bottom" class="fibody2"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="60%" align="left" valign="bottom"><input name="report_sent" type="checkbox"  value="1" <? echo (($fdata{'report_sent'} == '1')?' checked ':''); ?>>
Report sent</td>
            <td width="40%" align="left" valign="bottom"><input name="report_sent_date" type="text" class="fullin2"   size="12" value="<? echo $fdata{'report_sent_date'}; ?>"></td>
          </tr>
        </table> </td>
        </tr>
      <tr align="left" valign="bottom">
        <td colspan="2" class="fibody2" id="bordR">Primary care physician:<br> 
          <input name="primary_care" type="text" class="fullin2"  value="<? echo $fdata{'primary_care'}; ?>"></td>
        <td colspan="2" class="fibody2">Who sent patient:<br>
          <input name="who_sent" type="text" class="fullin2"  value="<? echo $fdata{'who_sent'}; ?>"></td>
      </tr>
      <tr align="left" valign="bottom">
        <td colspan="2" class="fibody2" id="bordR"> Other physician(s):<br> <input name="other_physician" type="text" class="fullin2"  value="<? echo $fdata{'other_physician'}; ?>"> </td>
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
        <td width="50%" nowrap class="fibody2" id="bordR"><textarea name="chief_complaint" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'chief_complaint'}; ?></textarea></td>
        <td width="50%" nowrap class="fibody2"><textarea name="current_prescription" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'current_prescription'}; ?></textarea></td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="50%" valign="bottom" class="ficaption2" id="bordR">History of present ilness (HPI): <br> </td>
        <td width="50%" valign="bottom" class="ficaption2">Current nonpresription, complem., and alt. medications:          </td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="50%" valign="bottom" nowrap class="fibody2" id="bordR"><textarea name="hpi" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'hpi'}; ?></textarea></td>
        <td width="50%" valign="bottom" nowrap class="fibody2"><textarea name="current_nonprescription" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'current_nonprescription'}; ?></textarea></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="fibody2">&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="bottom">
        <td width="200" class="ficaption2">Changes since last visit </td>
        <td width="40" align="center" class="ficaption2">yes</td>
        <td width="40" align="center" class="ficaption2" id="bordR">no</td>
        <td colspan="2" align="center" class="ficaption2">Notes</td>
        </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">Illnesses</td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_ill" type="radio" value="1" <? echo (($fdata{'ph_lvch_ill'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_ill" type="radio" value="0"  <? echo (($fdata{'ph_lvch_ill'} == '0')?' checked ':''); ?>></td>
        <td colspan="2" rowspan="7" valign="top" class="fibody2"><textarea name="ph_lvch_notes" rows="7" wrap="VIRTUAL" class="fullin2"  style="height: 100%"><? echo $fdata{'ph_lvch_notes'}; ?></textarea></td>
        </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">Surgery</td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_surg" type="radio" value="1" <? echo (($fdata{'ph_lvch_surg'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_surg" type="radio" value="0"  <? echo (($fdata{'ph_lvch_surg'} == '0')?' checked ':''); ?>></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">New medications </td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_newmed" type="radio" value="1" <? echo (($fdata{'ph_lvch_newmed'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_newmed" type="radio" value="0"  <? echo (($fdata{'ph_lvch_newmed'} == '0')?' checked ':''); ?>></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">Change in family history </td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_famhist" type="radio" value="1" <? echo (($fdata{'ph_lvch_famhist'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_famhist" type="radio" value="0"  <? echo (($fdata{'ph_lvch_famhist'} == '0')?' checked ':''); ?>></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">New allergies </td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_newallerg" type="radio" value="1" <? echo (($fdata{'ph_lvch_newallerg'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_newallerg" type="radio" value="0"  <? echo (($fdata{'ph_lvch_newallerg'} == '0')?' checked ':''); ?>></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">Change in gynecologic history </td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_gynhist" type="radio" value="1" <? echo (($fdata{'ph_lvch_gynhist'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_gynhist" type="radio" value="0"  <? echo (($fdata{'ph_lvch_gynhist'} == '0')?' checked ':''); ?>></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="200" class="fibody2">Change in obstetric history </td>
        <td width="40" align="center" class="fibody2"><input name="ph_lvch_obsthist" type="radio" value="1" <? echo (($fdata{'ph_lvch_obsthist'} == '1')?' checked ':''); ?>></td>
        <td width="40" align="center" class="fibody2" id="bordR"><input name="ph_lvch_obsthist" type="radio" value="0"  <? echo (($fdata{'ph_lvch_obsthist'} == '0')?' checked ':''); ?>></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="fibody2">&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="bottom">
        <td width="225" class="fibody2"><a name="allergies"></a>Allergies (describe reaction):
          <input name="ph_allergies_none" type="checkbox"  value="1"  <? echo (($fdata{'ph_allergies_none'} == '1')?' checked ':''); ?>>
none</td>
        <td class="fibody2"><input name="ph_allergies_data" type="text" class="fullin2"  value="<? echo $fdata{'ph_allergies_data'}; ?>"></td>
      </tr>
    </table></td>
    </tr>
  <tr>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
      <tr align="left" valign="bottom">
        <td width="70%" class="fibody2" id="bordR">Last cervical cancer screening: 
          <input name="cancer_scr_cytology" type="checkbox"  value="1" <? echo (($fdata{'cancer_scr_cytology'} == '1')?' checked ':''); ?>>
          Cytology
          <input name="cancer_scr_cytology_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'cancer_scr_cytology_date'}; ?>">
          <input name="cancer_scr_hpv" type="checkbox"  value="checkbox" <? echo (($fdata{'cancer_scr_hpv'} == 'checkbox')?' checked ':''); ?>>
          HPV test 
          <input name="cancer_scr_hpv_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'cancer_scr_hpv_date'}; ?>"></td>
        <td width="30%" class="fibody2"><input name="cancer_scr_notes" type="text" class="fullin2"  value="<? echo $fdata{'cancer_scr_notes'}; ?>"></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="70%" class="fibody2" id="bordR">last mammogram: 
          <input name="last_mammogram" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'last_mammogram'}; ?>"></td>
        <td class="fibody2"><input name="last_mammogram_notes" type="text" class="fullin2"  value="<? echo $fdata{'last_mammogram_notes'}; ?>"></td>
      </tr>
      <tr align="left" valign="bottom">
        <td width="70%" class="fibody2" id="bordR">Last colorectal screening: 
          <input name="last_colorectal" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'last_colorectal'}; ?>"></td>
        <td class="fibody2"><input name="last_colorectal_notes" type="text" class="fullin2"  value="<? echo $fdata{'last_colorectal_notes'}; ?>"></td>
      </tr>
    </table></td>
  </tr>
</table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="gh"></a>Gynecologic history (PH)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr align="left" valign="bottom">
      <td colspan="4"><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">Imp</td>
          <td nowrap class="fibody2"><input name="gh_imp" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'gh_imp'}; ?>"></td>
          <td nowrap class="fibody2">Age at menarche </td>
          <td nowrap class="fibody2"><input name="gh_age_at_menarche" type="text" class="fullin"  value="<? echo $fdata{'gh_age_at_menarche'}; ?>"></td>
          <td nowrap class="fibody2">Length of flow </td>
          <td nowrap class="fibody2"><input name="gh_length_of_flow" type="text" class="fullin"  value="<? echo $fdata{'gh_length_of_flow'}; ?>"></td>
          <td nowrap class="fibody2">Interval between periods </td>
          <td nowrap class="fibody2"><input name="gh_interval_periods" type="text" class="fullin"  value="<? echo $fdata{'gh_interval_periods'}; ?>"></td>
          <td nowrap class="fibody2">Recent changes </td>
          <td nowrap class="fibody2"><input name="gh_recent_changes" type="text" class="fullin"  value="<? echo $fdata{'gh_recent_changes'}; ?>"></td>
        </tr>
      </table></td>
    </tr>
    <tr align="left" valign="bottom">
      <td colspan="4" nowrap class="fibody2">Sexually active: 
        <input name="gh_sexually_active" type="radio" value="1" <? echo (($fdata{'gh_sexually_active'} == '1')?' checked ':''); ?>>
        Yes
        <input name="gh_sexually_active" type="radio" value="0" <? echo (($fdata{'gh_sexually_active'} == '0')?' checked ':''); ?>>
        No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ever had sex: 
        <input name="gh_had_sex" type="radio" value="1" <? echo (($fdata{'gh_had_sex'} == '1')?' checked ':''); ?>>
Yes
<input name="gh_had_sex" type="radio" value="0" <? echo (($fdata{'gh_had_sex'} == '0')?' checked ':''); ?>>
No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Number of partners (Lifetime) 
<input name="gh_partners" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'gh_partners'}; ?>"></td>
      </tr>
    <tr align="left" valign="bottom">
      <td colspan="4" nowrap class="fibody2">Partners are: 
        <input name="gh_partners_are" type="radio" value="men" <? echo (($fdata{'gh_partners_are'} == 'men')?' checked ':''); ?>>
        men 
        <input name="gh_partners_are" type="radio" value="women" <? echo (($fdata{'gh_partners_are'} == 'women')?' checked ':''); ?>>
        women
        <input name="ph_gh_partners_are" type="radio" value="both" <? echo (($fdata{'ph_gh_partners_are'} == 'both')?' checked ':''); ?>>
        both</td>
      </tr>
    <tr align="left" valign="bottom">
      <td width="202" nowrap class="fibody2">Current method of contraception: </td>
      <td width="30%" nowrap class="fibody2"><input name="gh_method_contraception" type="text" class="fullin2"  value="<? echo $fdata{'gh_method_contraception'}; ?>"></td>
      <td width="161" nowrap class="fibody2">past contraceptive history:</td>
      <td width="34%" nowrap class="fibody2"><input name="gh_contraceptive_history" type="text" class="fullin2"  value="<? echo $fdata{'gh_contraceptive_history'}; ?>"></td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="oh"></a>Obstetric history (PH)</h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
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
          <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_pregnancies" type="text" class="fullin2"  value="<? echo $fdata{'oh_pregnancies'}; ?>"></td>
          <td width="30%" nowrap class="fibody2" id="bordR">abortions</td>
          <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_abortions" type="text" class="fullin2"  value="<? echo $fdata{'oh_abortions'}; ?>"></td>
          <td width="30%" nowrap class="fibody2" id="bordR">miscarriages</td>
          <td width="50" nowrap class="fibody2"><input name="oh_miscarriages" type="text" class="fullin2"  value="<? echo $fdata{'oh_miscarriages'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td width="30%" nowrap class="fibody2" id="bordR">premature births(&lt;37 weeks) </td>
          <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_premature_births" type="text" class="fullin2" value="<? echo $fdata{'oh_premature_births'}; ?>"></td>
          <td width="30%" nowrap class="fibody2" id="bordR">live births </td>
          <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_live_births" type="text" class="fullin2" value="<? echo $fdata{'oh_live_births'}; ?>"></td>
          <td width="30%" nowrap class="fibody2" id="bordR">living children </td>
          <td width="50" nowrap class="fibody2"><input name="oh_living_children" type="text" class="fullin2" value="<? echo $fdata{'oh_living_children'}; ?>"></td>
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
          <td nowrap class="fibody2" id="bordR"><input name="oh_ch_date_${bi}" type="text" value="${oh_ch_date}" class="fullin2"></td>
          <td nowrap class="fibody2" id="bordR"><input name="oh_ch_width_${bi}" type="text" value="${oh_ch_width}"  class="fullin2"></td>
          <td nowrap class="fibody2" id="bordR"><input name="oh_ch_sex_${bi}" type="text" value="${oh_ch_sex}"  class="fullin2"></td>
          <td nowrap class="fibody2" id="bordR"><input name="oh_ch_weeks_${bi}" type="text" value="${oh_ch_weeks}"  class="fullin2"></td>
          <td nowrap class="fibody2" id="bordR"><input name="oh_ch_delivery_${bi}" type="text" value="${oh_ch_delivery}"  class="fullin2"></td>
          <td nowrap class="fibody2"><input name="oh_ch_notes_${bi}" type="text" value="${oh_ch_notes}"  class="fullin2"></td>
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
          <td class="fibody2"><input name="oh_complications" type="text" class="fullin2"  value="<? echo $fdata{'oh_complications'}; ?>"></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="left" valign="bottom">
          <td colspan="2" class="fibody2"><input name="oh_diabetes" type="checkbox"  value="1" <? echo (($fdata{'oh_diabetes'} == '1')?' checked ':''); ?>>
            diabetes 
              <input name="oh_hipertension" type="checkbox"  value="1" <? echo (($fdata{'oh_hipertension'} == '1')?' checked ':''); ?>>
              hypertension/high blood pressure 
              <input name="oh_preemclampsia" type="checkbox"  value="1" <? echo (($fdata{'oh_preemclampsia'} == '1')?' checked ':''); ?>>
              preeclampsia/foxemia 
              <input name="oh_complic_other" type="checkbox"  value="1" <? echo (($fdata{'oh_complic_other'} == '1')?' checked ':''); ?>>
              other </td>
        </tr>
        <tr align="left" valign="bottom">
          <td width="472" nowrap class="fibody2">any history of depression before or after pregnancy? 
            <input name="oh_depression" type="radio" value="0"  <? echo (($fdata{'oh_depression'} == '0')?' checked ':''); ?>>
            no 
            <input name="oh_depression" type="radio" value="1" <? echo (($fdata{'oh_depression'} == '1')?' checked ':''); ?>>
            yes, How treated </td>
          <td class="fibody2"><input name="oh_depression_treated" type="text" class="fullin2"  value="<? echo $fdata{'oh_depression_treated'}; ?>"></td>
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
      <td class="fibody2"><input name="ph_noncontrib" type="checkbox"  value="1" <? echo (($fdata{'ph_noncontrib'} == '1')?' checked ':''); ?>>
        Noncontributory 
        <input name="ph_nochange_since" type="checkbox"  value="1" <? echo (($fdata{'ph_nochange_since'} == '1')?' checked ':''); ?>>
        no interval change since 
        <input name="ph_nochange_since_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'ph_nochange_since_date'}; ?>"></td>
    </tr>
    <tr>
      <td class="fibody2">SUrgeries:<br>
        <textarea name="ph_surgeries" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'ph_surgeries'}; ?></textarea></td>
    </tr>
    <tr>
      <td class="fibody2">Illnesses (Physical and mental):<br>
        <textarea name="ph_illnesses" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'ph_illnesses'}; ?></textarea></td>
    </tr>
    <tr>
      <td class="fibody2">Injuries:<br>
        <textarea name="ph_injuries" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'ph_injuries'}; ?></textarea></td>
    </tr>
    <tr>
      <td class="fibody2">Immunizations/Tuberculosis test: <br>
        <textarea name="ph_immunizations_tuberculosis" rows="3" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'ph_immunizations_tuberculosis'}; ?></textarea></td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<h2 align="center"><a name="fh"></a>Family history (FH) </h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr align="left" valign="bottom">
      <td colspan="3" class="fibody2"><input name="fh_noncontrib" type="checkbox"  value="checkbox" <? echo (($fdata{'fh_noncontrib'} == 'checkbox')?' checked ':''); ?>>
Noncontributory
  <input name="fh_nochange_since" type="checkbox"  value="checkbox" <? echo (($fdata{'fh_nochange_since'} == 'checkbox')?' checked ':''); ?>>
no interval change since
<input name="fh_nochange_since_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'fh_nochange_since_date'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td colspan="3" class="fibody2">Mother:
        <input name="fh_mother" type="radio" value="0"  <? echo (($fdata{'fh_mother'} == '0')?' checked ':''); ?>> 
        living 
        <input name="fh_mother" type="radio" value="1" <? echo (($fdata{'fh_mother'} == '1')?' checked ':''); ?>>
        deceased - cause: 
        <input name="fh_mother_dec_cause" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_mother_dec_cause'}; ?>">
        age: 
        <input name="fh_mother_dec_age" type="text" class="fullin"  style="width: 30px" value="<? echo $fdata{'fh_mother_dec_age'}; ?>">
&nbsp;&nbsp;&nbsp;        Father:
        <input name="fh_father" type="radio" value="0"  <? echo (($fdata{'fh_father'} == '0')?' checked ':''); ?>>
living
<input name="fh_father" type="radio" value="1" <? echo (($fdata{'fh_father'} == '1')?' checked ':''); ?>>
deceased - cause:
<input name="fh_father_dec_cause" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_father_dec_cause'}; ?>">
age:
<input name="fh_father_dec_age" type="text" class="fullin"  style="width: 30px" value="<? echo $fdata{'fh_father_dec_age'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td colspan="3" class="fibody2">Siblings: number living:
        <input name="fh_sibl_living" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_sibl_living'}; ?>"> 
        Number deceased: 
        <input name="fh_sibl_deceased" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_sibl_deceased'}; ?>">
        cause(s) / Age(s) :<br>
        <input name="fh_sibl_cause" type="text" class="fullin2"  value="<? echo $fdata{'fh_sibl_cause'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td colspan="3" class="fibody2">Children: number living:
        <input name="fh_children_living" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_children_living'}; ?>">
Number deceased:
<input name="fh_children_deceased" type="text" class="fullin"  style="width: 7%" value="<? echo $fdata{'fh_children_deceased'}; ?>">
cause(s) / Age(s) :<br>
<input name="fh_children_cause" type="text" class="fullin2"  value="<? echo $fdata{'fh_children_cause'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td colspan="3" class="fibody2" style="border: none">(IF YES, indicate whom, and age of diagnosis) </td>
    </tr>
    <tr align="left" valign="bottom">
      <td width="33%" class="fibody2" id="bordR"><input name="fhd_diabetes" type="checkbox"  value="1" <? echo (($fdata{'fhd_diabetes'} == '1')?' checked ':''); ?>>
        diabetes 
        <input name="fhd_diabetes_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_diabetes'}; ?>"></td>
      <td width="33%" class="fibody2" id="bordR"><input name="fhd_heart" type="checkbox"  value="1" <? echo (($fdata{'fhd_heart'} == '1')?' checked ':''); ?>>
        heart disease 
        <input name="fhd_heart_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_heart_who'}; ?>"></td>
      <td width="33%" class="fibody2"> <input name="fhd_hyperlipidemia" type="checkbox"  value="1" <? echo (($fdata{'fhd_hyperlipidemia'} == '1')?' checked ':''); ?>>
        hyperlipidemia 
        <input name="fhd_hyperlipidemia_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_hyperlipidemia_who'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR"><input name="fhd_cancer" type="checkbox"  value="1" <? echo (($fdata{'fhd_cancer'} == '1')?' checked ':''); ?>>
        cancer
        <input name="fhd_cancer_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_cancer_who'}; ?>"></td>
      <td class="fibody2" id="bordR"><input name="fhd_hipertension" type="checkbox"  value="1" <? echo (($fdata{'fhd_hipertension'} == '1')?' checked ':''); ?>>
        Hypertension 
        <input name="fhd_hipertension_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_hipertension_who'}; ?>"></td>
      <td rowspan="2" valign="middle" class="fibody2"><input name="fhd_deepvenous" type="checkbox"  value="1" <? echo (($fdata{'fhd_deepvenous'} == '1')?' checked ':''); ?>>
        deep venous tromboembolIsm / Pulmonary embolism 
        <input name="fhd_deepvenous_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_deepvenous_who'}; ?>"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR"><input name="fhd_osteoporosis" type="checkbox"  value="1" <? echo (($fdata{'fhd_osteoporosis'} == '1')?' checked ':''); ?>>
        osteoporosis 
        <input name="fhd_osteoporosis_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_osteoporosis_who'}; ?>"></td>
      <td class="fibody2" id="bordR"><input name="fhd_other" type="checkbox"  value="1" <? echo (($fdata{'fhd_other'} == '1')?' checked ':''); ?>>
        other illnesses 
        <input name="fhd_other_who" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'fhd_other_who'}; ?>"></td>
      </tr>
  </table>
</div>
<p></p>
<p>&nbsp;</p>
<h2 align="center"><a name="sh"></a>Social history (SH) </h2>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="left" valign="bottom" class="fibody2"> <input name="sh_noncontrib" type="checkbox"  value="checkbox" <? echo (($fdata{'sh_noncontrib'} == 'checkbox')?' checked ':''); ?>>
Noncontributory
<input name="sh_nochange_since" type="checkbox"  value="checkbox" <? echo (($fdata{'sh_nochange_since'} == 'checkbox')?' checked ':''); ?>>
no interval change since
<input name="sh_nochange_since_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'sh_nochange_since_date'}; ?>"> </td>
    </tr>
    <tr>
      <td align="left" valign="bottom"><table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr align="center" valign="bottom">
          <td width="190" class="ficaption2">&nbsp;</td>
          <td width="30" class="ficaption2">yes</td>
          <td width="30" class="ficaption2" id="bordR">no</td>
          <td class="ficaption2" id="bordR">notes</td>
          <td width="190" class="ficaption2">&nbsp;</td>
          <td width="30" class="ficaption2">yes</td>
          <td width="30" class="ficaption2" id="bordR">no</td>
          <td class="ficaption2">notes</td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">Tobacco use </td>
          <td class="fibody2"><input name="sh_tobacco" type="radio" value="1" <? echo (($fdata{'sh_tobacco'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_tobacco" type="radio" value="0" <? echo (($fdata{'sh_tobacco'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_1" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_1'}; ?>"></td>
          <td nowrap class="fibody2">diet discussed </td>
          <td class="fibody2"><input name="sh_diet" type="radio" value="1" <? echo (($fdata{'sh_diet'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_diet" type="radio" value="0" <? echo (($fdata{'sh_diet'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2">
            <input name="sh_notes_9" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_9'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td rowspan="2" valign="middle" nowrap class="fibody2">Alcohol use<br>
            specify amount and type<br>
            <small>12 OZ beer = 5 oz wine = 1 1/2 oz liquor</small> </td>
          <td rowspan="2" valign="middle" class="fibody2"><input name="sh_alcohol" type="radio" value="1" <? echo (($fdata{'sh_alcohol'} == '1')?' checked ':''); ?>></td>
          <td rowspan="2" valign="middle" class="fibody2" id="bordR"><input name="sh_alcohol" type="radio" value="0" <? echo (($fdata{'sh_alcohol'} == '0')?' checked ':''); ?>></td>
          <td rowspan="2" valign="middle" class="fibody2" id="bordR"><input name="sh_notes_2" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_2'}; ?>"></td>
          <td nowrap class="fibody2">folic acid intake </td>
          <td class="fibody2"><input name="sh_folic_acid" type="radio" value="1" <? echo (($fdata{'sh_folic_acid'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_folic_acid" type="radio" value="0" <? echo (($fdata{'sh_folic_acid'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_10" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_10'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">calcium intake </td>
          <td class="fibody2"><input name="sh_calcium" type="radio" value="1" <? echo (($fdata{'sh_calcium'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_calcium" type="radio" value="0" <? echo (($fdata{'sh_calcium'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_11" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_11'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">Illegal/Street drug use </td>
          <td class="fibody2"><input name="sh_drugs" type="radio" value="1" <? echo (($fdata{'sh_drugs'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_drugs" type="radio" value="0" <? echo (($fdata{'sh_drugs'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_3" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_3'}; ?>"></td>
          <td nowrap class="fibody2">regular exercise </td>
          <td class="fibody2"><input name="sh_reg_exercise" type="radio" value="1" <? echo (($fdata{'sh_reg_exercise'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_reg_exercise" type="radio" value="0" <? echo (($fdata{'sh_reg_exercise'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_12" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_12'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">misuse of prescription drugs </td>
          <td class="fibody2"><input name="sh_misuse" type="radio" value="1" <? echo (($fdata{'sh_misuse'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_misuse" type="radio" value="0" <? echo (($fdata{'sh_misuse'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_4" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_4'}; ?>"></td>
          <td nowrap class="fibody2">caffeine intake </td>
          <td class="fibody2"><input name="sh_caffeine" type="radio" value="1" <? echo (($fdata{'sh_caffeine'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_caffeine" type="radio" value="0" <? echo (($fdata{'sh_caffeine'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_13" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_13'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">intimate partner violence </td>
          <td class="fibody2"><input name="sh_partner_violence" type="radio" value="1" <? echo (($fdata{'sh_partner_violence'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_partner_violence" type="radio" value="0" <? echo (($fdata{'sh_partner_violence'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_5" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_5'}; ?>"></td>
          <td nowrap class="fibody2">advance directive (living will) </td>
          <td class="fibody2"><input name="sh_advance" type="radio" value="1" <? echo (($fdata{'sh_advance'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_advance" type="radio" value="0" <? echo (($fdata{'sh_advance'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_14" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_14'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">sexual abuse </td>
          <td class="fibody2"><input name="sh_sexual_abuse" type="radio" value="1" <? echo (($fdata{'sh_sexual_abuse'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_sexual_abuse" type="radio" value="0" <? echo (($fdata{'sh_sexual_abuse'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_6" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_6'}; ?>"></td>
          <td nowrap class="fibody2">organ donation </td>
          <td class="fibody2"><input name="sh_organ_donation" type="radio" value="1" <? echo (($fdata{'sh_organ_donation'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_organ_donation" type="radio" value="0" <? echo (($fdata{'sh_organ_donation'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_15" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_15'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">health hazards at home/work </td>
          <td class="fibody2"><input name="sh_health_hazards" type="radio" value="1" <? echo (($fdata{'sh_health_hazards'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_health_hazards" type="radio" value="0" <? echo (($fdata{'sh_health_hazards'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_7" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_7'}; ?>"></td>
          <td nowrap class="fibody2">other</td>
          <td class="fibody2"><input name="sh_other" type="radio" value="1" <? echo (($fdata{'sh_other'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_other" type="radio" value="0" <? echo (($fdata{'sh_other'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2"><input name="sh_notes_16" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_16'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom">
          <td nowrap class="fibody2">seat belt use </td>
          <td class="fibody2"><input name="sh_seat_belt" type="radio" value="1" <? echo (($fdata{'sh_seat_belt'} == '1')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_seat_belt" type="radio" value="0" <? echo (($fdata{'sh_seat_belt'} == '0')?' checked ':''); ?>></td>
          <td class="fibody2" id="bordR"><input name="sh_notes_8" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_8'}; ?>"></td>
          <td nowrap class="fibody2"><input name="sh_nochanges_since2" type="checkbox"  value="1" <? echo (($fdata{'sh_nochanges_since2'} == '1')?' checked ':''); ?>>
            no changes since              <input name="sh_nochanges_since2_date" type="text" class="fullin2"  style="width: 70px"  value="<? echo $fdata{'sh_nochanges_since2_date'}; ?>"> </td>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_const_negative" value="1" <? echo (($fdata{'ros_const_negative'} == '1')?' checked ':''); ?>>
        Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_const_weight_loss" value="1" <? echo (($fdata{'ros_const_weight_loss'} == '1')?' checked ':''); ?>> 
          weight loss</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_const_weight_gain" value="1" <? echo (($fdata{'ros_const_weight_gain'} == '1')?' checked ':''); ?>> 
          weight gain
</td>
        <td>&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_const_fever" value="1" <? echo (($fdata{'ros_const_fever'} == '1')?' checked ':''); ?>> 
          fever</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_const_fatigue" value="1" <? echo (($fdata{'ros_const_fatigue'} == '1')?' checked ':''); ?>> 
          fatigue</td>
        <td nowrap><input type="checkbox" name="ros_const_other" value="1" <? echo (($fdata{'ros_const_other'} == '1')?' checked ':''); ?>> 
          other</td>
        <td align="right" nowrap>tallest height&nbsp; </td>
        <td><input name="ros_const_tallest_height" type="text" class="fullin" value="<? echo $fdata{'ros_const_tallest_height'}; ?>"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">2. Eyes </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_eyes_negative" value="1" <? echo (($fdata{'ros_eyes_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_eyes_vision_change" value="1" <? echo (($fdata{'ros_eyes_vision_change'} == '1')?' checked ':''); ?>>
      Vision change </td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_eyes_glasses" value="1" <? echo (($fdata{'ros_eyes_glasses'} == '1')?' checked ':''); ?>>
Glasses/contacts</td>
        </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_eyes_other" value="1" <? echo (($fdata{'ros_eyes_other'} == '1')?' checked ':''); ?>>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_ear_negative" value="1" <? echo (($fdata{'ros_ear_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_ear_ulcers" value="1" <? echo (($fdata{'ros_ear_ulcers'} == '1')?' checked ':''); ?>>
      Ulcers</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_ear_sinusitis" value="1" <? echo (($fdata{'ros_ear_sinusitis'} == '1')?' checked ':''); ?>>
      sinusitis</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_ear_headache" value="1" <? echo (($fdata{'ros_ear_headache'} == '1')?' checked ':''); ?>>
      Headache</td>
        <td nowrap><input type="checkbox" name="ros_ear_hearing_loss" value="1" <? echo (($fdata{'ros_ear_hearing_loss'} == '1')?' checked ':''); ?>>
      Hearing loss </td>
        <td nowrap><input type="checkbox" name="ros_ear_other" value="1" <? echo (($fdata{'ros_ear_other'} == '1')?' checked ':''); ?>>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_cv_negative" value="1" <? echo (($fdata{'ros_cv_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_cv_orthopnea" value="1" <? echo (($fdata{'ros_cv_orthopnea'} == '1')?' checked ':''); ?>>
      Orthopnea</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_cv_chest_pain" value="1" <? echo (($fdata{'ros_cv_chest_pain'} == '1')?' checked ':''); ?>>
      Chest pain </td>
        <td colspan="2" rowspan="2"><input type="checkbox" name="ros_cv_difficulty_breathing" value="1" <? echo (($fdata{'ros_cv_difficulty_breathing'} == '1')?' checked ':''); ?>> 
          Difficulty breathing on exertion
</td>
        </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_cv_edema" value="1" <? echo (($fdata{'ros_cv_edema'} == '1')?' checked ':''); ?>>
      Edema</td>
        <td nowrap><input type="checkbox" name="ros_cv_palpitation" value="1" <? echo (($fdata{'ros_cv_palpitation'} == '1')?' checked ':''); ?>>
      Palpitation</td>
        <td nowrap><input type="checkbox" name="ros_cv_other" value="1" <? echo (($fdata{'ros_cv_other'} == '1')?' checked ':''); ?>>
      other</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">5. Respiratory </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_resp_negative" value="1" <? echo (($fdata{'ros_resp_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_resp_wheezing" value="1" <? echo (($fdata{'ros_resp_wheezing'} == '1')?' checked ':''); ?>>
      Wheezing</td>
        <td width="20%" nowrap><input name="ros_resp_hemoptysis" type="checkbox"  value="1" <? echo (($fdata{'ros_resp_hemoptysis'} == '1')?' checked ':''); ?>>
      Hemoptysis</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><input type="checkbox" name="ros_resp_shortness" value="1" <? echo (($fdata{'ros_resp_shortness'} == '1')?' checked ':''); ?>>
      Shortness of breath </td>
        <td nowrap><input type="checkbox" name="ros_resp_cough" value="1" <? echo (($fdata{'ros_resp_cough'} == '1')?' checked ':''); ?>>
      Cough</td>
        <td colspan="2" align="left" nowrap><input type="checkbox" name="ros_resp_other" value="1" <? echo (($fdata{'ros_resp_other'} == '1')?' checked ':''); ?>> 
          Other
</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">6. Gastrointestinal </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_gastr_negative" value="1" <? echo (($fdata{'ros_gastr_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_gastr_diarrhea" value="1" <? echo (($fdata{'ros_gastr_diarrhea'} == '1')?' checked ':''); ?>>
      Diarrhea</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_gastr_bloody_stool" value="1" <? echo (($fdata{'ros_gastr_bloody_stool'} == '1')?' checked ':''); ?>>
      Bloody stool </td>
        <td colspan="2"><input type="checkbox" name="ros_gastr_nausea" value="1" <? echo (($fdata{'ros_gastr_nausea'} == '1')?' checked ':''); ?>> 
          Nausea/Vomiting/Indigestion
</td>
        </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_gastr_constipation" value="1" <? echo (($fdata{'ros_gastr_constipation'} == '1')?' checked ':''); ?>>
      Constipation</td>
        <td nowrap><input type="checkbox" name="ros_gastr_flatulence" value="1" <? echo (($fdata{'ros_gastr_flatulence'} == '1')?' checked ':''); ?>>
      Flatulence</td>
        <td nowrap><input type="checkbox" name="ros_gastr_pain" value="1" <? echo (($fdata{'ros_gastr_pain'} == '1')?' checked ':''); ?>>
      pain</td>
        <td align="left" nowrap><input type="checkbox" name="ros_gastr_fecal" value="1" <? echo (($fdata{'ros_gastr_fecal'} == '1')?' checked ':''); ?>> 
          Fecal incontinence</td>
        <td nowrap><input type="checkbox" name="ros_gastr_other" value="1" <? echo (($fdata{'ros_gastr_other'} == '1')?' checked ':''); ?>> 
          Other</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">7. Genitourinary </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_genit_negative" value="1" <? echo (($fdata{'ros_genit_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_genit_hematuria" value="1" <? echo (($fdata{'ros_genit_hematuria'} == '1')?' checked ':''); ?>>
      Hematuria</td>
        <td nowrap><input type="checkbox" name="ros_genit_dysuria" value="1" <? echo (($fdata{'ros_genit_dysuria'} == '1')?' checked ':''); ?>>
      Dysuria</td>
        <td align="left" nowrap><input type="checkbox" name="ros_genit_urgency" value="1" <? echo (($fdata{'ros_genit_urgency'} == '1')?' checked ':''); ?>> 
          Urgency</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_genit_frequency" value="1" <? echo (($fdata{'ros_genit_frequency'} == '1')?' checked ':''); ?>>
      Frequency</td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_genit_incomplete_emptying" value="1" <? echo (($fdata{'ros_genit_incomplete_emptying'} == '1')?' checked ':''); ?>>
      Incomplete emptying </td>
        <td align="left" nowrap><input type="checkbox" name="ros_genit_incontinence" value="1" <? echo (($fdata{'ros_genit_incontinence'} == '1')?' checked ':''); ?>> 
          Incontinence</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_genit_dyspareunia" value="1" <? echo (($fdata{'ros_genit_dyspareunia'} == '1')?' checked ':''); ?>>
  Dyspareunia</td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_genit_abnormal_periods" value="1" <? echo (($fdata{'ros_genit_abnormal_periods'} == '1')?' checked ':''); ?>>
          Abnormal or painful periods </td>
        <td nowrap><input type="checkbox" name="ros_genit_pms" value="1" <? echo (($fdata{'ros_genit_pms'} == '1')?' checked ':''); ?>>
  PMS</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><input type="checkbox" name="ros_genit_abnormal_bleeding" value="1" <? echo (($fdata{'ros_genit_abnormal_bleeding'} == '1')?' checked ':''); ?>>
  Abnormal vaginal bleeding </td>
        <td nowrap><input type="checkbox" name="ros_genit_abnormal_discharge" value="1" <? echo (($fdata{'ros_genit_abnormal_discharge'} == '1')?' checked ':''); ?>>
  Abnormal vaginal discharge </td>
        <td nowrap><input type="checkbox" name="ros_genit_other" value="1" <? echo (($fdata{'ros_genit_other'} == '1')?' checked ':''); ?>>
  other</td>
        <td align="left" nowrap>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">8. Musculoskeletal </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="2">
      <tr align="left" valign="baseline">
        <td width="40%" nowrap><input type="checkbox" name="ros_muscul_negative" value="1" <? echo (($fdata{'ros_muscul_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="40%" nowrap><input type="checkbox" name="ros_muscul_weakness" value="1" <? echo (($fdata{'ros_muscul_weakness'} == '1')?' checked ':''); ?>>
      Muscle weakness </td>
        <td nowrap>&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_muscul_pain" value="1" <? echo (($fdata{'ros_muscul_pain'} == '1')?' checked ':''); ?>>
      Muscle or joint pain </td>
        <td width="40%" nowrap><input type="checkbox" name="ros_muscul_other" value="1" <? echo (($fdata{'ros_muscul_other'} == '1')?' checked ':''); ?>>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_skin_negative" value="1" <? echo (($fdata{'ros_skin_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_skin_rash" value="1" <? echo (($fdata{'ros_skin_rash'} == '1')?' checked ':''); ?>>
      Rash</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_skin_ulcers" value="1" <? echo (($fdata{'ros_skin_ulcers'} == '1')?' checked ':''); ?>>
      Ulcers</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_skin_dry" value="1" <? echo (($fdata{'ros_skin_dry'} == '1')?' checked ':''); ?>>
      Dry skin </td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_skin_pigmented" value="1" <? echo (($fdata{'ros_skin_pigmented'} == '1')?' checked ':''); ?>>
      Pigmented lesions </td>
        <td align="left" nowrap><input type="checkbox" name="ros_skin_other" value="1" <? echo (($fdata{'ros_skin_other'} == '1')?' checked ':''); ?>>
other</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">9b. Breast </td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_breast_negative" value="1" <? echo (($fdata{'ros_breast_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_breast_mastalgia" value="1" <? echo (($fdata{'ros_breast_mastalgia'} == '1')?' checked ':''); ?>>
      Mastalgia</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_breast_discharge" value="1" <? echo (($fdata{'ros_breast_discharge'} == '1')?' checked ':''); ?>>
      Discharge</td>
        <td nowrap><input type="checkbox" name="ros_breast_masses" value="1" <? echo (($fdata{'ros_breast_masses'} == '1')?' checked ':''); ?>>
      Masses</td>
        <td nowrap><input type="checkbox" name="ros_breast_other" value="1" <? echo (($fdata{'ros_breast_other'} == '1')?' checked ':''); ?>>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_neuro_negative" value="1" <? echo (($fdata{'ros_neuro_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_neuro_syncope" value="1" <? echo (($fdata{'ros_neuro_syncope'} == '1')?' checked ':''); ?>>
      Syncope</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_neuro_seizures" value="1" <? echo (($fdata{'ros_neuro_seizures'} == '1')?' checked ':''); ?>>
      Seizures</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_neuro_numbness" value="1" <? echo (($fdata{'ros_neuro_numbness'} == '1')?' checked ':''); ?>> 
          Numbness</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><input type="checkbox" name="ros_neuro_trouble_walking" value="1" <? echo (($fdata{'ros_neuro_trouble_walking'} == '1')?' checked ':''); ?>>
      Trouble walking </td>
        <td colspan="2" nowrap><input type="checkbox" name="ros_neuro_memory" value="1" <? echo (($fdata{'ros_neuro_memory'} == '1')?' checked ':''); ?>>
Severe memory problems </td>
        <td><input type="checkbox" name="ros_neuro_other" value="1" <? echo (($fdata{'ros_neuro_other'} == '1')?' checked ':''); ?>>
other</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">11. Psychiatric</td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_psych_negative" value="1" <? echo (($fdata{'ros_psych_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_psych_depression" value="1" <? echo (($fdata{'ros_psych_depression'} == '1')?' checked ':''); ?>>
      Depression</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_psych_crying" value="1" <? echo (($fdata{'ros_psych_crying'} == '1')?' checked ':''); ?>>
      Crying</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td colspan="2" nowrap><input type="checkbox" name="ros_psych_anxiety" value="1" <? echo (($fdata{'ros_psych_anxiety'} == '1')?' checked ':''); ?>>
      Severe anxiety </td>
        <td width="20%" nowrap><input type="checkbox" name="ros_psych_other" value="1" <? echo (($fdata{'ros_psych_other'} == '1')?' checked ':''); ?>>
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
        <td width="20%" nowrap><input type="checkbox" name="ros_endo_negative" value="1" <? echo (($fdata{'ros_endo_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_endo_diabetes" value="1" <? echo (($fdata{'ros_endo_diabetes'} == '1')?' checked ':''); ?>>
        Diabetes</td>
        <td nowrap><input type="checkbox" name="ros_endo_hipothyroid" value="1" <? echo (($fdata{'ros_endo_hipothyroid'} == '1')?' checked ':''); ?>>
      Hypothyroid</td>
        <td nowrap><input type="checkbox" name="ros_endo_hiperthyroid" value="1" <? echo (($fdata{'ros_endo_hiperthyroid'} == '1')?' checked ':''); ?>>
Hyperthyroid</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_endo_flashes" value="1" <? echo (($fdata{'ros_endo_flashes'} == '1')?' checked ':''); ?>>
      Hot flashes </td>
        <td nowrap><input type="checkbox" name="ros_endo_hair_loss" value="1" <? echo (($fdata{'ros_endo_hair_loss'} == '1')?' checked ':''); ?>>
      Hair loss </td>
        <td nowrap><input type="checkbox" name="ros_endo_intolerance" value="1" <? echo (($fdata{'ros_endo_intolerance'} == '1')?' checked ':''); ?>>
      Heat/cold intolerance </td>
        <td><input type="checkbox" name="ros_endo_other" value="1" <? echo (($fdata{'ros_endo_other'} == '1')?' checked ':''); ?>> 
          Other
</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="190" align="left" valign="top" class="ficaption">13. Hematologic/Lymphatic</td>
    <td align="left" valign="top" class="fibody"><table width="100%"  border="0" cellpadding="0" cellspacing="1">
      <tr align="left" valign="baseline">
        <td width="20%" nowrap><input type="checkbox" name="ros_hemato_negative" value="1" <? echo (($fdata{'ros_hemato_negative'} == '1')?' checked ':''); ?>>
      Negative</td>
        <td width="20%" nowrap><input type="checkbox" name="ros_hemato_bruises" value="1" <? echo (($fdata{'ros_hemato_bruises'} == '1')?' checked ':''); ?>>
      Bruises</td>
        <td width="20%" nowrap>&nbsp;</td>
        <td width="20%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr align="left" valign="baseline">
        <td nowrap><input type="checkbox" name="ros_hemato_bleeding" value="1" <? echo (($fdata{'ros_hemato_bleeding'} == '1')?' checked ':''); ?>>
      Bleeding</td>
        <td nowrap><input type="checkbox" name="ros_hemato_adenopathy" value="1" <? echo (($fdata{'ros_hemato_adenopathy'} == '1')?' checked ':''); ?>>
      Adenopathy</td>
        <td nowrap><input type="checkbox" name="ros_hemato_other" value="1" <? echo (($fdata{'ros_hemato_other'} == '1')?' checked ':''); ?>>
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
<script language="JavaScript" type="text/JavaScript">
 InitSection();
</script> 
<table width="100%" border="0">
  <tr>
    <td align="left" width="100"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
    <td align="left" nowrap> <a href="<? echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<? echo $id; ?>&formname=<? echo $formname; ?>"
     target="_blank" class="link_submit" onclick="top.restoreSession()">[Printable form]</a> </td>
    <td align="right"> <a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit"
     onclick="top.restoreSession()">[Don't Save]</a> </td>
  </tr>
</table>
</form>
<?php
formFooter();
?>
</body>
</html>
