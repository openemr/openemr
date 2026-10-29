<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to new PHP_CodeSniffer\\\\Util\\\\Tokens\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/src/Runner.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to new PHP_CodeSniffer\\\\Util\\\\Tokens\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/src/Runner.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
