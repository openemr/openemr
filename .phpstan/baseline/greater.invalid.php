<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Files/File.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Fixer.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Reports/Cbf.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 2 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Ruleset.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Runner.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Files/File.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Fixer.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Reports/Cbf.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 2 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Ruleset.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between \\*NEVER\\* and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Runner.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
