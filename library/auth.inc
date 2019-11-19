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

//----------THINGS WE ALWAYS DO

// added for the phpGACL group check -- JRM
require_once("{$GLOBALS['srcdir']}/acl.inc");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Logging\EventAuditLogger;

$incoming_site_id = '';

if (isset($_GET['auth']) && ($_GET['auth'] == "login") && isset($_POST['authUser']) &&
    isset($_POST['clearPass']) && isset($_POST['new_login_session_management'])) {
    // set the language
    if (!empty($_POST['languageChoice'])) {
        $_SESSION['language_choice'] = $_POST['languageChoice'];
    } else {
        $_SESSION['language_choice'] = 1;
    }

    // set language direction according to language choice. Later in globals.php we'll override main theme name if needed.
    $_SESSION['language_direction'] = getLanguageDir($_SESSION['language_choice']);

    if (!(new AuthUtils('login'))->confirmUserPassword($_POST['authUser'], $_POST['clearPass'])) {
        $_SESSION['loginfailure'] = 1;
        authLoginScreen();
    }

    $_SESSION['loginfailure'] = null;
    unset($_SESSION['loginfailure']);
    //store the very first initial timestamp for timeout errors
    $_SESSION["last_update"] = time();
} else if ((isset($_GET['auth'])) && ($_GET['auth'] == "logout")) {
    //If session has timed out / been destroyed, logout record for null user/provider will be invalid.
    if (!empty($_SESSION['authUser']) && !empty($_SESSION['authProvider'])) {
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "success");
    }
    authCloseSession();
    authLoginScreen(true);
} else {
    if (AuthUtils::authCheckSession()) {
        if (isset($_SESSION['pid']) && empty($GLOBALS['DAEMON_FLAG'])) {
            require_once("{$GLOBALS['srcdir']}/patient.inc");
        }
    } else {
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "authCheckSession() check failed, so force logout!");
        authCloseSession();
        authLoginScreen(true);
    }
}

if (!isset($_SESSION["last_update"])) {
    EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "last_update not set, so force logout");
    authCloseSession();
    authLoginScreen(true);
} else {
     //if page has not been updated in a given period of time, we call login screen
     //--Note can't perform nice logout if skip_timeout_reset is set since these are called
     //via ajax scripts where this output is not getting sent to browser.
     //--Note DAEMON_FLAG is ok because it is run from a frame in the browser.
    if (((time() - $_SESSION["last_update"]) > $timeout) && empty($_REQUEST['skip_timeout_reset'])) {
        EventAuditLogger::instance()->newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "timeout, so force logout");
        authCloseSession();
        authLoginScreen(true);
    } else {
        // Have a mechanism to skip the timeout reset mechanism if a skip_timeout_reset parameter exists. This
        //  can be used by scripts that continually request information from the server; for example the Messages
        //  and Reminders automated intermittent requests that happen in the Messages Center script and in
        //  the left navigation menu script.
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
