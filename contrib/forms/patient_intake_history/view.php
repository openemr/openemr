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

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<link rel=stylesheet href="../../acog.css" type="text/css">

<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>

<script language="JavaScript" type="text/JavaScript">

window.onload = initialize;

</script>

</head>



<body <?echo $top_bg_line;?>>



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

<form action="<?echo $rootdir;?>/forms/patient_intake_history/save.php?mode=update&id=<? echo $id; ?>" method="post" enctype="multipart/form-data" name="my_form">

<?

$addmenu = <<<EOL

<blockquote>

  <small><strong>Local sections: </strong><br>

  <a href="#gh">Gynecologic history</a> | 

  <a href="#oh">Obstetric history</a> | <a href="#cm">Current medications</a> |

  <a href="#fh">Family history</a> | <a href="#sh">Social history</a> | 

  <a href="#pp">Personal profile</a> |

  <a href="#ih">Personal past history of illnesses</a> | <a href="#op">Operations/Hospitalizations</a> |

  <a href="#ii">Injuries/Illnesses</a> | <a href="#im">Immunizations/Test</a> |

  <a href="#ros">Review of systems</a></small>

</blockquote>

EOL;

?> 

<? include("../../acog_menu.inc"); ?> 

  <table width="50%"  border="0" cellspacing="0" cellpadding="2">

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3">For office use only </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"> <input name="pih_patient" type="radio" value="0"  <? echo (($fdata{'pih_patient'} == '0')?' checked ':''); ?>>

      New patient </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><input name="pih_patient" type="radio" value="1" <? echo (($fdata{'pih_patient'} == '1')?' checked ':''); ?>>

      Established patient </td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><input name="pih_consultation" type="checkbox"  value="1" <? echo (($fdata{'pih_consultation'} == '1')?' checked ':''); ?>>

      Consultation</td>

    </tr>

    <tr>

      <td align="left" valign="bottom" nowrap class="fibody3"><input name="pih_report_sent" type="checkbox"  value="1" <? echo (($fdata{'pih_report_sent'} == '1')?' checked ':''); ?>>

      Report sent 

      <input name="pih_report_sent_date" type="text" class="fullin"  style="width:90px"  value="<? echo $fdata{'pih_report_sent_date'}; ?>"></td>

    </tr>

</table>

<?

$tip1 = <<<EOL

<strong>Patient Intake History</strong> is an optional form

giving practices the flexibility to have patients complete

their own history at or before the visit. It uses language

that a patient is likely to understand and includes ample

space for physician notes. Space at the end of the form

allows physicians to review the history and sign off for 4

years. At year 5, the patient should be asked to complete

a new Patient Intake History.

EOL;

$tip1 = strtr($tip1, "\n\r", "  ");

?>

<div class="srvChapter">Patient Intake history <a href="#" onMouseOver="toolTip('<? echo $tip1; ?>', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>

<div style="border: solid 2px black; background-color: white;">

<table width="100%"  border="0" cellspacing="0" cellpadding="0">

  <tr>

    <td align="left" valign="top" class="fibody2" style="border-bottom: 2px solid black"><table width="100%"  border="0" cellspacing="0" cellpadding="5">

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

          <input name="pih_pid" type="text" class="fullin" id="pih_pid" size="12" value="<?

          echo $patient{'id'};

          ?>"></td>

        <td width="20%">date<br>

        <input name="pih_date" type="text" class="fullin" id="pih_date" value="<?

        echo date('Y-m-d');

        ?>" size="12"></td>

      </tr>

    </table> 

</td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr align="left" valign="bottom">

        <td colspan="3" class="fibody2">Address:

          <input name="address" type="text" class="fullin"  style="width: 90%" value="<? echo $fdata{'address'}; ?>"></td>

        </tr>

      <tr align="left" valign="bottom">

        <td width="50%" class="fibody2" id="bordR">City:

          <input name="city" type="text" class="fullin"  style="width: 250px" value="<? echo $fdata{'city'}; ?>"></td>

        <td width="50%" colspan="2" class="fibody2">State/ZIP:

          <input name="state" type="text" class="fullin"  style="width: 250px" value="<? echo $fdata{'state'}; ?>"></td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Home telephone: 

          <span style="width:auto">

          <input name="home_phone" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'home_phone'}; ?>">

          </span></td>

        <td colspan="2" class="fibody2">Work telephone: 

          <input name="work_phone" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'work_phone'}; ?>"></td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Employer:

          <input name="employer" type="text" class="fullin"  style="width: 80%" value="<? echo $fdata{'employer'}; ?>"></td>

        <td width="25%" class="fibody2" id="bordR">Insurance 

          <input name="insurance" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'insurance'}; ?>"></td>

        <td width="25%" class="fibody2">Policy No: 

          <input name="policy_no" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'policy_no'}; ?>"></td>

      </tr>

      <tr align="left" valign="bottom">

        <td class="fibody2" id="bordR">Name you would like us to use:           <input name="name_to_use" type="text" class="fullin"  style="width: 50%" value="<? echo $fdata{'name_to_use'}; ?>"> </td>

        <td colspan="2" class="fibody2">Primary language: 

          <input name="primary_language" type="text" class="fullin"  style="width: 150px" value="<? echo $fdata{'primary_language'}; ?>"></td>

      </tr>

    </table></td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr align="left" valign="bottom">

        <td width="40%" class="fibody2" id="bordR">Name of spouse/partner: </td>

        <td colspan="2" class="fibody2">Emergency contact: 

          <input name="partner_emergency_contact" type="text" class="fullin"  style="width: 70%" value="<? echo $fdata{'partner_emergency_contact'}; ?>"></td>

        </tr>

      <tr align="left" valign="bottom">

        <td rowspan="2" valign="top" class="fibody2" id="bordR"><textarea name="partner_name" rows="2" wrap="VIRTUAL" class="fullin2"  style="height:100%"><? echo $fdata{'partner_name'}; ?></textarea></td>

        <td colspan="2" class="fibody2">Relationship: 

          <input name="relationship" type="text" class="fullin"  style="width:80%" value="<? echo $fdata{'relationship'}; ?>"></td>

        </tr>

      <tr align="left" valign="bottom">

        <td width="30%" class="fibody2" id="bordR">Home telephone: 

          <input name="partner_home_phone" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'partner_home_phone'}; ?>"></td>

        <td width="30%" class="fibody2">Work telephone: 

          <input name="partner_work_phone" type="text" class="fullin"  style="width: 120px" value="<? echo $fdata{'partner_work_phone'}; ?>"></td>

      </tr>

    </table></td>

  </tr>

  <tr>

    <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="bottom" class="fibody2">Referred by: 

          <input name="referred_by" type="text" class="fullin"  style="width: 85%;" value="<? echo $fdata{'referred_by'}; ?>"></td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Why have you come to the office today? 

          <input name="why_come_to_office" type="text" class="fullin"  value="<? echo $fdata{'why_come_to_office'}; ?>"></td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">If you are here for the annual examination is this a 

          <input name="primary_care_visit" type="radio" value="1"  <? echo (($fdata{'primary_care_visit'} == '1')?' checked ':''); ?>>

          Primary care visit or 

          <input name="primary_care_visit" type="radio" value="0" <? echo (($fdata{'primary_care_visit'} == '0')?' checked ':''); ?>>

          Gynecology only </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Is this a new problem? 

          <input name="new_problem" type="radio" value="1"  <? echo (($fdata{'new_problem'} == '1')?' checked ':''); ?>>

          yes &nbsp;&nbsp;&nbsp;&nbsp;

          <input name="new_problem" type="radio" value="0" <? echo (($fdata{'new_problem'} == '0')?' checked ':''); ?>>

          no</td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Please, describe your problem, including, where it is, how severe it is, and how long it has lasted <br>

          <textarea name="problem_description" rows="6" class="fullin2" ><? echo $fdata{'problem_description'}; ?></textarea></td>

      </tr>

    </table></td>

  </tr>

</table> 

</div>

<h2 align="center"><small>If you are uncomfortable answering any questions, leave them blank; you can discuss them with your doctor or nurse.</small></h2>

<p align="center">&nbsp;</p>

<h2 align="center"><a name="gh"></a>Gynecologic history <br>

  </h2>

<div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr align="left" valign="bottom">

            <td width="50%" nowrap class="fibody2" id="bordR">&nbsp;</td>

            <td width="50%" align="center" class="ficaption2">Physicians notes </td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Last normal menstrual period (first day) 

              <input name="last_period_date" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'last_period_date'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_1" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_1'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Age periods began: 

              <input name="periods_began" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'periods_began'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_2" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_2'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Length of periods (number of days of bleeding): 

              <input name="period_lenght" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'period_lenght'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_3" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_3'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Number of days between periods: 

              <input name="period_days_between" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'period_days_between'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_4" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_4'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Any recent changes in periods? 

              <input name="period_changes" type="radio" value="1" <? echo (($fdata{'period_changes'} == '1')?' checked ':''); ?>>

Yes

<input name="pih_gh_recent_changes_periods" type="radio" value="0"  <? echo (($fdata{'pih_gh_recent_changes_periods'} == '0')?' checked ':''); ?>>

