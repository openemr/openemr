<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^@dataProvider dataStdIn related method must be static in PHPUnit 10 and newer\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Standards/Generic/Tests/PHP/SyntaxUnitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^@dataProvider dataStdIn related method must be static in PHPUnit 10 and newer\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Standards/Generic/Tests/PHP/SyntaxUnitTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
