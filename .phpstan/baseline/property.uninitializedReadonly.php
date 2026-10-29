<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to an uninitialized readonly property Symfony\\\\Component\\\\DependencyInjection\\\\Argument\\\\LazyClosure\\:\\:\\$service\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Argument/LazyClosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Symfony\\\\Component\\\\DependencyInjection\\\\Argument\\\\LazyClosure has an uninitialized readonly property \\$service\\. Assign it in the constructor\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Argument/LazyClosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenPlanIt\\\\Modules\\\\IrelandCore\\\\Tests\\\\Integration\\\\VitalsApiIntegrationTest has an uninitialized readonly property \\$encounterFixtureManager\\. Assign it in the constructor\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Integration/VitalsApiIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenPlanIt\\\\Modules\\\\IrelandCore\\\\Tests\\\\Integration\\\\VitalsApiIntegrationTest has an uninitialized readonly property \\$fixtureManager\\. Assign it in the constructor\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Integration/VitalsApiIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenPlanIt\\\\Modules\\\\IrelandCore\\\\Tests\\\\Integration\\\\VitalsApiIntegrationTest has an uninitialized readonly property \\$encounterFixtureManager\\. Assign it in the constructor\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Integration/VitalsApiIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenPlanIt\\\\Modules\\\\IrelandCore\\\\Tests\\\\Integration\\\\VitalsApiIntegrationTest has an uninitialized readonly property \\$fixtureManager\\. Assign it in the constructor\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Integration/VitalsApiIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
