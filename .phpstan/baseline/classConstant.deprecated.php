<?php declare(strict_types = 1);

// total 5 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Fetching deprecated class constant DEBUG of class Monolog\\\\Logger\\:
Use \\\\Monolog\\\\Level\\:\\:Debug$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching deprecated class constant WARNING of class Monolog\\\\Logger\\:
Use \\\\Monolog\\\\Level\\:\\:Warning$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching deprecated class constant FHIR_PROCEDURE_STATUS_COMPLETED of class OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:
use EventStatusEnum\\:\\:COMPLETED\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching deprecated class constant FHIR_PROCEDURE_STATUS_IN_PROGRESS of class OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:
use EventStatusEnum\\:\\:IN_PROGRESS\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching deprecated class constant FHIR_PROCEDURE_STATUS_UNKNOWN of class OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:
use EventStatusEnum\\:\\:UNKNOWN\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