No</td>

            <td class="fibody2"><input name="gh_notes_5" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_5'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Are you currently sexually active? 

              <input name="sexually_active" type="radio" value="1"  <? echo (($fdata{'sexually_active'} == '1')?' checked ':''); ?>>

Yes

<input name="sexually_active" type="radio" value="0" <? echo (($fdata{'sexually_active'} == '0')?' checked ':''); ?>>

No</td>

            <td class="fibody2"><input name="gh_notes_6" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_6'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">have you ever had sex? 

              <input name="ever_had_sex" type="radio" value="1"  <? echo (($fdata{'ever_had_sex'} == '1')?' checked ':''); ?>>

Yes

<input name="ever_had_sex" type="radio" value="0" <? echo (($fdata{'ever_had_sex'} == '0')?' checked ':''); ?>>

No</td>

            <td class="fibody2"><input name="gh_notes_7" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_7'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Number of sexual partners (Lifetime): 

              <input name="number_of_partners" type="text" class="fullin"  style="width: 90px"  value="<? echo $fdata{'number_of_partners'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_8" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_8'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Sexual partners are 

              <input name="partners" type="radio" value="men"  <? echo (($fdata{'partners'} == 'men')?' checked ':''); ?>>

              Men 

              <input name="partners" type="radio" value="women" <? echo (($fdata{'partners'} == 'women')?' checked ':''); ?>>

              Women 

              <input name="partners" type="radio" value="both" <? echo (($fdata{'partners'} == 'both')?' checked ':''); ?>>

              Both </td>

            <td class="fibody2"><input name="gh_notes_9" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_9'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Present method of birth control: 

              <input name="present_birth_control" type="text" class="fullin"  style="width: 90px"  value="<? echo $fdata{'present_birth_control'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_10" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_10'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Have you ever used an intrauterine device (IUD) or birth control pills ?

              <input name="pills_iud" type="radio" value="1" <? echo (($fdata{'pills_iud'} == '1')?' checked ':''); ?>>

Yes

<input name="pills_iud" type="radio" value="0"  <? echo (($fdata{'pills_iud'} == '0')?' checked ':''); ?>>

No </td>

            <td class="fibody2"><input name="gh_notes_11" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_11'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">if yes, for how long? 

              <input name="pills_how_long" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'pills_how_long'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_12" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_12'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">When was your last PAP test? 

              <input name="pap_test" type="text" class="fullin"  style="width: 90px" value="<? echo $fdata{'pap_test'}; ?>"></td>

            <td class="fibody2"><input name="gh_notes_13" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_13'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Do you do breast self examinations? 

              <input name="breast_self_exam" type="radio" value="1" <? echo (($fdata{'breast_self_exam'} == '1')?' checked ':''); ?>>

              Yes

              <input name="breast_self_exam" type="radio" value="0"  <? echo (($fdata{'breast_self_exam'} == '0')?' checked ':''); ?>>

              No</td>

            <td class="fibody2"><input name="gh_notes_14" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_14'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Have you been exposed to diethylstilbestrol (DES)? 

              <input name="des" type="radio" value="1" <? echo (($fdata{'des'} == '1')?' checked ':''); ?>>

Yes

<input name="des" type="radio" value="0" <? echo (($fdata{'des'} == '0')?' checked ':''); ?>>

No </td>

            <td class="fibody2"><input name="gh_notes_15" type="text" class="fullin2"  value="<? echo $fdata{'gh_notes_15'}; ?>"></td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="oh"></a>Obstetric history <br>

  </h2>

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

              <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_pregnancies" type="text" class="fullin2"   value="<? echo $fdata{'oh_pregnancies'}; ?>"></td>

              <td width="30%" nowrap class="fibody2" id="bordR">abortions</td>

              <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_abortions" type="text" class="fullin2"   value="<? echo $fdata{'oh_abortions'}; ?>"></td>

              <td width="30%" nowrap class="fibody2" id="bordR">miscarriages</td>

              <td width="50" nowrap class="fibody2"><input name="oh_miscarriages" type="text" class="fullin2"   value="<? echo $fdata{'oh_miscarriages'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td width="30%" nowrap class="fibody2" id="bordR">premature births(&lt;37 weeks) </td>

              <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_premature_births" type="text" class="fullin2"  value="<? echo $fdata{'oh_premature_births'}; ?>"></td>

              <td width="30%" nowrap class="fibody2" id="bordR">live births </td>

              <td width="50" nowrap class="fibody2" id="bordR"><input name="oh_live_births" type="text" class="fullin2"  value="<? echo $fdata{'oh_live_births'}; ?>"></td>

              <td width="30%" nowrap class="fibody2" id="bordR">living children </td>

              <td width="50" nowrap class="fibody2"><input name="oh_living_children" type="text" class="fullin2"  value="<? echo $fdata{'oh_living_children'}; ?>"></td>

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

              <td class="fibody2"><input name="oh_complications" type="text" class="fullin2"   value="<? echo $fdata{'oh_complications'}; ?>"></td>

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

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="cm"></a>Current medications <br>

    <small>(Including hormones, vitamins, herbs, nonprescription medications) </small><br>

  </h2>

  <div style="border: solid 2px black; background-color: white;">

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

        <td align="left" valign="top" class="fibody2" id="bordR"><input name="pres_drug_${bi}" value="${pres_dosage}" type="text" class="fullin2"></td>

        <td align="left" valign="top" class="fibody2" id="bordR"><input name="pres_dosage_${bi}" value="${pres_drug}" type="text" class="fullin2"></td>

        <td align="left" valign="top" class="fibody2" id="bordR"><input name="pres_who_${bi}" value="${pres_who}" type="text" class="fullin2"></td>

        <td align="left" valign="top" class="fibody2" id="bordR"><input name="pres_drug_${bi2}" value="${pres_drug1}" type="text" class="fullin2"></td>

        <td align="left" valign="top" class="fibody2" id="bordR"><input name="pres_dosage_${bi2}" value="${pres_dosage1}" type="text" class="fullin2"></td>

        <td align="left" valign="top" class="fibody2"><input name="pres_who_${bi2}" type="text" value="${pres_who1}" class="fullin2"></td>

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

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="0">

      <tr>

        <td align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="2">

          <tr align="left" valign="bottom">

            <td width="50%" nowrap class="fibody2" id="bordR">Mother: 

              <input name="fh_mother" type="radio" value="0"  <? echo (($fdata{'fh_mother'} == '0')?' checked ':''); ?>>

              living 

              <input name="fh_mother" type="radio" value="1" <? echo (($fdata{'fh_mother'} == '1')?' checked ':''); ?>>

              deceased - cause: 

              <input name="fh_mother_dec_cause" type="text" class="fullin"  style="width: 20%" value="<? echo $fdata{'fh_mother_dec_cause'}; ?>">

              Age: 

              <input name="fh_mother_dec_age" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_mother_dec_age'}; ?>"></td>

            <td width="50%" nowrap class="fibody2">father:

              <input name="fh_father" type="radio" value="0"  <? echo (($fdata{'fh_father'} == '0')?' checked ':''); ?>>

living

<input name="fh_father" type="radio" value="1" <? echo (($fdata{'fh_father'} == '1')?' checked ':''); ?>>

deceased - cause:

<input name="fh_father_dec_cause" type="text" class="fullin"  style="width: 20%" value="<? echo $fdata{'fh_father_dec_cause'}; ?>">

Age:

<input name="fh_father_dec_age" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_father_dec_age'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2">Siblings: Num.living: 

              <input name="fh_sibl_living" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_sibl_living'}; ?>">

              , num.deceased:

              <input name="fh_sib_deceased" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_sib_deceased'}; ?>">

              , cause(s)/age(s): </td>

            <td nowrap class="fibody2"><input name="fh_sib_dec_cause" type="text" class="fullin"  value="<? echo $fdata{'fh_sib_dec_cause'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2">Children: Num.living:

              <input name="fh_children_living" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_children_living'}; ?>">

, num.deceased:

<input name="fh_children_deceased" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'fh_children_deceased'}; ?>">

