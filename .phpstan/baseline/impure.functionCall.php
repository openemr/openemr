<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Impure call to function get_resource_type\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:resource\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
