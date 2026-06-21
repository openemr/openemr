<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$out by\\-ref type of function era_payments_callback\\(\\) expects array, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$out by\\-ref type of function eob_process_era_callback\\(\\) expects array, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$out by\\-ref type of function eob_search_era_callback\\(\\) expects array, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$matchreq by\\-ref type of function receive_hl7_results\\(\\) expects string, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$aTotals by\\-ref type of function ippfReceiptDetailLine\\(\\) expects string, mixed given\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$input by\\-ref type of method PortalController\\:\\:UTF8Encode\\(\\) expects VARIANT, array\\|string given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$history by\\-ref type of method OpenEMR\\\\Tests\\\\Isolated\\\\Modules\\\\FaxSMS\\\\RestClient\\\\SignalWireRestClientTest\\:\\:makeClient\\(\\) expects array\\<int, array\\{request\\: Psr\\\\Http\\\\Message\\\\RequestInterface, response\\: mixed\\}\\>, array\\|ArrayAccess\\<int, array\\> given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Modules/FaxSMS/Controller/SignalWireRestClientTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
