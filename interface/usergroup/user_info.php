<?php
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<span class="title"><?php xl('Password Change','e'); ?></span>
<br><br>

<?php

if ($_GET["mode"] == "update") {
	if ($_GET["authPass"] && $_GET["authPass2"] && $_GET["authPass"] != "d41d8cd98f00b204e9800998ecf8427e") { // account for empty
		$tqvar = addslashes($_GET["authPass"]);
		$tqvar2 = addslashes($_GET["authPass2"]);
		if ($tqvar == $tqvar2) {
			sqlStatement("update users set password='$tqvar' where id={$_SESSION['authId']}");
			echo "<span class='alert'>Password change successful.  Click <a href='$rootdir/logout.php?auth=logout' class=link_submit> here </a>to login again.<br><br></span>";
		}
		else
			echo "<span class=alert>Error: passwords don't match.  Please check your typing.</span><br><br>\n";
	}
}

$res = sqlStatement("select * from users where id={$_SESSION["authId"]}"); 
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
$iter = $result[0];

?>
<span class="text"><?php xl('Once you change your password, you will have to re-login.','e'); ?><br></span>
<FORM NAME="user_form" METHOD="GET" ACTION="user_info.php"
 onsubmit="top.restoreSession()">
<TABLE>
<TR>
<TD><span class=text><?php xl('Real Name','e'); ?>: </span></TD>
<TD><span class=text><?php echo $iter["realname"]; ?></span></td>
</TR>

<TR>
<TD><span class=text><?php xl('Username','e'); ?>: </span></TD>
<TD><span class=text><?php echo $iter["username"]; ?></span></td>
</TR>

<TR>
<TD><span class=text><?php xl('Password','e'); ?>: </span></TD>
<TD><input type=password name=clearPass size=20 value=""></td>
</TR>
<TR>
<TD><span class=text><?php xl('Password','e'); ?> (<?xl('Again','e');?>): </span></TD>
<TD><input type=password name=clearPass2 size=20 value=""></td>
</TR>

</TABLE>
<br>&nbsp;&nbsp;&nbsp;
<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo $_GET["id"]; ?>">
<INPUT TYPE="HIDDEN" NAME="mode" VALUE="update">
<INPUT TYPE="HIDDEN" NAME="authPass" VALUE="">
<INPUT TYPE="HIDDEN" NAME="authPass2" VALUE="">
<INPUT TYPE="Submit" VALUE="Save Changes" onClick="javascript:this.form.authPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';this.form.authPass2.value=MD5(this.form.clearPass2.value);this.form.clearPass2.value='';">

<?php if (! $GLOBALS['concurrent_layout']) { ?>
&nbsp;&nbsp;&nbsp;
[<a href="../main/main_screen.php" target="_top" class="link_submit"
  onclick="top.restoreSession()"><?php xl('Back','e'); ?></font></a>]
<?php } ?>

</FORM>

<br><br>
</BODY>
</HTML>

<?php
//  d41d8cd98f00b204e9800998ecf8427e == blank
?>
