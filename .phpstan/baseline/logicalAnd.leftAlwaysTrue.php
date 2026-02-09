<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Left side of and is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
