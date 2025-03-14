<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// prevent UI redressing
Header("X-Frame-Options: DENY");
Header("Content-Security-Policy: frame-ancestors 'none'");

//setting the session & other config options

// Will start the (patient) portal OpenEMR session/cookie.

require_once __DIR__ . "/../src/Common/Session/SessionUtil.php";
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

//don't require standard openemr authorization in globals.php
$ignoreAuth_onsite_portal = true;

//includes

require_once '../interface/globals.php';
require_once __DIR__ . "/lib/appsql.class.php";
$logit = new ApplicationTable();

use OpenEMR\Common\Auth\Exception\OneTimeAuthException;
use OpenEMR\Common\Auth\Exception\OneTimeAuthExpiredException;
use OpenEMR\Common\Auth\OneTimeAuth;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\LogoService;

//For redirect if the site on session does not match
$landingpage = $GLOBALS['web_root'] . "/portal/index.php?site=" . urlencode($_SESSION['site_id']);
$logoService = new LogoService();
$logoSrc = $logoService->getLogo("portal/login/primary");
$logo2ndSrc = $logoService->getLogo("portal/login/secondary"); /*rm - add secondary logo */

//exit if portal is turned off
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}
$auth['portal_pwd'] = '';
if (isset($_GET['woops'])) {
    unset($_GET['woops']);
    unset($_SESSION['password_update']);
}
/*
    The below will test and set the where to session variable when redirecting from the login page.
    First unset the where to session variable in case it is wrongly used.
*/
unset($_REQUEST['whereto']);
unset($_GET['whereto']);
// set the where to session variable to the page from previous session.
$whereto = $_SESSION['whereto'] ?? null;
// set the landOn session variable to the redirect page after successfully login.
$_SESSION['landOn'] = $_GET['landOn'] ?? null;
// unset the landOn super.
unset($_REQUEST['landOn']);
unset($_GET['landOn']);
/*
 allow both get and post redirect params here... everything will be sanitized in get_patient_info.php before we
 actually do anything with the redirect
 this value should already be url encoded.
*/
$redirectUrl = $_REQUEST['redirect'] ?? '';

/*
 * Patient for onetime is verified when token redirect is decoded.
 * The embedded pid in token is compared to the token looked up result pid.
 * Also verified as the portal account id is rebuilt from patient data
 * and compared to portal credential account id lookup.
 * */
if (!empty($_REQUEST['service_auth'] ?? null)) {
    $oneTime = new OneTimeAuth();
    if (!empty($_GET['service_auth'] ?? null)) {
        // we have to setup the csrf key to prevent CSRF Login attacks
        // we also implement this mechanism in order to handle Same-Site cookie blocking when being referred by
        // an external site domain.  We used to auto process via GET but now we submit via the POST in order to make it
        // a same site cookie origin request. This is a workaround for the Same-Site cookie blocking.
        $token = $_GET['service_auth'];
        $ot = $oneTime->decodePortalOneTime($token, null, false);
        $pin_required = $ot['actions']['enforce_auth_pin'] ? 1 : 0;
        CsrfUtils::setupCsrfKey();
        $twig = new TwigContainer(null, $GLOBALS['kernel']);
        echo $twig->getTwig()->render('portal/login/autologin.html.twig', [
            'action' => $GLOBALS['web_root'] . '/portal/index.php',
            'service_auth' => $_GET['service_auth'],
            'target' => $_GET['target'] ?? null,
            'csrf_token' => CsrfUtils::collectCsrfToken('autologin'),
            'pagetitle' => xl("OpenEMR Patient Portal"),
            'images_static_relative' => $GLOBALS['images_static_relative'] ?? '',
            'pin_required' => $pin_required,
        ]);
        exit;
    } elseif (!empty($_POST['service_auth'] ?? null)) {
        $token = $_POST['service_auth'];
        $redirect_token = $_POST['target'] ?? null;
        $csrfToken = $_POST['csrf_token'] ?? null;
        try {
            if (!CsrfUtils::verifyCsrfToken($csrfToken, 'autologin')) {
                throw new OneTimeAuthException('Invalid CSRF token');
            }
            $auth = $oneTime->processOnetime($token, $redirect_token);
            $logit->portalLog('onetime login attempt', $auth['pid'], 'patient logged in and redirecting', '', '1');
            exit();
        } catch (OneTimeAuthExpiredException $exception) {
            $logit->portalLog(
                'onetime login attempt',
                $exception->getPid() ?? '',
                ':invalid one time',
                '',
                '0'
            );
            // do we want a separate message that their token has expired?
            OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
            header('Location: ' . $landingpage . '&oe');
            exit();
        } catch (OneTimeAuthException $exception) {
            $logit->portalLog(
                'onetime login attempt',
                $exception->getPid() ?? '',
                ':invalid one time',
                '',
                '0'
            );
            OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
            header('Location: ' . $landingpage . '&oi');
            exit();
        }
    } else {
        (new SystemLogger())->errorLogCaller("Invalid service_auth request - should never reach here");
        exit();
    }
}

