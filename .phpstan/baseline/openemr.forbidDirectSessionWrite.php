<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/openplanit/oe-opit-compatibility/src/OpenEMRCompatibilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/openplanit/oe-opit-compatibility/src/OpenEMRCompatibilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/openplanit/oe-opit-compatibility/src/OpenEMRCompatibilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/src/OpenEMRCompatibilityService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
