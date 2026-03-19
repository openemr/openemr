<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var does not specify variable name\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/globals.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
