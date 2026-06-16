<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^eval\\(\\) is forbidden\\. It executes arbitrary PHP code and is a critical security risk\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^eval\\(\\) is forbidden\\. It executes arbitrary PHP code and is a critical security risk\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^eval\\(\\) is forbidden\\. It executes arbitrary PHP code and is a critical security risk\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.math.php',
];
$ignoreErrors[] = [
    'message' => '#^eval\\(\\) is forbidden\\. It executes arbitrary PHP code and is a critical security risk\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/zip.lib.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
