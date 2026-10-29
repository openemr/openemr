<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>\\=" between \\*NEVER\\* and 70000 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/Generators/GeneratorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>\\=" between \\*NEVER\\* and 70000 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/Generators/GeneratorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
