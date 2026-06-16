<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\Header\\:\\:readConfigFile\\(\\) should return array but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\Header\\:\\:setupHeader\\(\\) should return string but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/Header.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
