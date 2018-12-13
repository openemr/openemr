<?php
    /**
     *  /interface/main/mobile/m_flow.php
     *
     *  Mobile Flow Board for OpenEMR via MedEx
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
     *
     * LICENSE: This program is free software: you can redistribute it and/or modify
     *  it under the terms of the GNU Affero General Public License as
     *  published by the Free Software Foundation, either version 3 of the
     *  License, or (at your option) any later version.
     *
     *  This program is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU Affero General Public License for more details.
     *
     *  You should have received a copy of the GNU Affero General Public License
     *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     * @package OpenEMR
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    require_once('../../globals.php');
    require_once "$srcdir/patient.inc";
    require_once "$srcdir/options.inc.php";
    require_once("m_functions.php");
    require_once "$srcdir/patient_tracker.inc.php";
    require_once "$srcdir/user.inc";
    require_once "$srcdir/MedEx/API.php";
    
    use OpenEMR\Core\Header;
    
    $MedEx = new MedExApi\MedEx('medexbank.com');
    
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $script_version = $detect->getScriptVersion();
    
    $desktop ="";
    $categories = array();
    $display="flow";
    $doc =array();
    
    if (!empty($_GET['desktop'])) {
        $desktop = $_GET['desktop'];
    }

// If “Go to full website” link is clicked, redirect mobile user to main website
    if (!empty($_SESSION['desktop']) || ($device_type == 'computer')) {
        $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
        header("Location:" . $desktop_url);
    }

//set default start date of flow board to value based on globals
if (!$GLOBALS['ptkr_date_range']) {
    $from_date = date('Y-m-d');
} elseif (!is_null($_REQUEST['form_from_date'])) {
    $from_date = DateToYYYYMMDD($_REQUEST['form_from_date']);
} elseif (($GLOBALS['ptkr_start_date'])=='D0') {
    $from_date = date('Y-m-d');
} elseif (($GLOBALS['ptkr_start_date'])=='B0') {
    if (date(w)==GLOBALS['first_day_week']) {
        //today is the first day of the week
        $from_date = date('Y-m-d');
    } elseif ($GLOBALS['first_day_week']==0) {
        //Sunday
        $from_date = date('Y-m-d', strtotime('previous sunday'));
    } elseif ($GLOBALS['first_day_week']==1) {
        //Monday
        $from_date = date('Y-m-d', strtotime('previous monday'));
    } elseif ($GLOBALS['first_day_week']==6) {
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
    $to_date = !is_null($_REQUEST['form_to_date']) ? DateToYYYYMMDD($_REQUEST['form_to_date']) : $to_date;
} else {
    $to_date = date('Y-m-d');
}

$form_patient_name = !is_null($_POST['form_patient_name']) ? $_POST['form_patient_name'] : null;
$form_patient_id = !is_null($_POST['form_patient_id']) ? $_POST['form_patient_id'] : null;


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
    $logged_in=$results;
    if (!empty($prefs)) {
        foreach ($results['campaigns']['events'] as $event) {
            if ($event['M_group'] != 'REMINDER') {
                continue;
            }
            $icon = $icons[$event['M_type']]['SCHEDULED']['html'];
            if ($event['E_timing'] == '1') {
                $action = xl("before");
            }
            if ($event['E_timing'] == '2') {
                $action = xl("before (PM)");
            }
            if ($event['E_timing'] == '3') {
                $action = xl("after");
            }
            if ($event['E_timing'] == '4') {
                $action = xl("after (PM)");
            }
            $days = ($event['E_fire_time'] == '1') ? xl("day") : xl("days");
            $current_events .= $icon . " &nbsp; " . (int)$event['E_fire_time'] . " " . text($days) . " " . text($action) . "<br />";
        }
    } else {
        $current_events = $icons['SMS']['FAILED']['html'] . " " . xlt("Currently off-line");
    }
}



?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>

<script>
    var projects = [
        {
            label: "<?php echo xla('Select Document Category'); ?>"
        }<?php
        $categories =  sqlStatement("Select * from categories");
        
        while ($row1 = sqlFetchArray($categories)) {
            echo ',
                {
                    label: "'.attr($row1['name']).'",
                    catID: "'.attr($row1['id']).'"
                }';
        }
        ?>
    ];
    
    
    var reply = [];
    <?php
    if (!empty($setting_mRoom)) {
        echo "var mRoom = ".attr($setting_mRoom).";";
    } else {
        echo "var mRoom;";
    }
    ?>
    // used to display the patient demographic and encounter screens
    function topatient(newpid, enc) {
        if ($('#setting_new_window').val() === 'checked') {
            openNewTopWindow(newpid, enc);
        } else {
            top.restoreSession();
            if (enc > 0) {
                window.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
            } else {
                window.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
            }
        }
    }
</script>

<body style="background-color: #fff;" >
<?php common_header($display);
    
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
<div class="container-fluid">
    
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
            <img src="<?php echo $webroot; ?>/public/images/flowboard.png" id="head_img" alt="OpenEMR <?php echo xla('Calendar'); ?>">
        </div>

<div class="col-sm-12 text-center" style='margin:5px;'>
                <span class="hidden-xs" id="status_summary">
                    <?php
                        $statuses_output = "<span style='margin:0 10px;'><em>" . xlt('Total patients') . ':</em> <span class="badge">' . text($appointments_status['count_all']) . "</span></span>";
                        unset($appointments_status['count_all']);
                        foreach ($appointments_status as $status_symbol => $count) {
                            $statuses_output .= " | <span style='margin:0 10px;'><em>" . text(xl_list_label($statuses_list[$status_symbol])) . ":</em> <span class='badge'>" . text($count) . "</span></span>";
                        }
                        echo $statuses_output;
                    ?>
                </span>
</div>

<div class="col-sm-12 textclear" >
    
    <table class="table table-responsive table-condensed table-hover table-bordered custom-file-upload">
        <thead>
        <tr bgcolor="#cccff" class="small bold  text-center">
            <?php if ($GLOBALS['ptkr_show_pid']) { ?>
                <td class="dehead hidden-xs text-center" name="kiosk_hide">
                    <?php echo xlt('PID'); ?>
                </td>
            <?php } ?>
            <td class="dehead text-center" style="max-width:150px;">
                <?php echo xlt('Patient'); ?>
            </td>
            <?php if ($GLOBALS['ptkr_visit_reason'] == '1') { ?>
                <td class="dehead hidden-xs text-center" name="kiosk_hide">
                    <?php echo xlt('Reason'); ?>
                </td>
            <?php } ?>
            <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                <td class="dehead text-center hidden-xs hidden-sm" name="kiosk_hide">
                    <?php echo xlt('Encounter'); ?>
                </td>
            <?php } ?>
            
            <?php if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                <td class="dehead hidden-xs text-center" name="kiosk_hide">
                    <?php echo xlt('Appt Date'); ?>
                </td>
            <?php } ?>
            <td class="dehead text-center">
                <?php echo xlt('Appt Time'); ?>
            </td>
            <td class="dehead hidden-xs text-center">
                <?php echo xlt('Arrive Time'); ?>
            </td>
            <td class="dehead visible-xs hidden-sm hidden-md hidden-lg text-center">
                <?php echo xlt('Arrival'); ?>
            </td>
            <td class="dehead hidden-xs text-center">
                <?php echo xlt('Appt Status'); ?>
            </td>
            <td class="dehead hidden-xs text-center">
                <?php echo xlt('Current Status'); ?>
            </td>
            <td class="dehead visible-xs hidden-sm hidden-md hidden-lg text-center">
                <?php echo xlt('Current'); ?>
            </td>
            <td class="dehead hidden-xs text-center" name="kiosk_hide">
                <?php echo xlt('Visit Type'); ?>
            </td>
            <?php if (count($chk_prov) > 1) { ?>
                <td class="dehead text-center hidden-xs">
                    <?php echo xlt('Provider'); ?>
                </td>
            <?php } ?>
            <td class="dehead text-center">
                <?php echo xlt('Total Time'); ?>
            </td>
            <td class="dehead  hidden-xs text-center">
                <?php echo xlt('Check Out Time'); ?>
            </td>
            <td class="dehead  visible-xs hidden-sm hidden-md hidden-lg text-center">
                <?php echo xlt('Out Time'); ?>
            </td>
            <?php
                if ($GLOBALS['ptkr_show_staff']) { ?>
                    <td class="dehead hidden-xs hidden-sm text-center" name="kiosk_hide">
                        <?php echo xlt('Updated By'); ?>
                    </td>
                    <?php
                }
                if ($_REQUEST['kiosk'] != '1') {
                    if ($GLOBALS['drug_screen']) { ?>
                        <td class="dehead center hidden-xs " name="kiosk_hide">
                            <?php echo xlt('Random Drug Screen'); ?>
                        </td>
                        <td class="dehead center hidden-xs " name="kiosk_hide">
                            <?php echo xlt('Drug Screen Completed'); ?>
                        </td>
                        <?php
                    }
                } ?>
        </tr>
        </thead>
        <tbody>
        <?php
            $prev_appt_date_time = "";
            foreach ($appointments as $appointment) {
                // Collect appt date and set up squashed date for use below
                $date_appt = $appointment['pc_eventDate'];
                $date_squash = str_replace("-", "", $date_appt);
                if (empty($appointment['room']) && ($logged_in)) {
                    //Patient has not arrived yet, display MedEx Reminder info
                    //one icon per type of response.
                    //If there was a SMS dialog, display it as a mouseover/title
                    //Display date received also as mouseover title.
                    $other_title = '';
                    $title = '';
                    $icon2_here = '';
                    $icon_CALL = '';
                    $icon_4_CALL = '';
                    $appt['stage'] = '';
                    $icon_here = array();
                    $prog_text = '';
                    $CALLED = '';
                    $FINAL = '';
                    $icon_CALL = '';
                    
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
                            $icon_extra .= str_replace("EXTRA",
                                attr(oeFormatShortDate($row['msg_date'])) . "\n" . xla('Patient Message') . ":\n" . attr($row['msg_extra_text']) . "\n",
                                $icons[$row['msg_type']]['EXTRA']['html']);
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
                        } elseif ($row['msg_type'] == "NOTES") {
                            $CALLED = "1";
                            $FINAL = $icons['NOTES']['CALLED']['html'];
                            $icon_CALL = str_replace("Call Back: COMPLETED", attr(oeFormatShortDate($row['msg_date'])) . " :: " . xla('Callback Performed') . " | " . xla('NOTES') . ": " . $row['msg_extra_text'] . " | ", $FINAL);
                            continue;
                        } elseif (($row['msg_reply'] == "READ") || ($appointment[$row['msg_type']]['stage'] == "READ")) {
                            $appointment[$row['msg_type']]['stage'] = "READ";
                            $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'];
                        } elseif (($row['msg_reply'] == "SENT") || ($appointment[$row['msg_type']]['stage'] == "SENT")) {
                            $appointment[$row['msg_type']]['stage'] = "SENT";
                            $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'];
                        } elseif (($row['msg_reply'] == "To Send") || (empty($appointment['stage']))) {
                            if (($appointment[$row['msg_type']]['stage'] != "CONFIRMED") &&
                                ($appointment[$row['msg_type']]['stage'] != "READ") &&
                                ($appointment[$row['msg_type']]['stage'] != "SENT") &&
                                ($appointment[$row['msg_type']]['stage'] != "FAILED")) {
                                $appointment[$row['msg_type']]['stage'] = "QUEUED";
                                $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'];
                            }
                        }
                        //these are additional icons if present
                        if (($row['msg_reply'] == "CALL") && (!$CALLED)) {
                            $icon_here = '';
                            $icon_4_CALL = $icons[$row['msg_type']]['CALL']['html'];
                            $icon_CALL = "<span onclick=\"doCALLback('" . attr($date_squash) . "','" . attr($appointment['eid']) . "','" . attr($appointment['pc_cattype']) . "')\">" . $icon_4_CALL . "</span>
                                    <span class='hidden' name='progCALLback_" . attr($appointment['eid']) . "' id='progCALLback_" . attr($appointment['eid']) . "'>
                                      <form id='notation_" . attr($appointment['eid']) . "' method='post'
                                      action='#'>
                                        <h4>" . xlt('Call Back Notes') . ":</h4>
                                        <input type='hidden' name='pc_eid' id='pc_eid' value='" . attr($appointment['eid']) . "'>
                                        <input type='hidden' name='pc_pid' id='pc_pid' value='" . attr($appointment['pc_pid']) . "'>
                                        <input type='hidden' name='campaign_uid' id='campaign_uid' value='" . attr($row['campaign_uid']) . "'>
                                        <textarea name='txtCALLback' id='txtCALLback' rows=6 cols=20></textarea>
                                        <input type='submit' name='saveCALLback' id='saveCALLback' value='" . xla("Save") ."'>
                                      </form>
                                    </span>
                                      ";
                        } elseif ($row['msg_reply'] == "STOP") {
                            $icon2_here .= $icons[$row['msg_type']]['STOP']['html'];
                        } elseif ($row['msg_reply'] == "Other") {
                            $icon2_here .= $icons[$row['msg_type']]['Other']['html'];
                        } elseif ($row['msg_reply'] == "CALLED") {
                            $icon2_here .= $icons[$row['msg_type']]['CALLED']['html'];
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
                            bgcolor="' . attr($bgcolor) . '" >';
                if ($GLOBALS['ptkr_show_pid']) {
                    ?>
                    <td class="detail hidden-xs" align="center" name="kiosk_hide">
                        <?php echo text($appt_pid); ?>
                    </td>
                    <?php
                }
                
                ?>
                <td class="detail text-center hidden-xs" name="kiosk_hide">
                    <a href="#"
                       onclick="return topatient('<?php echo attr($appt_pid); ?>','<?php echo attr($appt_enc); ?>')">
                        <?php echo text($ptname); ?></a>
                </td>
                <td class="detail text-center visible-xs hidden-sm hidden-md hidden-lg"
                    style="white-space: normal;" name="kiosk_hide">
                    <a href="#"
                       onclick="return topatient('<?php echo attr($appt_pid); ?>','<?php echo attr($appt_enc); ?>')">
                        <?php echo text($ptname_short); ?></a>
                </td>
                
                
                
                <!-- reason -->
                <?php if ($GLOBALS['ptkr_visit_reason']) { ?>
                    <td class="detail hidden-xs text-center" name="kiosk_hide">
                        <?php echo text($reason_visit) ?>
                    </td>
                <?php } ?>
                <?php if ($GLOBALS['ptkr_show_encounter']) { ?>
                    <td class="detail hidden-xs hidden-sm text-center" name="kiosk_hide">
                        <?php if ($appt_enc != 0) {
                            echo text($appt_enc);
                        } ?>
                    </td>
                <?php }
                if ($GLOBALS['ptkr_date_range'] == '1') { ?>
                    <td class="detail hidden-xs text-center" name="kiosk_hide">
                        <?php echo text(oeFormatShortDate($appointment['pc_eventDate']));
                        ?>
                    </td>
                <?php } ?>
                <td class="detail" align="center">
                    <?php echo oeFormatTime($appt_time) ?>
                </td>
                <td class="detail text-center">
                    <?php
                        if ($newarrive) {
                            echo oeFormatTime($newarrive);
                        }
                    ?>
                </td>
                <td class="detail hidden-xs text-center small">
                    <?php if (empty($tracker_id)) { //for appt not yet with tracker id and for recurring appt ?>
                    <a onclick="return calendarpopup(<?php echo attr($appt_eid) . "," . attr($date_squash); // calls popup for add edit calendar event?>)">
                        <?php } else { ?>
                        <a onclick="return bpopup(<?php echo attr($tracker_id); // calls popup for patient tracker status?>)">
                            <?php }
                                if ($appointment['room'] > '') {
                                    echo getListItemTitle('patient_flow_board_rooms', $appt_room);
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
                    $from_time = strtotime($appointment['start_datetime']);
                    $yestime = '1';
                }
                
                $timecheck = round(abs($to_time - $from_time) / 60, 0);
                if ($timecheck >= $statalert && ($statalert > '0')) { // Determine if the time in status limit has been reached.
                    echo "<td class='text-center  js-blink-infinite small' nowrap>  "; // and if so blink
                } else {
                    echo "<td class='detail text-center' nowrap> "; // and if not do not blink
                }
                if (($yestime == '1') && ($timecheck >= 1) && (strtotime($newarrive) != '')) {
                    echo text($timecheck . ' ' . ($timecheck >= 2 ? xl('minutes') : xl('minute')));
                } else if ($icon_here || $icon2_here || $icon_CALL) {
                    echo "<span style='font-size:0.7em;' onclick='return calendarpopup(" . attr($appt_eid) . "," . attr($date_squash) . ")'>" . implode($icon_here) . $icon2_here . "</span> " . $icon_CALL;
                } else if ($logged_in) {
                    $pat = $MedEx->display->possibleModalities($appointment);
                    echo "<span style='font-size:0.7em;' onclick='return calendarpopup(" . attr($appt_eid) . "," . attr($date_squash) . ")'>" . $pat['SMS'] . $pat['AVM'] . $pat['EMAIL'] . "</span>";
                }
                //end time in current status
                echo "</td>";
                ?>
                <td class="detail hidden-xs text-center" name="kiosk_hide">
                    <?php echo xlt($appointment['pc_title']); ?>
                </td>
                <?php
                if (count($chk_prov) > 1) { ?>
                    <td class="detail text-center hidden-xs">
                        <?php echo text($docname); ?>
                    </td>
                    <?php
                } ?>
                <td class="detail text-center">
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
                        echo text($appointment['pc_time']); ?>
                </td>
                <td class="detail text-center">
                
                
                </td>
                <?php
                if ($GLOBALS['ptkr_show_staff'] == '1') {
                    ?>
                    <td class="detail hidden-xs hidden-sm text-center" name="kiosk_hide">
                        <?php echo text($appointment['user']) ?>
                    </td>
                    <?php
                }
                if ($GLOBALS['drug_screen']) {
                    if (strtotime($newarrive) != '') { ?>
                        <td class="detail hidden-xs text-center" name="kiosk_hide">
                            <?php
                                if (text($appointment['random_drug_test']) == '1') {
                                    echo xl('Yes');
                                } else {
                                    echo xl('No');
                                } ?>
                        </td>
                        <?php
                    } ?>
                    <?php
                    if (strtotime($newarrive) != '' && $appointment['random_drug_test'] == '1') { ?>
                        <td class="detail hidden-xs text-center" name="kiosk_hide">
                            <?php
                                if (strtotime($newend) != '') {
                                    // the following block allows the check box for drug screens to be disabled once the status is check out ?>
                                    <input type=checkbox disabled='disable' class="drug_screen_completed"
                                           id="<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>" <?php if ($appointment['drug_screen_completed'] == "1") {
                                        echo "checked";
                                    } ?>>
                                    <?php
                                } else {
                                    ?>
                                    <input type=checkbox class="drug_screen_completed"
                                           id='<?php echo htmlspecialchars($appointment['pt_tracker_id'], ENT_NOQUOTES) ?>'
                                           name="drug_screen_completed" <?php if ($appointment['drug_screen_completed'] == "1") {
                                        echo "checked";
                                    } ?>>
                                    <?php
                                } ?>
                        </td>
                        <?php
                    } else {
                        echo "  </td>";
                    }
                }
                ?>
                </tr>
                <?php
            } //end foreach
        ?>
        </tbody>
    </table>
</div>
    </div>
</div>

    
    
    <?php common_footer($display); ?>
