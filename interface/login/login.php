<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sha1.js");
include_once("$srcdir/sql.inc");
include_once("$srcdir/md5.js");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../themes/login.css" type="text/css">

<script language='JavaScript' src="../../library/js/jquery-1.4.3.min.js"></script>
<script language='JavaScript'>

//VicarePlus :: Validation function for checking the hashing algorithm used for encrypting password
function chk_hash_fn()
{
var str = document.forms[0].authUser.value;
   $.ajax({
  url: "validateUser.php?u="+str,
  context: document.body,
  success: function(data){
        if(data == 0) //VicarePlus :: If the hashing algorithm is 'MD5'
        {
                document.forms[0].authPass.value=MD5(document.forms[0].clearPass.value);
                document.forms[0].authNewPass.value=SHA1(document.forms[0].clearPass.value);
        }
        else  //VicarePlus :: If the hashing algorithm is 'SHA1'
        {
                document.forms[0].authPass.value=SHA1(document.forms[0].clearPass.value);
        }
                document.forms[0].clearPass.value='';
                document.login_form.submit();
                }
        });
}

function imsubmitted() {
<?php if (!empty($GLOBALS['restore_sessions'])) { ?>
 // Delete the session cookie by setting its expiration date in the past.
 // This forces the server to create a new session ID.
 var olddate = new Date();
 olddate.setFullYear(olddate.getFullYear() - 1);
 document.cookie = '<?php echo session_name() . '=' . session_id() ?>; path=/; expires=' + olddate.toGMTString();
<?php } ?>
 return false; //Currently the submit action is handled by the chk_hash_fn() function itself.
}
</script>

</head>
<body onload="javascript:document.login_form.authUser.focus();" >

<span class="text"></span>
<center>

<form method="POST"
 action="../main/main_screen.php?auth=login&site=<?php echo htmlspecialchars($_SESSION['site_id']); ?>"
 target="_top" name="login_form" onsubmit="return imsubmitted();">

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
    
        // sorting order of language titles depends on language translation options.
        $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation']))
        {
          $sql = "SELECT *,lang_description as trans_lang_description FROM lang_languages ORDER BY lang_description, lang_id";
	  $res3=SqlStatement($sql);
        }
        else {
          // Use and sort by the translated language name.
          $sql = "SELECT ll.lang_id, " .
            "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " .
	    "ll.lang_description " .
            "FROM lang_languages AS ll " .
            "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
            "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
            "ld.lang_id = '$mainLangID' " .
            "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
          $res3=SqlStatement($sql);
	}
    
        for ($iter = 0;$row = sqlFetchArray($res3);$iter++)
               $result3[$iter] = $row;
        if (count($result3) == 1) {
	       //default to english if only return one language
               echo "<input type='hidden' name='languageChoice' value='1' />\n";
        }
}
else {
        echo "<input type='hidden' name='languageChoice' value='".$defaultLangID."' />\n";   
}
?>

<table width="100%" height="90%">
<td align='center' valign='middle' width='34%'>
<div class="login-box">
<div class="logo-left"><?php echo $logocode;?></div>

<div class="table-right">
<table width="100%">
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

<?php if (isset($_SESSION['loginfailure']) && ($_SESSION['loginfailure'] == 1)): ?>
<tr><td colspan='2' class='text' style='color:red'>
Invalid username or password
</td></tr>
<?php endif; ?>

<?php if (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)): ?>
<tr><td colspan='2' class='text' style='color:red;background-color:#dfdfdf;border:solid 1px #bfbfbf;text-align:center'>
<b><?php echo xl('Password security has recently been upgraded.'); ?><br>
<?php echo xl('Please login again.'); ?></b>
<?php unset($_SESSION['relogin']); ?>
</td></tr>
<?php endif; ?>

<tr>
<td><span class="text"><?php xl('Username:','e'); ?></span></td>
<td>
<input class="entryfield" type="text" size="10" name="authUser">
</td></tr><tr>
<td><span class="text"><?php xl('Password:','e'); ?></span></td>
<td>
<input class="entryfield" type="password" size="10" name="clearPass">
</td></tr>

<?php
if ($GLOBALS['language_menu_login']) {
if (count($result3) != 1) { ?>
<tr>
<td><span class="text"><?php xl('Language','e'); ?>:</span></td>
<td>
<select class="entryfield" name=languageChoice size="1">
<?php
        echo "<option selected='selected' value='".$defaultLangID."'>" . xl('Default','','',' -') . xl($defaultLangName,'',' ') . "</option>\n";
        foreach ($result3 as $iter) {
	        if ($GLOBALS['language_menu_showall']) {
                    if ( !$GLOBALS['allow_debug_language'] && $iter[lang_description] == 'dummy') continue; // skip the dummy language
                    echo "<option value='".$iter['lang_id']."'>".$iter['trans_lang_description']."</option>\n";
		}
	        else {
		    if (in_array($iter[lang_description], $GLOBALS['language_menu_show'])) {
                        if ( !$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
		        echo "<option value='".$iter['lang_id']."'>" . $iter['trans_lang_description'] . "</option>\n";
		    }
		}
        }
?>
</select>
</td></tr>
<?php }} ?>

<tr><td>&nbsp;</td><td>
<input type="hidden" name="authPass">
<input type="hidden" name="authNewPass">
<?php if (isset($GLOBALS['use_adldap_auth']) && ($GLOBALS['use_adldap_auth']== true)): ?>
<!-- ViCareplus : As per NIST standard, the SHA1 encryption algorithm is used -->
<input class="button large" type="submit" onClick="javascript:this.form.authPass.value=SHA1(this.form.clearPass.value);" value="<?php xl('Login','e');?>">
<?php else: ?>
<input class="button large" type="submit" onClick="chk_hash_fn();" value="<?php xl('Login','e');?>">
<?php endif; ?>
</td></tr>
<tr><td colspan='2' class='text' style='color:red'>
<?php
$ip=$_SERVER['REMOTE_ADDR'];

// The following commented out because it is too slow when the log
// table is large.  -- Rod 2009-11-11
/*********************************************************************
$query = "select user, date, comments from log where event like 'login' and comments like '%".$ip."' order by date desc limit 1";
$statement = sqlStatement($query);
if ($result = sqlFetchArray($statement)) {
        if (strpos($result['comments'],"ailure")) {
                echo $result['user']." attempted unauthorized login on this machine: ".$result['date'];
        }
}
*********************************************************************/

?>
</div>
</td></tr>
</table>

</div>
<div style="clear: both;"> </div>
<div class="version">
<?php echo "v$openemr_version" ?> | <a  href="../../acknowledge_license_cert.html" target="main"><?php xl('Acknowledgments, Licensing and Certification','e'); ?></a>
</div>
</div>
<div class="demo">
		<!-- Uncomment this for the OpenEMR demo installation
		<p><center>login = admin
		<br>password = pass
		-->
</div>
</td>
</tr>
</table>
</form>
</center>
</body>
</html>
