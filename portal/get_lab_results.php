<?php

/**
 *
 * portal/get_lab_results.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");
require_once('../library/options.inc.php');

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

$where = "1 = 1";

$res = sqlStatement("SELECT $selects " .
    "FROM procedure_order AS po $joins " .
    "WHERE po.patient_id = ? AND $where " .
    "ORDER BY $orderby", array($pid));

if (sqlNumRows($res) > 0) {
    ?>
<table class="table table-striped table-sm table-bordered">
    <tr class="header">
        <th><?php echo xlt('Order Date'); ?></th>
        <th><?php echo xlt('Order Name'); ?></th>
        <th><?php echo xlt('Result Name'); ?></th>
        <th><?php echo xlt('Abnormal'); ?></th>
        <th><?php echo xlt('Value'); ?></th>
        <th><?php echo xlt('Range'); ?></th>
        <th><?php echo xlt('Units'); ?></th>
        <th><?php echo xlt('Result Status'); ?></th>
        <th><?php echo xlt('Report Status'); ?></th>
    </tr>
    <?php
    $even = false;

    while ($row = sqlFetchArray($res)) {
        $order_type_id = empty($row['order_type_id']) ? 0 : ($row['order_type_id'] + 0);
        $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);

        $selects = "pt2.procedure_type, pt2.procedure_code, pt2.units AS pt2_units, " .
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
            "WHERE $pt2cond" .
            ") UNION (" .
            "SELECT $selects FROM procedure_result AS ps " .
            "LEFT JOIN procedure_type AS pt2 ON $pt2cond AND $joincond " .
            "WHERE $pscond) " .
            "ORDER BY seq, name, procedure_type_id, result_code";

        $rres = sqlStatement($query);
        while ($rrow = sqlFetchArray($rres)) {
            if ($even) {
                $class = "class1_even";
                $even = false;
            } else {
                $class = "class1_odd";
                $even = true;
            }
            $date = explode('-', $row['date_ordered']);
            echo "<tr class='" . $class . "'>";
            echo "<td>" . text($date[1] . "/" . $date[2] . "/" . $date[0]) . "</td>";
            echo "<td>" . text($row['procedure_name']) . "</td>";
            echo "<td>" . text($rrow['name']) . "</td>";
            echo "<td>" . generate_display_field(array('data_type' => '1', 'list_id' => 'proc_res_abnormal'), $rrow['abnormal']) . "</td>";
            echo "<td>" . text($rrow['result']) . "</td>";
            echo "<td>" . text($rrow['pt2_range']) . "</td>";
            echo "<td>" . generate_display_field(array('data_type' => '1', 'list_id' => 'proc_unit'), $rrow['pt2_units']) . "</td>";
            echo "<td>" . generate_display_field(array('data_type' => '1', 'list_id' => 'proc_res_status'), $rrow['result_status']) . "</td>";
            echo "<td>" . generate_display_field(array('data_type' => '1', 'list_id' => 'proc_rep_status'), $row['report_status']) . "</td>";
            echo "</tr>";
        }
    }

    echo "</table>";
} else {
    echo xlt("No Results");
}
?>
