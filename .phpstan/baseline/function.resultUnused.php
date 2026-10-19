<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function rtrim\\(\\) on a separate line has no effect\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
