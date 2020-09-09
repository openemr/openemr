<?php

/**
 * List procedure orders and reports, and fetch new reports and their results.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2013-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("./receive_hl7_results.inc.php");
require_once("./gen_hl7_order.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

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

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'med');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

$errmsg = '';

// Send selected unsent orders if requested. This does not support downloading
// very well as it will only send the first of those.
if ($_POST['form_xmit']) {
    foreach ($_POST['form_cb'] as $formid) {
        $row = sqlQuery("SELECT lab_id FROM procedure_order WHERE " .
            "procedure_order_id = ?", array($formid));
        $ppid = intval($row['lab_id']);
        $hl7 = '';
        $errmsg = gen_hl7_order($formid, $hl7);
        if (empty($errmsg)) {
            $errmsg = send_hl7_order($ppid, $hl7);
        }

        if ($errmsg) {
            break;
        }

        sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE " .
            "procedure_order_id = ?", array($formid));
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
});

</script>
</head>

<body>
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <h2>
                <?php echo xlt('Electronic Reports'); ?>
            </h2>
            <form method='post' action='list_reports.php' enctype='multipart/form-data'>
                <!-- This might be set by the results window: -->
                <input type='hidden' name='form_external_refresh' value='' />

                <?php if ($errmsg) { ?>
                    <span class='text-danger'><?php echo text($errmsg); ?></span>
                    <br />
                <?php }?>

                <?php
                $info = array('select' => array());
                // We skip match/delete processing if this is just a refresh, because that
                // might be a nasty surprise.
                if (empty($_POST['form_external_refresh'])) {
                    // Get patient matching selections from this form if there are any.
                    if (is_array($_POST['select'])) {
                        foreach ($_POST['select'] as $selkey => $selval) {
                            $info['select'][$selkey] = $selval;
                        }
                    }

                    // Get file delete requests from this form if there are any.
                    if (is_array($_POST['delete'])) {
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
                if (empty($_POST['form_external_refresh'])) {
                    $errmsg = poll_hl7_results($info);
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
                if (is_array($info['match'])) {
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
                        $s .= "  <td><input class='form-control' type='text' name='select[" .
                            attr($matchkey) . "]' size='3' value='' " .
                            " readonly /></td>\n";
                        $s .= " </tr>\n";
                    }
                }

                foreach ($info as $infokey => $infoval) {
                    if ($infokey == 'match' || $infokey == 'select') {
                        continue;
                    }

                    $count = 0;
                    if (is_array($infoval['mssgs'])) {
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
                                $s .= "  <td colspan='2' class='text-danger'>" . text(substr($message, 1)) . "</td>\n";
                            } else {
                                // Informational message starts with '>'
                                $s .= "  <td>&nbsp;</td>\n";
                                $s .= "  <td>" . text($infokey) . "</td>\n";
                                $s .= "  <td colspan='2' class='text-success'>" . text(substr($message, 1)) . "</td>\n";
                            }
                            $s .= " </tr>\n";
                        }
                    }
                }

                if ($s) {
                    if ($matchreqs || $errors) {
                        $orphan_orders = true;
                        echo "<p class='font-weight-bold text-success'>";
                        echo xlt('Incoming results requiring attention:');
                        echo "</p>\n";
                    }

                    echo "<div class='table-responsive'><table class='table table-sm mb-0'>\n";
                    echo "<thead>\n";
                    echo " <tr class='head'>\n";
                    echo "  <th>" . xlt('Delete') . "</th>\n";
                    echo "  <th>" . xlt('Lab/File') . "</th>\n";
                    echo "  <th>" . xlt('Message') . "</th>\n";
                    echo "  <th>" . xlt('Match') . "</th>\n";
                    echo " </tr>\n";
                    echo "</thead>\n";
                    echo $s;
                    echo "</table></div>\n";
                    if ($matchreqs || $errors) {
                        echo "<p class='font-weight-bold text-success'>";
                        if ($matchreqs) {
                            echo xlt('Click where indicated above to match the patient.') . ' ';
                            echo xlt('After that the Match column will show the selected patient ID, or 0 to create.') . ' ';
                            echo xlt('If you do not select a match the patient will be created.') . ' ';
                        }

                        echo xlt('Checkboxes above indicate if you want to reject and delete the HL7 file.') . ' ';
                        echo xlt('When done, click Submit (below) to apply your choices.');
                        echo "</p>\n";
                    }
                }

                // If there was a fatal error display that.
                if ($errmsg) {
                    echo "<span class='text-danger'>" . text($errmsg) . "</span><br />\n";
                }

                $form_from_date = empty($_POST['form_from_date']) ? '' : trim($_POST['form_from_date']);
                $form_to_date = empty($_POST['form_to_date']) ? '' : trim($_POST['form_to_date']);
                // if (empty($form_to_date)) $form_to_date = $form_from_date;

                $form_reviewed = empty($_POST['form_reviewed']) ? 3 : intval($_POST['form_reviewed']);

                $form_patient = !empty($_POST['form_patient']);

                $form_provider = empty($_POST['form_provider']) ? '' : intval($_POST['form_provider']);
                ?>
            <div class="form-row my-3">
                <div class="col-sm">
                    <label class='col-form-label' for="form_from_date"><?php echo xlt('From'); ?>:</label>
                </div>
                <div class="col-sm">
                    <input type='text' size='9' name='form_from_date' id='form_from_date' class='form-control datepicker' value='<?php echo attr($form_from_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' placeholder='<?php echo xla('yyyy-mm-dd'); ?>' />
                </div>
                <div class="col-sm">
                    <label class='col-form-label' for="form_to_date"><?php echo xlt('To{{Range}}'); ?>:</label>
                </div>
                <div class="col-sm">
                    <input type='text' size='9' name='form_to_date' id='form_to_date' class='form-control datepicker' value='<?php echo attr($form_to_date); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>' placeholder='<?php echo xla('yyyy-mm-dd'); ?>' />
                </div>
                <div class="col-sm form-check form-check-inline mx-sm-1">
                    <input class="form-check-input" type='checkbox' name='form_patient' id="ck_patient" value='1'
                        <?php if ($form_patient) {
                            echo 'checked ';
                        } ?>/>
                    <label class='form-check-label' for="ck_patient"><?php echo xlt('Current Pt Only'); ?></label>
                </div>
                <div class="col-sm">
                    <select class="col-sm form-control" name='form_reviewed'>
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
                <div class="col-sm">
                    <?php
                    generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
                        'empty_title' => '-- All Providers --'), $form_provider);
                    ?>
                </div>
            <?php if (!$orphan_orders) { ?>
                <div class="col-sm">
                    <button type="submit" class="btn btn-primary btn-save" name='form_refresh' value='<?php echo xla('Submit'); ?>'>
                        <?php echo xlt('Submit'); ?>
                    </button>
                </div>
            <?php } else { ?>
                <div class="col-sm">
                    <button type="submit" class="btn btn-primary" name='form_manual' value='<?php echo xla('Resolve Orphan Results'); ?>'>
                        <?php echo xlt('Resolve Orphan Results'); ?>
                    </button>
                </div>
            <?php } ?>
            </div>
        <table class="table table-bordered table-sm table-striped table-hover">
            <thead>
                <tr class='head'>
                    <th colspan='2'><?php echo xlt('Patient'); ?></th>
                    <th colspan='2'><?php echo xlt('Order'); ?></th>
                    <th colspan='2'><?php echo xlt('Procedure'); ?></th>
                    <th colspan='2'><?php echo xlt('Report'); ?></th>
                </tr>
                <tr class='subhead'>
                    <th><?php echo xlt('Name'); ?></th>
                    <th><?php echo xlt('ID'); ?></th>
                    <th><?php echo xlt('Date'); ?></th>
                    <th><?php echo xlt('ID'); ?></th>
                    <th><?php echo xlt('Code'); ?></th>
                    <th><?php echo xlt('Description'); ?></th>
                    <th><?php echo xlt('Date'); ?></th>
                    <th><?php echo xlt('Status'); ?></th>
                    <!-- <td><?php echo xlt('Reviewed'); ?></td> -->
                </tr>
            </thead>
            <?php
            $selects =
                "po.patient_id, po.procedure_order_id, po.date_ordered, po.date_transmitted, " .
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

                // Sendable procedures sort first, so this also applies to the order on an order ID change.
                $sendable = isset($row['procedure_order_seq']) && $row['do_not_send'] == 0;

                $ptname = $row['lname'];
                if ($row['fname'] || $row['mname']) {
                    $ptname .= ', ' . $row['fname'] . ' ' . $row['mname'];
                }

                if ($lastpoid != $order_id || $lastpcid != $order_seq) {
                    ++$encount;
                }

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
                } else {
                    echo "  <td colspan='2'>&nbsp;</td>";
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
                    echo "  <td colspan='2'>&nbsp;</td>";
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
                    echo "  <td colspan='2'>&nbsp;</td>";
                }

                echo " </tr>\n";

                $lastptid = $patient_id;
                $lastpoid = $order_id;
                $lastpcid = $order_seq;
                ++$lino;
            }
            ?>

        </table>

    <?php if ($num_checkboxes) { ?>
        <button type="submit" class="btn btn-primary btn-transmit" name='form_xmit' value='<?php echo xla('Transmit Selected Orders'); ?>'>
            <?php echo xlt('Transmit Selected Orders'); ?>
        </button>
    <?php } ?>

</form>
</div>
</div>

</div>
</body>
</html>
