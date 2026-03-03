<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between OpenEMR\\\\Services\\\\Globals\\\\Effective and \\-1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
