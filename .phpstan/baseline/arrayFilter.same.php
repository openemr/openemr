<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(array\\<phpDocumentor\\\\Reflection\\\\Type\\>\\) to function array_filter does not contain falsy values, the array will always stay the same\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/phpdocumentor/type-resolver/src/TypeResolver.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
