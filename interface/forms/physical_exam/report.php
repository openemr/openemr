<?php

/**
 * physical_exam report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

use function OpenEMR\Forms\PhysicalExam\physical_exam_lines;

require_once(__DIR__ . '/../../globals.php');
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");
require_once(__DIR__ . '/lines.php');

function physical_exam_report(int $pid, int $encounter, int $cols, int $id): void
{
    // Coerce a database value (which PHPStan sees as mixed) to a string.
    $scalarString = static fn (mixed $value): string => is_scalar($value) ? (string) $value : '';
    // A stored checkbox/text value counts as "set" when it is non-empty and not '0'.
    $isSet = static fn (string $value): bool => $value !== '' && $value !== '0';

    $rows = [];
    foreach (QueryUtils::fetchRecords('SELECT * FROM form_physical_exam WHERE forms_id = ?', [$id]) as $row) {
        $rows[$scalarString($row['line_id'] ?? null)] = $row;
    }

    echo "<table cellpadding='0' cellspacing='0'>\n";

    foreach (physical_exam_lines() as $system) {
        $sysnamedisp = $system['label'];
        foreach ($system['lines'] as $line_id => $description) {
            $linedbrow = $rows[$line_id] ?? [];
            $wnl = $scalarString($linedbrow['wnl'] ?? null);
            $abn = $scalarString($linedbrow['abn'] ?? null);
            $diagnosis = $scalarString($linedbrow['diagnosis'] ?? null);
            $comments = $scalarString($linedbrow['comments'] ?? null);
            if (!($isSet($wnl) || $isSet($abn) || $isSet($diagnosis) || $isSet($comments))) {
                continue;
            }

            if ($system['code'] !== '*') { // observation line
                echo " <tr>\n";
                echo "  <td class='text' align='center'>" . ($isSet($wnl) ? "WNL" : "") . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' align='center'>" . ($isSet($abn) ? "ABNL" : "") . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' nowrap>" . text($sysnamedisp) . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' nowrap>" . text($description) . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text'>" . text($diagnosis) . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text'>" . text($comments) . "</td>\n";
                echo " </tr>\n";
            } else { // treatment line
                echo " <tr>\n";
                echo "  <td class='text' align='center'>" . ($isSet($wnl) ? "Y" : "") . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' align='center'>&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' colspan='2' nowrap>" . text($description) . "&nbsp;&nbsp;</td>\n";
                echo "  <td class='text' colspan='2'>" . text($comments) . "</td>\n";
                echo " </tr>\n";
            }

            $sysnamedisp = '';
        } // end of line
    } // end of system name

    echo "</table>\n";
}
