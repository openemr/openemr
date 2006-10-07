<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
?>

<html>
<head>
<title>Form: ACOG Immunization record</title>
<? include("../../acog_printable_h.css"); ?>
</head>

<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   $fres=sqlStatement("select * from form_immunization_record where id='".$id."'");
   if ($fres){
     $ir = sqlFetchArray($fres);
   }
?>
<body>

<div class="srvChapter">Immunization record<sup>*</sup></div>
<div style="border: solid 2pt black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr align="left" valign="bottom">
      <td colspan="4" class="fibody2" id="bordR">Patient name: <? echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'}; ?></td>
      <td colspan="2" class="fibody2" id="bordR">Birth date: <? echo $patient{'DOB'}; ?></td>
      <td colspan="2" class="fibody2">ID No: <?  echo $patient{'id'};  ?></td>
      </tr>
    <tr align="center" valign="middle">
      <td width="9%" class="ficaption2" id="bordR">age</td>
      <td width="13%" class="ficaption2" id="bordR">Tetanus-Diphteria booster </td>
      <td width="13%" class="ficaption2" id="bordR">Influenza vaccine </td>
      <td width="13%" class="ficaption2" id="bordR">Pneumococcal vaccine </td>
      <td width="13%" class="ficaption2" id="bordR">MMR Vaccine</td>
      <td width="13%" class="ficaption2" id="bordR">Hepatitis B vaccine </td>
      <td width="13%" class="ficaption2" id="bordR">Hepatitis A vaccine </td>
      <td width="13%" class="ficaption2">Varicella vaccine </td>
    </tr>
    <tr align="center" valign="middle">
      <td class="fibody2" id="bordR">13-18</td>
      <td class="fibody2" id="bordR">Once between ages 11-16 </td>
      <td class="fibody2" id="bordR">Based on risk </td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">One series for those not previously immunized </td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2">Based on risk</td>
    </tr>
    <tr align="center" valign="middle">
      <td class="fibody2" id="bordR">19-39</td>
      <td class="fibody2" id="bordR">Every 10 years </td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2">Based on risk</td>
    </tr>
    <tr align="center" valign="middle">
      <td class="fibody2" id="bordR">40-64</td>
      <td class="fibody2" id="bordR">Every 10 years</td>
      <td class="fibody2" id="bordR">Annually beginning at age 50 years </td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2">Based on risk</td>
    </tr>
    <tr align="center" valign="middle">
      <td class="fibody2" id="bordR">65 and older </td>
      <td class="fibody2" id="bordR">Every 10 years</td>
      <td class="fibody2" id="bordR">Annually</td>
      <td class="fibody2" id="bordR">Once</td>
      <td class="fibody2" id="bordR">&nbsp;</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2" id="bordR">Based on risk</td>
      <td class="fibody2">Based on risk</td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<div style="border: solid 2pt black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
<?
$vacci=0;
  $vacc_tetanus = explode("|~", $ir["vacc_tetanus"]);
  $vacc_influenza = explode("|~", $ir["vacc_influenza"]);
  $vacc_pneumococcal = explode("|~", $ir["vacc_pneumococcal"]);
  $vacc_mmr = explode("|~", $ir["vacc_mmr"]);
  $vacc_hep_a = explode("|~", $ir["vacc_hep_a"]);
  $vacc_hep_b = explode("|~", $ir["vacc_hep_b"]);
  $vacc_varicella= explode("|~", $ir["vacc_varicella"]);
while ($vacci<20){
print <<<EOL
    <tr align="left" valign="bottom">
      <td width="9%" class="fibody5" id="bordR">Date</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_tetanus[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_influenza[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_pneumococcal[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_mmr[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_hep_b[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5" id="bordR">${vacc_hep_a[$vacci]}&nbsp;</td>
      <td width="13%" class="fibody5">${vacc_varicella[$vacci]}&nbsp;</td>
    </tr>
EOL;
$vacci++;
}
?>	
  </table>
</div>
<p><sup>*</sup>For immunizations based on risk refer to the Table of High-Risk Factors.</p>
</body>
</html>