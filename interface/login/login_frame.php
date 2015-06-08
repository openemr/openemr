<?php
/**
 * Login screen.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @link    http://www.open-emr.org
 */

$ignoreAuth = true;
$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("$srcdir/sql.inc");
include_once ("../globals.php");
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Login', 'e');?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php
$new_ui = true;
if ($new_ui == true): ?>
<!-- If you delete this meta tag World War Z will become a reality -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../../library/assets/foundation-latest/css/normalize.css" type="text/css">
<link rel="stylesheet" href="../../library/assets/foundation-latest/css/foundation.css" type="text/css">
<link rel="stylesheet" href="../../library/assets/font-awesome/font-awesome.css" type="text/css">
<link rel="stylesheet" href="../../interface/themes/style_light.css" type="text/css">
<script src="../../library/assets/foundation-latest/js/foundation.js" type="javascript"></script>
<link rel="stylesheet" href="../../library/assets/foundation-datepicker/stylesheets/foundation-datepicker.css" type="text/css">
<link rel="stylesheet" href="../themes/style_light.css" type="text/css">
<script src="../../library/assets/foundation-latest/js/foundation.min.js" type="javascript"></script>
<?php else: ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<?php endif; ?>
</head>
<body>
<div class="row-fluid" style="background:#000000">
<img src=" <?php echo $GLOBALS['webroot']?>/interface/pic/logo.gif" />    
</div>

<span class="title_bar">
<div class="title_name"><?php echo "$openemr_name" ?></div>

</span><br>
<html>
<head>
<script language='JavaScript' src="../../library/js/jquery-1.4.3.min.js"></script>
<script language='JavaScript'>
function transmit_form()
{
    document.forms[0].submit();
}
function imsubmitted() {
<?php if (!empty($GLOBALS['restore_sessions'])) { ?>
 // Delete the session cookie by setting its expiration date in the past.
 // This forces the server to create a new session ID.
 var olddate = new Date();
 olddate.setFullYear(olddate.getFullYear() - 1);
 document.cookie = '<?php echo session_name() . '=' . session_id() ?>; path=/; expires=' + olddate.toGMTString();
<?php } ?>
    return false; //Currently the submit action is handled by the encrypt_form(). 
}
</script>

</head>
<body onload="javascript:document.login_form.authUser.focus();" >
<span class="text"></span>

<div class="row">
<div class="small-12 medium-6 medium-offset-3 columns">
    <div class="panel clearfix">
    <form method="POST"
          action="../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>"
          target="_top" name="login_form" onsubmit="return imsubmitted();">
        <input type='hidden' name='new_login_session_management' value='1'>
        <?php
        // collect groups
        $res = sqlStatement("select distinct name from groups");
        for ($iter = 0;$row = sqlFetchArray($res);$iter++)
            $result[$iter] = $row;
        if (count($result) == 1) {
            $resvalue = $result[0]{"name"};
            echo "<input type='hidden' name='authProvider' value='" . attr($resvalue) . "' />\n";
        }
        // collect default language id
        $res2 = sqlStatement("select * from lang_languages where lang_description = ?",array($GLOBALS['language_default']));
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
                    "ld.lang_id = ? " .
                    "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
                  $res3=SqlStatement($sql, array($mainLangID));
            }
            
                for ($iter = 0;$row = sqlFetchArray($res3);$iter++)
                       $result3[$iter] = $row;
                if (count($result3) == 1) {
                   //default to english if only return one language
                       echo "<input type='hidden' name='languageChoice' value='1' />\n";
                }
        }
        else {
            echo "<input type='hidden' name='languageChoice' value='".attr($defaultLangID)."' />\n";   
        }
        ?>
        <div class="row">
            <?php if (count($result) > 0): ?>
            <div class="small-12 columns">
                <label><?php echo xlt('Group:'); ?>
                    <select name="authProvider" id="">
                        <?php foreach ($result as $iter) : ?>
                        <option value="<?php echo attr($iter["name"]);?>">
                            <?php echo text($iter["name"]); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="small-12 columns">
                <label><?php echo xlt('Username:'); ?>
                    <input class="entryfield" type="text" size="10" name="authUser">
                </label>
            </div>
        </div>
        <div class="row">
            <div class="small-12 columns">
                <label><?php echo xlt('Password:'); ?>
                    <input class="entryfield" type="password" size="10" name="clearPass">
                </label>
            </div>
        </div>
        <div class="row">
            <div class="small-12 columns">
                <button class="button expand" type="submit" onClick="transmit_form()"><?php echo xla('Login');?></button>
            </div>
        </div>
        <div class="row">
            <div class="small-12 version text-right">
                <small>
                <?php echo "v".text($openemr_version) ?> | 
                <a href="../../acknowledge_license_cert.html" target="main">
                    <?php echo xlt('Acknowledgments, Licensing and Certification'); ?>
                </a>
                </small>
            </div>
        </div>
    </form>
    </div>
</div>
</div>

<table width="100%" height="90%">
<td align='center' valign='middle' width='34%'>
<div class="login-box">
<div class="logo-left"><?php echo $logocode;?></div>

<div class="table-right">
<table width="100%">
<?php if (isset($_SESSION['loginfailure']) && ($_SESSION['loginfailure'] == 1)): ?>
<tr><td colspan='2' class='text' style='color:red'>
<?php echo xlt('Invalid username or password'); ?>
</td></tr>
<?php endif; ?>

<?php if (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)): ?>
<tr><td colspan='2' class='text' style='color:red;background-color:#dfdfdf;border:solid 1px #bfbfbf;text-align:center'>
<b><?php echo xlt('Password security has recently been upgraded.'); ?><br>
<?php echo xlt('Please login again.'); ?></b>
<?php unset($_SESSION['relogin']); ?>
</td></tr>
<?php endif; ?>
<?php
if ($GLOBALS['language_menu_login']) {
if (count($result3) != 1) { ?>
<tr>
<td><span class="text"><?php echo xlt('Language'); ?>:</span></td>
<td>
<select class="entryfield" name=languageChoice size="1">
<?php
        echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" . xlt('Default') . " - " . xlt($defaultLangName) . "</option>\n";
        foreach ($result3 as $iter) {
            if ($GLOBALS['language_menu_showall']) {
                    if ( !$GLOBALS['allow_debug_language'] && $iter[lang_description] == 'dummy') continue; // skip the dummy language
                    echo "<option value='".attr($iter['lang_id'])."'>".text($iter['trans_lang_description'])."</option>\n";
        }
            else {
            if (in_array($iter[lang_description], $GLOBALS['language_menu_show'])) {
                        if ( !$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
                echo "<option value='".attr($iter['lang_id'])."'>" . text($iter['trans_lang_description']) . "</option>\n";
            }
        }
        }
?>
</select>
</td></tr>
<?php }} ?>
<tr><td colspan='2' class='text' style='color:red'>
<?php
$ip=$_SERVER['REMOTE_ADDR'];
?>
</div>
</td></tr>
</table>

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
