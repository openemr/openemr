<?php

/**
 * Login screen.
 *
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @author  Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;
require_once("../globals.php");

// mdsupport - Add 'App' functionality for user interfaces without standard menu and frames
// If this script is called with app parameter, validate it without showing other apps.
//
// Build a list of valid entries
$emr_app = array();
$rs = sqlStatement(
    "SELECT option_id, title,is_default FROM list_options
        WHERE list_id=? and activity=1 ORDER BY seq, option_id",
    array ('apps')
);
if (sqlNumRows($rs)) {
    while ($app = sqlFetchArray($rs)) {
        $app_req = explode('?', trim($app['title']));
        if (! file_exists('../' . $app_req[0])) {
            continue;
        }

            $emr_app [trim($app ['option_id'])] = trim($app ['title']);
        if ($app ['is_default']) {
            $emr_app_def = $app ['option_id'];
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
	<label for="appChoice" class="text-right">%s:</label>
    <div>
        <select class="form-control" id="selApp" name="appChoice" size="1">%s</select>
    </div>
</div>',
            xlt('App'),
            $opt_htm
        );
    }
}

// This code allows configurable positioning in the login page
$loginrow = "row login-row align-items-center m-5";

if ($GLOBALS['login_page_layout'] == 'left') {
    $logoarea = "col-md-6 login-bg-left py-3 px-5 py-md-login order-1 order-md-2";
    $formarea = "col-md-6 p-5 login-area-left order-2 order-md-1";
} elseif ($GLOBALS['login_page_layout'] == 'right') {
    $logoarea = "col-md-6 login-bg-right py-3 px-5 py-md-login order-1 order-md-1";
    $formarea = "col-md-6 p-5 login-area-right order-2 order-md-2";
} else {
    $logoarea = "col-12 login-bg-center py-3 px-5 order-1";
    $formarea = "col-12 p-5 login-area-center order-2";
    $loginrow = "row login-row login-row-center align-items-center";
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>

    <title><?php echo text($openemr_name) . " " . xlt('Login'); ?></title>

    <script>
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
        )); ?>;

        var registrationConstants = <?php echo json_encode(array(
            'webroot' => $GLOBALS['webroot']
        )); ?>;
    </script>

    <script src="<?php echo $webroot ?>/interface/product_registration/product_registration_service.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="<?php echo $webroot ?>/interface/product_registration/product_registration_controller.js?v=<?php echo $v_js_includes; ?>"></script>

    <script>
        $(function () {
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

        function transmit_form(element) {
            // disable submit button to insert a notification of working
            element.disabled = true;
            // nothing fancy. mainly for mobile.
            element.innerHTML = '<i class="fa fa-sync fa-spin"></i> ' + jsText(<?php echo xlj("Authenticating"); ?>);
            <?php if (session_name()) { ?>
                <?php $scparams = session_get_cookie_params(); ?>
                // Delete the session cookie by setting its expiration date in the past.
                // This forces the server to create a new session ID.
                var olddate = new Date();
                olddate.setFullYear(olddate.getFullYear() - 1);
                var mycookie = <?php echo json_encode(urlencode(session_name())); ?> + '=' + <?php echo json_encode(urlencode(session_id())); ?> +
                    '; path=' + <?php echo json_encode($scparams['path']); ?> +
                    '; domain=' + <?php echo json_encode($scparams['domain']); ?> +
                    '; expires=' + olddate.toGMTString();
                var samesite = <?php echo json_encode(empty($scparams['samesite']) ? '' : $scparams['samesite']); ?>;
                if (samesite) {
                    mycookie += '; SameSite=' + samesite;
                }
                document.cookie = mycookie;
            <?php } ?>
            document.forms[0].submit();
            return true;
        }
    </script>
</head>
<body class="login">
  <form method="POST" id="login_form" autocomplete="off" action="../main/main_screen.php?auth=login&site=<?php echo attr($_SESSION['site_id']); ?>" target="_top" name="login_form">
      <div class="<?php echo $loginrow; ?>">
          <div class="<?php echo $formarea; ?>">
              <input type='hidden' name='new_login_session_management' value='1' />

              <?php
              // collect default language id
                $res2 = sqlStatement("select * from lang_languages where lang_description = ?", array($GLOBALS['language_default']));
                for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
                    $result2[$iter] = $row;
                }

                if (count($result2) == 1) {
                    $defaultLangID = $result2[0]["lang_id"];
                    $defaultLangName = $result2[0]["lang_description"];
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

                    for ($iter = 0; $row = sqlFetchArray($res3); $iter++) {
                        $result3[$iter] = $row;
                    }

                    if (count($result3) == 1) {
                        //default to english if only return one language
                        echo "<input type='hidden' name='languageChoice' value='1' />\n";
                    }
                } else {
                    echo "<input type='hidden' name='languageChoice' value='" . attr($defaultLangID) . "' />\n";
                }

                if ($GLOBALS['login_into_facility']) {
                    $facilityService = new FacilityService();
                    $facilities = $facilityService->getAllFacility();
                    $facilitySelected = ($GLOBALS['set_facility_cookie'] && isset($_COOKIE['pc_facility'])) ? $_COOKIE['pc_facility'] : null;
                }
                ?>
              <?php if (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)) { // Begin relogin dialog ?>
              <div class="alert alert-info m-1 font-weight-bold">
                    <?php echo xlt('Password security has recently been upgraded.') . '&nbsp;&nbsp;' . xlt('Please login again.'); ?>
              </div>
                    <?php unset($_SESSION['relogin']);
              }
              if (isset($_SESSION['loginfailure']) && ($_SESSION['loginfailure'] == 1)) { // Begin login failure block ?>
              <div class="alert alert-danger login-failure m-1">
                  <?php echo xlt('Invalid username or password'); ?>
              </div>
            <?php } // End login failure block ?>
            <div class="form-group">
                <label for="authUser" class="text-right"><?php echo xlt('Username:'); ?></label>
                <input type="text" class="form-control" id="authUser" name="authUser" placeholder="<?php echo xla('Username:'); ?>" />
            </div>
            <div class="form-group">
                <label for="clearPass" class="text-right"><?php echo xlt('Password:'); ?></label>
                <input type="password" class="form-control" id="clearPass" name="clearPass" placeholder="<?php echo xla('Password:'); ?>" />
            </div>
            <?php echo $div_app; ?>
            <?php if ($GLOBALS['language_menu_login'] && (count($result3) != 1)) : // Begin language menu block ?>
                <div class="form-group">
                    <label for="language" class="text-right"><?php echo xlt('Language'); ?>:</label>
                    <div>
                        <select class="form-control" name="languageChoice" size="1">
                            <?php
                            echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" . xlt('Default') . " - " . xlt($defaultLangName) . "</option>\n";
                            foreach ($result3 as $iter) :
                                if ($GLOBALS['language_menu_showall']) {
                                    if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                        continue; // skip the dummy language
                                    }

                                        echo "<option value='" . attr($iter['lang_id']) . "'>" . text($iter['trans_lang_description']) . "</option>\n";
                                } else {
                                    if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                        if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                            continue; // skip the dummy language
                                        }

                                            echo "<option value='" . attr($iter['lang_id']) . "'>" . text($iter['trans_lang_description']) . "</option>\n";
                                    }
                                }
                            endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; // End language menu block ?>
            <?php if ($GLOBALS['login_into_facility']) { // Begin facilities menu block ?>
                <div class="form-group">
                    <label for="facility" class="text-right"><?php echo xlt('Facility'); ?>:</label>
                    <div>
                        <select class="form-control" name="facility" size="1">
                            <option value="user_default"><?php echo xlt('My default facility'); ?></option>
                            <?php foreach ($facilities as $facility) { ?>
                                <?php if (!is_null($facilitySelected) && $facilitySelected == $facility['id']) { ?>
                                    <option value="<?php echo attr($facility['id']);?>" selected><?php echo text($facility['name']);?></option>
                                <?php } else { ?>
                                    <option value="<?php echo attr($facility['id']);?>"><?php echo text($facility['name']);?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            <?php } // End facilities menu block ?>
            <div class="form-group oe-pull-away">
                <button type="submit" class="btn btn-login btn-lg" onClick="transmit_form(this)"><i class="fa fa-sign-in-alt"></i>&nbsp;&nbsp;<?php echo xlt('Login');?></button>
            </div>
          </div>
          <div class="<?php echo $logoarea; ?>">
            <?php $extraLogo = $GLOBALS['extra_logo_login']; ?>
            <?php if ($extraLogo) { ?>
            <div class="text-center">
              <span class="d-inline-block w-40"><?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/login-logo.svg"); ?></span>
              <span class="d-inline-block w-15 login-bg-text-color"><i class="fas fa-plus fa-2x"></i></span>
              <span class="d-inline-block w-40"><?php echo $logocode; ?></span>
            </div>
            <?php } else { ?>
              <div class="mx-auto m-4 w-75">
                  <?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/login-logo.svg"); ?>
              </div>
            <?php } ?>
            <div class="text-center login-title-label">
                <?php if ($GLOBALS['show_label_login']) { ?>
                    <?php echo text($openemr_name); ?>
                <?php } ?>
            </div>
                <?php
                // Figure out how to display the tiny logos
                $t1 = $GLOBALS['tiny_logo_1'];
                $t2 = $GLOBALS['tiny_logo_2'];
                if ($t1 && !$t2) {
                    echo $tinylogocode1;
                } if ($t2 && !$t1) {
                    echo $tinylogocode2;
                } if ($t1 && $t2) { ?>
                  <div class="row mb-3">
                    <div class="col-sm-6"><?php echo $tinylogocode1;?></div>
                    <div class="col-sm-6"><?php echo $tinylogocode2;?></div>
                  </div>
                <?php } ?>
            <p class="text-center lead font-weight-normal login-bg-text-color"><?php echo xlt('The most popular open-source Electronic Health Record and Medical Practice Management solution.'); ?></p>
            <p class="text-center small"><a href="../../acknowledge_license_cert.html" class="login-bg-text-color" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a></p>
          </div>
      </div>
      <div class="product-registration-modal modal fade">
          <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                  <div class="modal-header"></div>
                  <div class="modal-body">
                      <p class="context"><?php echo xlt("Register your installation with OEMR to receive important notifications, such as security fixes and new release announcements."); ?></p>
                      <input placeholder="<?php echo xlt('email'); ?>" type="email" class="email w-100 text-body form-control" />
                      <p class="message font-italic"></p>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary submit"><?php echo xlt("Submit"); ?></button>
                      <button type="button" class="btn btn-secondary nothanks"><?php echo xlt("No Thanks"); ?></button>
                  </div>
              </div>
          </div>
      </div>
  </form>
</body>
</html>
