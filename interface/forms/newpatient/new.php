<?
 include_once("../../globals.php");
 include_once("$srcdir/calendar.inc");

 $months = array("01","02","03","04","05","06","07","08","09","10","11","12");
 $days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
 $thisyear = date("Y");
 $years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

 // get issues
 $ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = $pid AND enddate IS NULL " .
  "ORDER BY type, begdate");
?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<html>
<head>
<title>New Encounter</title>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language="JavaScript">

// Process click on issue title.
function newissue() {
 dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 500, 450);
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
 marginwidth='2' marginheight='0' onload="javascript:document.new_encounter.reason.focus();">

<form method='post' action="<?echo $rootdir?>/forms/newpatient/save.php" name='new_encounter' target='Main'>
<input type='hidden' name='mode' value='new'>
<span class='title'>New Encounter Form</span>
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
   <textarea name='reason' cols='40' rows='5' wrap='virtual' style='width:96%'></textarea>
  </td>
  <td rowspan='4' valign='top'>
   <select multiple name='issues[]' size='10' style='width:100%'
    title='Hold down [Ctrl] for multiple selections or to unselect'>
<?
 while ($irow = sqlFetchArray($ires)) {
  $tcode = $irow['type'];
  if ($tcode == 'medical_problem' || $tcode == 'problem') $tcode = 'P';
  else if ($tcode == 'allergy')    $tcode = 'A';
  else if ($tcode == 'medication') $tcode = 'M';
  else if ($tcode == 'surgery')    $tcode = 'S';
  echo "    <option value='" . $irow['id'] . "'>$tcode: ";
  echo $irow['begdate'] . " " . htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
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
 $dres = sqlStatement("select facility from users where username='".$_SESSION{"authUser"}."'");
 $drow = sqlFetchArray($dres);
 $fres = sqlStatement("select * from facility order by name");
 if ($fres) {
  for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
   $result[$iter] = $frow;
  foreach($result as $iter) {
?>
    <option value="<?echo $iter{name};?>" <?if ($drow{facility} == $iter{name}) {echo "selected";};?>><?echo $iter{name};?></option>
<?
  }
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td class='text' nowrap>Date of Service:</td>
  <td nowrap>
   <select name='month'>
<?
 foreach($months as $month) {
?>
    <option value="<?echo $month;?>" <?if($month == date("m")) echo "selected";?>><?echo $month?></option>
<?
 }
?>
   </select>
   <select name='day'>
<?
 foreach($days as $day){
?>
    <option value="<?echo $day;?>" <?if($day == date("d")) echo "selected";?>><?echo $day?></option>
<?
 }
?>
   </select>
   <select name='year'>
<?
 foreach($years as $year){
?>
    <option value="<?echo $year;?>" <?if($year == date("Y")) echo "selected";?>><?echo $year?></option>
<?
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td class='text' nowrap>Onset/hospitalization date:</td>
  <td nowrap>
   <select name='onset_month'>
<?
 foreach($months as $month){
?>
    <option value="<?echo $month;?>" <?if($month == date("m")) echo "selected";?>><?echo $month?></option>
<?
 }
?>
   </select>
   <select name='onset_day'>
<?
 foreach($days as $day){
?>
    <option value="<?echo $day;?>" <?if($day == date("d")) echo "selected";?>><?echo $day?></option>
<?
 }
?>
   </select>
   <select name='onset_year'>
<?
 foreach($years as $year){
?>
    <option value="<?echo $year;?>" <?if($year == date("Y")) echo "selected";?>><?echo $year?></option>
<?
 }
?>
   </select>
  </td>
 </tr>

</table>

<p>
<a href="javascript:document.new_encounter.submit();" class="link_submit">[Save]</a>
<? if (!isset($_GET["autoloaded"]) || $_GET["autoloaded"] != "1") { ?>
&nbsp; &nbsp;
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link_submit">[Don't Save]</a>
<? } ?>
&nbsp; &nbsp;
<a href="" onclick="return newissue()" class="link_submit">[Add Issue]</a>

</center>

</form>

</body>
</html>
