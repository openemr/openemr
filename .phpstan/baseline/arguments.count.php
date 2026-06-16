<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function build_PMSFH invoked with 0 parameters, 1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 3 parameters, 0\\-1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\PatientAccessOnsiteService\\:\\:saveCredentials\\(\\) invoked with 4 parameters, 5 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
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
    'message' => '#^Class OpenEMR\\\\Core\\\\OEGlobalsBag constructor invoked with 2 parameters, 0\\-1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/OEHttpKernel.php',
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
    'message' => '#^Static method OpenEMR\\\\Common\\\\Csrf\\\\CsrfUtils\\:\\:collectCsrfToken\\(\\) invoked with 0 parameters, 1\\-2 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/PaginationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 3 parameters, 0\\-1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 2 parameters, 0\\-1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 2 parameters, 0\\-1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Twig/TwigExtensionIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Static method OpenEMR\\\\Common\\\\Utils\\\\FileUtils\\:\\:getExtensionFromMimeType\\(\\) invoked with 2 parameters, 1 required\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/FileUtilsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Static method OpenEMR\\\\Common\\\\Utils\\\\FormatMoney\\:\\:getFormattedMoney\\(\\) invoked with 3 parameters, 1\\-2 required\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/FormatMoneyTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 2 parameters, 0\\-1 required\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Core\\\\Kernel constructor invoked with 2 parameters, 0\\-1 required\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\ActionRouter constructor invoked with 3 parameters, 2 required\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ActionRouterTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
