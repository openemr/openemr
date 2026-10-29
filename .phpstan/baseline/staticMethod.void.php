<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of method PHPUnit\\\\Framework\\\\TestSuite\\:\\:run\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/TestSuite.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method PHPUnit\\\\Framework\\\\TestSuite\\:\\:run\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/TestSuite7.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method PHPUnit\\\\Framework\\\\TestSuite\\:\\:run\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/TestSuite.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method PHPUnit\\\\Framework\\\\TestSuite\\:\\:run\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/TestSuite7.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
