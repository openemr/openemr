<?php

/**
 * Easipro class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");

use OpenEMR\Easipro\Easipro;

$records = Easipro::assessmentsForPatient($pid);

if (!empty($records)) { ?>
    <table class="table table-striped">
    <tr>
        <th><?php echo xlt('Name'); ?></th>
        <th><?php echo xlt('Deadline (CST)'); ?></th>
        <th><?php echo xlt('Status'); ?></th>
        <th>&nbsp;</th>
    </tr>
    <?php
    foreach ($records as $row) {
        echo "<tr>";
        echo "<td>" . text($row['form_name']) . "</td>";
        echo "<td>" . text($row['deadline']) . "</td>";
        echo "<td id='asst_status_" . attr($row['assessment_oid']) . "'>" . text($row['status']) . "</td>";
        if ($row['status'] == 'ordered') {
            echo "<td id='asst_" . attr($row['assessment_oid']) . "'><button class='btn btn-sm btn-primary' onclick=\"startAssessment(this," . attr_js($row['assessment_oid']) . ")\">" . xlt('Start Assessment') . "</button></td>";
        } elseif ($row['status'] == 'in-progress') {
            echo "<td>" . xlt('Continue Assessment') . "</td>";
        } elseif ($row['status'] == 'completed') {
            echo "<td><i class='fa fa-check-circle'></i></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo xlt("No Assessment to Display.");
}
echo "<div id='Content'></div>" ?>
