<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Common\\\\Utils\\\\RandomGenUtils\\:\\:DEFAULT_TOKEN_LENGTH\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Common\\\\Utils\\\\RandomGenUtils\\:\\:DEFAULT_TOKEN_LENGTH\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\Common\\\\Utils\\\\RandomGenUtils\\:\\:DEFAULT_TOKEN_LENGTH\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant OpenEMR\\\\USPS\\\\USPSBase\\:\\:TEST_API_URL\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
