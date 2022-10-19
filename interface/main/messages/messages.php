<?php

/**
 * Message and Reminder Center UI
 *
 * @Package OpenEMR
 * @link http://www.open-emr.org
 * @author OpenEMR Support LLC
 * @author Roberto Vasquez <robertogagliotta@gmail.com>
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Ray Magauran <magauran@medfetch.com>
 * @author Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @copyright Copyright (c) 2017 MedEXBank.com
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/gprelations.inc.php");
require_once "$srcdir/user.inc";
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

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
        $result = $MedEx->login('');
        $MedEx->display->SMS_bot($result);
        exit();
    }
    $logged_in = $MedEx->login();
} else {
    $logged_in = null;
}

$setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
//use $uspfx as the first variable for page/script specific user settings instead of '' (which is like a global but you have to request it).
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$rcb_selectors = prevSetting($uspfx, 'rcb_selectors', 'rcb_selectors', 'block');
$rcb_facility = prevSetting($uspfx, 'form_facility', 'form_facility', '');
$rcb_provider = prevSetting($uspfx, 'form_provider', 'form_provider', $_SESSION['authUserID']);

if (
    (array_key_exists('setting_bootstrap_submenu', $_POST)) ||
    (array_key_exists('rcb_selectors', $_POST))
) {
    // These are not form elements. We only ever change them via ajax, so exit now.
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    //validation library
    $use_validate_js = 1;
    require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
    ?>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="MedEx Bank" />
    <meta name="author" content="OpenEMR: MedExBank" />
    <?php Header::setupHeader(['datetime-picker', 'opener', 'moment', 'select2']); ?>
    <link rel="stylesheet" href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>">

    <script>
        var xljs1 = '<?php echo xla('Preferences updated successfully'); ?>';
        var format_date_moment_js = '<?php echo attr(DateFormatRead("validateJS")); ?>';
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <script src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>
    <style>
        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left !important;
            }

            .icon-bar {
                background-color: var(--danger);
            }
        }
    </style>

<?php
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    $MedEx->display->navigation($logged_in);
    echo "<br /><br /><br />";
}

if (!empty($_REQUEST['go'])) { ?>
    <?php
    if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
        echo "<title>" . xlt('MedEx Setup') . "</title>";
        $stage = $_REQUEST['stage'];
        if (!is_numeric($stage)) {
            echo "<br /><span class='title'>" . text($stage) . " " . xlt('Warning') . ": " . xlt('This is not a valid request') . ".</span>";
        } else {
            $MedEx->setup->MedExBank($stage);
        }
    } elseif ($_REQUEST['go'] == "addRecall") {
        echo "<title>" . xlt('New Recall') . "</title>";
        $MedEx->display->display_add_recall();
    } elseif ($_REQUEST['go'] == 'Recalls') {
        echo "<title>" . xlt('Recall Board') . "</title>";
        $MedEx->display->display_recalls($logged_in);
    } elseif ((($_REQUEST['go'] == "setup") || ($_REQUEST['go'] == 'Preferences')) && ($logged_in)) {
        echo "<title>MedEx: " . xlt('Preferences') . "</title>";
        $MedEx->display->preferences();
    } elseif ($_REQUEST['go'] == 'icons') {
        echo "<title>MedEx: " . xlt('Icons') . "&#x24B8;</title>";
        $MedEx->display->icon_template();
    } elseif ($_REQUEST['go'] == 'SMS_bot') {
        echo "<title>MedEx: SMS Bot&#x24B8;</title>";
        $MedEx->display->SMS_bot($logged_in);
        exit;
    } else {
        echo "<title>" . xlt('MedEx Setup') . "</title>";
        echo xlt('Warning: Navigation error. Please refresh this page.');
    }
} else {
    //original message.php stuff

    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="float-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color: var(--gray)" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="float-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color: var(--gray300) !important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
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

    echo "<title>" .  xlt('Message Center') . "</title>";
    ?>
</head>
<body class='body_top'>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="container-fluid mb-3">
            <ul class="nav nav-pills">
                <li class="nav-item" id='li-mess'>
                    <a href='#' class="active nav-link font-weight-bold" id='messages-li'><?php echo xlt('Messages'); ?></a>
                </li>
                <li class="nav-item" id='li-remi'>
                    <a href='#' id='reminders-li' class="nav-link font-weight-bold"><?php echo xlt('Reminders'); ?></a>
                </li>
                <?php if ($GLOBALS['disable_rcb'] != '1') { ?>
                <li class="nav-item" id='li-reca'>
                    <a href='#' id='recalls-li' class="nav-link font-weight-bold"><?php echo xlt('Recalls'); ?></a>
                </li>
                <?php }?>
                <?php if ($logged_in) { ?>
                <li class="nav-item" id='li-sms'>
                    <a href='#' id='sms-li' class="nav-link font-weight-bold"><?php echo xlt('SMS Zone'); ?></a>
                </li>
                <?php }?>
            </ul>
        </div>
        <div class="row" id="messages-div">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
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
                        if (AclMain::aclCheckCore('admin', 'super')) {
                            if ($show_all == 'yes') {
                                $showall = "yes";
                                $lnkvar = "messages.php?show_all=no&" . $activity_string_html;
                                $lnkattributes = "name='Just Mine' onclick='top.restoreSession()'";
                                $otherstuff = "<i id='just-mine-tooltip' class='fa fa-user fa-lg text-body' aria-hidden='true'></i>";
                                $messages = xl('All Messages');
                            } else {
                                $showall = "no";
                                $lnkvar = "messages.php?show_all=yes&" . $activity_string_html;
                                $lnkattributes = "name='See All' onclick='top.restoreSession()'";
                                $otherstuff = "<i id='see-all-tooltip' class='fa fa-users fa-lg text-body' aria-hidden='true'></i>";
                                $messages = xl('My Messages');
                            }
                        } else {
                            $messages = xlt('My Messages');
                        }
                        ?>
                        <div class="oe-margin-b-20">
                            <span class="title"><?php echo text($messages); ?></span>
                            <a class='more' href="<?php echo $lnkvar ?? ''; ?>" <?php echo $lnkattributes ?? ''; ?>><?php echo $otherstuff ?? ''; ?></a>
                        </div>
                        <div class="oe-margin-b-10">
                            <?php
                            //show the activity links
                            if (empty($task) || $task == "add" || $task == "delete") { ?>
                                    <?php if ($active == "all") { ?>
                                <span class="font-weight-bold"><?php echo xlt('All Messages'); ?></span>
                                    <?php } else { ?>
                                <a href="messages.php" class="link btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt('Show All'); ?></a>
                                    <?php } ?>
                                    |
                                    <?php if ($active == '1') { ?>
                                <span class="font-weight-bold"><?php echo xlt('Active Messages'); ?></span>
                                    <?php } else { ?>
                                <a href="messages.php?form_active=1" class="link btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt('Show Active'); ?></a>
                                    <?php } ?>
                                    |
                                    <?php if ($active == '0') { ?>
                                <span class="font-weight-bold"><?php echo xlt('Inactive Messages'); ?></span>
                                    <?php } else { ?>
                                <a href="messages.php?form_inactive=1" class="link btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt('Show Inactive'); ?></a>
                                    <?php } ?>
                            <?php } ?>
                        </div>
                        <?php
                        $note = '';
                        $noteid = '';
                        $title = '';
                        $form_message_status = '';
                        $reply_to = '';
                        $patientname = '';
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
                                    // if our reply-to is 0 it breaks multi patient select and other functionality
                                    // this most likely didn't break before due to php implicit type conversion of 0 to ""
                                    if ($reply_to == "" && $result['pid'] != 0) {
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
                        // This is for sorting the records.
                        $sort = array("users.lname", "patient_data.lname", "pnotes.title", "pnotes.date", "pnotes.message_status");
                        $sortby = (isset($_REQUEST['sortby']) && ($_REQUEST['sortby'] != "")) ? $_REQUEST['sortby'] : $sort[3];
                        $sortorder = (isset($_REQUEST['sortorder']) && ($_REQUEST['sortorder'] != "")) ? $_REQUEST['sortorder'] : "desc";
                        $begin = isset($_REQUEST['begin']) ? $_REQUEST['begin'] : 0;

                        if ($task == "addnew" or $task == "edit") {
                            // Display the Messages page layout.
                            echo "<form name='form_patient' id='new_note'
                                    class='form-horizontal'
                                    action=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&$activity_string_html\"
                                    method='post'>
                                    <input type='hidden' name='noteid' id='noteid' value='" . attr($noteid) . "' />
                                    <input type='hidden' name='task' id='task' value='add' />";
                            if ($task == "addnew") {
                                $message_legend = xl('Create New Message');
                                $onclick = "onclick=multi_sel_patient()";
                            } elseif ($task == "edit") {
                                $message_legend = xl('Add To Existing Message');
                                $onclick = "";
                            }

                            ?>
                            <div class='col-md-12'>
                                <div class="jumbotron jumbotron-fluid py-3">
                                    <h4><?php echo text($message_legend); ?></h4>
                                    <div class="row">
                                        <div class="col-12 oe-custom-line">
                                            <div class="row">
                                                <div class="col-6 col-md-3">
                                                    <label for="form_note_type"><?php echo xlt('Type'); ?>:</label>
                                                    <?php
                                                    if ($title == "") {
                                                        $title = "Unassigned";
                                                    }
                                                    // Added 6/2009 by BM to incorporate the patient notes into the list_options listings.
                                                    generate_form_field(array('data_type' => 1, 'field_id' => 'note_type', 'list_id' => 'note_type', 'empty_title' => 'SKIP', 'order_by' => 'title', 'class' => 'form-control'), $title);
                                                    ?>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label for="form_message_status"><?php echo xlt('Status'); ?>:</label>
                                                    <?php
                                                    if ($form_message_status == "") {
                                                        $form_message_status = 'New';
                                                    }
                                                    generate_form_field(array('data_type' => 1, 'field_id' => 'message_status', 'list_id' => 'message_status', 'empty_title' => 'SKIP', 'order_by' => 'title', 'class' => 'form-control'), $form_message_status); ?>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <?php
                                                    if ($task != "addnew" && $result['pid'] != 0) { ?>
                                                        <a class="patLink" onclick="goPid('<?php echo attr(addslashes($result['pid'])); ?>')" title='<?php echo xla('Click me to Open Patient Dashboard') ?>'><?php echo xlt('Patient'); ?>:</a><label for="form_patient">&nbsp</label>
                                                        <?php
                                                    } else { ?>
                                                        <span class='font-weight-bold <?php echo($task == "addnew" ? "text-danger" : "") ?>'><?php echo xlt('Patient'); ?>:</span></a><label for="form_patient"></label>
                                                        <?php
                                                    }

                                                    if ($reply_to) {
                                                        $prow = sqlQuery("SELECT lname, fname,pid, pubpid, DOB  " .
                                                            "FROM patient_data WHERE pid = ?", array($reply_to));
                                                        $patientname = $prow['lname'] . ", " . $prow['fname'];
                                                    }
                                                    if ($task == "addnew" || $result['pid'] == 0) {
                                                        $cursor = "oe-cursor-add";
                                                        $background = "oe-patient-background";
                                                    } elseif ($task == "edit") {
                                                        $cursor = "oe-cursor-stop";
                                                        $background = '';
                                                    }
                                                    ?>
                                                    <input type='text' id='form_patient' name='form_patient' class='form-control <?php echo $cursor . " " . $background;?>' onclick="multi_sel_patient()" placeholder='<?php echo xla("Click to add patient"); ?>' value='<?php echo attr($patientname); ?>' readonly />
                                                    <input type='hidden' class="form-control" name='reply_to' id='reply_to' value='<?php echo attr($reply_to); ?>'/>
                                                </div>
                                                <div class="col-6 col-md-2 d-flex flex-wrap">
                                                    <?php
                                                    if ($task == "addnew" || $result['pid'] == 0) {
                                                        echo "<label class='oe-empty-label' for='clear_patients'></label>";
                                                        echo '<button type="button" id="clear_patients"  class="btn btn-secondary btn-undo float-left flip" value="' . xla('Clear') . '">' . xlt("Clear") . '</button>';
                                                    } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 oe-custom-line">
                                            <div class="row">
                                                <?php if ($GLOBALS['messages_due_date']) { ?>
                                                <div class="col-6 col-sm-2">
                                                    <label for="form_note_type"><?php echo xlt('Due date'); ?>:</label>
                                                    <?php generate_form_field(array('data_type' => 4, 'field_id' => 'datetime', 'edit_options' => 'F'), empty($datetime) ? date('Y-m-d H:i') : $datetime) ?>
                                                </div>
                                                <?php } ?>
                                                <div class="col-6 col-sm-4 d-flex align-items-end flex-wrap">
                                                    <label for="assigned_to_text"><?php echo xlt('To{{Destination}}'); ?>:</label>
                                                    <input type='text' name='assigned_to_text' class='form-control oe-cursor-stop' id='assigned_to_text' readonly='readonly' value='' placeholder='<?php echo xla("SELECT Users FROM The Dropdown LIST"); ?>' />
                                                    <input type='hidden' name='assigned_to' id='assigned_to' />
                                                </div>
                                                <div class="col-6 col-sm-4">
                                                    <label class="oe-empty-label" for="users"></label>
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
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-6 col-sm-2  d-flex align-items-end flex-wrap">
                                                    <label class="oe-empty-label" for="users"></label>
                                                    <button type="button" name="clear_user" id="clear_user" class="btn btn-secondary btn-undo float-left flip" value="<?php echo xla('Clear'); ?>"><?php echo xlt('Clear'); ?></button>
                                                </div>
                                            </div>
                                        <div class='col-12 oe-margin-t-3'>
                                        <?php
                                        if ($noteid) {
                                            include "templates/linked_documents.php";

                                            // Get the related procedure order IDs if any.
                                            $tmp = sqlStatement(
                                                "SELECT id1 FROM gprelations WHERE " .
                                                "type1 = ? AND type2 = ? AND id2 = ?",
                                                array('2', '6', $noteid)
                                            );
                                            if (sqlNumRows($tmp)) {
                                                echo " <tr>\n";
                                                echo "  <td class='text'><span class='font-weight-bold'>" . xlt('Linked procedure order') . ":</span>\n";
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
                                    <!-- <div class="row"> -->
                                        <div class='col-12'>
                                            <?php

                                            if ($noteid) {
                                                $body = preg_replace('/(:\d{2}\s\()' . $result['pid'] . '(\sto\s)/', '${1}' . $patientname . '${2}', $body);
                                                $body = preg_replace('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}\s\([^)(]+\s)(to)(\s[^)(]+\))/', '${1}' . xl('to{{Destination}}') . '${3}', $body);
                                                $body = pnoteConvertLinks(nl2br(text(oeFormatPatientNote($body))));
                                                echo "<div style='height: 120px; resize: vertical;' class='border overflow-auto text oe-margin-t-3 p-2 mb-2 w-100'>" . $body . "</div>";
                                            }

                                            ?>
                                            <textarea name='note' id='note' class='form-control oe-margin-t-3 p-1' rows="5"><?php echo nl2br(text($note)); ?></textarea>
                                        </div>
                                        <div class="col-12 position-override oe-margin-t-10">
                                            <?php if ($noteid) { ?>
                                                <!-- This is for displaying an existing note. -->
                                                <button type="button" class="btn btn-primary btn-send-msg" id="newnote" value="<?php echo xla('Send message'); ?>"><?php echo xlt('Send message'); ?></button>
                                                <button type="button" class="btn btn-primary btn-print" id="printnote" value="<?php echo xla('Print message'); ?>"><?php echo xlt('Print message'); ?></button>
                                                <button type="button" class="btn btn-secondary btn-cancel" id="cancel" value="<?php echo xla('Cancel'); ?>"><?php echo xlt('Cancel'); ?></button>
                                            <?php } else { ?>
                                                <!-- This is for displaying a new note. -->
                                                <button type="button" class="btn btn-primary btn-send-msg" id="newnote" value="<?php echo xla('Send message'); ?>"><?php echo xlt('Send message'); ?></button>
                                                <button type="button" class="btn btn-cancel btn-secondary" id="cancel" value="<?php echo xla('Cancel'); ?>"><?php echo xlt('Cancel'); ?></button>
                                            <?php }
                                            ?>
                                        </div>
                                    <!-- </div> -->
                                </div>
                                </div>
                            </form>
                            <?php
                        } else {
                            for ($i = 0; $i < count($sort); $i++) {
                                $sortlink[$i] = "<a  class='arrowhead' href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sort[$i]) . "&sortorder=asc&$activity_string_html\" onclick=\"top.restoreSession()\" alt=\"" . xla('Sort Up') . "\"><i class='fa fa-sort-down fa-lg' aria-hidden='true'></i></a>";
                            }
                            for ($i = 0; $i < count($sort); $i++) {
                                if ($sortby == $sort[$i]) {
                                    switch ($sortorder) {
                                        case "asc":
                                            $sortlink[$i] = "<a class='arrowhead' href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=desc&$activity_string_html\" onclick=\"top.restoreSession()\" alt=\"" . xla('Sort Up') . "\"><i class='fa fa-sort-up fa-lg' aria-hidden='true'></i></a>";
                                            break;
                                        case "desc":
                                            $sortlink[$i] = "<a class='arrowhead' href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=asc&$activity_string_html\" onclick=\"top.restoreSession()\"  alt=\"" . xla('Sort Down') . "\"><i class='fa fa-sort-down fa-lg' aria-hidden='true'></i></a>";
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
                                $prevlink = "<i class=\"fa " . $chevron_icon_left . " text-muted\" aria-hidden=\"true\" title=\"" . xla("On first page") . "\"></i>";
                            }

                            if ($next < $total) {
                                $nextlink = "<a href=\"messages.php?show_all=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=" . attr($sortorder) . "&begin=" . attr($next) . "&$activity_string_html\" onclick=\"top.restoreSession()\"><i class=\"fa . $chevron_icon_right . chevron_color\" aria-hidden=\"true\"></i></a>";
                            } else {
                                $nextlink = "<i class=\"fa " . $chevron_icon_right . " text-muted\" aria-hidden=\"true\" title=\"" . xla("On first page") . "\"></i>";
                            }
                            // Display the Messages table header.
                            echo "
                                <table class=\"w-100\">
                                    <tr>
                                        <td>
                                            <form name='MessageList' id='MessageList' action=\"messages.php?showall=" . attr($showall) . "&sortby=" . attr($sortby) . "&sortorder=" . attr($sortorder) . "&begin=" . attr($begin) . "&$activity_string_html\" method='post'>
                                                <table class='table table-sm table-hover w-100'>
                                                    <input type='hidden' name='task' value='delete' />
                                                    <thead class='table-primary'>
                                                      <tr height='24'>
                                                          <th align='center' width='25'><input type='checkbox' id='checkAll' onclick='selectAll()'></th>
                                                          <th width='20%' class='font-weight-bold'>&nbsp;" . xlt('From') . " $sortlink[0]</th>
                                                          <th width='20%' class='font-weight-bold'>&nbsp;" . xlt('Patient') . " $sortlink[1]</th>
                                                          <th class='font-weight-bold'>&nbsp;" . xlt('Type') . " $sortlink[2]</th>
                                                          <th width='15%' class='font-weight-bold'>&nbsp;" . xlt($GLOBALS['messages_due_date'] ? 'Due date' : 'Date') . " $sortlink[3]</th>
                                                          <th width='15%' class='font-weight-bold'>&nbsp;" . xlt('Status') . " $sortlink[4]</th>
                                                      </tr>
                                                    </thead>";
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
                                    <tr id=\"row" . attr($count) . "\" height='24'>
                                        <td align='center'>
                                            <input type='checkbox' id=\"check" . attr($count) . "\" name=\"delete_id[]\" value=\"" .
                                            attr($myrow['id']) . "\" onclick=\"if(this.checked==true){ selectRow('row" . attr(addslashes($count)) . "'); }else{ deselectRow('row" . attr(addslashes($count)) . "'); }\"></td>
                                        <td>
                                            <div>" . text($name) . "</div>
                                        </td>
                                        <td>
                                            <div><a href=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&task=edit&noteid=" .
                                            attr_url($myrow['id']) . "&$activity_string_html\" onclick=\"top.restoreSession()\">" .
                                            text($patient) . "</a></div>
                                        </td>
                                        <td>
                                            <div>" .
                                                xlt($myrow['title']) . "</div>
                                        <td>
                                            <div>" . text(oeFormatDateTime($myrow['date'])) . "</div>
                                        </td>
                                        <td>
                                            <div>" . text(getListItemTitle('message_status', $myrow['message_status'])) . "</div>
                                        </td>
                                    </tr>";
                            }
                            // Display the Messages table footer.

                            echo "  </table>
                                            </form>
                                            <div class='row oe-margin-t-10'>

                                                <div class=\"col-12 col-md-12 col-lg-12\"><a href=\"messages.php?showall=" . attr_url($showall) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($begin) . "&task=addnew&$activity_string_html\" class=\"btn btn-primary btn-add\" onclick=\"top.restoreSession()\">" .
                                                xlt('Add New{{Message}}') . "</a> &nbsp; <a href=\"javascript:confirmDeleteSelected()\" class=\"btn btn-danger btn-delete\" onclick=\"top.restoreSession()\">" .
                                                xlt('Delete') . "</a>";

                            if ($GLOBALS['phimail_enable']) {
                                echo "&nbsp; <a href='trusted-messages.php' onclick='top.restoreSession()' class='btn btn-secondary btn-mail'>" . xlt("Compose Trusted Direct Message") . "</a>";
                                echo "&nbsp; <button class='btn btn-secondary btn-refresh trusted-messages-force-check'>" . xlt("Check New Trusted Messages") . "</button>";
                            }
                            echo "
                                                <div  class=\"text-right\">$prevlink &nbsp; " . text($end) . " " . xlt('of') . " " . text($total) . " &nbsp; $nextlink</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <br />";
                            ?>

                            <script>
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
                                            echo "document.getElementById(\"check$i\").checked=true; document.getElementById(\"row$i\").style.background='var(--gray200)';  ";
                                        } ?>
                                    } else {
                                        document.getElementById("checkAll").checked = false;<?php
                                        for ($i = 1; $i <= $count; $i++) {
                                            echo "document.getElementById(\"check$i\").checked=false; document.getElementById(\"row$i\").style.background='var(--light)';  ";
                                        } ?>
                                    }
                                }

                                // The two functions below are for managing row styles in Messages table.
                                function selectRow(row) {
                                    document.getElementById(row).style.background = "var(--gray200)";
                                }

                                function deselectRow(row) {
                                    document.getElementById(row).style.background = "var(--light)";
                                }
                            </script>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div><!--end of messages div-->
        <div class="row oe-display" id="reminders-div">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
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
                </div>
            </div>
        </div><!--end of reminders div-->
        <div class="row oe-display" id="recalls-div">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
                    <?php if ($GLOBALS['disable_rcb'] != '1') { ?>
                    <div class="col-sm-6 col-md-6 col-lg-6">
                        <div class="dr_container">
                            <span class="title"><?php echo xlt('Recalls'); ?></span>
                            <br/><br/>
                            <button class="btn btn-primary btn-add" onclick="goReminderRecall('addRecall');"><?php echo xlt('New Recall'); ?></button>
                            <a class="btn btn-secondary btn-transmit" onclick="goReminderRecall('Recalls');"><span><?php echo xlt('Recall Board'); ?></span></a>
                            &nbsp;
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div><!--end of recalls div-->
        <div class="row oe-display" id="sms-div">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
                    <?php if ($logged_in) { ?>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <span class="title"><?php echo xlt('SMS Zone'); ?></span>
                        <br/><br/>
                        <form id="smsForm" class="input-group">
                            <select id="SMS_patient" type="text" class="form-control m-0 w-100" placeholder="<?php echo xla("Patient Name"); ?>" > </select>
                            <span class="input-group-addon" onclick="SMS_direct();"><i class="fas fa-phone"></i></span>
                            <input type="hidden" id="sms_pid" />
                            <input type="hidden" id="sms_mobile" value="" />
                            <input type="hidden" id="sms_allow" value="" />
                        </form>
                    </div>
                    <?php } ?>
                </div>
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
    <script>
        var collectvalidation = <?php echo $collectthis; ?>;

        $(function () {
            var webRoot = <?php echo js_escape($GLOBALS['web_root']); ?>;
            $("#reminders-div").hide();
            $("#recalls-div").hide();
            $("#sms-div").hide();
            $("#messages-li").click(function(){
                $("#messages-div").show(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#messages-li").addClass("active");
                $("#reminders-li").removeClass("active");
                $("#recalls-li").removeClass("active");
                $("#sms-li").removeClass("active");

            });
            $("#reminders-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").show(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#reminders-li").addClass("active");
                $("#messages-li").removeClass("active");
                $("#recalls-li").removeClass("active");
                $("#sms-li").removeClass("active");
            });
            $("#recalls-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").show(250);
                $("#sms-div").hide(250);
                $("#reminders-li").removeClass("active");
                $("#messages-li").removeClass("active");
                $("#recalls-li").addClass("active");
                $("#sms-li").removeClass("active");
            });
            $("#sms-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").show(250);
                $("#reminders-li").removeClass("active");
                $("#messages-li").removeClass("active");
                $("#recalls-li").removeClass("active");
                $("#sms-li").addClass("active");
            });

            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                ,minDate : 0 //only future
            })

            <?php if ($GLOBALS['phimail_enable']) : ?>
            $('.trusted-messages-force-check').click(function() {
                window.top.restoreSession();
                request = new FormData;
                request.append("ajax", "1");
                request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
                request.append("background_service", "phimail");
                request.append("background_force", "1");
                fetch(webRoot + "/library/ajax/execute_background_services.php", {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: request
                }).then((response) => {
                    if (response.status !== 200) {
                        console.log('Background Service refresh failed. Status Code: ' + response.status);
                    } else {
                        // we've refreshed give them time to reload the page
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    }
                }).catch(function(error) {
                    console.log('Background Service refresh failed: ', error);
                    alert(window.xl("Check new messages failed. Check the server logs for more information."));
                });
            });
            <?php endif; ?>

        });
        $(function () {
            $( "ul.navbar-nav" ).children().click(function(){
                $(".collapse").collapse('hide');
            });
        });
        $(function () {
            $('#see-all-tooltip').attr({"title": <?php echo xlj('Click to show messages for all users'); ?>, "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();
            $('#just-mine-tooltip').attr({"title": <?php echo xlj('Click to show messages for only the current user'); ?>, "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();
        });
        $(function () {
            var f = $("#smsForm");
            $("#SMS_patient").select2({
                ajax: {
                    url: "save.php",
                    dataType: 'json',
                    data: function(params) {
                        return {
                        go: "sms_search",
                        term: params.term
                        };
                    },
                    processResults: function(data) {
                        return  {
                            results: $.map(data, function(item, index) {
                                return {
                                    text: item.value,
                                    id: index,
                                    value: item.Label + ' ' + item.mobile,
                                    pid: item.pid,
                                    mobile: item.mobile,
                                    allow: item.allow
                                }
                            })
                        };
                    },
                    cache: true
                }
            })

            $('#SMS_patient').on('select2:select', function (e) {
                        e.preventDefault();
                        $("#SMS_patient").val(e.params.data.value);
                        $("#sms_pid").val(e.params.data.pid);
                        $("#sms_mobile").val(e.params.data.mobile);
                        $("#sms_allow").val(e.params.data.allow);
            });
        })

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

            $("#form_patient").focus();

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
            <?php if ($noteid) { ?>
            top.restoreSession();
            window.open('../../patient_file/summary/pnotes_print.php?noteid=' + <?php echo js_url($noteid); ?>, '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
            <?php } ?>

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
