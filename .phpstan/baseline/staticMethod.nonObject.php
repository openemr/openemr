<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot call static method getServiceType\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call static method getServiceType\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call static method z_xlt\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
