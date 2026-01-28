<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:exportCustomTables\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
