<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Match arm comparison between string and null is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Test/HarFileResponseFactory.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
