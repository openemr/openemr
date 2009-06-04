<?php
$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<script language='JavaScript'>

function imsubmitted() {
<?php if (!empty($GLOBALS['restore_sessions'])) { ?>
 // Delete the session cookie by setting its expiration date in the past.
 // This forces the server to create a new session ID.
 var olddate = new Date();
 olddate.setFullYear(olddate.getFullYear() - 1);
 document.cookie = '<?php echo session_name() . '=' . session_id() ?>; path=/; expires=' + olddate.toGMTString();
<?php } ?>
 return true;
}

</script>

</head>
<body <?php echo $login_body_line;?> onload="javascript:document.login_form.authUser.focus();" >

<span class="text"></span>

<center>

<form method="POST" action="../main/main_screen.php?auth=login" target="_top"
 name="login_form" onsubmit="return imsubmitted();">

<?php
// collect groups
$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
	$result[$iter] = $row;
if (count($result) == 1) {
	$resvalue = $result[0]{"name"};
	echo "<input type='hidden' name='authProvider' value='$resvalue' />\n";
}
// collect default language id
$res2 = sqlStatement("select * from lang_languages where lang_description = '".$GLOBALS['language_default']."'");
for ($iter = 0;$row = sqlFetchArray($res2);$iter++)
          $result2[$iter] = $row;
if (count($result2) == 1) {
          $defaultLangID = $result2[0]{"lang_id"};
          $defaultLangName = $result2[0]{"lang_description"};
}
else {
          //default to english if any problems
          $defaultLangID = 1;
          $defaultLangName = "English";
}
// set session variable to default so login information appears in default language
$_SESSION['language_choice'] = $defaultLangID;
// collect languages if showing language menu
if ($GLOBALS['language_menu_login']) {
        $res3 = sqlStatement("select * from lang_languages order by lang_description");
        for ($iter = 0;$row = sqlFetchArray($res3);$iter++)
               $result3[$iter] = $row;
        if (count($result3) == 1) {
	       //default to english if only return one language
               echo "<input type='hidden' name='languageChoice' value='1' />\n";
        }
}
else {
        echo "<input type='hidden' name='languageChoice' value='".$defaultLanguage."' />\n";   
}
?>

<table width=100% height="90%">
<tr>
<td valign=middle width=33%>
<?php echo $logocode;?>
</td>
<td align='center' valign='middle' width=34%>
<table>
<?php if (count($result) != 1) { ?>
<tr>
<td><span class="text"><?php xl('Group:','e'); ?></span></td>
<td>
<select name=authProvider>
<?php
	foreach ($result as $iter) {
		echo "<option value='".$iter{"name"}."'>".$iter{"name"}."</option>\n";
	}
?>
</select>
</td></tr>
<?php } ?>

<?php if ($_SESSION['loginfailure'] == 1): ?>
<tr><td colspan='2' class='text' style='color:red'>
Invalid username or password
</td></tr>
<?php endif; ?>

<tr>
<td><span class="text"><?php xl('Username:','e'); ?></span></td>
<td>
<input type="text" size="10" name="authUser">
</td></tr><tr>
<td><span class="text"><?php xl('Password:','e'); ?></span></td>
<td>
<input type="password" size="10" name="clearPass">
</td></tr>

<?php
if ($GLOBALS['language_menu_login']) {
if (count($result3) != 1) { ?>
<tr>
<td><span class="text"><?php xl('Language:','e'); ?></span></td>
<td>
<select name=languageChoice size="1">
<?php
        echo "<option selected='selected' value='".$defaultLangID."'>" . xl('Default','','',' (') . xl($defaultLangName,'','',')') . "</option>\n";
        foreach ($result3 as $iter) {
	        if ($GLOBALS['language_menu_showall']) {
                    echo "<option value='".$iter[lang_id]."'>".xl($iter[lang_description])."</option>\n";
		}
	        else {
		    if (in_array($iter[lang_description], $GLOBALS['language_menu_show'])) {
		        echo "<option value='".$iter[lang_id]."'>" . xl($iter[lang_description]) . "</option>\n";
		    }
		}
        }
?>
</select>
</td></tr>
<?php }} ?>

<tr><td>&nbsp;</td><td>
<input type="hidden" name="authPass">
<?php if ($GLOBALS['use_adldap_auth'] == true): ?>
<input type="submit" onClick="javascript:this.form.authPass.value=MD5(this.form.clearPass.value);" value=<?php xl('Login','e');?>>
<?php else: ?>
<input type="submit" onClick="javascript:this.form.authPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';" value=<?php xl('Login','e');?>>
<?php endif; ?>
</td></tr>
<tr><td colspan='2' class='text' style='color:red'>
<?php
$ip=$_SERVER['REMOTE_ADDR'];
$query = "select user, date, comments from log where event like 'login' and comments like '%".$ip."' order by date desc limit 1";
$statement = sqlStatement($query);
if ($result = sqlFetchArray($statement)) {
        if (strpos($result['comments'],"ailure")) {
                echo $result['user']." attempted unauthorized login on this machine: ".$result['date'];
        }
}
?>
</td></tr>
</table>
</td>
<td width=33%>

<!-- Uncomment this for the OpenEMR demo installation
<p><center>login = admin
<br>password = pass
-->

</center></p>

</td>
</table>

</form>

<address>
<a href="copyright_notice.html" target="main"><?php xl('Copyright Notice','e'); ?></a><br />
</address>

</center>
</body>
</html>
