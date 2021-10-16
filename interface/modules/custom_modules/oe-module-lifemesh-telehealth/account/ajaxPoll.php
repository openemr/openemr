<?php

/**
 * ajaxPoll.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../../globals.php";
require_once "../controller/Container.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;

header('Content-type: application/json');

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"], 'lifemesh')) {
    CsrfUtils::csrfNotVerified();
}

$eid = $_POST['eid'] ?? null;

// no acl check since this needs to be accessed by entire practice from calendar

// poll to see if patient has logged into session
$container = new Container();
$credentials =  $container->getDatabase()->getCredentials();
$isCredentials = (!empty($credentials) && !empty($credentials[0]) && !empty($credentials[1]));
$poll = [];
if ($isCredentials && !empty($eid)) {
    $app = $container->getAppDispatch();
    $patientCheckSignon = $app->apiCheckPatientStatus($credentials[1], $credentials[0], 'checkPatientStatus');
    if (!empty($patientCheckSignon) && ($patientCheckSignon['patient_accessed_session'] == "true")) {
        $poll['patientSignon'] = "yes";
    } else{
        $poll['patientSignon'] = "no";
    }
}

echo json_encode($poll);
exit;
