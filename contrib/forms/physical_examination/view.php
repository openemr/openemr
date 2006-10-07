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
<title>Physical examination</title>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
window.onload = initialize;
</script>
</head>

<body <?echo $top_bg_line;?>>

<?
$tip1 = <<<EOL
The <b>Physical Examination</b> section
should be completed by the physician each time a
physical examination is provided. The form offers prompts to
aid in documenting the services that are provided. This form
is based on the 1997 CMS (formerly, HCFA) guidelines for
the female genitourinary system examination and can be
used to document any level of examination.<br><br>

The female genitourinary examination template includes 9 organ systems/body areas with 3 shaded boxes and 6 unshaded boxes. The shading only becomes important when a comprehensive
examination is performed. For all other levels of examination, the total number of bulleted elements documented in the medical record will determine the level that can be
reported.<br><br>
<table border=1>
<tr><td>LEVEL OF EXAMINATION</td><td>PERFORM AND DOCUMENT</td></tr>
<tr><td>PROBLEM FOCUSED</td><td>1–5 ELEMENTS IDENTIFIED BY A BULLET</td></tr>
<tr><td>EXPANDED PROBLEM FOCUSED</td><td>6–11 ELEMENTS IDENTIFIED BY A BULLET</td></tr>
<tr><td>DETAILED</td><td>12 OR MORE ELEMENTS IDENTIFIED BY A BULLET</td></tr>
<tr><td>COMPREHENSIVE</td><td>ALL ELEMENTS IDENTIFIED BY A BULLET IN CONSTITUTIONAL AND GASTROINTESTINAL,
ANY 7 BULLETS IN GYNECOLOGIC, AT LEAST 1 BULLET IN ALL OTHER SYSTEMS</td></tr>
</table>
EOL;
$tip1 = strtr($tip1, "\n\r", "  ");
?>
<div class="srvChapter">Physical examination <a href="#" onMouseOver="toolTip('<? echo $tip1; ?>', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>
<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   $fres=sqlStatement("select * from form_physical_examination where id=$id");
   if ($fres){
     $fdata = sqlFetchArray($fres);
   }
?>
<form action="<?echo $rootdir;?>/forms/physical_examination/save.php?mode=update&id=<? echo $id; ?>" method="post" enctype="multipart/form-data" name="my_form">
<? include("../../acog_menu.inc"); ?>
<div style="border: solid 2px black; background-color: white;">
<table width="100%"  border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td align="left" valign="top" style="border-bottom: 2px solid black;"><table width="100%"  border="0" cellspacing="0" cellpadding="5">
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
          <input name="pe_id" type="text" class="fullin" id="pe_id" size="12" value="<?
          echo $patient{'id'};
          ?>"></td>
        <td width="20%">date<br>
        <input name="pe_date" type="text" class="fullin" id="pe_date" value="<?
        echo date('Y-m-d');
        ?>" size="12"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#EDEDED"><h2>Constitutional</h2> 
      <li>Vital signs (record <u><strong>&gt;</strong></u> 3 vital signs)</li> 
      <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr align="left" valign="top" class="fibody">
          <td width="14%" nowrap>Height<br>
            <input name="height" type="text" class="fullin"  value="<? echo $fdata{'height'}; ?>"></td>
          <td width="14%" nowrap>Weight<br>
            <input name="weight" type="text" class="fullin"  value="<? echo $fdata{'weight'}; ?>"></td>
          <td width="14%" nowrap>BMI<br>
            <input name="bmi" type="text" class="fullin"  value="<? echo $fdata{'bmi'}; ?>"></td>
          <td nowrap>Blood pressure (sitting) <br>
            <input name="blood_pressure" type="text" class="fullin"  value="<? echo $fdata{'blood_pressure'}; ?>"></td>
          <td width="14%" nowrap>Temperature<br>
            <input name="temperature" type="text" class="fullin"  value="<? echo $fdata{'temperature'}; ?>"></td>
          <td width="14%" nowrap>Pulse<br>
            <input name="pulse" type="text" class="fullin"  value="<? echo $fdata{'pulse'}; ?>"></td>
          <td width="14%" nowrap>Respiration<br>
            <input name="respiration" type="text" class="fullin"  value="<? echo $fdata{'respiration'}; ?>"></td>
        </tr>
      </table> 
      <li>General appearance (Note all that apply):</li> <table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr class="fibody">
          <td width="150" nowrap><input name="general_well_developed" type="radio" value="0"  <? echo (($fdata{'general_well_developed'} == '0')?' checked ':''); ?>> 
            Well-developed
