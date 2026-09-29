<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$adminSettingsController \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\Admin\\\\TeleHealthUserAdminController\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$calendarController \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthCalendarController\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$patientAdminSettingsController \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\Admin\\\\TeleHealthPatientAdminController\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$patientPortalController \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthPatientPortalController\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$personSettingsRepository \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthPersonSettingsRepository\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$providerRepository \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Repository\\\\TeleHealthProviderRepository\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Bootstrap\\:\\:\\$registrationController \\(Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleHealthVideoRegistrationController\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\AccessTokenRepository\\:\\:\\$builder \\(OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\SMARTSessionTokenContextBuilder\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:\\$_address \\(OpenEMR\\\\Common\\\\ORDataObject\\\\Address\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ORDataObject\\\\ContactAddress\\:\\:\\$_contact \\(OpenEMR\\\\Common\\\\ORDataObject\\\\Contact\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\ORDataObject\\\\Person\\:\\:\\$uuid \\(null\\) in empty\\(\\) is always falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\AppointmentService\\:\\:\\$encounterService \\(OpenEMR\\\\Services\\\\EncounterService\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\AppointmentService\\:\\:\\$patientService \\(OpenEMR\\\\Services\\\\PatientService\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:\\$serviceLocator \\(OpenEMR\\\\Services\\\\FHIR\\\\Utils\\\\FhirServiceLocator\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Cqm\\\\Qdm\\\\Patient\\:\\:\\$id \\(OpenEMR\\\\Cqm\\\\Qdm\\\\Identifier\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Qrda\\\\Cat3\\:\\:\\$patient \\(OpenEMR\\\\Cqm\\\\Qdm\\\\Patient\\) in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
