<?php

/**
 * physical_exam report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once("lines.php");

function physical_exam_report($pid, $encounter, $cols, $id)
{
    global $pelines;

    $rows = array();
    $res = sqlStatement("SELECT * FROM form_physical_exam WHERE forms_id = ?", array($id));
    while ($row = sqlFetchArray($res)) {
        $rows[$row['line_id']] = $row;
    }

    echo "<table cellpadding='0' cellspacing='0'>\n";

    foreach ($pelines as $sysname => $sysarray) {
        $sysnamedisp = xl($sysname);
        foreach ($sysarray as $line_id => $description) {
            $linedbrow = $rows[$line_id];
            if (
                !($linedbrow['wnl'] || $linedbrow['abn'] || $linedbrow['diagnosis'] ||
                $linedbrow['comments'])
            ) {
                continue;
            }

            if ($sysname != '*') { // observation line
                   echo " <tr>\n";
                   echo "  <td class='text' align='center'>" . ($linedbrow['wnl'] ? "WNL" : "") . "&nbsp;&nbsp;</td>\n";
                   echo "  <td class='text' align='center'>" . ($linedbrow['abn'] ? "ABNL" : "") . "&nbsp;&nbsp;</td>\n";
                   echo "  <td class='text' nowrap>" . text($sysnamedisp) . "&nbsp;&nbsp;</td>\n";
                   echo "  <td class='text' nowrap>" . text($description) . "&nbsp;&nbsp;</td>\n";
                   echo "  <td class='text'>" . text($linedbrow['diagnosis']) . "&nbsp;&nbsp;</td>\n";
                   echo "  <td class='text'>" . text($linedbrow['comments']) . "</td>\n";
                   echo " </tr>\n";
            } else { // treatment line
                     echo " <tr>\n";
                     echo "  <td class='text' align='center'>" . ($linedbrow['wnl'] ? "Y" : "") . "&nbsp;&nbsp;</td>\n";
                     echo "  <td class='text' align='center'>&nbsp;&nbsp;</td>\n";
                     echo "  <td class='text' colspan='2' nowrap>" . text($description) . "&nbsp;&nbsp;</td>\n";
                     echo "  <td class='text' colspan='2'>" . text($linedbrow['comments']) . "</td>\n";
                     echo " </tr>\n";
            }

            $sysnamedisp = '';
        } // end of line
    } // end of system name

    echo "</table>\n";
}
