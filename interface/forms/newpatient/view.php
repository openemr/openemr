<?
include_once("../../globals.php");

$result = sqlQuery("select * from form_encounter where id='$id'");
?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<html>
<head>
<title>New Patient Encounter</title>

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form method=post action="<?echo $rootdir?>/forms/newpatient/save.php" name=new_encounter target=Main>
<input type=hidden name=mode value='update'>
<input type=hidden name=id value='<?echo $_GET["id"];?>'>
<span class=title>New Patient Encounter Form</span>
<br>
<br>
<select name=facility>
<?
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
for ($iter = 0;$frow = sqlFetchArray($fres);$iter++)
                $fresult[$iter] = $frow;
foreach($fresult as $iter) {
?>
<option <?if ($iter{name} == $result{facility}) {echo "selected";};?> value="<?echo $iter{name};?>"><?echo $iter{name};?></option>
<?
}
}
?>
</select>
<br>
<span class=text>Chief Complaint:</span><br>
<textarea name=reason cols=40 rows=6 wrap=virtual><?

echo $result{"reason"};

?></textarea>

<br>

<a href="javascript:document.new_encounter.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>


</form>

</body>
</html>
