<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../setup.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
