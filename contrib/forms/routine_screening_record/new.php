<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
$frmn = 'form_routine_screening_record';
$ftitle = 'Routine screening record';
$old = sqlStatement("select form_id, formdir from forms where (form_name='${ftitle}') and (pid=$pid) order by date desc limit 1");
if ($old) {
  $dt = sqlFetchArray($old);
  $fid = $dt{'form_id'};
  if ($fid && ($fid != 0) && ($fid != '')){
  $fdir = $dt{'formdir'};
  unset($dt);
  $dt = formFetch($frmn, $fid);
  $newid = formSubmit($frmn, array_slice($dt,7), $id, $userauthorized);
  addForm($encounter, $ftitle, $newid, $fdir, $pid, $userauthorized);
  $id = $newid;
  formJump("${rootdir}/patient_file/encounter/view_form.php?formname=${fdir}&id=${newid}");
  exit;
  }
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
<? html_header_show();?>
<title>Form: Routine screening record</title>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
window.onload = initialize;
</script>
</head>

<? 
   $fres=sqlStatement("select * from patient_data where pid='".$pid."'");
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
?>
<body <?echo $top_bg_line;?>>

<form action="<?echo $rootdir;?>/forms/routine_screening_record/save.php?mode=new" method="post" enctype="multipart/form-data" name="my_form">
<? include("../../acog_menu.inc"); ?>
<div class="srvChapter">Routine screening record <a href="#" onMouseOver="toolTip('The <strong>Routine Screening Record</strong> includes those screening tests recommended by ACOG for routine use and provides reminders for recommended frequency of services.', 300)" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table  border="0" cellpadding="2" cellspacing="0">
    <tr align="left" valign="bottom">
      <td colspan="4" class="fibody2" id="bordR">patient name: 
        <input name="pname" type="text" class="fullin" id="pname" style="width: 74%" value="<?
          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};
          ?>"></td>
      <td colspan="4" class="fibody2" id="bordR">Birth date: 
        <input name="pbdate" type="text" class="fullin" id="pbdate" style="width: 65%" value="<?
          echo $patient{'DOB'};
          ?>"></td>
      <td colspan="3" class="fibody2">ID No:
        <input name="rs_pid" type="text" class="fullin" id="rs_pid" style="width:80%" value="<?
          echo $patient{'id'};
          ?>"></td>
      </tr>
    <tr align="center" valign="middle">
      <td class="ficaption2" id="bordR">age</td>
      <td width="9%" class="ficaption2" id="bordR">Cervical cytology </td>
      <td width="9%" class="ficaption2" id="bordR">Lipid profile assessment<a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
      <td width="9%" class="ficaption2" id="bordR">Mammo-<br>
        graphy<a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
      <td width="9%" class="ficaption2" id="bordR">Colorectal cancer screening <a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
      <td width="9%" class="ficaption2" id="bordR">Bone density screening <a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"> <img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
      <td width="9%" class="ficaption2" id="bordR">Chlamydia screening <a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a> </td>
      <td width="9%" class="ficaption2" id="bordR">Gonor-<br>
        rhea screening <a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a> </td>
      <td width="9%" class="ficaption2" id="bordR">Urinalysis</td>
      <td width="9%" class="ficaption2" id="bordR">Fasting glucose test <a href="#" onMouseOver="toolTip('This test may be appropriate for other patients based on risk (see High-Risk Laboratory Record and Table of High-Risk Factors)')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
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
      <td class="fibody2" id="bordR">EVERY 23 YEARS AFTER 3
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
      <td class="fibody2" id="bordR">EVERY 12 YEARS UNTIL
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
      <td class="fibody2" id="bordR">EVERY 23 YEARS AFTER 3<br>
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
$rsi = 1;
while ($rsi <  9){
print <<<EOL
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR">Date</td>
      <td class="fibody3" id="bordR"><input name="cervical_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="lipid_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="mammo_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="colorectal_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="bone_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="chlamyd_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="gonor_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="urinal_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="glucose_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3"><input name="thyroid_date_${rsi}" type="text" class="fullin2"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR">result</td>
      <td class="fibody5" id="bordR"><input name="cervical_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="lipid_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="mammo_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="colorectal_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="bone_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="chlamyd_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="gonor_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="urinal_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5" id="bordR"><input name="glucose_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody5"><input name="thyroid_res_${rsi}" type="text" class="fullin2"></td>
    </tr>
EOL;
$rsi++;
}
?>
  </table>
</div>
<p>&nbsp;</p>
<table width="100%" border="0">
  <tr>
    <td align="left"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
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
