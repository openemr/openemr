<?php
/**
 * The outside frame that holds all of the OpenEMR User Interface.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Include our required headers */
require_once('../globals.php');

use OpenEMR\Core\Header;
use u2flib_server\U2F;

///////////////////////////////////////////////////////////////////////
// Functions to support MFA.
///////////////////////////////////////////////////////////////////////

function posted_to_hidden($name)
{
    if (isset($_POST[$name])) {
        echo "<input type='hidden' name='" . attr($name) . "' value='" . attr($_POST[$name]) . "' />\n";
    }
}

function generate_html_start($title)
{
    global $appId;
    ?>
<html>
<head>
    <?php Header::setupHeader(); ?>
<title><?php echo text($title); ?></title>
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
<script>
function doAuth() {
  var f = document.forms[0];
  var requests = JSON.parse(f.form_requests.value);
  // The server's getAuthenticateData() repeats the same challenge in all requests.
  var challenge = requests[0].challenge;
  var registeredKeys = new Array();
  for (var i = 0; i < requests.length; ++i) {
    registeredKeys[i] = {"version": requests[i].version, "keyHandle": requests[i].keyHandle};
  }
  u2f.sign(
    '<?php echo addslashes($appId); ?>',
    challenge,
    registeredKeys,
    function(data) {
      if(data.errorCode && data.errorCode != 0) {
        alert('<?php echo xls("Key access failed with error"); ?> ' + data.errorCode);
        return;
      }
      f.form_response.value = JSON.stringify(data);
      f.submit();
    },
    60
  );
}
</script>
</head>
<body class='body_top'>
<center>
<h2><?php echo text($title); ?></h2>
<form method="post"
 action="main_screen.php?auth=login&site=<?php echo attr(urlencode($_GET['site'])); ?>"
 target="_top" name="challenge_form">
    <?php
    posted_to_hidden('new_login_session_management');
    posted_to_hidden('authProvider');
    posted_to_hidden('languageChoice');
    posted_to_hidden('authUser');
    posted_to_hidden('clearPass');
}

function generate_html_end()
{
    echo "</form></center></body></html>\n";
    session_unset();
    session_destroy();
    unset($_COOKIE[session_name()]);
    return 0;
}

$errormsg = '';

///////////////////////////////////////////////////////////////////////
// Begin code to support U2F.
///////////////////////////////////////////////////////////////////////

$regs = array();          // for mapping device handles to their names
$registrations = array(); // the array of stored registration objects
$res1 = sqlStatement(
    "SELECT a.name, a.var1 FROM login_mfa_registrations AS a " .
    "WHERE a.user_id = ? AND a.method = 'U2F' ORDER BY a.name",
    array($_SESSION['authId'])
);
while ($row1 = sqlFetchArray($res1)) {
    $regobj = json_decode($row1['var1']);
    $regs[json_encode($regobj->keyHandle)] = $row1['name'];
    $registrations[] = $regobj;
}
if (!empty($registrations)) {
    // There is at least one U2F key registered so we have to request or verify key data.
    // https is required, and with a proxy the server might not see it.
    $scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
    $appId = $scheme . $_SERVER['HTTP_HOST'];
    $u2f = new u2flib_server\U2F($appId);
    $userid = $_SESSION['authId'];
    $form_response = empty($_POST['form_response']) ? '' : $_POST['form_response'];
    if ($form_response) {
        // We have key data, check if it matches what was registered.
        $tmprow = sqlQuery("SELECT login_work_area FROM users_secure WHERE id = ?", array($userid));
        try {
            $registration = $u2f->doAuthenticate(
                json_decode($tmprow['login_work_area']), // these are the original challenge requests
                $registrations,
                json_decode($_POST['form_response'])
            );
            // Stored registration data needs to be updated because the usage count has changed.
            // We have to use the matching registered key.
            $strhandle = json_encode($registration->keyHandle);
            if (isset($regs[$strhandle])) {
                sqlStatement(
                    "UPDATE login_mfa_registrations SET `var1` = ? WHERE " .
                    "`user_id` = ? AND `method` = 'U2F' AND `name` = ?",
                    array(json_encode($registration), $userid, $regs[$strhandle])
                );
            } else {
                error_log("Unexpected keyHandle returned from doAuthenticate(): '$strhandle'");
            }
            // Keep track of when challenges were last answered correctly.
            sqlStatement(
                "UPDATE users_secure SET last_challenge_response = NOW() WHERE id = ?",
                array($_SESSION['authId'])
            );
        } catch (u2flib_server\Error $e) {
            // Authentication failed so we will build the U2F form again.
            $form_response = '';
            $errormsg = xl('Authentication error') . ": " . $e->getMessage();
        }
    }
    if (!$form_response) {
        // There is no key data yet or authentication failed, so we need to solicit it.
        $requests = json_encode($u2f->getAuthenticateData($registrations));
        // Persist the challenge also in the database because the browser is untrusted.
        sqlStatement(
            "UPDATE users_secure SET login_work_area = ? WHERE id = ?",
            array($requests, $userid)
        );
        generate_html_start(xl('U2F Key Verification'));
        echo "<p>\n";
        echo xlt('Insert your key into a USB port and click the Authenticate button below.');
        echo " " . xlt('Then press the flashing button on your key within 1 minute.') . "</p>\n";
        echo "<table><tr><td>\n";
        echo "<input type='button' value='" . xla('Authenticate') . "' onclick='doAuth()' />\n";
        echo "<input type='hidden' name='form_requests' value='" . attr($requests) . "' />\n";
        echo "<input type='hidden' name='form_response' value='' />\n";
        echo "</td></tr></table>\n";
        if ($errormsg) {
            echo "<p style='color:red;font-weight:bold'>" . text($errormsg) . "</p>\n";
        }
        exit(generate_html_end());
    }
}

