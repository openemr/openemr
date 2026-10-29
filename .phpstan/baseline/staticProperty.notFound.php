<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\CachingHttpClient\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/CachingHttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\DataCollector\\\\HttpClientDataCollector\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/DataCollector/HttpClientDataCollector.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\EventSourceHttpClient\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/EventSourceHttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\MockHttpClient\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/MockHttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\NoPrivateNetworkHttpClient\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/NoPrivateNetworkHttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property class@anonymous/interface/modules/custom_modules/oe\\-module\\-ihi/vendor/symfony/http\\-client/Response/AmpResponseV4\\.php\\:294\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV4.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property class@anonymous/interface/modules/custom_modules/oe\\-module\\-ihi/vendor/symfony/http\\-client/Response/AmpResponseV5\\.php\\:291\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to an undefined static property Symfony\\\\Component\\\\HttpClient\\\\ScopingHttpClient\\:\\:\\$emptyDefaults\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/ScopingHttpClient.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
