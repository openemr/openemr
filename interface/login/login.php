<?php
/**
 * Login screen.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see 
 * <http://opensource.org/licenses/gpl-license.php>;.
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
 * @author  Robert Down <robertdown@live.com
 * @license https://www.gnu.org/licenses/gpl.html GNU GPL 3
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<script language='javascript' src="../../library/js/jquery-1.4.3.min.js"></script>
<script language='javascript'>
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

<?php // echo $logocode;?>
<div class="row">
  <div class="medium-5 columns small-centered end">
    <div class="panel-with-heading">
      <div class="heading">Sign in</div>
      <div class="body">
        <form method="POST"
               action="../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>"
               target="_top" name="login_form" onsubmit="return imsubmitted();">
          <div class="row">
            <div class="small-12 columns">
              <?php
              // Collect groups
              $res = sqlStatement("select distinct name from groups");
              for ($i = 0; $row = sqlFetchArray($res); $i++) {
                $result[$i] = $row;
              }
              if (count($result) != 1) : ?>
              <div class="row">
                <div class="small-3 columns">
                  <label for="right-label" class="right inline">
                    <?= xlt('Group:'); ?>
                  </label>
                </div>
                <div class="small-9 columns">
                  <select name=authProvider>
                  <?php
                    foreach ($result as $iter) {
                      echo "<option value='".attr($iter{"name"})."'>".text($iter{"name"})."</option>\n";
                    }
                  ?>
                  </select>
                </div>
              </div>
              <?php endif; ?>
              <div class="row">
                <div class="small-3 columns">
                  <label for="right-label" class="right inline">
                    <?= xlt('Username'); ?>
                  </label>
                </div>
                <div class="small-9 columns">
                  <input type="text" name="authUser" id="right-label">
                </div>
              </div>
              <div class="row">
                <div class="small-3 columns">
                  <label for="right-label" class="right inline">
                    <?= xlt('Password:'); ?>
                  </label>
                </div>
                <div class="small-9 columns">
                  <input type="password" name="clearPass" id="right-label">
                </div>
              </div>
              <?php
              if ($GLOBALS['language_menu_login']) {
                if (count($result3) != 1) { ?>
              <div class="row">
                <div class="small-3 columns">
                  <label for="right-label" class="right inline">
                    <?= xlt('Language'); ?>
                  </label>
                </div>
                <div class="small-9 columns">
                  <select class="entryfield" name=languageChoice size="1">
                  <?php
                  echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" . xlt('Default') . " - " . xlt($defaultLangName) . "</option>\n";
                  foreach ($result3 as $iter) {
                    if ($GLOBALS['language_menu_showall']) {
                      if ( !$GLOBALS['allow_debug_language'] && $iter[lang_description] == 'dummy') continue; // skip the dummy language
                        echo "<option value='".attr($iter['lang_id'])."'>".text($iter['trans_lang_description'])."</option>\n";
                      } else {
                        if (in_array($iter[lang_description], $GLOBALS['language_menu_show'])) {
                          if ( !$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
                            echo "<option value='".attr($iter['lang_id'])."'>" . text($iter['trans_lang_description']) . "</option>\n";
                        }
                      }
                  }
                  ?>
                  </select>
                </div>
              </div>
              <?php }}; ?>
              <div class="row">
                <div class="small-9 columns small-offset-3">
                  <button class="button expand" type="submit" onClick="transmit_form()"><?php echo xla('Login');?></button>
                </div>
              <div class="row">
                <div class="small-12 columns centered version">
                  <small><?php echo "v".text($openemr_version) ?> | <a  href="../../acknowledge_license_cert.html" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a></small>
                  <input type='hidden' name='new_login_session_management' value='1' />
                  <?php
                  if (count($result) == 1) {
                    $resvalue = $result[0]["name"];
                    echo "<input type='hidden' name='authProvider' value='" . attr($resvalue) . "' />\n";
                  }

                  // Collect default language ID
                  $res2 = sqlStatement("select * from lang_languages where lang_description = ?",array($GLOBALS['language_default']));
                  for ($iter = 0;$row = sqlFetchArray($res2);$iter++) {
                    $result2[$iter] = $row;
                  }

                  $defaultLangID = 1;
                  $defaultLangName = "English";
                  if (count($result2) == 1) {
                    $defaultLangID = $result2[0]["lang_id"];
                    $defaultLangName = $result2[0]["lang_description"];
                  }

                  // set session variable to default so login information appears in default language
                  $_SESSION['language_choice'] = $defaultLangID;
                  // collect languages if showing language menu
                  if ($GLOBALS['language_menu_login']) {
                    // sorting order of language titles depends on language translation options.
                    $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
                    if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation'])) {
                      $sql = "SELECT *,lang_description as trans_lang_description FROM lang_languages ORDER BY lang_description, lang_id";
                      $res3 = SqlStatement($sql);
                    } else {
                      // Use and sort by the translated language name.
                      $sql = "SELECT ll.lang_id, " .
                             "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " .
                             "ll.lang_description " .
                             "FROM lang_languages AS ll " .
                             "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
                             "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                             "ld.lang_id = ? " .
                             "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
                      $res3 = SqlStatement($sql, array($mainLangID));
                    }
                      
                    for($iter = 0;$row = sqlFetchArray($res3);$iter++) {
                      $result3[$iter] = $row;
                    }

                    if(count($result3) == 1) {
                      //default to english if only return one language
                      echo "<input type='hidden' name='languageChoice' value='1' />\n";
                    }
                  } else {
                    echo "<input type='hidden' name='languageChoice' value='".attr($defaultLangID)."' />\n";   
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>     
    </div>
  </div>
</div>

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
</td></tr>
<tr><td colspan='2' class='text' style='color:red'>
<?php
$ip=$_SERVER['REMOTE_ADDR'];
?>
<div class="demo">
		<!-- Uncomment this for the OpenEMR demo installation
		<p><center>login = admin
		<br>password = pass
		-->
</div>
</body>
</html>
