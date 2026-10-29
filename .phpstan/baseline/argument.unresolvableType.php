<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function array_values contains unresolvable type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/serializer/Serializer.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$expected of method PHPUnit\\\\Framework\\\\Assert\\:\\:assertSame\\(\\) contains unresolvable type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/Files/File/GetMethodParametersTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$expected of method PHPUnit\\\\Framework\\\\Assert\\:\\:assertSame\\(\\) contains unresolvable type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/Files/File/GetMethodParametersTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
