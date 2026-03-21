<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of and is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
