<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\ClaimRevApi\\:\\:GetAccessToken\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ConnectorApi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
