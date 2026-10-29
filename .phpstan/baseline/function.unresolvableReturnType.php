<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type of call to function array_values contains unresolvable type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/serializer/Serializer.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
