<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
