<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between 0 and 0 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between 0 and 0 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between 0 and 0 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between 0 and \\-1 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between 0 and 0 is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
