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
        // TODO: these styles should really be moved somewhere else... but at least we abstracted them into css classes so we
        // can eventually move them elsewhere
        ?>
        <style>
            .clinical-notes.clinical-notes-report
            {
                border-collapse: collapse;
                border-spacing: 0;
                width: 100%;
            }
            .clinical-notes.clinical-notes-report td
            {
                text-align: left;
                border: 1px solid #ccc;
                padding: 4px;
            }
        </style>
        <table class="clinical-notes clinical-notes-report">
            <thead>
                <tr>
                    <th><?php echo xlt('Date'); ?></th>
                    <th><?php echo xlt('Note Type'); ?></th>
                    <th><?php echo xlt('Narrative'); ?></th>
                    <th><?php echo xlt('Author'); ?></th>
                    <th><?php echo xlt('Note Category'); ?></th>
                    <th><?php echo xlt('Code'); ?></th>
                </tr>
            </thead>
        <?php
        foreach ($data as $key => $value) {
            ?>
            <tr>
                <td><span class='text'><?php echo text($value['date']); ?></span></td>
                <td><span class='text text-wrap'><?php echo text($value['codetext']); ?></span></td>
                <td><span class='text'><?php echo text($value['description']); ?></span></td>
                <td><span class='text'><?php echo text($value['user']); ?></span></td>
                <td><span class='text text-wrap'><?php echo text($value['category_title']); ?></span></td>
                <td><span class='text'><?php echo text($value['code']); ?></span></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
}
?>
