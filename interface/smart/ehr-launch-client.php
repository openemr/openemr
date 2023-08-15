<?php

/**
 * ehr-launch-client.php  Main entry point for the OpenEMR OAUTH2 / SMART client in ehr launch
 * Allows a smart app to launch into the OpenEMR EHR in a seamless interaction
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\SMART\SmartLaunchController;

$controller = new SmartLaunchController();

$intentData = [];
try {
    $intentData['appointment_id'] = $_REQUEST['appointment_id'] ?? null;
    $controller->redirectAndLaunchSmartApp(
        $_REQUEST['intent'] ?? null,
        $_REQUEST['client_id'] ?? null,
        $_REQUEST['csrf_token'] ?? null,
        $intentData
    );
} catch (CsrfInvalidException $exception) {
    CsrfUtils::csrfNotVerified();
} catch (AccessDeniedException $exception) {
    (new SystemLogger())->critical($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die();
} catch (Exception $exception) {
    (new SystemLogger())->error($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die("Unknown system error occurred");
}
