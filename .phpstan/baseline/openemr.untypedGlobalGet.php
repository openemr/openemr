<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'enforce_signin_email\'\\) instead of get\\(\'enforce_signin_email\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
