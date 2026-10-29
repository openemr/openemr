<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Generator expects key type int, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Helper/TreeNode.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects key type int, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Helper/TreeNode.php',
];
$ignoreErrors[] = [
    'message' => '#^Generator expects key type int, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/process/InputStream.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
