<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function display_QP\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_form_id_of_existing_attendance_form\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getOrCreateProcedureType\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModLoad\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:setModuleState\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ModuleManagerListener\\:\\:setModuleState\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isValidPhone\\(\\) never returns array so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:processMessageStoreList\\(\\) never returns false so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:processMessageStoreList\\(\\) never returns string so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\EtherFaxClient\\:\\:clientHttpPost\\(\\) never returns bool so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\EtherFaxClient\\:\\:clientHttpPost\\(\\) never returns string so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Juggernaut\\\\OpenEMR\\\\Modules\\\\PriorAuthModule\\\\Controller\\\\ListAuthorizations\\:\\:formPriorAuth\\(\\) never returns false so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Juggernaut\\\\OpenEMR\\\\Modules\\\\PriorAuthModule\\\\Controller\\\\ListAuthorizations\\:\\:getAuthsFromModulePriorAuth\\(\\) never returns false so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Multipledb\\\\Controller\\\\MultipledbController\\:\\:getMultipledbTable\\(\\) never returns array so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Patientvalidation\\\\Controller\\\\PatientvalidationController\\:\\:getPatientDataTable\\(\\) never returns array so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:addRecurrent\\(\\) never returns array so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:addRecurrent\\(\\) never returns bool so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_order\\(\\) never returns bool so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getBarId\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabProviders\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabconfig\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patient_balance\\(\\) never returns float so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patient_balance\\(\\) never returns int so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_math\\(\\) never returns string so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.math.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceRequestService\\:\\:mapOrderTypeToCategory\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findIdentiferCodeValue\\(\\) never returns null so it can be removed from the return type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
