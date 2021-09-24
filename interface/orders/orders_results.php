<?php

/**
 * orders_results.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lab.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// Indicates if we are entering in batch mode.
$form_batch = empty($_GET['batch']) ? 0 : 1;

// Indicates if we are entering in review mode.
$form_review = empty($_GET['review']) ? 0 : 1;

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'med');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

// Check authorization for pending review.
$reviewauth = AclMain::aclCheckCore('patients', 'sign');
if ($form_review and !$reviewauth and !$thisauth) {
    die(xlt('Not authorized'));
}

// Set pid for pending review.
if (!empty($_GET['set_pid']) && $form_review) {
    require_once("$srcdir/pid.inc");
    require_once("$srcdir/patient.inc");
    setpid($_GET['set_pid']);

    $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    ?>
    <script>
        parent.left_nav.setPatient(<?php echo js_escape($result['fname'] . " " . $result['lname']) . "," . js_escape($pid) . "," . js_escape($result['pubpid']) . ",''," . js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($result['DOB_YMD'])); ?>);
    </script>
    <?php
}

if (!$form_batch && !$pid && !$form_review) {
    die(xlt('There is no current patient'));
}

function oresRawData($name, $index)
{
    $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
    return trim($s);
}

function oresData($name, $index)
{
    $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
    return add_escape_custom(trim($s));
}

function QuotedOrNull($fld)
{
    if (empty($fld)) {
        return "NULL";
    }

    return "'$fld'";
}

$current_report_id = 0;

if (!empty($_POST['form_submit']) && !empty($_POST['form_line'])) {
    foreach ($_POST['form_line'] as $lino => $line_value) {
        list($order_id, $order_seq, $report_id, $result_id) = explode(':', $line_value);

// Not using xl() here because this is for debugging only.
        if (empty($order_id)) {
            die("Order ID is missing from line " . text($lino) . ".");
        }

// If report data exists for this line, save it.
        $date_report = oresData("form_date_report", $lino);

        if (!empty($date_report)) {
            $sets =
                "procedure_order_id = '" . add_escape_custom($order_id) . "', " .
                "procedure_order_seq = '" . add_escape_custom($order_seq) . "', " .
                "date_report = '" . add_escape_custom($date_report) . "', " .
                "date_collected = " . QuotedOrNull(oresData("form_date_collected", $lino)) . ", " .
                "specimen_num = '" . oresData("form_specimen_num", $lino) . "', " .
                "report_status = '" . oresData("form_report_status", $lino) . "'";

// Set the review status to reviewed.
            if ($form_review) {
                $sets .= ", review_status = 'reviewed'";
            }

            if ($report_id) { // Report already exists.
                sqlStatement("UPDATE procedure_report SET $sets " .
                    "WHERE procedure_report_id = '" . add_escape_custom($report_id) . "'");
            } else { // Add new report.
                $report_id = sqlInsert("INSERT INTO procedure_report SET $sets");
            }
        }

// If this line had report data entry fields, filled or not, set the
// "current report ID" which the following result data will link to.
        if (isset($_POST["form_date_report"][$lino])) {
            $current_report_id = $report_id;
        }

// If there's a report, save corresponding results.
        if ($current_report_id) {
// Comments and notes will be combined into one comments field.
            $form_comments = oresRawData("form_comments", $lino);
            $form_comments = str_replace("\n", '~', $form_comments);
            $form_comments = str_replace("\r", '', $form_comments);
            $form_notes = oresRawData("form_notes", $lino);
            if ($form_notes !== '') {
                $form_comments .= "\n" . $form_notes;
            }

            $sets =
                "procedure_report_id = '" . add_escape_custom($current_report_id) . "', " .
                "result_code = '" . oresData("form_result_code", $lino) . "', " .
                "result_text = '" . oresData("form_result_text", $lino) . "', " .
                "abnormal = '" . oresData("form_result_abnormal", $lino) . "', " .
                "result = '" . oresData("form_result_result", $lino) . "', " .
                "`range` = '" . oresData("form_result_range", $lino) . "', " .
                "units = '" . oresData("form_result_units", $lino) . "', " .
                "facility = '" . oresData("form_facility", $lino) . "', " .
                "comments = '" . $form_comments . "', " .
                "result_status = '" . oresData("form_result_status", $lino) . "'";
            if ($result_id) { // result already exists
                sqlStatement("UPDATE procedure_result SET $sets " .
                    "WHERE procedure_result_id = '" . add_escape_custom($result_id) . "'");
            } else { // Add new result.
                $result_id = sqlInsert("INSERT INTO procedure_result SET $sets");
            }
        }
    } // end foreach
}
?>
<html>

<head>

    <?php Header::setupHeader('datetime-picker'); ?>

    <title><?php echo xlt('Procedure Results'); ?></title>

    <style>

        tr.head {
            font-size: 13px;
            background-color: var(--gray400);
            text-align: center;
        }
        tr.detail {
            font-size: 13px;
        }

        .reccolor {
            color: var(--success);
        }

    </style>

    <script>

        // This invokes the find-procedure-type popup.
        var ptvarname;
        function sel_proc_type(varname) {
            var f = document.forms[0];
            if (typeof varname == 'undefined') {
                varname = 'form_proc_type';
            }
            ptvarname = varname;
            dlgopen('types.php?popup=1&order=' + encodeURIComponent(f[ptvarname].value), '_blank', 800, 500);
        }

        // This is for callback by the find-procedure-type popup.
        // Sets both the selected type ID and its descriptive name.
        function set_proc_type(typeid, typename) {
            var f = document.forms[0];
            f[ptvarname].value = typeid;
            f[ptvarname + '_desc'].value = typename;
        }

        // Helper functions.
        function extGetX(elem) {
            var x = 0;
            while (elem != null) {
                x += elem.offsetLeft;
                elem = elem.offsetParent;
            }
            return x;
        }

        function extGetY(elem) {
            var y = 0;
            while (elem != null) {
                y += elem.offsetTop;
                elem = elem.offsetParent;
            }
            return y;
        }

        // Show or hide the "extras" div for a result.
        var extdiv = null;
        function extShow(lino, show) {
            var thisdiv = document.getElementById("ext_" + lino);
            if (extdiv) {
                extdiv.style.visibility = 'hidden';
                extdiv.style.left = '-1000px';
                extdiv.style.top = '0px';
            }
            if (show && thisdiv != extdiv) {
                extdiv = thisdiv;
                var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
                x = dw - extdiv.offsetWidth;
                if (x < 0) {
                    x = 0;
                }
                var y = extGetY(show) + show.offsetHeight;
                extdiv.style.left = x;
                extdiv.style.top = y;
                extdiv.style.visibility = 'visible';
            } else {
                extdiv = null;
            }
        }

        // Helper function for validate.
        function prDateRequired(rlino) {
            var f = document.forms[0];
            if (f['form_date_report[' + rlino + ']'].value.length < 10) {
                alert(<?php echo xlj('Missing report date'); ?>);
                if (f['form_date_report[' + rlino + ']'].focus)
                    f['form_date_report[' + rlino + ']'].focus();
                return false;
            }
            return true;
        }

        // Validation at submit time.
        function validate(f) {
            var rlino = 0;
            for (var lino = 0; f['form_line[' + lino + ']']; ++lino) {
                if (f['form_date_report[' + lino + ']']) {
                    rlino = lino;
                    if (f['form_report_status[' + rlino + ']'].selectedIndex > 0) {
                        if (!prDateRequired(rlino)) {
                            return false;
                        }
                    }
                }
                var abnstat = f['form_result_abnormal[' + lino + ']'].selectedIndex > 0;
                if (abnstat && !prDateRequired(rlino)) {
                    return false;
                }
            }
            top.restoreSession();
            return true;
        }

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

    </script>

</head>

<body>
    <div class="container-fluid mt-3">
        <form method='post' action='orders_results.php?batch=<?php echo attr_url($form_batch); ?>&review=<?php echo attr_url($form_review); ?>' onsubmit='return validate(this)'>
            <table class="table table-borderless">
                <tr>
                    <td class='text form-inline'>
                        <?php
                        if ($form_batch) {
                            $form_from_date = isset($_POST['form_from_date']) ? trim($_POST['form_from_date']) : '';
                            $form_to_date = isset($_POST['form_to_date']) ? trim($_POST['form_to_date']) : '';
                            if (empty($form_to_date)) {
                                $form_to_date = $form_from_date;
                            }

                            $form_proc_type = isset($_REQUEST['form_proc_type']) ? $_REQUEST['form_proc_type'] + 0 : 0;
                            if (!$form_proc_type) {
                                $form_proc_type = -1;
                            }

                            $form_proc_type_desc = '';
                            if ($form_proc_type > 0) {
                                $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " .
                                    "procedure_type_id = ?", [$form_proc_type]);
                                $form_proc_type_desc = $ptrow['name'];
                            }
                            ?>
                            <?php echo xlt('Procedure'); ?>:

                            <input class='form-control' type='text' size='30' name='form_proc_type_desc'
                                value='<?php echo attr($form_proc_type_desc) ?>'
                                onclick='sel_proc_type()' onfocus='this.blur()'
                                title='<?php echo xla('Click to select the desired procedure'); ?>'
                                style='cursor:pointer;cursor:hand' readonly />

                            <input type='hidden' name='form_proc_type' value='<?php echo attr($form_proc_type); ?>' />

                            &nbsp;<?php echo xlt('From'); ?>:
                            <input type='text' size='10' class='form-control datepicker' name='form_from_date' id='form_from_date' value='<?php echo attr($form_from_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' />

                            &nbsp;<?php echo xlt('To{{Range}}'); ?>:
                            <input type='text' size='10' class='form-control datepicker' name='form_to_date' id='form_to_date' value='<?php echo attr($form_to_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' />

                            &nbsp;
                            <?php
                        } // end header for batch option
                        ?>
                        <!-- removed by jcw -- check/submit sequece too tedious.  This is a quick fix -->
                        <!--   <input type='checkbox' name='form_all' value='1' <?php if (!empty($_POST['form_all'])) {
                            echo " checked";
                                                                                } ?>><?php echo xlt('Include Completed') ?>&nbsp;-->
                        <button type="submit" class="btn btn-primary btn-refresh" name='form_refresh' value='<?php echo xla('Refresh'); ?>'>
                            <?php echo xlt('Refresh'); ?>
                        </button>
                    </td>
                </tr>
            </table>

            <?php if (!$form_batch || ($form_proc_type > 0 && $form_from_date)) { ?>
            <table class="table table-hover">
                <tr class='head'>
                    <td colspan='2'>
                        <?php echo $form_batch ? xlt('Patient') : xlt('Order'); ?>
                    </td>
                    <td colspan='4'>
                        <?php echo xlt('Report'); ?>
                    </td>
                    <td colspan='7'>
                        <?php echo xlt('Results and'); ?> <span class='reccolor'><?php echo xlt('Recommendations'); ?></span>
                    </td>
                </tr>
                <tr class='head'>
                    <td><?php echo $form_batch ? xlt('Name') : xlt('Date'); ?></td>
                    <td><?php echo $form_batch ? xlt('ID') : xlt('Procedure Name'); ?></td>
                    <td><?php echo xlt('Reported'); ?></td>
                    <td><?php echo xlt('Ext Time Collected'); ?></td>
                    <td><?php echo xlt('Specimen'); ?></td>
                    <td><?php echo xlt('Status'); ?></td>
                    <td><?php echo xlt('Code'); ?></td>
                    <td><?php echo xlt('Name'); ?></td>
                    <td><?php echo xlt('Abn'); ?></td>
                    <td><?php echo xlt('Value'); ?></td>
                    <td><?php echo xlt('Units'); ?></td>
                    <td><?php echo xlt('Range'); ?></td>
                    <td><?php echo xlt('?'); ?></td>
                </tr>

                <?php
                $sqlBindArray = array();

                $selects =
                    "po.procedure_order_id, po.date_ordered, pc.procedure_order_seq, " .
                    "pt1.procedure_type_id AS order_type_id, pc.procedure_name, " .
                    "pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, " .
                    "pr.report_status, pr.review_status";

                $joins =
                    "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
                    "LEFT JOIN procedure_type AS pt1 ON pt1.lab_id = po.lab_id AND pt1.procedure_code = pc.procedure_code " .
                    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
                    "pr.procedure_order_seq = pc.procedure_order_seq";

                $orderby =
                    "po.date_ordered, po.procedure_order_id, " .
                    "pc.procedure_order_seq, pr.procedure_report_id";

                // removed by jcw -- check/submit sequece too tedious.  This is a quick fix
                //$where = empty($_POST['form_all']) ?
                //  "( pr.report_status IS NULL OR pr.report_status = '' OR pr.report_status = 'prelim' )" :
                //  "1 = 1";

                $where = "1 = 1";

                if ($form_batch) {
                    $query = "SELECT po.patient_id, " .
                        "pd.fname, pd.mname, pd.lname, pd.pubpid, $selects " .
                        "FROM procedure_order AS po " .
                        "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id $joins " .
                        "WHERE pt1.procedure_type_id = ? AND " .
                        "po.date_ordered >= ? AND po.date_ordered <= ? " .
                        "AND $where " .
                        "ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, $orderby";
                    array_push($sqlBindArray, $form_proc_type, $form_from_date, $form_to_date);
                } else {
                    $query = "SELECT $selects " .
                        "FROM procedure_order AS po " .
                        "$joins " .
                        "WHERE po.patient_id = ? AND $where " .
                        "ORDER BY $orderby";
                    array_push($sqlBindArray, $pid);
                }

                $res = sqlStatement($query, $sqlBindArray);

                $lastpoid = -1;
                $lastpcid = -1;
                $lastprid = -1;
                $encount = 0;
                $lino = 0;
                $extra_html = '';
                $lastrcn = '';
                $facilities = array();

                while ($row = sqlFetchArray($res)) {
                    $order_type_id = empty($row['order_type_id']) ? 0 : ($row['order_type_id'] + 0);
                    $order_id = empty($row['procedure_order_id']) ? 0 : ($row['procedure_order_id'] + 0);
                    $order_seq = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
                    $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
                    $date_report = empty($row['date_report']) ? '' : substr($row['date_report'], 0, 16);
                    $date_collected = empty($row['date_collected']) ? '' : substr($row['date_collected'], 0, 16);
                    $specimen_num = empty($row['specimen_num']) ? '' : $row['specimen_num'];
                    $report_status = empty($row['report_status']) ? '' : $row['report_status'];
                    $review_status = empty($row['review_status']) ? 'received' : $row['review_status'];

                    // skip report_status = receive to make sure do not show the report before it reviewed and sign off by Physicians
                    if ($form_review) {
                        if ($review_status == "reviewed") {
                            continue;
                        }
                    } else {
                        if ($review_status == "received") {
                            continue;
                        }
                    }

                    $query_test = sqlFetchArray(sqlStatement("select deleted from forms where form_id=? and formdir='procedure_order'", array($order_id)));
                    // skip the procedure that has been deleted from the encounter form
                    if ($query_test['deleted'] == 1) {
                        continue;
                    }

                    $selects = "pt2.procedure_type, pt2.procedure_code, ll.title AS pt2_units, " .
                        "pt2.range AS pt2_range, pt2.procedure_type_id AS procedure_type_id, " .
                        "pt2.name AS name, pt2.description, pt2.seq AS seq, " .
                        "ps.procedure_result_id, ps.result_code AS result_code, ps.result_text, ps.abnormal, ps.result, " .
                        "ps.range, ps.result_status, ps.facility, ps.comments, ps.units, ps.comments";

                    // procedure_type_id for order:
                    $pt2cond = "pt2.parent = '" . add_escape_custom($order_type_id) . "' AND " .
                        "(pt2.procedure_type LIKE 'res%' OR pt2.procedure_type LIKE 'rec%')";

                    // pr.procedure_report_id or 0 if none:
                    $pscond = "ps.procedure_report_id = '" . add_escape_custom($report_id) . "'";

                    $joincond = "ps.result_code = pt2.procedure_code";

                    // This union emulates a full outer join. The idea is to pick up all
                    // result types defined for this order type, as well as any actual
                    // results that do not have a matching result type.
                    $query = "(SELECT $selects FROM procedure_type AS pt2 " .
                        "LEFT JOIN procedure_result AS ps ON $pscond AND $joincond " .
                        "LEFT JOIN list_options AS ll ON ll.list_id = 'proc_unit' AND ll.option_id = pt2.units " .
                        "WHERE $pt2cond" .
                        ") UNION (" .
                        "SELECT $selects FROM procedure_result AS ps " .
                        "LEFT JOIN procedure_type AS pt2 ON $pt2cond AND $joincond " .
                        "LEFT JOIN list_options AS ll ON ll.list_id = 'proc_unit' AND ll.option_id = pt2.units " .
                        "WHERE $pscond) " .
                        "ORDER BY seq, name, procedure_type_id, result_code";

                    $rres = sqlStatement($query);
                    while ($rrow = sqlFetchArray($rres)) {
                        $restyp_code = empty($rrow['procedure_code']) ? '' : $rrow['procedure_code'];
                        $restyp_type = empty($rrow['procedure_type']) ? '' : $rrow['procedure_type'];
                        $restyp_name = empty($rrow['name']) ? '' : $rrow['name'];
                        $restyp_units = empty($rrow['pt2_units']) ? '' : $rrow['pt2_units'];
                        $restyp_range = empty($rrow['pt2_range']) ? '' : $rrow['pt2_range'];

                        $result_id = empty($rrow['procedure_result_id']) ? 0 : ($rrow['procedure_result_id'] + 0);
                        $result_code = empty($rrow['result_code']) ? $restyp_code : $rrow['result_code'];
                        $result_text = empty($rrow['result_text']) ? $restyp_name : $rrow['result_text'];
                        $result_abnormal = empty($rrow['abnormal']) ? '' : $rrow['abnormal'];
                        $result_result = empty($rrow['result']) ? '' : $rrow['result'];
                        $result_units = empty($rrow['units']) ? $restyp_units : $rrow['units'];
                        $result_facility = empty($rrow['facility']) ? '' : $rrow['facility'];
                        $result_comments = empty($rrow['comments']) ? '' : $rrow['comments'];
                        $result_range = empty($rrow['range']) ? $restyp_range : $rrow['range'];
                        $result_status = empty($rrow['result_status']) ? '' : $rrow['result_status'];

                        // If there is more than one line of comments, everything after that is "notes".
                        $result_notes = '';
                        $i = strpos($result_comments, "\n");
                        if ($i !== false) {
                            $result_notes = trim(substr($result_comments, $i + 1));
                            $result_comments = substr($result_comments, 0, $i);
                        }

                        $result_comments = trim($result_comments);

                        if ($result_facility <> "" && !in_array($result_facility, $facilities)) {
                            $facilities[] = $result_facility;
                        }

                        if ($lastpoid != $order_id || $lastpcid != $order_seq) {
                            ++$encount;
                            $lastrcn = '';
                        }

                        echo " <tr class='detail'>\n";

                        // Generate first 2 columns.
                        if ($lastpoid != $order_id || $lastpcid != $order_seq) {
                            $lastprid = -1; // force report fields on first line of each procedure
                            if ($form_batch) {
                                if ($lastpoid != $order_id) {
                                    $tmp = $row['lname'];
                                    if ($row['fname'] || $row['mname']) {
                                        $tmp .= ', ' . $row['fname'] . ' ' . $row['mname'];
                                    }

                                    echo "  <td>" . text($tmp) . "</td>\n";
                                    echo "  <td>" . text($row['pubpid']) . "</td>\n";
                                } else {
                                    echo "  <td colspan='2'>&nbsp;</td>";
                                }
                            } else {
                                if ($lastpoid != $order_id) {
                                    echo "  <td>" . text($row['date_ordered']) . "</td>\n";
                                } else {
                                    echo "  <td>&nbsp;</td>";
                                }

                                echo "  <td>" . text($row['procedure_name']) . "</td>\n";
                            }
                        } else {
                            echo "  <td colspan='2'>&nbsp;</td>";
                        }

                        // If this starts a new report or a new order, generate the report form
                        // fields.  In the case of a new order with no report yet, the fields will
                        // have their blank/default values, and form_line (above) will indicate a
                        // report ID of 0.
                        //
                        // TBD: Also generate default report fields and another set of results if
                        // the previous report is marked "Preliminary".
                        //
                        if ($report_id != $lastprid) { ?>
                            <td class="text-nowrap">
                                <input type='text' size='13' name='form_date_report[<?php echo attr($lino); ?>]'
                                    id='form_date_report[<?php echo attr($lino); ?>]'
                                    class='form-control datetimepicker' value='<?php echo attr($date_report); ?>'
                                    title='<?php echo xla('Date and time of this report'); ?>' />
                            </td>
                            <td class="text-nowrap">
                                <input type='text' size='13' name='form_date_collected[<?php echo attr($lino); ?>]'
                                    id='form_date_collected[<?php echo attr($lino); ?>]'
                                    class='form-control datetimepicker' value='<?php echo attr($date_collected); ?>'
                                    title='<?php echo xla('Date and time of sample collection'); ?>' />
                            </td>
                            <td>
                                <input type='text' size='8' name='form_specimen_num[<?php echo attr($lino); ?>]'
                                    class='form-control'
                                    value='<?php echo attr($specimen_num); ?>'
                                    title='<?php echo xla('Specimen number/identifier'); ?>' />
                            </td>
                            <td>
                                <?php
                                echo generate_select_list(
                                    "form_report_status[$lino]",
                                    'proc_rep_status',
                                    $report_status,
                                    xl('Report Status'),
                                    ' ',
                                    'form-control'
                                ); ?>
                            </td>
                        <?php } else { ?>
                            <td colspan='4'>&nbsp;</td>
                        <?php } ?>

                        <td class="text-nowrap">
                            <input type='text' size='6' name='form_result_code[<?php echo attr($lino); ?>]'
                                class='form-control'
                                value='<?php echo attr($result_code); ?>' />
                        </td>
                        <td>
                            <input type='text' size='16' name='form_result_text[<?php echo attr($lino); ?>]'
                                class='form-control'
                                value='<?php echo attr($result_text); ?>' />
                        </td>
                        <td>
                            <?php echo generate_select_list(
                                "form_result_abnormal[$lino]",
                                'proc_res_abnormal',
                                $result_abnormal,
                                xl('Indicates abnormality'),
                                ' ',
                                'form-control'
                            ); ?>
                        </td>
                        <td>
                            <?php if ($result_units == 'bool') {
                                echo "&nbsp;--";
                            } else { ?>
                                <input type='text' size='7' name='form_result_result[<?php echo attr($lino); ?>]'
                                    class='form-control'
                                    value='<?php echo attr($result_result); ?>' />
                            <?php } ?>
                        </td>
                        <td>
                            <input type='text' size='4' name='form_result_units[<?php echo attr($lino); ?>]'
                                class='form-control'
                                value='<?php echo attr($result_units); ?>'
                                title='<?php echo xla('Units applicable to the result value'); ?>' />
                        </td>
                        <td>
                            <input type='text' size='8' name='form_result_range[<?php echo attr($lino); ?>]'
                                class='form-control'
                                value='<?php echo attr($result_range); ?>'
                                title='<?php echo xla('Reference range of results'); ?>' />
                            <!-- Include a hidden form field containing all IDs for this line. -->
                            <input type='hidden' name='form_line[<?php echo attr($lino); ?>]'
                                value='<?php echo attr($order_id) . ":" . attr($order_seq) . ":" . attr($report_id) . ":" . attr($result_id); ?>' />
                        </td>
                        <td class='font-weight-bold text-center' style='cursor:pointer' onclick='extShow(<?php echo attr_js($lino); ?>, this)'
                            title='<?php echo xla('Click here to view/edit more details'); ?>'>
                            &nbsp;?&nbsp;
                        </td>
                        </tr>
                        <?php
                        // Create a floating div for additional attributes of this result.
                        $extra_html .= "<div id='ext_" . attr($lino) . "' " .
                            "style='position:absolute;width:750px;border:1px solid black;" .
                            "padding:2px;background-color:#cccccc;visibility:hidden;" .
                            "z-index:1000;left:-1000px;top:0px;font-size:9pt;'>\n" .
                            "<table class='table'>\n" .
                            "<tr><td class='font-weight-bold text-center' colspan='2' style='padding:4pt 0 4pt 0'>" .
                            text($result_text) .
                            "</td></tr>\n" .
                            "<tr><td class='text-nowrap'>" . xlt('Status') . ": </td>" .
                            "<td>" . generate_select_list(
                                "form_result_status[$lino]",
                                'proc_res_status',
                                $result_status,
                                xl('Result Status'),
                                ''
                            ) . "</td></tr>\n" .
                            "<tr><td class='font-weight-bold text-nowrap'>" . xlt('Facility') . ": </td>" .     // Ensoftek: Changed Facility to Text Area as the field procedure_result-->facility is now multi-line
                            "<td><textarea class='form-control' rows='3' cols='15' name='form_facility[" . attr($lino) . "]'" .
                            " title='" . xla('Supplier facility name') . "'" .
                            " />" . text($result_facility) .
                            "</textarea></td></tr>\n" .
                            "<tr><td class='font-weight-bold text-nowrap'>" . xlt('Comments') . ": </td>" .
                            "<td><textarea class='form-control' rows='3' cols='15' name='form_comments[" . attr($lino) . "]'" .
                            " title='" . xla('Comments for this result or recommendation') . "'" .
                            " />" . text($result_comments) .
                            "</textarea></td></tr>\n" .
                            "<tr><td class='font-weight-bold text-nowrap'>" . xlt('Notes') . ": </td>" .
                            "<td><textarea class='form-control' rows='4' cols='15' name='form_notes[" . attr($lino) . "]'" .
                            " title='" . xla('Additional notes for this result or recommendation') . "'" .
                            " />" . text($result_notes) .
                            "</textarea></td></tr>\n" .
                            "</table>\n" .
                            "<p class='text-center'><input class='btn btn-primary' type='button' value='" . xla('Close') . "' " .
                            "onclick='extShow(" . attr_js($lino) . ", false)' /></p>\n" .
                            "</div>";

                        $lastpoid = $order_id;
                        $lastpcid = $order_seq;
                        $lastprid = $report_id;
                        ++$lino;
                    }
                }

                if (!empty($facilities)) {
                    // display facility information
                    $extra_html .= "<table class='table'>";
                    $extra_html .= "<tr><th>" . xlt('Performing Laboratory Facility') . "</th></tr>";
                    foreach ($facilities as $facilityID) {
                        foreach (explode(":", $facilityID) as $lab_facility) {
                            $facility_array = getFacilityInfo($lab_facility);
                            if ($facility_array) {
                                $extra_html .=
                                    "<tr><td><hr></td></tr>" .
                                    "<tr><td>" . text($facility_array['fname']) . " " . text($facility_array['lname']) . ", " . text($facility_array['title']) . "</td></tr>" .
                                    "<tr><td>" . text($facility_array['organization']) . "</td></tr>" .
                                    "<tr><td>" . text($facility_array['street']) . " " . text($facility_array['city']) . " " . text($facility_array['state']) . "</td></tr>" .
                                    "<tr><td>" . text(formatPhone($facility_array['phone'])) . "</td></tr>";
                            }
                        }
                    }
                    $extra_html .= "</table>\n";
                }
                ?>

            </table>
            <div class="col-12 text-center mt-3">
                <?php
                if ($form_review) {
                    // if user authorized for pending review.
                    if ($reviewauth) { ?>
                        <button type="submit" class="btn btn-primary" name='form_submit' value='<?php echo xla('Sign Results'); ?>'>
                            <?php echo xlt('Sign Results'); ?>
                        </button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-primary" name='form_submit' value='<?php echo xla('Sign Results'); ?>' onclick="alert(<?php echo attr_js(xl('Not authorized')) ?>);">
                            <?php echo xlt('Sign Results'); ?>
                        </button>
                    <?php }
                } else { ?>
                    <button type="submit" class="btn btn-primary btn-save" name='form_submit' value='<?php echo xla('Save'); ?>'>
                        <?php echo xlt('Save'); ?>
                    </button>
                <?php } ?>
                <?php } ?>
            </div>
            <?php echo ($extra_html ?? ''); ?>
        </form>
    </div>
</body>
</html>
