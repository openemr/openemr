<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\PatientRestController\\:\\:getOne\\(\\) invoked with 1 parameter, 2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../apis/routes/_rest_routes_portal.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_PMSFH invoked with 0 parameters, 1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientAccessOnsiteService\\:\\:saveCredentials\\(\\) invoked with 4 parameters, 5 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Ccr\\\\Model\\\\CcrTable constructor invoked with 1 parameter, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/config/module.config.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Menu\\\\PatientMenuRole constructor invoked with 1 parameter, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Patient\\\\Cards\\\\PortalCard constructor invoked with 1 parameter, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ESign\\\\SignableIF\\:\\:sign\\(\\) invoked with 3 parameters, 1\\-2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^Class sms_clickatell constructor invoked with 3 parameters, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Class sms_tmb4 constructor invoked with 3 parameters, 2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Class GenericRouter constructor invoked with 0 parameters, 3 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Class GenericRouter constructor invoked with 0 parameters, 3 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Crypto\\\\CryptoInterface\\:\\:encryptStandard\\(\\) invoked with 1 parameter, 3 required\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Cqm\\\\CqmClient constructor invoked with 0 parameters, 2\\-4 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/test.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService constructor invoked with 1 parameter, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Validators\\\\BaseValidator\\:\\:validate\\(\\) invoked with 1 parameter, 2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Events\\\\Patient\\\\Summary\\\\PortalCredentialsTemplateDataFilterEvent constructor invoked with 2 parameters, 0 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\ActionRouter constructor invoked with 3 parameters, 2 required\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ActionRouterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\ActionRouter constructor invoked with 3 parameters, 2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerRouterTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
