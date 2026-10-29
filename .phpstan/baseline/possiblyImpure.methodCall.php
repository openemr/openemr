<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method Webmozart\\\\Assert\\\\Assert\\:\\:strlen\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:lengthBetween\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method Webmozart\\\\Assert\\\\Assert\\:\\:strlen\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:length\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method Webmozart\\\\Assert\\\\Assert\\:\\:strlen\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:maxLength\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method Webmozart\\\\Assert\\\\Assert\\:\\:strlen\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:minLength\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method stdClass\\:\\:__toString\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:valueToString\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
