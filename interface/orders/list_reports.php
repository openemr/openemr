<?php

/**
 * List procedure orders and reports, and fetch new reports and their results.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Tyler Wrenn <tyler@tylerwrenn.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2013-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$orphanLog = '';

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
if (file_exists("$include_root/procedure_tools/quest/QuestResultClient.php")) {
    require_once("$include_root/procedure_tools/quest/QuestResultClient.php");
}
require_once("./receive_hl7_results.inc.php");
require_once("./gen_hl7_order.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'med');
if (!$thisauth) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Procedure Orders and Reports")]);
    exit;
}

$form_patient = !empty($_POST['form_patient']);
$processing_lab = $_REQUEST['form_lab_id'] ?? '';
$start_form = false;
if (!isset($_REQUEST['form_refresh']) && !isset($_REQUEST['form_process_labs']) && !isset($_REQUEST['form_manual'])) {
    $start_form = true;
}

/**
 * Get a list item title, translating if required.
 *
 * @param  string $listid List identifier.
 * @param  string $value List item identifier.
 * @return string  The item's title.
 */
function getListItem($listid, $value)
{
    $lrow = sqlQuery(
        "SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ? AND activity = 1",
        array($listid, $value)
    );
    $tmp = xl_list_label($lrow['title']);
    if (empty($tmp)) {
        $tmp = (($value === '') ? '' : "($value)");
    }

    return $tmp;
}

/**
 * Adapt text to be suitable as the contents of a table cell.
 *
 * @param  string $s Input text.
 * @return string  Output text.
 */
function myCellText($s)
{
    if ($s === '') {
        return '&nbsp;';
    }

    return text($s);
}

$errmsg = '';

// Send selected unsent orders if requested. This does not support downloading
// very well as it will only send the first of those.
if (!empty($_POST['form_xmit'])) {
    foreach ($_POST['form_cb'] as $formid) {
        $row = sqlQuery("SELECT lab_id FROM procedure_order WHERE procedure_order_id = ?", array($formid));
        $ppid = (int)$row['lab_id'];
        $hl7 = '';
        $errmsg = gen_hl7_order($formid, $hl7);
        if (empty($errmsg)) {
            $errmsg = send_hl7_order($ppid, $hl7);
        }

        if ($errmsg) {
            break;
        }

        sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE procedure_order_id = ?", array($formid));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker']); ?>
<title><?php echo xlt('Procedure Orders and Reports'); ?></title>

<style>

tr.head {
        font-size: 13px;
        background-color: var(--gray);
    text-align: center;
        color: var(--white);
}

tr.subhead {
        font-size: 13px;
        background-color: var(--gray200);
    text-align: center;
}

tr.detail {
        margin: 0;
        padding: 0;
        font-size: 13px;
}

a, a:visited, a:hover {
    color: #0000cc;
}

</style>
<script>
var dlgtitle = <?php echo xlj("Match Patient") ?>;

function openResults(orderid) {
    top.restoreSession();
// Open results in a new window. The options parameter serves to defeat Firefox's
// "open windows in a new tab", which is what we want because the doc may want to
// see the results concurrently with other stuff like related patient notes.
// Opening in the other frame is not good enough because if they then do related
// patients notes it will wipe out this script's list. We need 3 viewports.
    window.open('single_order_results.php?orderid=' + encodeURIComponent(orderid), '_blank', 'toolbar=0,location=0,menubar=0,scrollbars=yes');
//
// To open results in the same frame:
// document.location.href = 'single_order_results.php?orderid=' + orderid;
//
// To open results in the "other" frame:
// var w = window;
// var othername = (w.name == 'RTop') ? 'RBot' : 'RTop';
// w.parent.left_nav.forceDual();
// w.parent.left_nav.loadFrame('ore1', othername, 'orders/single_order_results.php?orderid=' + orderid);
}

// Invokes the patient matching dialog.
// args is a PHP-serialized array of patient attributes.
// The dialog script will directly insert the selected pid value, or 0,
// into the value of the form field named "[select][$key]".
//
function openPtMatch(args) {
    top.restoreSession();
    dlgopen('patient_match_dialog.php?key=' + encodeURIComponent(args), '_blank', 850, 400, '', dlgtitle);
}

function openPatient(pid) {
    top.restoreSession();
    document.location.href = "../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(pid);
}

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    $("#wait").addClass('d-none');
});

