<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
$frmn = 'form_high_risk_screening_record';
$ftitle = 'High risk screening record';
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
<title>Form: High risk screening record</title>
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

<form action="<?echo $rootdir;?>/forms/high_risk_screening_record/save.php?mode=new" method="post" enctype="multipart/form-data" name="my_form">
<? include("../../acog_menu.inc"); ?>
<div class="srvChapter">High risk screening  record <a href="#" onMouseOver="toolTip('See Table of High-Risk Factors.')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table  border="0" cellpadding="2" cellspacing="0">
    <tr align="left" valign="bottom">
      <td colspan="6" class="fibody2" id="bordR">patient name: 
        <input name="pname" type="text" class="fullin" id="pname" style="width: 70%" value="<?
          echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'};
          ?>"></td>
      <td colspan="5" class="fibody2" id="bordR">Birth date: 
        <input name="pbdate" type="text" class="fullin" id="pbdate" style="width: 65%" value="<?
          echo $patient{'DOB'};
          ?>"></td>
      <td colspan="4" class="fibody2">ID No:
        <input name="hr_pid" type="text" class="fullin" id="hr_pid" style="width:80%" value="<?
          echo $patient{'id'};
          ?>"></td>
      </tr>
    <tr align="center" valign="middle">
      <td class="ficaption2" id="bordR">&nbsp;</td>
      <td width="6%" class="ficaption2" id="bordR">HEMO-<br>
        GLOBIN<br>
        TEST</td>
      <td width="6%" class="ficaption2" id="bordR">BONE DENSITY<br>
        SCREENING</td>
      <td width="6%" class="ficaption2" id="bordR">BACTERI-<br>
        URIA&nbsp;TEST</td>
      <td width="6%" class="ficaption2" id="bordR">STD<br>
        TESTING</td>
      <td width="6%" class="ficaption2" id="bordR">HIV test <a href="#" onMouseOver="toolTip('Check state requirements before recording results.')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0" align="texttop"></a></td>
      <td width="6%" class="ficaption2" id="bordR">Genetic testing </td>
      <td width="6%" class="ficaption2" id="bordR">Rubella titer </td>
      <td width="6%" class="ficaption2" id="bordR">TB skin test </td>
      <td width="6%" class="ficaption2" id="bordR">Lipid profile assessment </td>
      <td width="6%" class="ficaption2" id="bordR">mammo-<br>
        graphy</td>
      <td width="6%" class="ficaption2" id="bordR">FASTING<br>
        GLUCOSE TEST</td>
      <td width="6%" class="ficaption2" id="bordR">TSH<br>
        TEST</td>
      <td width="6%" class="ficaption2" id="bordR">COLORECTAL<br>
        CANCER<br>
        SCREENING</td>
      <td width="6%" class="ficaption2">HEPATITIS C<br>
        VIRUS TEST</td>
    </tr>
<?	
$rsi = 1;
while ($rsi <  13){
print <<<EOL
    <tr align="left" valign="bottom">
      <td class="fibody3" id="bordR">Date:</td>
      <td class="fibody3" id="bordR"><input name="hemoglobin_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="bone_density_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="bacteriuria_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="std_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="hiv_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="genetic_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="rubella_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="tb_skin_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="lipid_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="mammography_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="fasting_glucose_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="tsh_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3" id="bordR"><input name="cancer_date_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody3"><input name="hepatitis_c_date_${rsi}" type="text" class="fullin2"></td>
    </tr>
    <tr align="left" valign="bottom">
      <td class="fibody2" id="bordR">Result:</td>
      <td class="fibody2" id="bordR"><input name="hemoglobin_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="bone_density_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="bacteriuria_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="std_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="hiv_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="genetic_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="rubella_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="tb_skin_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="lipid_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="mammography_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="fasting_glucose_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="tsh_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2" id="bordR"><input name="cancer_res_${rsi}" type="text" class="fullin2"></td>
      <td class="fibody2"><input name="hepatitis_c_res_${rsi}" type="text" class="fullin2"></td>
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