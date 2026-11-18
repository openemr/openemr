<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$out by\\-ref type of function era_payments_callback\\(\\) expects array, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
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

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
