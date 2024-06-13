<?php

/**
 * Patient Tracker (Patient Flow Board)
 *
 * This program displays the information entered in the Calendar program ,
 * allowing the user to change status and view those changed here and in the Calendar
 * Will allow the collection of length of time spent in each status
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Terry Hill <terry@lilysystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Ray Magauran <magauran@medexbank.com>
 * @copyright Copyright (c) 2015-2017 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Ray Magauran <magauran@medexbank.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../globals.php";
require_once "$srcdir/patient.inc.php";
require_once "$srcdir/options.inc.php";
require_once "$srcdir/patient_tracker.inc.php";
require_once "$srcdir/user.inc.php";
require_once "$srcdir/MedEx/API.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// These settings are sticky user preferences linked to a given page.
// mdsupport - user_settings prefix
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_new_window = prevSetting($uspfx, 'setting_new_window', 'setting_new_window', ' ');
// flow board and recall board share bootstrap settings:
$setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
$setting_selectors = prevSetting($uspfx, 'setting_selectors', 'setting_selectors', 'd-block');
$form_apptcat = prevSetting($uspfx, 'form_apptcat', 'form_apptcat', '');
$form_apptstatus = prevSetting($uspfx, 'form_apptstatus', 'form_apptstatus', '');
$facility = prevSetting($uspfx, 'form_facility', 'form_facility', '');
$provider = prevSetting($uspfx, 'form_provider', 'form_provider', $_SESSION['authUserID']);

if (
    ($_POST['setting_new_window'] ?? '') ||
    ($_POST['setting_bootstrap_submenu'] ?? '') ||
    ($_POST['setting_selectors'] ?? '')
) {
    // These are not form elements. We only ever change them via ajax, so exit now.
    exit();
}

//set default start date of flow board to value based on globals
if (!$GLOBALS['ptkr_date_range']) {
    $from_date = date('Y-m-d');
} elseif (!is_null($_REQUEST['form_from_date'] ?? null)) {
    $from_date = DateToYYYYMMDD($_REQUEST['form_from_date']);
} elseif (($GLOBALS['ptkr_start_date']) == 'D0') {
    $from_date = date('Y-m-d');
} elseif (($GLOBALS['ptkr_start_date']) == 'B0') {
    if (date(w) == GLOBALS['first_day_week']) {
        //today is the first day of the week
        $from_date = date('Y-m-d');
    } elseif ($GLOBALS['first_day_week'] == 0) {
        //Sunday
        $from_date = date('Y-m-d', strtotime('previous sunday'));
    } elseif ($GLOBALS['first_day_week'] == 1) {
        //Monday
        $from_date = date('Y-m-d', strtotime('previous monday'));
    } elseif ($GLOBALS['first_day_week'] == 6) {
        //Saturday
        $from_date = date('Y-m-d', strtotime('previous saturday'));
    }
} else {
    //shouldnt be able to get here...
    $from_date = date('Y-m-d');
}

//set default end date of flow board to value based on globals
if ($GLOBALS['ptkr_date_range']) {
    if (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'Y') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d'), date('Y') + $ptkr_time);
    } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'M') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m') + $ptkr_time, date('d'), date('Y'));
    } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'D') {
        $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
        $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d') + $ptkr_time, date('Y'));
    }

    $to_date = date('Y-m-d', $ptkr_future_time);
    $to_date = !is_null($_REQUEST['form_to_date'] ?? null) ? DateToYYYYMMDD($_REQUEST['form_to_date']) : $to_date;
} else {
    $to_date = date('Y-m-d');
}

$form_patient_name = !is_null($_POST['form_patient_name'] ?? null) ? $_POST['form_patient_name'] : null;
$form_patient_id = !is_null($_POST['form_patient_id'] ?? null) ? $_POST['form_patient_id'] : null;


$lres = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1", array('apptstat'));
while ($lrow = sqlFetchArray($lres)) {
    // if exists, remove the legend character
    if ($lrow['title'][1] == ' ') {
        $splitTitle = explode(' ', $lrow['title']);
        array_shift($splitTitle);
        $title = implode(' ', $splitTitle);
    } else {
        $title = $lrow['title'];
    }
    $statuses_list[$lrow['option_id']] = $title;
}

if ($GLOBALS['medex_enable'] == '1') {
    $query2 = "SELECT * FROM medex_icons";
    $iconed = sqlStatement($query2);
    while ($icon = sqlFetchArray($iconed)) {
        $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
    }
    $MedEx = new MedExApi\MedEx('MedExBank.com');
    $sql = "SELECT * FROM medex_prefs LIMIT 1";
    $preferences = sqlStatement($sql);
    $prefs = sqlFetchArray($preferences);
    $results = json_decode($prefs['status'], true);
    $logged_in = $results;
    $logged_in = $results;
    if (!empty($logged_in['token'])) {
        $current_events = xlt("On-line");
    } else {
        $current_events = xlt("Currently off-line");
    }
}

if (!($_REQUEST['flb_table'] ?? null)) {
    ?>
<html>
<head>
    <meta name="author" content="OpenEMR: MedExBank" />
    <?php Header::setupHeader(['datetime-picker', 'opener']); ?>
    <title><?php echo xlt('Flow Board'); ?></title>
    <script>
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <?php if ($_SESSION['language_direction'] == "rtl") { ?>
      <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_bootstrap_navbar.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } else { ?>
      <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/bootstrap_navbar.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } ?>

    <script src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>
</head>

<body>
    <?php
    if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu']))) {
        $logged_in = $MedEx->login();
        $MedEx->display->navigation($logged_in);
    }
    ?>

    <div class="container">
        <div class="row mt-3">
            <div class="col-sm-12 col-md-4">
                <div class="d-flex">
                    <div class="mb-2">
                        <!-- Visit Categories Section -->
                        <select id="form_apptcat" name="form_apptcat" class="form-control mr-1" onchange="refineMe('apptcat');">
                            <?php
                            $categories = fetchAppointmentCategories();
                            echo "<option value=''>" . xlt("Visit Categories") . "</option>";
                            while ($cat = sqlFetchArray($categories)) {
                                echo "<option value='" . attr($cat['id']) . "'";
                                if ($cat['id'] == ($_POST['form_apptcat'] ?? null)) {
                                    echo " selected='true' ";
                                }
                                echo ">" . xlt($cat['category']) . "</option>";
                            }
                            ?>
                        </select>
                        <!-- Visit Status Section -->
                        <select id="form_apptstatus" name="form_apptstatus" class="form-control" onchange="refineMe();">
                            <option value=""><?php echo xlt("Visit Status"); ?></option>
                            <?php
                            $apptstats = sqlStatement("SELECT * FROM list_options WHERE list_id = 'apptstat' AND activity = 1 ORDER BY seq");
                            while ($apptstat = sqlFetchArray($apptstats)) {
                                echo "<option value='" . attr($apptstat['option_id']) . "'";
                                if ($apptstat['option_id'] == ($_POST['form_apptstatus'] ?? null)) {
                                    echo " selected='true' ";
                                }
                                echo ">" . xlt($apptstat['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <!-- Facility Section -->
                        <select class="form-control mr-1" id="form_facility" name="form_facility"
                            <?php
                            $fac_sql = sqlStatement("SELECT * FROM facility ORDER BY id");
                            while ($fac = sqlFetchArray($fac_sql)) {
                                $true = ($fac['id'] == ($_POST['form_facility'] ?? null)) ? "selected=true" : '';
                                ($select_facs ?? null) ? $select_facs : $select_facs = '';
                                $select_facs .= "<option value=" . attr($fac['id']) . " " . $true . ">" . text($fac['name']) . "</option>\n";
                                ($count ?? null) ? $count_facs : $count_facs = 0;
                                $count_facs++;
                            }
                            if ($count_facs < '1') {
                                echo "disabled";
                            }
                            ?> onchange="refineMe('facility');">
                            <option value=""><?php echo xlt('All Facilities'); ?></option>
                            <?php echo $select_facs; ?>
                        </select>
                        <?php
                        // Build a drop-down list of ACTIVE providers.
                        $query = "SELECT id, lname, fname FROM users WHERE " .
                        "authorized = 1  AND active = 1 AND username > '' ORDER BY lname, fname"; #(CHEMED) facility filter
                        $ures = sqlStatement($query);
                        while ($urow = sqlFetchArray($ures)) {
                            $provid = $urow['id'];
                            ($select_provs ?? null) ? $select_provs : $select_provs = '';
                            $select_provs .= "    <option value='" . attr($provid) . "'";
                            if (isset($_POST['form_provider']) && $provid == $_POST['form_provider']) {
                                $select_provs .= " selected";
                            } elseif (!isset($_POST['form_provider']) && $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                $select_provs .= " selected";
                            }
                            $select_provs .= ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                            ($count_provs ?? null) ? $count_provs : $count_provs = 0;
                            $count_provs++;
                        }
                        ?>
                        <!-- Provider Section -->
                        <select class="form-control" id="form_provider" name="form_provider" <?php echo ($count_provs < "2") ? "disabled" : ""; ?> onchange="refineMe('provider');">
                            <option value="" selected><?php echo xlt('All Providers'); ?></option>
                            <?php echo $select_provs; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="d-flex w-100 flex-column">
                    <input type="text" placeholder="<?php echo xla('Patient Name'); ?>" class="form-control mb-1" id="form_patient_name" name="form_patient_name" value="<?php echo ($form_patient_name) ? attr($form_patient_name) : ""; ?>" onKeyUp="refineMe();" />
                    <input placeholder="<?php echo xla('Patient ID'); ?>" class="form-control" type="text" id="form_patient_id" name="form_patient_id" value="<?php echo ($form_patient_id) ? attr($form_patient_id) : ""; ?>" onKeyUp="refineMe();" />
                </div>
            </div>
            <div class="col-sm-12 col-md-4" class="<?php echo $style; ?>">
                <?php
                $type = ($GLOBALS['ptkr_date_range'] == '1') ? 'date' : 'hidden';
                $style = ($GLOBALS['ptkr_date_range'] == '1') ? '' : 'd-none';
                ?>
                <div class="d-flex w-100">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?php echo xlt("From{{Range}}"); ?></span>
                        </div>
                        <input type="text" id="form_from_date" name="form_from_date" class="datepicker form-control " value="<?php echo attr(oeFormatShortDate($from_date)); ?>">
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?php echo xlt("To{{Range}}"); ?></span>
                        </div>
                        <input type="text" id="form_to_date" name="form_to_date" class="datepicker form-control " value="<?php echo attr(oeFormatShortDate($to_date)); ?>">
                    </div>
                </div>
                <div class="d-flex w-100 mt-1">
                    <button id="filter_submit" type="submit" class="btn btn-secondary btn-block btn-filter"><?php echo xlt('Apply Filters'); ?></button>
                    <input type="hidden" id="kiosk" name="kiosk" value="<?php echo attr($_REQUEST['kiosk'] ?? ''); ?>" />
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-sm-12">
                <div id="message" class="warning"></div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">
        <div id="flb_selectors" class="<?php echo attr($setting_selectors); ?>;">
            <div class="p-4">
                <div class="showRFlow " id="show_flows" name="kiosk_hide">
                    <div name="div_response" id="div_response" class="nodisplay"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class=" row mx-auto divTable">
                    <div class="col-sm-12" id="loader">
                        <div class="spinner-border" role="status">
                            <span class="sr-only"><?php echo xlt('Loading data'); ?>...</span>
                        </div>
                        <h2><?php echo xlt('Loading data'); ?>...</h2>
                    </div>
                    <div id="flb_table" name="flb_table" class="w-100">
            <?php
} else {
    //end of if !$_REQUEST['flb_table'] - this is the table we fetch via ajax during a refreshMe() call
    // get all appts for date range and refine view client side.  very fast...
    $appointments = array();
    $datetime = date("Y-m-d H:i:s");
    $appointments = fetch_Patient_Tracker_Events($from_date, $to_date, '', '', '', '', $form_patient_name, $form_patient_id);
    $appointments = sortAppointments($appointments, 'date', 'time');
    //grouping of the count of every status
    $appointments_status = getApptStatus($appointments);

    $chk_prov = array();  // list of providers with appointments
    // Scan appointments for additional info
    foreach ($appointments as $apt) {
        $chk_prov[$apt['uprovider_id']] = $apt['ulname'] . ', ' . $apt['ufname'] . ' ' . $apt['umname'];
    }
    ?>
            <div class="col-sm-12 m-1 d-flex justify-content-between align-items-center">
                <div class="flex-fill">
                    <span id="status_summary">
                        <ul class="list-group list-group-horizontal">
                            <?php
                            echo "<li class='list-group-item bg-light d-flex justify-content-between align-items-center'>";
                            echo text(xlt('Total patients'));
                            echo "<span class='ml-2 badge badge-primary'>" . text($appointments_status['count_all']) . "</span>";
                            echo "</li>";
                            unset($appointments_status['count_all']);
                            foreach ($appointments_status as $status_symbol => $count) {
                                echo '<li class="list-group-item bg-light d-flex justify-content-between align-items-center">';
                                echo '<span>' . text(xl_list_label($statuses_list[$status_symbol])) . '</span>';
                                echo '<span class="ml-2 badge badge-primary">' . text($count) . '</span>';
                                echo "</li>";
                            }
                            ?>
                        </ul>
                    </span>
                </div>
                <div id="pull_kiosk_right">
                    <div class="collapse mt-2 mb-2" id="collapseSetting">
                        <input type='checkbox' name='setting_new_window' id='setting_new_window' value='<?php echo attr($setting_new_window); ?>' <?php echo attr($setting_new_window); ?> />
                        <?php echo xlt('Open Patient in New Window'); ?>
                    </div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary" onclick="toggleSelectors();" id="flb_caret"><i class="fa fa-chevron-up"></i>&nbsp;<?php echo xlt("Toggle Filter Areas"); ?></button>
                        <button class="btn btn-secondary btn-setting" data-toggle="collapse"><?php echo xlt("Setting"); ?></button>
                        <button class="btn btn-secondary btn-refresh" id="refreshme"><?php echo xlt("Refresh"); ?></button>
                        <button class='btn btn-secondary btn-print' onclick="print_FLB();"><?php echo xlt('Print'); ?></button>
                        <button class='btn btn-secondary' onclick="kiosk_FLB();"><i class="fa fa-tv pr-1"></i>&nbsp;<?php echo xlt('Kiosk'); ?></button>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-light">
                    <thead>
                        <tr>
                            <?php if ($GLOBALS['ptkr_show_pid']) { ?>
                                <th name="kiosk_hide"><?php echo xlt('PID'); ?></th>
                            <?php } ?>
                            <th style="max-width: 150px;"><?php echo xlt('Patient'); ?></th>
                            <?php if ($GLOBALS['ptkr_visit_reason'] == '1') { ?>
                                <th name="kiosk_hide"><?php echo xlt('Reason'); ?></th>
                            <?php } ?>
                            <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                                <th name="kiosk_hide"><?php echo xlt('Encounter'); ?></th>
                            <?php } ?>

                            <?php if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                                <th name="kiosk_hide"><?php echo xlt('Appt Date'); ?></th>
                            <?php } ?>
                            <th><?php echo xlt('Appt Time'); ?></th>
                            <th><?php echo xlt('Arrive Time'); ?></th>
                            <th><?php echo xlt('Appt Status'); ?></th>
                            <th><?php echo xlt('Current Status'); ?></th>
                            <th name="kiosk_hide"><?php echo xlt('Visit Type'); ?></th>
                            <?php if (count($chk_prov) > 1) { ?>
                                <th><?php echo xlt('Provider'); ?></th>
                            <?php } ?>
                            <th><?php echo xlt('Total Time'); ?></th>
                            <th><?php echo xlt('Check Out Time'); ?></th>
                            <?php if ($GLOBALS['ptkr_show_staff']) : ?>
                                <th name="kiosk_hide"><?php echo xlt('Updated By'); ?></th>
                            <?php endif;
                            if ($_REQUEST['kiosk'] != '1' && $GLOBALS['drug_screen']) : ?>
                                <th name="kiosk_hide"><?php echo xlt('Random Drug Screen'); ?></th>
                                <th name="kiosk_hide"><?php echo xlt('Drug Screen Completed'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $prev_appt_date_time = "";
                    foreach ($appointments as $appointment) {
                        // Collect appt date and set up squashed date for use below
                        $date_appt = $appointment['pc_eventDate'];
                        $date_squash = str_replace("-", "", $date_appt);
                        if (empty($appointment['room']) && ($logged_in ?? null) && ($setting_bootstrap_submenu != 'hide')) {
                            //Patient has not arrived yet, display MedEx Reminder info
                            //one icon per type of response.
                            //If there was a SMS dialog, display it as a mouseover/title
                            //Display date received also as mouseover title.
                            $other_title = '';
                            $title = '';
                            $icon2_here = '';
                            $appt['stage'] = '';
                            $icon_here = array();
                            $prog_text = '';
                            $FINAL = '';

                            $query = "SELECT * FROM medex_outgoing WHERE msg_pc_eid =? ORDER BY medex_uid asc";
                            $myMedEx = sqlStatement($query, array($appointment['eid']));
                            /**
                             * Each row for this pc_eid in the medex_outgoing table represents an event.
                             * Every event is recorded in $prog_text.
                             * A modality is represented by an icon (eg mail icon, phone icon, text icon).
                             * The state of the Modality is represented by the color of the icon:
                             *      CONFIRMED       =   green
                             *      READ            =   blue
                             *      FAILED          =   pink
                             *      SENT/in process =   yellow
                             *      SCHEDULED       =   white
                             * Icons are displayed in their highest state.
                             */
                            while ($row = sqlFetchArray($myMedEx)) {
                                // Need to convert $row['msg_date'] to localtime (stored as GMT) & then oeFormatShortDate it.
                                // I believe there is a new GLOBAL for server timezone???  If so, it will be easy.
                                // If not we need to import it from Medex through medex_preferences.  It should really be in openEMR though.
                                // Delete when we figure this out.
                                $other_title = '';
                                if (!empty($row['msg_extra_text'])) {
                                    $local = attr($row['msg_extra_text']) . " |";
                                }
                                $prog_text .= attr(oeFormatShortDate($row['msg_date'])) . " :: " . attr($row['msg_type']) . " : " . attr($row['msg_reply']) . " | " . $local . " |";

                                if ($row['msg_reply'] == 'Other') {
                                    $other_title .= $row['msg_extra_text'] . "\n";
                                    $icon_extra .= str_replace(
                                        "EXTRA",
                                        attr(oeFormatShortDate($row['msg_date'])) . "\n" . xla('Patient Message') . ":\n" . attr($row['msg_extra_text']) . "\n",
                                        $icons[$row['msg_type']]['EXTRA']['html']
                                    );
                                    continue;
                                } elseif ($row['msg_reply'] == 'CANCELLED') {
                                    $appointment[$row['msg_type']]['stage'] = "CANCELLED";
                                    $icon_here[$row['msg_type']] = '';
                                } elseif ($row['msg_reply'] == "FAILED") {
                                    $appointment[$row['msg_type']]['stage'] = "FAILED";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'];
                                } elseif (($row['msg_reply'] == "CONFIRMED") || ($appointment[$row['msg_type']]['stage'] == "CONFIRMED")) {
                                    $appointment[$row['msg_type']]['stage'] = "CONFIRMED";
                                    $icon_here[$row['msg_type']]  = $icons[$row['msg_type']]['CONFIRMED']['html'];
                                } elseif (($row['msg_reply'] == "READ") || ($appointment[$row['msg_type']]['stage'] == "READ")) {
                                    $appointment[$row['msg_type']]['stage'] = "READ";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'];
                                } elseif (($row['msg_reply'] == "SENT") || ($appointment[$row['msg_type']]['stage'] == "SENT")) {
                                    $appointment[$row['msg_type']]['stage'] = "SENT";
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'];
                                } elseif (($row['msg_reply'] == "To Send") || (empty($appointment['stage']))) {
                                    if (
                                        ($appointment[$row['msg_type']]['stage'] != "CONFIRMED") &&
                                        ($appointment[$row['msg_type']]['stage'] != "READ") &&
                                        ($appointment[$row['msg_type']]['stage'] != "SENT") &&
                                        ($appointment[$row['msg_type']]['stage'] != "FAILED")
                                    ) {
                                        $appointment[$row['msg_type']]['stage'] = "QUEUED";
                                        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'];
                                    }
                                }
                                //these are additional icons if present
                                if ($row['msg_reply'] == "CALL") {
                                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['CALL']['html'];
                                    if (($appointment['allow_sms'] != "NO") && ($appointment['phone_cell'] > '')) {
                                        $icon_4_CALL = "<span class='input-group-addon'
                                                            onclick='SMS_bot(" . attr_js($row['msg_pid']) . ");'>
                                                            <i class='fas fa-sms'></i>
                                                        </span>";
                                    }
                                } elseif ($row['msg_reply'] == "STOP") {
                                    $icon2_here .= $icons[$row['msg_type']]['STOP']['html'];
                                } elseif ($row['msg_reply'] == "Other") {
                                    $icon2_here .= $icons[$row['msg_type']]['Other']['html'];
                                }
                            }
                            //if pc_apptstatus == '-', update it now to=status
                            if (!empty($other_title)) {
                                $appointment['messages'] = $icon2_here . $icon_extra;
                            }
                        }

                        // Collect variables and do some processing
                        $docname = $chk_prov[$appointment['uprovider_id']];
                        if (strlen($docname) <= 3) {
                            continue;
                        }
                        $ptname = $appointment['lname'] . ', ' . $appointment['fname'] . ' ' . $appointment['mname'];
                        $ptname_short = $appointment['fname'][0] . " " . $appointment['lname'][0];
                        $appt_enc = $appointment['encounter'];
                        $appt_eid = (!empty($appointment['eid'])) ? $appointment['eid'] : $appointment['pc_eid'];
                        $appt_pid = (!empty($appointment['pid'])) ? $appointment['pid'] : $appointment['pc_pid'];
                        if ($appt_pid == 0) {
                            continue; // skip when $appt_pid = 0, since this means it is not a patient specific appt slot
                        }
                        $status = (!empty($appointment['status']) && (!is_numeric($appointment['status']))) ? $appointment['status'] : $appointment['pc_apptstatus'];
                        $appt_room = (!empty($appointment['room'])) ? $appointment['room'] : $appointment['pc_room'];
                        $appt_time = (!empty($appointment['appttime'])) ? $appointment['appttime'] : $appointment['pc_startTime'];
                        $tracker_id = $appointment['id'];
                        // reason for visit
                        if ($GLOBALS['ptkr_visit_reason']) {
                            $reason_visit = $appointment['pc_hometext'];
                        }
                        $newarrive = collect_checkin($tracker_id);
                        $newend = collect_checkout($tracker_id);
                        $colorevents = (collectApptStatusSettings($status));
                        $bgcolor = $colorevents['color'];
                        $statalert = $colorevents['time_alert'];
                        // process the time to allow items with a check out status to be displayed
                        if (is_checkout($status) && (($GLOBALS['checkout_roll_off'] > 0) && strlen($form_apptstatus) != 1)) {
                            $to_time = strtotime($newend);
                            $from_time = strtotime($datetime);
                            $display_check_out = round(abs($from_time - $to_time) / 60, 0);
                            if ($display_check_out >= $GLOBALS['checkout_roll_off']) {
                                continue;
                            }
                        }

                        echo '<tr data-apptstatus="' . attr($appointment['pc_apptstatus']) . '"
                            data-apptcat="' . attr($appointment['pc_catid']) . '"
                            data-facility="' . attr($appointment['pc_facility']) . '"
                            data-provider="' . attr($appointment['uprovider_id']) . '"
                            data-pid="' . attr($appointment['pc_pid']) . '"
                            data-pname="' . attr($ptname) . '"
                            class="text-small"
                            style="background-color:' . attr($bgcolor) . ';" >';

                        if ($GLOBALS['ptkr_show_pid']) {
                            ?>
                            <td class="detail " name="kiosk_hide">
                                <?php echo text($appt_pid); ?>
                            </td>
                            <?php
                        }

                        ?>
                        <td class="detail " name="kiosk_hide">
                            <a href="#" onclick="return topatient(<?php echo attr_js($appt_pid); ?>,<?php echo attr_js($appt_enc); ?>)">
                                <?php echo text($ptname); ?>
                            </a>
                        </td>
                        <td class="detail " style="white-space: normal;" name="kiosk_show">
                            <a href="#" onclick="return topatient(<?php echo attr_js($appt_pid); ?>,<?php echo attr_js($appt_enc); ?>)">
                                <?php echo text($ptname_short); ?>
                            </a>
                        </td>
                        <!-- reason -->
                        <?php if ($GLOBALS['ptkr_visit_reason']) { ?>
                            <td class="detail " name="kiosk_hide">
                                <?php echo text($reason_visit) ?>
                            </td>
                        <?php } ?>
                        <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                            <td class="detail " name="kiosk_hide">
                                <?php echo ($appt_enc != 0) ? text($appt_enc) : ''; ?>
                            </td>
                        <?php } ?>
                        <?php if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                            <td class="detail " name="kiosk_hide">
                                <?php echo text(oeFormatShortDate($appointment['pc_eventDate'])); ?>
                            </td>
                        <?php } ?>
                        <td class="detail ">
                            <?php echo text(oeFormatTime($appt_time)); ?>
                        </td>
                        <td class="detail ">
                            <?php echo ($newarrive) ? text(oeFormatTime($newarrive)) : ''; ?>
                        </td>
                        <td class="detail  ">
                            <?php $_onclick = (empty($tracker_id)) ? "calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")" : "bpopup(" . attr_js($tracker_id) . ")"; ?>
                            <a href="#" onclick="return <?php echo $_onclick; ?>">
                                <?php
                                if ($appointment['room'] > '') {
                                    echo text(getListItemTitle('patient_flow_board_rooms', $appt_room));
                                } else {
                                    echo text(getListItemTitle("apptstat", $status)); // drop down list for appointment status
                                }
                                ?>
                            </a>
                        </td>

                        <?php
                        //time in current status
                        $to_time = strtotime(date("Y-m-d H:i:s"));
                        $yestime = '0';
                        if (strtotime($newend) != '') {
                            $from_time = strtotime($newarrive);
                            $to_time = strtotime($newend);
                            $yestime = '0';
                        } else {
                            $from_time = (($appointment['start_datetime'] ?? null) ? strtotime($appointment['start_datetime']) : null);
                            $yestime = '1';
                        }

                        $timecheck = round(abs($to_time - ($from_time ?? null)) / 60, 0);
                        if ($timecheck >= $statalert && ($statalert > '0')) { // Determine if the time in status limit has been reached.
                            echo "<td class='js-blink-infinite' nowrap>  "; // and if so blink
                        } else {
                            echo "<td class='detail' nowrap> "; // and if not do not blink
                        }
                        if (($yestime == '1') && ($timecheck >= 1) && (strtotime($newarrive) != '')) {
                            echo text($timecheck . ' ' . ($timecheck >= 2 ? xl('minutes') : xl('minute')));
                        } elseif (($icon_here ?? null) || ($icon2_here ?? null)) {
                            echo "<span onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . implode($icon_here) . $icon2_here . "</span> " . $icon_4_CALL;
                        } elseif ($logged_in ?? null) {
                            $pat = $MedEx->display->possibleModalities($appointment);
                            echo "<span onclick='return calendarpopup(" . attr_js($appt_eid) . "," . attr_js($date_squash) . ")'>" . $pat['SMS'] . $pat['AVM'] . $pat['EMAIL'] . "</span>";
                        }
                        //end time in current status
                        echo "</td>";
                        ?>
                        <td class="detail " name="kiosk_hide">
                            <?php echo xlt($appointment['pc_title']); ?>
                        </td>
                        <?php
                        if (count($chk_prov) > 1) { ?>
                            <td class="detail">
                                <?php echo text($docname); ?>
                            </td>
                            <?php
                        } ?>
                        <td class="detail">
                            <?php
                            // total time in practice
                            if (strtotime($newend) != '') {
                                $from_time = strtotime($newarrive);
                                $to_time = strtotime($newend);
                            } else {
                                $from_time = strtotime($newarrive);
                                $to_time = strtotime(date("Y-m-d H:i:s"));
                            }
                            $timecheck2 = round(abs($to_time - $from_time) / 60, 0);
                            if (strtotime($newarrive) != '' && ($timecheck2 >= 1)) {
                                echo text($timecheck2 . ' ' . ($timecheck2 >= 2 ? xl('minutes') : xl('minute')));
                            }
                            // end total time in practice
                            echo text($appointment['pc_time'] ?? ''); ?>
                        </td>
                        <td class="detail ">
                            <?php
                            if (strtotime($newend) != '') {
                                echo text(oeFormatTime(substr($newend, 11))) ;
                            }
                            ?>
                        </td>
                        <?php
                        if ($GLOBALS['ptkr_show_staff'] == '1') {
                            ?>
                                <td class="detail " name="kiosk_hide">
                                    <?php echo text($appointment['user']) ?>
                                </td>
                            <?php
                        }
                        if ($GLOBALS['drug_screen']) {
                            if (strtotime($newarrive) != '') { ?>
                                <td class="detail " name="kiosk_hide">
                                    <?php
                                    if ($appointment['random_drug_test'] == '1') {
                                        echo xlt('Yes');
                                    } else {
                                        echo xlt('No');
                                    } ?>
                                </td>
                                <?php
                            } else { ?>
                                <td class="detail " name="kiosk_hide"></td>
                            <?php }
                            if (strtotime($newarrive) != '' && $appointment['random_drug_test'] == '1') { ?>
                                <td class="detail " name="kiosk_hide">
                                    <?php
                                    if (strtotime($newend) != '') {
                                        // the following block allows the check box for drug screens to be disabled once the status is check out ?>
                                        <input type='checkbox' disabled='disable' class="drug_screen_completed" id="<?php echo attr($appointment['pt_tracker_id']) ?>" <?php echo ($appointment['drug_screen_completed'] == "1") ? "checked" : ""; ?> />
                                        <?php
                                    } else {
                                        ?>
                                        <input type='checkbox' class="drug_screen_completed" id='<?php echo attr($appointment['pt_tracker_id']) ?>' name="drug_screen_completed" <?php echo ($appointment['drug_screen_completed'] == "1") ? "checked" : ""; ?> />
                                        <?php
                                    } ?>
                                </td>
                                <?php
                            } else { ?>
                                <td class="detail " name="kiosk_hide"></td>
                            <?php }
                        }
                        ?>
                        </tr>
                        <?php
                    } //end foreach
                    ?>
                    </tbody>
                </table>
            </div>

    <?php
}
if (!($_REQUEST['flb_table'] ?? null)) { ?>
                </div>
            </div>
        </div>
    </div><?php //end container ?>
    <!-- form used to open a new top level window when a patient row is clicked -->
    <form name='fnew' method='post' target='_blank' action='../main/main_screen.php?auth=login&site=<?php echo attr_url($_SESSION['site_id']); ?>'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type='hidden' name='patientID' value='0' />
        <input type='hidden' name='encounterID' value='0' />
    </form>

    <?php echo myLocalJS(); ?>
</body>
</html>
    <?php
} //end of second !$_REQUEST['flb_table']


