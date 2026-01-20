<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to deprecated static property \\$TestMode of class RequestUtil\\:
use \\$VALIDATE_FILE_UPLOAD instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
