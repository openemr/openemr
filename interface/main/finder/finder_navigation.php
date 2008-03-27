<?php 
include_once("../../globals.php");
?>

<html>
<head>
<?php html_header_show();?>
<title>Navigation</title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_nav">

<form border='0' method='post' target="_top"
 name="find_patient" action="<?php echo $rootdir?>/main/finder/patient_finder.php"
 onsubmit="return top.restoreSession()">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<td width="10%" nowrap>
<input type=entry size=10 name=patient> <select name="findBy" size=1> 
	<option value="ID"><?php xl('ID','e');?></option>
	<option value="Last" selected><?php xl('Name','e');?></option>
	<option value="SSN"><?php xl('SSN','e');?></option>
	<option value="DOB"><?php xl('DOB','e');?></option>
</select>

</td>

<td width="5%" nowrap>
<a href="javascript:top.restoreSession();document.find_patient.action='<?php echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();" class="link">&nbsp;<?php xl('Find Patient','e');?></a>
</td>

<td width="48%" nowrap>
&nbsp;&nbsp;&nbsp;<a class="menu" target="_top" href="../../new/new_patient.php" onclick="top.restoreSession()">
<?php xl('New Patient','e');?></a>&nbsp;
</td>

<td align="right" nowrap>
&nbsp;<a href="../main_screen.php" target="_top" class="logout" onclick="top.restoreSession()">
<?php xl('Back','e');?></a>&nbsp;&nbsp;
</td>
</tr>
</table>

</form>

</body>
</html>
