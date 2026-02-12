<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Variable \\$eventDispatcher in PHPDoc tag @var does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$module in PHPDoc tag @var does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$eventDispatcher in PHPDoc tag @var does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$module in PHPDoc tag @var does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dispatcher in PHPDoc tag @var does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Kernel.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
