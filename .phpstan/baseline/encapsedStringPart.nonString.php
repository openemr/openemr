<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../Documentation/help_files/sl_eob_help.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$login \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pass \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$res\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row2\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$err \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$phimail_username \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$our_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/billing/load_fee_schedule.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$otherID \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupscore.cli.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$doc_pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$docdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$document_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$email \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$provider_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$type_title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$email \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pFName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient\\-\\>id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/chart_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limit_query \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/download_qrda.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$EXPORT_PATH \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$seq \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tag \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$seq \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tagtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/zutil.cli.doc_import.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_section_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_section_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_section_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>trace \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$site_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../index.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$appt_time \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'fname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'lname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'phone_home\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$response\\-\\>ErrorMessage \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/emailnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/settingsnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/smsnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/billing/edi_270.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/billing/edi_271.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$val \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$Ins \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$Modifier \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ReasonCodeDB \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/indigent_patients_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/new_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/new_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$AdjustmentCode \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$PaymentMethod \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$PaymentType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$screen \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$Modifier \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_pat_sel.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/print_billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num1.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num2.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num3.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$PaymentDateString \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$reason \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codekey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$csc \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$eraname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$production_date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rmk \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aPatFName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aPatID \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/code_systems/standard_tables_manage.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$taxrates \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$whid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$facid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$whid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$catstring \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ffname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$inbase \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$newid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'form_exit_url\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bpd \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bps \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_category \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_code_text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_code_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_content \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_fee \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_item \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_modifier \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_subcategory \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clone_units \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code_text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$days_ago \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dob \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$enc_date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fee \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$height \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modifier \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pulse \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$temperature \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$units \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$weight \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'form_exit_url\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'form_exit_url\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$BS_COL_CLASS \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code_text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lino \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/care_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/care_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/dictation/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/dictation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/dictation/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$LENS_MATERIAL \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/SpectacleRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/SpectacleRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/help.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$providerID \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/js/eye_base.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$providerNAME \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/js/eye_base.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/js/eye_base.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$W \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$i \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tabindex \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$term \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v_js_includes \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/initialize_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/initialize_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'rootdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_POST\\[\'form_checksum\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$justify \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$justifystyle \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lino \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$liprovstyle \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$usbillstyle \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/gad7.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/newpatient/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newpatient/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/note/print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/note/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/observation/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/observation/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/observation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/phq9/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$account \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$account_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'rootdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/sdoh/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/track_anything/create.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/track_anything/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dir\\[\'directory\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webroot \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/help_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/language.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/login_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$end_date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$listid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$zipname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_group_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_patient_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$columns\\[\'modname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$columns\\[\'name\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$columns\\[\'value\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$func \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'description\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'directory\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'displayname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'name\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'regid\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'state\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'type\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulescolumn\\[\'version\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulestable \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulevarscolumn\\[\'modname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulevarscolumn\\[\'name\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulevarscolumn\\[\'value\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulevarstable \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bgcolor1 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bgcolor2 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cat_table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$close \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jumpday \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jumpmonth \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jumpyear \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$textcolor2 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$topics_column\\[\'topicid\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$topics_column\\[\'topicname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$topics_column\\[\'topictext\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$topics_table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pcSmarty.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pcSmarty.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pcSmarty.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$c\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$class \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$label \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pntable\\[\'postcalendar_events\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$t\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$t\\[\'text\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$background \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bgbackground \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bgcolor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$border \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$caparray \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$capcolor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$capicon \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$captionfont \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$captionsize \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$closecolor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$closefont \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$closesize \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$delay \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fgbackground \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fgcolor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fixx \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fixy \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$frame \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$function \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$height \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$inarray \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$offsetx \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$offsety \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$padx \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pady \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$snapx \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$snapy \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$textcolor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$textfont \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$textsize \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$timeout \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$width \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$eid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_url.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$args\\[\'class\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$mdir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcTemplate \\(non\\-empty\\-list\\<string\\>\\|non\\-falsy\\-string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$viewtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dels \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modversion\\[\'version\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$msg \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pntable\\[\'postcalendar_categories\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadminapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pntable\\[\'postcalendar_categories\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadminapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$year \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$calFilter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cattable \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$minutes \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pcDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$s_category \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$s_keywords \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$s_topic \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sort \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_view \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_view_load \\(list\\<string\\>\\|string\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$viewtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/display_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$customWhere \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sSearch \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$where \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/multi_patients_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$search \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/multi_patients_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/multi_patients_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$customWhere \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tag \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sortlink\\[0\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sortlink\\[1\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sortlink\\[2\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sortlink\\[3\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sortlink\\[4\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$resultModel\\-\\>message \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/get_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$protocol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$commentdelim \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$datetime_report \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$in_orderid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$orphanLog \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$provider \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$send_account \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tablename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$txdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportClinicalNotesFormTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportContactTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportEsignatureTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportFormsGroupsEncounterTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportOnsiteMailTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportOnsiteMessagesTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportOnsiteMessagesTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportOpenEmrPostCalendarEventsTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportPersonTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>getSelectClause\\(\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selectQuery \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportTrackAnythingFormTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$direction \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$eventType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fromName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fromNumber \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$messageId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$messageType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$partyId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$recordingId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sessionId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$status \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$telephonySessionId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$telephonyStatus \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$toName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$toNumber \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$validationToken \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$user_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/setup_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$mypubpid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$vendor \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$service \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/BootstrapService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$appkey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$appsecret \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ext \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$password \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$username \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$docId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$faxStoreDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$documentId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jobId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$mediaPath \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jobId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$partyId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$recordingId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$subscriptionId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$telephonySessionId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$transferTarget \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$direction \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fax\\-\\>mediaUrl \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$jobId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$numPages \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$status \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>faxNumber \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$to \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sendMethod \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lastStatus \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoLogService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>encryptionKey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$where \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limitEnd \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limitStart \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code_text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limit \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$baseModuleDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$customDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$zendModDir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$base \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$directory \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$BS_COL_CLASS \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$mypubpid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_search_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sub \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$protocol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remote_host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/list_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/list_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$diagnoses \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ins_addr \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ins_city \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ins_state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ins_zip \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$procedure_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$procedure_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_DOB \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_fname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_lname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/pending_orders.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$commentdelim \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$datetime_report \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$file \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$in_orderid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lab_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$orphanLog \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$provider \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remote_host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$send_account \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tablename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$txdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$diagnosis \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$procedure_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$procedure_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$formdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codetype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codevalue \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'incdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/cash_receipt.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/cash_receipt.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'rootdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/encounter_bottom.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/encounter_top.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sellist \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$where1 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$modulePath \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'path\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codeid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$coderel \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$taxrates \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$formname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/trend_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$incdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/trend_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tcode \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/history/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fieldKey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/manage_dup_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$count \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$contraception_billing_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_num_amount_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_num_method_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_num_ref_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_num_type_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$invoice_refno \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lino \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rcpt_num_amount_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rcpt_num_method_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rcpt_num_ref_columns \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$refno \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lino \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prodcode \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/problem_encounter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/active_reminder_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/clinical_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_begin \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/advancedirectives.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/clinical_reminders_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/dashboard_header.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'webroot\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$doc_catg \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/insurance_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/list_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/patient_reminders_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$N \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$body \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/print_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/record_disclosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/shot_record.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'webroot\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$outcome \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/void_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$colname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$protocol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remote_host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$protocol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remote_host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$protocol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remote_host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/amc_tracking.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/cdr_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/chart_location_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/charts_checked_out.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/clinical_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/cqm.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/criteria.tab.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/custom_report_range.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/daily_summary_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/destroyed_drugs_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/external_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/external_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/front_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cvx_code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/immunization_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/immunization_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$extracond \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fwcond \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lotno \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'encounter\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'pid\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_cyp_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$abtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_facility \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/message_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/non_reported.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$include_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/patient_flow_board_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/patient_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/patient_list_creation.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/prescriptions_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/referrals_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/report_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/svc_code_financial_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/unique_seen_patients_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$colstr \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$condition \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lbfonly \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$new_field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$old_field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tablename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$acokey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout_props.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$list_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codetype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_dest_filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'srcdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'rootdir\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/appointmentComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facilities_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_registrations.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$facid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dbase \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$list_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ippfconmeth \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ndc_info \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$bill \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/FeeSheetHtml.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$price \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheetHtml.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prod \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/FeeSheetHtml.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$error \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/MedEx.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$remoteAddr \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/MedEx.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fileroot \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/dated_reminders_counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/facility_ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$name_alt \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$first_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$last_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$phone \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/set_pt.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$order_by \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'login_screen\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$incoming_site_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$orderby \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'OE_SITE_DIR\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$method \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$post_data \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>host \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$url \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$note \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITES_BASE \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$destination_directory \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$theme_title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$data \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$domain \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$line \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$localhost \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$recipient \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sender \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sel \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/share_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$auth \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$billstring \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$query_part \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$query_part_day \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$idnum \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$phimail_username \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_271_code_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$elem \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_271_code_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_835_code_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_997_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$loopid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fn \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$elem04 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$elem20 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fn \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ackcode \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$acknote \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ak901 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ak902 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ak903 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ak904 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ak905 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filepath \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$archive_date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$archive_filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$archive_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$claims_csv \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$file_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$files_csv \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filetype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fn \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$frow \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ft \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 22,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fz \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$msg \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$period \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$td \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filetype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ins \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$trace \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$csv_file \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filename \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filepath \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filetype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$search_dir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$to_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$trace \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cmt \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fn \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fn1 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fnupl \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'pay_total\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'reference\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ftype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$actn \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dir_to \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$f_ct \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$libdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$today \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$method \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$path \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>Endpoint \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>SiteId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>Token \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/maviq_phone_api.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$BS_COL_CLASS \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$CPR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'assets_static_relative\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'v_js_includes\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'web_root\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_labStr \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$age_asof_date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$class \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cpid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ctype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$currvalue \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cuser \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$disabled \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 39,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$form_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$formtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$itemid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$lbfonchange \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$membership_group_number \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$o\\[\'value\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$onchange \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prefix \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$resdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$restype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sel \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$smallform \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$under \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$given \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$orderby \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pt\\[\'lname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pt\\[\'mname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pt\\[\'suffix\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$where \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$assigned_to \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limit \\(0\\|0\\.0\\|array\\{\\}\\|string\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$search \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$directory \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$mod \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sep \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_cache_attrs \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_count \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_line_no \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_open_tag \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$append \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$arg_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$arg_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$assign \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$attr_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$buffer \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$close_tag \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$expr_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$filter_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$plugin_info\\[1\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$plugin_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$plugin_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>_cache_serial \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$token \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_plugin_file \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_plugin_file \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_resource_plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_params\\[\'value\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/compiler.assign.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_params\\[\'var\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/compiler.assign.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$accept \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$agent \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pass \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$referer \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$user \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cellstatic \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$key \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$spreadsheet_form_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'OE_SITE_DIR\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$authorized \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$appt_time \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'fname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'lname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prow\\[\'phone_home\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$response\\-\\>ErrorMessage \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$from \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$strFrom \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$strTo \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$subject \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$limit \\(0\\|0\\.0\\|array\\{\\}\\|string\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$search \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$href \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$classname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/IO/Includer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/IO/Includer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$propname \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>_label \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rKey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$urlPiece \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$s_line \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ObserveToFile.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prop \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$id \\(VARIANT\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pkcol \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prop \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$callback \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$s_line \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Util/ExceptionFormatter.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$returnurl \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$rootdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$srcdir \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$OE_SITE_DIR \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$more \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'attn\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'city\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'name\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'phone\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'postal_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'state\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'street\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$flddef \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fldid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$flddef \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$fldid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>bat_hhmm \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>bat_yymmdd \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$webserver_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>batch\\-\\>getBatFilename\\(\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$label \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/X12RemoteTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$auth \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$billstring \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cols \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$query_part \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$query_part2 \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmp \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ndc \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>hcfa_curr_line \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/SLEOB.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/SLEOB.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$encounter \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ndc \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tmpdate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$ndc \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837P.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$file \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$acokey \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$return_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section_title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$validate \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$oauthTokenUrl \\(list\\<mixed\\>\\|string\\|false\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateClientCredentialsAssertionCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$oauthTokenUrl \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateClientCredentialsAssertionSymfonyCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$clientId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$smallform \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/BillingCodeType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$currvalue \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/LocalProviderListType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$smallform \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/LocalProviderListType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$prefix \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$smallform \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$smoking_status_title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sessionType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpSessionFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cols \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$domSelector \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$selector \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/CacheUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$col \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'fileroot\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$favicon \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$\\{\\$property\\} \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Qdm/BaseTypes/AbstractType.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$meta\\[\'fname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$meta\\[\'lname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$meta\\[\'pid\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$objtype \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/SectionEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$action \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/PageHeadingRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$cache_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$acl_id \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$acl_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_section_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aco_value_array \\(array\\<mixed, mixed\\>\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_group_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_group_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_section_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_value_array \\(array\\<mixed, mixed\\>\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$aro_value_array \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_group_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_group_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_value_array \\(array\\<mixed, mixed\\>\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$axo_value_array \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$current_acl_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$erase \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$group_id \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$group_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$group_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$id \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$left \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$new_acl_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_id \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_section_id \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_section_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_type \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_type \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$object_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$order \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$parent_id \\(\'\'\\|array\\{\\}\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$parent_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$recurse \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$reparent_children \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$return_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$return_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section_value \\(0\\|0\\.0\\|\'\'\\|\'0\'\\|array\\{\\}\\|false\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$section_value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$value \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$web_root \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$relationship \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'subscriber_fname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$row\\[\'subscriber_lname\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$params\\[\\$key\\] \\(array\\|bool\\|float\\|int\\|string\\|null\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tableDefinition\\[\'alias\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$memberDisplayInfo \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/CareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$sourceFile \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$author\\[\'name\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$author\\[\'wp_phone\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>templateData\\[\'field_name_value_array\'\\]\\[\'clinical_notes\'\\]\\[\\$i\\]\\[\'author_address\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$note\\[\'caption\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$note\\[\'id\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>title \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$error\\-\\>code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$error\\-\\>line \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$referenceId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$codeType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$personId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$targetTable \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$c \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DemographicsRelatedPersonsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$period \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$template_name \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$default_warehouse \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patient_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DrugSalesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$given \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dataRecord\\[\'screening_category_code\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$date \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$orderby \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'OE_SITE_DIR\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$GLOBALS\\[\'images_static_absolute\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$index \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$match\\[\'relationship_count\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PersonPatientLinkService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$procedureOrderId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$qdmRecord\\-\\>getPid\\(\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$serviceClass \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$code \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$count \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$documentId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$entryId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measure\\-\\>cms_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measure\\-\\>hqmf_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measure\\-\\>hqmf_set_id \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$organizationInfo\\[\'address\'\\]\\[\'country\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$organizationInfo\\[\'address\'\\]\\[\'zip\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$organizationInfo\\[\'npi\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$organizationInfo\\[\'tin\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$parametersId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$patients \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$popCode \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$popId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>escapeXml\\(\\$measure\\-\\>title\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>escapeXml\\(\\$organizationInfo\\[\'address\'\\]\\[\'city\'\\]\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>escapeXml\\(\\$organizationInfo\\[\'address\'\\]\\[\'state\'\\]\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>escapeXml\\(\\$organizationInfo\\[\'address\'\\]\\[\'street\'\\]\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>escapeXml\\(\\$organizationInfo\\[\'name\'\\]\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$indentLevel \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$display \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$instrumentName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$score \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tDetail \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$dateType \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$given \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/SocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v\\[\'v_major\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VersionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v\\[\'v_minor\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VersionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v\\[\'v_patch\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VersionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$v\\[\'v_tag\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VersionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$this\\-\\>tabsid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Tabs/TabsWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$field \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$testRunId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$testRunId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$testRunId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient700APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$profile \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/CapabilityStatementTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$testRunId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/CapabilityStatementTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$testRunId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/US_Core_311/InfernoSinglePatientAPITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$state \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measure \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measureResult\\[\'pubpid\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$measureResult\\[\'qrda_file\'\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$pid \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$setName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$idField \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$tableName \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/MedicationDispenseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$option\\[1\\] \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/MiscBillingOptionsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$facilityId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$table \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$recordId \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$keyVersion\\-\\>toString\\(\\) \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$numeral \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/NumberToTextTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Part \\$text \\(mixed\\) of encapsed string cannot be cast to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/NumberToTextTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
