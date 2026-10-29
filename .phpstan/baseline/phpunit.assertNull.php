<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^You should use assertNull\\(\\) instead of assertSame\\(null, \\$actual\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/squizlabs/php_codesniffer/tests/Core/Ruleset/ProcessRulesetShouldProcessElementTest.php',
];
$ignoreErrors[] = [
    'message' => '#^You should use assertNull\\(\\) instead of assertSame\\(null, \\$actual\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/squizlabs/php_codesniffer/tests/Core/Ruleset/ProcessRulesetShouldProcessElementTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