</td>
          <td width="90" nowrap><input name="general_well_developed" type="radio" value="1" <? echo (($fdata{'general_well_developed'} == '1')?' checked ':''); ?>> 
            Other
</td>
          <td width="100" nowrap>&nbsp;</td>
          <td>&nbsp;</td>
          <td width="120"><input name="general_no_deformities" type="radio" value="0"  <? echo (($fdata{'general_no_deformities'} == '0')?' checked ':''); ?>> 
            No deformities
</td>
          <td width="100"><input name="general_no_deformities" type="radio" value="1" <? echo (($fdata{'general_no_deformities'} == '1')?' checked ':''); ?>> 
            Other
</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="fibody">
          <td width="150" nowrap><input name="general_well_nourished" type="radio" value="0"  <? echo (($fdata{'general_well_nourished'} == '0')?' checked ':''); ?>> 
            Well-nourished
</td>
          <td width="90" nowrap><input name="general_well_nourished" type="radio" value="1" <? echo (($fdata{'general_well_nourished'} == '1')?' checked ':''); ?>> 
            Other
</td>
          <td width="100" nowrap>&nbsp;</td>
          <td>&nbsp;</td>
          <td width="120"><input name="general_well_groomed" type="radio" value="0"  <? echo (($fdata{'general_well_groomed'} == '0')?' checked ':''); ?>> 
            Well-groomed
</td>
          <td width="100"><input name="general_well_groomed" type="radio" value="1" <? echo (($fdata{'general_well_groomed'} == '1')?' checked ':''); ?>> 
            Other
