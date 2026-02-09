<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Elseif condition is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
