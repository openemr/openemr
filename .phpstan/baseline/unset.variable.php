<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function unset\\(\\) contains undefined variable \\$serialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/PhpArrayAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function unset\\(\\) contains undefined variable \\$cell\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/mpdf/mpdf/src/Mpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function unset\\(\\) contains undefined variable \\$content\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/mpdf/mpdf/src/Mpdf.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
