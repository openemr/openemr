<?php
/**
 * The outside frame that holds all of the OpenEMR User Interface.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* Include our required headers */
require_once('../globals.php');

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;
use u2flib_server\U2F;

///////////////////////////////////////////////////////////////////////
// Functions to support MFA.
///////////////////////////////////////////////////////////////////////

function posted_to_hidden($name)
{
    if (isset($_POST[$name])) {
        echo "<input type='hidden' name='" . attr($name) . "' value='" . attr($_POST[$name]) . "' />\r\n";
    }
}

function generate_html_start()
{
    ?>
    <html>
    <head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("MFA Authorization"); ?></title>
    <style>
    .alert-msg {
        font-size:100%;
        font-weight:700;
    }
    </style>
    <?php
}

function generate_html_u2f()
{
    global $appId;
    ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
    <script>
        function doAuth() {
            var f = document.getElementById("u2fform");
            var requests = JSON.parse(f.form_requests.value);
            // The server's getAuthenticateData() repeats the same challenge in all requests.
            var challenge = requests[0].challenge;
            var registeredKeys = new Array();
            for (var i = 0; i < requests.length; ++i) {
                registeredKeys[i] = {"version": requests[i].version, "keyHandle": requests[i].keyHandle};
            }
            u2f.sign(
                <?php echo js_escape($appId); ?>,
                challenge,
                registeredKeys,
                function (data) {
                    if (data.errorCode && data.errorCode != 0) {
                        alert(<?php echo xlj("Key access failed with error"); ?> +' ' + data.errorCode);
                        return;
                    }
                    f.form_response.value = JSON.stringify(data);
                    f.submit();
                },
                60
            );
        }

    </script>
    <?php
}
function input_focus()
{
    ?>
    <script>
        $(function() {
                $('#totp').focus();
        });
    </script>

    <?php
}

function generate_html_top()
{
    echo '</head>';
    echo '<body>';
}

function generate_html_middle()
{
    posted_to_hidden('new_login_session_management');
    posted_to_hidden('authProvider');
    posted_to_hidden('languageChoice');
    posted_to_hidden('authUser');
    posted_to_hidden('clearPass');
}

require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
function generate_html_end()
{
    echo "</div></body></html>\n";
    OpenEMR\Common\Session\SessionUtil::coreSessionDestroy();
    return 0;
}

