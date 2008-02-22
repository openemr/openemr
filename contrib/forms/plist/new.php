<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");
include_once("$srcdir/lists.inc");
$frmn = 'form_plist';
$ftitle = 'Problem list';
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
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../acog.css" type="text/css">
<script language="JavaScript" src="../../acog.js" type="text/JavaScript"></script>
<script language="JavaScript" type="text/JavaScript">
  window.onload = initialize;
</script>
</head>
<? 
   $fres=sqlStatement("select * from patient_data where pid=".$_SESSION["pid"]);
   if ($fres){
     $patient = sqlFetchArray($fres);
   }
   $history = getHistoryData($_SESSION["pid"]);
?>
<body <?echo $top_bg_line;?> >

<form action="<?echo $rootdir;?>/forms/plist/save.php?mode=new" method="post" enctype="multipart/form-data" name="my_form">
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
      <td class="fibody2"><textarea name="pl_family_history" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_family_history"><?
//	  ($history['history_mother'] != ''   ) ? $tmp[] = "Mother: "    . $history['history_mother']    : '';
//	  ($history['history_father'] != ''   ) ? $tmp[] = "Father: "    . $history['history_father']    : '';
//	  ($history['history_siblings'] != '' ) ? $tmp[] = "Siblings: "  . $history['history_siblings']  : '';
//	  ($history['history_spouse'] != ''   ) ? $tmp[] = "Spouse: "    . $history['history_spouse']    : '';
//	  ($history['history_offspring'] != '') ? $tmp[] = "Offspring: " . $history['history_offspring'] : '';
//	  echo join(', ', $tmp);
	  $tmp = array();
	  if ($history['history_mother'   ] != '') $tmp[] = "Mother: "    . $history['history_mother'   ];
	  if ($history['history_father'   ] != '') $tmp[] = "Father: "    . $history['history_father'   ];
	  if ($history['history_siblings' ] != '') $tmp[] = "Siblings: "  . $history['history_siblings' ];
	  if ($history['history_spouse'   ] != '') $tmp[] = "Spouse: "    . $history['history_spouse'   ];
	  if ($history['history_offspring'] != '') $tmp[] = "Offspring: " . $history['history_offspring'];
	  if (count($tmp)) echo join(', ', $tmp);
	  ?></textarea></td>
    </tr>
    <tr>
      <td class="ficaption3" id="bordR">Drug/Latex/Transfusion/Allergic reactions: </td>
      <td class="ficaption3">Current medications:</td>
    </tr>
    <tr>
      <td class="fibody3" id="bordR"><textarea name="pl_reactions" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_reactions"><?
$allergies = ''; $checked = 'checked';
if ($result = getListByType($pid, "allergy", "id,title,comments,activity,date", 1, "all", 0)){
	foreach ($result as $iter) {
	  $al_tmp[] = $iter{"title"}.' ('.$iter{"comments"}.') ';
	  $checked = '';
	}
	$allergies = join(',', $al_tmp);
}
echo $allergies;
?></textarea></td>
      <td class="fibody3"><textarea name="pl_medications" rows="6" wrap="VIRTUAL" class="fullin2" id="pl_medications"><?
if ($result = getListByType($pid, "medication", "id,title,comments,activity,date", 1, "all", 0)){
	foreach ($result as $iter) {
	  $tmp_med[] = $iter{"title"}.' ('.$iter{"comments"}.') ';
	}
echo join(', ', $tmp_med);
}
?></textarea></td>
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
<table width="100%" border="0">
  <tr>
    <td align="left" width="100"> <a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save Data]</a> </td>
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
