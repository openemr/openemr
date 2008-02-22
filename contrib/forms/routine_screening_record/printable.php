<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"

"http://www.w3.org/TR/html4/loose.dtd">

<?php

include_once("../../globals.php");

include_once("$srcdir/api.inc");

include_once("$srcdir/forms.inc");

include_once("$srcdir/calendar.inc");formHeader("Form: Routine screening record");

?>



<html>

<head>
<? html_header_show();?>

<title>Form: Routine screening record</title>

<? include("../../acog_printable_h.css"); ?>

</head>



<? 

   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");

   if ($fres){

     $patient = sqlFetchArray($fres);

   }

   $fres=sqlStatement("select * from form_routine_screening_record where id=$id");

   if ($fres){

     $fdata = sqlFetchArray($fres);

   }

?>

<body <?echo $top_bg_line;?>>

<div class="srvChapter">Routine screening record </div>

<div style="border: solid 2px black; background-color:#FFFFFF;">

  <table  border="0" cellpadding="2" cellspacing="0">

    <tr align="left" valign="bottom">

      <td colspan="4" class="fibody2" id="bordR">Patient name: <?  echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'}; ?></td>

      <td colspan="4" class="fibody2" id="bordR">Birth date: <?   echo $patient{'DOB'};  ?></td>

      <td colspan="3" class="fibody2">ID No: <?    echo $patient{'id'};   ?></td>

    </tr>

    <tr align="center" valign="middle">

      <td class="ficaption2" id="bordR">age</td>

      <td width="9%" class="ficaption2" id="bordR">Cervical cytology </td>

      <td width="9%" class="ficaption2" id="bordR">Lipid profile assessment<sup>*</sup></td>

      <td width="9%" class="ficaption2" id="bordR">Mammo-<br>

        graphy<sup>*</sup></td>

      <td width="9%" class="ficaption2" id="bordR">Colorectal cancer screening <sup>*</sup></td>

      <td width="9%" class="ficaption2" id="bordR">Bone density screening<sup>*</sup> </td>

      <td width="9%" class="ficaption2" id="bordR">Chlamydia screening<sup>*</sup> </td>

      <td width="9%" class="ficaption2" id="bordR">Gonor-<br>

        rhea screening <sup>*</sup> </td>

      <td width="9%" class="ficaption2" id="bordR">Urinalysis</td>

      <td width="9%" class="ficaption2" id="bordR">Fasting glucose test<sup>*</sup> </td>

      <td width="9%" class="ficaption2">Thyroid stimulating hormone screening </td>

    </tr>

    <tr align="center" valign="middle">

      <td class="fibody2" id="bordR">13-18</td>

      <td class="fibody2" id="bordR">ANNUALLY BEGINNING

        APPROXIMATELY 3 YEARS

        AFTER INITIATION OF

        SEXUAL INTERCOURSE</td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">        SEXUALLY ACTIVE WOMEN

        UNDER AGE 25</td>

      <td class="fibody2" id="bordR">SEXUALLY ACTIVE

        ADOLES-<br>

        CENTS</td>

      <td class="fibody2" id="bordR">&nbsp;</td>

      <td class="fibody2" id="bordR">&nbsp;</td>

      <td class="fibody2">&nbsp;</td>

    </tr>

    <tr align="center" valign="middle">

      <td class="fibody2" id="bordR">19-39</td>

      <td class="fibody2" id="bordR">ANNUALLY BEGINNING NO LATER THAN AGE 21 YEARS</td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR"> SEXUALLY ACTIVE WOMEN<br>