if (isset($_POST['new_login_session_management'])) {
///////////////////////////////////////////////////////////////////////
// Begin code to support U2F and APP Based TOTP logic.
///////////////////////////////////////////////////////////////////////
    $errormsg = '';
    $regs = array();          // for mapping device handles to their names
    $registrations = array(); // the array of stored registration objects
    $res1 = sqlStatement(
        "SELECT a.name, a.method, a.var1 FROM login_mfa_registrations AS a " .
        "WHERE a.user_id = ? AND (a.method = 'TOTP' OR a.method = 'U2F') ORDER BY a.name",
        array($_SESSION['authId'])
    );

    $registrationAttempt = false;
    $isU2F = false;
    $isTOTP = false;
    while ($row1 = sqlFetchArray($res1)) {
        $registrationAttempt = true;
        if ($row1['method'] == 'U2F') {
            $isU2F = true;
            $regobj = json_decode($row1['var1']);
            $regs[json_encode($regobj->keyHandle)] = $row1['name'];
            $registrations[] = $regobj;
        } else { // $row1['method'] == 'TOTP'
            $isTOTP = true;
        }
    }

    if ($registrationAttempt) {
        $requests = '';
        $errortype = '';
        if ($isU2F) {
            // There is at least one U2F key registered so we have to request or verify key data.
            // https is required, and with a proxy the server might not see it.
            $scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
            $appId = $scheme . $_SERVER['HTTP_HOST'];
            $u2f = new u2flib_server\U2F($appId);
        }
        $userid = $_SESSION['authId'];
        $form_response = empty($_POST['form_response']) ? '' : $_POST['form_response'];
        if ($form_response) {
            // TOTP METHOD enabled if TOTP is visible in post request
            if (isset($_POST['totp']) && trim($_POST['totp']) != "" && $isTOTP) {
                $errormsg = false;

                $form_response = '';

                $res1 = sqlQuery(
                    "SELECT a.var1 FROM login_mfa_registrations AS a WHERE a.user_id = ? AND a.method = 'TOTP'",
                    array($_SESSION['authId'])
                );
                $registrationSecret = false;
                if (!empty($res1['var1'])) {
                    $registrationSecret = $res1['var1'];
                }

                // Decrypt the secret
                // First, try standard method that uses standard key
                $cryptoGen = new CryptoGen();
                $secret = $cryptoGen->decryptStandard($registrationSecret);
                if (empty($secret)) {
                    // Second, try the password hash, which was setup during install and is temporary
                    $passwordResults = privQuery(
                        "SELECT password FROM users_secure WHERE username = ?",
                        array($_POST["authUser"])
                    );
                    if (!empty($passwordResults["password"])) {
                        $secret = $cryptoGen->decryptStandard($registrationSecret, $passwordResults["password"]);
                        if (!empty($secret)) {
                            error_log("Disregard the decryption failed authentication error reported above this line; it is not an error.");
                            // Re-encrypt with the more secure standard key
                            $secretEncrypt = $cryptoGen->encryptStandard($secret);
                            privStatement(
                                "UPDATE login_mfa_registrations SET var1 = ? where user_id = ? AND method = 'TOTP'",
                                array($secretEncrypt, $userid)
                            );
                        }
                    }
                }

                if (!empty($secret)) {
                    $googleAuth = new Totp($secret);
                    $form_response = $googleAuth->validateCode($_POST['totp']);
                }

                if ($form_response) {
                    // Keep track of when challenges were last answered correctly.
                    privStatement(
                        "UPDATE users_secure SET last_challenge_response = NOW() WHERE id = ?",
                        array($_SESSION['authId'])
                    );
                } else {
                    $errormsg = xl("The code you entered was not valid");
                    $errortype = "TOTP";
                }
            } elseif ($isU2F) { // Otherwise use U2F METHOD
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
                        error_log("Unexpected keyHandle returned from doAuthenticate(): '" . errorLogEscape($strhandle) . "'");
                    }
                    // Keep track of when challenges were last answered correctly.
                    sqlStatement(
                        "UPDATE users_secure SET last_challenge_response = NOW() WHERE id = ?",
                        array($_SESSION['authId'])
                    );
                } catch (u2flib_server\Error $e) {
                    // Authentication failed so we will build the U2F form again.
                    $form_response = '';
                    $errormsg = xl('U2F Key Authentication error') . ": " . $e->getMessage();
                    $errortype = "U2F";
                }
            } else {
                // do nothing
                $form_response = '';
            }
        }
        if (!$form_response) {
            generate_html_start();
            if ($isU2F) {
                generate_html_u2f();
            }
            if ($isTOTP) {
                input_focus();
            }
            generate_html_top();
            if ($isTOTP) {
                echo '<div class="container">';
                echo '<div class="row">';
                echo '    <div class="col-sm-12">';
                echo '        <div class="page-header">';
                echo '            <h2>' . xlt('TOTP Verification') . '</h2>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
                if ($errormsg && $errortype == "TOTP") {
                    echo '<div class="row"><div class="col-sm-12"><div class="alert alert-danger alert-msg">' . text($errormsg) . '</div></div></div>';
                }

                echo '<div class="row">';
                echo '  <div class="col-sm-12">';
                echo '      <form method="post" action="main_screen.php?auth=login&site=' . attr_url($_GET['site']) . '" target="_top" name="challenge_form" id=="challenge_form">';
                echo '              <fieldset>';
                echo '                  <legend>' . xlt('Provide TOTP code') . '</legend>';
                echo '                  <div class="form-group">';
                echo '                      <div class="col-sm-6 col-sm-offset-3">';
                echo '                          <label for="totp">' . xlt('Enter the code from your authentication application on your device') . ':</label>';
                echo '                          <input type="text" name="totp" class="form-control input-lg" id="totp" maxlength="12" required>';
                echo '                          <input type="hidden" name="form_response" value="true" />';
                generate_html_middle();
                echo '                  </div>';
                echo '              </fieldset>';
                echo '                  <div class="form-group clearfix">';
                echo '                      <div class="col-sm-12 text-left position-override">';
                echo '                          <button type="submit" class="btn btn-default btn-save">' . xlt('Authenticate TOTP') . '</button>';
                echo '                  </div>';
                echo '              </div>';
                echo '          </div>';
                echo '      </form>';
                echo '  </div>';
                echo '</div>';
            }
            if ($isU2F) {
                // There is no key data yet or authentication failed, so we need to solicit it.
                $requests = json_encode($u2f->getAuthenticateData($registrations));
                // Persist the challenge also in the database because the browser is untrusted.
                sqlStatement(
                    "UPDATE users_secure SET login_work_area = ? WHERE id = ?",
                    array($requests, $userid)
                );

                echo '<div class="container">';
                echo '<div class="row">';
                echo '    <div class="col-sm-12">';
                echo '        <div class="page-header">';
                echo '            <h2>' . xlt('U2F Key Verification') . '</h2>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
                if ($errormsg && $errortype == "U2F") {
                    echo '<div class="row"><div class="col-sm-12"><div class="alert alert-danger  alert-msg">' . text($errormsg) . '</div></div></div>';
                }
                echo '<div class="row">';
                echo '  <div class="col-sm-12">';
                echo '          <form method="post" name="u2fform" id="u2fform" action="main_screen.php?auth=login&site=' . attr_url($_GET['site']) . '" target="_top">';
                echo '              <fieldset>';
                echo '                  <legend>' . xlt('Insert U2F Key') . '</legend>';
                echo '                  <div class="form-group">';
                echo '                      <div class="col-sm-6 col-sm-offset-3">';
                echo '                          <ul>';
                echo '                              <li>' . xlt('Insert your key into a USB port and click the Authenticate button below.') . '</li>';
                echo '                              <li>' . xlt('Then press the flashing button on your key within 1 minute.') . '</li>';
                echo '                          </ul>';
                echo '                  </div>';
                echo '              </fieldset>';
                echo '                  <div class="form-group clearfix">';
                echo '                      <div class="col-sm-12 text-left position-override">';
                echo '                          <button type="button"  id="authutf" class="btn btn-default btn-save" onclick="doAuth()">' . xlt('Authenticate U2F') . '</button>';
                echo '                          <input type="hidden" name="form_requests" value="' . attr($requests) . '" />';
                echo '                          <input type="hidden" name="form_response" value="" />';
                generate_html_middle();
                echo '                      </div>';
                echo '                  </div>';
                echo '          </form>';
                echo '  </div>';
                echo '</div>';
            }
            exit(generate_html_end());
        }
    }
    ///////////////////////////////////////////////////////////////////////
    // End of U2F and APP Based TOTP logic.
    ///////////////////////////////////////////////////////////////////////


    // Creates a new session id when load this outer frame
    // (allows creations of separate OpenEMR frames to view patients concurrently
    //  on different browser frame/windows)
    // This session id is used below in the restoreSession.php include to create a
    // session cookie for this specific OpenEMR instance that is then maintained
    // within the OpenEMR instance by calling top.restoreSession() whenever
    // refreshing or starting a new script.

    // This is a new login, so create a new session id and remove the old session
    session_regenerate_id(true);
} else {
    // This is not a new login, so check csrf and then create a new session id and do NOT remove the old session
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    session_regenerate_id(false);
}
// Set up the csrf private_key
//  Note this key always remains private and never leaves server session. It is used to create
//  the csrf tokens.
CsrfUtils::setupCsrfKey();

