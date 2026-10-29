<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot unset offset \'request_header\' on array\\{response_headers\\: array\\{\\}, url\\: mixed, error\\: null, canceled\\: false, http_method\\: string, http_code\\: 0, redirect_count\\: 0, start_time\\: 0\\.0, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/NativeHttpClient.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
