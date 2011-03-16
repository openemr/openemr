<?php
include_once("../globals.php");
include_once("$srcdir/sha1.js");
include_once("$srcdir/sql.inc");
include_once("$srcdir/auth.inc");
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script src="checkpwd_validation.js" type="text/javascript"></script>
<script language='JavaScript'>
//Validating password and display message if password field is empty - starts
function pwdvalidation()
{
  var password1=trim(document.user_form.clearPass.value);
  var password2=trim(document.user_form.clearPass2.value);
  document.getElementById("display_msg").innerHTML="";
  if (password1 == "")
  {
	alert("<?php echo xl('Please enter the password'); ?>");
    document.user_form.clearPass.focus();
    return false;
  }
  if (password2 == "")
  {
	alert("<?php echo xl('Please enter the password'); ?>");
    document.user_form.clearPass2.focus();
    return false;
  }
  if (password1 != password2)
  {
	alert("<?php echo xl('Error: passwords don\'t match. Please check your typing.'); ?>");
    document.user_form.clearPass.value="";
    document.user_form.clearPass2.value="";
    document.user_form.clearPass.focus();
    return false;
  }
//Checking for the strong password if the 'secure password' feature is enabled
  if(document.user_form.secure_pwd.value == 1){ 
  var pwdresult = passwordvalidate(password1);
  if  (pwdresult == 0){
    alert("<?php echo xl('The password must be at least eight characters, and should'); echo '\n'; echo xl('contain at least three of the four following items:'); echo '\n'; echo xl('A number'); echo '\n'; echo xl('A lowercase letter'); echo '\n'; echo xl('An uppercase letter'); echo '\n'; echo xl('A special character');echo '('; echo xl('not a letter or number'); echo ').'; echo '\n'; echo xl('For example:'); echo ' healthCare@09'; ?>");
    document.user_form.clearPass.value="";
    document.user_form.clearPass2.value="";
    document.user_form.clearPass.focus();
    return false;
  }
}
  // ViCareplus : As per NIST standard, SHA1 encryption algorithm is used
  document.user_form.authPass.value=SHA1(document.user_form.clearPass.value);
  document.user_form.clearPass.value='';
  document.user_form.authPass2.value=SHA1(document.user_form.clearPass2.value);
  document.user_form.clearPass2.value='';
}

</script>
</head>
<body class="body_top">

<span class="title"><?php xl('Password Change','e'); ?></span>
<br><br>

<?php

$update_pwd_failed=0;
$ip=$_SERVER['REMOTE_ADDR'];
if ($_GET["mode"] == "update") {
  if ($_GET["authPass"] && $_GET["authPass2"] && $_GET["authPass"] != "da39a3ee5e6b4b0d3255bfef95601890afd80709") { // account for empty
    $tqvar = addslashes($_GET["authPass"]);
    $tqvar2 = addslashes($_GET["authPass2"]);
    if ($tqvar == $tqvar2)  {

   // Validating the password  
    if($GLOBALS['password_history'] != 0){
      $updatepwd = UpdatePasswordHistory($_SESSION["authId"],$tqvar);  
      }else {
      sqlStatement("update users set password='$tqvar' where id={$_SESSION["authId"]}");
      $updatepwd=1;	
     }
      if ($updatepwd == 1) {
        echo "<span class='alert'>".xl("Password change successful.",'','',' ').xl("Click")."<a href='$rootdir/logout.php?auth=logout' class=link_submit>".xl("here",'',' ',' ')."</a>".xl("to login again").".<br><br></span>";
      } else {
        $update_pwd_failed=1;
      }
    }
    else {
      echo "<span class=alert>" . xl("Error: passwords don't match. Please check your typing.") . "</span><br><br>\n";
    }
  }
}

$res = sqlStatement("select * from users where id={$_SESSION["authId"]}"); 
$row = sqlFetchArray($res);
      $iter=$row;
?>
<div id="display_msg">
<?
if ($update_pwd_failed==1) //display message if entered password matched one of last three passwords.
{
  echo "<font class='redtext'>". xl("Recent three passwords are not allowed.") ."</font>";
}
?>
</div>
<br>
<span class="text"><?php xl('Once you change your password, you will have to re-login.','e'); ?><br></span>
<FORM NAME="user_form" METHOD="GET" ACTION="user_info.php"
 onsubmit="top.restoreSession()">
<input type=hidden name=secure_pwd value="<? echo $GLOBALS['secure_password']; ?>">
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
<INPUT TYPE="Submit" VALUE=<?php xl('Save Changes','e','\'','\''); ?> onClick="return pwdvalidation()">

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
//  da39a3ee5e6b4b0d3255bfef95601890afd80709 == blank
?>
