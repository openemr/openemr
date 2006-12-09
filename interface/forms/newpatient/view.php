<?
 include_once("../../globals.php");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/lists.inc");

 $disabled = "disabled";

 // If we are allowed to change encounter dates...
 if (acl_check('encounters', 'date_a')) {
  $disabled = "";
 }

 $months = array("01","02","03","04","05","06","07","08","09","10","11","12");
 $days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
 $thisyear = date("Y");
 $years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

 $id = $_REQUEST['id'];

 $result = sqlQuery("SELECT * FROM form_encounter WHERE id = '$id'");

 $encounter = $result['encounter'];

 if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
  echo "<body>\n<html>\n";
  echo "<p>You are not authorized to see this encounter.</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 // Sort comparison for sensitivities by their order attribute.
 function sensitivity_compare($a, $b) {
  return ($a[2] < $b[2]) ? -1 : 1;
 }

 // get issues
 $ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = $pid AND enddate IS NULL " .
  "ORDER BY type, begdate");
?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<html>
<head>
<title>Patient Encounter</title>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../../library/calendar.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 600, 475);
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0]['issues[]'];
  s.options[s.options.length] = new Option(title, issue, true, true);
 }

</script>
</head>

<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2' bottommargin='0'
 marginwidth='2' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' action="<?echo $rootdir?>/forms/newpatient/save.php" name='new_encounter'
 <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<? echo $_GET["id"] ?>'>
<span class=title>Patient Encounter Form</span>
<br>
<center>
<table width='96%'>

 <tr>
  <td colspan='2' width='50%' nowrap class='text'>Chief Complaint:</td>
  <td class='text' width='50%' nowrap>
   Issues (Problems, Medications, Surgeries, Allergies):
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   <textarea name='reason' cols='40' rows='4' wrap='virtual' style='width:96%'><? echo htmlspecialchars($result['reason']) ?></textarea>
  </td>
  <td rowspan='5' valign='top'>
   <select multiple name='issues[]' size='10' style='width:100%'
    title='Hold down [Ctrl] for multiple selections or to unselect'>
<?
 while ($irow = sqlFetchArray($ires)) {
  $list_id = $irow['id'];
  $tcode = $irow['type'];
  if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];

  echo "    <option value='$list_id'";
  $perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
   "pid = '$pid' AND encounter = '$encounter' AND list_id = '$list_id'");
  if ($perow['count']) echo " selected";
  echo ">$tcode: " . $irow['begdate'] . " " .
   htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td class='text' width='1%' nowrap>Facility:</td>
  <td>
   <select name='facility'>
<?
 $fres = sqlStatement("select * from facility order by name");
 if ($fres) {
  $result = array();
  for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
   $result[$iter] = $frow;
  foreach($result as $iter) {
?>
    <option value="<?echo $iter{name};?>" <?if ($result{facility} == $iter{name}) echo "selected";?>><?echo $iter{name};?></option>
<?
  }
 }
?>
   </select>
  </td>
 </tr>

 <tr>
<?php
 $sensitivities = acl_get_sensitivities();
 if ($sensitivities && count($sensitivities)) {
  usort($sensitivities, "sensitivity_compare");
?>
  <td class='text' width='1%' nowrap>Sensitivity:</td>
  <td>
   <select name='form_sensitivity'>
<?php
  foreach ($sensitivities as $value) {
   // Omit sensitivities to which this user does not have access.
   if (acl_check('sensitivities', $value[1])) {
    echo "    <option value='" . $value[1] . "'";
    if ($result['sensitivity'] == $value[1]) echo " selected";
    echo ">" . $value[3] . "</option>\n";
   }
  }
  echo "    <option value=''";
  if (!$result['sensitivity']) echo " selected";
  echo ">None</option>\n";
?>
   </select>
  </td>
<?php
 } else {
?>
  <td colspan='2'><!-- sensitivities not used --></td>
<?php
 }
?>
 </tr>

 <tr>
  <td class='text' nowrap>Date of Service:</td>
  <td nowrap>
   <input type='text' size='10' name='form_date' <? echo $disabled ?>
    value='<? echo substr($result['date'], 0, 10) ?>'
    title='yyyy-mm-dd Date of service'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <a href="javascript:show_calendar('new_encounter.form_date')"
    title="Click here to choose a date"
    ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' alt='[?]'></a>
  </td>
 </tr>

 <tr>
  <td class='text' nowrap>Onset/hospitalization date:</td>
  <td nowrap>
   <input type='text' size='10' name='form_onset_date'
    value='<? echo substr($result['onset_date'], 0, 10) ?>'
    title='yyyy-mm-dd Date of onset or hospitalization'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <a href="javascript:show_calendar('new_encounter.form_onset_date')"
    title="Click here to choose a date"
    ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' alt='[?]'></a>
  </td>
 </tr>

</table>

<p>
<a href="javascript:document.new_encounter.submit();" class="link_submit">[Save]</a>
&nbsp; &nbsp;

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="<?php echo "$rootdir/patient_file/encounter/encounter_top.php"; ?>"
 class="link_submit">[Cancel]</a>
<?php } else { ?>
<a href="<?php echo "$rootdir/patient_file/encounter/patient_encounter.php"; ?>"
 class="link_submit" target='Main'>[Cancel]</a>
<?php } ?>

&nbsp; &nbsp;
<a href="" onclick="return newissue()" class="link_submit">[Add Issue]</a>

</center>

</form>

</body>
</html>
