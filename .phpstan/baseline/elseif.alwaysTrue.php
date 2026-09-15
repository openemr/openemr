<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Elseif condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
