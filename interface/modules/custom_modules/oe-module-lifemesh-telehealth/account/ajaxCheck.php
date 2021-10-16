<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Brady Miller <brady.g.miller@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

require_once "../../../../globals.php";
require_once "../controller/Container.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;

header('Content-type: application/json');

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"], 'lifemesh')) {
    CsrfUtils::csrfNotVerified();
}

// set if will be checking to see if patient is already signed into session
$eid = $_POST['eid'] ?? null;

// no acl check since this needs to be accessed by entire practice from calendar

// check for existence and status of lifemesh account
$container = new Container();
$credentials =  $container->getDatabase()->getCredentials();
$isCredentials = (!empty($credentials) && !empty($credentials[0]) && !empty($credentials[1]));
$status = [];
if ($isCredentials) {
    $app = $container->getAppDispatch();
    $subscriberStatus = $app->apiRequest($credentials[1], $credentials[0], 'accountCheck');
    if (!$subscriberStatus) {
        $status['status'] = "no";
        $status['statusMessage'] = "Not working. " . $app->getStatusMessage();
    } else {
        $status['status'] = "ok";
        $status['statusMessage'] = "OK";
        if (!empty($eid)) {
            $patientCheckSignon = $app->apiCheckPatientStatus($credentials[1], $credentials[0], 'checkPatientStatus');
            if (!empty($patientCheckSignon) && ($patientCheckSignon['patient_accessed_session'] == "true")) {
                $status['patientSignon'] = "yes";
            } else{
                $status['patientSignon'] = "no";
            }
        }
    }
} else {
    $status['status'] = "no";
    $status['statusMessage'] = "Not functional. A subscriber is not configured in the Lifemesh Telehealth module.";
}

echo json_encode($status);
exit;
