<?php
/**
 * Message and Reminder Center UI
 *
 * @Package OpenEMR
 * @link http://www.open-emr.org
 * @author OpenEMR Support LLC
 * @author Roberto Vasquez robertogagliotta@gmail.com
 * @author Rod Roark rod@sunsetsystems.com
 * @author Brady Miller brady.g.miller@gmail.com
 * @author Ray Magauran magauran@medfetch.com
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @copyright Copyright (c) 2017 MedEXBank.com
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/gprelations.inc.php");
require_once "$srcdir/user.inc";
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

//validation library
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
//Gets validation rules from Page Validation list.
$collectthis = collectValidationPageRules("/interface/main/messages/messages.php");
if (empty($collectthis)) {
    $collectthis = "{}";
} else {
    $collectthis = json_sanitize($collectthis[array_keys($collectthis)[0]]["rules"]);
}

$MedEx = new MedExApi\MedEx('MedExBank.com');

if ($GLOBALS['medex_enable'] == '1') {
    if ($_REQUEST['SMS_bot']) {
        $result = $MedEx->login('1');
        $MedEx->display->SMS_bot($result);
        exit();
    }
    $logged_in = $MedEx->login();
}

$setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
//use $uspfx as the first variable for page/script specific user settings instead of '' (which is like a global but you have to request it).
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$rcb_selectors = prevSetting($uspfx, 'rcb_selectors', 'rcb_selectors', 'block');
$rcb_facility = prevSetting($uspfx, 'form_facility', 'form_facility', '');
$rcb_provider = prevSetting($uspfx, 'form_provider', 'form_provider', $_SESSION['authUserID']);

if (($_POST['setting_bootstrap_submenu']) ||
    ($_POST['rcb_selectors'])) {
    // These are not form elements. We only ever change them via ajax, so exit now.
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet"  href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">

    <?php Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
    <script>
        var xljs1 = '<?php echo xla('Preferences updated successfully'); ?>';
        var format_date_moment_js = '<?php echo attr(DateFormatRead("validateJS")); ?>';
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>

    <link rel="shortcut icon" href="<?php echo $webroot; ?>/sites/default/favicon.ico" />

        <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="MedEx Bank">
    <meta name="author" content="OpenEMR: MedExBank">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left!important;
            }
            .navbar-toggle>span.icon-bar {
                background-color: #68171A ! important;
            }
            .navbar-default .navbar-toggle {
                border-color: #4a4a4a;
            }
            .navbar-default .navbar-toggle:focus, .navbar-default .navbar-toggle:hover {
                background-color: #f2f2f2 !important;
                font-weight: 900 !important;
                color: #000000 !important;
            }
            .navbar-color {
                background-color: #E5E5E5;
            }
            .icon-bar {
                background-color: #68171A;
            }
            .navbar-header {
                float: none;
            }
            .navbar-toggle {
                display: block;
                background-color: #f2f2f2;
            }
            .navbar-nav {
                float: none!important;
            }
            .navbar-nav>li {
                float: none;
            }
            .navbar-collapse.collapse.in {
                z-index: 100;
                background-color: #dfdfdf;
                font-weight: 700;
                color: #000000 !important;
            }

        }

        </style>

<?php
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    $MedEx->display->navigation($logged_in);
    echo "<br />";
}

if (!empty($_REQUEST['go'])) { ?>
    <?php
    if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
        echo "<title>" . xlt('MedEx Setup') . "</title></head><body class='body_top'>";
        $stage = $_REQUEST['stage'];
        if (!is_numeric($stage)) {
            echo "<br /><span class='title'>$stage " . xlt('Warning') . ": " . xlt('This is not a valid request') . ".</span>";
        } else {
            $MedEx->setup->MedExBank($stage);
        }
    } elseif ($_REQUEST['go'] == "addRecall") {
        echo "<title>" . xlt('New Recall') . "</title></head><body class='body_top'>";
        $MedEx->display->display_add_recall();
    } elseif ($_REQUEST['go'] == 'Recalls') {
        echo "<title>" . xlt('Recall Board') . "</title></head><body class='body_top'>";
        $MedEx->display->display_recalls($logged_in);
    } elseif ((($_REQUEST['go'] == "setup") || ($_REQUEST['go'] == 'Preferences')) && ($logged_in)) {
        echo "<title>MedEx" . xlt('Preferences') . "</title></head><body class='body_top'>";
        $MedEx->display->preferences();
    } elseif ($_REQUEST['go'] == 'icons') {
        echo "<title>MedEx" . xlt('Icons') . "</title></head><body class='body_top'>";
        $MedEx->display->icon_template();
    } elseif ($_REQUEST['go'] == 'SMS_bot') {
        echo "<title>MedEx" . xlt('SMS') . "</title></head><body class='body_top'>";
        $MedEx->display->SMS_bot($logged_in);
        exit;
    } else {
        echo "<title>" . xlt('MedEx Setup') . "</title></head><body class='body_top'>";
        echo xlt('Warning: Navigation error. Please refresh this page.');
    }
} else {
    //original message.php stuff
    
    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 0) {
         $help_icon = '';
    }
    $heading_caption = xlt('Messages') . ', ' . xlt('Reminders');
    if ($GLOBALS['disable_rcb'] != '1') {
        $heading_caption .= ', ' . xlt('Recalls');
    }
    
    $arrOeUiSettings = array(
        'heading_title' => $heading_caption,
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => false,
        'expandable_files' => array(""),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => true,
        'help_file_name' => "message_center_help.php"
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);

    echo "<title>" . xlt('Message Center') . "</title>
    </head>
    <body class='body_top'>";
    ?>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="col-sm-12">
                <nav class="navbar navbar-default navbar-color navbar-static-top" >
                    <div class="container-fluid">
                        <div class="navbar-header">
                            <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span></button>
                        </div>
                        <div class="collapse navbar-collapse" id="myNavbar" >
                            <ul class="nav navbar-nav" >
                                <li class="active oe-bold-black" id='li-mess'>
                                    <a href='#'  style="font-weight:700; color:#000000" id='messages-li'><?php echo xlt('Messages'); ?></a>
                                </li>
                                <li class="oe-bold-black" id='li-remi' >
                                    <a href='#' id='reminders-li' style="font-weight:700; color:#000000"><?php echo xlt('Reminders'); ?></a>
                                </li>
                                <?php if ($GLOBALS['disable_rcb'] != '1') { ?>
                                <li class="oe-bold-black" id='li-reca'>
                                    <a href='#' id='recalls-li' style="font-weight:700; color:#000000"><?php echo xlt('Recalls'); ?></a>
                                </li>
                                <?php }?>
                                <?php if ($logged_in) { ?>
                                <li class="oe-bold-black" id='li-sms'>
                                    <a href='#' id='sms-li' style="font-weight:700; color:#000000"><?php echo xlt('SMS Zone'); ?></a>
                                </li>
                                <?php }?>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row" id="messages-div">
            <div class="col-sm-12">
                <fieldset>
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <?php
                        // Check to see if the user has Admin rights, and if so, allow access to See All.
                        $showall = isset($_GET['show_all']) ? $_GET['show_all'] : "";
                        if ($showall == "yes") {
                            $show_all = $showall;
                        } else {
                            $show_all = "no";
                        }
                        // Collect active variable and applicable html code for links
                        $form_active = (isset($_REQUEST['form_active']) ? $_REQUEST['form_active'] : false);
                        $form_inactive = (isset($_REQUEST['form_inactive']) ? $_REQUEST['form_inactive'] : false);
                        if ($form_active) {
                            $active = '1';
                            $activity_string_html = 'form_active=1';
                        } elseif ($form_inactive) {
                            $active = '0';
                            $activity_string_html = 'form_inactive=1';
                        } else {
                            $active = 'all';
                            $activity_string_html = '';
                        }
                        //collect the task setting
                        $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : "";
                        if (acl_check('admin', 'super')) {
                            if ($show_all == 'yes') {
                                $showall = "yes";
                                $lnkvar = "\"messages.php?show_all=no&$activity_string_html\" name='Just Mine' onclick=\"top.restoreSession()\"><i id='just-mine-tooltip' class='fa fa-user fa-lg' aria-hidden='true'></i>";
                                $messages = xl('All Messages');
                            } else {
                                $showall = "no";
                                $lnkvar = "\"messages.php?show_all=yes&$activity_string_html\" name='See All' onclick=\"top.restoreSession()\"><i id='see-all-tooltip' class='fa fa-users fa-lg' aria-hidden='true'></i>";
                                $messages = xl('My Messages');
                            }
                        } else {
                            $messages = xlt('My Messages');
                        }
                        ?>
                        <div class="oe-margin-b-20">
                            <span class="title"><?php echo text($messages); ?></span>
                            <a class='more' href=<?php echo $lnkvar; ?></a>
                        </div>
                        <div class="oe-margin-b-10">
                            <?php
                            //show the activity links
                            if (empty($task) || $task == "add" || $task == "delete") { ?>
                                    <?php if ($active == "all") { ?>
                                <span><strong><?php echo xlt('All Messages'); ?></strong></span>
                                    <?php } else { ?>
                                <a href="messages.php" class="link btn btn-default"
                                   onclick="top.restoreSession()"><span><?php echo xlt('Show All'); ?></span></a>
                                    <?php } ?>
                                    |
                                    <?php if ($active == '1') { ?>
                                <span><strong><?php echo xlt('Active Messages'); ?></strong></span>
                                    <?php } else { ?>
                                <a href="messages.php?form_active=1" class="link btn btn-default"
                                   onclick="top.restoreSession()"><span><?php echo xlt('Show Active'); ?></span></a>
                                    <?php } ?>
                                    |
                                    <?php if ($active == '0') { ?>
                                <span><strong><?php echo xlt('Inactive Messages'); ?></strong></span>
                                    <?php } else { ?>
                                <a href="messages.php?form_inactive=1" class="link btn btn-default"
                                   onclick="top.restoreSession()"><span><?php echo xlt('Show Inactive'); ?></span></a>
                                    <?php } ?>
                            <?php } ?>
                        </div>
                        <?php
                        switch ($task) {
                            case "add":
                                // Add a new message for a specific patient; the message is documented in Patient Notes.
                                // Add a new message; it's treated as a new note in Patient Notes.
                                $note = $_POST['note'];
                                $noteid = $_POST['noteid'];
                                $form_note_type = $_POST['form_note_type'];
                                $form_message_status = $_POST['form_message_status'];
                                $reply_to = explode(';', rtrim($_POST['reply_to'], ';'));
                                $assigned_to_list = explode(';', $_POST['assigned_to']);
                                $datetime = isset($_POST['form_datetime']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_datetime']) : '';
                                foreach ($assigned_to_list as $assigned_to) {
                                    if ($noteid && $assigned_to != '-patient-') {
                                        updatePnote($noteid, $note, $form_note_type, $assigned_to, $form_message_status, $datetime);
                                        $noteid = '';
                                    } else {
                                        if ($noteid && $assigned_to == '-patient-') {
                                            // When $assigned_to == '-patient-' we don't update the current note, but
                                            // instead create a new one with the current note's body prepended and
                                            // attributed to the patient.  This seems to be all for the patient portal.
                                            $row = getPnoteById($noteid);
                                            if (!$row) {
                                                die("getPnoteById() did not find id '" . text($noteid) . "'");
                                            }
                                            $pres = sqlQuery("SELECT lname, fname " .
                                                "FROM patient_data WHERE pid = ?", array($reply_to[0]));
                                            $patientname = $pres['lname'] . ", " . $pres['fname'];
                                            $note .= "\n\n$patientname on " . $row['date'] . " wrote:\n\n";
                                            $note .= $row['body'];
                                        }
                                        // There's no note ID, and/or it's assigned to the patient.
                                        // In these cases a new note is created.
                                        foreach ($reply_to as $patient) {
                                            addPnote($patient, $note, $userauthorized, '1', $form_note_type, $assigned_to, $datetime, $form_message_status);
                                        }
                                    }
                                }
                                break;
                            case "savePatient":
                            case "save":
                                // Update alert.
                                $noteid = $_POST['noteid'];
                                $form_message_status = $_POST['form_message_status'];
                                $reply_to = $_POST['reply_to'];
                                if ($task == "save") {
                                    updatePnoteMessageStatus($noteid, $form_message_status);
                                } else {
                                    updatePnotePatient($noteid, $reply_to);
                                }
                                $task = "edit";
                                $note = $_POST['note'];
                                $title = $_POST['form_note_type'];
                                $reply_to = $_POST['reply_to'];
                                break;
                            case "edit":
                                if ($noteid == "") {
                                    $noteid = $_GET['noteid'];
                                }
                                // Update the message if it already exists; it's appended to an existing note in Patient Notes.
                                $result = getPnoteById($noteid);
                                if ($result) {
                                    if ($title == "") {
                                        $title = $result['title'];
                                    }
                                    $body = $result['body'];
                                    if ($reply_to == "") {
                                        $reply_to = $result['pid'];
                                    }
                                    $form_message_status = $result['message_status'];
                                    $datetime = $result['date'];
                                }
                                break;
                            case "delete":
                                // Delete selected message(s) from the Messages box (only).
                                $delete_id = $_POST['delete_id'];
                                for ($i = 0; $i < count($delete_id); $i++) {
                                    deletePnote($delete_id[$i]);
                                    EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "pnotes: id " . $delete_id[$i]);
                                }
                                break;
                        }
                        if ($task == "addnew" or $task == "edit") {
                            // Display the Messages page layout.
                            echo "<form name='form_patient' id='new_note'
                                    class='form-horizontal' 
                                    action=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&$activity_string_html\" 
                                    method='post'>
                                    <input type=hidden name=noteid id=noteid value='" . attr($noteid) . "'>
                                    <input type=hidden name=task id=task value=add>";
                            if ($task == "addnew") {
                                $message_legend = xl('Create New Message');
                                $onclick = "onclick=multi_sel_patient()";
                            } elseif ($task == "edit") {
                                $message_legend = xl('Add To Existing Message');
                                $onclick = "";
                            }

                            ?>
                            <div class='col-md-12'>
                                <fieldset>
                                <legend><?php echo text($message_legend); ?></legend>
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 oe-custom-line col-lg-offset-1">
                                            <div class="row">
                                                <div class="col-xs-3 col-sm-3">
                                                    <label class="control-label" for="form_note_type"><?php echo xlt('Type'); ?>:</label>
                                                    <?php
                                                    if ($title == "") {
                                                        $title = "Unassigned";
                                                    }
                                                    // Added 6/2009 by BM to incorporate the patient notes into the list_options listings.
                                                    generate_form_field(array('data_type' => 1, 'field_id' => 'note_type', 'list_id' => 'note_type', 'empty_title' => 'SKIP', 'order_by' => 'title', 'class' => 'form-control'), $title);
                                                    ?>
                                                </div>
                                                <div class="col-xs-3 col-sm-3">
                                                    <label class="control-label" for="form_message_status"><?php echo xlt('Status'); ?>:</label>
                                                    <?php
                                                    if ($form_message_status == "") {
                                                        $form_message_status = 'New';
                                                    }
                                                    generate_form_field(array('data_type' => 1, 'field_id' => 'message_status', 'list_id' => 'message_status', 'empty_title' => 'SKIP', 'order_by' => 'title', 'class' => 'form-control'), $form_message_status); ?>
                                                </div>
                                                <div class="col-xs-4">
                                                    <label class="control-label" for="form_patient">
                                                        <?php
                                                        if ($task != "addnew" && $result['pid'] != 0) { ?>
                                                            <a class="patLink"
                                                               onclick="goPid('<?php echo attr(addslashes($result['pid'])); ?>')"><?php echo xlt('Patient'); ?>
                                                                :</a>
                                                            <?php
                                                        } else { ?>
                                                            <b class='<?php echo($task == "addnew" ? "required" : "") ?>'><?php echo xlt('Patient'); ?>
                                                                :</b>
                                                            <?php
                                                        }
                                                        ?>
                                                    </label>
                                                    <?php
                                                    if ($reply_to) {
                                                        $prow = sqlQuery("SELECT lname, fname,pid, pubpid, DOB  " .
                                                            "FROM patient_data WHERE pid = ?", array($reply_to));
                                                        $patientname = $prow['lname'] . ", " . $prow['fname'];
                                                    }
                                                    if ($task == "addnew" || $result['pid']==0) {
                                                        $cursor = "oe-cursor-add";
                                                        $background = "oe-patient-background";
                                                    } elseif ($task == "edit") {
                                                        $cursor = "oe-cursor-stop";
                                                        $background = '';
                                                    }
                                                    ?>
                                                    <input type='text'  id='form_patient' name='form_patient' class='form-control <?php echo $cursor . " " .$background;?>' onclick="multi_sel_patient()" placeholder='<?php echo xla("Click to add patient"); ?>' value='<?php echo attr($patientname); ?>' readonly/>
                                                    <input type='hidden' class="form-control" name='reply_to' id='reply_to' value='<?php echo attr($reply_to); ?>'/>
                                                </div>
                                                <div class="col-xs-2">
                                                    <?php
                                                    if ($task=="addnew" || $result['pid']==0) {
                                                        echo "<label class='control-label oe-empty-label' for='clear_patients'></label>";
                                                        echo '<button type="button" id="clear_patients"  class="btn btn-default btn-undo pull-left flip" value="' . xla('Clear') .'">' . xlt("Clear") . '</button>';
                                                    } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 oe-custom-line col-lg-offset-1">
                                            <div class="row">
                                                <?php if ($GLOBALS['messages_due_date']) { ?>
                                                <div class="col-xs-6 col-sm-2">
                                                    <label class="control-label" for="form_note_type"><?php echo xlt('Due date'); ?>:</label>
                                                    <?php generate_form_field(array('data_type' => 4, 'field_id' => 'datetime', 'edit_options' => 'F'), empty($datetime) ? date('Y-m-d H:i') : $datetime) ?>
                                                </div>
                                                <?php } ?>
                                                <div class="col-xs-6 col-sm-4">
                                                    <label class="control-label" for="assigned_to_text"><?php echo xlt('To'); ?>:</label>
                                                    <input type='text' name='assigned_to_text' class='form-control oe-cursor-stop' id='assigned_to_text' readonly='readonly'
                                                        value='' placeholder='<?php echo xla("SELECT Users FROM The Dropdown LIST"); ?>'>
                                                    <input type='hidden' name='assigned_to' id='assigned_to'>
                                                </div>
                                                <div class="col-xs-6 col-sm-4">
                                                    <label class="control-label oe-empty-label" for="users"></label>
                                                    <select name='users' id='users' class='form-control' onchange='addtolist(this);'>
                                                        <?php
                                                        echo "<option value='--'";
                                                        echo ">" . xlt('Select User');
                                                        echo "</option>\n";
                                                        $ures = sqlStatement("SELECT username, fname, lname FROM users " .
                                                            "WHERE username != '' AND active = 1 AND " .
                                                            "( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
                                                            "ORDER BY lname, fname");
                                                        while ($urow = sqlFetchArray($ures)) {
                                                            echo "    <option value='" . attr($urow['username']) . "'";
                                                            echo ">" . text($urow['lname']);
                                                            if ($urow['fname']) {
                                                                echo ", " . text($urow['fname']);
                                                            }
                                                            echo "</option>\n";
                                                        }
                                                        if ($GLOBALS['portal_offsite_enable']) {
                                                            echo "<option value='-" . xla('patient') . "-'";
                                                            echo ">-" . xlt('Patient') . "-";
                                                            echo "</option>\n";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-xs-6 col-sm-2">
                                                    <label class="control-label oe-empty-label" for="users"></label>
                                                    <button type="button" name="clear_user" id="clear_user" class="btn btn-default btn-undo pull-left flip" value="<?php echo xla('Clear'); ?>"><?php echo xlt('Clear'); ?></button>
                                                </div>
                                            </div>
                                        <div class='col-xs-12 oe-margin-t-3'>
                                        <?php
                                        if ($noteid) {
                                            // Get the related document IDs if any.
                                            $tmp = sqlStatement(
                                                "SELECT id1 FROM gprelations WHERE " .
                                                "type1 = ? AND type2 = ? AND id2 = ?",
                                                array('1', '6', $noteid)
                                            );
                                            if (sqlNumRows($tmp)) {
                                                echo " <tr>\n";
                                                echo "  <td class='text'><b>";
                                                echo xlt('Linked document') . ":</b>\n";
                                                while ($gprow = sqlFetchArray($tmp)) {
                                                    $d = new Document($gprow['id1']);
                                                    $enc_list = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
                                                        " LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date DESC", array($prow['pid']));
                                                    $str_dob = xl("DOB") . ":" . $prow['DOB'] . " " . xl("Age") . ":" . getPatientAge($prow['DOB']);
                                                    $pname = $prow['fname'] . " " . $prow['lname'];
                                                    echo "<a href='javascript:void(0);' ";
                                                    echo "onClick=\"gotoReport(" . attr(addslashes($d->get_id())) . ",'" . attr(addslashes($pname)) . "'," . attr(addslashes($prow['pid'])) . "," . attr(addslashes($prow['pubpid'])) . ",'" . attr(addslashes($str_dob)) . "');\">";
                                                    echo text($d->get_url_file());
                                                    echo "</a>\n";
                                                }
                                                echo "  </td>\n";
                                                echo " </tr>\n";
                                            }
                                            // Get the related procedure order IDs if any.
                                            $tmp = sqlStatement(
                                                "SELECT id1 FROM gprelations WHERE " .
                                                "type1 = ? AND type2 = ? AND id2 = ?",
                                                array('2', '6', $noteid)
                                            );
                                            if (sqlNumRows($tmp)) {
                                                echo " <tr>\n";
                                                echo "  <td class='text'><b>";
                                                echo xlt('Linked procedure order') . ":</b>\n";
                                                while ($gprow = sqlFetchArray($tmp)) {
                                                    echo "   <a href='";
                                                    echo $GLOBALS['webroot'] . "/interface/orders/single_order_results.php?orderid=";
                                                    echo attr_url($gprow['id1']);
                                                    echo "' target='_blank' onclick='top.restoreSession()'>";
                                                    echo text($gprow['id1']);
                                                    echo "</a>\n";
                                                }
                                                echo "  </td>\n";
                                                echo " </tr>\n";
                                            }
                                        }
                                        ?>
                                    </div>
                                    </div>
                                    <div class="row">
                                        <div class='col-xs-12'>
                                            <?php

                                            if ($noteid) {
                                                $body = preg_replace('/(:\d{2}\s\()' . $result['pid'] . '(\sto\s)/', '${1}' . $patientname . '${2}', $body);
                                                $body = preg_replace('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}\s\([^)(]+\s)(to)(\s[^)(]+\))/', '${1}' . xl('to') . '${3}', $body);
                                                $body =nl2br(text(oeFormatPatientNote($body)));
                                                echo "<div class='text oe-margin-t-3' style='background-color:white; color: gray; border:1px solid #999; padding: 5px;'>" . $body . "</div>";
                                            }

                                            ?>
                                            <textarea name='note' id='note' class='form-control oe-margin-t-3'
                                                      style='margin-left:-1px !important; background-color:white; color: gray; border:1px solid #999; padding: 5px; height:100px!important;'><?php echo nl2br(text($note)); ?></textarea>
                                        </div>
                                        <div class="col-xs-12 position-override oe-margin-t-10">
                                            <?php if ($noteid) { ?>
                                                <!-- This is for displaying an existing note. -->
                                                <button type="button" class="btn btn-default btn-send-msg" id="newnote"
                                                        value="<?php echo xla('Send message'); ?>"><?php echo xlt('Send message'); ?></button>
                                                <button type="button" class="btn btn-default btn-print" id="printnote"
                                                        value="<?php echo xla('Print message'); ?>"><?php echo xlt('Print message'); ?></button>
                                                <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" id="cancel"
                                                        value="<?php echo xla('Cancel'); ?>"><?php echo xlt('Cancel'); ?></button>
                                            <?php } else { ?>
                                                <!-- This is for displaying a new note. -->
                                                <button type="button" class="btn btn-default btn-send-msg" id="newnote"
                                                        value="<?php echo xla('Send message'); ?>"><?php echo xlt('Send message'); ?></button>
                                                <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" id="cancel"
                                                        value="<?php echo xla('Cancel'); ?>"><?php echo xlt('Cancel'); ?></button>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            </form>
                            <?php
                        } else {
                            // This is for sorting the records.
                            $sort = array("users.lname", "patient_data.lname", "pnotes.title", "pnotes.date", "pnotes.message_status");
                            $sortby = (isset($_REQUEST['sortby']) && ($_REQUEST['sortby'] != "")) ? $_REQUEST['sortby'] : $sort[3];
                            $sortorder = (isset($_REQUEST['sortorder']) && ($_REQUEST['sortorder'] != "")) ? $_REQUEST['sortorder'] : "desc";
                            $begin = isset($_REQUEST['begin']) ? $_REQUEST['begin'] : 0;

                            for ($i = 0; $i < count($sort); $i++) {
                                $sortlink[$i] = "<a  class='arrowhead' href=\"messages.php?show_all=".attr($showall)."&sortby=".attr($sort[$i])."&sortorder=asc&$activity_string_html\" onclick=\"top.restoreSession()\" alt=\"" . xla('Sort Up') . "\"><i class='fa fa-sort-desc fa-lg' aria-hidden='true'></i></a>";
                            }
                            for ($i = 0; $i < count($sort); $i++) {
                                if ($sortby == $sort[$i]) {
                                    switch ($sortorder) {
                                        case "asc":
                                            $sortlink[$i] = "<a class='arrowhead' href=\"messages.php?show_all=".attr($showall)."&sortby=".attr($sortby)."&sortorder=desc&$activity_string_html\" onclick=\"top.restoreSession()\" alt=\"" . xla('Sort Up') . "\"><i class='fa fa-sort-asc fa-lg' aria-hidden='true'></i></a>";
                                            break;
                                        case "desc":
                                            $sortlink[$i] = "<a class='arrowhead' href=\"messages.php?show_all=".attr($showall)."&sortby=".attr($sortby)."&sortorder=asc&$activity_string_html\" onclick=\"top.restoreSession()\"  alt=\"" . xla('Sort Down') . "\"><i class='fa fa-sort-desc fa-lg' aria-hidden='true'></i></a>";
                                            break;
                                    } break;
                                }
                            }
                            // Manage page numbering and display beneath the Messages table.
                            $listnumber = 25;
                            $total = getPnotesByUser($active, $show_all, $_SESSION['authUser'], true);
                            if ($begin == "" or $begin == 0) {
                                $begin = 0;
                            }
                            $prev = $begin - $listnumber;
                            $next = $begin + $listnumber;
                            $start = $begin + 1;
                            $end = $listnumber + $start - 1;

                            $chevron_icon_left = $_SESSION['language_direction'] == 'ltr' ? 'fa-chevron-circle-left' : 'fa-chevron-circle-right';
                            $chevron_icon_right = $_SESSION['language_direction'] == 'ltr' ? 'fa-chevron-circle-right' : 'fa-chevron-circle-left';

                            if ($end >= $total) {
                                $end = $total;
                            }
                            if ($end < $start) {
                                $start = 0;
                            }
                            if ($prev >= 0) {
                                $prevlink = "<a href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=" . attr($sortorder) . "&begin=" . attr($prev) . "&$activity_string_html\" onclick=\"top.restoreSession()\"><i class=\"fa " . $chevron_icon_left . " chevron_color\" aria-hidden=\"true\"></i></a>";
                            } else {
                                $prevlink = "<i class=\"fa ". $chevron_icon_left ." \" style=\"color:grey\" aria-hidden=\"true\" title=\"". xla("On first page") . "\"></i>";
                            }

                            if ($next < $total) {
                                $nextlink = "<a href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=" . attr($sortorder) . "&begin=" . attr($next) . "&$activity_string_html\" onclick=\"top.restoreSession()\"><i class=\"fa . $chevron_icon_right . chevron_color\" aria-hidden=\"true\"></i></a>";
                            } else {
                                $nextlink = "<i class=\"fa . $chevron_icon_right .\" style=\"color:grey\" aria-hidden=\"true\" title=\"". xla("On first page") . "\"></i>";
                            }
                            // Display the Messages table header.
                            echo "
                                <table width=100%>
                                    <tr>
                                        <td>
                                            <form name='MessageList' id='MessageList' action=\"messages.php?showall=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=" . attr($sortorder) . "&begin=" . attr($begin) . "&$activity_string_html\" method=post>
                                                <table border=0 cellpadding=1 cellspacing=0   style=\"border-left: 1px #000000 solid;  width:100%; border-right: 1px #000000 solid; border-top: 1px #000000 solid;\">
                                                    <input type=hidden name=task value=delete>
                                                    <tr height=\"24\" style=\"background:lightgrey\" class=\"head\">
                                                        <td align=\"center\" width=\"25\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><input type=checkbox id=\"checkAll\" onclick=\"selectAll()\"></td>
                                                        <td width=\"20%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>" .
                                                        xlt('From') . "</b> $sortlink[0]</td>
                                                                                <td width=\"20%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>" .
                                                        xlt('Patient') . "</b> $sortlink[1]</td>
                                                                                <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>" .
                                                        xlt('Type') . "</b> $sortlink[2]</td>
                                                                                <td width=\"15%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>" .
                                                        xlt($GLOBALS['messages_due_date'] ? 'Due date' : 'Date') . "</b> $sortlink[3]</td>
                                                                                <td width=\"15%\" style=\"border-bottom: 1px #000000 solid; \" class=bold>&nbsp;<b>" .
                                                        xlt('Status') . "</b> $sortlink[4]</td>
                                                    </tr>";
                            // Display the Messages table body.
                            $count = 0;
                            $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby, $sortorder, $begin, $listnumber);
                            while ($myrow = sqlFetchArray($result)) {
                                $name = $myrow['user'];
                                $name = $myrow['users_lname'];
                                if ($myrow['users_fname']) {
                                    $name .= ", " . $myrow['users_fname'];
                                }
                                $patient = $myrow['pid'];
                                if ($patient > 0) {
                                    $patient = $myrow['patient_data_lname'];
                                    if ($myrow['patient_data_fname']) {
                                        $patient .= ", " . $myrow['patient_data_fname'];
                                    }
                                } else {
                                    $patient = "* " . xl('Patient must be set manually') . " *";
                                }
                                $count++;
                                echo "
                                    <tr id=\"row" . attr($count) . "\" style=\"background:white\" height=\"24\">
                                        <td align=\"center\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\">
                                            <input type=checkbox id=\"check" . attr($count) . "\" name=\"delete_id[]\" value=\"" .
                                            attr($myrow['id']) . "\" onclick=\"if(this.checked==true){ selectRow('row" . attr(addslashes($count)) . "'); }else{ deselectRow('row" . attr(addslashes($count)) . "'); }\"></td>
                                        <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\">
                                            <table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">" .
                                            text($name) . "</td><td width=5></td></tr>
                                            </table></td>
                                        <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\">
                                            <table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\"><a href=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&task=edit&noteid=" .
                                            attr_url($myrow['id']) . "&$activity_string_html\" onclick=\"top.restoreSession()\">" .
                                            text($patient) . "</a></td><td width=5></td></tr>
                                            </table></td>
                                        <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\">
                                            <table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">" .
                                                xlt($myrow['title']) . "</td><td width=5></td></tr>
                                            </table></td>
                                        <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\">
                                            <table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">" .
                                                text(oeFormatShortDate(substr($myrow['date'], 0, strpos($myrow['date'], " ")))) . "</td><td width=5></td></tr>
                                            </table>
                                        </td>
                                        <td style=\"border-bottom: 1px #000000 solid;\">
                                            <table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">" .
                                            text(getListItemTitle('message_status', $myrow['message_status'])) . "</td><td width=5></td></tr>
                                            </table>
                                        </td>
                                    </tr>";
                            }
                            // Display the Messages table footer.

                            echo "  </table>
                                            </form>
                                            <div class='row oe-margin-t-10'>
                                                
                                                <div class=\"col-xs-12 col-md-12 col-lg-12\"><a href=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&task=addnew&$activity_string_html\" class=\"btn btn-default btn-add\" onclick=\"top.restoreSession()\">" .
                                                xlt('Add New') . "</a> &nbsp; <a href=\"javascript:confirmDeleteSelected()\" class=\"btn btn-default btn-delete\" onclick=\"top.restoreSession()\">" .
                                                xlt('Delete') . "</a>
                                                <div  class=\"text-right\">$prevlink &nbsp; " . text($end) . " " . xlt('of') . " " . text($total) . " &nbsp; $nextlink</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <br>";
                            ?>

                            <script language="javascript">
                                // This is to confirm delete action.
                                function confirmDeleteSelected() {
                                        var int_checked = 0;
                                        var elem = document.forms.namedItem("MessageList").getElementsByTagName("input");

                                        for (i=0; i < elem.length; i++){
                                            if(elem[i].checked == true){
                                                int_checked = ++int_checked;
                                            }
                                        }
                                        if (int_checked > 0){
                                            if (confirm("<?php echo xls('Do you really want to delete the selection?'); ?>")) {
                                                document.MessageList.submit();
                                            }
                                        } else {
                                            alert("<?php echo xls('Please select message(s) to delete'); ?>");
                                        }
                                    }


                                // This is to allow selection of all items in Messages table for deletion.
                                function selectAll() {
                                    if (document.getElementById("checkAll").checked === true) {
                                        document.getElementById("checkAll").checked = true;<?php
                                        for ($i = 1; $i <= $count; $i++) {
                                            echo "document.getElementById(\"check$i\").checked=true; document.getElementById(\"row$i\").style.background='#E7E7E7';  ";
                                        } ?>
                                    } else {
                                        document.getElementById("checkAll").checked = false;<?php
                                        for ($i = 1; $i <= $count; $i++) {
                                            echo "document.getElementById(\"check$i\").checked=false; document.getElementById(\"row$i\").style.background='#F7F7F7';  ";
                                        } ?>
                                    }
                                }

                                // The two functions below are for managing row styles in Messages table.
                                function selectRow(row) {
                                    document.getElementById(row).style.background = "#E7E7E7";
                                }

                                function deselectRow(row) {
                                    document.getElementById(row).style.background = "#F7F7F7";
                                }
                            </script>
                            <?php
                        }
                        ?>
                    </div>
                </fieldset>
            </div>
        </div><!--end of messages div-->
        <div class="row oe-display" id="reminders-div">
            <div class="col-sm-12">
                <fieldset>
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <div class="oe-margin-b-10">
                            <span class="title"><?php echo xlt('Reminders'); ?></span>
                        </div>
                        <?php
                        // TajEmo Work by CB 2012/01/11 02:51:25 PM adding dated reminders
                        // I am asuming that at this point security checks have been performed
                        //require_once '../dated_reminders/dated_reminders.php';
                        require_once '../dated_reminders/dated_reminders.php';
                        ?>
                    </div>
                </fieldset>
            </div>
        </div><!--end of reminders div-->
        <div class="row oe-display" id="recalls-div">
            <div class="col-sm-12">
                <fieldset>
                    <?php if ($GLOBALS['disable_rcb'] != '1') { ?>
                    <div class="col-sm-6 col-md-6 col-lg-6">
                        <div class="dr_container">
                            <span class="title"><?php echo xlt('Recalls'); ?></span>
                            <br/><br/>
                            <a class="btn btn-default btn-add"
                               onclick="goReminderRecall('addRecall');"><span><?php echo xlt('New Recall'); ?></span></a>
                            &nbsp;
                            <a class="btn btn-default btn-transmit"
                               onclick="goReminderRecall('Recalls');"><span><?php echo xlt('Recall Board'); ?></span></a>
                            &nbsp;
                        </div>
                    </div>
                    <?php } ?>
                </fieldset>
            </div>
        </div><!--end of recalls div-->
        <div class="row oe-display" id="sms-div">
            <div class="col-sm-12">
                <fieldset>
                    <?php if ($logged_in) { ?>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <span class="title"><?php echo xlt('SMS Zone'); ?></span>
                        <br/><br/>
                        <form id="smsForm" class="input-group">
                            <input id="SMS_patient" type="text" style="margin:0;max-width:100%;" class="form-control"
                                   placeholder="<?php echo xla("Patient Name"); ?>" />
                            <span class="input-group-addon" onclick="SMS_direct();"><i
                                        class="glyphicon glyphicon-phone"></i></span>
                            <input type="hidden" id="sms_pid">
                            <input type="hidden" id="sms_mobile" value="">
                            <input type="hidden" id="sms_allow" value="">
                        </form>
                    </div>
                    <?php } ?>
                </fieldset>
            </div>
        </div><!--end of sms div-->
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <?php
    //home of the help modal ;)
    //$GLOBALS['enable_help'] = 0; // Please comment out line if you want help modal to function on this page
    if ($GLOBALS['enable_help'] == 1) {
        echo "<script>var helpFile = 'message_center_help.php'</script>";
        //help_modal.php lives in interface, set path accordingly
        require "../../help_modal.php";
    }
    ?>
    <script language="javascript">

        var collectvalidation = <?php echo $collectthis; ?>;

        $(function (){
            $("#reminders-div").hide();
            $("#recalls-div").hide();
            $("#sms-div").hide();
            $("#messages-li").click(function(){
                $("#messages-div").show(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#li-mess").addClass("active");
                $("#li-remi").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").removeClass("active");

            });
            $("#reminders-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").show(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#li-remi").addClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").removeClass("active");
            });
            $("#recalls-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").show(250);
                $("#sms-div").hide(250);
                $("#li-remi").removeClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").addClass("active");
                $("#li-sms").removeClass("active");
            });
            $("#sms-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").show(250);
                $("#li-remi").removeClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").addClass("active");
            });

            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                ,minDate : 0 //only future
            })

        });
        $(function (){
            $( "ul.navbar-nav" ).children().click(function(){
                $(".collapse").collapse('hide');
            });
        });
        $(function (){
            //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
            // the relevant css file needs to be called i.e. jquery-ui-darkness
            $('#see-all-tooltip').attr( "title", "<?php echo xla('Click to show messages for all users'); ?>" );
            $('#see-all-tooltip').tooltip();
            $('#just-mine-tooltip').attr( "title", "<?php echo xla('Click to show messages for only the current user'); ?>" );
            $('#just-mine-tooltip').tooltip();
        });
        $(function () {
            var f = $("#smsForm");
            $("#SMS_patient").autocomplete({
                source: "save.php?go=sms_search",
                minLength: 2,
                select: function (event, ui) {
                    event.preventDefault();
                    $("#SMS_patient").val(ui.item.label + ' ' + ui.item.mobile);
                    $("#sms_pid").val(ui.item.pid);
                    $("#sms_mobile").val(ui.item.mobile);
                    $("#sms_allow").val(ui.item.allow);
                }
            });
        });
        jQuery.ui.autocomplete.prototype._resizeMenu = function () {
            var ul = this.menu.element;
            ul.outerWidth(this.element.outerWidth());
        };
        $(function () {
            $("#newnote").click(function (event) {
                NewNote(event);
            });
            $("#printnote").click(function () {
                PrintNote();
            });
            var obj = $("#form_message_status");
            obj.onchange = function () {
                SaveNote();
            };
            $("#cancel").click(function () {
                CancelNote();
            });
            $("#note").focus();

            //clear button in messages
            $("#clear_user").click(function(){
                $("#assigned_to_text").val("<?php echo xls('Select Users From The Dropdown List'); ?>");
                $("#assigned_to").val("");
                $("#users").val("--");
            });

            //clear inputs of patients
            $("#clear_patients").click(function(){
                $("#reply_to").val("");
                $("#form_patient").val("");
            });
        });

        var NewNote = function (event) {
            top.restoreSession();
            if(document.getElementById("form_message_status").value !== 'Done'){
                collectvalidation.assigned_to = {
                    presence: {message: "<?php echo xls('Recipient required unless status is Done'); ?>"}
                }
            }
            else{
                delete collectvalidation.assigned_to;
            }

            $('#newnote').attr('disabled', true);

            var submit = submitme(1, event, 'new_note', collectvalidation);
            if(!submit){
                $('#newnote').attr('disabled', false);
            }
            else {
                $("#new_note").submit();
            }
        };
        var PrintNote = function () {
            top.restoreSession();
            window.open('../../patient_file/summary/pnotes_print.php?noteid=' + <?php echo js_url($noteid); ?>, '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
        };

        var SaveNote = function () {
            <?php if ($noteid) { ?>
            top.restoreSession();
            $("#task").val("save");
            $("#new_note").submit();
            <?php } ?>
        };

        var CancelNote = function () {
            top.restoreSession();
            $("#task").val("");
            $("#new_note").submit();
        };

        function gotoReport(doc_id, pname, pid, pubpid, str_dob) {
            EncounterDateArray = [];
            CalendarCategoryArray = [];
            EncounterIdArray = [];
            Count = 0;
            <?php
            if (isset($enc_list) && sqlNumRows($enc_list) > 0) {
                while ($row = sqlFetchArray($enc_list)) {
                    ?>
                EncounterIdArray[Count] = '<?php echo attr($row['encounter']); ?>';
            EncounterDateArray[Count] = '<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($row['date'])))); ?>';
            CalendarCategoryArray[Count] = '<?php echo attr(xl_appt_category($row['pc_catname'])); ?>';
            Count++;
                    <?php
                }
            }
            ?>
            top.restoreSession();
            $.ajax({
                type: 'get',
                url: '<?php echo $GLOBALS['webroot'] . "/library/ajax/set_pt.php";?>',
                data: {
                    set_pid: pid,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                },
                async: false
            });
            parent.left_nav.setPatient(pname, pid, pubpid, '', str_dob);
            parent.left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray);
            <?php if ($GLOBALS['new_tabs_layout']) { ?>
            var docurl = '../controller.php?document&view' + "&patient_id=" + encodeURIComponent(pid) + "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = 'patient_file/summary/demographics.php?pid=' + encodeURIComponent(pid);
            parent.left_nav.loadFrame('dem1', 'pat', paturl);
            parent.left_nav.loadFrame('doc0', 'enc', docurl);
            top.activateTabByName('enc', true);
            <?php } else { ?>
            var docurl = '<?php  echo $GLOBALS['webroot'] . "/controller.php?document&view"; ?>' + "&patient_id=" + encodeURIComponent(pid) + "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = '<?php  echo $GLOBALS['webroot'] . "/interface/patient_file/summary/demographics.php?pid="; ?>' + encodeURIComponent(pid);
            var othername = (window.name === 'RTop') ? 'RBot' : 'RTop';
            parent.frames[othername].location.href = paturl;
            location.href = docurl;
            <?php } ?>
        }

        // This is for callback by the find-patient popup.
        function setpatient(pid, lname, fname, dob) {
            var f = document.getElementById('new_note');
            f.form_patient.value += lname + ', ' + fname + '; ';
            f.reply_to.value += pid + ';';
            <?php if ($noteid) { ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit();
            <?php } ?>
        }

        // This is for callback by the multi_patients_finder popup.
        function setMultiPatients(patientsList) {
            var f = document.getElementById('new_note');
            f.form_patient.value='';
            f.reply_to.value='';
            $.each(patientsList, function (key, patient) {
                f.form_patient.value += patient.lname + ', ' + patient.fname + '; ';
                f.reply_to.value += patient.pid + ';';
            })

            <?php if ($noteid) { ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit();
            <?php } ?>
        }

        // This invokes the find-patient popup.
        function sel_patient() {
            dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 625, 400);
        }

        function multi_sel_patient() {
            $('#reply_to').trigger('click');
            var url = '../../main/finder/multi_patients_finder.php'
            // for edit selected list
            if ($('#reply_to').val() !== '') {
                url = url + '?patients=' + $('#reply_to').val() + '&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>';
            }
            dlgopen(url, '_blank', 625, 400);
        }

        function addtolist(sel) {
            $('#assigned_to').trigger("click");
            var itemtext = document.getElementById('assigned_to_text');
            var item = document.getElementById('assigned_to');
            if (sel.value !== '--') {
                if (item.value) {
                    if (item.value.indexOf(sel.value) === -1) {
                        itemtext.value = itemtext.value + ' ; ' + sel.options[sel.selectedIndex].text;
                        item.value = item.value + ';' + sel.value;
                    }
                } else {
                    itemtext.value = sel.options[sel.selectedIndex].text;
                    item.value = sel.value;
                }
            }
        }

        function SMS_direct() {
            var pid = $("#sms_pid").val();
            var m = $("#sms_mobile").val();
            var allow = $("#sms_allow").val();
            if ((pid === '') || (m === '')) {
                alert('<?php echo xls("MedEx needs a valid mobile number to send SMS messages..."); ?>');
            } else if (allow === 'NO') {
                alert('<?php echo xls("This patient does not allow SMS messaging!"); ?>');
            } else {
                top.restoreSession();
                window.open('messages.php?nomenu=1&go=SMS_bot&pid=' + encodeURIComponent(pid) + '&m=' + encodeURIComponent(m), 'SMS_bot', 'width=370,height=600,resizable=0');
            }
        }
    </script>
    <?php
}
?>
</body>
</html>