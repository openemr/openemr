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
use function OpenEMR\Forms\PhysicalExam\scalar_string;

require_once(__DIR__ . '/../../globals.php');
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/api.inc.php");
require_once(__DIR__ . '/lines.php');

function physical_exam_report(int $pid, int $encounter, int $cols, int $id): void
{
    // A stored checkbox/text value counts as "set" when non-empty and not '0'.
    $isSet = static fn (string $value): bool => $value !== '' && $value !== '0';

    $rows = [];
    foreach (QueryUtils::fetchRecords('SELECT * FROM form_physical_exam WHERE forms_id = ?', [$id]) as $row) {
        $rows[scalar_string($row['line_id'] ?? null)] = $row;
    }

    $body = '';
    foreach (physical_exam_lines() as $system) {
        $systemCell = text($system['label']);
        foreach ($system['lines'] as $line_id => $description) {
            $linedbrow = $rows[$line_id] ?? [];
            $wnl = scalar_string($linedbrow['wnl'] ?? null);
            $abn = scalar_string($linedbrow['abn'] ?? null);
            $diagnosis = scalar_string($linedbrow['diagnosis'] ?? null);
            $comments = scalar_string($linedbrow['comments'] ?? null);
            if (!($isSet($wnl) || $isSet($abn) || $isSet($diagnosis) || $isSet($comments))) {
                continue;
            }

            $descriptionCell = text($description);
            $commentsCell = text($comments);
            if ($system['code'] !== '*') { // observation line
                $wnlCell = $isSet($wnl) ? 'WNL' : '';
                $abnCell = $isSet($abn) ? 'ABNL' : '';
                $diagnosisCell = text($diagnosis);
                $body .= <<<HTML
                    <tr>
                        <td class='text' align='center'>{$wnlCell}&nbsp;&nbsp;</td>
                        <td class='text' align='center'>{$abnCell}&nbsp;&nbsp;</td>
                        <td class='text' nowrap>{$systemCell}&nbsp;&nbsp;</td>
                        <td class='text' nowrap>{$descriptionCell}&nbsp;&nbsp;</td>
                        <td class='text'>{$diagnosisCell}&nbsp;&nbsp;</td>
                        <td class='text'>{$commentsCell}</td>
                    </tr>
                    HTML;
            } else { // treatment line
                $wnlCell = $isSet($wnl) ? 'Y' : '';
                $body .= <<<HTML
                    <tr>
                        <td class='text' align='center'>{$wnlCell}&nbsp;&nbsp;</td>
                        <td class='text' align='center'>&nbsp;&nbsp;</td>
                        <td class='text' colspan='2' nowrap>{$descriptionCell}&nbsp;&nbsp;</td>
                        <td class='text' colspan='2'>{$commentsCell}</td>
                    </tr>
                    HTML;
            }

            // The system heading only shows on its first populated line.
            $systemCell = '';
        }
    }

    echo <<<HTML
        <table cellpadding='0' cellspacing='0'>{$body}</table>
        HTML;
}
