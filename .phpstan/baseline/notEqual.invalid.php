<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\!\\=" between int and \\*NEVER\\* results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
