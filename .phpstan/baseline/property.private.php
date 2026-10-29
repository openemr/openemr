<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to private property \\$_debug of parent class OpenEMR\\\\Gacl\\\\Gacl\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Integration/OPITPermissionsInstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to private property \\$_debug of parent class OpenEMR\\\\Gacl\\\\Gacl\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Integration/OPITPermissionsInstallerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
