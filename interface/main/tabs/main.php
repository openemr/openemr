<?php
Use Esign\Api;
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

$fake_register_globals=false;
$sanitize_all_escapes=true;

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

    // TODO: placeholder for checking the messages and displaying them

    // run background-services
    $.post("<?php echo $GLOBALS['webroot']; ?>/library/ajax/execute_background_services.php",
        { skip_timeout_reset: "1", ajax: "1" }
    );

    // auto run this function every 60 seconds
    var repeater = setTimeout("goRepeaterServices()", 60000);
}

function isEncounterLocked( encounterId ) {
    <?php if ( $esignApi->lockEncounters() ) { ?>
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
</script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>/interface/themes/<?php echo $GLOBALS['theme_tabs_layout']; ?>?v=<?php echo $v_js_includes; ?>"/>
<link rel="shortcut icon" href="<?php echo $webroot; ?>/interface/pic/favicon.ico" />
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/knockout-3-4-0/dist/knockout.js"></script>
<script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-2-2-0/index.js"></script>

<script type="text/javascript" src="js/custom_bindings.js?v=<?php echo $v_js_includes; ?>"></script>

<script type="text/javascript" src="js/user_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/patient_data_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/tabs_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/application_view_model.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/frame_proxies.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>

<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
    
<?php require_once("templates/tabs_template.php"); ?>
<?php require_once("templates/menu_template.php"); ?>
<?php require_once("templates/patient_data_template.php"); ?>
<?php require_once("templates/user_data_template.php"); ?>
<?php require_once("menu/menu_json.php"); ?>
<?php $userQuery = sqlQuery("select * from users where username = ?", array($_SESSION['authUser'])); ?>
<script type="text/javascript">
    <?php if(isset($_REQUEST['url']))
        {
        ?>
            app_view_model.application_data.tabs.tabsList()[0].url(<?php echo json_encode("../".urldecode($_REQUEST['url'])); ?>);
        <?php 
        }
    ?>
    app_view_model.application_data.user(new user_data_view_model(<?php echo json_encode($_SESSION{"authUser"})
                                                                  .',' . json_encode($userQuery['fname'])
                                                                  .',' . json_encode($userQuery['lname'])
                                                                  .',' . json_encode($_SESSION['authGroup']); ?>));
</script>

</head>
<body>
<div id="mainBox">
    <div id="dialogDiv"></div>
    <div class="body_top">
        <a href="http://www.open-emr.org" title="<?php echo xla("OpenEMR Website"); ?>" target="_blank"><img class="logo" alt="openEMR small logo" style="float: left; margin:3px 4px 0px 10px;width:20px;z-index:10000;" border="0" src="<?php echo $webroot; ?>/interface/pic/favicon.ico"></a>
        <span id="menu logo" data-bind="template: {name: 'menu-template', data: application_data} "></span>
        <span id="userData" data-bind="template: {name: 'user-data-template', data:application_data} "></span>
    </div>
    <div id="patientData" class="body_title acck" data-bind="template: {name: 'patient-data-template', data: application_data} ">
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
        goRepeaterServices();
        $('#patient_caret').click(function() {
           $('#patientData').slideToggle();
            $('#patient_caret').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
        });
    });
</script>
</body>
</html>
