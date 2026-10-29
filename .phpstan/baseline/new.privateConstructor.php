<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHP_CodeSniffer\\\\Tests\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHPUnit\\\\Framework\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHPUnit\\\\Framework\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Standards/AllSniffs.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHP_CodeSniffer\\\\Tests\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHPUnit\\\\Framework\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/AllTests.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot instantiate class PHPUnit\\\\Framework\\\\TestSuite via private constructor PHPUnit\\\\Framework\\\\TestSuite\\:\\:__construct\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Standards/AllSniffs.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
