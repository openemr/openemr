<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Match arm comparison between 9 and 9 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-dumper/Caster/FFICaster.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
