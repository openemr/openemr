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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @link    http://www.open-emr.org
 */



use OpenEMR\Core\Header;

$ignoreAuth=true;
require_once("../globals.php");

// mdsupport - Add 'App' functionality for user interfaces without standard menu and frames
// If this script is called with app parameter, validate it without showing other apps.
//
// Build a list of valid entries
$emr_app = array();
if ($GLOBALS['new_tabs_layout']) {
    $rs = sqlStatement(
        "SELECT option_id, title,is_default FROM list_options
			WHERE list_id=? and activity=1 ORDER BY seq, option_id",
        array ('apps')
    );
    if (sqlNumRows($rs)) {
        while ($app = sqlFetchArray($rs)) {
            $app_req = explode('?', trim($app['title']));
            if (! file_exists('../'.$app_req[0])) {
                continue;
            }

                $emr_app [trim($app ['option_id'])] = trim($app ['title']);
            if ($app ['is_default']) {
                $emr_app_def = $app ['option_id'];
            }
        }
    }
}

$div_app = '';
if (count($emr_app)) {
    // Standard app must exist
    $std_app = 'main/main_screen.php';
    if (!in_array($std_app, $emr_app)) {
        $emr_app['*OpenEMR'] = $std_app;
    }

    if (isset($_REQUEST['app']) && $emr_app[$_REQUEST['app']]) {
        $div_app = sprintf('<input type="hidden" name="appChoice" value="%s">', attr($_REQUEST['app']));
    } else {
        foreach ($emr_app as $opt_disp => $opt_value) {
            $opt_htm .= sprintf(
                '<option value="%s" %s>%s</option>\n',
                attr($opt_disp),
                ($opt_disp == $opt_default ? 'selected="selected"' : ''),
                text(xl_list_label($opt_disp))
            );
        }

        $div_app = sprintf(
            '
<div id="divApp" class="form-group">
	<label for="appChoice" class="control-label text-right">%s:</label>
    <div>
        <select class="form-control" id="selApp" name="appChoice" size="1">%s</select>
    </div>
</div>',
            xlt('App'),
            $opt_htm
        );
    }
}

?>
<html>
<head>
    <title><?php echo text($openemr_name) . " " . xlt('Login'); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <?php Header::setupHeader(['jquery-ui', 'jquery-ui-darkness']); ?>

    <link rel="stylesheet" href="../themes/login.css?v=<?php echo $v_js_includes; ?>" type="text/css">

    <link rel="shortcut icon" href="<?php echo $GLOBALS['images_static_relative']; ?>/favicon.ico" />

    <script type="text/javascript">
        var registrationTranslations = <?php echo json_encode(array(
            'title' => xla('OpenEMR Product Registration'),
            'pleaseProvideValidEmail' => xla('Please provide a valid email address'),
            'success' => xla('Success'),
            'registeredSuccess' => xla('Your installation of OpenEMR has been registered'),
            'submit' => xla('Submit'),
            'noThanks' => xla('No Thanks'),
            'registeredEmail' => xla('Registered email'),
            'registeredId' => xla('Registered id'),
            'genericError' => xla('Error. Try again later'),
            'closeTooltip' => ''
        ));
        ?>;

        var registrationConstants = <?php echo json_encode(array(
            'webroot' => $GLOBALS['webroot']
        ))
        ?>;
    </script>

    <script type="text/javascript" src="<?php echo $webroot ?>/interface/product_registration/product_registration_service.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/product_registration/product_registration_controller.js?v=<?php echo $v_js_includes; ?>"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            init();

            var productRegistrationController = new ProductRegistrationController();
            productRegistrationController.getProductRegistrationStatus(function(err, data) {
                if (err) { return; }

                if (data.statusAsString === 'UNREGISTERED') {
                    productRegistrationController.showProductRegistrationModal();
                }
            });
        });

        function init() {
            $("#authUser").focus();
        }

        function transmit_form() {
            document.forms[0].submit();
        }

        function imsubmitted() {
            <?php if (!empty($GLOBALS['restore_sessions'])) { ?>
                // Delete the session cookie by setting its expiration date in the past.
                // This forces the server to create a new session ID.
                var olddate = new Date();
                olddate.setFullYear(olddate.getFullYear() - 1);
                document.cookie = '<?php echo session_name() . '=' . session_id() ?>; path=<?php echo($web_root ? $web_root : '/');?>; expires=' + olddate.toGMTString();
            <?php } ?>
            return false; //Currently the submit action is handled by the encrypt_form().
        }
    </script>

