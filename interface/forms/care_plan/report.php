<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Jacob T Paul <jacob@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function care_plan_report($pid, $encounter, $cols, $id) {
    $count = 0;
    $sql = "SELECT * FROM `form_care_plan` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($id,$_SESSION["pid"], $_SESSION["encounter"]));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
        $data[$iter] = $row;
    if ($data) {
        ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Code'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Code Text'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Description'); ?></span></td> 
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Date'); ?></span></td>
            </tr>
        <?php
        foreach ($data as $key => $value) {
            ?>
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['code']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['codetext']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['description']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['date']); ?></span></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}
?> 
