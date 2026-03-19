<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'enforce_signin_email\'\\) instead of get\\(\'enforce_signin_email\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'ptkr_show_pid\'\\) instead of get\\(\'ptkr_show_pid\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/RecallBoard/DisplayService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
