<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$key with type string is incompatible with native type bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$pid with type mixed is not subtype of native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$container with type Application\\\\Plugin\\\\type is not subtype of native type Psr\\\\Container\\\\ContainerInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$container with type Application\\\\Plugin\\\\type is not subtype of native type Psr\\\\Container\\\\ContainerInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$arr with type string is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/htmlspecialchars.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$id with type string is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$target with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$periodStart with type DateTime\\|null is not subtype of native type DateTime\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$boundFilter with type string is incompatible with native type OpenEMR\\\\Events\\\\BoundFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/AbstractBoundFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$component with type string is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/CDA/CDAPostParseEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$components with type string is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/CDA/CDAPreParseEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$menu with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/EncounterMenuEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$formName with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/LoadEncounterFormFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$formname with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/LoadEncounterFormFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$pageName with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/LoadEncounterFormFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$globalsService with type array is incompatible with native type OpenEMR\\\\Services\\\\Globals\\\\GlobalsService\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Globals/GlobalsInitializedEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$patientData with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/BeforePatientCreatedEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$patientData with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/BeforePatientUpdatedEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$patientData with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/PatientBeforeCreatedAuxEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$patientData with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/PatientCreatedEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$fhirResource with type array is incompatible with native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$fhirResource with type array is incompatible with native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$resource with type array is incompatible with native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$fhirResource with type array is incompatible with native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationFacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$event_data with type mixed is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param for parameter \\$loginLocation with type array\\|null is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
