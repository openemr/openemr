<?php
include_once("../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/auth.inc");
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script src="checkpwd_validation.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script>

<script language='JavaScript'>
//Validating password and display message if password field is empty - starts
var webroot='<?php echo $webroot?>';
function update_password()
{
    top.restoreSession();
    // Not Empty
    // Strong if required
    // Matches

    $.post("user_info_ajax.php",
        {
            curPass:    $("input[name='curPass']").val(),
            newPass:    $("input[name='newPass']").val(),
            newPass2:   $("input[name='newPass2']").val(),
        },
        function(data)
        {
            $("input[type='password']").val("");
            $("#display_msg").html(data);
        }

    );
    return false;
}

</script>
</head>
<body class="body_top">

<span class="title"><?php echo xlt('Password Change'); ?></span>
<br><br>

<?php

$ip=$_SERVER['REMOTE_ADDR'];
$res = sqlStatement("select fname,lname,username from users where id=?",array($_SESSION["authId"]));
$row = sqlFetchArray($res);
      $iter=$row;
?>
<div id="display_msg">
</div>
<br>
<FORM NAME="user_form" METHOD="POST" ACTION="user_info.php"
 onsubmit="top.restoreSession()">
<input type=hidden name=secure_pwd value="<?php echo $GLOBALS['secure_password']; ?>">
<TABLE>
<TR>
<TD><span class=text><?php xl('Full Name','e'); ?>: </span></TD>
<TD><span class=text><?php echo htmlspecialchars($iter["fname"] . " " . $iter["lname"], ENT_NOQUOTES); ?></span></td>
</TR>

<TR>
<TD><span class=text><?php xl('Username','e'); ?>: </span></TD>
<TD><span class=text><?php echo $iter["username"]; ?></span></td>
</TR>

<TR>
<TD><span class=text><?php xl('Current Password','e'); ?>: </span></TD>
<TD><input type=password name=curPass size=20 value="" autocomplete='off'></td>
</TR>

<TR>
<TD><span class=text><?php xl('New Password','e'); ?>: </span></TD>
<TD><input type=password name=newPass size=20 value="" autocomplete='off'></td>
</TR>
<TR>
<TD><span class=text><?php xl('Repeat New Password','e'); ?>: </span></TD>
<TD><input type=password name=newPass2 size=20 value="" autocomplete='off'></td>
</TR>

</TABLE>
<br>&nbsp;&nbsp;&nbsp;
<INPUT TYPE="Submit" VALUE=<?php echo xla('Save Changes'); ?> onClick="return update_password()">

</FORM>

<br><br>
</BODY>
</HTML>

<?php
//  da39a3ee5e6b4b0d3255bfef95601890afd80709 == blank
?>
