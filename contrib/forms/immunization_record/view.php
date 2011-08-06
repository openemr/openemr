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
<?php html_header_show();?>

<title>Form: ACOG Immunization record</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<link rel=stylesheet href="../../acog.css" type="text/css">

<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>

<script language="JavaScript" type="text/JavaScript">

<!--

window.onload = initialize;

//-->

</script>

</head>



<?php 

   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");

   if ($fres){

     $patient = sqlFetchArray($fres);

   }

   $fres=sqlStatement("select * from form_immunization_record where id='".$id."'");

   if ($fres){

     $ir = sqlFetchArray($fres);

   }

?>

<body <?echo $top_bg_line;?>>





<form action="<?echo $rootdir;?>/forms/immunization_record/save.php?mode=update&id=<?php echo $id;?>" method="post" enctype="multipart/form-data" name="my_form">

<?php include("../../acog_menu.inc"); ?>

<div class="srvChapter">Immunization record<sup><a href="#" onClick="toolTip('The Immunization Record lists immunization services recommended by ACOG for either routine use or in high-risk patients, as defined in the enclosed table of high-risk factors. space for listing problems and immunization services allows the same form to be used for years.<br><br>For immunizations based on risk refer to the Table of High-Risk Factors')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0"></a></sup></div>

<div style="border: solid 2px black; background-color:#FFFFFF;">

  <table width="100%"  border="0" cellspacing="0" cellpadding="2">

    <tr align="left" valign="bottom">

      <td colspan="4" class="fibody2" id="bordR">patient name: 

        <input name="pname" type="text" class="fullin" id="pname" style="width: 74%" value="<?php


          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};

          ?>"></td>

      <td colspan="2" class="fibody2" id="bordR">Birth date: 

        <input name="pbdate" type="text" class="fullin" id="pbdate" style="width: 65%" value="<?php


          echo $patient{'DOB'};

          ?>"></td>

      <td colspan="2" class="fibody2">ID No: 

        <input name="pl_pid" type="text" class="fullin" id="pl_pid" style="width:80%" value="<?php


          echo $patient{'id'};

          ?>"></td>

      </tr>

    <tr align="center" valign="middle">

      <td width="9%" class="ficaption2" id="bordR">age</td>

      <td width="13%" class="ficaption2" id="bordR">Tetanus-<br>

        Diphteria<br>

        booster </td>

      <td width="13%" class="ficaption2" id="bordR">Influenza<br> 

        vaccine </td>

      <td width="13%" class="ficaption2" id="bordR">Pneumococcal vaccine </td>

      <td width="13%" class="ficaption2" id="bordR">MMR Vaccine </td>

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

<div style="border: solid 2px black; background-color:#FFFFFF;">

  <table width="100%"  border="0" cellspacing="0" cellpadding="2">

<?php


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

      <td width="9%" class="fibody2" id="bordR">Date</td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_tetanus_${vacci}" type="text" class="fullin2" value="${vacc_tetanus[$vacci]}"></td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_influenza_${vacci}" type="text" class="fullin2" value="${vacc_influenza[$vacci]}"></td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_pneumococcal_${vacci}" type="text" class="fullin2" value="${vacc_pneumococcal[$vacci]}"></td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_mmr_${vacci}" type="text" class="fullin2" value="${vacc_mmr[$vacci]}"></td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_hep_b_${vacci}" type="text" class="fullin2" value="${vacc_hep_b[$vacci]}"></td>

      <td width="13%" class="fibody2" id="bordR"><input name="vacc_hep_a_${vacci}" type="text" class="fullin2" value="${vacc_hep_a[$vacci]}"></td>

      <td width="13%" class="fibody2"><input name="vacc_varicella_${vacci}" type="text" class="fullin2" value="${vacc_varicella[$vacci]}"></td>

    </tr>

EOL;

$vacci++;

}

?>	

  </table>

</div>

<p><sup>*</sup>For immunizations based on risk refer to the Table of High-Risk Factors.</p>

<p>&nbsp;</p>

<table width="100%" border="0">

  <tr>

    <td align="left" width="100"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>

    <td align="left" nowrap> <a href="<?php echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<?php echo $id; ?>&formname=<?php echo $formname; ?>" target="_blank" class="link_submit"

     onclick="top.restoreSession()">[Printable form]</a> </td>

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