<?
include_once("../../globals.php");
include_once("$srcdir/calendar.inc");

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17",
	"18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$years = array("2004","2005","2006","2007");
?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<html>
<head>
<title>New Encounter</title>

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 onload="javascript:document.new_encounter.reason.focus();">

<form method=post action="<?echo $rootdir?>/forms/newpatient/save.php" name=new_encounter target=Main>
<input type=hidden name=mode value='new'>
<span class=title>New Encounter Form</span>
<br>

<table>

<tr><td><select name='facility'>
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
</select></td><td></td></tr>

<tr><td><span class=text>Chief Complaint:</span></td><td></td></tr>
<tr><td><textarea name='reason' cols='40' rows='6' wrap='virtual'></textarea></td></td></tr>

<tr><td><span class='text'>Date Of Service:</span></td>
<td><select name='month'>
<?
foreach($months as $month){
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
</select></td></tr>

<tr><td><span class='text'>Date of onset or hospitalization:</span></td>
<td><select name='onset_month'>
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
</select></td></tr>

</table>

<a href="javascript:document.new_encounter.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?
if (isset($_GET["autoloaded"]) && $_GET["autoloaded"] == "1") {
} else {
?>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link">[Don't Save]</a>
<?
}
?>

</form>

</body>
</html>