///////////////////////////////////////////////////////////////////////
// End of U2F logic.
///////////////////////////////////////////////////////////////////////

// Creates a new session id when load this outer frame
// (allows creations of separate OpenEMR frames to view patients concurrently
//  on different browser frame/windows)
// This session id is used below in the restoreSession.php include to create a
// session cookie for this specific OpenEMR instance that is then maintained
// within the OpenEMR instance by calling top.restoreSession() whenever
// refreshing or starting a new script.
if (isset($_POST['new_login_session_management'])) {
    // This is a new login, so create a new session id and remove the old session
    session_regenerate_id(true);
} else {
    // This is not a new login, so check csrf and then create a new session id and do NOT remove the old session
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
    session_regenerate_id(false);
}
// Create the csrf_token
$_SESSION['csrf_token'] = createCsrfToken();

$_SESSION["encounter"] = '';

// Fetch the password expiration date
$is_expired=false;
if ($GLOBALS['password_expiration_days'] != 0) {
    $is_expired=false;
    $q= (isset($_POST['authUser'])) ? $_POST['authUser'] : '';
    $result = sqlStatement("select pwd_expiration_date from users where username = ?", array($q));
    $current_date = date('Y-m-d');
    $pwd_expires_date = $current_date;
    if ($row = sqlFetchArray($result)) {
        $pwd_expires_date = $row['pwd_expiration_date'];
    }

  // Display the password expiration message (starting from 7 days before the password gets expired)
    $pwd_alert_date = date('Y-m-d', strtotime($pwd_expires_date . '-7 days'));

    if (strtotime($pwd_alert_date) != '' &&
      strtotime($current_date) >= strtotime($pwd_alert_date) &&
      (!isset($_SESSION['expiration_msg'])
      or $_SESSION['expiration_msg'] == 0)) {
        $is_expired = true;
        $_SESSION['expiration_msg'] = 1; // only show the expired message once
    }
}

if ($is_expired) {
  //display the php file containing the password expiration message.
    $frame1url = "pwd_expires_alert.php?csrf_token_form=" . attr(urlencode(collectCsrfToken()));
    $frame1target = "adm";
} else if (!empty($_POST['patientID'])) {
    $patientID = 0 + $_POST['patientID'];
    if (empty($_POST['encounterID'])) {
        // Open patient summary screen (without a specific encounter)
        $frame1url = "../patient_file/summary/demographics.php?set_pid=" . attr(urlencode($patientID));
        $frame1target = "pat";
    } else {
        // Open patient summary screen with a specific encounter
        $encounterID = 0 + $_POST['encounterID'];
        $frame1url = "../patient_file/summary/demographics.php?set_pid=" . attr(urlencode($patientID)) . "&set_encounterid=" . attr(urlencode($encounterID));
        $frame1target = "pat";
    }
} else if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
    $frame1url = "calendar/index.php?pid=" . attr(urlencode($_GET['pid']));
    if (isset($_GET['date'])) {
        $frame1url .= "&date=" . attr(urlencode($_GET['date']));
    }

    $frame1target = "cal";
} else {
    // standard layout
    $map_paths_to_targets = array(
        'main_info.php' => ('cal'),
        '../new/new.php' => ('pat'),
        '../../interface/main/finder/dynamic_finder.php' => ('fin'),
        '../../interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1' => ('flb'),
        '../../interface/main/messages/messages.php?form_active=1' => ('msg')
    );
    if ($GLOBALS['default_top_pane']) {
        $frame1url=attr($GLOBALS['default_top_pane']);
        $frame1target = $map_paths_to_targets[$GLOBALS['default_top_pane']];
        if (empty($frame1target)) {
            $frame1target = "msc";
        }
    } else {
        $frame1url = "main_info.php";
        $frame1target = "cal";
    }
    if ($GLOBALS['default_second_tab']) {
        $frame2url=attr($GLOBALS['default_second_tab']);
        $frame2target = $map_paths_to_targets[$GLOBALS['default_second_tab']];
        if (empty($frame2target)) {
            $frame2target = "msc";
        }
    } else {
        $frame2url = "../../interface/main/messages/messages.php?form_active=1";
        $frame2target = "msg";
    }
}

$nav_area_width = '130';
if (!empty($GLOBALS['gbl_nav_area_width'])) {
    $nav_area_width = $GLOBALS['gbl_nav_area_width'];
}

