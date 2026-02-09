<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between Immunization\\\\Controller\\\\type and 10 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between OpenEMR\\\\Tests\\\\Fixtures\\\\the and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