$_SESSION["encounter"] = '';

if ($GLOBALS['login_into_facility']) {
    $facility_id = $_POST['facility'];
    if ($facility_id === 'user_default') {
        //get the default facility of login user from users table
        $facilityService = new FacilityService();
        $facility = $facilityService->getFacilityForUser($_SESSION['authUserID']);
        $facility_id = $facility['id'];
    }
    $_SESSION['facilityId'] = $facility_id;
    if ($GLOBALS['set_facility_cookie']) {
        // set cookie with facility for the calender screens
        setcookie("pc_facility", $_SESSION['facilityId'], time() + (3600 * 365), $GLOBALS['webroot']);
    }
}

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
    $frame1url = "pwd_expires_alert.php?csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken());
    $frame1target = "adm";
} elseif (!empty($_POST['patientID'])) {
    $patientID = 0 + $_POST['patientID'];
    if (empty($_POST['encounterID'])) {
        // Open patient summary screen (without a specific encounter)
        $frame1url = "../patient_file/summary/demographics.php?set_pid=" . attr_url($patientID);
        $frame1target = "pat";
    } else {
        // Open patient summary screen with a specific encounter
        $encounterID = 0 + $_POST['encounterID'];
        $frame1url = "../patient_file/summary/demographics.php?set_pid=" . attr_url($patientID) . "&set_encounterid=" . attr_url($encounterID);
        $frame1target = "pat";
    }
} elseif (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
    $frame1url = "calendar/index.php?pid=" . attr_url($_GET['pid']);
    if (isset($_GET['date'])) {
        $frame1url .= "&date=" . attr_url($_GET['date']);
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
    $_SESSION['token_main_php'] = RandomGenUtils::createUniqueToken();
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
// some globals to access using top.variable
var userDebug = <?php echo js_escape($GLOBALS['user_debug']); ?>;
var webroot_url = <?php echo js_escape($web_root); ?>;
var jsLanguageDirection = <?php echo js_escape($_SESSION['language_direction']); ?>;

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
