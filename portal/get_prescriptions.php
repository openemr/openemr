<?php

/**
 * Patient portal: the patient's active prescriptions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (C) 2016-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");

$sql = "SELECT * FROM prescriptions WHERE `patient_id` = ? AND `active` = 1 ORDER BY `start_date`";
$res = sqlStatement($sql, [$pid]);

if (sqlNumRows($res) > 0) {
    ?>
<table class="table table-striped table-sm">
    <tr>
        <th><?php echo xlt('Drug'); ?></th>
        <th><?php echo xlt('Start Date'); ?></th>
        <th><?php echo xlt('Last Modified'); ?></th>
        <th><?php echo xlt('End Date'); ?></th>
    </tr>
    <?php
    $even = false;
    while ($row = sqlFetchArray($res)) {
        echo "<tr class='" . text($class ?? '') . "'>";
        echo "<td>" . text($row['drug']) . "</td>";
        echo "<td>" . text($row['date_added']) . "</td>";
        echo "<td>" . text($row['date_modified']) . "</td>";
        echo "<td>" . text($row['end_date']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo xlt("No Results");
}
?>
