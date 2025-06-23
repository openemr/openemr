<?php

/**
 *
 * Copyright (C) 2016-2024 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Cassian LUP <cassi.lup@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 *
 */

require_once("verify_session.php");

$sql = "SELECT * FROM lists WHERE pid = ? AND type = 'medical_problem' ORDER BY begdate";

$res = sqlStatement($sql, array($pid));

if (sqlNumRows($res) > 0) { ?>
<table class="table table-sm table-striped">
    <tr class="header">
        <th><?php echo xlt('Title'); ?></th>
        <th><?php echo xlt('Reported Date'); ?></th>
        <th><?php echo xlt('Start Date'); ?></th>
        <th><?php echo xlt('End Date'); ?></th>
    </tr>
    <?php
    $even = false;
    while ($row = sqlFetchArray($res)) {
        echo "<tr class='" . text($class ?? '') . "'>";
        echo "<td>" . text($row['title']) . "</td>";
        echo "<td>" . text($row['date']) . "</td>";
        echo "<td>" . text($row['begdate']) . "</td>";
        echo "<td>" . text($row['enddate']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo xlt("No Results");
}
?>
