<?php

/**
 * Functional cognitive status form.
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
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function observation_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $sql = "SELECT * FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($id,$_SESSION["pid"], $_SESSION["encounter"]));



    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $data[$iter] = $row;
    }

    if (!empty($data)) {
        print "<table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Code') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Description') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Code Type') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Table Code') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Value') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Unit') . "</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>" . xlt('Date') . "</span></td>
            </tr>";
        foreach ($data as $key => $value) {
            if ($value['code'] == 'SS003') {
                if ($value['ob_value'] == '261QE0002X') {
                    $value['ob_value'] = 'Emergency Care';
                } elseif ($value['ob_value'] == '261QM2500X') {
                    $value['ob_value'] = 'Medical Specialty';
                } elseif ($value['ob_value'] == '261QP2300X') {
                    $value['ob_value'] = 'Primary Care';
                } elseif ($value['ob_value'] == '261QU0200X') {
                    $value['ob_value'] = 'Urgent Care';
                }
            }

            if ($value['code'] == '21612-7') {
                if ($value['ob_unit'] == 'd') {
                    $value['ob_unit'] = 'Day';
                } elseif ($value['ob_unit'] == 'mo') {
                    $value['ob_unit'] = 'Month';
                } elseif ($value['ob_unit'] == 'UNK') {
                    $value['ob_unit'] = 'Unknown';
                } elseif ($value['ob_unit'] == 'wk') {
                    $value['ob_unit'] = 'Week';
                } elseif ($value['ob_unit'] == 'a') {
                    $value['ob_unit'] = 'Year';
                }
            }

            print "<tr>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['code']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['description']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['code_type']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['table_code']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['ob_value']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['ob_unit']) . "</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>" . text($value['date']) . "</span></td>
                    </tr>";
            print "\n";
        }

        print "</table>";
    }
}
