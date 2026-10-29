<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Missing call to parent\\:\\:setUp\\(\\) method\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/tests/Uat/Live/LiveHseTestCase.php',
];
$ignoreErrors[] = [
    'message' => '#^Missing call to parent\\:\\:tearDown\\(\\) method\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/tests/Uat/Live/LiveHseTestCase.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