</head>
<body class="login">
    <div class="container">
        <form method="POST" id="login_form"
            action="../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>"
            target="_top" name="login_form" onsubmit="return imsubmitted();">
            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <div class="center-block" style="max-width:400px">
                            <img class="img-fluid img-responsive center-block" src="<?php echo $GLOBALS['images_static_relative']; ?>/login-logo.png" />
                        </div>

                        <input type='hidden' name='new_login_session_management' value='1' />

                        <?php
                        // collect groups
                        $res = sqlStatement("select distinct name from groups");
                        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                              $result[$iter] = $row;
                        }

                        if (count($result) == 1) {
                              $resvalue = $result[0]{"name"};
                              echo "<input type='hidden' name='authProvider' value='" . attr($resvalue) . "' />\n";
                        }

                        // collect default language id
                        $res2 = sqlStatement("select * from lang_languages where lang_description = ?", array($GLOBALS['language_default']));
                        for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
                            $result2[$iter] = $row;
                        }

                        if (count($result2) == 1) {
                            $defaultLangID = $result2[0]{"lang_id"};
                            $defaultLangName = $result2[0]{"lang_description"};
                        } else {
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
                            if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation'])) {
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

                            for ($iter = 0; $row = sqlFetchArray($res3); $iter++) {
                                $result3[$iter] = $row;
                            }

                            if (count($result3) == 1) {
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
            <div class="row">
                <div class="col-sm-12">
                    <?php if (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)) : // Begin relogin dialog ?>
                    <div class="alert alert-info m-1">
                        <strong>
                            <?php echo xlt('Password security has recently been upgraded.').'&nbsp;&nbsp;'.xlt('Please login again.'); ?>
                        </strong>
                    </div>
                    <?php unset($_SESSION['relogin']);
                    endif;
if (isset($_SESSION['loginfailure']) && ($_SESSION['loginfailure'] == 1)) : // Begin login failure block ?>
                    <div class="alert alert-danger login-failure m-1">
                        <?php echo xlt('Invalid username or password'); ?>
                    </div>
                    <?php endif; // End login failure block?>
                </div>
            </div>
            <div class="row">
                <?php
                $extraLogo = $GLOBALS['extra_logo_login'];
                $loginFormColumnCount = ($extraLogo == 1) ? '6' : '12';
                ?>
                <?php if ($extraLogo) : ?>
                    <div class="col-sm-6">
                        <?php echo $logocode; ?>
                    </div>
                <?php endif; ?>
                <div class="col-sm-<?php echo $loginFormColumnCount;?>">
                    <div class="row">
                        <div class="center-block login-title-label">
                            <?php if ($GLOBALS['show_label_login']) : ?>
                                <?php echo text($openemr_name); ?>
                            <?php endif; ?>
                        </div>
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
                            <div class="col-sm-6 center-block"><?php echo $tinylogocode1;?></div>
                            <div class="col-sm-6 center-block"><?php echo $tinylogocode2;?></div>
                        <?php
                        endif;
                        ?>
                    </div>
                    <?php if (count($result) > 1) : // Begin Display check for groups ?>
                        <div class="form-group">
                            <label for="group" class="control-label text-right"><?php echo xlt('Group:'); ?></label>
                            <div>
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
                    <div class="form-group">
                        <label for="authUser" class="control-label text-right"><?php echo xlt('Username:'); ?></label>
                        <input type="text" class="form-control" id="authUser" name="authUser" placeholder="<?php echo xla('Username:'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="clearPass" class="control-label text-right"><?php echo xlt('Password:'); ?></label>
                        <input type="password" class="form-control" id="clearPass" name="clearPass" placeholder="<?php echo xla('Password:'); ?>">
                    </div>
                    <?php echo $div_app ?>
                    <?php if ($GLOBALS['language_menu_login'] && (count($result3) != 1)) : // Begin language menu block ?>
                        <div class="form-group">
                            <label for="language" class="control-label text-right"><?php echo xlt('Language'); ?>:</label>
                            <div>
                                <select class="form-control" name="languageChoice" size="1">
                                    <?php
                                    echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" . xlt('Default') . " - " . xlt($defaultLangName) . "</option>\n";
                                    foreach ($result3 as $iter) :
                                        if ($GLOBALS['language_menu_showall']) {
                                            if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                                continue; // skip the dummy language
                                            }

                                                echo "<option value='".attr($iter['lang_id'])."'>".text($iter['trans_lang_description'])."</option>\n";
                                        } else {
                                            if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                                if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                                    continue; // skip the dummy language
                                                }

                                                    echo "<option value='".attr($iter['lang_id'])."'>" . text($iter['trans_lang_description']) . "</option>\n";
                                            }
                                        }
                                    endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; // End language menu block ?>
                    <div class="form-group pull-right">
                        <button type="submit" class="btn btn-default btn-lg" onClick="transmit_form()"><i class="fa fa-sign-in"></i>&nbsp;&nbsp;<?php echo xlt('Login');?></button>
                    </div>
                </div>
                <div class="col-sm-12 text-center">
                    <p class="small">
                        <a href="../../acknowledge_license_cert.html" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a>
                    </p>
                </div>
                <div class="product-registration-modal" style="display: none">
                    <p class="context"><?php echo xlt("Register your installation with OEMR to receive important notifications, such as security fixes and new release announcements."); ?></p>
                    <input placeholder="<?php echo xlt('email'); ?>" type="email" class="email" style="width: 100%; color: black" />
                    <p class="message" style="font-style: italic"></p>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
