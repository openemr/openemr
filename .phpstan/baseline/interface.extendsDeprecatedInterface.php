<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Interface Doctrine\\\\Common\\\\Cache\\\\MultiOperationCache extends deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiDeleteCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/MultiOperationCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Interface Doctrine\\\\Common\\\\Cache\\\\MultiOperationCache extends deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiGetCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/MultiOperationCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Interface Doctrine\\\\Common\\\\Cache\\\\MultiOperationCache extends deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiPutCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/MultiOperationCache.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
