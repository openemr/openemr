<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array \\(array\\{\\}\\) to function array_values is empty, call has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/ReferralController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
