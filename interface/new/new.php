<?
include_once("../globals.php");



?>


<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 onload="javascript:document.new_patient.fname.focus();">

<form name=new_patient method=post action="new_patient_save.php" target=_top>
<a class="title" href="../main/main_screen.php" target="_top">New Patient</a>

<br><br>

<table border=0>
<tr>
<td>
<span class=bold>Title: </span></td><td>
<select name=title>
	<option value="Mr.">Mr.</option>
	<option value="Mrs.">Mrs.</option>
	<option value="Ms.">Ms.</option>
	<option value="Dr.">Dr.</option>
</select>
</td>
</tr>

<tr>
<td>
<span class=bold>First Name: </span></td><td>
<input type=entry size=15 name=fname>
</td></tr>

<tr>
<td>
<span class=bold>Middle Name: </span></td><td>
<input type=entry size=15 name=mname>
</td></tr>

<tr>
<td>
<span class=bold>Last Name: </span></td><td>
<input type=entry size=15 name=lname>
</td></tr>

<tr>
<td>
<span class=bold>Patient Number: </span></td><td>
<input type=entry size=5 name=pubpid>
<span class=text>Leave blank to autoassign</span>
</td></tr>

<tr><td colspan=2>
<a href="javascript:document.new_patient.submit();" class=link_submit>[New Patient File]</a>
</td></tr>
</table>
</form>


</body>
</html>
