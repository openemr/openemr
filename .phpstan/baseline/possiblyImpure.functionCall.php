<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function class_exists\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:objectish\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function enum_exists\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:valueToString\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function is_callable\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:isCallable\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function iterator_to_array\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:isAnyOf\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function preg_match\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:notRegex\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function preg_match\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:notWhitespaceOnly\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function preg_match\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:regex\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function preg_match\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:unicodeLetters\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function preg_match\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:uuid\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:alnum\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:alpha\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:digits\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:lower\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:startsWithLetter\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to function setlocale\\(\\) in pure method Webmozart\\\\Assert\\\\Assert\\:\\:upper\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/webmozart/assert/src/Assert.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
