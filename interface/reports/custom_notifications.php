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
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/wmt-v3/wmt.globals.php";

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
    $from_date = date("$tmp-m-d H:i");
    $to_date = date('Y-m-d H:i');
} else {
    $from_date = isset($_POST['form_from_date']) ? date("Y-m-d H:i",strtotime($_POST['form_from_date'])) : date('Y-m-d H:i');
    $to_date = isset($_POST['form_to_date']) ? date("Y-m-d H:i",strtotime($_POST['form_to_date'])) : date('Y-m-d H:i');
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
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder($_REQUEST['form_orderby']) ?  $_REQUEST['form_orderby'] : 'date';

$communication_type_List = array(
    '' => 'Select',
    'email' => 'Email',
    'sms' => 'SMS',
    'fax' => 'Fax',
    'postalmethod' => 'Postal Method',
    'internalmessage' => 'Internal Message'
);

// Option lists
function getEmailTpl() {
    $tpl_list = new wmt\Options('Email_Messages');
    $msgtpl = $tpl_list->getOptionsWithTitle('free_text');
    $htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
    return $htmlStr;
}

function getSmsTpl() {
    $tpl_list = new wmt\Options('SMS_Messages');
    $msgtpl = $tpl_list->getOptionsWithTitle('free_text');
    $htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
    return $htmlStr;
}

function getFaxTpl() {
    $tpl_list = new wmt\Options('Fax_Messages');
    $msgtpl = $tpl_list->getOptionsWithTitle('free_text');
    $htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
    return $htmlStr;
}

function getPostalTpl() {
    $tpl_list = new wmt\Options('Postal_Letters');
    $msgtpl = $tpl_list->getOptionsWithTitle('free_text');
    $htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
    return $htmlStr;
}

function getInternalMsgTpl() {
    $tpl_list = new wmt\Options('Internal_Messages');
    $msgtpl = $tpl_list->getOptionsWithTitle('free_text');
    $htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
    return $htmlStr;
}

function getDefaultTpl() {
    $htmlStr = "<option value=''>Select Template</option>";
    return $htmlStr;
}

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

function fetchCustomEvents($from_date, $to_date, $where_param = null, $orderby_param = null, $tracker_board = false, $nextX = 0, $bind_param = null, $query_param = null)
{

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
            "((cast(concat(e.pc_endDate, ' ', e.pc_startTime) as datetime) >= ? AND e.pc_recurrtype > '0') OR " .
            "(cast(concat(e.pc_eventDate, ' ', e.pc_startTime) as datetime) >= ?))";

            array_push($sqlBindArray, $from_date, $from_date);
        } else {
          //////
            $where =
            "((cast(concat(e.pc_endDate, ' ', e.pc_startTime) as datetime) >= ? AND cast(concat(e.pc_eventDate, ' ', pc_endTime) as datetime) <= ? AND e.pc_recurrtype > '0') OR " .
            "(cast(concat(e.pc_eventDate, ' ', e.pc_startTime) as datetime) >= ? AND cast(concat(e.pc_eventDate, ' ', e.pc_startTime) as datetime) <= ?))";

            array_push($sqlBindArray, $from_date, $to_date, $from_date, $to_date);
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

//Support for therapy group appointments added by shachar z.
function fetchAppts($from_date, $to_date, $patient_id = null, $provider_id = null, $facility_id = null, $pc_appstatus = null, $with_out_provider = null, $with_out_facility = null, $pc_catid = null, $tracker_board = false, $nextX = 0, $group_id = null, $patient_name = null)
{
    $sqlBindArray = array();

    $where = "";

    if ($provider_id) {
        if(is_array($provider_id) && !empty($provider_id) && !in_array("ALL", $provider_id)) {
            $tmp_provider_id = "'".implode("','", $provider_id)."'";
            $where .= " AND e.pc_aid IN (".$tmp_provider_id.")";
        } else if(!is_array($provider_id)) {
            $where .= " AND e.pc_aid = ?";
            array_push($sqlBindArray, $provider_id);
        }
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
        if(is_array($facility_id) && !empty($facility_id) && !in_array("ALL", $facility_id)) {
            $tmp_facility_id = "'".implode("','", $facility_id)."'";
            $where .= " AND e.pc_facility IN (".$tmp_facility_id.")";
        } else if(!is_array($facility_id)) {
            $where .= " AND e.pc_facility = ?";
            array_push($sqlBindArray, $facility_id);
        }
    }

    //Appointment Status Checking
    if ($pc_appstatus != '') {
        $where .= " AND e.pc_apptstatus = ?";
        array_push($sqlBindArray, $pc_appstatus);
    }

    if ($pc_catid != null) {
        if(is_array($pc_catid) && !empty($pc_catid) && !in_array("ALL", $pc_catid)) {
            $tmp_pc_catid = "'".implode("','", $pc_catid)."'";
            $where .= " AND e.pc_catid IN (".$tmp_pc_catid.")";
        } else if(!is_array($pc_catid)) {
            $where .= " AND e.pc_catid = ?";
            array_push($sqlBindArray, $pc_catid);
        }
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

    $appointments = fetchCustomEvents($from_date, $to_date, $where, '', $tracker_board, $nextX, $sqlBindArray);
    return $appointments;
}

//dropdown for facility
function ct_dropdown_facility(
    $selected = '',
    $name = 'form_facility',
    $id = 'form_facility',
    $allow_unspecified = true,
    $allow_allfacilities = true,
    $disabled = '',
    $onchange = ''
) {

    global $facilityService;

    $have_selected = false;
    $fres = $facilityService->getAll();

    $name = htmlspecialchars($name, ENT_QUOTES);
    echo "   <select class='form-control' name='$name' id='$id' multiple='multiple'";
    if ($onchange) {
        echo " onchange='$onchange'";
    }

    echo " $disabled>\n";

    if ($allow_allfacilities) {
        $option_value = 'ALL';
        $option_selected_attr = '';
        if (empty($selected) || in_array("ALL", $selected)) {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = htmlspecialchars('-- ' . xl('All Facilities') . ' --', ENT_NOQUOTES);
        echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    } elseif ($allow_unspecified) {
        $option_value = '0';
        $option_selected_attr = '';
        if (in_array("0", $selected)) {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = htmlspecialchars('-- ' . xl('Unspecified') . ' --', ENT_NOQUOTES);
        echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    }

    foreach ($fres as $frow) {
        $facility_id = $frow['id'];
        $option_value = htmlspecialchars($facility_id, ENT_QUOTES);
        $option_selected_attr = '';
        if (in_array($facility_id, $selected)) {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = htmlspecialchars($frow['name'], ENT_NOQUOTES);
        echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    }

    if ($allow_unspecified && $allow_allfacilities) {
        $option_value = '0';
        $option_selected_attr = '';
        if (in_array("0", $selected)) {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = htmlspecialchars('-- ' . xl('Unspecified') . ' --', ENT_NOQUOTES);
        echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    }

    if (!$have_selected) {
        $option_value = htmlspecialchars($selected, ENT_QUOTES);
        $option_label = htmlspecialchars('(' . xl('Do not change') . ')', ENT_QUOTES);
        $option_content = htmlspecialchars(xl('Missing or Invalid'), ENT_NOQUOTES);
        echo "    <option value='$option_value' label='$option_label' selected='selected'>$option_content</option>\n";
    }

    echo "   </select>\n";
}
?>

<html>

<head>
    <title><?php echo xlt('Communication Blast'); ?></title>

    <?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

    <script type="text/javascript">
        $(document).ready(function() {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

            $('#form_communication_type').change(function(){
                var selectedVal = $(this).val();
                setOptionVal(selectedVal);
            });

            $('#allItemCheckbox').click(function(){
                if(this.checked) {
                    $('.itemCheckbox').prop('checked',true);
                } else {
                    $('.itemCheckbox').prop('checked', false);
                }
            });
        });

        async function handleSend() {
            var communicationTypeVal = document.getElementById("form_communication_type").value;
            var notificationTemplateVal = document.getElementById("form_notification_template").value;

            if(communicationTypeVal == "") {
                alert("Please select communication type");
                return false;
            }

            if(notificationTemplateVal == "") {
                alert("Please select template");
                return false;
            }

            var checkboxes = document.querySelectorAll('.itemCheckbox:checked');
            var selectVals = [];

            for (var checkbox of checkboxes) {
                selectVals.push(checkbox.value);
            }

            if(selectVals.length == 0) {
                alert("Please select items.");
                return false;
            }

            $('#sendbutton').attr("disabled", true);
            $('#sendbutton').text("Send...");

            var data = {'ids' : selectVals, 'type' : communicationTypeVal, 'template' : notificationTemplateVal};

            const result = await $.ajax({
                type: "POST",
                url: "<?php echo $GLOBALS['webroot'].'/interface/reports/ajax/ajax_custom_notifications.php'; ?>",
                datatype: "json",
                data: data
            });

            if(result) {
                resultObj = JSON.parse(result);
                if(resultObj['message']) {
                    alert(resultObj['message']);
                }
            }
            
            $('#sendbutton').attr("disabled", false);
            $('#sendbutton').text("Send");
        }

        //Set Option
        function setOptions(val, id) {
            if(val == "email") {
                $('#'+id).html("<?php echo getEmailTpl(); ?>");
            } else if(val == "sms") {
                $('#'+id).html("<?php echo getSmsTpl(); ?>");
            } else if(val == "fax") {
                $('#'+id).html("<?php echo getFaxTpl(); ?>");
            } else if(val == "postalmethod") {
                $('#'+id).html("<?php echo getPostalTpl(); ?>");
            } else if(val == "internalmessage") {
                $('#'+id).html("<?php echo getInternalMsgTpl(); ?>");
            } else {
                $('#'+id).html("<?php echo getDefaultTpl(); ?>");
            }
        }

        //Init
        function setOptionVal(val, selectVal = '') {
            var id = 'form_notification_template';
            setOptions(val, id);
            if(selectVal != "") {
                $('#'+id).val(selectVal);
            }
        }   

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
        .notification_template {
            max-width: 260px;
        }
        .redText {
            color: red!important;
        }
        </style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Communication Blast'); ?></span>

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='custom_notifications.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
    <tr>
        <td width='850px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='control-label'><?php echo xlt('Facility'); ?>:</td>
                <td><?php ct_dropdown_facility($facility, 'form_facility[]', 'form_facility'); ?>
                </td>
                <td class='control-label'><?php echo xlt('Provider'); ?>:</td>
                <td><?php

                // Build a drop-down list of providers.
                //

                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

                $ures = sqlStatement($query);
                $select1 = (empty($_POST['form_provider']) || in_array("ALL", $_POST['form_provider'])) ? "selected" : "";

                echo "   <select name='form_provider[]' class='form-control' multiple='multiple'>\n";
                echo "    <option value='ALL' ".$select1.">-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if (in_array($provid, $_POST['form_provider'])) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                }

                echo "   </select>\n";
                ?>
                </td>
                <td></td>
            </tr>
            <tr>
                <td class='control-label'><?php echo xlt('From'); ?>:</td>
                <td><input type='text' name='form_from_date' id="form_from_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($from_date))." ".date("H:i",strtotime($from_date)); ?>'>
                </td>
                <td class='control-label'><?php echo xlt('To'); ?>:</td>
                <td><input type='text' name='form_to_date' id="form_to_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($to_date))." ".date("H:i",strtotime($to_date)); ?>'>
                </td>
                <td></td>
            </tr>

            <tr>
                <td class='control-label'><?php echo xlt('Status'); # status code drop down creation ?>:</td>
                <td><?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?></td>
                <td><?php echo xlt('Category') #category drop down creation ?>:</td>
                <td>
                                    <select id="form_apptcat" name="form_apptcat[]" class="form-control" multiple='multiple'>
                                        <?php
                                            $categories=fetchAppointmentCategories();
                                            $select2 = (empty($_POST['form_apptcat']) || in_array("ALL", $_POST['form_apptcat'])) ? "selected" : "";
                                            echo "<option value='ALL' ".$select2.">".xlt("All")."</option>";
                                        while ($cat=sqlFetchArray($categories)) {
                                            echo "<option value='".attr($cat['id'])."'";
                                            if (in_array($cat['id'], $_POST['form_apptcat'])) {
                                                echo " selected='true' ";
                                            }

                                            echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
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
                <td></td>
            <tr>
                <td></td>
                <?php # these two selects will show entries that do not have a facility or a provider ?>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_out_provider" id="with_out_provider" <?php echo ($chk_with_out_provider) ? "checked" : ""; ?>><?php echo xlt('Without Provider'); ?>
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
                <td></td>
            </tr>
            <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                <tr>
                    <td class='control-label'><?php echo xlt('Communication');?>:</td>
                    <td>
                        <select id="form_communication_type" name="form_communication_type" class="communication_type form-control">
                            <?php
                            foreach ($communication_type_List as $key => $desc) {
                                ?>
                                <option value="<?php echo $key ?>" <?php echo ($key == $communication_type) ? "selected" : ""; ?> ><?php echo htmlspecialchars($desc) ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td class='control-label'><?php echo xlt('Template');?>:</td>
                    <td>
                        <select name="form_notification_template" id="form_notification_template" class="optin notification_template form-control">
                            <?php echo getDefaultTpl() ?>
                        </select>
                    </td>
                    <td>
                        <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                <a href='#' class='btn btn-secondary btn-save' id='sendbutton' onclick="handleSend()">
                                    <?php echo xlt('Send'); ?>
                                </a>
                            <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>

        </div>

        </td>
        <td align='left' valign='middle' height="100%">
        <table style='border-left: 1px solid; width: 100%; height: 100%'>
            <tr>
                <td>
                    <div class="text-left">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('Submit'); ?>
                            </a>
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
        <th width="30">
            <input type="checkbox" name="all_select_checkbox" class="allItemCheckbox" id="allItemCheckbox" value="all">
        </th>
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
        <th><?php echo xlt('Email'); ?></th>
        <!-- <th><?php //echo xlt('Home'); //Sorting by phone# not really useful ?></th> -->
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
        if(is_array($_POST['form_apptcat'])) {
            $form_apptcat = $_POST['form_apptcat'];
        } else {
            if ($form_apptcat!="ALL") {
                $form_apptcat=intval($_POST['form_apptcat']);
            }
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

    $appointments = fetchAppts($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

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

        $email_direct = $appointment['email'];
        if($GLOBALS['wmt::use_email_direct']) {
            $patientData = getPatientData($patient_id, "fname, mname, lname, email, email_direct");
            $email_direct = $GLOBALS['wmt::use_email_direct'] ? $patientData['email_direct'] : $patientData['email'];
        }

        $email_messaging_enabled = ($appointment['hipaa_allowemail'] != 'YES' || (empty($appointment['email']) && !$GLOBALS['wmt::use_email_direct'])) ? false : true;

        $sms_messaging_enabled = $appointment['hipaa_allowsms'] != 'YES' || empty($appointment['phone_cell']) ? false : true;

        ?>
        <?php 
        ?>
        <tr valign='top' id='p1.<?php echo attr($patient_id) ?>' bgcolor='<?php echo $bgcolor ?>'>
        <td class="detail">
            <input type="checkbox" name="select_checkbox[]" class="itemCheckbox" id="select_checkbox_<?php echo $appointment['pc_eid']; ?>" value="<?php echo $appointment['pc_eid']; ?>">
        </td>
        <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?>
        </td>

        <td class="detail" <?php echo $showDate ? '' : 'style="display:none;"' ?>><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td class="detail"><a class="detailsLink" onclick="oldEvt('<?php echo $appointment['pc_eid']; ?>')"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?><a/>
        </td>

        <td class="detail">&nbsp;<a class="detailsLink" onclick="goPid('<?php echo $appointment['pid']; ?>')"><?php echo text($appointment['fname'] . " " . $appointment['lname']) ?><a/>
        </td>

        <td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <td class="detail <?php echo ($email_messaging_enabled==false) ? 'redText' : ''; ?>">&nbsp;<?php echo text($email_direct) ?></td>

        <!-- <td class="detail">&nbsp;<?php //echo text($appointment['phone_home']) ?></td> -->

        <td class="detail <?php echo ($sms_messaging_enabled==false) ? 'redText' : ''; ?>">&nbsp;<?php echo text($appointment['phone_cell']) ?></td>

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
       <td colspan=<?php echo $showDate ? '"4"' : '"3"' ?> class="detail" />
       <td colspan=<?php echo ($incl_reminders ? "3":"6") ?> class="detail" align='left'>
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
        <td colspan="11" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppontments);?></td>
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
