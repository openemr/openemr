<?php

/**
 * Portal Verify Email
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Common\Logging\SystemLogger;

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();
session_regenerate_id(true);

unset($_SESSION['itsme']);
$_SESSION['verifyPortalEmail'] = true;

$ignoreAuth_onsite_portal = true;
require_once("../../interface/globals.php");

$landingpage = "../index.php?site=" . urlencode($_SESSION['site_id']);

if (empty($GLOBALS['portal_onsite_two_register']) || empty($GLOBALS['google_recaptcha_site_key']) || empty($GLOBALS['google_recaptcha_secret_key'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    echo xlt("Not Authorized");
    header('HTTP/1.1 401 Unauthorized');
    die();
}

// set up csrf
CsrfUtils::setupCsrfKey();

$res2 = sqlStatement("select * from lang_languages where lang_description = ?", array(
    $GLOBALS['language_default']
));
for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
    $result2[$iter] = $row;
}
if (count($result2) == 1) {
    $defaultLangID = $result2[0]["lang_id"];
    $defaultLangName = $result2[0]["lang_description"];
} else {
    // default to english if any problems
    $defaultLangID = 1;
    $defaultLangName = "English";
}

if (!isset($_SESSION['language_choice'])) {
    $_SESSION['language_choice'] = $defaultLangID;
}
// collect languages if showing language menu
if ($GLOBALS['language_menu_login']) {
    // sorting order of language titles depends on language translation options.
    $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
    // Use and sort by the translated language name.
    $sql = "SELECT ll.lang_id, " . "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " . "ll.lang_description " .
        "FROM lang_languages AS ll " . "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
        "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " . "ld.lang_id = ? " .
        "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
    $res3 = SqlStatement($sql, array(
        $mainLangID
    ));

    for ($iter = 0; $row = sqlFetchArray($res3); $iter++) {
        $result3[$iter] = $row;
    }

    if (count($result3) == 1) {
        // default to english if only return one language
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='1' />\n";
    }
} else {
    $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='" . attr($defaultLangID) . "' />\n";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('New Patient'); ?> | <?php echo xlt('Register'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com" />

    <?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'patientportal-style', 'patientportal-register']); ?>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        function enableVerifyBtn(){
            document.getElementById("verifyBtn").disabled = false;
        }
    </script>

</head>
<body class="mt-4 skin-blue">
    <div class="container-lg">
        <h1 class="text-center"><?php echo xlt('Account Registration'); ?></h1>
        <div class="stepwiz">
            <div class="stepwiz-row setup-panel">
                <div class="stepwiz-step">
                    <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                    <p><?php echo xlt('Verify Email') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-2" type="button" class="btn btn-light btn-circle disabled">2</a>
                    <p><?php echo xlt('Profile') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-3" type="button" class="btn btn-light btn-circle disabled">3</a>
                    <p><?php echo xlt('Insurance') ?></p>
                </div>
                <div class="stepwiz-step">
                    <a href="#step-4" type="button" class="btn btn-light btn-circle disabled">4</a>
                    <p><?php echo xlt('Register') ?></p>
                </div>
            </div>
        </div>
        <!-- // Start Forms // -->
        <form id="startForm" role="form" action="account.php?action=verify_email" method="post">
            <input type='hidden' name='csrf_token_form' value='<?php echo attr(CsrfUtils::collectCsrfToken('verifyEmailCsrf')); ?>' />
            <div class="text-center setup-content" id="step-1">
                <legend class="bg-primary text-white"><?php echo xlt('Contact Information') ?></legend>
                <div class="jumbotron">
                    <?php if ($GLOBALS['language_menu_login'] && (count($result3) != 1)) { ?>
                        <div class="form-group">
                            <label class="col-form-label" for="selLanguage"><?php echo xlt('Language'); ?></label>
                            <select class="form-control" id="selLanguage" name="languageChoice">
                                <?php
                                echo "<option selected='selected' value='" . attr($defaultLangID) . "'>" .
                                    text(xl('Default') . " - " . xl($defaultLangName)) . "</option>\n";
                                foreach ($result3 as $iter) {
                                    if ($GLOBALS['language_menu_showall']) {
                                        if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                            continue; // skip the dummy language
                                        }
                                        echo "<option value='" . attr($iter['lang_id']) . "'>" .
                                            text($iter['trans_lang_description']) . "</option>\n";
                                    } else {
                                        if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                            if (!$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') {
                                                continue; // skip the dummy language
                                            }
                                            echo "<option value='" . attr($iter['lang_id']) . "'>" .
                                                text($iter['trans_lang_description']) . "</option>\n";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    <?php } else {
                        echo $hiddenLanguageField;
                    }
                    ?>

                    <div class="form-row">
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="fname"><?php echo xlt('First Name') ?></label>
                            <input type="text" class="form-control" id="fname" name="fname" required placeholder="<?php echo xla('First Name'); ?>" />
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="mname"><?php echo xlt('Middle Name') ?></label>
                            <input type="text" class="form-control" id="mname" name="mname" placeholder="<?php echo xla('Full or Initial'); ?>" />
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="lname"><?php echo xlt('Last Name') ?></label>
                            <input type="text" class="form-control" id="lname" name="lname" required placeholder="<?php echo xla('Enter Last'); ?>" />
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 form-group">
                            <label for="dob"><?php echo xlt('Birth Date') ?></label>
                            <input id="dob" type="text" required class="form-control datepicker" name="dob" placeholder="<?php echo xla('YYYY-MM-DD'); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="emailInput"><?php echo xlt('Enter E-Mail Address') ?></label>
                        <input id="emailInput" type="email" class="reg-email form-control" name="email" required placeholder="<?php echo xla('Enter email address to receive registration.'); ?>" maxlength="100" />
                    </div>
                    <div class="form-group">
                        <div class="d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo attr($GLOBALS['google_recaptcha_site_key']); ?>" data-callback="enableVerifyBtn"></div>
                        </div>
                    </div>
                </div>
                <button type="submit" id="verifyBtn" class="btn btn-primary pull-right mb-5" type="button" disabled="disabled"><?php echo xlt('Verify Email') ?></button>
            </div>
        </form>
    </div>
</body>
</html>
