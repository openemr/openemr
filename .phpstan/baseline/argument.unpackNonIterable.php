<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Only iterables can be unpacked, mixed given in argument \\#1\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/chart_review_pids.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
