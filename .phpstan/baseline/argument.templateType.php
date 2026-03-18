<?php declare(strict_types = 1);

// total 3 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unable to resolve the template type T in call to method OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:getSingleEntry\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
