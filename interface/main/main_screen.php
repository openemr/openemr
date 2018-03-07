<?php
/**
 * The outside frame that holds all of the OpenEMR User Interface.
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




/* Include our required headers */
require_once('../globals.php');

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
  // This is not a new login, so create a new session id and do NOT remove the old session
    session_regenerate_id(false);
}

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
    $frame1url = "pwd_expires_alert.php";
    $frame1target = "adm";
} else if (!empty($_POST['patientID'])) {
    $patientID = 0 + $_POST['patientID'];
    if (empty($_POST['encounterID'])) {
        // Open patient summary screen (without a specific encounter)
        $frame1url = "../patient_file/summary/demographics.php?set_pid=".attr($patientID);
        $frame1target = "pat";
    } else {
        // Open patient summary screen with a specific encounter
        $encounterID = 0 + $_POST['encounterID'];
        $frame1url = "../patient_file/summary/demographics.php?set_pid=".attr($patientID)."&set_encounterid=".attr($encounterID);
        $frame1target = "pat";
    }
} else if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
    $frame1url = "calendar/index.php?pid=" . attr($_GET['pid']);
    if (isset($_GET['date'])) {
        $frame1url .= "&date=" . attr($_GET['date']);
    }

    $frame1target = "cal";
} else {
    // standard layout
    $map_paths_to_targets = array(
        'main_info.php' => ('cal'),
        '../new/new.php' => ('pat'),
        '../../interface/main/finder/dynamic_finder.php' => ('pat'),
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

    header('Location: '.$web_root."/interface/main/tabs/main.php");
    exit();
}

?>
<html>
<head>
<title>
<?php echo text($openemr_name) ?>
</title>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script>
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

    <?php }?>

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
