<?php

/**
 * Functional cognitive status form report.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");

function functional_cognitive_status_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $sql = "SELECT * FROM `form_functional_cognitive_status` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($id,$pid, $encounter));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $data[$iter] = $row;
    }

    if (!empty($data)) {
        ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Code'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Code Text'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Description'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Date'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Active'); ?></span></td>
            </tr>
            <?php
            foreach ($data as $key => $value) {
                ?>
                <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['code']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['codetext']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['description']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($value['date']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo ($value['activity'] == 1) ? xlt('Yes') : xlt('No'); ?></span></td>
            </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
?>
