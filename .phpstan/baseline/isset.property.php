<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthCalendarController\\:\\:\\$apptService \\(OpenEMR\\\\Services\\\\AppointmentService\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthCalendarController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthVideoRegistrationController\\:\\:\\$userRepository \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthUserRepository\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingLogger\\:\\:\\$onLogCompleteCallback \\(callable\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRQuestionnaireResponse\\:\\:\\$questionnaire \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCanonical\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFhirQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirCareTeamRestController\\:\\:\\$fhirCareTeamService \\(OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Billing\\\\BillingProcessor\\\\BillingLogger\\:\\:\\$onLogCompleteCallback \\(callable\\) in isset\\(\\) is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Billing/BillingLoggerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
