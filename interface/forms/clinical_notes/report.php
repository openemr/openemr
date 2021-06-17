<?php

/**
 * Clinical Notes form report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ClinicalNotesService;

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");

function clinical_notes_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $clinicalNotesService = new ClinicalNotesService();
    $records = $clinicalNotesService->getClinicalNotesForPatientForm($id, $pid, $encounter) ?? [];
    $data = array_filter($records, function ($val) {
        return $val['activity'] == ClinicalNotesService::ACTIVITY_ACTIVE;
    });

    if ($data) {
        ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class='bold'><?php echo xlt('Code'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class='bold'><?php echo xlt('Note Type'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class='bold'><?php echo xlt('Author'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px; min-width:50%;max-width:55%;'><span class='bold'><?php echo xlt('Narrative'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class='bold'><?php echo xlt('Date'); ?></span></td>
            </tr>
        <?php
        foreach ($data as $key => $value) {
            ?>
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class='text'><?php echo text($value['code']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class='text text-wrap'><?php echo text($value['codetext']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class='text'><?php echo text($value['user']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class='text'><?php echo text($value['description']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class='text'><?php echo text($value['date']); ?></span></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}
?>
