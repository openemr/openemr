<?php declare(strict_types = 1);

// total 5 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var does not specify variable name\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var does not specify variable name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFhirQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var above assignment does not specify variable name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
