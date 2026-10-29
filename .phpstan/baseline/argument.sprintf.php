<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to sprintf contains 0 placeholders, 1 value given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/mpdf/mpdf/src/Mpdf.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
