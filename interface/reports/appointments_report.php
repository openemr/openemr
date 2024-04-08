<?php

/**
 * This report shows upcoming appointments with filtering and
 * sorting by patient, practitioner, appointment type, and date.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ron Pulcer <rspulcer_2k@yahoo.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ron Pulcer <rspulcer_2k@yahoo.com>
 * @copyright Copyright (c) 2019-2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Set $sessionAllowWrite to true since there are session writes here after html has already been outputted
//  TODO - refactor the session writes in this script to happen at beginning or change to a mechanism
//         that does not require sessions
$sessionAllowWrite = true;
require_once("../globals.php");
require_once("../../library/patient.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";

use OpenEMR\Common\{
    Acl\AclMain,
    Csrf\CsrfUtils,
    Twig\TwigContainer,
};
use OpenEMR\Core\Header;
use OpenEMR\Services\SpreadSheetService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('patients', 'appt')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Appointments Report")]);
    exit;
}

# Clear the pidList session whenever load this page.
# This session will hold array of patients that are listed in this
# report, which is then used by the 'Superbills' and 'Address Labels'
# features on this report.
unset($_SESSION['pidList']);
unset($_SESSION['apptdateList']);

$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'] ?? null;

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
if (!empty($_POST['form_show_available'])) {
    $show_available_times = true;
}

$chk_with_out_provider = false;
if (!empty($_POST['with_out_provider'])) {
    $chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if (!empty($_POST['with_out_facility'])) {
    $chk_with_out_facility = true;
}

$chk_day_of_week = false;
if (!empty($_POST['with_day_of_week'])) {
    $chk_day_of_week = true;
}

$chk_with_canceled_appt = false;
if (!empty($_POST['with_canceled_appt'])) {
    $chk_with_canceled_appt = true;
}

$provider  = $_POST['form_provider'] ?? null;
$facility  = $_POST['form_facility'] ?? null;  //(CHEMED) facility filter
$form_orderby = (!empty($_REQUEST['form_orderby']) && getComparisonOrder($_REQUEST['form_orderby'])) ?  $_REQUEST['form_orderby'] : 'date';

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
        $rems[$rem['due_txt']] .= (isset($rems[$rem['due_txt']]) ? ', ' : '') .
            $rem['act_txt'] . ' ' . $rem['cat_txt'];
    }

    return $rems;
}

// In the case of CSV export only, a download will be forced.
if (empty($_POST['form_csvexport'])) {
    ?>

<html>

<head>
    <title><?php echo xlt('Appointments Report'); ?></title>

    <?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

    <script>
        $(function () {
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
            dlgopen('../main/calendar/add_edit_event.php?eid=' + encodeURIComponent(eventid), 'blank', 775, 500);
        }

        </script>

        <style>
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
        </style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments'); ?></span>

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='appointments_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
    <tr>
        <td width='650px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='col-form-label'><?php echo xlt('Facility'); ?>:</td>
                <td><?php dropdown_facility($facility, 'form_facility'); ?>
                </td>
                <td class='col-form-label'><?php echo xlt('Provider'); ?>:</td>
                <td><?php

                // Build a drop-down list of providers.
                //

                $query = "SELECT id, lname, fname FROM users WHERE " .
                  "authorized = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

                $ures = sqlStatement($query);

                echo "   <select name='form_provider' class='form-control'>\n";
                echo "    <option value=''>-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                }

                echo "   </select>\n";
                ?>
                </td>
            </tr>
            <tr>
                <td class='col-form-label'><?php echo xlt('From'); ?>:</td>
                <td><input type='text' name='form_from_date' id="form_from_date" class='datepicker form-control' size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>' />
                </td>
                <td class='col-form-label'><?php echo xlt('To{{Range}}'); ?>:</td>
                <td><input type='text' name='form_to_date' id="form_to_date" class='datepicker form-control' size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                </td>
            </tr>

            <tr>
                <td class='col-form-label'><?php echo xlt('Status'); # status code drop down creation ?>:</td>
                <td><?php generate_form_field(array('data_type' => 1,'field_id' => 'apptstatus','list_id' => 'apptstat','empty_title' => 'All'), ($_POST['form_apptstatus'] ?? ''));?></td>
                <td><?php echo xlt('Category') #category drop down creation ?>:</td>
                <td>
                                    <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                            $categories = fetchAppointmentCategories();
                                            echo "<option value='ALL'>" . xlt("All") . "</option>";
                                        while ($cat = sqlFetchArray($categories)) {
                                            echo "<option value='" . attr($cat['id']) . "'";
                                            if (!empty($_POST['form_apptcat']) && ($cat['id'] == $_POST['form_apptcat'])) {
                                                echo " selected='true' ";
                                            }

                                            echo    ">" . text(xl_appt_category($cat['category'])) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
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
                        <?php echo ($incl_reminders ? ' checked' : ''); # This will include the reminder for the patients on the report ?>>
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

            <tr>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_day_of_week" id="with_day_of_week"
                        <?php echo ($chk_day_of_week ? ' checked' : ''); ?>>
                        <?php echo xlt('Show Day of Week'); ?>
                        </label>
                    </div>
                </td>

                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_canceled_appt" id="with_canceled_appt" <?php echo ($chk_with_canceled_appt) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('With Canceled Appointments'); ?>
                        </label>
                    </div>
                </td>
            </tr>

        </table>

        </div>

        </td>
        <td class='h-100' align='left' valign='middle'>
        <table class='w-100 h-100' style='border-left: 1px solid;'>
            <tr>
                <td>
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value", null); $("#theform").submit();'>
                                <?php echo xlt('Submit'); ?>
                            </a>
                            <?php if (!empty($_POST['form_refresh']) || !empty($_POST['form_orderby'])) { ?>
                                <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                    <?php echo xlt('Print'); ?>
                                </a>
                                <a href='#' class='btn btn-secondary btn-transmit' onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2", "_blank").opener = null' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Superbills'); ?>
                                </a>
                                <a href='#' class='btn btn-secondary btn-transmit' onclick='window.open("../patient_file/addr_appt_label.php", "_blank").opener = null' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Address Labels'); ?>
                                </a>
                                <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                                    <?php echo xlt('Export to CSV'); ?>
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
<!-- end of search parameters --> 
<?php } // end not form_csvexport

if (!empty($_POST['form_refresh']) || !empty($_POST['form_orderby'])) {
    $showDate = ($from_date != $to_date) || (!$to_date);
    if (empty($_POST['form_csvexport'])) {
        ?>
<div id="report_results">
<table class='table'>

    <thead class='thead-light'>
        <th><a href="nojs.php" onclick="return dosort('doctor')"
        <?php echo ($form_orderby == "doctor") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('Provider'); ?>
        </a></th>

        <th <?php echo $chk_day_of_week ? '' : 'style="display:none;"' ?>>
            <?php
                echo xlt('DOW');
            ?>
        </th>

        <th <?php echo $showDate ? '' : 'style="display:none;"' ?>><a href="nojs.php" onclick="return dosort('date')"
        <?php echo ($form_orderby == "date") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
        <?php echo ($form_orderby == "time") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
        <?php echo ($form_orderby == "patient") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('Patient'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('pubpid')"
        <?php echo ($form_orderby == "pubpid") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('ID'); ?></a>
        </th>

            <th><?php echo xlt('Home'); //Sorting by phone# not really useful ?></th>

                <th><?php echo xlt('Cell'); //Sorting by phone# not really useful ?></th>

        <th><a href="nojs.php" onclick="return dosort('type')"
        <?php echo ($form_orderby == "type") ? " style=\"color: var(--success)\"" : ""; ?>><?php echo xlt('Type'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('status')"
        <?php echo ($form_orderby == "status") ? " style=\"color: var(--success)\"" : ""; ?>><?php  echo xlt('Status'); ?></a>
        </th>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php } // end not csv export
    $lastdocname = "";
    //Appointment Status Checking
    $form_apptstatus = $_POST['form_apptstatus'];
    $form_apptcat = null;
    if (isset($_POST['form_apptcat'])) {
        if ($form_apptcat != "ALL") {
            $form_apptcat = intval($_POST['form_apptcat']);
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

    $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

    if ($show_available_times) {
        $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
        $appointments = array_merge($appointments, $availableSlots);
    }

    $appointments = sortAppointments($appointments, $form_orderby);
    if (!empty($_POST['form_csvexport'])) {
        $fields = ['pc_eventDate', 'pc_startTime', 'fname', 'lname', 'DOB'];
        $spreadsheet = new SpreadSheetService($appointments, $fields, 'appts');
        if (!empty($spreadsheet->buildSpreadsheet())) {
            $spreadsheet->downloadSpreadsheet('Csv');
        }
    } else {
        $pid_list = array();  // Initialize list of PIDs for Superbill option
        $apptdate_list = array(); // same as above for the appt details
        $totalAppointments = count($appointments);
        $canceledAppointments = 0;

        $cntr = 1; // column labels above start at 1
        foreach ($appointments as $appointment) {
            if (
                $appointment['pc_apptstatus'] == "x"
                && empty($chk_with_canceled_appt)
            ) {
                $canceledAppointments++;
                continue;
            } elseif (
                $appointment['pc_apptstatus'] == "x"
                && !empty($chk_with_canceled_appt)
            ) {
                $canceledAppointments++;
            }
            $cntr++;
            array_push($pid_list, $appointment['pid']);
            array_push($apptdate_list, $appointment['pc_eventDate']);
            $patient_id = $appointment['pid'];
            $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];

            $errmsg  = "";
            $pc_apptstatus = $appointment['pc_apptstatus'];
            ?>

            <tr valign='top' id='p1.<?php echo attr($patient_id) ?>' bgcolor='<?php echo attr($bgcolor ?? ''); ?>'>
            <td class="detail">&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?>
            </td>

            <td class="detail" <?php echo $chk_day_of_week ? '' : 'style="display:none;"' ?>>
                <?php
                    echo date('D', strtotime($appointment['pc_eventDate']));
                ?>
            </td>

            <td class="detail" <?php echo $showDate ? '' : 'style="display:none;"' ?>>
                <?php
                    echo text(oeFormatShortDate($appointment['pc_eventDate']));
                ?>
            </td>

            <td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
            </td>

            <td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
            </td>

            <td class="detail">&nbsp;<?php echo text($appointment['pubpid']) ?></td>

            <td class="detail">&nbsp;<?php echo text($appointment['phone_home']) ?></td>

            <td class="detail">&nbsp;<?php echo text($appointment['phone_cell']) ?></td>

            <td class="detail">&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?></td>

            <td class="detail">&nbsp;
                <?php
                    //Appointment Status
                if ($pc_apptstatus != "") {
                    echo text(getListItemTitle('apptstat', $pc_apptstatus));
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
            <td colspan='<?php echo $showDate ? '"3"' : '"2"' ?>' class="detail"></td>
        <td colspan='<?php echo ($incl_reminders ? "3" : "6") ?>' class="detail" align='left'>
                    <?php
                    if (trim($appointment['pc_hometext'])) {
                        echo '<strong>' . xlt('Comments') . '</strong>: ' . text($appointment['pc_hometext']);
                    }

                    if ($incl_reminders) {
                        echo "<td class='detail' colspan='3' align='left'>";
                        $new_line = '';
                        foreach ($rems as $rem_due => $rem_items) {
                            echo "$new_line<strong>$rem_due</strong>: " . attr($rem_items);
                            $new_line = '<br />';
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
        $_SESSION['apptdateList'] = $apptdate_list;
    } // end not form_csvexport

    if (empty($_POST['form_csvexport'])) { ?>
    <tr>
        <td colspan="2" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppointments);?></td>
        <td colspan="2" align="left"><?php echo xlt('Total number of canceled appointments'); ?>:&nbsp;<?php echo text($canceledAppointments);?></td>
    </tr>
    </tbody>
</table>
</div>
<!-- end of search results -->
    <?php }
} else { ?>
<div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php }
if (empty($_POST['form_csvexport'])) { ?>
<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" /> <input type="hidden" name="patient" value="<?php echo attr($patient) ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value='' />
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/></form>
<script>

<?php }
if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
}

if (empty($_POST['form_csvexport'])) { ?>
</script>

</body>

</html>

    <?php
}
