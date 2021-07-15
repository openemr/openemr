<?php

/**
 * Clinical Notes form report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
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
        <table class="table w-100">
            <thead>
            <tr>
                <th class="border p-1"><?php echo xlt('Date'); ?></th>
                <th class="border p-1"><?php echo xlt('Note Type'); ?></th>
                <th class="border p-1"><?php echo xlt('Narrative'); ?></th>
                <th class="border p-1"><?php echo xlt('Author'); ?></th>
                <th class="border p-1"><?php echo xlt('Note Category'); ?></th>
                <th class="border p-1"><?php echo xlt('Code'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($data as $key => $value) {
                ?>
                <tr>
                    <td class="border p-1"><span class='text'><?php echo text($value['date']); ?></span></td>
                    <td class="border p-1"><span class='text text-wrap'><?php echo text($value['codetext']); ?></span></td>
                    <td class="border p-1"><span class='text'><?php echo text($value['description']); ?></span></td>
                    <td class="border p-1"><span class='text'><?php echo text($value['user']); ?></span></td>
                    <td class="border p-1"><span class='text text-wrap'><?php echo text($value['category_title']); ?></span></td>
                    <td class="border p-1"><span class='text'><?php echo text($value['code']); ?></span></td>
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
