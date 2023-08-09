<?php

require_once("../globals.php");

$controller = new \OpenEMR\FHIR\SMART\SmartLaunchController();

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
