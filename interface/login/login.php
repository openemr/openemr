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

$fake_register_globals = false;
$sanitize_all_escapes = true;

$ignoreAuth = true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");

$cookieValue = session_name() . " = " . session_id();
?>
<html>
<head>
<?php html_header_show();?>
    <title><?php echo text($openemr_name) . " " . xlt('Login'); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
    <link rel=stylesheet href="../themes/login.css" type="text/css">
    <link rel="shortcut icon" href="<?php echo $webroot; ?>/interface/pic/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" />

    <script language='JavaScript' src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-1/index.js"></script>
    <script type="javascript">
    $(document).ready(init);

    function init() {
        $("#authUser").focus();
        $("#login_form").submit(function(){return imsubmitted();});
    }

    function imsubmitted() {
        <?php if (!empty($GLOBALS['restore_sessions'])) : ?>
        var oldDate = new Date();
        oldDate.setFullYear(now.getFullYear() - 1);
        document.cookie = "<?php echo $cookieValue;?>; path=/; expires=" + oldDate.toGMTString();
        <?php endif; ?>
        return false;
    }
    </script>
</head>
<body class="login">
    <div class="container">
        <form method="POST" id="loginForm" action="../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>" target="_top" name="login_form">
            <div class="row">
                <div class="col-sm-12">
                    <div class="">
                        <img class="img-responsive center-block" src="<?php echo $GLOBALS['webroot']?>/public/images/login-logo.svg" />
                        <input type='hidden' name='new_login_session_management' value='1' />
                        <?php
                        // collect groups
                        $res = sqlStatement("select distinct name from groups");
                        $groupTmp = sqlFetchArray($res);
                        $groups = array();
                        foreach ($groupTmp as $group) {
                            array_push($groups, $group);
                        }
                        if (count($groups) == 1) {
                            echo "<input type='hidden' name='authProvider' value='" . attr($groups[0]) . "' />\n";
                        }
                        // collect default language id
                        $res2 = sqlStatement("select * from lang_languages where lang_description = ?",array($GLOBALS['language_default']));
                        for ($i = 0; $row = sqlFetchArray($res2); $i++) {
                            $languages[] = $row;
                        }
                        $defaultLangID = 1;
                        $defaultLangName = "English";
                        if (count($languages) == 1) {
                            $defaultLangID = $languages[0]{"lang_id"};
                            $defaultLangName = $languages[0]{"lang_description"};
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
                                $res3=SqlStatement($sql, array($mainLangID));
                            }

                            $languags = array();
                            for ($i = 0; $row = sqlFetchArray($res3); $i++) {
                                $languages[] = $row;
                            }
                            if (count($languages) == 1) {
                                //default to english if only return one language
                                echo "<input type='hidden' name='languageChoice' value='1' />\n";
                            }
                        } else {
                            echo "<input type='hidden' name='languageChoice' value='".attr($defaultLangID)."' />\n";
                        }
                        ?></div>
                </div>
            </div>
            <?php if (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)) : // Begin relogin dialog ?>
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        <strong><?php echo xlt('Password security has recently been upgraded.'); ?><br>
                        <?php echo xlt('Please login again.'); ?></strong>
                    </p>
                    <?php unset($_SESSION['relogin']); ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['loginfailure']) && ($_SESSION['loginfailure'] == 1)) : // Begin login failure block ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="well well-lg login-failure">
                        <?php echo xlt('Invalid username or password'); ?>
                    </div>
                </div>
            </div>
            <?php endif; // End login failure block?>
            <div class="row">
                <?php
                // Figure out how to display the tiny logos
                $t1 = $GLOBALS['tiny_logo_1'];
                $t2 = $GLOBALS['tiny_logo_2'];
                if ($t1 && !$t2) : ?>
                    <div class="col-sm-12 center-block">
                        <?php echo $tinylogocode1; ?>
                    </div>
                <?php
                endif;
                if ($t2 && !$t1) : ?>
                    <div class="col-sm-12 center-block">
                        <?php echo $tinylogocode2; ?>
                    </div>
                <?php
                endif;
                if ($t1 && $t2) : ?>
                    <div class="col-sm-6"><?php echo $tinylogocode1;?></div>
                    <div class="col-sm-6"><?php echo $tinylogocode2;?></div>
                <?php
                endif;
                $extraLogo = $GLOBALS['extra_logo_login'];
                $loginFormColumnCount = ($extraLogo == 1) ? '6' : '12'; ?>
                <div class="col-sm-6">
                    <?php if ($GLOBALS['show_label_login']) : ?>
                        <?php echo text($openemr_name); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
            <?php if ($extraLogo) : ?>
                <div class="col-sm-6">
                    <?php echo $logocode; ?>
                </div>
            <?php endif; ?>
                <div class="col-sm-<?php echo $loginFormColumnCount;?>">
                    <?php if (count($result) > 1) : // Begin Display check for groups ?>
                        <div class="form-group">
                            <label for="group" class="col-sm-2 control-label"><?php echo xlt('Group:'); ?></label>
                            <div class="col-sm-10">
                                <select name="authProvider" class="form-control">
                                <?php
                                foreach ($result as $iter) {
                                    echo "<option value='".attr($iter{"name"})."'>".text($iter{"name"})."</option>\n";
                                }
                                ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; // End Display check for groups ?>
                    <?php if ($GLOBALS['language_menu_login'] && (count($languages) > 1)) : // Begin language menu block ?>
                        <div class="form-group">
                            <label for="language" class="col-sm-2 control-label"><?php echo xlt('Language'); ?></label>
                            <div class="col-sm-10">
                                <select class="form-control" name=languageChoice size="1">
                                    <option selected="selected" value="<?php echo attr($defaultLangID);?>"><?php echo xlt('Default') . ' - ' . xlt($defaultLangName);?></option>
                                    <?php
                                    foreach ($languages as $iter) :
                                        if ($GLOBALS['language_menu_showall']) {
                                            if (!$GLOBALS['allow_debug_language'] && $iter[lang_description] == 'dummy') continue; // skip the dummy language
                                                echo "<option value='".attr($iter['lang_id'])."'>".text($iter['trans_lang_description'])."</option>\n";
                                        } else {
                                            if (in_array($iter[lang_description], $GLOBALS['language_menu_show'])) {
                                                if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
                                                    echo "<option value='".attr($iter['lang_id'])."'>" . text($iter['trans_lang_description']) . "</option>\n";
                                            }
                                        }
                                    endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; // End language menu block ?>
                    <div class="form-group">
                        <label for="authUser" class="control-label text-right"><?php echo xlt('Username:'); ?></label>
                        <input type="text" class="form-control" id="authUser" name="authUser" placeholder="<?php echo xlt('Username:'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="clearPass" class="control-label text-right"><?php echo xlt('Password:'); ?></label>
                        <input type="password" class="form-control" id="clearPass" name="clearPass" placeholder="<?php echo xlt('Password:'); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" onClick="transmit_form()" class="btn btn-block btn-large"><i class="fa fa-sign-in"></i>&nbsp;<?php echo xla('Login');?></button>
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <p class="small">
                        <a href="../../acknowledge_license_cert.html" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a>
                    </p>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
