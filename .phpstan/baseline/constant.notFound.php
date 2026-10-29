<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_ERROR not found\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_INFO not found\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_WARNING not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_ERROR not found\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_INFO not found\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant ZEND_MONITOR_EVENT_SEVERITY_WARNING not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
