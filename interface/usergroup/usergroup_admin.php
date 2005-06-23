<?
include_once("../globals.php");


include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");

if (isset($_POST["mode"])) {
if ($_POST["mode"] == "facility")
{
	 sqlStatement("insert into facility set
	 	name='{$_POST['facility']}',
		phone='{$_POST['phone']}',
		street='{$_POST['street']}',
		city='{$_POST['city']}',
		state='{$_POST['state']}',
		postal_code='{$_POST['postal_code']}',
		country_code='{$_POST['country_code']}',
		federal_ein='{$_POST['federal_ein']}'");
}elseif ($_POST["mode"] == "new_user") {
	if ($_POST["authorized"] != "1") {
		$_POST["authorized"] = 0;
	}
	$_POST["info"] = addslashes($_POST["info"]);

	$res = sqlStatement("select distinct username from users");
	$doit = true;
	while ($row = mysql_fetch_array($res)) {
		if ($doit == true && $row['username'] == $_POST["username"]) {
			$doit = false;
		}
	}

	if ($doit == true) {
		$prov_id = idSqlStatement("insert into users set " .
			"username = '"         . $_POST["username"] .
			"', password = '"      . $_POST["newauthPass"] .
			"', fname = '"         . $_POST["fname"] .
			"', mname = '"         . $_POST["mname"] .
			"', lname = '"         . $_POST["lname"] .
			"', federaltaxid = '"  . $_POST["federaltaxid"] .
			"', authorized = '"    . $_POST["authorized"] .
			"', info = '"          . $_POST["info"] .
			"', federaldrugid = '" . $_POST["federaldrugid"] .
			"', upin = '"          . $_POST["upin"] .
			"', facility = '"      . $_POST["facility"] .
			"'");
		sqlStatement("insert into groups set name='".$_POST["groupname"]."',user='".$_POST["username"]."'");
		$ws = new WSProvider($prov_id);
	}
} 
elseif ($_POST["mode"] == "new_group") {

$res = sqlStatement("select distinct name,user from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++) 
                $result[$iter] = $row;
$doit = 1;
foreach ($result as $iter) {
	if ($doit == 1 && $iter{"name"} == $_POST["groupname"] && $iter{"user"} == $_POST["username"])
		$doit--;
}
if ($doit == 1)
	sqlStatement("insert into groups set name='".$_POST["groupname"]."',user='".$_POST["username"]."'");
}

}

if (isset($_GET["mode"])) {
if ($_GET["mode"] == "delete") {
$res = sqlStatement("select distinct username,id from users where id={$_GET["id"]}");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
foreach($result as $iter) {
	sqlStatement("delete from groups where user='".$iter{"username"}."'");
}
sqlStatement("delete from users where id='".$_GET["id"]."'");
} elseif ($_GET["mode"] == "delete_group") {

$res = sqlStatement("select distinct user from groups where id={$_GET["id"]}");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
foreach($result as $iter) 
	$un = $iter{"user"};

$res = sqlStatement("select name,user from groups where user='".$iter{"user"}."' and id!={$_GET["id"]}\n");
if (sqlFetchArray($res) != FALSE) 
sqlStatement("delete from groups where id='".$_GET["id"]."'");
}
}


?>


<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>



<span class="title">User & Group Administration</span>

<br><br>

<table width=100%>
<tr>

<td valign=top>

<form name='facility' method='post' action="usergroup_admin.php">
<input type=hidden name=mode value="facility">
<span class=bold>New Facility Information: </span>
</td><td>

<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td><span class=text>Name: </span></td><td><input type=entry name=facility size=20 value=""></td>
<td><span class=text>Phone: </span></td><td><input type=entry name=phone size=20 value=""></td>
</tr>
<tr>
<td><span class=text>Address: </span></td><td><input type=entry size=20 name=street value=""></td>
<td><span class=text>City: </span></td><td><input type=entry size=20 name=city value=""></td>
</tr>
<tr>
<td><span class=text>State: </span></td><td><input type=entry size=20 name=state value=""></td>
<td><span class=text>Zip Code: </span></td><td><input type=entry size=20 name=postal_code value=""></td>
</tr>
<tr>
<td><span class=text>Country: </span></td><td><input type=entry size=20 name=country_code value=""></td>
<td><span class=text>Federal EIN: </span></td><td><input type=entry size=20 name=federal_ein value=""></td>
</tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>
<td>&nbsp;</td><td><input type="submit" value="Add Facility"></td>
</tr>
</table>
</form>
<br><br>
</tr>
<tr>
<td valign=top>

<form name='facility' method='post' action="usergroup_admin.php">
<input type=hidden name=mode value="facility">
<span class=bold>Edit Facilities: </span>
</td><td valign=top>
<?
$fres = 0;
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
$result2 = array();
for ($iter3 = 0;$frow = sqlFetchArray($fres);$iter3++)
                $result2[$iter3] = $frow;
foreach($result2 as $iter3) {
?>
<span class=text><?echo $iter3{name};?></span><a href="facility_admin.php?fid=<?echo $iter3{id};?>" class=link_submit>(Edit)</a><br>


<?
}
}
?>

</td>
</tr>
<tr><td valign=top>
<form name='new_user' method='post' action="usergroup_admin.php">
<input type=hidden name=mode value=new_user>
<span class=bold>New User:</span>
</td><td>
<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td><span class=text>Username: </span></td><td><input type=entry name=username size=20></td>
<td><span class=text>Password: </span></td><td><input type="password" size=20 name=clearPass></td>
</tr>
<tr>
<td><span class=text>Groupname: </span></td><td>
<select name=groupname>
<?
$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result2[$iter] = $row;
foreach ($result2 as $iter) {
print "<option value='".$iter{"name"}."'>" . $iter{"name"} . "</option>\n";
}
?>
</select></td>
<td><span class=text>Authorized: </span></td><td><input type=checkbox name='authorized' value="1"></td>
</tr>
<tr>
<td><span class=text>First Name: </span></td><td><input type=entry name='fname' size=20></td>
<td><span class=text>Middle Name: </span></td><td><input type=entry name='mname' size=20></td>
</tr>
<tr>
<td><span class=text>Last Name: </span></td><td><input type=entry name='lname' size=20></td>
<td><span class=text>Default Facility: </span></td><td><select name=facility>
<?
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
for ($iter = 0;$frow = sqlFetchArray($fres);$iter++)
                $result[$iter] = $frow;
foreach($result as $iter) {
?>
<option value="<?echo $iter{name};?>"><?echo $iter{name};?></option>
<?
}
}
?>
</select></td>
</tr>
<tr>
<td><span class=text>Federal Tax ID: </span></td><td><input type=entry name='federaltaxid' size=20></td>
<td><span class=text>Federal Drug ID: </span></td><td><input type=entry name='federaldrugid' size=20></td>
</tr>
<tr>
<td><span class="text">UPIN: </span></td><td><input type="entry" name="upin" size="20"></td>
<td><span class=text>&nbsp;</span></td><td>&nbsp;</td>
</tr>
</table>
<span class=text>Additional Info: </span><br>
<textarea name=info cols=40 rows=4 wrap=auto></textarea>
<br><input type="hidden" name="newauthPass">
<input type="submit" onClick="javascript:this.form.newauthPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';" value="Add User">
</form>
</td>

