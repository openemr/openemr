<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above a function has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above a function has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above a method has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above a method has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFhirQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above a method has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
