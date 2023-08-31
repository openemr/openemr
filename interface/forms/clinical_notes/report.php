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

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\ClinicalNotesService;

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function clinical_notes_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $clinicalNotesService = new ClinicalNotesService();
    $records = $clinicalNotesService->getClinicalNotesForPatientForm($id, $pid, $encounter) ?? [];
    $data = array_filter($records, function ($val) {
        return $val['activity'] == ClinicalNotesService::ACTIVITY_ACTIVE;
    });

    $viewArgs = [
        'notes' => $data
    ];

    $twig = new TwigContainer(__DIR__, $GLOBALS['kernel']);
    $t = $twig->getTwig();
    echo $t->render('templates/report.html.twig', $viewArgs);
}
