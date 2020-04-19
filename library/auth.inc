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
 * @author    cfapress
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Logging\EventAuditLogger;

$incoming_site_id = '';

if (
    isset($_GET['auth']) && ($_GET['auth'] == "login") && isset($_POST['authUser']) &&
    isset($_POST['clearPass']) && isset($_POST['new_login_session_management'])
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

    if (!(new AuthUtils('login'))->confirmUserPassword($_POST['authUser'], $_POST['clearPass'])) {
        // login attempt failed
        $_SESSION['loginfailure'] = 1;
        authLoginScreen();
    }

    // login attempt success
    $_SESSION['loginfailure'] = null;
    unset($_SESSION['loginfailure']);

    // store the very first initial timestamp for timeout errors
    $_SESSION["last_update"] = time();
} else if ((isset($_GET['auth'])) && ($_GET['auth'] == "logout")) {
    // Logout
    // If session has timed out / been destroyed, logout record for null user/provider will be invalid.
    if (!empty($_SESSION['authUser']) && !empty($_SESSION['authProvider'])) {
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "success");
    }
    authCloseSession();
    authLoginScreen(true);
} else {
    // Check if session is valid (already logged in user)
    if (AuthUtils::authCheckSession()) {
        // Session is valid
        if (isset($_SESSION['pid']) && empty($GLOBALS['DAEMON_FLAG'])) {
            require_once("{$GLOBALS['srcdir']}/patient.inc");
        }
    } else {
        // Session is not valid (this should only happen if a user's password is changed via another session while the user is logged in)
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "authCheckSession() check failed, so force logout");
        authCloseSession();
        authLoginScreen(true);
    }
}

// Ensure user has not timed out, if applicable
// Have a mechanism to skip the timeout and timeout reset mechanisms if a skip_timeout_reset parameter exists. This
//  can be used by scripts that continually request information from the server; for example the Messages
//  and Reminders automated intermittent requests.
if (!isset($_SESSION["last_update"])) {
    // This should never happen
    EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "last_update not set, so force logout");
    error_log("OpenEMR ERROR: last_update not set, so forced logout");
    authCloseSession();
    authLoginScreen(true);
} else {
    if (((time() - $_SESSION["last_update"]) > $GLOBALS['timeout']) && empty($_REQUEST['skip_timeout_reset'])) {
        // User has timed out.
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "timeout, so force logout");
        authCloseSession();
        authLoginScreen(true);
    } else {
        // User not timed out. Reset the timer, if applicable.
        if (empty($GLOBALS['DAEMON_FLAG']) && empty($_REQUEST['skip_timeout_reset'])) {
            $_SESSION["last_update"] = time();
        }
    }
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
