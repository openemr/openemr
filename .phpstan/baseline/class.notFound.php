<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function pnModURL\\(\\) has invalid return type absolute\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\:\\:getDateRegistered\\(\\) has invalid return type Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\DateTime\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Models/TeleHealthUser.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\TelehealthGlobalConfig\\:\\:\\$publicWebPath has unknown class Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\publicWebPath as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Modules\\\\Dorn\\\\CustomSkeletonFHIRResourceService not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\OrderStatusViewModel\\:\\:\\$createdDateTimeUtc has unknown class OpenEMR\\\\Modules\\\\Dorn\\\\models\\\\DateTime as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/models/OrderStatusViewModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\LogProperties\\:\\:\\$container has unknown class OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\Container as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Application\\\\Listener\\\\Listener\\:\\:\\$listeners has unknown class Laminas\\\\Stdlib\\\\CallbackHandler as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Listener/Listener.php',
];
$ignoreErrors[] = [
    'message' => '#^Iterating over an object of an unknown class Installer\\\\Controller\\\\unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$data of method Installer\\\\Controller\\\\InstallerController\\:\\:getContent\\(\\) has invalid type Installer\\\\Controller\\\\unknown_type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$RECEIVING_APPLICATION on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$RECEIVING_FACILITY on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$SENDING_FACILITY on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method buildRequest\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method buildResultAck\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getResultsBatch\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method sendResultAck\\(\\) on an unknown class QuestResultClient\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class QuestResultClient not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$direction of function receive_hl7_results\\(\\) has invalid type char\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiated class MedExApi\\\\DateTime not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listingCDRReminderLog\\(\\) has invalid return type sqlret\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prevSetting\\(\\) has invalid return type Prior\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_header_2\\(\\) has invalid return type outputs\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule\\:\\:\\$groups has unknown class RuleTargetActionGroups as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/Rule.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:rehashPassword\\(\\) has invalid return type s\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:\\$notes has unknown class OpenEMR\\\\Common\\\\ORDataObject\\\\Note as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$uuid of method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:getRegistryRecordForUuid\\(\\) has invalid type OpenEMR\\\\Common\\\\Uuid\\\\binary\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Gacl\\\\GaclApi\\:\\:get_object\\(\\) has invalid return type OpenEMR\\\\Gacl\\\\ADORecordSet\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$APP_ROOT has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\app as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$FHIR_ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\fhir as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$PORTAL_ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\portal as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$ROOT_URL has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\root as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$ROUTE_MAP has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\routemap as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$localCall has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$notRestCall has unknown class OpenEMR\\\\RestControllers\\\\Config\\\\set as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\RestControllers\\\\TransactionRestController\\:\\:CreateTransaction\\(\\) has invalid return type OpenEMR\\\\RestControllers\\\\a\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:parseOpenEMRRecord\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getValues\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method hasCodeValue\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$openEMRSearchParameters of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:searchForOpenEMRRecords\\(\\) has invalid type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method hasCodeValue\\(\\) on an unknown class OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$openEMRSearchParameters of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:searchForOpenEMRRecords\\(\\) has invalid type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) has invalid return type OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Globals\\\\UserSettingsService\\:\\:prevSetting\\(\\) has invalid return type OpenEMR\\\\Services\\\\Globals\\\\Prior\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:\\$dateType has unknown class OpenEMR\\\\Services\\\\Search\\\\Tracks as its type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method clearTelemetryData\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method expects\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method fetchUsageRecords\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method saveTelemetryEvent\\(\\) on an unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$repository contains unknown class OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceMappingTest\\:\\:findIdentiferCodeValue\\(\\) has invalid return type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\the\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
