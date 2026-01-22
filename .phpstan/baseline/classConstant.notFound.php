<?php declare(strict_types = 1);

// total 4 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\GlobalConfig\\:\\:CONFIG_OPTION_ENCRYPTED\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\GlobalConfig\\:\\:CONFIG_OPTION_TEXT\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Modules\\\\Dorn\\\\GlobalConfig\\:\\:CONFIG_OPTION_ENCRYPTED\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Modules\\\\Dorn\\\\GlobalConfig\\:\\:CONFIG_OPTION_TEXT\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/GlobalConfig.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
