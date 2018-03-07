<?php
use Esign\Api;
use OpenEMR\Core\Header;

/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




/* Include our required headers */
require_once('../../globals.php');
require_once $GLOBALS['srcdir'].'/ESign/Api.php';

$esignApi = new Api();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo text($openemr_name); ?></title>

<script type="text/javascript">
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// Since this should be the parent window, this is to prevent calls to the
// window that opened this window. For example when a new window is opened
// from the Patient Flow Board or the Patient Finder.
window.opener = null;

// This flag indicates if another window or frame is trying to reload the login
// page to this top-level window.  It is set by javascript returned by auth.inc
// and is checked by handlers of beforeunload events.
var timed_out = false;

//  Include this variable for backward compatibility
var loadedFrameCount = 0;
var tab_mode=true;
function allFramesLoaded() {
// Stub function for backward compatibility with frame race condition mechanism
 return true;
}

function goRepeaterServices(){
    top.restoreSession();
    // Ensure send the skip_timeout_reset parameter to not count this as a manual entry in the
    //  timing out mechanism in OpenEMR.

    // Send the skip_timeout_reset parameter to not count this as a manual entry in the
    //  timing out mechanism in OpenEMR.
    $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/dated_reminders_counter.php",
        { skip_timeout_reset: "1" },
        function(data) {
            // Go knockout.js
            app_view_model.application_data.user().messages(data);
        }
    );

    // run background-services
    $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/execute_background_services.php",
        { skip_timeout_reset: "1", ajax: "1" }
    );

    // auto run this function every 60 seconds
    var repeater = setTimeout("goRepeaterServices()", 60000);
}

function isEncounterLocked( encounterId ) {
    <?php if ($esignApi->lockEncounters()) { ?>
    // If encounter locking is enabled, make a syncronous call (async=false) to check the
    // DB to see if the encounter is locked.
    // Call restore session, just in case
    top.restoreSession();
    $.ajax({
        type: 'POST',
        url: '<?php echo $GLOBALS['webroot']?>/interface/esign/index.php?module=encounter&method=esign_is_encounter_locked',
        data: { encounterId : encounterId },
        success: function( data ) {
            encounter_locked = data;
        },
        dataType: 'json',
        async:false
    });
    return encounter_locked;
    <?php } else { ?>
    // If encounter locking isn't enabled then always return false
    return false;
    <?php } ?>
}
var webroot_url="<?php echo $web_root; ?>";
var jsLanguageDirection = "<?php echo $_SESSION["language_direction"]; ?>";
</script>

<?php Header::setupHeader(["knockout","tabs-theme",'jquery-ui']); ?>


<link rel="shortcut icon" href="<?php echo $GLOBALS['images_static_relative']; ?>/favicon.ico" />

<script type="text/javascript" src="js/custom_bindings.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/user_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/patient_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/therapy_group_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>

<script type="text/javascript">
// Create translations to be used in the menuActionClick() function in below js/tabs_view_model.js script
var xl_strings_tabs_view_model = <?php echo json_encode(array(
    'encounter_locked' => xla('This encounter is locked. No new forms can be added.'),
    'must_select_patient'  => $GLOBALS['enable_group_therapy'] ? xla('You must first select or add a patient or therapy group.') : xla('You must first select or add a patient.'),
    'must_select_encounter'    => xla('You must first select or create an encounter.')
));
?>;
</script>
<script type="text/javascript" src="js/tabs_view_model.js?v=<?php echo $v_js_includes; ?>"></script>

<script type="text/javascript" src="js/application_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/frame_proxies.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>

<?php
// Below code block is to prepare certain elements for deciding what links to show on the menu
//
// prepare newcrop globals that are used in creating the menu
if ($GLOBALS['erx_enable']) {
    $newcrop_user_role_sql = sqlQuery("SELECT `newcrop_user_role` FROM `users` WHERE `username` = ?", array($_SESSION['authUser']));
    $GLOBALS['newcrop_user_role'] = $newcrop_user_role_sql['newcrop_user_role'];
    if ($GLOBALS['newcrop_user_role'] === 'erxadmin') {
        $GLOBALS['newcrop_user_role_erxadmin'] = 1;
    }
}

