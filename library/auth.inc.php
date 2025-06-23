<?php

/**
 * Authorization functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    ViCarePlus <visolve_emr@visolve.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    cfapress
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionTracker;
use OpenEMR\Services\UserService;

$incoming_site_id = '';
// This is the conditional that ensures that the submission has the required parameters to attempt a login
if (
    isset($_GET['auth'])
    && ($_GET['auth'] == "login")
    && isset($_POST['new_login_session_management'])
    && (
        // Either normal login or google sign-in
        (isset($_POST['authUser']) && isset($_POST['clearPass']))
        || (!empty($GLOBALS['google_signin_enabled']) && !empty($GLOBALS['google_signin_client_id']) && !empty($_POST['used_google_signin']) && !empty($_POST['google_signin_token']))
    )
) {
    // Attempt login

    // set the language
    if (!empty($_POST['languageChoice'])) {
        $_SESSION['language_choice'] = $_POST['languageChoice'];
    } else {
        $_SESSION['language_choice'] = 1;
    }

    // set language direction according to language choice. Later in globals.php we'll override main theme name if needed.
    $_SESSION['language_direction'] = getLanguageDir($_SESSION['language_choice']);

    // Note we are purposefully keeping $_POST['clearPass'], which is needed for MFA to work. It is cleared from memory after a
    //  unsuccessful or successful login
    $passTemp = $_POST['clearPass'];

    $login_success = false;
    if (
        !empty($GLOBALS['google_signin_enabled']) &&
        !empty($GLOBALS['google_signin_client_id']) &&
        !empty($_POST['used_google_signin']) &&
        !empty($_POST['google_signin_token'])
    ) {
        // google sign-in
        $login_success = AuthUtils::verifyGoogleSignIn($_POST['google_signin_token']);
    } else {
        // normal login
        $login_success = (new AuthUtils('login'))->confirmPassword($_POST['authUser'], $passTemp);
    }

    if ($login_success !== true) {
        // login attempt failed
        $_SESSION['loginfailure'] = 1;
        if (function_exists('sodium_memzero')) {
            sodium_memzero($_POST["clearPass"]);
        } else {
            $_POST["clearPass"] = '';
        }
        authLoginScreen();
    }

    // login attempt success
    $_SESSION['loginfailure'] = null;
    unset($_SESSION['loginfailure']);

    // skip the session expiration check below since the entry in session_tracker is not ready yet
    $skipSessionExpirationCheck = true;
} elseif ((isset($_GET['auth'])) && ($_GET['auth'] == "logout")) {
    // Logout
    // If session has timed out / been destroyed, logout record for null user/provider will be invalid.
    if (!empty($_SESSION['authUser']) && !empty($_SESSION['authProvider'])) {
        if ((isset($_GET['timeout'])) && ($_GET['timeout'] == "1")) {
            EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "timeout, so force logout");
        } else {
            EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "success");
        }
    }
    authCloseSession();
    authLoginScreen(true);
} else {
    // Check if session is valid (already logged in user)
    if (!AuthUtils::authCheckSession()) {
        // Session is not valid (this should only happen if a user's password is changed via another session while the user is logged in)
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'] ?? '', $_SESSION['authProvider'] ?? '', 0, "authCheckSession() check failed, so force logout");
        authCloseSession();
        authLoginScreen(true);
    }
}

// Ensure user has not timed out, if applicable
// Have a mechanism to skip the timeout and timeout reset mechanisms if a skip_timeout_reset parameter exists. This
//  can be used by scripts that continually request information from the server; for example the Messages
//  and Reminders automated intermittent requests.
// Also skipping this all on login since entry in session_tracker is not ready yet
if (empty($skipSessionExpirationCheck) && empty($_REQUEST['skip_timeout_reset'])) {
    if (!SessionTracker::isSessionExpired()) {
        SessionTracker::updateSessionExpiration();
    } else {
        // User has timed out.
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "timeout, so force logout");
        authCloseSession();
        authLoginScreen(true);
    }
}

// below 2 function calls are only completed when environment setting 'THROTTLE_DOWN_WAIT_MILLISECONDS' is set
//  used predominantly by demo farm to prevent abuse of demo farm
$throttleDownWaitMilliseconds = getenv('THROTTLE_DOWN_WAIT_MILLISECONDS', true) ?? 0;
if (empty($skipSessionExpirationCheck) && $throttleDownWaitMilliseconds > 0) {
    SessionTracker::updateSessionThrottleDown();
    SessionTracker::processSessionThrottleDown($throttleDownWaitMilliseconds);
}

require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
function authCloseSession()
{
  // Before destroying the session, save its site_id so that the next
  // login will default to that same site.
    global $incoming_site_id;
    $incoming_site_id = $_SESSION['site_id'];
    OpenEMR\Common\Session\SessionUtil::coreSessionDestroy();
}

function authLoginScreen($timed_out = false)
{
  // See comment in authCloseSession().
    global $incoming_site_id;
    ?>
<script>
 // Find the top level window for this instance of OpenEMR, set a flag indicating
 // session timeout has occurred, and reload the login page into it.  This is so
 // that beforeunload event handlers will not obstruct the process in this case.
 var w = window;
 while (w.opener) { // in case we are in a dialog window
  var wtmp = w;
  w = w.opener;
  wtmp.close();
 }
    <?php if ($timed_out) { ?>
 w.top.timed_out = true;
<?php } ?>
 w.top.location.href = '<?php echo "{$GLOBALS['login_screen']}?error=1&site=$incoming_site_id"; ?>';
</script>
    <?php
    exit;
}

?>
