<?
include_once("../../globals.php");
?>

<html>
<head>
<title>Navigation</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<form border=0 method=post target="_top" name="find_patient" action="<?echo $rootdir?>/main/finder/patient_finder.php">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<td width="10%" nowrap>
<?//<a href="javascript:document.find_patient.action='finder/patient_finder_keyboard.php';document.find_patient.submit();" class=link>Find Patient:</a>?>
<input type=entry size=10 name=patient> <select name="findBy" size=1> 
	<option value="ID"><?xl('ID','e');?></option>
	<option value="Last" selected><?xl('Name','e');?></option>
	<option value="SSN"><?xl('SSN','e');?></option>
	<option value="DOB"><?xl('DOB','e');?></option>
</select>

</td>

<td width="5%" nowrap>
<a href="javascript:document.find_patient.action='<?echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();" class=link>&nbsp;<?xl('Find Patient','e');?></a>
</td>

<td width="48%" nowrap>
&nbsp;&nbsp;&nbsp;<a class=menu target=_top href="../../new/new_patient.php"><?xl('New Patient','e');?></a>&nbsp;
</td>

<td align="right" nowrap>
<?//<a href="../logout.php?auth=logout" target="_top" class="logout">Logout</a>?>
&nbsp;<a href="../main_screen.php" target="_top" class="logout"><?xl('Back','e');?></a>&nbsp;&nbsp;
</td>
</tr>
</table>

</form>

</body>
</html>
