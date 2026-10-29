<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Static call to instance method PHPUnit\\\\TextUI\\\\TestRunner\\:\\:run\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Static call to instance method PHPUnit\\\\TextUI\\\\TestRunner\\:\\:run\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Standards/AllSniffs.php',
];
$ignoreErrors[] = [
    'message' => '#^Static call to instance method PHPUnit\\\\TextUI\\\\TestRunner\\:\\:run\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Static call to instance method PHPUnit\\\\TextUI\\\\TestRunner\\:\\:run\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Standards/AllSniffs.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