exit;

function myLocalJS()
{
    ?>
    <script>
        var auto_refresh = null;
        //this can be refined to redact HIPAA material using @media print options.
        window.parent.$("[name='flb']").attr('allowFullscreen', 'true');
        $("[name='kiosk_hide']").show();
        $("[name='kiosk_show']").hide();

        function print_FLB() {
            window.print();
        }

        function toggleSelectors() {
            top.restoreSession();
            const opts = {
                setting_selectors: $("#flb_selectors").css('display') === 'none' ? 'd-block' : 'd-none',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            };
            $.post("<?php echo $GLOBALS['webroot'] . "/interface/patient_tracker/patient_tracker.php"; ?>", opts).done(
                function (data) {
                    $("#flb_selectors").slideToggle();
                }
            );
            $("#print_caret").toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        }

        /**
         * This function refreshes the whole flb_table according to our to/from dates.
         */
        function refreshMe(fromTimer) {

            if (typeof fromTimer === 'undefined' || !fromTimer) {
                //Show loader in the first loading or manual loading not by timer
                $("#flb_table").html('');
                $('#loader').show();
                skip_timeout_reset = 0;
            } else {
                skip_timeout_reset = 1;
            }

            var startRequestTime = Date.now();
            top.restoreSession();
            var posting = $.post('../patient_tracker/patient_tracker.php', {
                flb_table: '1',
                form_from_date: $("#form_from_date").val(),
                form_to_date: $("#form_to_date").val(),
                form_facility: $("#form_facility").val(),
                form_provider: $("#form_provider").val(),
                form_apptstatus: $("#form_apptstatus").val(),
                form_patient_name: $("#form_patient_name").val(),
                form_patient_id: $("#form_patient_id").val(),
                form_apptcat: $("#form_apptcat").val(),
                kiosk: $("#kiosk").val(),
                skip_timeout_reset: skip_timeout_reset,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }).done(
                function (data) {
                    //minimum 400 ms of loader (In the first loading or manual loading not by timer)
                    if((typeof fromTimer === 'undefined' || !fromTimer) && Date.now() - startRequestTime < 400 ){
                        setTimeout(drawTable, 500, data);
                    } else {
                        drawTable(data)
                    }
                });
        }

        function drawTable(data) {

            $('#loader').hide();
            $("#flb_table").html(data);
            if ($("#kiosk").val() === '') {
                $("[name='kiosk_hide']").show();
                $("[name='kiosk_show']").hide();
            } else {
                $("[name='kiosk_hide']").hide();
                $("[name='kiosk_show']").show();
            }
            refineMe();
            initTableButtons();
        }

        function refreshme() {
            // Just need this to support refreshme call from the popup used for recurrent appt
            refreshMe();
        }

        /**
         * This function hides all then shows only the flb_table rows that match our selection, client side.
         * It is called on initial load, on refresh and 'onchange/onkeyup' of a flow board parameter.
         */
        function refineMe() {
            var apptcatV = $("#form_apptcat").val();
            var apptstatV = $("#form_apptstatus").val();
            var facV = $("#form_facility").val();
            var provV = $("#form_provider").val();
            var pidV = String($("#form_patient_id").val());
            var pidRE = new RegExp(pidV, 'g');
            var pnameV = $("#form_patient_name").val();
            var pnameRE = new RegExp(pnameV, 'ig');

            //and hide what we don't want to show
            $('#flb_table tbody tr').hide().filter(function () {
                var d = $(this).data();
                meets_cat = (apptcatV === '') || (apptcatV == d.apptcat);
                meets_stat = (apptstatV === '') || (apptstatV == d.apptstatus);
                meets_fac = (facV === '') || (facV == d.facility);
                meets_prov = (provV === '') || (provV == d.provider);
                meets_pid = (pidV === '');
                if ((pidV > '') && pidRE.test(d.pid)) {
                    meets_pid = true;
                }
                meets_pname = (pnameV === '');
                if ((pnameV > '') && pnameRE.test(d.pname)) {
                    meets_pname = true;
                }
                return meets_pname && meets_pid && meets_cat && meets_stat && meets_fac && meets_prov;
            }).show();
        }

        // popup for patient tracker status
        function bpopup(tkid) {
            top.restoreSession();
            dlgopen('../patient_tracker/patient_tracker_status.php?tracker_id=' + encodeURIComponent(tkid) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 250);
            return false;
        }

        // popup for calendar add edit
        function calendarpopup(eid, date_squash) {
            top.restoreSession();
            dlgopen('../main/calendar/add_edit_event.php?eid=' + encodeURIComponent(eid) + '&date=' + encodeURIComponent(date_squash), '_blank', 775, 500);
            return false;
        }

        // used to display the patient demographic and encounter screens
        function topatient(newpid, enc) {
            if ($('#setting_new_window').val() === 'checked') {
                openNewTopWindow(newpid, enc);
            }
            else {
                top.restoreSession();
                if (enc > 0) {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid) + "&set_encounterid=" + encodeURIComponent(enc);
                }
                else {
                    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
                }
            }
        }

        // opens the demographic and encounter screens in a new window
        function openNewTopWindow(newpid, newencounterid) {
            document.fnew.patientID.value = newpid;
            document.fnew.encounterID.value = newencounterid;
            top.restoreSession();
            document.fnew.submit();
        }

        //opens the two-way SMS phone app
        /**
         * @return {boolean}
         */
        function SMS_bot(pid) {
            top.restoreSession();
            var from = <?php echo js_escape($from_date ?? ''); ?>;
            var to = <?php echo js_escape($to_date ?? ''); ?>;
            var oefrom = <?php echo js_escape(oeFormatShortDate($from_date ?? null)); ?>;
            var oeto = <?php echo js_escape(oeFormatShortDate($to_date ?? null)); ?>;
            window.open('../main/messages/messages.php?nomenu=1&go=SMS_bot&pid=' + encodeURIComponent(pid) + '&to=' + encodeURIComponent(to) + '&from=' + encodeURIComponent(from) + '&oeto=' + encodeURIComponent(oeto) + '&oefrom=' + encodeURIComponent(oefrom), 'SMS_bot', 'width=370,height=600,resizable=0');
            return false;
        }

        function kiosk_FLB() {
            $("#kiosk").val('1');
            $("[name='kiosk_hide']").hide();
            $("[name='kiosk_show']").show();

            var i = document.getElementById("flb_table");
            // go full-screen
            if (i.requestFullscreen) {
                i.requestFullscreen();
            } else if (i.webkitRequestFullscreen) {
                i.webkitRequestFullscreen();
            } else if (i.mozRequestFullScreen) {
                i.mozRequestFullScreen();
            } else if (i.msRequestFullscreen) {
                i.msRequestFullscreen();
            }
            // refreshMe();
        }

        function KioskUp() {
            var kv = $("#kiosk").val();
            if (kv == '0') {
                $("#kiosk").val('1');
                $("[name='kiosk_hide']").show();
                $("[name='kiosk_show']").hide();
            } else {
                $("#kiosk").val('0');
                $("[name='kiosk_hide']").hide();
                $("[name='kiosk_show']").show();
            }
        }

        $(function () {
            refreshMe();
            $("#kiosk").val('');
            $("[name='kiosk_hide']").show();
            $("[name='kiosk_show']").hide();

            onresize = function () {
                var state = 1 >= outerHeight - innerHeight ? "fullscreen" : "windowed";
                if (window.state === state) return;
                window.state = state;
                var event = document.createEvent("Event");
                event.initEvent(state, true, true);
                window.dispatchEvent(event);
            };

            ["fullscreenchange", "webkitfullscreenchange", "mozfullscreenchange", "msfullscreenchange"].forEach(
                eventType => document.addEventListener(eventType, KioskUp, false)
            );

            <?php if ($GLOBALS['pat_trkr_timer'] != '0') { ?>
                var reftime = <?php echo js_escape($GLOBALS['pat_trkr_timer']); ?>;
                var parsetime = reftime.split(":");
                parsetime = (parsetime[0] * 60) + (parsetime[1] * 1) * 1000;
                if (auto_refresh) clearInteral(auto_refresh);
                auto_refresh = setInterval(function () {
                    refreshMe(true) // this will run after every parsetime seconds
                }, parsetime);
            <?php } ?>

            $('.js-blink-infinite').each(function () {
                // set up blinking text
                var elem = $(this);
                setInterval(function () {
                    if (elem.css('visibility') === 'hidden') {
                        elem.css('visibility', 'visible');
                    } else {
                        elem.css('visibility', 'hidden');
                    }
                }, 500);
            });
            // toggle of the check box status for drug screen completed and ajax call to update the database
            $('body').on('click', '.drug_screen_completed', function () {
                top.restoreSession();
                testcomplete_toggle = (this.checked) ? "true" : "false";
                $.post("../../library/ajax/drug_screen_completed.php", {
                    trackerid: this.id,
                    testcomplete: testcomplete_toggle,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                });
            });

            // mdsupport - Immediately post changes to setting_new_window
            $('body').on('click', '#setting_new_window', function () {
                $('#setting_new_window').val(this.checked ? 'checked' : ' ');
                top.restoreSession();
                $.post("<?php echo $GLOBALS['webroot'] . "/interface/patient_tracker/patient_tracker.php"; ?>", {
                    setting_new_window: $('#setting_new_window').val(),
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }).done(
                    function (data) {
                });
            });

            $('#filter_submit').click(function (e) {
                e.preventDefault;
                refreshMe();
            });

            $('[data-toggle="tooltip"]').tooltip();

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

        });

        function initTableButtons() {
            $('#refreshme').click(function () {
                refreshMe();
                refineMe();
            });
        }

        initTableButtons();

    </script>
<?php }
?>