function doWait(e){
    $("#wait").removeClass('d-none');
    return true;
}
</script>
</head>

<body onsubmit="doWait(event)">
    <div class="page-header ml-2">
        <h2><?php echo xlt('Procedure Orders and Reports'); ?></h2>
    </div>
<form class="form-inline" method='post' action='list_reports.php' enctype='multipart/form-data'>
    <div class="container">
    <!-- This might be set by the results window: -->
    <input class="d-none row" type='text' name='form_external_refresh' value='' />
    <div class="form-row">
        <div class="col-md">
            <div class="form-group">
                <div class="input-group-btn input-group-append">
                    <button class='btn btn-primary' name='form_process_labs'
                        title="Click to process pending results from selected Labs."
                        value="true"><?php echo xlt('Process Results For'); ?><i class="ml-1 btn-transmit"></i>
                    </button>
                    <select name='form_lab_id' id='form_lab_id' class='form-control'>
                        <option value="0"><?php echo xlt('All Labs'); ?></option>
                        <?php
                        $ppres = sqlStatement("SELECT ppid, name, npi FROM procedure_providers ORDER BY name, ppid");
                        while ($pprow = sqlFetchArray($ppres)) {
                            echo "<option value='" . attr($pprow['ppid']) . "'";
                            if ($pprow['ppid'] == $processing_lab) {
                                echo " selected";
                            }
                            if (stripos($pprow['npi'], 'QUEST') !== false) {
                                $pprow['name'] = "Quest Diagnostics";
                            }
                            echo ">" . text($pprow['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <div class="input-group-append">
                    <input name='form_max_results' id='form_max_results' class='form-control'
                        style="max-width:75px;margin-left:20px;"
                        type="number" title="<?php echo xla('Max number of results to process at a time per Lab') ?>"
                        step="1" min="0" max="50"
                        value="<?php echo attr($_REQUEST['form_max_results'] ?? 10); ?>" />
                        <span class="input-group-text"><?php echo xlt('Results Per Lab'); ?></span>
                    </div>
                    <div class="form-check form-check-inline ml-2">
                        <input class="form-check-input" type='checkbox' name='form_patient' id="ck_patient" value='1'
                            <?php if ($form_patient) {
                                echo 'checked ';
                            } ?>/>
                        <label class="input-group-text form-check-label" for="ck_patient"><?php echo xlt('Current Patient Only'); ?></label>
                    </div>
                    <span class="d-none" id="wait"><?php echo xlt("Working") . ' .. ';?>
                        <i class="fa fa-cog fa-spin fa-2x"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php if ($errmsg) { ?>
        <span class='text-danger'><?php echo text($errmsg); ?></span>
        <br />
    <?php } ?>

    <?php
    $info = array('select' => array());
    // We skip match/delete processing if this is just a refresh, because that
    // might be a nasty surprise.
    if (empty($_POST['form_external_refresh'])) {
        // Get patient matching selections from this form if there are any.
        if (!empty($_POST['select']) && is_array($_POST['select'])) {
            foreach ($_POST['select'] as $selkey => $selval) {
                $info['select'][urldecode($selkey)] = $selval;
            }
        }
        // Get file delete requests from this form if there are any.
        if (!empty($_POST['delete']) && is_array($_POST['delete'])) {
            foreach ($_POST['delete'] as $delkey => $dummy) {
                $info[$delkey] = array('delete' => true);
            }
        }
    }

    // is this a manual run
    if (isset($_REQUEST['form_manual'])) {
        $info['orphaned_order'] = "R";
    }
    // Attempt to post any incoming results.
    if (!empty($_REQUEST['form_process_labs']) || (!empty($info['orphaned_order']) && $info['orphaned_order'] == "R")) {
        $errmsg = poll_hl7_results($info, $processing_lab);
    }
    // echo "<!--\n";  // debugging
    // print_r($info); // debugging
    // echo "-->\n";   // debugging

    // Display a row for each required patient matching decision or message.
    $s = '';
    $matchreqs = false;
    $errors = false;
    $orphan_orders = false;

    // Generate HTML to request patient matching.
    if (!empty($info['match']) && is_array($info['match'])) {
        foreach ($info['match'] as $matchkey => $matchval) {
            $matchreqs = true;
            $s .= " <tr class='detail'>\n";
            $s .= "  <td>&nbsp;</td>\n";
            $s .= "  <td>&nbsp;</td>\n";
                        $s .= "  <td><a href='javascript:openPtMatch(" . attr_js($matchkey) . ")'>";
                        $tmp = unserialize($matchkey, ['allowed_classes' => false]);
            $s .= xlt('Click to match patient') . ' "' . text($tmp['lname']) . ', ' . text($tmp['fname']) . '"';
            $s .= "</a>";
            $s .= "</td>\n";
            $s .= "  <td style='width:1%'><input type='text' name='select[" .
                attr($matchkey) . "]' size='3' value=0 " .
                "style='background-color:transparent' readonly /></td>\n";
            $s .= " </tr>\n";
        }
    }

    foreach ($info as $infokey => $infoval) {
        if ($infokey == 'match' || $infokey == 'select') {
            continue;
        }

        $count = 0;
        if (is_array($infoval) && !empty($infoval['mssgs'])) {
            foreach ($infoval['mssgs'] as $message) {
                $s .= " <tr class='detail'>\n";
                if (substr($message, 0, 1) == '*') {
                    $errors = true;
                    // Error message starts with '*'
                    if (!$count++) {
                        $s .= "  <td><input type='checkbox' name='delete[" . attr($infokey) . "]' value='1' /></td>\n";
                        $s .= "  <td>" . text($infokey) . "</td>\n";
                    } else {
                        $s .= "  <td>&nbsp;</td>\n";
                        $s .= "  <td>&nbsp;</td>\n";
                    }
                    $s .= "  <td colspan='2' class='bg-danger'>" . text(substr($message, 1)) . "</td>\n";
                } else {
                    // Informational message starts with '>'
                    $s .= "  <td>&nbsp;</td>\n";
                    $s .= "  <td>" . text($infokey) . "</td>\n";
                    $s .= "  <td colspan='2' class='bg-success'>" . text(substr($message, 1)) . "</td>\n";
                }
                $s .= " </tr>\n";
            }
        }
    }

    if (!empty($s)) {
        if ($matchreqs || $errors) {
            $orphan_orders = true;
            echo "<h4  class='bg-success text-white mb-0'>";
            echo xlt('Incoming results requiring attention:');
            echo "</h4>\n";
        }

        echo "<div class='table-responsive'><table class='table table-sm mb-0'>\n";
        echo "<thead>\n";
        echo " <tr class='head'>\n";
        echo "  <th>" . xlt('Delete') . "</th>\n";
        echo "  <th>" . xlt('Processing Lab Name/Internal Lab Id/Results File Name') . "</th>\n";
        echo "  <th>" . xlt('Message') . "</th>\n";
        echo "  <th>" . xlt('Match') . "</th>\n";
        echo " </tr></thead><tbody id='wait'>\n";
        echo $s;
        echo "</tbody></table>\n";

        if ($matchreqs || $errors) { ?>
            <div class="" data-toggle="collapse" data-target="#help">
                <i class="fa fa-plus"></i><span id="wait"> <strong>Help</strong></span>
            </div>
            <div class="collapse bg-warning" id="help">
                <?php if ($matchreqs) { ?>
                <p>
                    Click the returned patient line item to pull up the patient matching dialog to verify if a match is indicated or select to create a new patient. The match column shows the selected patient ID, or if 0, will create a new patient. Simply clicking Resolve Orphans again without selecting a patient will automatically create a new patient based on the orders patient information. After verifying patients, click Resolve Orphans one last time to complete transactions. A reminder message will be sent to the provider associated with order.
                </p>
                <?php } else { ?>
                <p>
                    Checkboxes indicate if you want to reject and delete the HL7 file. Clicking the Resolve Orphans button will determine if the patient associated with the manual orders exists and will then create a new encounter if one doesn't exist within 30 days of the orphaned orders order date, then will attach the new order to it. If the patient does not exist and there could be a potential match with existing patients then, on return, you will be given an opportunity to match or create a new patient to assign the orphaned order. Otherwise, if there is not any ambiguity that the concerning patient of the order does not currently exist, then the patient will be automatically created and logged to messages and results returned below for review.
                </p>
                <?php } ?>
            </div>
        <?php }

        echo "<table><tr>";
        if ($orphan_orders) {
            echo "<tr><td><button class='btn btn-add btn-danger' type='submit' name='form_manual'>" . xlt('Resolve Orphans') . "</button></td></tr>";
        }
        echo "</tr></table>";
    }

    // If there was a fatal error display that.
    if ($errmsg) {
        echo "<span class='text-danger'>" . text($errmsg) . "</span><br />\n";
    }

    $form_from_date = empty($_POST['form_from_date']) ? '' : trim($_POST['form_from_date']);
    $form_to_date = empty($_POST['form_to_date']) ? '' : trim($_POST['form_to_date']);

    $form_reviewed = empty($_POST['form_reviewed']) ? 3 : (int)$_POST['form_reviewed'];
    $form_patient = !empty($_POST['form_patient']);
    $form_provider = empty($_POST['form_provider']) ? '' : (int)$_POST['form_provider'];
    $form_lab_search = empty($_POST['form_lab_search']) ? '' : (int)$_POST['form_lab_search'];
    ?>

    <div class="form-row my-2">
        <div class="col-md input-group">
            <label class='col-form-label' for="form_from_date"><?php echo xlt('From'); ?>:</label>
            <input type='text' size='9' name='form_from_date' id='form_from_date' class='form-control datepicker' value='<?php echo attr($form_from_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' placeholder='<?php echo xla('yyyy-mm-dd'); ?>' />
        </div>
        <div class="col-md input-group">
            <label class='col-form-label' for="form_to_date"><?php echo xlt('To{{Range}}'); ?>:</label>
            <input type='text' size='9' name='form_to_date' id='form_to_date' class='form-control datepicker' value='<?php echo attr($form_to_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' placeholder='<?php echo xla('yyyy-mm-dd'); ?>' />
        </div>
        <div class="col-md">
            <select class="col-md form-control" name='form_reviewed'>
                <?php
                foreach (
                    array(
                        '1' => xl('All'),
                        '2' => xl('Reviewed'),
                        '3' => xl('Received, unreviewed'),
                        '4' => xl('Sent, not received'),
                        '5' => xl('Not sent'),
                    ) as $key => $value
                ) {
                    echo "<option value='" . attr($key) . "'";
                    if ($key == $form_reviewed) {
                        echo " selected";
                    }
                    echo ">" . text($value) . "</option>\n";
                }
                ?>
            </select>
        </div>
        <div class="col-md">
            <?php
            generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
                'empty_title' => '-- All Providers --'), $form_provider);
            ?>
        </div>
        <div class="col-md">
            <select name='form_lab_search' id='form_lab_search' class='form-control'>
                <option value="0"><?php echo xlt('All Labs'); ?></option>
                <?php
                $ppres = sqlStatement("SELECT ppid, name FROM procedure_providers ORDER BY name, ppid");
                while ($pprow = sqlFetchArray($ppres)) {
                    echo "<option value='" . attr($pprow['ppid']) . "'";
                    if ($pprow['ppid'] == $form_lab_search) {
                        echo " selected";
                    }
                    echo ">" . text($pprow['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md">
            <button type="submit" class="btn btn-outline-primary btn-search float-left" name='form_refresh'><?php echo xlt('Filter'); ?></button>
        </div>
    </div>
    </div>
    <div class="container-fluid">
    <table class="table table-bordered table-condensed table-striped table-hover">
        <thead>
        <tr class='head'>
            <th colspan='2'><?php echo xlt('Patient'); ?></th>
            <th colspan='3'><?php echo xlt('Order'); ?></th>
            <th colspan='2'><?php echo xlt('Procedure'); ?></th>
            <th colspan='2'><?php echo xlt('Report'); ?></th>
        </tr>
        <tr class='subhead'>
            <th><?php echo xlt('Name'); ?></th>
            <th><?php echo xlt('ID'); ?></th>
            <th><?php echo xlt('Date'); ?></th>
            <th><?php echo xlt('ID'); ?></th>
            <th><?php echo xlt('Lab'); ?></th>
            <th><?php echo xlt('Code'); ?></th>
            <th><?php echo xlt('Description'); ?></th>
            <th><?php echo xlt('Date'); ?></th>
            <th><?php echo xlt('Status'); ?></th>
        </tr>
        </thead>
        <?php
        if ($start_form !== true) {
            $selects =
                "po.patient_id, po.procedure_order_id, po.date_ordered, po.date_transmitted, po.lab_id, pp.npi, " .
                "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, pc.do_not_send, " .
                "pr.procedure_report_id, pr.date_report, pr.date_report_tz, pr.report_status, pr.review_status";

            $joins =
                "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
                "pr.procedure_order_seq = pc.procedure_order_seq";

            $orderby =
                "po.date_ordered, po.procedure_order_id, " .
                "pc.do_not_send, pc.procedure_order_seq, pr.procedure_report_id";

            $where = "1 = 1";
            $sqlBindArray = array();

            if (!empty($form_from_date)) {
                $where .= " AND po.date_ordered >= ?";
                $sqlBindArray[] = $form_from_date;
            }

            if (!empty($form_to_date)) {
                $where .= " AND po.date_ordered <= ?";
                $sqlBindArray[] = $form_to_date;
            }

            if ($form_patient) {
                $where .= " AND po.patient_id = ?";
                $sqlBindArray[] = $pid;
            }

            if ($form_provider) {
                $where .= " AND po.provider_id = ?";
                $sqlBindArray[] = $form_provider;
            }

            if ($form_lab_search > 0) {
                $where .= " AND po.lab_id = ?";
                $sqlBindArray[] = $form_lab_search;
            }

            if ($form_reviewed == 2) {
                $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status = 'reviewed'";
            } elseif ($form_reviewed == 3) {
                $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status != 'reviewed'";
            } elseif ($form_reviewed == 4) {
                $where .= " AND po.date_transmitted IS NOT NULL AND pr.procedure_report_id IS NULL";
            } elseif ($form_reviewed == 5) {
                $where .= " AND po.date_transmitted IS NULL AND pr.procedure_report_id IS NULL";
            }

            $query = "SELECT " .
                "pd.fname, pd.mname, pd.lname, pd.pubpid, $selects " .
                "FROM procedure_order AS po " .
                "LEFT JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
                "LEFT JOIN procedure_providers AS pp ON po.lab_id = pp.ppid " .
                "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id $joins " .
                "WHERE $where " .
                "ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, $orderby";

            $res = sqlStatement($query, $sqlBindArray);

            $lastptid = -1;
            $lastpoid = -1;
            $lastpcid = -1;
            $encount = 0;
            $lino = 0;
            $extra_html = '';
            $num_checkboxes = 0;

            while ($row = sqlFetchArray($res)) {
                $patient_id = empty($row['patient_id']) ? 0 : ($row['patient_id'] + 0);
                $order_id = empty($row['procedure_order_id']) ? 0 : ($row['procedure_order_id'] + 0);
                $order_seq = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
                $date_ordered = empty($row['date_ordered']) ? '' : $row['date_ordered'];
                $date_transmitted = empty($row['date_transmitted']) ? '' : $row['date_transmitted'];
                $procedure_code = empty($row['procedure_code']) ? '' : $row['procedure_code'];
                $procedure_name = empty($row['procedure_name']) ? '' : $row['procedure_name'];
                $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
                $date_report = empty($row['date_report']) ? '' : substr($row['date_report'], 0, 16);
                $date_report_suf = empty($row['date_report_tz']) ? '' : (' ' . $row['date_report_tz']);
                $report_status = empty($row['report_status']) ? '' : $row['report_status'];
                $review_status = empty($row['review_status']) ? '' : $row['review_status'];
                $report_lab = empty($row['npi']) ? '' : $row['npi'];

                // Sendable procedures sort first, so this also applies to the order on an order ID change.
                $sendable = isset($row['procedure_order_seq']) && $row['do_not_send'] == 0;

                $ptname = $row['lname'];
                if ($row['fname'] || $row['mname']) {
                    $ptname .= ', ' . $row['fname'] . ' ' . $row['mname'];
                }

                if ($lastpoid != $order_id || $lastpcid != $order_seq) {
                    ++$encount;
                }

                //$bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
                echo " <tr class='detail'>\n";

                // Generate patient columns.
                if ($lastptid != $patient_id) {
                    $lastpoid = -1;
                    echo "  <td class='text-primary' onclick='openPatient(" . attr_js($patient_id) . ")' style='cursor: pointer;'>";
                    echo text($ptname);
                    echo "</td>\n";
                    echo "  <td>" . text($row['pubpid']) . "</td>\n";
                } else {
                    echo "  <td colspan='2'>&nbsp;</td>";
                }

                // Generate order columns.
                if ($lastpoid != $order_id) {
                    $lastpcid = -1;
                    echo "  <td>";
                    // Checkbox to support sending unsent orders, disabled if sent.
                    echo "<input type='checkbox' name='form_cb[" . attr($order_id) . "]' value='" . attr($order_id) . "' ";
                    if ($date_transmitted || !$sendable) {
                        echo "disabled";
                    } else {
                        echo "checked";
                        ++$num_checkboxes;
                    }

                    echo " />&nbsp";
                    // Order date comes with a link to open results in the same frame.
                    echo "<a href='javascript:openResults(" . attr_js($order_id) . ")' ";
                    echo "title='" . xla('Click for results') . "'>";
                    echo text($date_ordered);
                    echo "</a></td>\n";
                    echo "  <td>";
                    // Order ID comes with a link to open the manifest in a new window/tab.
                    echo "<a href='" . $GLOBALS['webroot'];
                    echo "/interface/orders/order_manifest.php?orderid=";
                    echo attr_url($order_id);
                    echo "' target='_blank' onclick='top.restoreSession()' ";
                    echo "title='" . xla('Click for order summary') . "'>";
                    echo text($order_id);
                    echo "</a></td>\n";
                    echo "  <td>" . text($report_lab) . "</td>\n";
                } else {
                    echo "  <td colspan='3' style='background-color:transparent'>&nbsp;</td>";
                }

                // Generate procedure columns.
                if ($order_seq && $lastpcid != $order_seq) {
                    if ($sendable) {
                        echo "  <td>" . text($procedure_code) . "</td>\n";
                        echo "  <td>" . text($procedure_name) . "</td>\n";
                    } else {
                        echo "  <td><s>" . text($procedure_code) . "</s></td>\n";
                        echo "  <td><s>" . text($procedure_name) . "</s></td>\n";
                    }
                } else {
                    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
                }

                // Generate report columns.
                if ($report_id) {
                    echo "  <td>" . text($date_report . $date_report_suf) . "</td>\n";
                    echo "  <td title='" . xla('Check mark indicates reviewed') . "'>";
                    echo myCellText(getListItem('proc_rep_status', $report_status));
                    if ($review_status == 'reviewed') {
                        echo " &#x2713;"; // unicode check mark character
                    }

                    echo "</td>\n";
                } else {
                    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
                }

                echo " </tr>\n";

                $lastptid = $patient_id;
                $lastpoid = $order_id;
                $lastpcid = $order_seq;
                ++$lino;
            }
        } ?>
    </table>
    <?php if (!empty($num_checkboxes)) { ?>
        <button type="submit" class="btn btn-primary btn-transmit" name='form_xmit'
            value='<?php echo xla('Transmit Selected Orders'); ?>'><?php echo xlt('Transmit Selected Orders'); ?>
        </button>
    <?php } ?>
    </div>
</form>
</body>
</html>
