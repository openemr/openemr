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
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
window.onload = initialize;
//-->
</script>
</head>
<? 
   $fres = sqlStatement("select * from form_plist where id=$id");
   $obj = sqlFetchArray($fres);
   $fres=sqlStatement("select * from patient_data where pid=".$_SESSION["pid"]);
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   
?>
<body <?echo $top_bg_line;?>>

<form action="<?echo $rootdir;?>/forms/plist/save.php?mode=update&id=<?echo $_GET["id"];?>" method="post" enctype="multipart/form-data" name="my_form">
<? include("../../acog_menu.inc"); ?>
  <table width="70%"  border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Patient name:</td>
    <td align="left" valign="bottom"><input name="pname" type="text" class="fullin" id="pname" value="<? echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'}; ?>"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Birth date: </td>
    <td align="left" valign="bottom"><input name="pbdate" type="text" class="fullin" id="pbdate" value="<?  echo $patient{'DOB'};  ?>"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">ID No:</td>
    <td align="left" valign="bottom"><input name="pl_pid" type="text" class="fullin" id="pl_pid" value="<? echo $patient{'id'};  ?>" readonly="true"></td>
  </tr>
  <tr>
    <td width="120" align="left" valign="bottom" class="srvCaption">Date</td>
    <td align="left" valign="bottom"><input name="pl_date" type="text" class="fullin" id="pl_date" value="<? echo date('Y-m-d'); ?>"></td>
  </tr>
</table>
<div class="srvChapter">Problem list <a href="#" onMouseOver="toolTip('The Problem List captures problems, allergies, family history, and current medication use.')" onMouseOut="toolTip();"><img src="../../pic/mark_q.png" width="13" height="13" border="0"></a></div>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td width="50%" class="ficaption3" id="bordR">High risk:</td>
      <td width="50%" class="ficaption3">Family history:</td>
    </tr>
    <tr>
      <td class="fibody2" id="bordR"><textarea name="pl_high_risk" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_high_risk"><? echo $obj{'pl_high_risk'} ?></textarea></td>
      <td class="fibody2"><textarea name="pl_family_history" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_family_history"><? echo $obj{'pl_family_history'} ?></textarea></td>
    </tr>
    <tr>
      <td class="ficaption3" id="bordR">Drug/Latex/Transfusion/Allergic reactions: </td>
      <td class="ficaption3">Current medications:</td>
    </tr>
    <tr>
      <td class="fibody3" id="bordR"><textarea name="pl_reactions" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_reactions"><? echo $obj{'pl_reactions'} ?></textarea></td>
      <td class="fibody3"><textarea name="pl_medications" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_medications"><? echo $obj{'pl_medications'} ?></textarea></td>
    </tr>
  </table>
</div>
<p>&nbsp;</p>
<div style="border: solid 2px black; background-color:#FFFFFF;">
  <table width="100%"  border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td width="20" align="left" valign="bottom" class="ficaption2" id="bordR">No</td>
      <td width="120" align="center" valign="bottom" class="ficaption2" id="bordR">Entry date </td>
      <td align="center" valign="bottom" class="ficaption2" id="bordR">Problem/Resolution</td>
      <td width="120" align="center" valign="bottom" class="ficaption2" id="bordR">Onset age and date </td>
      <td width="120" align="center" valign="bottom" class="ficaption2">Resolution date </td>
    </tr>
<?
$pli = 1;

while ($pli < 26){
//print $obj["pl_problem_$pli"];
list($pl_ed, $pl_problem, $pl_onset, $pl_rd) = explode('|~', $obj["pl_problem_${pli}"]);
print <<<EOL
    <tr>
      <td align="left" valign="bottom" class="fibody2" id="bordR">${pli}.</td>
      <td align="left" valign="bottom" class="fibody2" id="bordR"><input name="pl_ed_${pli}" type="text" class="fullin2" value="${pl_ed}"></td>
      <td align="left" valign="bottom" class="fibody2" id="bordR"><input name="pl_problem_${pli}" type="text" class="fullin2" value="${pl_problem}"></td>
      <td align="left" valign="bottom" class="fibody2" id="bordR"><input name="pl_onset_${pli}" type="text" class="fullin2" value="${pl_onset}"></td>
      <td align="left" valign="bottom" class="fibody2"><input name="pl_rd_${pli}" type="text" class="fullin2" value="${pl_rd}"></td>
    </tr>
EOL;
$pli++;
}
?>	
  </table>
</div>
<p>&nbsp;</p>
<table width="100%" border="0">
  <tr>
    <td align="left" width="100"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
    <td align="left" nowrap> <a href="<? echo $rootdir; ?>/patient_file/encounter/print_form.php?id=<? echo $id; ?>&formname=plist"
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
