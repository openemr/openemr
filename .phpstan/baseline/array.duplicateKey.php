<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Array has 2 duplicate keys with value \'message_import\' \\(\'message_import\', \'message_import\'\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/FrontController.php',
];
$ignoreErrors[] = [
    'message' => '#^Array has 2 duplicate keys with value \'rphf\' \\(\'rphf\', \'rphf\'\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/mpdf/mpdf/src/Otl.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
