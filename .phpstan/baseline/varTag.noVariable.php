<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var does not specify variable name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var does not specify variable name\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/public/index.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
