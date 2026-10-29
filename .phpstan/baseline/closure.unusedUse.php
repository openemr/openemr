<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Anonymous function has an unused use \\$errorArray\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Runner.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous function has an unused use \\$lengthAttribute1\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/Tokenizers/PHP/AttributesParseError4Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous function has an unused use \\$errorArray\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Runner.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous function has an unused use \\$lengthAttribute1\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/Tokenizers/PHP/AttributesParseError4Test.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