// This is where will decide whether to use tabs layout or non-tabs layout
// Will also set Session variables to communicate settings to tab layout
if ($GLOBALS['new_tabs_layout']) {
    $_SESSION['frame1url'] = $frame1url;
    $_SESSION['frame1target'] = $frame1target;
    $_SESSION['frame2url'] = $frame2url;
    $_SESSION['frame2target'] = $frame2target;
    // mdsupport - Apps processing invoked for valid app selections from list
    if ((isset($_POST['appChoice'])) && ($_POST['appChoice'] !== '*OpenEMR')) {
        $_SESSION['app1'] = $_POST['appChoice'];
    }

    // Pass a unique token, so main.php script can not be run on its own
    $_SESSION['token_main_php'] = createUniqueToken();
    header('Location: ' . $web_root . "/interface/main/tabs/main.php?token_main=" . urlencode($_SESSION['token_main_php']));
    exit();
}

?>
<html>
<head>
<title>
<?php echo text($openemr_name) ?>
</title>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-1-9-1/jquery.min.js"></script>
<script type="text/javascript" src="../../library/topdialog.js"></script>
    <script type="text/javascript" src="tabs/js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>

<link rel="shortcut icon" href="<?php echo $GLOBALS['images_static_relative']; ?>/favicon.ico" />

<script language='JavaScript'>

// Flag that tab mode is off
var tab_mode=false;

var webroot_url = '<?php echo $GLOBALS['web_root']; ?>';
var jsLanguageDirection = "<?php echo $_SESSION["language_direction"]; ?>";

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// Since this should be the parent window, this is to prevent calls to the
// window that opened this window. For example when a new window is opened
// from the Patient Flow Board or the Patient Finder.
window.opener = null;

// This flag indicates if another window or frame is trying to reload the login
// page to this top-level window.  It is set by javascript returned by auth.inc
// and is checked by handlers of beforeunload events.
var timed_out = false;

// This counts the number of frames that have reported themselves as loaded.
// Currently only left_nav and Title do this, so the maximum will be 2.
// This is used to determine when those frames are all loaded.
var loadedFrameCount = 0;

function allFramesLoaded() {
 // Change this number if more frames participate in reporting.
 return loadedFrameCount >= 2;
}
</script>

</head>

<?php
/*
 * for RTL layout we need to change order of frames in framesets
 */
$lang_dir = $_SESSION['language_direction'];

$sidebar_tpl = "<frameset rows='*,0' frameborder='0' border='0' framespacing='0'>
   <frame src='left_nav.php' name='left_nav' />
   <frame src='daemon_frame.php' name='Daemon' scrolling='no' frameborder='0'
    border='0' framespacing='0' />
  </frameset>";

$main_tpl = "<frameset rows='60%,*' id='fsright' bordercolor='#999999' frameborder='1'>" ;
$main_tpl .= "<frame src='". $frame1url ."' name='RTop' scrolling='auto' />
   <frame src='messages/messages.php?form_active=1' name='RBot' scrolling='auto' /></frameset>";

// Please keep in mind that border (mozilla) and framespacing (ie) are the
// same thing. use both.
// frameborder specifies a 3d look, not whether there are borders.

if (empty($GLOBALS['gbl_tall_nav_area'])) {
    // not tall nav area ?>
<frameset rows='<?php echo attr($GLOBALS['titleBarHeight']) + 5 ?>,*' frameborder='1' border='1' framespacing='1' onunload='imclosing()'>
 <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' noresize />
    <?php if ($lang_dir != 'rtl') { ?>
     <frameset cols='<?php echo attr($nav_area_width) . ',*'; ?>' id='fsbody' frameborder='1' border='4' framespacing='4'>
        <?php echo $sidebar_tpl ?>
        <?php echo $main_tpl ?>
     </frameset>

    <?php } else { ?>
     <frameset cols='<?php echo  '*,' . attr($nav_area_width); ?>' id='fsbody' frameborder='1' border='4' framespacing='4'>
        <?php echo $main_tpl ?>
        <?php echo $sidebar_tpl ?>
     </frameset>

    <?php } ?>

 </frameset>
</frameset>

<?php } else { // use tall nav area ?>
<frameset cols='<?php echo attr($nav_area_width); ?>,*' id='fsbody' frameborder='1' border='4' framespacing='4' onunload='imclosing()'>
 <frameset rows='*,0' frameborder='0' border='0' framespacing='0'>
  <frame src='left_nav.php' name='left_nav' />
  <frame src='daemon_frame.php' name='Daemon' scrolling='no' frameborder='0'
   border='0' framespacing='0' />
 </frameset>
 <frameset rows='<?php echo attr($GLOBALS['titleBarHeight']) + 5 ?>,*' frameborder='1' border='1' framespacing='1'>
  <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' />
  <frameset rows='60%,*' id='fsright' bordercolor='#999999' frameborder='1' border='4' framespacing='4'>
   <frame src='<?php echo $frame1url ?>' name='RTop' scrolling='auto' />
   <frame src='messages/messages.php?form_active=1' name='RBot' scrolling='auto' />
  </frameset>
 </frameset>
</frameset>

<?php } // end tall nav area ?>

</html>
