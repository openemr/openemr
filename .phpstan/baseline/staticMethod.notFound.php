<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidIpAddress\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidNPI\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/UserEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/FhirUserClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\HttpUtils\\:\\:base64url_decode\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createMissingUuidForRow\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidPort\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/DbUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Session\\\\SessionUtil\\:\\:coreSessionStart\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/PHPSessionWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/NetworkUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\HttpUtils\\:\\:base64url_decode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createMissingUuidForRow\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUuid\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidCAPostalCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUSPostalCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidPhoneNumber\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateFloat\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateFloat\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createMissingUuidForRow\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/UserService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\:\\:instance\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidIpAddress\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\HttpUtils\\:\\:base64url_decode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/HttpUtilsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidCAPostalCode\\(\\)\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidIpAddress\\(\\)\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidNPI\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidPhoneNumber\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidPostalCode\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUSPostalCode\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUrl\\(\\)\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:isValidUuid\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateFloat\\(\\)\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Utils\\\\ValidationUtils\\:\\:validateInt\\(\\)\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Uuid\\\\UuidRegistry\\:\\:createMissingUuidForRow\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Uuid/UuidRegistryTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
