<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Possibly impure instantiation of class ReflectionProperty in pure method Webmozart\\\\Assert\\\\Assert\\:\\:isInitialized\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