</tr><tr>

<td valign=top>
<form name=new_group method=post action="usergroup_admin.php">
<input type=hidden name=mode value=new_group>
<span class=bold>New Group:</span>
</td><td>
<span class=text>Groupname: </span><input type=entry name=groupname size=10>
&nbsp;&nbsp;&nbsp;
<span class=text>Initial User: </span>
<select name=username>
<?
$res = sqlStatement("select distinct username from users");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result[$iter] = $row;
foreach ($result as $iter) {
print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value="Add Group">
</form>
</td>

</tr><tr>

<td valign=top>
<form name=new_group method=post action="usergroup_admin.php">
<input type=hidden name=mode value=new_group>
<span class=bold>Add User To Group:</span>
</td><td>
<span class=text>User: </span>
<select name=username>
<?
$res = sqlStatement("select distinct username from users");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result3[$iter] = $row;
foreach ($result3 as $iter) {
print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<span class=text>Groupname: </span>
<select name=groupname>
<?
$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result2[$iter] = $row;
foreach ($result2 as $iter) {
print "<option value='".$iter{"name"}."'>" . $iter{"name"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value="Add User To Group">
</form>
</td>





</tr>
</table>

<hr>



<table border=0 cellpadding=1 cellspacing=2>
<tr><td><span class=bold>Username</span></td><td><span class=bold>Real Name</span></td><td><span class=bold>Info</span></td><td><span class=bold>Authorized?</span></td></tr>
<?
$res = sqlStatement("select * from users order by username");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result4[$iter] = $row;
foreach ($result4 as $iter) {
if ($iter{"authorized"}) {
$iter{"authorized"} = "yes";
} else {
$iter{"authorized"} = "";
}

print "<tr><td><span class=text>".$iter{"username"}."</span><a href='user_admin.php?id=".$iter{"id"}."' class=link_submit>(Edit)</a></td><td><span class=text>".$iter{"fname"}.' '.$iter{"lname"}."</span></td><td><span class=text>".$iter{"info"}."</span></td><td align='center'><span class=text>".$iter{"authorized"}."</span></td>";
print "<td><!--<a href='usergroup_admin.php?mode=delete&id=".$iter{"id"}."' class=link_submit>[Delete]</a>--></td>";
print "</tr>\n";
}



?>

</table>


<hr>


<?
$res = sqlStatement("select * from groups order by name");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result5[$iter] = $row;

foreach ($result5 as $iter) {
$grouplist{$iter{"name"}} .= $iter{"user"} . "(<a class=link_submit href='usergroup_admin.php?mode=delete_group&id=".$iter{"id"}."'>Remove</a>), ";


}

foreach ($grouplist as $groupname => $list) {
print "<span class=bold>" . $groupname . "</span><br>\n<span class=text>" . substr($list,0,strlen($list)-2) . "</span><br>\n";
}


?>




</body>
</html>
