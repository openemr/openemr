<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private property League\\\\Flysystem\\\\InMemory\\\\StaticInMemoryAdapterRegistry\\:\\:\\$filesystems through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/league/flysystem-memory/StaticInMemoryAdapterRegistry.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
