<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between OpenEMR\\\\Tests\\\\Fixtures\\\\the and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
