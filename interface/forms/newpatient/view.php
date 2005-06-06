<?
include_once("../../globals.php");
include_once("../../../library/acl.inc");

$disabled = "disabled";

// If we are allowed to change encounter dates...
if (acl_check('encounters', 'date_a')) {
	$disabled = "";
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17",
	"18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$years = array("2004","2005","2006","2007");

$result = sqlQuery("select * from form_encounter where id='$id'");

$enc_year  = substr($result{'date'}, 0, 4);
$enc_month = substr($result{'date'}, 5, 2);
$enc_day   = substr($result{'date'}, 8, 2);
$ons_year  = substr($result{'onset_date'}, 0, 4);
$ons_month = substr($result{'onset_date'}, 5, 2);
$ons_day   = substr($result{'onset_date'}, 8, 2);
?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<html>
<head>
<title>Patient Encounter</title>

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form method=post action="<?echo $rootdir?>/forms/newpatient/save.php" name=new_encounter target=Main>
<input type=hidden name=mode value='update'>
<input type=hidden name=id value='<?echo $_GET["id"];?>'>
<span class=title>Patient Encounter Form</span>
<br>

<table>

<tr><td><select name='facility'>
<?
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
                $result[$iter] = $frow;
foreach($result as $iter) {
?>
<option <?if ($iter{name} == $result{facility}) {echo "selected";};?> value="<?echo $iter{name};?>"><?echo $iter{name};?></option>
<?
}
}
?>
</select></td><td></td></tr>

<tr><td><span class=text>Chief Complaint:</span></td><td></td></tr>
<tr><td><textarea name=reason cols=40 rows=6 wrap=virtual><?
echo $result{"reason"};
?></textarea>
</td></td></tr>

<tr><td><span class='text'>Date Of Service:</span></td>
<td><select name='month' <? echo $disabled ?>>
<?
foreach($months as $month){
?>
<option value="<?echo $month;?>" <?if($month == $enc_month) echo "selected";?>><?echo $month?></option>
<?
}
?>
</select>
<select name='day' <? echo $disabled ?>>
<?
foreach($days as $day){
?>
<option value="<?echo $day;?>" <?if($day == $enc_day) echo "selected";?>><?echo $day?></option>
<?
}
?>
</select>
<select name='year' <? echo $disabled ?>>
<?
foreach($years as $year){
?>
<option value="<?echo $year;?>" <?if($year == $enc_year) echo "selected";?>><?echo $year?></option>
<?
}
?>
</select></td></tr>

<tr><td><span class='text'>Date of onset or hospitalization:</span></td>
<td><select name='onset_month'>
<?
foreach($months as $month){
?>
<option value="<?echo $month;?>" <?if($month == $ons_month) echo "selected";?>><?echo $month?></option>
<?
}
?>
</select>
<select name='onset_day'>
<?
foreach($days as $day){
?>
<option value="<?echo $day;?>" <?if($day == $ons_day) echo "selected";?>><?echo $day?></option>
<?
}
?>
</select>
<select name='onset_year'>
<?
foreach($years as $year){
?>
<option value="<?echo $year;?>" <?if($year == $ons_year) echo "selected";?>><?echo $year?></option>
<?
}
?>
</select></td></tr>

</table>

<a href="javascript:document.new_encounter.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>

</form>

</body>
</html>
