<?
$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
?>



<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $login_body_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 onload="javascript:document.login_form.authUser.focus();" >




<span class="text"></span>

<center>



<form method="POST" action="../main/main_screen.php?auth=login" target="_top" name=login_form>

<table width=100% height="90%">
<tr>
<td valign=middle width=30%>
<?echo $logocode;?>
</td>
<td valign=middle width=30%>
<table><tr>
<td><span class="text">Group:</span></td>
<td>
<select name=authProvider>
<?

$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result[$iter] = $row;

foreach ($result as $iter) {
echo "<option value='".$iter{"name"}."'>".$iter{"name"}."</option>\n";
}


?>
</select>
</td></tr>

<tr>
<td><span class="text">Username:</span></td>
<td>
<input type="entry" size=10 name=authUser>
</td></tr><tr>
<td><span class="text">Password:</span></td>
<td>
<input type="password" size=10 name=clearPass>
</td></tr>
<tr><td>&nbsp;</td><td>
<input type="hidden" name="authPass">
<input type="submit" onClick="javascript:this.form.authPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';" value="Login">
</td></tr>
</table>

</td>
<td width=30%>

<p><center>login = admin
<br>password = pass
</center></p>

</td>
</table>



</form>

<address>
<strong>
Copyright &copy; 2003-2004 <a href="www.pennfirm.com"><strong>Pennington Firm</strong></a></strong><br />
Copyright &copy; -2003 Synitech Inc.
</address>

</center>
</body>
</html>



