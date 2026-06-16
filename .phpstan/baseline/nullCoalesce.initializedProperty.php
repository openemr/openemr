<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:\\$baseDir on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
