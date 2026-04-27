<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(array\\{\'none\', \'evidence\', \'predictive\'\\}\\) of array_values is already a list, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
