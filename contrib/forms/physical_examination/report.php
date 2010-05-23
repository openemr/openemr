<? function physical_examination_report( $pid, $encounter, $cols, $id) { 
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){  $patient = sqlFetchArray($fres);  }
   $fres=sqlStatement("select * from form_physical_examination where id=$id");
   if ($fres){  $fdata = sqlFetchArray($fres);  }
?>
<div class="srvChapter">Physical examination</div>
<div style="border: solid 1.5pt black; background-color: white;">
<table width="100%"  border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td align="left" valign="top" style="border-bottom: 2px solid black;"><table width="100%"  border="0" cellspacing="0" cellpadding="5">
      <tr align="left" valign="top" class="fibody">
        <td width="40%" nowrap id="bordR">Patient name: <b><?  echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};  ?></b></td>
        <td width="20%" nowrap id="bordR">Birth date: <? echo $patient{'DOB'}; ?></td>
        <td width="20%" nowrap id="bordR">ID No: <? echo $patient{'id'};  ?></td>
        <td width="20%" nowrap>Date: <? echo date('Y-m-d');  ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#EDEDED"><h2>Constitutional</h2> 
      <li>Vital signs (record <u><strong>&gt;</strong></u> 3 vital signs)</li> 
      <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr align="left" valign="top" class="fibody">
          <td width="14%" nowrap>Height<br>
            <? echo $fdata{'height'}; ?></td>
          <td width="14%" nowrap>Weight<br>
            <? echo $fdata{'weight'}; ?></td>
          <td width="14%" nowrap>BMI<br>
            <? echo $fdata{'bmi'}; ?></td>
          <td nowrap>Blood pressure (sitting) <br>
            <? echo $fdata{'blood_pressure'}; ?></td>
          <td width="14%" nowrap>Temperature<br>
            <? echo $fdata{'temperature'}; ?></td>
          <td width="14%" nowrap>Pulse<br>
            <? echo $fdata{'pulse'}; ?></td>
          <td width="14%" nowrap>Respiration<br>
            <? echo $fdata{'respiration'}; ?></td>
        </tr>
      </table> 
      <li>General appearance (Note all that apply):</li> <table width="100%"  border="0" cellspacing="0" cellpadding="2">
        <tr class="fibody">
          <td width="150" nowrap><? echo (($fdata{'general_well_developed'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Well-developed
</td>
          <td width="90" nowrap><? echo (($fdata{'general_well_developed'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Other
</td>
          <td width="100" nowrap>&nbsp;</td>
          <td>&nbsp;</td>
          <td width="120"><? echo (($fdata{'general_no_deformities'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            No deformities
</td>
          <td width="100"><? echo (($fdata{'general_no_deformities'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Other
</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="fibody">
          <td width="150" nowrap><? echo (($fdata{'general_well_nourished'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Well-nourished
</td>
          <td width="90" nowrap><? echo (($fdata{'general_well_nourished'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Other
</td>
          <td width="100" nowrap>&nbsp;</td>
          <td>&nbsp;</td>
          <td width="120"><? echo (($fdata{'general_well_groomed'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Well-groomed
</td>
          <td width="100"><? echo (($fdata{'general_well_groomed'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Other
</td>
          <td>&nbsp;</td>
        </tr>
        <tr class="fibody">
          <td width="150" nowrap><? echo (($fdata{'general_normal_habitus'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Normal habitus</td>
          <td width="90" nowrap><? echo (($fdata{'general_normal_habitus'} == '2')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Obese</td>
          <td width="100" nowrap><? echo (($fdata{'general_normal_habitus'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
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
          <td width="90" nowrap> <? echo (($fdata{'neck_neck'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td width="100" nowrap><? echo (($fdata{'neck_neck'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
            ABNormal</td>
          <td><? echo $fdata{'neck_neck_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Thyroid</li></td>
          <td width="90" nowrap>
            <? echo (($fdata{'neck_thyroid'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td width="100" nowrap><? echo (($fdata{'neck_thyroid'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'neck_thyroid_data'}; ?></td>
          </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Respiratory</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Respiratory effort </li></td>
          <td nowrap>
            <? echo (($fdata{'respiratory_effort'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'respiratory_effort'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'respiratory_effort_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Auscultated lungs </li></td>
          <td width="90" nowrap><? echo (($fdata{'auscultated_lungs'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Normal</td>
          <td width="100" nowrap><? echo (($fdata{'auscultated_lungs'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'auscultated_lungs_data'}; ?></td>
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
          <td width="90" nowrap><? echo (($fdata{'auscultated_heart_sounds'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Normal</td>
          <td width="100" nowrap><? echo (($fdata{'auscultated_heart_sounds'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'auscultated_heart_sounds_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Murmurs</blockquote></td>
          <td nowrap><? echo (($fdata{'auscultated_heart_murmurs'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Normal</td>
          <td nowrap><? echo (($fdata{'auscultated_heart_murmurs'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'auscultated_heart_murmurs_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Perirheral vascular </li></td>
          <td nowrap><? echo (($fdata{'peripheral_vascular'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Normal</td>
          <td nowrap><? echo (($fdata{'peripheral_vascular'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'peripheral_vascular_data'}; ?></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top" bgcolor="#EDEDED"><h2>Gastrointestinal</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Abdomen</li></td>
          <td width="90" nowrap><? echo (($fdata{'gastr_abdomen'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
Normal</td>
          <td width="100" nowrap><? echo (($fdata{'gastr_abdomen'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
ABNormal</td>
          <td><? echo $fdata{'gastr_abdomen_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Hernia</li></td>
          <td width="90" nowrap><? echo (($fdata{'gastr_hernia'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      none</td>
          <td width="100" nowrap><? echo (($fdata{'gastr_hernia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      present</td>
          <td><? echo $fdata{'gastr_hernia_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Liver/Spleen</li></td>
          <td nowrap>&nbsp;</td>
          <td nowrap>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Liver </blockquote></td>
          <td nowrap><? echo (($fdata{'gastr_liver'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    Normal</td>
          <td nowrap><? echo (($fdata{'gastr_liver'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    ABNormal</td>
          <td><? echo $fdata{'gastr_liver_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Spleen </blockquote></td>
          <td nowrap><? echo (($fdata{'gastr_spleen'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    Normal</td>
          <td nowrap><? echo (($fdata{'gastr_spleen'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    ABNormal</td>
          <td><? echo $fdata{'gastr_spleen_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li><a name="sg"></a>Stool guaIac, if indic.</li></td>
          <td nowrap><? echo (($fdata{'gastr_stool_guaiac'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    positive</td>
          <td nowrap><? echo (($fdata{'gastr_stool_guaiac'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    negative</td>
          <td><? echo $fdata{'gastr_stool_guaiac_data'}; ?></td>
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
          <td width="90" nowrap><? echo (($fdata{'lymph_neck'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td width="100" nowrap><? echo (($fdata{'lymph_neck'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      ABNormal</td>
          <td><? echo $fdata{'lymph_neck_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Axilla </blockquote></td>
          <td nowrap><? echo (($fdata{'lymph_axilla'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td nowrap><? echo (($fdata{'lymph_axilla'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      ABNormal</td>
          <td><? echo $fdata{'lymph_axilla_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Groin </blockquote></td>
          <td nowrap><? echo (($fdata{'lymph_groin'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    Normal</td>
          <td nowrap><? echo (($fdata{'lymph_groin'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    ABNormal</td>
          <td><? echo $fdata{'lymph_groin_data'}; ?></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><blockquote> Other site </blockquote></td>
          <td nowrap><? echo (($fdata{'lymph_other'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    Normal</td>
          <td nowrap><? echo (($fdata{'lymph_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
    ABNormal</td>
          <td><? echo $fdata{'lymph_other_data'}; ?></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Skin</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>inspected/palpated</li></td>
          <td width="90" nowrap><? echo (($fdata{'skin_inspected'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td width="100" nowrap><? echo (($fdata{'skin_inspected'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      ABNormal</td>
          <td><? echo $fdata{'skin_inspected_data'}; ?></td>
        </tr>
      </table> </td>
  </tr>
  <tr>
    <td align="left" valign="top"><h2>Neurologic/psychiatric</h2>
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Orientation</li></td>
          <td width="90" nowrap><? echo (($fdata{'neur_orient_time'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Time</td>
          <td width="100" nowrap><? echo (($fdata{'neur_orient_place'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Place</td>
          <td width="100"><? echo (($fdata{'neur_orient_person'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Person
</td>
          <td width="100"><? echo (($fdata{'neur_orient_comments'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Comments
</td>
          <td>&nbsp;</td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Mood and affect </li></td>
          <td width="90" nowrap><? echo (($fdata{'neur_mood_normal'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td width="100" nowrap><? echo (($fdata{'neur_mood_depressed'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Depressed</td>
          <td width="100"><? echo (($fdata{'neur_mood_anxious'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Anxious
</td>
          <td width="100"><? echo (($fdata{'neur_mood_agitated'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
            Agitated
</td>
          <td><? echo (($fdata{'neur_mood_other'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?> 
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
          <td width="90" nowrap><? echo (($fdata{'gynec_breasts'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      Normal</td>
          <td width="100" nowrap><? echo (($fdata{'gynec_breasts'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
      ABNormal</td>
          <td><? echo $fdata{'gynec_breasts_data'}; ?></td>
          <td width="231" rowspan="12" align="center" valign="middle">
          <img src="<?
          echo "$rootdir/forms/$formname/";
          ?>genit01.gif" width="231" height="222"></td>
        </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>External genitalia </li></td>
          <td nowrap><? echo (($fdata{'gynec_ext_genitalia'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_ext_genitalia'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_ext_genitalia_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Urethral meatus </li></td>
          <td nowrap><? echo (($fdata{'gynec_urethral_meatus'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_urethral_meatus'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_urethral_meatus_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Urethra</li></td>
          <td width="90" nowrap><? echo (($fdata{'gynec_urethra'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td width="100" nowrap><? echo (($fdata{'gynec_urethra'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_urethra_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Bladder</li></td>
          <td nowrap><? echo (($fdata{'gynec_bladder'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_bladder'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_bladder_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Vagina/Pelvic support</li></td>
          <td nowrap><? echo (($fdata{'gynec_vagina_support'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_vagina_support'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_vagina_support_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Cervix</li></td>
          <td width="90" nowrap><? echo (($fdata{'gynec_cervix'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td width="100" nowrap><? echo (($fdata{'gynec_cervix'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_cervix_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Uterus</li></td>
          <td nowrap><? echo (($fdata{'gynec_uterus'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_uterus'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_uterus_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Adnexa/Parametria</li></td>
          <td nowrap><? echo (($fdata{'gynec_adnexa'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_adnexa'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_adnexa_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td width="150" nowrap><li>Anus/Perineum</li></td>
          <td width="90" nowrap><? echo (($fdata{'gynec_anus'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td width="100" nowrap><? echo (($fdata{'gynec_anus'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_anus_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td nowrap><li>Rectal</li></td>
          <td nowrap><? echo (($fdata{'gynec_rectal'} == '0')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  Normal</td>
          <td nowrap><? echo (($fdata{'gynec_rectal'} == '1')?'<img src="../../pic/mrkcheck.png" width="12" height="11"> ':'<img src="../../pic/mrkempty.png" width="12" height="11"> '); ?>
  ABNormal</td>
          <td><? echo $fdata{'gynec_rectal_data'}; ?></td>
          </tr>
        <tr align="left" valign="bottom" class="fibody">
          <td colspan="4" nowrap><br>
            (See also &quot;<a href="#sg">Stool guaiac</a>&quot; above ) </td>
          </tr>
      </table> </td>
  </tr>
  <tr class="fibody">
    <td align="left" valign="top" style="border-bottom:2px solid black;border-top:2px solid black;">Total number of bulleted elements examined 
      <? echo $fdata{'tot_num_examined'}; ?></td>
  </tr>
</table> 
</div>
<? } ?>