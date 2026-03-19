<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unused result of ternary operator\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
