<?php declare(strict_types = 1);

// total 29 errors

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
$ignoreErrors[] = [
    'message' => '#^Function mysql_affected_rows not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_close not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_error not found\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_fetch_assoc not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_free_result not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_insert_id not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_ping not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_query not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_select_db not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_set_charset not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mysql_ping not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQL_PDO.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
