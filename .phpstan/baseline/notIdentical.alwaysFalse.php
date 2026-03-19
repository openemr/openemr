<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between false and false will always evaluate to false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and null will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and null will always evaluate to false\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and null will always evaluate to false\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
