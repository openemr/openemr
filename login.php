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

<?
$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
	$result[$iter] = $row;
if (count($result) == 1) {
	$resvalue = $result[0]{"name"};
	echo "<input type='hidden' name='authProvider' value='$resvalue' />\n";
}
?>

<table width=100% height="90%">
<tr>
<td valign=middle width=33%>
<?echo $logocode;?>
</td>
<td align='center' valign='middle' width=34%>
<table>
<?
if (count($result) != 1) {
?>
<tr>
<td><span class="text">Group:</span></td>
<td>
<select name=authProvider>
<?
	foreach ($result as $iter) {
		echo "<option value='".$iter{"name"}."'>".$iter{"name"}."</option>\n";
	}
?>
</select>
</td></tr>
<?
}
?>
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
<td width=33%>
<center>
<table>
<tr><td>User:</td><td>Password:</td></tr> 
<tr><td>clerk</td><td>clerk1</td></tr> 
<tr><td>demo</td><td>pass</td></tr> 
<tr><td>drwhitehair</td><td>drwhitehair1</td></tr> 
<tr><td>admin</td><td>pass</td></tr>

</center></p>

</td>
</table>



</form>
<br><br>
<center>
<address>
<a href="copyright_notice.html" target="main">Copyright Notice:</a><br />
<a href="http://www.openmedsoftware.org/" target="main">Return to Open Source Medical Software</a>
</address>

</center>
</body>
</html>
