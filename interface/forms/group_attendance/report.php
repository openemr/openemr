<?php
/**
 * interface/forms/group_attendance/report.php
 *
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */





require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once("{$GLOBALS['srcdir']}/group.inc");

function group_attendance_report($pid, $encounter, $cols, $id)
{

    global $therapy_group;

    $sql = "SELECT * FROM `form_group_attendance` WHERE id=? AND group_id = ? AND encounter_id = ?";
    $res = sqlStatement($sql, array($id,$therapy_group, $_SESSION["encounter"]));
    $form_data = sqlFetchArray($res);
    $group_data = getGroup($therapy_group);
    $group_name = $group_data['group_name'];

    if ($form_data) { ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Date'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Group'); ?></span></td>
            </tr>
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($form_data['date']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($group_name); ?></span></td>
            </tr>
        </table>
        <?php
    }
}
?>
