<?php
/**
 * This report shows upcoming appointments with filtering and
 * sorting by patient, practitioner, appointment type, and date.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";

use OpenEMR\Core\Header;

# Clear the pidList session whenever load this page.
# This session will hold array of patients that are listed in this
# report, which is then used by the 'Superbills' and 'Address Labels'
# features on this report.
unset($_SESSION['pidList']);

$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

if ($patient && !isset($_POST['form_from_date'])) {
    // If a specific patient, default to 2 years ago.
    $tmp = date('Y') - 2;
    $from_date = date("$tmp-m-d");
    $to_date = date('Y-m-d');
} else {
    $from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
    $to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
}

$show_available_times = false;
if ($_POST['form_show_available']) {
    $show_available_times = true;
}

$chk_with_out_provider = false;
if ($_POST['with_out_provider']) {
    $chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if ($_POST['with_out_facility']) {
    $chk_with_out_facility = true;
}

$provider  = $_POST['form_provider'];
$rprovider  = $_POST['form_rprovider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder($_REQUEST['form_orderby']) ?  $_REQUEST['form_orderby'] : 'date';

// Reminders related stuff
$incl_reminders = isset($_POST['incl_reminders']) ? 1 : 0;
function fetch_rule_txt($list_id, $option_id)
{
    $rs = sqlQuery(
        'SELECT title, seq from list_options WHERE list_id = ? AND option_id = ? AND activity = 1',
        array($list_id, $option_id)
    );
    $rs['title'] = xl_list_label($rs['title']);
    return $rs;
}
function fetch_reminders($pid, $appt_date)
{
    $rems = test_rules_clinic('', 'passive_alert', $appt_date, 'reminders-due', $pid);
    $seq_due = array();
    $seq_cat = array();
    $seq_act = array();
    foreach ($rems as $ix => $rem) {
        $rem_out = array();
        $rule_txt = fetch_rule_txt('rule_reminder_due_opt', $rem['due_status']);
        $seq_due[$ix] = $rule_txt['seq'];
        $rem_out['due_txt'] = $rule_txt['title'];
        $rule_txt = fetch_rule_txt('rule_action_category', $rem['category']);
        $seq_cat[$ix] = $rule_txt['seq'];
        $rem_out['cat_txt'] = $rule_txt['title'];
        $rule_txt = fetch_rule_txt('rule_action', $rem['item']);
        $seq_act[$ix] = $rule_txt['seq'];
        $rem_out['act_txt'] = $rule_txt['title'];
        $rems_out[$ix] = $rem_out;
    }

    array_multisort($seq_due, SORT_DESC, $seq_cat, SORT_ASC, $seq_act, SORT_ASC, $rems_out);
    $rems = array();
    foreach ($rems_out as $ix => $rem) {
        $rems[$rem['due_txt']] .= (isset($rems[$rem['due_txt']]) ? ', ':'').
            $rem['act_txt'].' '.$rem['cat_txt'];
    }

    return $rems;
}

//Support for therapy group appointments added by shachar z.
function ctfetchAppointments($from_date, $to_date, $patient_id = null, $provider_id = null, $facility_id = null, $pc_appstatus = null, $with_out_provider = null, $with_out_facility = null, $pc_catid = null, $tracker_board = false, $nextX = 0, $group_id = null, $patient_name = null)
{
    $sqlBindArray = array();

    $where = "";

    if ($provider_id) {
        $where .= " AND e.pc_aid = ?";
        array_push($sqlBindArray, $provider_id);
    }

    if ($patient_id) {
        $where .= " AND e.pc_pid = ?";
        array_push($sqlBindArray, $patient_id);
    } elseif ($group_id) {
        //if $group_id this means we want only the group events
        $where .= " AND e.pc_gid = ? AND e.pc_pid = ''";
        array_push($sqlBindArray, $group_id);
    } else {
        $where .= " AND e.pc_pid != ''";
    }

    if ($facility_id) {
        $where .= " AND e.pc_facility = ?";
        array_push($sqlBindArray, $facility_id);
    }

    //Appointment Status Checking
    if ($pc_appstatus != '') {
        $where .= " AND e.pc_apptstatus = ?";
        array_push($sqlBindArray, $pc_appstatus);
    }

    if ($pc_catid != null) {
        $where .= " AND e.pc_catid = ?";
        array_push($sqlBindArray, $pc_catid);
    }

    if ($patient_name != null) {
        $where .= " AND (p.fname LIKE CONCAT('%',?,'%') OR p.lname LIKE CONCAT('%',?,'%'))";
        array_push($sqlBindArray, $patient_name, $patient_name);
    }

    //Without Provider checking
    if ($with_out_provider != '') {
        $where .= " AND e.pc_aid = ''";
    }

    //Without Facility checking
    if ($with_out_facility != '') {
        $where .= " AND e.pc_facility = 0";
    }

    $appointments = ctfetchEvents($from_date, $to_date, $where, '', $tracker_board, $nextX, $sqlBindArray);
    return $appointments;
}

function ctfetchEvents($from_date, $to_date, $where_param = null, $orderby_param = null, $tracker_board = false, $nextX = 0, $bind_param = null, $query_param = null)
{
    global $rprovider;

    $sqlBindArray = array();

    if ($query_param) {
        $query = $query_param;

        if ($bind_param) {
            $sqlBindArray = $bind_param;
        }
    } else {
        //////
        if ($nextX) {
            $where =
            "((e.pc_endDate >= ? AND e.pc_recurrtype > '0') OR " .
            "(e.pc_eventDate >= ?))";

            array_push($sqlBindArray, $from_date, $from_date);
        } else {
          //////
            $where =
            "((e.pc_endDate >= ? AND e.pc_eventDate <= ? AND e.pc_recurrtype > '0') OR " .
            "(e.pc_eventDate >= ? AND e.pc_eventDate <= ?))";

            array_push($sqlBindArray, $from_date, $to_date, $from_date, $to_date);
        }

        if(!empty($rprovider)) {
            $where .= " AND fc.referring_id = ? ";
            array_push($sqlBindArray, $rprovider);
        }

        if ($where_param) {
            $where .= $where_param;
        }

        $order_by = "e.pc_eventDate, e.pc_startTime";
        if ($orderby_param) {
             $order_by = $orderby_param;
        }

        // Tracker Board specific stuff
        $tracker_fields = '';
        $tracker_joins = '';
        if ($tracker_board) {
            $tracker_fields = "e.pc_room, e.pc_pid, t.id, t.date, t.apptdate, t.appttime, t.eid, t.pid, t.original_user, t.encounter, t.lastseq, t.random_drug_test, t.drug_screen_completed, " .
            "q.pt_tracker_id, q.start_datetime, q.room, q.status, q.seq, q.user, " .
            "s.toggle_setting_1, s.toggle_setting_2, s.option_id, ";
            $tracker_joins = "LEFT OUTER JOIN patient_tracker AS t ON t.pid = e.pc_pid AND t.apptdate = e.pc_eventDate AND t.appttime = e.pc_starttime AND t.eid = e.pc_eid " .
            "LEFT OUTER JOIN patient_tracker_element AS q ON q.pt_tracker_id = t.id AND q.seq = t.lastseq " .
            "LEFT OUTER JOIN list_options AS s ON s.list_id = 'apptstat' AND s.option_id = q.status AND s.activity = 1 " ;
        }

        $query = "SELECT " .
        "e.pc_eventDate, e.pc_endDate, e.pc_startTime, e.pc_endTime, e.pc_duration, e.pc_recurrtype, e.pc_recurrspec, e.pc_recurrfreq, e.pc_catid, e.pc_eid, e.pc_gid, " .
        "e.pc_title, e.pc_hometext, e.pc_apptstatus, " .
        "p.fname, p.mname, p.lname, p.pid, p.pubpid, p.phone_home, p.phone_cell, " .
        "p.hipaa_allowsms, p.phone_home, p.phone_cell, p.hipaa_voice, p.hipaa_allowemail, p.email, " .
        "u.fname AS ufname, u.mname AS umname, u.lname AS ulname, u.id AS uprovider_id, " .
        "f.name, " .
        "$tracker_fields" .
        "c.pc_catname, c.pc_catid, e.pc_facility " .
        "FROM openemr_postcalendar_events AS e " .
        "$tracker_joins" .
        "LEFT OUTER JOIN facility AS f ON e.pc_facility = f.id " .
        "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
        "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
        "LEFT OUTER JOIN openemr_postcalendar_categories AS c ON c.pc_catid = e.pc_catid " .
        "LEFT OUTER JOIN form_cases AS fc ON e.pc_case = fc.id " .
        "WHERE $where " .
        "ORDER BY $order_by";

        if ($bind_param) {
            $sqlBindArray = array_merge($sqlBindArray, $bind_param);
        }
    }


  ///////////////////////////////////////////////////////////////////////
  // The following code is from the calculateEvents function in the    //
  // PostCalendar Module modified and inserted here by epsdky          //
  ///////////////////////////////////////////////////////////////////////

    $events2 = array();

    $res = sqlStatement($query, $sqlBindArray);

  ////////
    if ($nextX) {
        global $resNotNull;
        $resNotNull = (isset($res) && $res != null);
    }

    while ($event = sqlFetchArray($res)) {
        ///////
        if ($nextX) {
            $stopDate = $event['pc_endDate'];
        } else {
            $stopDate = ($event['pc_endDate'] <= $to_date) ? $event['pc_endDate'] : $to_date;
        }

        ///////
        $incX = 0;
        switch ($event['pc_recurrtype']) {
            case '0':
                $events2[] = $event;

                break;
      //////
            case '1':
            case '3':
                $event_recurrspec = @unserialize($event['pc_recurrspec']);

                if (checkEvent($event['pc_recurrtype'], $event_recurrspec)) {
                    break; }

                $rfreq = $event_recurrspec['event_repeat_freq'];
                $rtype = $event_recurrspec['event_repeat_freq_type'];
                $exdate = $event_recurrspec['exdate'];

                list($ny,$nm,$nd) = explode('-', $event['pc_eventDate']);
        //        $occurance = Date_Calc::dateFormat($nd,$nm,$ny,'%Y-%m-%d');
                $occurance = $event['pc_eventDate'];

                while ($occurance < $from_date) {
                    $occurance =& __increment($nd, $nm, $ny, $rfreq, $rtype);
                    list($ny,$nm,$nd) = explode('-', $occurance);
                }

                while ($occurance <= $stopDate) {
                    $excluded = false;
                    if (isset($exdate)) {
                        foreach (explode(",", $exdate) as $exception) {
                            // occurrance format == yyyy-mm-dd
                            // exception format == yyyymmdd
                            if (preg_replace("/-/", "", $occurance) == $exception) {
                                $excluded = true;
                            }
                        }
                    }

                    if ($excluded == false) {
                        $event['pc_eventDate'] = $occurance;
                        $event['pc_endDate'] = '0000-00-00';
                        $events2[] = $event;
                      //////
                        if ($nextX) {
                            ++$incX;
                            if ($incX == $nextX) {
                                break;
                            }
                        }

                      //////
                    }

                    $occurance =& __increment($nd, $nm, $ny, $rfreq, $rtype);
                    list($ny,$nm,$nd) = explode('-', $occurance);
                }
                break;

      //////
            case '2':
                $event_recurrspec = @unserialize($event['pc_recurrspec']);

                if (checkEvent($event['pc_recurrtype'], $event_recurrspec)) {
                    break; }

                $rfreq = $event_recurrspec['event_repeat_on_freq'];
                $rnum  = $event_recurrspec['event_repeat_on_num'];
                $rday  = $event_recurrspec['event_repeat_on_day'];
                $exdate = $event_recurrspec['exdate'];

                list($ny,$nm,$nd) = explode('-', $event['pc_eventDate']);

                if (isset($event_recurrspec['rt2_pf_flag']) && $event_recurrspec['rt2_pf_flag']) {
                    $nd = 1;
                }

                $occuranceYm = "$ny-$nm"; // YYYY-mm
                $from_dateYm = substr($from_date, 0, 7); // YYYY-mm
                $stopDateYm = substr($stopDate, 0, 7); // YYYY-mm

                // $nd will sometimes be 29, 30 or 31 and if used in the mktime functions below
                // a problem with overflow will occur so it is set to 1 to avoid this (for rt2
                // appointments set prior to fix $nd remains unchanged). This can be done since
                // $nd has no influence past the mktime functions.
                while ($occuranceYm < $from_dateYm) {
                    $occuranceYmX = date('Y-m-d', mktime(0, 0, 0, $nm+$rfreq, $nd, $ny));
                    list($ny,$nm,$nd) = explode('-', $occuranceYmX);
                    $occuranceYm = "$ny-$nm";
                }

                while ($occuranceYm <= $stopDateYm) {
                    // (YYYY-mm)-dd
                    $dnum = $rnum;
                    do {
                        $occurance = Date_Calc::NWeekdayOfMonth($dnum--, $rday, $nm, $ny, $format = "%Y-%m-%d");
                    } while ($occurance === -1);

                    if ($occurance >= $from_date && $occurance <= $stopDate) {
                        $excluded = false;
                        if (isset($exdate)) {
                            foreach (explode(",", $exdate) as $exception) {
                                // occurrance format == yyyy-mm-dd
                                // exception format == yyyymmdd
                                if (preg_replace("/-/", "", $occurance) == $exception) {
                                    $excluded = true;
                                }
                            }
                        }

                        if ($excluded == false) {
                            $event['pc_eventDate'] = $occurance;
                            $event['pc_endDate'] = '0000-00-00';
                            $events2[] = $event;
                            //////
                            if ($nextX) {
                                ++$incX;
                                if ($incX == $nextX) {
                                    break;
                                }
                            }

                            //////
                        }
                    }

                    $occuranceYmX = date('Y-m-d', mktime(0, 0, 0, $nm+$rfreq, $nd, $ny));
                    list($ny,$nm,$nd) = explode('-', $occuranceYmX);
                    $occuranceYm = "$ny-$nm";
                }
                break;
        }
    }

    return $events2;
////////////////////// End of code inserted by epsdky
}
?>

<html>

<head>
    <title><?php echo xlt('Appointments Report (Referring Provider)'); ?></title>

    <?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

    <script type="text/javascript">
        $(document).ready(function() {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

        });

        function dosort(orderby) {
            var f = document.forms[0];
            f.form_orderby.value = orderby;
            f.submit();
            return false;
        }

        function oldEvt(eventid) {
            dlgopen('../main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 775, 500);
        }

        function goPid(pid) {
            top.restoreSession();
            top.RTop.location = '<?php echo $GLOBALS['webroot']; ?>' + '/interface/patient_file/summary/demographics.php?set_pid='+pid;
        }

        function refreshme() {
            // location.reload();
            document.forms[0].submit();
        }
        </script>

        <style type="text/css">
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results table {
                margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }
        .detailsLink {
            cursor: pointer;
        }
        </style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Appointments Report (Referring Provider)'); ?></span>

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='custom_appointments_report.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
    <tr>
        <td width='650px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='control-label'><?php echo xlt('Facility'); ?>:</td>
                <td><?php dropdown_facility($facility, 'form_facility'); ?>
                </td>
                <td class='control-label'><?php echo xlt('Provider'); ?>:</td>
                <td><?php

                // Build a drop-down list of providers.
                //

                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

                $ures = sqlStatement($query);

                echo "   <select name='form_provider' class='form-control'>\n";
                echo "    <option value=''>-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == $_POST['form_provider']) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                }

                echo "   </select>\n";
                ?>
                </td>
            </tr>
            <tr>
                <td class='control-label'><?php echo xlt('From'); ?>:</td>
                <td><input type='text' name='form_from_date' id="form_from_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                </td>
                <td class='control-label'><?php echo xlt('To'); ?>:</td>
                <td><input type='text' name='form_to_date' id="form_to_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                </td>
            </tr>

            <tr>
                <td class='control-label'><?php echo xlt('Status'); # status code drop down creation ?>:</td>
                <td><?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?></td>
                <td><?php echo xlt('Category') #category drop down creation ?>:</td>
                <td>
                                    <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                            $categories=fetchAppointmentCategories();
                                            echo "<option value='ALL'>".xlt("All")."</option>";
                                        while ($cat=sqlFetchArray($categories)) {
                                            echo "<option value='".attr($cat['id'])."'";
                                            if ($cat['id']==$_POST['form_apptcat']) {
                                                echo " selected='true' ";
                                            }

                                            echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
            </tr>
             <tr>
                <td class='control-label'><?php echo xlt('Referring Provider'); ?>:</td>
                <td>
                <?php
                    $query = "SELECT id, lname, fname, mname, specialty FROM users WHERE `active` = 1 AND (lname != '' AND fname != '') AND username='' ORDER BY lname";
                    $rprov = sqlStatement($query);

                    echo "   <select name='form_rprovider' class='form-control'>\n";
                    echo "    <option value=''>-- " . xlt('All') . " --\n";

                    while ($rurow = sqlFetchArray($rprov)) {
                        $provid = $rurow['id'];
                        echo "    <option value='" . attr($provid) . "'";
                        if ($provid == $_POST['form_rprovider']) {
                            echo " selected";
                        }

                        echo ">" . text($rurow['lname']) . ", " . text($rurow['fname']) . "\n";
                    }

                    echo "   </select>\n";

                ?>
                </td>
                <td></td>
                <td></td>
             </tr>
            <tr>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type='checkbox' name='form_show_available'
                        <?php  echo ($show_available_times) ? ' checked' : ''; ?>> <?php echo xlt('Show Available Times'); # check this to show available times on the report ?>
                        </label>
                    </div>
                </td>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="incl_reminders" id="incl_reminders"
                        <?php echo ($incl_reminders ? ' checked':''); # This will include the reminder for the patients on the report ?>>
                        <?php echo xlt('Show Reminders'); ?>
                        </label>
                    </div>
                </td>

            <tr>
                <td></td>
                <?php # these two selects will show entries that do not have a facility or a provider ?>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_out_provider" id="with_out_provider" <?php echo ($chk_with_out_provider) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Without Provider'); ?>
                        </label>
                    </div>
                </td>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_out_facility" id="with_out_facility" <?php echo ($chk_with_out_facility) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Without Facility'); ?>
                        </label>
                    </div>
                </td>
            </tr>

        </table>

        </div>

        </td>
        <td align='left' valign='middle' height="100%">
        <table style='border-left: 1px solid; width: 100%; height: 100%'>
            <tr>
                <td>
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('Submit'); ?>
                            </a>
                            <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                    <?php echo xlt('Print'); ?>
                                </a>
                                <a href='#' class='btn btn-secondary btn-transmit' onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2","_blank")' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Superbills'); ?>
                                </a>
                                <a href='#' class='btn btn-secondary btn-transmit' onclick='window.open("../patient_file/addr_appt_label.php","_blank")' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Address Labels'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
                        <tr>&nbsp;&nbsp;<?php echo xlt('Most column headers can be clicked to change sort order') ?></tr>
        </table>
        </td>
    </tr>
</table>

</div>
<!-- end of search parameters --> <?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
    $showDate = ($from_date != $to_date) || (!$to_date);
    ?>
<div id="report_results">
<table class="table">

    <thead class="thead-light">
        <th><a href="nojs.php" onclick="return dosort('doctor')"
    <?php echo ($form_orderby == "doctor") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Provider'); ?>
        </a></th>

        <th <?php echo $showDate ? '' : 'style="display:none;"' ?>><a href="nojs.php" onclick="return dosort('date')"
    <?php echo ($form_orderby == "date") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
    <?php echo ($form_orderby == "time") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
    <?php echo ($form_orderby == "patient") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Patient'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('pubpid')"
    <?php echo ($form_orderby == "pubpid") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('ID'); ?></a>
        </th>

            <th><?php echo xlt('Home'); //Sorting by phone# not really useful ?></th>

                <th><?php echo xlt('Cell'); //Sorting by phone# not really useful ?></th>

        <th><a href="nojs.php" onclick="return dosort('type')"
    <?php echo ($form_orderby == "type") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Type'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('status')"
            <?php echo ($form_orderby == "status") ? " style=\"color:#00cc00\"" : ""; ?>><?php  echo xlt('Status'); ?></a>
        </th>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php

    $lastdocname = "";
    //Appointment Status Checking
        $form_apptstatus = $_POST['form_apptstatus'];
        $form_apptcat=null;
    if (isset($_POST['form_apptcat'])) {
        if ($form_apptcat!="ALL") {
            $form_apptcat=intval($_POST['form_apptcat']);
        }
    }

    //Without provider and facility data checking
    $with_out_provider = null;
    $with_out_facility = null;

    if (isset($_POST['with_out_provider'])) {
        $with_out_provider = $_POST['with_out_provider'];
    }

    if (isset($_POST['with_out_facility'])) {
        $with_out_facility = $_POST['with_out_facility'];
    }

    $appointments = ctfetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

    if ($show_available_times) {
        $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
        $appointments = array_merge($appointments, $availableSlots);
    }

    $appointments = sortAppointments($appointments, $form_orderby);
    $pid_list = array();  // Initialize list of PIDs for Superbill option
    $totalAppontments = count($appointments);

    foreach ($appointments as $appointment) {
                array_push($pid_list, $appointment['pid']);
        $patient_id = $appointment['pid'];
        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];

        $errmsg  = "";
        $pc_apptstatus = $appointment['pc_apptstatus'];

        ?>

        <tr valign='top' id='p1.<?php echo attr($patient_id) ?>' bgcolor='<?php echo $bgcolor ?>'>
        <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?>
        </td>

        <td class="detail" <?php echo $showDate ? '' : 'style="display:none;"' ?>><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td class="detail"><a class="detailsLink" onclick="oldEvt('<?php echo $appointment['pc_eid']; ?>')"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?><a/>
        </td>

        <td class="detail">&nbsp;<a class="detailsLink" onclick="goPid('<?php echo $appointment['pid']; ?>')"><?php echo text($appointment['fname'] . " " . $appointment['lname']) ?><a/>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <td class="detail">&nbsp;<?php echo text($appointment['phone_home']) ?></td>

        <td class="detail">&nbsp;<?php echo text($appointment['phone_cell']) ?></td>

        <td class="detail">&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?></td>

        <td class="detail">&nbsp;
            <?php
                //Appointment Status
            if ($pc_apptstatus != "") {
                echo getListItemTitle('apptstat', $pc_apptstatus);
            }
            ?>
        </td>
    </tr>

    <?php
    if ($patient_id && $incl_reminders) {
        // collect reminders first, so can skip it if empty
        $rems = fetch_reminders($patient_id, $appointment['pc_eventDate']);
    }
    ?>
    <?php
    if ($patient_id && (!empty($rems) || !empty($appointment['pc_hometext']))) { // Not display of available slot or not showing reminders and comments empty ?>
    <tr valign='top' id='p2.<?php echo attr($patient_id) ?>' >
       <td colspan='<?php echo $showDate ? '"3"' : '"2"' ?>' class="detail" />
       <td colspan='<?php echo ($incl_reminders ? "3":"6") ?>' class="detail" align='left'>
        <?php
        if (trim($appointment['pc_hometext'])) {
            echo '<b>'.xlt('Comments') .'</b>: '.attr($appointment['pc_hometext']);
        }

        if ($incl_reminders) {
            echo "<td class='detail' colspan='3' align='left'>";
            $new_line = '';
            foreach ($rems as $rem_due => $rem_items) {
                echo "$new_line<b>$rem_due</b>: ".attr($rem_items);
                $new_line = '<br>';
            }

            echo "</td>";
        }
        ?>
        </td>
    </tr>
    <?php
    } // End of row 2 display

    $lastdocname = $docname;
    }

    // assign the session key with the $pid_list array - note array might be empty -- handle on the printed_fee_sheet.php page.
        $_SESSION['pidList'] = $pid_list;
    ?>
    <tr>
        <td colspan="10" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppontments);?></td>
    </tr>
    </tbody>
</table>
</div>
<!-- end of search results -->
<?php } else { ?>
<div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?> <input type="hidden" name="form_orderby"
    value="<?php echo attr($form_orderby) ?>" /> <input type="hidden"
    name="patient" value="<?php echo attr($patient) ?>" /> <input type='hidden'
    name='form_refresh' id='form_refresh' value='' /></form>

<script type="text/javascript">

<?php
if ($alertmsg) {
    echo " alert('$alertmsg');\n";
}
?>

</script>

</body>

</html>
