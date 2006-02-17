<?
include_once("../globals.php");
include_once("$srcdir/auth.inc");
include_once("../../library/acl.inc");

include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<a href="usergroup_admin.php"><span class="title"><?xl('User Administration','e');?></span></a>
<br><br>

<?
if (!$_GET["id"] || !acl_check('admin', 'users'))
  exit();

if ($_GET["mode"] == "update") {
  if ($_GET["username"]) {
    $tqvar = addslashes($_GET["username"]);
    $user_data = mysql_fetch_array(sqlStatement("select * from users where id={$_GET["id"]}"));
    sqlStatement("update users set username='$tqvar' where id={$_GET["id"]}");
    sqlStatement("update groups set user='$tqvar' where user='". $user_data["username"]  ."'");
    //echo "query was: " ."update groups set user='$tqvar' where user='". $user_data["username"]  ."'" ;
  }
  if ($_GET["taxid"]) {
    $tqvar = addslashes($_GET["taxid"]);
    sqlStatement("update users set federaltaxid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["drugid"]) {
    $tqvar = addslashes($_GET["drugid"]);
    sqlStatement("update users set federaldrugid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["upin"]) {
    $tqvar = addslashes($_GET["upin"]);
    sqlStatement("update users set upin='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["lname"]) {
    $tqvar = addslashes($_GET["lname"]);
    sqlStatement("update users set lname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["mname"]) {
          $tqvar = addslashes($_GET["mname"]);
          sqlStatement("update users set mname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["facility"]) {
          $tqvar = addslashes($_GET["facility"]);
          sqlStatement("update users set facility='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["fname"]) {
          $tqvar = addslashes($_GET["fname"]);
          sqlStatement("update users set fname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["newauthPass"] && $_GET["newauthPass"] != "d41d8cd98f00b204e9800998ecf8427e") { // account for empty
    $tqvar = addslashes($_GET["newauthPass"]);
    sqlStatement("update users set password='$tqvar' where id={$_GET["id"]}");
  }

  if ($_GET["authorized"] == "on")
    $tqvar = 1;
  else
    $tqvar = 0;

  sqlStatement("UPDATE users SET authorized = $tqvar, " .
    "see_auth = '" . $_GET['see_auth'] . "' WHERE " .
    "id = {$_GET["id"]}");

  if ($_GET["comments"]) {
    $tqvar = addslashes($_GET["comments"]);
    sqlStatement("update users set info='$tqvar' where id={$_GET["id"]}");
  }
  $ws = new WSProvider($_GET['id']);
}

$res = sqlStatement("select * from users where id={$_GET["id"]}");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
$iter = $result[0];

?>
<FORM NAME="user_form" METHOD="GET" ACTION="user_admin.php">
<TABLE border=0 cellpadding=0 cellspacing=0>
<TR>
<TD><span class=text><?xl('Username','e');?>: </span></TD><TD><input type=entry name=username size=20 value="<? echo $iter["username"]; ?>" disabled> &nbsp;</td>
<TD><span class=text><?xl('Password','e');?>: </span></TD><TD class='text'><input type=password name=clearPass size=20 value=""> * <?xl('Leave blank to keep password unchanged.','e');?></td>
</TR>

<TR>
<td><span class="text">&nbsp;</span></td><td>&nbsp;</td>
<TD><span class=text><?xl('Authorized','e');?>: </TD>
<TD><INPUT TYPE="checkbox" name="authorized"<?
if ($iter["authorized"] == 1)
	echo " checked";
?>></TD>
</TR>

<TR>
<TD><span class=text><?xl('First Name','e');?>: </span></TD>
<TD><input type=entry name=fname size=20 value="<? echo $iter["fname"]; ?>"></td>
<td><span class=text><?xl('Middle Name','e');?>: </span></TD><td><input type=entry name=mname size=20 value="<? echo $iter["mname"]; ?>"></td>
</TR>

<TR>
<td><span class=text><?xl('Last Name','e');?>: </span></td><td><input type=entry name=lname size=20 value="<? echo $iter["lname"]; ?>"></td>
<td><span class=text><?xl('Default Facility','e');?>: </span></td><td><select name=facility>
<?
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
for ($iter2 = 0;$frow = sqlFetchArray($fres);$iter2++)
                $result[$iter2] = $frow;
foreach($result as $iter2) {
?>
<option value="<?echo $iter2{name};?>" <?if ($iter{"facility"} == $iter2{name}) {echo "selected";};?>><?echo $iter2{name};?></option>
<?
}
}
?>
</select></td>
</tr>

<TR>
<TD><span class=text><?xl('Federal Tax ID','e');?>: </span></TD><TD><input type=text name=taxid size=20 value="<? echo $iter["federaltaxid"]?>"></td>
<TD><span class=text><?xl('Federal Drug ID','e');?>: </span></TD><TD><input type=text name=drugid size=20 value="<? echo $iter["federaldrugid"]?>"></td>
</TR>

<tr>
<td><span class="text"><?xl('UPIN','e');?>: </span></td><td><input type="text" name="upin" size="20" value="<? echo $iter["upin"]?>"></td>
<td class='text'><?xl('See Authorizations','e');?>: </td>
<td><select name="see_auth">
<?php
 foreach (array(1 => xl('None'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value)
 {
  echo " <option value='$key'";
  if ($key == $iter['see_auth']) echo " selected";
  echo ">$value</option>\n";
 }
?>
</select></td>
</tr>

</table>
<span class=text><?xl('Additional Info','e');?>:</span><br>
<textarea name="comments" wrap=auto rows=4 cols=30><? echo $iter["info"];?></textarea>

<br>&nbsp;&nbsp;&nbsp;
<INPUT TYPE="HIDDEN" NAME="id" VALUE="<? echo $_GET["id"]; ?>">
<INPUT TYPE="HIDDEN" NAME="mode" VALUE="update">
<INPUT TYPE="HIDDEN" NAME="newauthPass" VALUE="">
<INPUT TYPE="Submit" VALUE=<?xl('Save Changes','e');?> onClick="javascript:this.form.newauthPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';">
&nbsp;&nbsp;&nbsp;
<a href="usergroup_admin.php" class=link_submit>[<?xl('Back','e');?>]</font></a>
</FORM>

<br><br>
</BODY>
</HTML>

<?
//  d41d8cd98f00b204e9800998ecf8427e == blank

?>
