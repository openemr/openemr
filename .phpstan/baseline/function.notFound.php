<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function set_magic_quotes_runtime not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cms_field_to_lbf not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cms_portal_call not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gzopen64 not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateMessage not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_layout_rows_group_new not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cms_portal_call not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generateCheckoutReceipt not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cms_portal_call not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function acl_check not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateInvoiceRefNumber not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function NumberToText not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/NumberToText.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_sftp_connect not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
