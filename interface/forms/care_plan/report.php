<?php

/**
 * Care plan form report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once($GLOBALS["srcdir"] . "/options.inc.php");

function care_plan_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $sql = "SELECT * FROM `form_care_plan` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($id, $_SESSION["pid"], $_SESSION["encounter"]));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $data[$iter] = $row;
    }

    if ($data) {
        ?>
        <table class="table w-100">
            <thead>
            <tr>
                <th class="border p-1"><?php echo xlt('Author'); ?></th>
                <th class="border p-1"><?php echo xlt('Type'); ?></th>
                <th class="border p-1"><?php echo xlt('Code'); ?></th>
                <th class="border p-1"><?php echo xlt('Code Text'); ?></th>
                <th class="border p-1"><?php echo xlt('Description'); ?></th>
                <th class="border p-1"><?php echo xlt('Date'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($data as $key => $value) {
                ?>
                <tr>
                    <td class="border p-1"><span class='text'><?php echo text($value['user']); ?></span></td>
                    <td class="border p-1"><span class='text'><?php echo text(getListItemTitle('Plan_of_Care_Type', $value['care_plan_type'])); ?></span></td>
                    <td class="border p-1"><span class=text><?php echo text($value['code']); ?></span></td>
                    <td class="border p-1"><span class=text><?php echo text($value['codetext']); ?></span></td>
                    <td class="border p-1"><span class=text><?php echo text($value['description']); ?></span></td>
                    <td class="border p-1"><span class=text><?php echo text($value['date']); ?></span></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    }
}

?>
