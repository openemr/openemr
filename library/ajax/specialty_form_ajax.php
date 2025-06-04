<?php

/**
 * For various specialty forms to call from dialog using the
 * Ajax, iFrame, Alert, Confirm or HTML modes. Just follow
 * the example patient previous names history form pattern shown below.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientNameHistoryService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$post_items = $_POST;
if ($post_items['task_name_history'] === 'save') {
    nameHistorySave($post_items);
}
if ($post_items['task_name_history'] === 'delete') {
    nameHistoryDelete($post_items['id']);
}

function nameHistoryDelete($id)
{
    $patientNameService = new PatientNameHistoryService();
    $is_ok = $patientNameService->deletePatientNameHistoryById($id);
    $is_ok =  empty($is_ok) ? xlt("Success") : xlt("Failed");
    echo js_escape($is_ok);
    exit;
}

function nameHistorySave($post_items)
{
    if (!empty($post_items['previous_name_enddate'])) {
        $date = new DateTime($post_items['previous_name_enddate']);
        $post_items['previous_name_enddate'] = $date->format('Y-m-d');
    }
    $patientNameService = new PatientNameHistoryService();
    $is_new = $patientNameService->createPatientNameHistory($post_items['pid'], $post_items);
    $name = $patientNameService->formatPreviousName($post_items);

    $ret = array();
    if (!empty($is_new)) {
        $ret['id'] = $is_new;
        $ret['name'] = $name;
    }

    echo js_escape($ret);
    exit;
}
