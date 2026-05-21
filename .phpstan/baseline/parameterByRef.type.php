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
    'message' => '#^Parameter &\\$compiled_content by\\-ref type of method Smarty_Compiler_Legacy\\:\\:_compile_file\\(\\) expects string, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$compiled_content by\\-ref type of method Smarty_Compiler_Legacy\\:\\:_compile_file\\(\\) expects string, string\\|null given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$indexes by\\-ref type of method Smarty_Compiler_Legacy\\:\\:_compile_smarty_ref\\(\\) expects string, array given\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$indexes by\\-ref type of method Smarty_Compiler_Legacy\\:\\:_compile_smarty_ref\\(\\) expects string, mixed given\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$output by\\-ref type of method Smarty_Compiler_Legacy\\:\\:_compile_compiler_tag\\(\\) expects string, mixed given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter &\\$input by\\-ref type of method PortalController\\:\\:UTF8Encode\\(\\) expects VARIANT, array\\|string given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
