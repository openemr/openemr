<?php

/**
 * Ajax Handler for Register
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::instance()->getWrapper();
$globalsBag = OEGlobalsBag::getInstance();

if (
    ($session->get('verifyPortalEmail') === true) ||
    (($session->get('register', null)) === true && $session->has('pid')) ||
    (($session->get('credentials_update', null)) === 1 && $session->has('pid')) ||
    (($session->get('itsme', null)) === 1 && $session->has('password_update'))
) {
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . "/../../interface/globals.php");
require_once("$srcdir/patient.inc.php");
require_once(__DIR__ . "/../lib/portal_mail.inc.php");
require_once("$srcdir/pnotes.inc.php");
require_once("./account.lib.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\Header;

$action = $_REQUEST['action'] ?? '';

if ($action == 'verify_email') {
    if (!empty($session->get('verifyPortalEmail')) && ($session->get('verifyPortalEmail') === true)) {
        if (!empty($globalsBag->get('portal_onsite_two_register')) && !empty($globalsBag->get('google_recaptcha_site_key')) && !empty($globalsBag->get('google_recaptcha_secret_key'))) {
            // check csrf
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'verifyEmailCsrf', $session->getSymfonySession())) {
                CsrfUtils::csrfNotVerified(true, true, false);
                cleanupRegistrationSession();
                exit;
            }
            // check recaptcha
            $recaptcha = processRecaptcha($_POST['g-recaptcha-response'] ?? '');
            if (!$recaptcha) {
                echo xlt("Something went wrong. Recommend contacting the clinic.");
                cleanupRegistrationSession();
                exit;
            }
            // process
            $rtn = verifyEmail($_POST['languageChoice'] ?? '', $_POST['fname'] ?? '', $_POST['mname'] ?? '', $_POST['lname'] ?? '', $_POST['dob'] ?? '', $_POST['email'] ?? '');
            if ($rtn) {
                Header::setupHeader();
                echo '<div class="alert alert-success" role="alert">' . xlt("Check your email inbox (and possibly your spam folder) for further instructions to register. If you have not received an email, then recommend contacting the clinic.") . '</div>';
            } else {
                echo xlt("Something went wrong. Recommend contacting the clinic.");
            }
        }
    }
    cleanupRegistrationSession();
    exit;
}

if ($action == 'userIsUnique') {
    if (
        ((int)$session->get('credentials_update') === 1 && $session->has('pid')) ||
        ((int)$session->get('itsme') === 1 && $session->has('password_update'))
    ) {
        // The above comparisons will not allow querying for usernames if not authorized (ie. not including the register stuff)
        if (empty(trim((string) $_REQUEST['account']))) {
            echo "0";
            exit;
        }
        $tmp = trim((string) $_REQUEST['loginUname']);
        if (empty($tmp)) {
            echo "0";
            exit;
        }
        $auth = sqlQueryNoLog("Select * From patient_access_onsite Where portal_login_username = ? Or portal_username = ?", [$tmp, $tmp]);
        if ($auth === false) {
            echo "1";
            exit;
        }

        if ($auth['portal_username'] === trim((string) $_REQUEST['account'])) {
            echo "1";
            exit;
        }
    }
    echo "0";
    exit;
}

if ($action == 'reset_password') {
    if ($session->get('register', null) === true && $session->has('pid')) {
        $rtn = 0;
        if (!empty($globalsBag->get('portal_two_pass_reset')) && !empty($globalsBag->get('google_recaptcha_site_key')) && !empty($globalsBag->get('google_recaptcha_secret_key'))) {
            // check csrf
            if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"], 'passwordResetCsrf', $session->getSymfonySession())) {
                CsrfUtils::csrfNotVerified(true, true, false);
                cleanupRegistrationSession();
                exit;
            }
            // check recaptcha
            $recaptcha = processRecaptcha($_GET['g-recaptcha-response'] ?? '');
            if ($recaptcha) {
                // Allow Patients to Reset Credentials setting is turned on
                $rtn = resetPassword($_GET['dob'] ?? '', $_GET['last'] ?? '', $_GET['first'] ?? '', $_GET['email'] ?? '');
            }
        }
        echo js_escape($rtn);
        exit();
    } else {
        cleanupRegistrationSession();
        exit();
    }
}

if ($action == 'do_signup') {
    if ($session->get('register_silo_ajax', null) === true && $session->get('register', null) === true && $session->has('pid')) {
        if (!empty($globalsBag->get('portal_onsite_two_register')) && !empty($globalsBag->get('google_recaptcha_site_key')) && !empty($globalsBag->get('google_recaptcha_secret_key'))) {
            $pidHolder = getPidHolder();
            if ($pidHolder == 0) {
                (new SystemLogger())->error("account.php action do_signup failed because unable to collect pid from pid_holder");
                cleanupRegistrationSession();
                exit();
            }
            $rtn = doCredentials($pidHolder);
            if ($rtn) {
                (new SystemLogger())->debug("account.php action do_signup apparently successful");
                if (!empty($_GET['provider'])) {
                    notifyAdmin($pidHolder, $_GET['provider']);
                    (new SystemLogger())->debug("account.php action do_signup apparently successful, so sent a pnote to the provider");
                }
                Header::setupHeader();
                echo '<div class="alert alert-success" role="alert">' . xlt("Your new credentials have been sent. Check your email inbox and also possibly your spam folder. Once you log into your patient portal feel free to make an appointment or send us a secure message. We look forward to seeing you soon.") . '</div>';
            } else {
                (new SystemLogger())->debug("account.php action do_signup apparently not successful");
                Header::setupHeader();
                echo '<div class="alert alert-danger" role="alert">' . xlt("There was a problem registering you. Recommend contacting clinic for assistance.") . '</div>';
            }
        } else {
            (new SystemLogger())->error("account.php action do_signup attempted without registration module on, so failed");
        }
    }
    cleanupRegistrationSession();
    exit();
}

if ($action == 'new_insurance') {
    if ($session->get('register_silo_ajax', null) === true && $session->get('register', null) === true && $session->has('pid')) {
        if (!empty($globalsBag->get('portal_onsite_two_register')) && !empty($globalsBag->get('google_recaptcha_site_key')) && !empty($globalsBag->get('google_recaptcha_secret_key'))) {
            $pidHolder = getPidHolder(true);
            if ($pidHolder == 0) {
                (new SystemLogger())->error("account.php action new_insurance was not successful because unable to collect pid from pid_holder. will still complete registration process, which will not include insurance.");
                exit();
            }
            saveInsurance($pidHolder);
            (new SystemLogger())->debug("account.php action new_insurance was apparently successful");
            exit();
        } else {
            (new SystemLogger())->error("account.php action new_insurance attempted without registration module on, so failed");
            cleanupRegistrationSession();
            exit();
        }
    } else {
        cleanupRegistrationSession();
        exit();
    }
}

if ($action == 'cleanup') {
    cleanupRegistrationSession();
    exit();
}
