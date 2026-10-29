<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^The overwriting return is on this line\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Fixtures/IrelandVitalsFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^This throw is overwritten by a different one in the finally block below\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Fixtures/IrelandVitalsFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^The overwriting return is on this line\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Fixtures/IrelandVitalsFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^This throw is overwritten by a different one in the finally block below\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Fixtures/IrelandVitalsFixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
