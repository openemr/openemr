<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:storeInboundFax\\(\\) has OpenEMR\\\\Modules\\\\FaxSMS\\\\Exception\\\\FaxDocumentException in PHPDoc @throws tag but it\'s not thrown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