if (!empty($_GET['forward_email_verify'])) {
    if (empty($GLOBALS['portal_onsite_two_register']) || empty($GLOBALS['google_recaptcha_site_key']) || empty($GLOBALS['google_recaptcha_secret_key'])) {
        (new SystemLogger())->debug("registration not supported, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }

    $crypto = new CryptoGen();
    if (!$crypto->cryptCheckStandard($_GET['forward_email_verify'])) {
        (new SystemLogger())->debug("illegal token, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }

    $token_one_time = $crypto->decryptStandard($_GET['forward_email_verify'], null, 'drive', 6);
    if (empty($token_one_time)) {
        (new SystemLogger())->debug("unable to decrypt token, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }

    $sqlResource = sqlStatementNoLog("SELECT `id`, `token_onetime`, `fname`, `mname`, `lname`, `dob`, `email`, `language` FROM `verify_email` WHERE `active` = 1 AND `token_onetime` LIKE BINARY ?", [$token_one_time . '%']);
    if (sqlNumRows($sqlResource) > 1) {
        (new SystemLogger())->debug("active token (" . $token_one_time . ") found more than once, so stopped attempt to use forward_email_verify token");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "active token (" . $token_one_time . ") found more than once, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    if (!sqlNumRows($sqlResource)) {
        (new SystemLogger())->debug("active token (" . $token_one_time . ") not found, so stopped attempt to use forward_email_verify token");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "active token (" . $token_one_time . ") not found, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    $sqlVerify = sqlFetchArray($sqlResource);
    if (empty($sqlVerify['id']) || empty($sqlVerify['token_onetime'])) {
        (new SystemLogger())->debug("active token (" . $token_one_time . ") not properly set up, so stopped attempt to use forward_email_verify token");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "active token (" . $token_one_time . ") not properly set up, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    // have "used" token, so now make it inactive
    sqlStatementNoLog("UPDATE `verify_email` SET `active` = 0 WHERE `id` = ?", [$sqlVerify['id']]);

    $validateTime = hex2bin(str_replace($token_one_time, '', $sqlVerify['token_onetime']));
    if ($validateTime <= time()) {
        (new SystemLogger())->debug("active token (" . $token_one_time . ") has expired, so stopped attempt to use forward_email_verify token");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "active token (" . $token_one_time . ") has expired, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        die(xlt("Your email verification link has expired. Reset and try again."));
    }

    if (!empty($sqlVerify['fname']) && !empty($sqlVerify['lname']) && !empty($sqlVerify['dob']) && !empty($sqlVerify['email']) && !empty($sqlVerify['language'])) {
        // token has passed and have all needed data
        $fnameRegistration = $sqlVerify['fname'];
        $_SESSION['fnameRegistration'] = $fnameRegistration;
        $mnameRegistration = $sqlVerify['mname'] ?? '';
        $_SESSION['mnameRegistration'] = $mnameRegistration;
        $lnameRegistration = $sqlVerify['lname'];
        $_SESSION['lnameRegistration'] = $lnameRegistration;
        $dobRegistration = $sqlVerify['dob'];
        $_SESSION['dobRegistration'] = $dobRegistration;
        $emailRegistration = $sqlVerify['email'];
        $_SESSION['emailRegistration'] = $emailRegistration;
        $languageRegistration = $sqlVerify['language'];
        $_SESSION['language_choice'] = (int)($languageRegistration ?? 1);
        $portalRegistrationAuthorization = true;
        $_SESSION['token_id_holder'] = $sqlVerify['id'];
        (new SystemLogger())->debug("token worked for forward_email_verify token, now on to registration");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 1, "token (" . $token_one_time . ") was successful for forward_email_verify token");
        require_once(__DIR__ . "/account/register.php");
        exit();
    } else {
        (new SystemLogger())->debug("active token (" . $token_one_time . ") did not have all required data, so stopped attempt to use forward_email_verify token");
        EventAuditLogger::instance()->newEvent('patient-reg-email-verify', '', '', 0, "active token (" . $token_one_time . ") did not have all required data, so stopped attempt to use forward_email_verify token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
} elseif (isset($_GET['forward'])) {
    if ((empty($GLOBALS['portal_two_pass_reset']) && empty($GLOBALS['portal_onsite_two_register'])) || empty($GLOBALS['google_recaptcha_site_key']) || empty($GLOBALS['google_recaptcha_secret_key'])) {
        (new SystemLogger())->debug("reset password and registration not supported, so stopped attempt to use forward token");
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    $auth = false;
    if (strlen($_GET['forward']) >= 64) {
        $crypto = new CryptoGen();
        if ($crypto->cryptCheckStandard($_GET['forward'])) {
            $one_time = $crypto->decryptStandard($_GET['forward'], null, 'drive', 6);
            if (!empty($one_time)) {
                $auth = sqlQueryNoLog("Select * From patient_access_onsite Where portal_onetime Like BINARY ?", array($one_time . '%'));
            }
        }
    }
    if ($auth === false) {
        error_log("PORTAL ERROR: " . errorLogEscape('One time reset:' . $_GET['forward']), 0);
        $logit->portalLog('login attempt', '', ($_GET['forward'] . ':invalid one time'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&u');
        exit();
    }
    $parse = str_replace($one_time, '', $auth['portal_onetime']);
    $validate = hex2bin(substr($parse, 6));
    if ($validate <= time()) {
        error_log("PORTAL ERROR: " . errorLogEscape('One time reset link expired. Dying.'), 0);
        $logit->portalLog('password reset attempt', '', ($_POST['uname'] . ':link expired'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        die(xlt("Your one time credential reset link has expired. Reset and try again.") . "time:$validate time:" . time());
    }
    $_SESSION['pin'] = substr($parse, 0, 6);
    $_SESSION['forward'] = $auth['portal_onetime'];
    $_SESSION['portal_username'] = $auth['portal_username'];
    $_SESSION['portal_login_username'] = $auth['portal_login_username'];
    $_SESSION['password_update'] = 2;
    $_SESSION['onetime'] = $auth['portal_pwd'];
    unset($auth);
}
// security measure -- will check on next page.
$_SESSION['itsme'] = 1;
//

//
// Deal with language selection
//
// collect default language id (skip this if this is a password update or reset)
if (!(isset($_SESSION['password_update']) || (!empty($GLOBALS['portal_two_pass_reset']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key']) && isset($_GET['requestNew'])))) {
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
            $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='1' />\n";
        }
    } else {
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='" . attr($defaultLangID) . "' />\n";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Patient Portal Login'); ?></title>
    <?php
    Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']);
    ?>
    <script>
        function checkUserName() {
            let vacct = document.getElementById('uname').value;
            let vsuname = document.getElementById('login_uname').value;
            if (vsuname.length < 8) {
                alert(<?php echo xlj('User Name must be at least 8 characters!'); ?>);
                return false;
            }
            let data = {
                'action': 'userIsUnique',
                'account': vacct,
                'loginUname': vsuname
            };
            $.ajax({
                type: 'GET',
                url: './account/account.php',
                data: data
            }).done(function (rtn) {
                if (rtn === '1') {
                    return true;
                }
                alert(<?php echo xlj('Log In Name is unavailable. Try again!'); ?>);
                return false;
            });
        }

        function process() {
            if (!(validate())) {
                alert(<?php echo xlj('Field(s) are missing!'); ?>);
                return false;
            }
            return true;
        }

        function validate() {
            let pass = true;

            if (document.getElementById('uname').value == "") {
                $('#uname').addClass('is-invalid');
                pass = false;
            }
            if (document.getElementById('pass').value == "") {
                $('#pass').addClass('is-invalid');
                pass = false;
            }
            return pass;
        }

        function process_new_pass() {
            if (!(validate_new_pass())) {
                alert(<?php echo xlj('Field(s) are missing!'); ?>);
                return false;
            }
            if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
                alert(<?php echo xlj('The new password fields are not the same.'); ?>);
                return false;
            }
            if (document.getElementById('pass').value == document.getElementById('pass_new').value) {
                alert(<?php echo xlj('The new password can not be the same as the current password.'); ?>);
                return false;
            }
        }

        function validate_new_pass() {
            var pass = true;
            if (document.getElementById('uname').value == "") {
                $('#uname').addClass('is-invalid');
                pass = false;
            }
            if (document.getElementById('pass').value == "") {
                $('#pass').addClass('is-invalid');
                pass = false;
            }
            if (document.getElementById('pass_new').value == "") {
                $('#pass_new').addClass('is-invalid');
                pass = false;
            }
            if (document.getElementById('pass_new_confirm').value == "") {
                $('#pass_new_confirm').addClass('is-invalid');
                pass = false;
            }
            return pass;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const passInput = document.getElementById('pass');
            const toggle = document.getElementById('password-icon');
            toggle.addEventListener('click', function () {
                const isPassword = passInput.getAttribute('type') === 'password';
                passInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>

    <?php if (!empty($GLOBALS['portal_two_pass_reset']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key']) && isset($_GET['requestNew'])) { ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            function enableVerifyBtn() {
                document.getElementById("submitRequest").disabled = false;
            }
        </script>
        <?php // add csrf mechanism for the password reset ui
        CsrfUtils::setupCsrfKey();
        ?>
    <?php } ?>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 800px;
            margin: 30px 0px auto;
            padding: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .login-logo {
            max-width: 60%;
            height: auto;
            text-align: center;
        }

        .login-title {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body class="login">
    <div id="wrapper" class="login-wrapper mx-auto">
        <?php if (isset($_SESSION['password_update']) || isset($_GET['password_update'])) {
            $_SESSION['password_update'] = 1;
            ?>
            <h2 class="title"><?php echo xlt('Please Enter New Credentials'); ?></h2>
            <form class="form pb-5" action="get_patient_info.php" method="POST" onsubmit="return process_new_pass()">
                <?php if (isset($redirectUrl)) { ?>
                    <input id="redirect" type="hidden" name="redirect" value="<?php echo attr($redirectUrl); ?>" />
                <?php } ?>
                <div class="form-row my-3">
                    <label class="col-md-2 col-form-label" for="uname"><?php echo xlt('Account Name'); ?></label>
                    <div class="col-md">
                        <input class="form-control" name="uname" id="uname" type="text" readonly autocomplete="none" value="<?php echo attr($_SESSION['portal_username']); ?>" />
                    </div>
                </div>
                <div class="form-row my-3">
                    <label class="col-md-2 col-form-label" for="login_uname"><?php echo xlt('Use Username'); ?></label>
                    <div class="col-md">
                        <input class="form-control" name="login_uname" id="login_uname" type="text" autofocus autocomplete="none" title="<?php echo xla('Please enter a username of a minimum of 8 characters. Recommended to include symbols and numbers but not required.'); ?>" placeholder="<?php echo xla('Must be a minimum of 8 characters'); ?>" pattern=".{8,80}" value="<?php echo attr($_SESSION['portal_login_username']); ?>" onblur="checkUserName()" />
                    </div>
                </div>
                <div class="form-row my-3">
                    <label class="col-md-2 col-form-label" for="pass"><?php echo empty($_SESSION['onetime'] ?? null) ? xlt('Current Password') : ''; ?></label>
                    <div class="col-md">
                        <input class="form-control" name="pass" id="pass" <?php echo ($_SESSION['onetime'] ?? null) ? 'type="hidden" ' : 'type="password" '; ?> autocomplete="none" value="<?php echo attr($_SESSION['onetime'] ?? '');
                        $_SESSION['password_update'] = ($_SESSION['onetime'] ?? null) ? 2 : 1;
                        unset($_SESSION['onetime']); ?>" required />
                    </div>
                </div>
                <?php if ($_SESSION['pin'] ?? null) { ?>
                    <div class="form-row my-3">
                        <label class="col-md-2 col-form-label" for="token_pin"><?php echo xlt('One Time PIN'); ?></label>
                        <div class="col-md">
                            <input class="form-control" name="token_pin" id="token_pin" type="password" autocomplete="none" value="" required pattern=".{6,20}" />
                        </div>
                    </div>
                <?php } ?>
                <div class="form-row my-3">
                    <label class="col-md-2 col-form-label" for="pass_new"><?php echo xlt('New Password'); ?></label>
                    <div class="col-md">
                        <input class="form-control" name="pass_new" id="pass_new" type="password" required placeholder="<?php echo xla('Min length is 8 with upper,lowercase,numbers mix'); ?>" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </div>
                </div>
                <div class="form-row my-3">
                    <label class="col-md-2 col-form-label" for="pass_new_confirm"><?php echo xlt('Confirm New Password'); ?></label>
                    <div class="col-md">
                        <input class="form-control" name="pass_new_confirm" id="pass_new_confirm" type="password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </div>
                </div>
                <?php if ($GLOBALS['enforce_signin_email']) { ?>
                    <div class="form-row my-3">
                        <label class="col-md-2 col-form-label" for="passaddon"><?php echo xlt('Confirm Email Address'); ?></label>
                        <div class="col-md">
                            <input class="form-control" name="passaddon" id="passaddon" required placeholder="<?php echo xla('Current on record trusted email'); ?>" type="email" autocomplete="none" value="" />
                        </div>
                    </div>
                <?php } ?>
                <input class="btn btn-secondary" type="button" onclick="document.location.replace('./index.php?woops=1&site=<?php echo attr_url($_SESSION['site_id']); ?><?php if (!empty($redirectUrl)) {
                    echo "&redirect=" . attr_url($redirectUrl); } ?>');" value="<?php echo xla('Cancel'); ?>" />
                <input class="btn btn-primary" type="submit" value="<?php echo xla('Log In'); ?>" />
            </form>
        <?php } elseif (!empty($GLOBALS['portal_two_pass_reset']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key']) && isset($_GET['requestNew'])) { ?>
            <form id="resetPass" action="#" method="post">
                <input type='hidden' id='csrf_token_form' name='csrf_token_form' value='<?php echo attr(CsrfUtils::collectCsrfToken('passwordResetCsrf')); ?>' />
                <?php if (isset($redirectUrl)) { ?>
                    <input id="redirect" type="hidden" name="redirect" value="<?php echo attr($redirectUrl); ?>" />
                <?php } ?>
                <div class="text-center">
                    <fieldset>
                        <legend class='bg-primary text-white pt-2 py-1'><h3><?php echo xlt('Patient Credentials Reset') ?></h3></legend>
                        <div class="jumbotron jumbotron-fluid px-5 py-3">
                            <div class="form-row my-3">
                                <label class="col-md-2 col-form-label" for="fname"><?php echo xlt('First Name') ?></label>
                                <div class="col-md">
                                    <input type="text" class="form-control" id="fname" required placeholder="<?php echo xla('First Name'); ?>" />
                                </div>
                            </div>
                            <div class="form-row my-3">
                                <label class="col-md-2 col-form-label" for="lname"><?php echo xlt('Last Name') ?></label>
                                <div class="col-md">
                                    <input type="text" class="form-control" id="lname" required placeholder="<?php echo xla('Last Name'); ?>" />
                                </div>
                            </div>
                            <div class="form-row my-3">
                                <label class="col-md-2 col-form-label" for="dob"><?php echo xlt('Birth Date') ?></label>
                                <div class="col-md">
                                    <input id="dob" type="text" required class="form-control datepicker" placeholder="<?php echo xla('YYYY-MM-DD'); ?>" />
                                </div>
                            </div>
                            <div class="form-row my-3">
                                <label class="col-md-2 col-form-label" for="emailInput"><?php echo xlt('Enter E-Mail Address') ?></label>
                                <div class="col-md">
                                    <input id="emailInput" type="email" class="form-control" required placeholder="<?php echo xla('Current trusted email address on record.'); ?>" maxlength="100" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="<?php echo attr($GLOBALS['google_recaptcha_site_key']); ?>" data-callback="enableVerifyBtn"></div>
                            </div>
                        </div>
                        <input class="btn btn-secondary" type="button" onclick="document.location.replace('./index.php?woops=1&site=<?php echo attr_url($_SESSION['site_id']); ?><?php if (!empty($redirectUrl)) {
                            echo "&redirect=" . attr_url($redirectUrl); } ?>');" value="<?php echo xla('Cancel'); ?>" />
                        <button id="submitRequest" class="btn btn-primary nextBtn" type="submit" disabled="disabled"><?php echo xlt('Verify') ?></button>
                    </fieldset>
                </div>
            </form>
        <?php } else {
            ?> <!-- Main logon -->
        <div class="container-xl p-1">
            <!-- Optionally show two logos, and in either order -->
            <?php if (($GLOBALS['secondary_portal_logo_position'] ?? null) == 'second') { ?>
                <?php if ($GLOBALS['show_portal_primary_logo'] ?? null) { ?>
                    <div class="img-fluid text-center"><img class="login-logo" src='<?php echo $logoSrc; ?>'></div>
                <?php } ?>
                <?php if ($GLOBALS['extra_portal_logo_login'] ?? null) { ?>
                    <div class="img-fluid text-center"><img class="login-logo" src='<?php echo $logo2ndSrc; ?>'></div>
                <?php } ?>
            <?php } else {
                if (($GLOBALS['secondary_portal_logo_position'] ?? null) == 'first') { ?>
                    <?php if ($GLOBALS['extra_portal_logo_login']) { ?>
                        <div class="img-fluid text-center"><img class="login-logo" src='<?php echo $logo2ndSrc; ?>'></div>
                    <?php } ?>
                    <?php if ($GLOBALS['show_portal_primary_logo'] ?? null) { ?>
                        <div class="img-fluid text-center"><img class="login-logo" src='<?php echo $logoSrc; ?>'></div>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <legend class="text-center bg-light text-dark pt-2 py-1"><h2><?php echo $GLOBALS['openemr_name'] . ' ' . xlt('Portal Login'); ?></h2></legend>
            <form class="mx-1" action="get_patient_info.php" method="POST" onsubmit="return process()">
                <?php if (isset($redirectUrl)) { ?>
                    <input id="redirect" type="hidden" name="redirect" value="<?php echo attr($redirectUrl); ?>" />
                <?php } ?>
                <div class="form-group">
                    <label for="uname"><?php echo xlt('Username') ?></label>
                    <input type="text" class="form-control" name="uname" id="uname" autocomplete="none" required />
                </div>
                    <div id="standard-auth-password" class="form-group">
                        <label for="pass"><?php echo xlt('Password') ?></label>
                        <div class="input-group">
                            <input class="form-control" name="pass" id="pass" type="password" required autocomplete="none" />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-eye" id="password-icon" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php if ($GLOBALS['enforce_signin_email']) { ?>
                    <div class="form-group">
                        <label for="passaddon"><?php echo xlt('E-Mail Address') ?></label>
                        <input class="form-control" name="passaddon" id="passaddon" type="email" autocomplete="none" />
                    </div>
                <?php } ?>
                <?php if ($GLOBALS['language_menu_login']) { ?>
                    <?php if (count($result3) != 1) { ?>
                <div class="form-group">
                    <label for="selLanguage"><?php echo xlt('Language'); ?></label>
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
                    <?php }
                } ?>
                </div>
                <div class="col col-md col-sm">
                    <button class="btn btn-success btn-block" type="submit"><?php echo xlt('Log In'); ?></button>
                    <?php if (!empty($GLOBALS['portal_onsite_two_register']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key'])) { ?>
                        <button class="btn btn-secondary btn-block" onclick="location.replace('./account/verify.php?site=<?php echo attr_url($_SESSION['site_id']); ?>')"><?php echo xlt('Register'); ?></button>
                    <?php } ?>
                    <?php if (!empty($GLOBALS['portal_two_pass_reset']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key']) && isset($_GET['w']) && (isset($_GET['u']) || isset($_GET['p']))) { ?>
                        <button class="btn btn-danger btn-block" onclick="location.replace('./index.php?requestNew=1&site=<?php echo attr_url($_SESSION['site_id']); ?><?php if (!empty($redirectUrl)) {
                                echo "&redirect=" . attr_url($redirectUrl); } ?>')"><?php echo xlt('Reset Credentials'); ?></button>
                    <?php } ?>
                </div>
                </fieldset>
                <?php if (!(empty($hiddenLanguageField))) {
                    echo $hiddenLanguageField;
                } ?>
            </form>
        </div>
    </div><!-- div wrapper -->
    <?php } ?> <!--  logon wrapper -->

    <div id="alertStore" class="d-none">
        <div class="h6 alert alert-warning alert-dismissible fade show my-1 py-1" role="alert">
            <button type="button" class="close my-1 py-0" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <script>
        var tab_mode = true;
        var webroot_url = <?php echo js_escape($GLOBALS['web_root']) ?>;

        function restoreSession() {
            //dummy functions so the dlgopen function will work in the patient portal
            return true;
        }

        var isPortal = 1;

        $(function () {
            <?php // if something went wrong
            if (!empty($GLOBALS['portal_two_pass_reset']) && !empty($GLOBALS['google_recaptcha_site_key']) && !empty($GLOBALS['google_recaptcha_secret_key']) && isset($_GET['requestNew'])) {
                $_SESSION['register'] = true;
                $_SESSION['authUser'] = 'portal-user';
                $_SESSION['pid'] = true;
                ?>
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'; ?>
            });
            $(document.body).on('hidden.bs.modal', function () {
                callServer('cleanup');
            });
            $("#resetPass").on('submit', function (e) {
                e.preventDefault();
                callServer('reset_password');
                return false;
            });
            <?php } ?>
            <?php if (isset($_GET['w'])) { ?>
            // mdsupport - Would be good to include some clue about what went wrong!
            bsAlert(<?php echo xlj('Something went wrong. Please try again.'); ?>);
            <?php } ?>
            <?php if (isset($_GET['oe'])) { ?>
            // mdsupport - Would be good to include some clue about what went wrong!
            bsAlert(<?php echo xlj('Something went wrong. Onetime Authentication! Expired.'); ?>);
            <?php } ?>
            <?php if (isset($_GET['oi'])) { ?>
            // mdsupport - Would be good to include some clue about what went wrong!
            bsAlert(<?php echo xlj('Something went wrong. Onetime Authentication! Invalid.'); ?>);
            <?php } ?>
            <?php // if successfully logged out
            if (isset($_GET['logout'])) { ?>
            bsAlert(<?php echo xlj('You have been successfully logged out.'); ?>);
            <?php } ?>

            return false;
        });

        function callServer(action) {
            var data = {};
            if (action === 'reset_password') {
                data = {
                    'action': action,
                    'dob': $("#dob").val(),
                    'last': $("#lname").val(),
                    'first': $("#fname").val(),
                    'email': $("#emailInput").val(),
                    'g-recaptcha-response': grecaptcha.getResponse(),
                    'csrf_token_form': $("#csrf_token_form").val()
                }
            }
            if (action === 'cleanup') {
                data = {
                    'action': action
                }
            }
            $.ajax({
                type: 'GET',
                url: './account/account.php',
                data: data
            }).done(function (rtn) {
                if (action === "cleanup") {
                    let url = "./index.php?site=" + <?php echo js_url($_SESSION['site_id']); ?>; // Goto landing page.
                    let redirectUrl = $("#redirect").val();
                    if (redirectUrl) {
                        url += "&redirect=" + encodeURIComponent(redirectUrl);
                    }
                    window.location.href = url;
                } else if (action === "reset_password") {
                    if (JSON.parse(rtn) === 1) {
                        dialog.alert(<?php echo xlj("Check your email inbox (and possibly your spam folder) for further instructions to reset your password. If you have not received an email, then recommend contacting the clinic.") ?>);
                        return false;
                    } else {
                        dialog.alert(<?php echo xlj("Something went wrong. Recommend contacting the clinic.") ?>);
                        return false;
                    }
                }
            }).fail(function (err) {
                var message = <?php echo xlj('Something went wrong.') ?>;
                alert(message);
            });
        }

        function bsAlert(msg) {
            let divAlert = document.getElementById("alertStore").querySelector("div.alert").cloneNode(true);
            document.querySelector("form").prepend(divAlert);
            let strongMsg = document.createElement("strong");
            strongMsg.innerHTML = msg;
            divAlert.prepend(strongMsg);
            setTimeout(() => {
                document.querySelector("div.alert").remove();
            }, 6000);
        }
    </script>
</body>
</html>
