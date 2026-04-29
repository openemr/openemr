<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter mixed of print cannot be converted to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
