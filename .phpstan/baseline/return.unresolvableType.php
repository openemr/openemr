<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return contains unresolvable type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