, cause(s)/age(s):</td>

            <td nowrap class="fibody2"><input name="fh_children_dec_cause" type="text" class="fullin"  value="<? echo $fdata{'fh_children_dec_cause'}; ?>"></td>

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

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_diabetes" type="checkbox"  value="1" <? echo (($fdata{'fh_diabetes'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_diabetes_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_diabetes_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_1" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_1'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Stroke</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_stroke" type="checkbox"  value="1" <? echo (($fdata{'fh_stroke'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_stroke_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_stroke_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_2" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_2'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Heart dIsease </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_heart_disease" type="checkbox"  value="1" <? echo (($fdata{'fh_heart_disease'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"> <input name="fh_heart_disease_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_heart_disease_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_3" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_3'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Blood clots in lungs or legs </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fhbllod_clots" type="checkbox"  value="1" <? echo (($fdata{'fhbllod_clots'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fhbllod_clots_info" type="text" class="fullin2"  value="<? echo $fdata{'fhbllod_clots_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_4" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_4'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">High blood pressure </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_high_pressure" type="checkbox"  value="1" <? echo (($fdata{'fh_high_pressure'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_high_pressure_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_high_pressure_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_5" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_5'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">High cholesterol</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_high_cholesterol" type="checkbox"  value="1" <? echo (($fdata{'fh_high_cholesterol'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_high_cholesterol_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_high_cholesterol_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_6" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_6'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Osteoporosis (weak bones) </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_osteoporosis" type="checkbox"  value="1" <? echo (($fdata{'fh_osteoporosis'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_osteoporosis_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_osteoporosis_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_7" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_7'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Hepatitis</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_hepatitis" type="checkbox"  value="1" <? echo (($fdata{'fh_hepatitis'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_hepatitis_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_hepatitis_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_8" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_8'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">HIV / AIDS</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_hiv" type="checkbox"  value="1" <? echo (($fdata{'fh_hiv'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_hiv_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_hiv_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_9" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_9'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Tuberculosis</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_tuberculosis" type="checkbox"  value="1" <? echo (($fdata{'fh_tuberculosis'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_tuberculosis_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_tuberculosis_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_10" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_10'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Birth defects </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="dh_birth_defects" type="checkbox"  value="1" <? echo (($fdata{'dh_birth_defects'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="dh_birth_defects_info" type="text" class="fullin2"  value="<? echo $fdata{'dh_birth_defects_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_11" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_11'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Alcohol or drug problems </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_alcohol_drugs" type="checkbox"  value="1" <? echo (($fdata{'fh_alcohol_drugs'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_alcohol_drugs_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_alcohol_drugs_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_12" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_12'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Breast cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_breast_cancer" type="checkbox"  value="1" <? echo (($fdata{'fh_breast_cancer'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_breast_cancer_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_breast_cancer_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_13" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_13'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Colon cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_colon_cancer" type="checkbox"  value="1" <? echo (($fdata{'fh_colon_cancer'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_colon_cancer_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_colon_cancer_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_14" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_14'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Ovarian cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_ovarian_cancer" type="checkbox"  value="1" <? echo (($fdata{'fh_ovarian_cancer'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_ovarian_cancer" type="text" class="fullin2"  value="<? echo $fdata{'fh_ovarian_cancer'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_15" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_15'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Uterine cancer </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_uterine_cancer" type="checkbox"  value="1" <? echo (($fdata{'fh_uterine_cancer'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_uterine_cancer_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_uterine_cancer_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_16" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_16'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Mental illness/Depression </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_mental_illness" type="checkbox"  value="1" <? echo (($fdata{'fh_mental_illness'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_mental_illness_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_mental_illness_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_17" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_17'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Alzheimer's disease </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_alzheimer" type="checkbox"  value="1" <? echo (($fdata{'fh_alzheimer'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_alzheimer_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_alzheimer_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_18" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_18'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Other</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="fh_other_illness" type="checkbox"  value="1" <? echo (($fdata{'fh_other_illness'} == '1')?' checked ':''); ?>></td>

            <td class="fibody2" id="bordR"><input name="fh_other_illness_info" type="text" class="fullin2"  value="<? echo $fdata{'fh_other_illness_info'}; ?>"></td>

            <td class="fibody2"><input name="fh_notes_19" type="text" class="fullin2"  value="<? echo $fdata{'fh_notes_19'}; ?>"></td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="sh"></a>Social history <br>

  </h2>

  <div style="border: solid 2px black; background-color: white;">

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

            <td nowrap class="fibody2" id="bordR">Ever smoked? current smoking: packs/day:

              <input name="sh_smoked_packs" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_smoked_packs'}; ?>"> 

              , years: 

              <input name="sh_smoked_years" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_smoked_years'}; ?>"></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_smoked" type="radio" value="1" <? echo (($fdata{'pih_smoked'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_smoked" type="radio" value="0"  <? echo (($fdata{'pih_smoked'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_1" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_1'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">alcohol: drinks/day:

              <input name="sh_alcohol_drinks_day" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_alcohol_drinks_day'}; ?>">

              , 

              drinks/week:

              <input name="sh_alcohol_drinks_week" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_alcohol_drinks_week'}; ?>">

              , 

              type of drink:

              <input name="sh_alcohol_drinks_type" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_alcohol_drinks_type'}; ?>"></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_alcohol" type="radio" value="1" <? echo (($fdata{'pih_alcohol'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_alcohol" type="radio" value="0"  <? echo (($fdata{'pih_alcohol'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_2" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_2'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Drug use </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_drug" type="radio" value="1" <? echo (($fdata{'pih_drug'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_drug" type="radio" value="0"  <? echo (($fdata{'pih_drug'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_3" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_3'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">seat belt use </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_seat_belt" type="radio" value="1" <? echo (($fdata{'pih_seat_belt'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_seat_belt" type="radio" value="0"  <? echo (($fdata{'pih_seat_belt'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_4" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_4'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">regular exercise: how long and how often? 

              <input name="sh_exercise_info" type="text" class="fullin"  style="width: 150px" value="<? echo $fdata{'sh_exercise_info'}; ?>"></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_exercise" type="radio" value="1" <? echo (($fdata{'pih_exercise'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_exercise" type="radio" value="0"  <? echo (($fdata{'pih_exercise'} == '0')?' checked ':''); ?>></td>

            <td width="400" class="fibody2"><input name="sh_notes_5" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_5'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Dairy product intake and/or calcium supplements: daily intake: 

              <input name="sh_dairy_daily" type="text" class="fullin"  style="width: 40px" value="<? echo $fdata{'sh_dairy_daily'}; ?>"></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_dairy" type="radio" value="1" <? echo (($fdata{'pih_dairy'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_dairy" type="radio" value="0"  <? echo (($fdata{'pih_dairy'} == '0')?' checked ':''); ?>></td>

            <td width="400" class="fibody2"><input name="sh_notes_6" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_6'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">health hazards at home or work? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_hazards" type="radio" value="1" <? echo (($fdata{'pih_hazards'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_hazards" type="radio" value="0"  <? echo (($fdata{'pih_hazards'} == '0')?' checked ':''); ?>></td>

            <td width="400" class="fibody2"><input name="sh_notes_7" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_7'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">have you been sexually abused, threatened or hurt by anyone? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_abuse" type="radio" value="1" <? echo (($fdata{'pih_abuse'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_abuse" type="radio" value="0"  <? echo (($fdata{'pih_abuse'} == '0')?' checked ':''); ?>></td>

            <td width="400" class="fibody2"><input name="sh_notes_8" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_8'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">do you have an advance directive (living will)?</td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_living_will" type="radio" value="1" <? echo (($fdata{'pih_living_will'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_living_will" type="radio" value="0"  <? echo (($fdata{'pih_living_will'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_9" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_9'}; ?>"></td>

          </tr>

          <tr align="left" valign="bottom">

            <td nowrap class="fibody2" id="bordR">Are you an organ donor? </td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_donor" type="radio" value="1" <? echo (($fdata{'pih_donor'} == '1')?' checked ':''); ?>></td>

            <td align="center" valign="middle" class="fibody2" id="bordR"><input name="pih_donor" type="radio" value="0"  <? echo (($fdata{'pih_donor'} == '0')?' checked ':''); ?>></td>

            <td class="fibody2"><input name="sh_notes_10" type="text" class="fullin2"  value="<? echo $fdata{'sh_notes_10'}; ?>"></td>

          </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="pp"></a>Personal profile <br>

  </h2>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="bottom" class="fibody2">Sexual orientation: 

          <input name="pih_pp_orientation" type="radio" value="hetero"  <? echo (($fdata{'pih_pp_orientation'} == 'hetero')?' checked ':''); ?>>

        heterosexual 

        <input name="pih_pp_orientation" type="radio" value="homo" <? echo (($fdata{'pih_pp_orientation'} == 'homo')?' checked ':''); ?>>

        homosexual 

        <input name="pih_pp_orientation" type="radio" value="bi" <? echo (($fdata{'pih_pp_orientation'} == 'bi')?' checked ':''); ?>>

        bisexual </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Marital status: 

          <input name="pih_pp_status" type="radio" value="married" <? echo (($fdata{'pih_pp_status'} == 'married')?' checked ':''); ?>>

        married

        &nbsp;&nbsp;

        <input name="pih_pp_status" type="radio" value="partner" <? echo (($fdata{'pih_pp_status'} == 'partner')?' checked ':''); ?>> 

        living with partner&nbsp;&nbsp;        <input name="pih_pp_status" type="radio" value="single"  <? echo (($fdata{'pih_pp_status'} == 'single')?' checked ':''); ?>>

        single

        &nbsp;&nbsp;

        <input name="pih_pp_status" type="radio" value="widowed" <? echo (($fdata{'pih_pp_status'} == 'widowed')?' checked ':''); ?>> 

        widowed&nbsp;&nbsp;        <input name="pih_pp_status" type="radio" value="divorced" <? echo (($fdata{'pih_pp_status'} == 'divorced')?' checked ':''); ?>>

        divorced </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Number of living children:        

        <input name="pp_living_children" type="text" class="fullin"  style="width: 70px"  value="<? echo $fdata{'pp_living_children'}; ?>"></td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Number of people in household: 

        <input name="pp_number_household" type="text" class="fullin"  style="width: 70px"  value="<? echo $fdata{'pp_number_household'}; ?>"></td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">School completed: 

          <input name="pih_pp_education" type="radio"  value="highschool" <? echo (($fdata{'pih_pp_education'} == 'highschool')?' checked ':''); ?>>

        high school 

        <input name="pih_pp_education" type="radio"  value="aadegree" <? echo (($fdata{'pih_pp_education'} == 'aadegree')?' checked ':''); ?>>

        some college/AA degree 

        <input name="pih_pp_education" type="radio"  value="college" <? echo (($fdata{'pih_pp_education'} == 'college')?' checked ':''); ?>>

        college 

        <input name="pih_pp_education" type="radio"  value="gdegree" <? echo (($fdata{'pih_pp_education'} == 'gdegree')?' checked ':''); ?>>

        graduate degree 

        <input name="pih_pp_education" type="radio"  value="other"  <? echo (($fdata{'pih_pp_education'} == 'other')?' checked ':''); ?>>

        other </td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody2">Current or most recent job: 

        <input name="pp_current_job" type="text" class="fullin"  style="width: 77%"  value="<? echo $fdata{'pp_current_job'}; ?>"></td>

      </tr>

      <tr>

        <td align="left" valign="bottom" class="fibody3">Travel outside the United States? 

          <input name="pp_travel_outside_us" type="radio" value="1" <? echo (($fdata{'pp_travel_outside_us'} == '1')?' checked ':''); ?>>

          yes 

          <input name="pp_travel_outside_us" type="radio" value="0"  <? echo (($fdata{'pp_travel_outside_us'} == '0')?' checked ':''); ?>>

          no.&nbsp;&nbsp;&nbsp;Location(s): <span class="fibody2">

          <input name="pp_travel_outside_locations" type="text" class="fullin"  style="width:50%" value="<? echo $fdata{'pp_travel_outside_locations'}; ?>">

        </span></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="ih"></a>Personal past history of illnesses <br>

  </h2>

  <div style="border: solid 2px black; background-color: white;">

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

              <td class="fibody2" id="bordR"><input name="pih_ih_asthma" type="radio" value="1" <? echo (($fdata{'pih_ih_asthma'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_asthma_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_asthma_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_asthma" type="radio" value="0"  <? echo (($fdata{'pih_ih_asthma'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_asthma" type="radio" value="2" <? echo (($fdata{'pih_ih_asthma'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_1" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_1'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Pneumonia/lungs disease </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_pneumonia" type="radio" value="1" <? echo (($fdata{'pih_ih_pneumonia'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_pneumonia_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_pneumonia_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_pneumonia" type="radio" value="0"  <? echo (($fdata{'pih_ih_pneumonia'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_pneumonia" type="radio" value="2" <? echo (($fdata{'pih_ih_pneumonia'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_2" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_2'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Kidney infections/stones </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_kidney" type="radio" value="1" <? echo (($fdata{'pih_ih_kidney'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_kidney_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_kidney_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_kidney" type="radio" value="0"  <? echo (($fdata{'pih_ih_kidney'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_kidney" type="radio" value="2" <? echo (($fdata{'pih_ih_kidney'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_3" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_3'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Tuberculosis</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_tuber" type="radio" value="1" <? echo (($fdata{'pih_ih_tuber'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_tuber_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_tuber_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_tuber" type="radio" value="0"  <? echo (($fdata{'pih_ih_tuber'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_tuber" type="radio" value="2" <? echo (($fdata{'pih_ih_tuber'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_4" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_4'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Fibroids</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_fibroids" type="radio" value="1" <? echo (($fdata{'pih_ih_fibroids'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_fibroids_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_fibroids_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_fibroids" type="radio" value="0"  <? echo (($fdata{'pih_ih_fibroids'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_fibroids" type="radio" value="2" <? echo (($fdata{'pih_ih_fibroids'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_5" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_5'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Sexually transmitted disease/chlamydia </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_sexually" type="radio" value="1" <? echo (($fdata{'pih_ih_sexually'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_sexually_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_sexually_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_sexually" type="radio" value="0"  <? echo (($fdata{'pih_ih_sexually'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_sexually" type="radio" value="2" <? echo (($fdata{'pih_ih_sexually'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_6" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_6'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Infertility</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_infertil" type="radio" value="1" <? echo (($fdata{'pih_ih_infertil'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_infertil_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_infertil_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_infertil" type="radio" value="0"  <? echo (($fdata{'pih_ih_infertil'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_infertil" type="radio" value="2" <? echo (($fdata{'pih_ih_infertil'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_7" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_7'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">HIV / AIDS </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_hiv" type="radio" value="1" <? echo (($fdata{'pih_ih_hiv'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_hiv_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_hiv_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_hiv" type="radio" value="0"  <? echo (($fdata{'pih_ih_hiv'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_hiv" type="radio" value="2" <? echo (($fdata{'pih_ih_hiv'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_8" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_8'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Heart attack / Disease </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_heart" type="radio" value="1" <? echo (($fdata{'pih_ih_heart'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_heart_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_heart_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_heart" type="radio" value="0"  <? echo (($fdata{'pih_ih_heart'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_heart" type="radio" value="2" <? echo (($fdata{'pih_ih_heart'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_9" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_9'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Diabetes</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_diabetes" type="radio" value="1" <? echo (($fdata{'pih_ih_diabetes'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_diabetes_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_diabetes_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_diabetes" type="radio" value="0"  <? echo (($fdata{'pih_ih_diabetes'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_diabetes" type="radio" value="2" <? echo (($fdata{'pih_ih_diabetes'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_10" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_10'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">High blood pressure </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_high_pressure" type="radio" value="1" <? echo (($fdata{'pih_ih_high_pressure'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_high_pressure_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_high_pressure_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_high_pressure" type="radio" value="0"  <? echo (($fdata{'pih_ih_high_pressure'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_high_pressure" type="radio" value="2" <? echo (($fdata{'pih_ih_high_pressure'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_11" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_11'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Stroke</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_stroke" type="radio" value="1" <? echo (($fdata{'pih_ih_stroke'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_stroke_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_stroke_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_stroke" type="radio" value="0"  <? echo (($fdata{'pih_ih_stroke'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_stroke" type="radio" value="2" <? echo (($fdata{'pih_ih_stroke'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_12" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_12'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Rheumatic fever </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_rheumatic" type="radio" value="1" <? echo (($fdata{'pih_ih_rheumatic'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_rheumatic_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_rheumatic_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_rheumatic" type="radio" value="0"  <? echo (($fdata{'pih_ih_rheumatic'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_rheumatic" type="radio" value="2" <? echo (($fdata{'pih_ih_rheumatic'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_13" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_13'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Blood clots in lungs or legs </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_blood_clots" type="radio" value="1" <? echo (($fdata{'pih_ih_blood_clots'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_blood_clots_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_blood_clots_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_blood_clots" type="radio" value="0"  <? echo (($fdata{'pih_ih_blood_clots'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_blood_clots" type="radio" value="2" <? echo (($fdata{'pih_ih_blood_clots'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_14" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_14'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Eating disorders </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_eating_disorder" type="radio" value="1" <? echo (($fdata{'pih_ih_eating_disorder'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_eating_disorder_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_eating_disorder_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_eating_disorder" type="radio" value="0"  <? echo (($fdata{'pih_ih_eating_disorder'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_eating_disorder" type="radio" value="2" <? echo (($fdata{'pih_ih_eating_disorder'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_15" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_15'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Autoimmune disease (Lupus)</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_autoimmune" type="radio" value="1" <? echo (($fdata{'pih_ih_autoimmune'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_autoimmune_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_autoimmune_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_autoimmune" type="radio" value="0"  <? echo (($fdata{'pih_ih_autoimmune'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_autoimmune" type="radio" value="2" <? echo (($fdata{'pih_ih_autoimmune'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_16" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_16'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Chickenpox</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_chickenpox" type="radio" value="1" <? echo (($fdata{'pih_ih_chickenpox'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_chickenpox_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_chickenpox_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_chickenpox" type="radio" value="0"  <? echo (($fdata{'pih_ih_chickenpox'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_chickenpox" type="radio" value="2" <? echo (($fdata{'pih_ih_chickenpox'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_17" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_17'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Cancer</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_cancer" type="radio" value="1" <? echo (($fdata{'pih_ih_cancer'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_cancer_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_cancer_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_cancer" type="radio" value="0"  <? echo (($fdata{'pih_ih_cancer'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_cancer" type="radio" value="2" <? echo (($fdata{'pih_ih_cancer'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_18" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_18'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Reflux / Hiatal hernia / Ulcers </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_reflux" type="radio" value="1" <? echo (($fdata{'pih_ih_reflux'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_reflux_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_reflux_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_reflux" type="radio" value="0"  <? echo (($fdata{'pih_ih_reflux'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_reflux" type="radio" value="2" <? echo (($fdata{'pih_ih_reflux'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_19" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_19'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Depression / Anxiety </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_depression" type="radio" value="1" <? echo (($fdata{'pih_ih_depression'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_depression_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_depression_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_depression" type="radio" value="0"  <? echo (($fdata{'pih_ih_depression'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_depression" type="radio" value="2" <? echo (($fdata{'pih_ih_depression'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_20" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_20'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Anemia</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_anemia" type="radio" value="1" <? echo (($fdata{'pih_ih_anemia'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_anemia_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_anemia_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_anemia" type="radio" value="0"  <? echo (($fdata{'pih_ih_anemia'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_anemia" type="radio" value="2" <? echo (($fdata{'pih_ih_anemia'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_21" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_21'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Blood transfusions </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_blood_transf" type="radio" value="1" <? echo (($fdata{'pih_ih_blood_transf'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_blood_transf_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_blood_transf_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_blood_transf" type="radio" value="0"  <? echo (($fdata{'pih_ih_blood_transf'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_blood_transf" type="radio" value="2" <? echo (($fdata{'pih_ih_blood_transf'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_22" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_22'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Seizures / Convulsions /Epilepsy </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_seizures" type="radio" value="1" <? echo (($fdata{'pih_ih_seizures'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_seizures_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_seizures_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_seizures" type="radio" value="0"  <? echo (($fdata{'pih_ih_seizures'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_seizures" type="radio" value="2" <? echo (($fdata{'pih_ih_seizures'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_23" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_23'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Bowel problems </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_bowel_problems" type="radio" value="1" <? echo (($fdata{'pih_ih_bowel_problems'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_bowel_problems_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_bowel_problems_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_bowel_problems" type="radio" value="0"  <? echo (($fdata{'pih_ih_bowel_problems'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_bowel_problems" type="radio" value="2" <? echo (($fdata{'pih_ih_bowel_problems'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_24" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_24'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Glaucoma</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_glaucoma" type="radio" value="1" <? echo (($fdata{'pih_ih_glaucoma'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_glaucoma_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_glaucoma_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_glaucoma" type="radio" value="0"  <? echo (($fdata{'pih_ih_glaucoma'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_glaucoma" type="radio" value="2" <? echo (($fdata{'pih_ih_glaucoma'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_25" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_25'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Cataracts</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_cataracts" type="radio" value="1" <? echo (($fdata{'pih_ih_cataracts'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_cataracts_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_cataracts_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_cataracts" type="radio" value="0"  <? echo (($fdata{'pih_ih_cataracts'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_cataracts" type="radio" value="2" <? echo (($fdata{'pih_ih_cataracts'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_26" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_26'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Arthritis / Joint pain / Back problems </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_joint_pain" type="radio" value="1" <? echo (($fdata{'pih_ih_joint_pain'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_joint_pain_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_joint_pain_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_joint_pain" type="radio" value="0"  <? echo (($fdata{'pih_ih_joint_pain'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_joint_pain" type="radio" value="2" <? echo (($fdata{'pih_ih_joint_pain'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_27" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_27'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Broken bones </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_broken_bones" type="radio" value="1" <? echo (($fdata{'pih_ih_broken_bones'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_broken_bones_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_broken_bones_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_broken_bones" type="radio" value="0"  <? echo (($fdata{'pih_ih_broken_bones'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_broken_bones" type="radio" value="2" <? echo (($fdata{'pih_ih_broken_bones'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_28" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_28'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Hepatitis / Yellow jaundice / Liver disease </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_hepatitis" type="radio" value="1" <? echo (($fdata{'pih_ih_hepatitis'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_hepatitis_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_hepatitis_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_hepatitis" type="radio" value="0"  <? echo (($fdata{'pih_ih_hepatitis'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_hepatitis" type="radio" value="2" <? echo (($fdata{'pih_ih_hepatitis'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_29" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_29'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Thyroid disease </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_thyroid" type="radio" value="1" <? echo (($fdata{'pih_ih_thyroid'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_thyroid_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_thyroid_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_thyroid" type="radio" value="0"  <? echo (($fdata{'pih_ih_thyroid'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_thyroid" type="radio" value="2" <? echo (($fdata{'pih_ih_thyroid'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_30" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_30'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Gallbladder disease </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_galibladder" type="radio" value="1" <? echo (($fdata{'pih_ih_galibladder'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_galibladder_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_galibladder_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_galibladder" type="radio" value="0"  <? echo (($fdata{'pih_ih_galibladder'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_galibladder" type="radio" value="2" <? echo (($fdata{'pih_ih_galibladder'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_31" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_31'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Headaches</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_headaches" type="radio" value="1" <? echo (($fdata{'pih_ih_headaches'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_headaches_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_headaches_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_headaches" type="radio" value="0"  <? echo (($fdata{'pih_ih_headaches'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_headaches" type="radio" value="2" <? echo (($fdata{'pih_ih_headaches'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_32" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_32'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">DES Exposure </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_des" type="radio" value="1" <? echo (($fdata{'pih_ih_des'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_des_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_des_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_des" type="radio" value="0"  <? echo (($fdata{'pih_ih_des'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_des" type="radio" value="2" <? echo (($fdata{'pih_ih_des'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_33" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_33'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">Bleeding disorders </td>

              <td class="fibody2" id="bordR"><input name="pih_ih_bleeding_disorders" type="radio" value="1" <? echo (($fdata{'pih_ih_bleeding_disorders'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_bleeding_disorders_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_bleeding_disorders_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_bleeding_disorders" type="radio" value="0"  <? echo (($fdata{'pih_ih_bleeding_disorders'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_bleeding_disorders" type="radio" value="2" <? echo (($fdata{'pih_ih_bleeding_disorders'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_34" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_34'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td nowrap class="fibody2" id="bordR">other</td>

              <td class="fibody2" id="bordR"><input name="pih_ih_other" type="radio" value="1" <? echo (($fdata{'pih_ih_other'} == '1')?' checked ':''); ?>>

                  <input name="pih_ih_other_date" type="text" class="fullin"  style="width: 70px" value="<? echo $fdata{'pih_ih_other_date'}; ?>"></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_other" type="radio" value="0"  <? echo (($fdata{'pih_ih_other'} == '0')?' checked ':''); ?>></td>

              <td align="center" class="fibody2" id="bordR"><input name="pih_ih_other" type="radio" value="2" <? echo (($fdata{'pih_ih_other'} == '2')?' checked ':''); ?>></td>

              <td class="fibody2"><input name="ih_notes_35" type="text" class="fullin2"  value="<? echo $fdata{'ih_notes_35'}; ?>"></td>

            </tr>

            <tr align="left" valign="bottom">

              <td colspan="5" nowrap class="fibody3"><textarea name="pih_ih_extended_info" rows="4" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'pih_ih_extended_info'}; ?></textarea></td>

            </tr>

        </table></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <h2 align="center"><a name="op"></a>Operations/Hospitalizations<br>

  </h2>

  <div style="border: solid 2px black; background-color: white;">

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

        <td align="left" valign="bottom" class="fibody2" id="bordR"><input name="op_reason_${ii}" value="${op_reason}" type="text" class="fullin2"></td>

        <td align="left" valign="bottom" class="fibody2" id="bordR"><input name="op_date_${ii}" value="${op_date}" type="text" class="fullin2"></td>

        <td align="left" valign="bottom" class="fibody2"><input name="op_hospital_${ii}" value="${op_hospital}" type="text" class="fullin2"></td>

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

  <div style="border: solid 2px black; background-color: white;">

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

     <td align="left" class="fibody2" id="bordR"><input name="ii_type_${ii}" value="${ii_type}" type="text" class="fullin2"></td>

     <td align="left" nowrap class="fibody2" id="bordR"><input name="ii_date_${ii}" value="${ii_date}" type="text" class="fullin2"></td>

     <td align="left" class="fibody2" id="bordR"><input name="ii_type_${ij}" value="${ii_type1}" type="text" class="fullin2"></td>

     <td align="left" nowrap class="fibody2"><input name="ii_date_${ij}" value="${ii_date1}" type="text" class="fullin2"></td>

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

  <div style="border: solid 2px black; background-color: white;">

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

          <input name="imm_tetanus" type="text" class="fullin2"  value="<? echo $fdata{'imm_tetanus'}; ?>">

        </td>

        <td align="left" nowrap class="fibody2" id="bordR">Influenza vaccine (Flu shot) </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <input name="imm_influenza" type="text" class="fullin2"  value="<? echo $fdata{'imm_influenza'}; ?>">

        </td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">hepatitis a vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <input name="imm_hepatitis_a" type="text" class="fullin2"  value="<? echo $fdata{'imm_hepatitis_a'}; ?>">

        </td>

        <td align="left" nowrap class="fibody2" id="bordR">Hepatitis B vaccine </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <input name="imm_hepatitis_b" type="text" class="fullin2"  value="<? echo $fdata{'imm_hepatitis_b'}; ?>">

        </td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">varicella (Chickenpox) vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <input name="imm_varicella" type="text" class="fullin2"  value="<? echo $fdata{'imm_varicella'}; ?>">

        </td>

        <td align="left" nowrap class="fibody2" id="bordR">pneumococcal (pneumonia) vaccine </td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <input name="imm_pneumococcal" type="text" class="fullin2"  value="<? echo $fdata{'imm_pneumococcal'}; ?>">

        </td>

      </tr>

      <tr valign="bottom">

        <td align="left" nowrap class="fibody2" id="bordR">Measles-Mumps-Rubella (MMR) Vaccine </td>

        <td align="left" nowrap class="fibody2" id="bordR">

          <input name="imm_mmr" type="text" class="fullin2"  value="<? echo $fdata{'imm_mmr'}; ?>">

        </td>

        <td align="left" nowrap class="fibody2" id="bordR">Tuberculosis (TB) Skin test:

        <input name="imm_tuberculosis_skin" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'imm_tuberculosis_skin'}; ?>">

        , result: 

        <input name="imm_tuberculosis_result" type="text" class="fullin"  style="width:40px" value="<? echo $fdata{'imm_tuberculosis_result'}; ?>"></td>

        <td align="left" valign="bottom" nowrap class="fibody2">

          <input name="imm_tuberculosis" type="text" class="fullin2"  value="<? echo $fdata{'imm_tuberculosis'}; ?>">

        </td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr>

        <td align="left" valign="top" class="fibody3">Physician's notes: <br>

        <textarea name="imm_extended_info" rows="6" wrap="VIRTUAL" class="fullin2" ><? echo $fdata{'imm_extended_info'}; ?></textarea></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <a name="ros">

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_loss_now" value="1" <? echo (($fros{'ros_weight_loss_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_loss_now" value="2" <? echo (($fros{'ros_weight_loss_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_loss_now" value="3" <? echo (($fros{'ros_weight_loss_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_1"   value="<? echo $fros{'ros_notes_1'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Weight gain </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_gain_now" value="1" <? echo (($fros{'ros_weight_gain_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_gain_now" value="2" <? echo (($fros{'ros_weight_gain_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_weight_gain_now" value="3" <? echo (($fros{'ros_weight_gain_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_2"   value="<? echo $fros{'ros_notes_2'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Fever</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fever_now" value="1" <? echo (($fros{'ros_fever_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fever_now" value="2" <? echo (($fros{'ros_fever_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fever_now" value="3" <? echo (($fros{'ros_fever_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_3"   value="<? echo $fros{'ros_notes_3'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Fatigue</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fatigue_now" value="1" <? echo (($fros{'ros_fatigue_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fatigue_now" value="2" <? echo (($fros{'ros_fatigue_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_fatigue_now" value="3" <? echo (($fros{'ros_fatigue_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_4"   value="<? echo $fros{'ros_notes_4'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Change in height </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_height_change_now" value="1" <? echo (($fros{'ros_height_change_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_height_change_now" value="2" <? echo (($fros{'ros_height_change_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_height_change_now" value="3" <? echo (($fros{'ros_height_change_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_5"   value="<? echo $fros{'ros_notes_5'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dvision_now" value="1" <? echo (($fros{'ros_dvision_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dvision_now" value="2" <? echo (($fros{'ros_dvision_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dvision_now" value="3" <? echo (($fros{'ros_dvision_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_6"   value="<? echo $fros{'ros_notes_6'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Spots before eyes </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spots_eyes_now" value="1" <? echo (($fros{'ros_spots_eyes_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spots_eyes_now" value="2" <? echo (($fros{'ros_spots_eyes_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spots_eyes_now" value="3" <? echo (($fros{'ros_spots_eyes_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_7"   value="<? echo $fros{'ros_notes_7'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Vision changes </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vis_changes_now" value="1" <? echo (($fros{'ros_vis_changes_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vis_changes_now" value="2" <? echo (($fros{'ros_vis_changes_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vis_changes_now" value="3" <? echo (($fros{'ros_vis_changes_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_8"   value="<? echo $fros{'ros_notes_8'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Glasses/contacts</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_glasses_now" value="1" <? echo (($fros{'ros_glasses_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_glasses_now" value="2" <? echo (($fros{'ros_glasses_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_glasses_now" value="3" <? echo (($fros{'ros_glasses_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_9"   value="<? echo $fros{'ros_notes_9'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_earaches_now" value="1" <? echo (($fros{'ros_earaches_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_earaches_now" value="2" <? echo (($fros{'ros_earaches_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_earaches_now" value="3" <? echo (($fros{'ros_earaches_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_10"   value="<? echo $fros{'ros_notes_10'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Ringing in ears </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_ringing_now" value="1" <? echo (($fros{'ros_ringing_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_ringing_now" value="2" <? echo (($fros{'ros_ringing_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_ringing_now" value="3" <? echo (($fros{'ros_ringing_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_11"   value="<? echo $fros{'ros_notes_11'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Hearing problems</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hearing_problems_now" value="1" <? echo (($fros{'ros_hearing_problems_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hearing_problems_now" value="2" <? echo (($fros{'ros_hearing_problems_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hearing_problems_now" value="3" <? echo (($fros{'ros_hearing_problems_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_12"   value="<? echo $fros{'ros_notes_12'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sinus problems </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sinus_problems_now" value="1" <? echo (($fros{'ros_sinus_problems_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sinus_problems_now" value="2" <? echo (($fros{'ros_sinus_problems_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sinus_problems_now" value="3" <? echo (($fros{'ros_sinus_problems_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_13"   value="<? echo $fros{'ros_notes_13'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sore throat </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sore_throat_now" value="1" <? echo (($fros{'ros_sore_throat_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sore_throat_now" value="2" <? echo (($fros{'ros_sore_throat_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sore_throat_now" value="3" <? echo (($fros{'ros_sore_throat_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_14"   value="<? echo $fros{'ros_notes_14'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Mouth sores </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_mouth_sores_now" value="1" <? echo (($fros{'ros_mouth_sores_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_mouth_sores_now" value="2" <? echo (($fros{'ros_mouth_sores_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_mouth_sores_now" value="3" <? echo (($fros{'ros_mouth_sores_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_15"   value="<? echo $fros{'ros_notes_15'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Dental problems </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dental_problems_now" value="1" <? echo (($fros{'ros_dental_problems_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dental_problems_now" value="2" <? echo (($fros{'ros_dental_problems_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dental_problems_now" value="3" <? echo (($fros{'ros_dental_problems_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_16"   value="<? echo $fros{'ros_notes_16'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chest_pain_now" value="1" <? echo (($fros{'ros_chest_pain_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chest_pain_now" value="2" <? echo (($fros{'ros_chest_pain_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chest_pain_now" value="3" <? echo (($fros{'ros_chest_pain_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_17"   value="<? echo $fros{'ros_notes_17'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Difficulty breathing on exertion </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_difficulty_breathing_now" value="1" <? echo (($fros{'ros_difficulty_breathing_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_difficulty_breathing_now" value="2" <? echo (($fros{'ros_difficulty_breathing_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_difficulty_breathing_now" value="3" <? echo (($fros{'ros_difficulty_breathing_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_18"   value="<? echo $fros{'ros_notes_18'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Swelling on legs </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_swelling_legs_now" value="1" <? echo (($fros{'ros_swelling_legs_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_swelling_legs_now" value="2" <? echo (($fros{'ros_swelling_legs_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_swelling_legs_now" value="3" <? echo (($fros{'ros_swelling_legs_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_19"   value="<? echo $fros{'ros_notes_19'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Rapid or irregular heartbeat </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rapid_heartbeat_now" value="1" <? echo (($fros{'ros_rapid_heartbeat_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rapid_heartbeat_now" value="2" <? echo (($fros{'ros_rapid_heartbeat_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rapid_heartbeat_now" value="3" <? echo (($fros{'ros_rapid_heartbeat_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_20"   value="<? echo $fros{'ros_notes_20'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_breathing_now" value="1" <? echo (($fros{'ros_painful_breathing_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_breathing_now" value="2" <? echo (($fros{'ros_painful_breathing_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_breathing_now" value="3" <? echo (($fros{'ros_painful_breathing_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_21"   value="<? echo $fros{'ros_notes_21'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Wheezing</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_wheezing_now" value="1" <? echo (($fros{'ros_wheezing_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_wheezing_now" value="2" <? echo (($fros{'ros_wheezing_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_wheezing_now" value="3" <? echo (($fros{'ros_wheezing_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_22"   value="<? echo $fros{'ros_notes_22'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Spitting up blood </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spitting_blood_now" value="1" <? echo (($fros{'ros_spitting_blood_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spitting_blood_now" value="2" <? echo (($fros{'ros_spitting_blood_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_spitting_blood_now" value="3" <? echo (($fros{'ros_spitting_blood_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_23"   value="<? echo $fros{'ros_notes_23'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Shortness of breath </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_breath_shortness_now" value="1" <? echo (($fros{'ros_breath_shortness_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_breath_shortness_now" value="2" <? echo (($fros{'ros_breath_shortness_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_breath_shortness_now" value="3" <? echo (($fros{'ros_breath_shortness_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_24"   value="<? echo $fros{'ros_notes_24'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Chronic cough </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chronic_cough_now" value="1" <? echo (($fros{'ros_chronic_cough_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chronic_cough_now" value="2" <? echo (($fros{'ros_chronic_cough_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_chronic_cough_now" value="3" <? echo (($fros{'ros_chronic_cough_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_25"   value="<? echo $fros{'ros_notes_25'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_diarrhea_now" value="1" <? echo (($fros{'ros_diarrhea_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_diarrhea_now" value="2" <? echo (($fros{'ros_diarrhea_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_diarrhea_now" value="3" <? echo (($fros{'ros_diarrhea_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_26"   value="<? echo $fros{'ros_notes_26'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Bloody stool </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_bloody_stool_now" value="1" <? echo (($fros{'ros_bloody_stool_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_bloody_stool_now" value="2" <? echo (($fros{'ros_bloody_stool_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_bloody_stool_now" value="3" <? echo (($fros{'ros_bloody_stool_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_27"   value="<? echo $fros{'ros_notes_27'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Nausea / vomiting indigestion </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nausea_now" value="1" <? echo (($fros{'ros_nausea_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nausea_now" value="2" <? echo (($fros{'ros_nausea_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nausea_now" value="3" <? echo (($fros{'ros_nausea_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_28"   value="<? echo $fros{'ros_notes_28'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Constipation</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_constipation_now" value="1" <? echo (($fros{'ros_constipation_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_constipation_now" value="2" <? echo (($fros{'ros_constipation_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_constipation_now" value="3" <? echo (($fros{'ros_constipation_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_29"   value="<? echo $fros{'ros_notes_29'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Involuntary loss of gas or stool </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_gas_loss_now" value="1" <? echo (($fros{'ros_gas_loss_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_gas_loss_now" value="2" <? echo (($fros{'ros_gas_loss_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_gas_loss_now" value="3" <? echo (($fros{'ros_gas_loss_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_30"   value="<? echo $fros{'ros_notes_30'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_blood_urine_now" value="1" <? echo (($fros{'ros_blood_urine_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_blood_urine_now" value="2" <? echo (($fros{'ros_blood_urine_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_blood_urine_now" value="3" <? echo (($fros{'ros_blood_urine_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_31"   value="<? echo $fros{'ros_notes_31'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Pain with urination </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_urination_now" value="1" <? echo (($fros{'ros_pain_urination_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_urination_now" value="2" <? echo (($fros{'ros_pain_urination_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_urination_now" value="3" <? echo (($fros{'ros_pain_urination_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_32"   value="<? echo $fros{'ros_notes_32'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Strong urgency to urinate </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_urgency_urinate_now" value="1" <? echo (($fros{'ros_urgency_urinate_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_urgency_urinate_now" value="2" <? echo (($fros{'ros_urgency_urinate_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_urgency_urinate_now" value="3" <? echo (($fros{'ros_urgency_urinate_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_33"   value="<? echo $fros{'ros_notes_33'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent urination </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_urination_now" value="1" <? echo (($fros{'ros_frequent_urination_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_urination_now" value="2" <? echo (($fros{'ros_frequent_urination_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_urination_now" value="3" <? echo (($fros{'ros_frequent_urination_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_34"   value="<? echo $fros{'ros_notes_34'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Incomplete emtying </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_incomplete_emptying_now" value="1" <? echo (($fros{'ros_incomplete_emptying_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_incomplete_emptying_now" value="2" <? echo (($fros{'ros_incomplete_emptying_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_incomplete_emptying_now" value="3" <? echo (($fros{'ros_incomplete_emptying_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_35"   value="<? echo $fros{'ros_notes_35'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Involuntary/Unintended urine loss </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_unint_urine_loss_now" value="1" <? echo (($fros{'ros_unint_urine_loss_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_unint_urine_loss_now" value="2" <? echo (($fros{'ros_unint_urine_loss_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_unint_urine_loss_now" value="3" <? echo (($fros{'ros_unint_urine_loss_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_36"   value="<? echo $fros{'ros_notes_36'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Urine loss when coughing or lifting </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lifting_urine_loss_now" value="1" <? echo (($fros{'ros_lifting_urine_loss_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lifting_urine_loss_now" value="2" <? echo (($fros{'ros_lifting_urine_loss_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lifting_urine_loss_now" value="3" <? echo (($fros{'ros_lifting_urine_loss_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_37"   value="<? echo $fros{'ros_notes_37'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal bleeding</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_bleeding_now" value="1" <? echo (($fros{'ros_abnormal_bleeding_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_bleeding_now" value="2" <? echo (($fros{'ros_abnormal_bleeding_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_bleeding_now" value="3" <? echo (($fros{'ros_abnormal_bleeding_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_38"   value="<? echo $fros{'ros_notes_38'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Painful periods </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_periods_now" value="1" <? echo (($fros{'ros_painful_periods_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_periods_now" value="2" <? echo (($fros{'ros_painful_periods_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_periods_now" value="3" <? echo (($fros{'ros_painful_periods_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_39"   value="<? echo $fros{'ros_notes_39'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Premenstrual Syndrome (PMS) </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pms_now" value="1" <? echo (($fros{'ros_pms_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pms_now" value="2" <? echo (($fros{'ros_pms_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pms_now" value="3" <? echo (($fros{'ros_pms_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_40"   value="<? echo $fros{'ros_notes_40'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Painful intercourse </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_intercourse_now" value="1" <? echo (($fros{'ros_painful_intercourse_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_intercourse_now" value="2" <? echo (($fros{'ros_painful_intercourse_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_painful_intercourse_now" value="3" <? echo (($fros{'ros_painful_intercourse_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_41"   value="<? echo $fros{'ros_notes_41'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal vaginal discharge </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vaginal_discharge_now" value="1" <? echo (($fros{'ros_vaginal_discharge_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vaginal_discharge_now" value="2" <? echo (($fros{'ros_vaginal_discharge_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_vaginal_discharge_now" value="3" <? echo (($fros{'ros_vaginal_discharge_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_42"   value="<? echo $fros{'ros_notes_42'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_weakness_now" value="1" <? echo (($fros{'ros_muscle_weakness_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_weakness_now" value="2" <? echo (($fros{'ros_muscle_weakness_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_weakness_now" value="3" <? echo (($fros{'ros_muscle_weakness_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_43"   value="<? echo $fros{'ros_notes_43'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Muscle or joint pain </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_pain_now" value="1" <? echo (($fros{'ros_muscle_pain_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_pain_now" value="2" <? echo (($fros{'ros_muscle_pain_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_muscle_pain_now" value="3" <? echo (($fros{'ros_muscle_pain_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_44"   value="<? echo $fros{'ros_notes_44'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rash_now" value="1" <? echo (($fros{'ros_rash_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rash_now" value="2" <? echo (($fros{'ros_rash_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_rash_now" value="3" <? echo (($fros{'ros_rash_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_45"   value="<? echo $fros{'ros_notes_45'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Sores</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sores_now" value="1" <? echo (($fros{'ros_sores_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sores_now" value="2" <? echo (($fros{'ros_sores_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_sores_now" value="3" <? echo (($fros{'ros_sores_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_46"   value="<? echo $fros{'ros_notes_46'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Dry skin </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dry_skin_now" value="1" <? echo (($fros{'ros_dry_skin_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dry_skin_now" value="2" <? echo (($fros{'ros_dry_skin_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dry_skin_now" value="3" <? echo (($fros{'ros_dry_skin_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_47"   value="<? echo $fros{'ros_notes_47'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Moles (growth or changes) </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_moles_now" value="1" <? echo (($fros{'ros_moles_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_moles_now" value="2" <? echo (($fros{'ros_moles_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_moles_now" value="3" <? echo (($fros{'ros_moles_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_48"   value="<? echo $fros{'ros_notes_48'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_breast_now" value="1" <? echo (($fros{'ros_pain_breast_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_breast_now" value="2" <? echo (($fros{'ros_pain_breast_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_pain_breast_now" value="3" <? echo (($fros{'ros_pain_breast_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_49"   value="<? echo $fros{'ros_notes_49'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Nipple discharge </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nipple_discharge_now" value="1" <? echo (($fros{'ros_nipple_discharge_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nipple_discharge_now" value="2" <? echo (($fros{'ros_nipple_discharge_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_nipple_discharge_now" value="3" <? echo (($fros{'ros_nipple_discharge_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_50"   value="<? echo $fros{'ros_notes_50'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Lumps</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lumps_now" value="1" <? echo (($fros{'ros_lumps_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lumps_now" value="2" <? echo (($fros{'ros_lumps_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_lumps_now" value="3" <? echo (($fros{'ros_lumps_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_51"   value="<? echo $fros{'ros_notes_51'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dizziness_now" value="1" <? echo (($fros{'ros_dizziness_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dizziness_now" value="2" <? echo (($fros{'ros_dizziness_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_dizziness_now" value="3" <? echo (($fros{'ros_dizziness_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_52"   value="<? echo $fros{'ros_notes_52'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Seizures</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_seizures_now" value="1" <? echo (($fros{'ros_seizures_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_seizures_now" value="2" <? echo (($fros{'ros_seizures_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_seizures_now" value="3" <? echo (($fros{'ros_seizures_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_53"   value="<? echo $fros{'ros_notes_53'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Numbness</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_numbness_now" value="1" <? echo (($fros{'ros_numbness_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_numbness_now" value="2" <? echo (($fros{'ros_numbness_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_numbness_now" value="3" <? echo (($fros{'ros_numbness_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_54"   value="<? echo $fros{'ros_notes_54'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Trouble walking </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_trouble_walking_now" value="1" <? echo (($fros{'ros_trouble_walking_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_trouble_walking_now" value="2" <? echo (($fros{'ros_trouble_walking_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_trouble_walking_now" value="3" <? echo (($fros{'ros_trouble_walking_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_55"   value="<? echo $fros{'ros_notes_55'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Memory problems </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_memory_problems_now" value="1" <? echo (($fros{'ros_memory_problems_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_memory_problems_now" value="2" <? echo (($fros{'ros_memory_problems_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_memory_problems_now" value="3" <? echo (($fros{'ros_memory_problems_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_56"   value="<? echo $fros{'ros_notes_56'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Frequent headaches </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_freq_headaches_now" value="1" <? echo (($fros{'ros_freq_headaches_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_freq_headaches_now" value="2" <? echo (($fros{'ros_freq_headaches_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_freq_headaches_now" value="3" <? echo (($fros{'ros_freq_headaches_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_57"   value="<? echo $fros{'ros_notes_57'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_depression_now" value="1" <? echo (($fros{'ros_depression_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_depression_now" value="2" <? echo (($fros{'ros_depression_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_depression_now" value="3" <? echo (($fros{'ros_depression_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_58"   value="<? echo $fros{'ros_notes_58'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Anxiety</td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_anxiety_now" value="1" <? echo (($fros{'ros_anxiety_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_anxiety_now" value="2" <? echo (($fros{'ros_anxiety_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_anxiety_now" value="3" <? echo (($fros{'ros_anxiety_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_59"   value="<? echo $fros{'ros_notes_59'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hair_loss_now" value="1" <? echo (($fros{'ros_hair_loss_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hair_loss_now" value="2" <? echo (($fros{'ros_hair_loss_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hair_loss_now" value="3" <? echo (($fros{'ros_hair_loss_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_60"   value="<? echo $fros{'ros_notes_60'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Heat/cold intolerance </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_heat_cold_intolerance_now" value="1" <? echo (($fros{'ros_heat_cold_intolerance_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_heat_cold_intolerance_now" value="2" <? echo (($fros{'ros_heat_cold_intolerance_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_heat_cold_intolerance_now" value="3" <? echo (($fros{'ros_heat_cold_intolerance_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_61"   value="<? echo $fros{'ros_notes_61'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Abnormal thirst </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_thirst_now" value="1" <? echo (($fros{'ros_abnormal_thirst_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_thirst_now" value="2" <? echo (($fros{'ros_abnormal_thirst_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_abnormal_thirst_now" value="3" <? echo (($fros{'ros_abnormal_thirst_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_62"   value="<? echo $fros{'ros_notes_62'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Hot flashes </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hot_flashes_now" value="1" <? echo (($fros{'ros_hot_flashes_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hot_flashes_now" value="2" <? echo (($fros{'ros_hot_flashes_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_hot_flashes_now" value="3" <? echo (($fros{'ros_hot_flashes_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_63"   value="<? echo $fros{'ros_notes_63'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_bruises_now" value="1" <? echo (($fros{'ros_frequent_bruises_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_bruises_now" value="2" <? echo (($fros{'ros_frequent_bruises_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_frequent_bruises_now" value="3" <? echo (($fros{'ros_frequent_bruises_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_64"   value="<? echo $fros{'ros_notes_64'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Cuts do not stop bleeding </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_cuts_bleeding_now" value="1" <? echo (($fros{'ros_cuts_bleeding_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_cuts_bleeding_now" value="2" <? echo (($fros{'ros_cuts_bleeding_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_cuts_bleeding_now" value="3" <? echo (($fros{'ros_cuts_bleeding_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_65"   value="<? echo $fros{'ros_notes_65'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Enlarged Lymph nodes (glands) </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_enlarged_nodes_now" value="1" <? echo (($fros{'ros_enlarged_nodes_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_enlarged_nodes_now" value="2" <? echo (($fros{'ros_enlarged_nodes_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_enlarged_nodes_now" value="3" <? echo (($fros{'ros_enlarged_nodes_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_66"   value="<? echo $fros{'ros_notes_66'}; ?>"></td>

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

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_med_allergy_now" value="1" <? echo (($fros{'ros_med_allergy_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_med_allergy_now" value="2" <? echo (($fros{'ros_med_allergy_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_med_allergy_now" value="3" <? echo (($fros{'ros_med_allergy_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_68"   value="<? echo $fros{'ros_notes_68'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >If any, please list allergy and type of reaction: </td>

        <td colspan="4" align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_med_allergy_reaction"   value="<? echo $fros{'ros_med_allergy_reaction'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Latex allergy </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_latex_allergy_now" value="1" <? echo (($fros{'ros_latex_allergy_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_latex_allergy_now" value="2" <? echo (($fros{'ros_latex_allergy_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_latex_allergy_now" value="3" <? echo (($fros{'ros_latex_allergy_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_69"   value="<? echo $fros{'ros_notes_69'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Other allergies </td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_other_allergy_now" value="1" <? echo (($fros{'ros_other_allergy_now'} == '1')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_other_allergy_now" value="2" <? echo (($fros{'ros_other_allergy_now'} == '2')?' checked ':''); ?>></td>

        <td align="center" class="fibody2"  id="bordR" ><input  type="radio" name="ros_other_allergy_now" value="3" <? echo (($fros{'ros_other_allergy_now'} == '3')?' checked ':''); ?>></td>

        <td align="left" class="fibody2"><input  type="text" class="fullin2"   name="ros_notes_70"   value="<? echo $fros{'ros_notes_70'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td align="left" class="fibody4"  id="bordR" >Please list allergy and type of reaction: </td>

        <td colspan="4" align="left" class="fibody2"><input type="text" class="fullin2"   name="ros_other_allergy_reaction_"   value="<? echo $fros{'ros_other_allergy_reaction_'}; ?>"></td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

  <div style="border: solid 2px black; background-color: white;">

    <table width="100%"  border="0" cellspacing="0" cellpadding="2">

      <tr valign="bottom">

        <td colspan="2" align="left" class="fibody2">Form completed by 

          <input name="pih_completed_by" type="radio" value="patient"  <? echo (($fdata{'pih_completed_by'} == 'patient')?' checked ':''); ?>>

        patient 

        <input name="pih_completed_by" type="radio" value="nurse" <? echo (($fdata{'pih_completed_by'} == 'nurse')?' checked ':''); ?>>

        office nurse 

       <input name="pih_completed_by" type="radio" value="physician" <? echo (($fdata{'pih_completed_by'} == 'physician')?' checked ':''); ?>>

        physician 

        <input name="pih_completed_by" type="radio" value="other" <? echo (($fdata{'pih_completed_by'} == 'other')?' checked ':''); ?>>

        other: 

        <input name="pih_completed_by_other" type="text" class="fullin"  style="width: 40%" value="<? echo $fdata{'pih_completed_by_other'}; ?>"></td>

      </tr>

      <tr valign="bottom">

        <td height="46" colspan="2" align="left" class="fibody2">Signature of patient:</td>

      </tr>

      <tr valign="bottom">

        <td width="39%" height="46" align="left" class="fibody3" id="bordR">Date reviewed by physician with patient 

        <input name="pih_date_reviewed_1" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_1'}; ?>"></td>

        <td width="61%" height="46" align="left" class="fibody3">Physician signature: </td>

      </tr>

      <tr valign="bottom">

        <td colspan="2" align="left" class="ficaption3" style="border-top: 2px solid black; border-bottom: 2px solid black;">Annual review of history </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <input name="pih_date_reviewed_2" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_2'}; ?>">

        </span></td>

        <td height="46" align="left" class="fibody2">Physician signature: </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <input name="pih_date_reviewed_3" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_3'}; ?>">

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <input name="pih_date_reviewed_4" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_4'}; ?>">

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <input name="pih_date_reviewed_5" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_5'}; ?>">

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

      <tr valign="bottom">

        <td height="46" align="left" class="fibody2" id="bordR">Date reviewed: <span class="fibody3">

          <input name="pih_date_reviewed_6" type="text" class="fullin"  style="width:70px" value="<? echo $fdata{'pih_date_reviewed_6'}; ?>">

        </span> </td>

        <td height="46" align="left" class="fibody2">Physician signature:  </td>

      </tr>

    </table>

  </div>

  <p align="center">&nbsp;</p>

<table width="100%" border="0">

  <tr><td align="left" width="120"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>

  <td align="left" nowrap> <a href="<? echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<? echo $id; ?>&formname=<? echo $formname; ?>"

   target="_blank" class="link_submit" onclick="top.restoreSession()">[Printable form]</a> </td>

  <td align="right"> <a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link_submit"

   onclick="top.restoreSession()">[Don't Save]</a> </td></tr>

</table>

</form>

<?php

formFooter();

?>

</body>

</html>