// prepare track anything to be used in creating the menu
$track_anything_sql = sqlQuery("SELECT `state` FROM `registry` WHERE `directory` = 'track_anything'");
$GLOBALS['track_anything_state'] = $track_anything_sql['state'];
// prepare Issues popup link global that is used in creating the menu
$GLOBALS['allow_issue_menu_link'] = ((acl_check('encounters', 'notes', '', 'write') || acl_check('encounters', 'notes_a', '', 'write')) &&
  acl_check('patients', 'med', '', 'write'));
?>

<?php require_once("templates/tabs_template.php"); ?>
<?php require_once("templates/menu_template.php"); ?>
<?php require_once("templates/patient_data_template.php"); ?>
<?php require_once("templates/therapy_group_template.php"); ?>
<?php require_once("templates/user_data_template.php"); ?>
<?php require_once("menu/menu_json.php"); ?>
<?php $userQuery = sqlQuery("select * from users where username = ?", array($_SESSION['authUser'])); ?>
<script type="text/javascript">
    <?php if (!empty($_SESSION['frame1url']) && !empty($_SESSION['frame1target'])) { ?>
        app_view_model.application_data.tabs.tabsList()[0].url(<?php echo json_encode("../".$_SESSION['frame1url']); ?>);
        app_view_model.application_data.tabs.tabsList()[0].name(<?php echo json_encode($_SESSION['frame1target']); ?>);
    <?php } ?>
    <?php unset($_SESSION['frame1url']); ?>
    <?php unset($_SESSION['frame1target']); ?>
    <?php if (!empty($_SESSION['frame2url']) && !empty($_SESSION['frame2target'])) { ?>
    app_view_model.application_data.tabs.tabsList()[1].url(<?php echo json_encode("../".$_SESSION['frame2url']); ?>);
    app_view_model.application_data.tabs.tabsList()[1].name(<?php echo json_encode($_SESSION['frame2target']); ?>);
    <?php } ?>
    <?php unset($_SESSION['frame2url']); ?>
    <?php unset($_SESSION['frame2target']); ?>

    app_view_model.application_data.user(new user_data_view_model(<?php echo json_encode($_SESSION{"authUser"})
        .',' . json_encode($userQuery['fname'])
        .',' . json_encode($userQuery['lname'])
        .',' . json_encode($_SESSION['authGroup']); ?>));

</script>

</head>
<body>
<!-- Below iframe is to support auto logout when timeout is reached -->
<iframe name="timeout" style="visibility:hidden; position:absolute; left:0; top:0; height:0; width:0; border:none;" src="timeout_iframe.php"></iframe>
<?php // mdsupport - app settings
    $disp_mainBox = '';
if (isset($_SESSION['app1'])) {
    $rs = sqlquery(
        "SELECT title app_url FROM list_options WHERE activity=1 AND list_id=? AND option_id=?",
        array('apps', $_SESSION['app1'])
    );
    if ($rs['app_url'] != "main/main_screen.php") {
        echo '<iframe name="app1" src="../../'.attr($rs['app_url']).'"
    			style="position:absolute; left:0; top:0; height:100%; width:100%; border:none;" />';
        $disp_mainBox = 'style="display: none;"';
    }
}
?>
<div id="mainBox" <?php echo $disp_mainBox ?>>
    <div id="dialogDiv"></div>
    <div class="body_top">
        <a href="http://www.open-emr.org" title="OpenEMR <?php echo xla("Website"); ?>" target="_blank"><img class="logo" alt="openEMR small logo"  border="0" src="<?php echo $GLOBALS['images_static_relative']; ?>/menu-logo.png"></a>
        <span id="menu logo" data-bind="template: {name: 'menu-template', data: application_data} "></span>
        <span id="userData" data-bind="template: {name: 'user-data-template', data:application_data} "></span>
    </div>
    <div id="attendantData" class="body_title acck" data-bind="template: {name: app_view_model.attendant_template_type, data: application_data} ">
    </div>
    <div class="body_title" data-bind="template: {name: 'tabs-controls', data: application_data} "> </div>

    <div class="mainFrames">
        <div id="framesDisplay" data-bind="template: {name: 'tabs-frames', data: application_data}"> </div>
    </div>
</div>
<script>
    $("#dialogDiv").hide();
    ko.applyBindings(app_view_model);

    $(document).ready(function() {
        $('.dropdown-toggle').dropdown();
        goRepeaterServices();
        $('#patient_caret').click(function() {
           $('#attendantData').slideToggle();
            $('#patient_caret').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
        });
    });
</script>
</body>
</html>
