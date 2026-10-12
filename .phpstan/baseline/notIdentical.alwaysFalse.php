<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and null will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