</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="fibody">
          <td width="150" nowrap><input name="general_normal_habitus" type="radio" value="0"  <? echo (($fdata{'general_normal_habitus'} == '0')?' checked ':''); ?>> 
            Normal habitus</td>
          <td width="90" nowrap><input name="general_normal_habitus" type="radio" value="2" <? echo (($fdata{'general_normal_habitus'} == '2')?' checked ':''); ?>> 
            Obese</td>
          <td width="100" nowrap><input name="general_normal_habitus" type="radio" value="1" <? echo (($fdata{'general_normal_habitus'} == '1')?' checked ':''); ?>> 
            Other</td>
          <td>&nbsp;</td>
          <td width="120">&nbsp;</td>
          <td width="100">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Neck</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Neck</li></td>
          <td width="90" nowrap> <input name="neck_neck" type="radio" value="0"  <? echo (($fdata{'neck_neck'} == '0')?' checked ':''); ?>>
      Normal</td>
          <td width="100" nowrap><input name="neck_neck" type="radio" value="1" <? echo (($fdata{'neck_neck'} == '1')?' checked ':''); ?>>
            ABNormal</td>
          <td><input name="neck_neck_data" type="text" class="fullin"  value="<? echo $fdata{'neck_neck_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Thyroid</li></td>
          <td width="90" nowrap>
            <input name="neck_thyroid" type="radio" value="0"  <? echo (($fdata{'neck_thyroid'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td width="100" nowrap><input name="neck_thyroid" type="radio" value="1" <? echo (($fdata{'neck_thyroid'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="neck_thyroid_data" type="text" class="fullin"  value="<? echo $fdata{'neck_thyroid_data'}; ?>"></td>
          </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Respiratory</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Respiratory effort </li></td>
          <td nowrap>
            <input name="respiratory_effort" type="radio" value="0"  <? echo (($fdata{'respiratory_effort'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="respiratory_effort" type="radio" value="1" <? echo (($fdata{'respiratory_effort'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="respiratory_effort_data" type="text" class="fullin"  value="<? echo $fdata{'respiratory_effort_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Auscultated lungs </li></td>
          <td width="90" nowrap><input name="auscultated_lungs" type="radio" value="0"  <? echo (($fdata{'auscultated_lungs'} == '0')?' checked ':''); ?>>
Normal</td>
          <td width="100" nowrap><input name="auscultated_lungs" type="radio" value="1" <? echo (($fdata{'auscultated_lungs'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="auscultated_lungs_data" type="text" class="fullin"  value="<? echo $fdata{'auscultated_lungs_data'}; ?>"></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Cardiovascular</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Auscultated heart </li></td>
          <td width="90" nowrap>&nbsp;</td>
          <td width="100" nowrap>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><blockquote> Sounds</blockquote></td>
          <td width="90" nowrap><input name="auscultated_heart_sounds" type="radio" value="0"  <? echo (($fdata{'auscultated_heart_sounds'} == '0')?' checked ':''); ?>>
Normal</td>
          <td width="100" nowrap><input name="auscultated_heart_sounds" type="radio" value="1" <? echo (($fdata{'auscultated_heart_sounds'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="auscultated_heart_sounds_data" type="text" class="fullin"  value="<? echo $fdata{'auscultated_heart_sounds_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Murmurs</blockquote></td>
          <td nowrap><input name="auscultated_heart_murmurs" type="radio" value="0"  <? echo (($fdata{'auscultated_heart_murmurs'} == '0')?' checked ':''); ?>>
Normal</td>
          <td nowrap><input name="auscultated_heart_murmurs" type="radio" value="1" <? echo (($fdata{'auscultated_heart_murmurs'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="auscultated_heart_murmurs_data" type="text" class="fullin"  value="<? echo $fdata{'auscultated_heart_murmurs_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Perirheral vascular </li></td>
          <td nowrap><input name="peripheral_vascular" type="radio" value="0"  <? echo (($fdata{'peripheral_vascular'} == '0')?' checked ':''); ?>>
Normal</td>
          <td nowrap><input name="peripheral_vascular" type="radio" value="1" <? echo (($fdata{'peripheral_vascular'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="peripheral_vascular_data" type="text" class="fullin"  value="<? echo $fdata{'peripheral_vascular_data'}; ?>"></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#EDEDED"><h2>Gastrointestinal</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Abdomen</li></td>
          <td width="90" nowrap><input name="gastr_abdomen" type="radio" value="0"  <? echo (($fdata{'gastr_abdomen'} == '0')?' checked ':''); ?>>
Normal</td>
          <td width="100" nowrap><input name="gastr_abdomen" type="radio" value="1" <? echo (($fdata{'gastr_abdomen'} == '1')?' checked ':''); ?>>
ABNormal</td>
          <td><input name="gastr_abdomen_data" type="text" class="fullin"  value="<? echo $fdata{'gastr_abdomen_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Hernia</li></td>
          <td width="90" nowrap><input name="gastr_hernia" type="radio" value="0"  <? echo (($fdata{'gastr_hernia'} == '0')?' checked ':''); ?>>
      none</td>
          <td width="100" nowrap><input name="gastr_hernia" type="radio" value="1" <? echo (($fdata{'gastr_hernia'} == '1')?' checked ':''); ?>>
      present</td>
          <td><input name="gastr_hernia_data" type="text" class="fullin"  value="<? echo $fdata{'gastr_hernia_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Liver/Spleen</li></td>
          <td nowrap>&nbsp;</td>
          <td nowrap>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Liver </blockquote></td>
          <td nowrap><input name="gastr_liver" type="radio" value="0"  <? echo (($fdata{'gastr_liver'} == '0')?' checked ':''); ?>>
    Normal</td>
          <td nowrap><input name="gastr_liver" type="radio" value="1" <? echo (($fdata{'gastr_liver'} == '1')?' checked ':''); ?>>
    ABNormal</td>
          <td><input name="gastr_liver_data" type="text" class="fullin"  value="<? echo $fdata{'gastr_liver_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Spleen </blockquote></td>
          <td nowrap><input name="gastr_spleen" type="radio" value="0"  <? echo (($fdata{'gastr_spleen'} == '0')?' checked ':''); ?>>
    Normal</td>
          <td nowrap><input name="gastr_spleen" type="radio" value="1" <? echo (($fdata{'gastr_spleen'} == '1')?' checked ':''); ?>>
    ABNormal</td>
          <td><input name="gastr_spleen_data" type="text" class="fullin"  value="<? echo $fdata{'gastr_spleen_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li><a name="sg"></a>Stool guaIac, if indic.</li></td>
          <td nowrap><input name="gastr_stool_guaiac" type="radio" value="0"  <? echo (($fdata{'gastr_stool_guaiac'} == '0')?' checked ':''); ?>>
    positive</td>
          <td nowrap><input name="gastr_stool_guaiac" type="radio" value="1" <? echo (($fdata{'gastr_stool_guaiac'} == '1')?' checked ':''); ?>>
    negative</td>
          <td><input name="gastr_stool_guaiac_data" type="text" class="fullin"  value="<? echo $fdata{'gastr_stool_guaiac_data'}; ?>"></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Lymphatic</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td colspan="4" nowrap><li>Palpation of nodes (Choose all, that are applicable) </li></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><blockquote> Neck </blockquote></td>
          <td width="90" nowrap><input name="lymph_neck" type="radio" value="0"  <? echo (($fdata{'lymph_neck'} == '0')?' checked ':''); ?>>
      Normal</td>
          <td width="100" nowrap><input name="lymph_neck" type="radio" value="1" <? echo (($fdata{'lymph_neck'} == '1')?' checked ':''); ?>>
      ABNormal</td>
          <td><input name="lymph_neck_data" type="text" class="fullin"  value="<? echo $fdata{'lymph_neck_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Axilla </blockquote></td>
          <td nowrap><input name="lymph_axilla" type="radio" value="0"  <? echo (($fdata{'lymph_axilla'} == '0')?' checked ':''); ?>>
      Normal</td>
          <td nowrap><input name="lymph_axilla" type="radio" value="1" <? echo (($fdata{'lymph_axilla'} == '1')?' checked ':''); ?>>
      ABNormal</td>
          <td><input name="lymph_axilla_data" type="text" class="fullin"  value="<? echo $fdata{'lymph_axilla_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Groin </blockquote></td>
          <td nowrap><input name="lymph_groin" type="radio" value="0"  <? echo (($fdata{'lymph_groin'} == '0')?' checked ':''); ?>>
    Normal</td>
          <td nowrap><input name="lymph_groin" type="radio" value="1" <? echo (($fdata{'lymph_groin'} == '1')?' checked ':''); ?>>
    ABNormal</td>
          <td><input name="lymph_groin_data" type="text" class="fullin"  value="<? echo $fdata{'lymph_groin_data'}; ?>"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Other site </blockquote></td>
          <td nowrap><input name="lymph_other" type="radio" value="0"  <? echo (($fdata{'lymph_other'} == '0')?' checked ':''); ?>>
    Normal</td>
          <td nowrap><input name="lymph_other" type="radio" value="1" <? echo (($fdata{'lymph_other'} == '1')?' checked ':''); ?>>
    ABNormal</td>
          <td><input name="lymph_other_data" type="text" class="fullin"  value="<? echo $fdata{'lymph_other_data'}; ?>"></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Skin</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>inspected/palpated</li></td>
          <td width="90" nowrap><input name="skin_inspected" type="radio" value="0"  <? echo (($fdata{'skin_inspected'} == '0')?' checked ':''); ?>>
      Normal</td>
          <td width="100" nowrap><input name="skin_inspected" type="radio" value="1" <? echo (($fdata{'skin_inspected'} == '1')?' checked ':''); ?>>
      ABNormal</td>
          <td><input name="skin_inspected_data" type="text" class="fullin"  value="<? echo $fdata{'skin_inspected_data'}; ?>"></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Neurologic/psychiatric</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Orientation</li></td>
          <td width="90" nowrap><input name="neur_orient_time" type="checkbox"  value="1" <? echo (($fdata{'neur_orient_time'} == '1')?' checked ':''); ?>>
      Time</td>
          <td width="100" nowrap><input name="neur_orient_place" type="checkbox"  value="1" <? echo (($fdata{'neur_orient_place'} == '1')?' checked ':''); ?>>
      Place</td>
          <td width="100"><input name="neur_orient_person" type="checkbox"  value="1" <? echo (($fdata{'neur_orient_person'} == '1')?' checked ':''); ?>> 
            Person
</td>
          <td width="100"><input name="neur_orient_comments" type="checkbox"  value="1" <? echo (($fdata{'neur_orient_comments'} == '1')?' checked ':''); ?>> 
            Comments
</td>
          <td>&nbsp;</td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Mood and affect </li></td>
          <td width="90" nowrap><input name="neur_mood_normal" type="checkbox"  value="1" <? echo (($fdata{'neur_mood_normal'} == '1')?' checked ':''); ?>>
      Normal</td>
          <td width="100" nowrap><input name="neur_mood_depressed" type="checkbox"  value="1" <? echo (($fdata{'neur_mood_depressed'} == '1')?' checked ':''); ?>>
      Depressed</td>
          <td width="100"><input name="neur_mood_anxious" type="checkbox"  value="1" <? echo (($fdata{'neur_mood_anxious'} == '1')?' checked ':''); ?>> 
            Anxious
</td>
          <td width="100"><input name="neur_mood_agitated" type="checkbox"  value="1" <? echo (($fdata{'neur_mood_agitated'} == '1')?' checked ':''); ?>> 
            Agitated
</td>
          <td><input name="neur_mood_other" type="checkbox"  value="1" <? echo (($fdata{'neur_mood_other'} == '1')?' checked ':''); ?>> 
            Other
</td>
          </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#EDEDED"><h2>Gynecologic (at least 7) </h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Breasts</li></td>
          <td width="90" nowrap><input name="gynec_breasts" type="radio" value="0"  <? echo (($fdata{'gynec_breasts'} == '0')?' checked ':''); ?>>
      Normal</td>
          <td width="100" nowrap><input name="gynec_breasts" type="radio" value="1" <? echo (($fdata{'gynec_breasts'} == '1')?' checked ':''); ?>>
      ABNormal</td>
          <td><input name="gynec_breasts_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_breasts_data'}; ?>"></td>
          <td width="231" rowspan="12" align="center" valign="middle">
          <img src="<?
          echo "$rootdir/forms/$formname/";
          ?>genit01.gif" width="231" height="222"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>External genitalia </li></td>
          <td nowrap><input name="gynec_ext_genitalia" type="radio" value="0"  <? echo (($fdata{'gynec_ext_genitalia'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_ext_genitalia" type="radio" value="1" <? echo (($fdata{'gynec_ext_genitalia'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_ext_genitalia_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_ext_genitalia_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Urethral meatus </li></td>
          <td nowrap><input name="gynec_urethral_meatus" type="radio" value="0"  <? echo (($fdata{'gynec_urethral_meatus'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_urethral_meatus" type="radio" value="1" <? echo (($fdata{'gynec_urethral_meatus'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_urethral_meatus_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_urethral_meatus_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Urethra</li></td>
          <td width="90" nowrap><input name="gynec_urethra" type="radio" value="0"  <? echo (($fdata{'gynec_urethra'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td width="100" nowrap><input name="gynec_urethra" type="radio" value="1" <? echo (($fdata{'gynec_urethra'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_urethra_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_urethra_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Bladder</li></td>
          <td nowrap><input name="gynec_bladder" type="radio" value="0"  <? echo (($fdata{'gynec_bladder'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_bladder" type="radio" value="1" <? echo (($fdata{'gynec_bladder'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_bladder_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_bladder_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Vagina/Pelvic support</li></td>
          <td nowrap><input name="gynec_vagina_support" type="radio" value="0"  <? echo (($fdata{'gynec_vagina_support'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_vagina_support" type="radio" value="1" <? echo (($fdata{'gynec_vagina_support'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_vagina_support_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_vagina_support_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Cervix</li></td>
          <td width="90" nowrap><input name="gynec_cervix" type="radio" value="0"  <? echo (($fdata{'gynec_cervix'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td width="100" nowrap><input name="gynec_cervix" type="radio" value="1" <? echo (($fdata{'gynec_cervix'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_cervix_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_cervix_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Uterus</li></td>
          <td nowrap><input name="gynec_uterus" type="radio" value="0"  <? echo (($fdata{'gynec_uterus'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_uterus" type="radio" value="1" <? echo (($fdata{'gynec_uterus'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_uterus_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_uterus_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Adnexa/Parametria</li></td>
          <td nowrap><input name="gynec_adnexa" type="radio" value="0"  <? echo (($fdata{'gynec_adnexa'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_adnexa" type="radio" value="1" <? echo (($fdata{'gynec_adnexa'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_adnexa_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_adnexa_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Anus/Perineum</li></td>
          <td width="90" nowrap><input name="gynec_anus" type="radio" value="0"  <? echo (($fdata{'gynec_anus'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td width="100" nowrap><input name="gynec_anus" type="radio" value="1" <? echo (($fdata{'gynec_anus'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_anus_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_anus_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Rectal</li></td>
          <td nowrap><input name="gynec_rectal" type="radio" value="0"  <? echo (($fdata{'gynec_rectal'} == '0')?' checked ':''); ?>>
  Normal</td>
          <td nowrap><input name="gynec_rectal" type="radio" value="1" <? echo (($fdata{'gynec_rectal'} == '1')?' checked ':''); ?>>
  ABNormal</td>
          <td><input name="gynec_rectal_data" type="text" class="fullin"  value="<? echo $fdata{'gynec_rectal_data'}; ?>"></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td colspan="4" nowrap><br>
            (See also &quot;<a href="#sg">Stool guaiac</a>&quot; above ) </td>
          </tr>
      </table> </td>
  </tr>
  <tr class="fibody">
    <td align="left" valign="top" style="border-bottom:2px solid black;border-top:2px solid black;">Total number of bulleted elements examined 
      <input name="tot_num_examined" type="text"  value="<? echo $fdata{'tot_num_examined'}; ?>"></td>
  </tr>
</table> 
<br>
</div>
<table width="100%" border="0">
  <tr><td align="left" width="120"> <a href="javascript:document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
  <td align="left" nowrap> <a href="<? echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<? echo $id; ?>&formname=<? echo $formname; ?>" target="_blank" class="link_submit">[Printable form]</a> </td>
  <td align="right"> <a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link_submit">[Don't Save]</a> </td></tr>
</table>
</form>
<?php
formFooter();
?>
</body>
</html>