UNDER AGE 25</td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp;</td>

      <td class="fibody2" id="bordR">&nbsp;</td>

      <td class="fibody2">&nbsp;</td>

    </tr>

    <tr align="center" valign="middle">

      <td class="fibody2" id="bordR">40-64</td>

      <td class="fibody2" id="bordR">EVERY 2�3 YEARS AFTER 3

        CONSECuTIVE NEGATIVE TEST

        RESULTS IF NO HISTORY OF

        CIN 2 OR 3,

        IMMUNOSUP-<br>

        PRESSION,

        HIV INFECTION, OR DES

        EXPOSURE IN UTERO</td>

      <td class="fibody2" id="bordR">EVERY 5 YEARS

        BEGINNING

        AT AGE 45</td>

      <td class="fibody2" id="bordR">EVERY 1�2 YEARS UNTIL

        AGE 50; YEARLY

        BEGINNING AT AGE 50</td>

      <td class="fibody2" id="bordR">        BEGINNING AT AGE 50

        YEARLY FOBT OR FLEXIBLE

        SIGMOIDOSCOPY EVERY

        5 YEARS OR YEARLY FOBT

        PLUS FLEXIBLE

        SIGMOIDOSCOPY EVERY 5

        YEARS OR DCBE EVERY 5

        YEARS OR COLONOSCOPY

        EVERY 10 YEARS</td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp;</td>

      <td class="fibody2" id="bordR">EVERY 3 YEARS

        AFTER AGE 45</td>

      <td class="fibody2">EVERY 5 YEARS

        BEGINNING AT AGE 50</td>

    </tr>

    <tr align="center" valign="middle">

      <td class="fibody2" id="bordR">65 and older </td>

      <td class="fibody2" id="bordR">EVERY 2�3 YEARS AFTER 3<br>

        CONSECUTIVE NEGATIVE TEST

        RESULTS IF NO HISTORY OF

        CIN 2 OR 3,

        IMMUNOSUP-<br>

        PRESSION,

        HIV INFECTION, OR DES

        EXPOSURE IN UTERO</td>

      <td class="fibody2" id="bordR">EVERY 5 YEARS</td>

      <td class="fibody2" id="bordR">YEARLY OR AS

        APPROPRIATE</td>

      <td class="fibody2" id="bordR">        YEARLY FOBT OR FLEXIBLE

        SIGMOIDOSCOPY EVERY

        5 YEARS OR YEARLY FOBT

        PLUS FLEXIBLE

        SIGMOIDOSCOPY EVERY

        YEARS OR DCBE EVERY

        YEARS OR COLONOSCOPY

        EVERY 10 YEARS</td>

      <td class="fibody2" id="bordR">        IN THE ABSENCE OF

        NEW RISK FACTORS,

        SUBSEQUENT SCREENING

        NOT MORE FREQUENTLY

        THAN EVERY 2 YEARS</td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">&nbsp; </td>

      <td class="fibody2" id="bordR">YEARLY OR AS

        APPROPRIATE</td>

      <td class="fibody2" id="bordR">EVERY 3 YEARS</td>

      <td class="fibody2">EVERY 5 YEARS</td>

    </tr>

    <tr align="left" valign="bottom">

      <td colspan="11" class="fibody2" style="border-bottom: 2px solid black;">&nbsp;</td>

    </tr>

<?	

$factors = array("cervical", "lipid", "mammo", "colorectal",

"bone", "chlamyd", "gonor", "urinal", "glucose", "thyroid");

$rsi = 1;

while ($rsi < 9){

$chunks = explode("|~", $fdata["record_$rsi"]);

list($cervical_date, $cervical_res) = explode('|', $chunks[0]);

list($lipid_date, $lipid_res) = explode('|', $chunks[1]);

list($mammo_date,$mammo_res) = explode('|', $chunks[2]);

list($colorectal_date,$colorectal_res) = explode('|', $chunks[3]);

list($bone_date,$bone_res) = explode('|', $chunks[4]);

list($chlamyd_date,$chlamyd_res) = explode('|', $chunks[5]);

list($gonor_date,$gonor_res) = explode('|', $chunks[6]);

list($urinal_date,$urinal_res) = explode('|', $chunks[7]);

list($glucose_date,$glucose_res) = explode('|', $chunks[8]);

list($thyroid_date,$thyroid_res) = explode('|', $chunks[9]);





print <<<EOL

    <tr align="left" valign="bottom">

      <td class="fibody2" id="bordR">Date</td>

      <td class="fibody3" id="bordR">${cervical_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${lipid_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${mammo_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${colorectal_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${bone_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${chlamyd_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${gonor_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${urinal_date} &nbsp;</td>

      <td class="fibody3" id="bordR">${glucose_date} &nbsp;</td>

      <td class="fibody3">${thyroid_date} &nbsp;</td>

    </tr>

    <tr align="left" valign="bottom">

      <td class="fibody2" id="bordR">result</td>

      <td class="fibody5" id="bordR">${cervical_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${lipid_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${mammo_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${colorectal_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${bone_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${chlamyd_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${gonor_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${urinal_res} &nbsp;</td>

      <td class="fibody5" id="bordR">${glucose_res} &nbsp;</td>

      <td class="fibody5">${thyroid_res} &nbsp;</td>

    </tr>

EOL;

$rsi++;

}

?>

  </table>

</div>

<p><sup>*</sup>This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)</p>

</body>

</html>