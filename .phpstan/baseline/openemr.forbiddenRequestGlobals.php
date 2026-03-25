<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../Documentation/help_files/sl_eob_help.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../apis/routes/_rest_routes_standard.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/display.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/billing/load_fee_schedule.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/chart_review_pids.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupscore.cli.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 43,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Hl7.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../controllers/C_PatientFinder.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_PatientFinder.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_PracticeSettings.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 33,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../custom/chart_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/download_qrda.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/export_registry_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/zutil.cli.doc_import.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 70,
    'path' => __DIR__ . '/../../gacl/admin/acl_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../gacl/admin/assign_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../gacl/admin/assign_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/admin/assign_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/batchcom/batchEmail.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchPhoneList.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 37,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/batchcom/emailnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/batchcom/settingsnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/batchcom/smsnotification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/billing_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/clear_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/billing/edi_270.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/billing/edi_271.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edi_271.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 23,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 33,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/billing/get_claim_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/billing/indigent_patients_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/billing/new_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/billing/new_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/payment_pat_sel.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/billing/payment_pat_sel.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 34,
    'path' => __DIR__ . '/../../interface/billing/print_billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 34,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num1.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num1.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 34,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num2.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num2.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 34,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num3.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num3.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 40,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 36,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_patient_note.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_patient_note.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 57,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/billing/ub04_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/ub04_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/code_systems/dataloads_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/code_systems/list_installed.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/code_systems/list_staged.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/code_systems/standard_tables_manage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 52,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/drugs/destroy_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/drugs/destroy_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 29,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch_newpid.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/fax/fax_view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/ajax_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/ajax_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 63,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/notegen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/notegen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 23,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 84,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/care_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/care_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/forms/care_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/dictation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/dictation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/dictation/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/SpectacleRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/SpectacleRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/help.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/js/eye_base.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 231,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 165,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/ajax/find_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 69,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_options_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/gad7/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/gad7/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 63,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 29,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/note/print.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/note/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/note/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/phq9/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/phq9/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/forms/phq9/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/edit_diagnoses.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/edit_diagnoses.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/C_FormPriorAuth.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 60,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/handle_deletions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/patient_portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/requisition/barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 110,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/ros/C_FormROS.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/sdoh/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/patient_portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/sdoh/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 130,
    'path' => __DIR__ . '/../../interface/forms/sdoh/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/soap/C_FormSOAP.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../interface/forms/track_anything/create.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/track_anything/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/forms/track_anything/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 23,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/language/csv/commit_csv.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/load_csv_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/language/csv/validate_csv.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/language/csv/validate_csv.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/language/lang_constant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/language/lang_definition.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/language/lang_language.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/language/lang_manage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/language/language.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logout.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/logview/erx_logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/logview/erx_logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 36,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 41,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 121,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/calendar/find_group_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/find_patient_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/calendar/find_patient_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/display_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/finder/multi_patients_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/finder/multi_patients_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/import_holidays.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/holidays/import_holidays.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/holidays/import_holidays.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/pwd_expires_alert.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/EraDownload.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/claims.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/era.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/x12Tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/templates/eligibility.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/moduleConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index-portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index-portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthUserAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 37,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/UserContextController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/UserContextController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextAdminService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/ack_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/ack_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/ack_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/compendium_install.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/compendium_install.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/get_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/get_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/get_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/lab_setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/orders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/primary_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/primary_config_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/primary_config_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/route_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/route_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/route_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/routes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/routes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/setup_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_email.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_email.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_rc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_voice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/public/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/file_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_pharmacy_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/SelectedPatientPharmacy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/download_log_viewer.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/setup_facilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_users.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/Javascript.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceRequestModelGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/new/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_search_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/new/new_search_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/new/new_search_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../interface/orders/list_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/orders/list_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/load_compendium.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/orders/load_compendium.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/patient_match_dialog.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/orders/pending_followup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/orders/pending_orders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/orders/types_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/birthday_alert/birthday_pop.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/cash_receipt.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/encounter_top.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/load_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/search_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/search_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/select_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 36,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/trend_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/view_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_terminal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/edit_billnote.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/history/edit_billnote.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/letter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 34,
    'path' => __DIR__ . '/../../interface/patient_file/letter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/manage_dup_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 28,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/problem_encounter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/problem_encounter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/clinical_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 22,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/advancedirectives.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/clinical_reminders_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/patient_file/summary/create_portallogin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disc_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 50,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/insurance_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/lbf_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/lbf_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/patient_reminders_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/print_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/record_disclosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/shot_record.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/track_anything_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/vitals_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/print_referral.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/practice/address_verify.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/practice/ins_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/procedure_tools/libs/labs_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/product_registration/product_registration_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/product_registration/product_registration_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/reports/amc_tracking.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 40,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/reports/audit_log_tamper_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/reports/chart_location_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 61,
    'path' => __DIR__ . '/../../interface/reports/clinical_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 71,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/reports/cqm.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/reports/cqm.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/reports/criteria.tab.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/reports/custom_report_range.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/reports/daily_summary_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/reports/destroyed_drugs_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/reports/direct_message_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/reports/front_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/reports/immunization_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/reports/insurance_allocation_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/reports/ip_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../interface/reports/ippf_cyp_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/reports/message_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/reports/non_reported.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 37,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/patient_edu_web_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 31,
    'path' => __DIR__ . '/../../interface/reports/patient_flow_board_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/reports/patient_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 76,
    'path' => __DIR__ . '/../../interface/reports/patient_list_creation.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/reports/payment_processing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/reports/prescriptions_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/reports/referrals_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/report_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/rwt_2026_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 30,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/services_by_category.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/services_by_category.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 28,
    'path' => __DIR__ . '/../../interface/reports/svc_code_financial_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/reports/unique_seen_patients_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/smart/ehr-launch-client.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_accountStatusDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_allergy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_patientfullmedication.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 72,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/super/edit_layout_props.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 30,
    'path' => __DIR__ . '/../../interface/super/edit_layout_props.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/super/layout_listitems_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/load_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/super/load_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/therapy_groups/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/addGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/usergroup/facilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/facilities_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/facility_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_registrations.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 30,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/usergroup/user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/user_info_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/usergroup/user_info_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 168,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Abstract/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 37,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/MedEx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/MedEx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/ajax/addlistitem.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 81,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/ajax/amc_misc_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/billing_tracker_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/collect_new_report_id.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/dated_reminders_counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/document_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/drug_screen_completed.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 22,
    'path' => __DIR__ . '/../../library/ajax/easipro_util.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/ajax/execute_cdr_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/ajax/execute_pat_reminder.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/facility_ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/facility_ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/facility_ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/get_preference_answers.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/graph_track_anything.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/i18n_generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/imm_autocomplete/search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/immunization_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/ajax/lists_touch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/log_print_action_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../library/ajax/login_counter_ip_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/ajax/plan_setting.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/prescription_drugname_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/ajax/rule_setting.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/ajax/set_pt.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/set_pt.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/specialty_form_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/sql_server_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/sql_server_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/status_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/template_context_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/turnoff_birthday_alert.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/udi.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/unset_session_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../library/ajax/user_settings.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/allow_cronjobs.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/allow_cronjobs.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/allow_cronjobs.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/fpdf/fpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../library/custom_template/add_context.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 22,
    'path' => __DIR__ . '/../../library/custom_template/add_custombutton.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/add_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/custom_template/ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/custom_template/personalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/quest_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/share_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/custom_template/updateDB.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/deletedrug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 41,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 22,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sanitize.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/specialty_forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_email_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 61,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 30,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 47,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 30,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/lib/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/lib/patient_groups.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/lib/patient_groups.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/lib/patient_groups.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 32,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 28,
    'path' => __DIR__ . '/../../portal/messaging/handle_note.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/messaging/secure_chat.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/messaging/secure_chat.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/messaging/secure_chat.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Authentication/Auth401.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/BrowserDevice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 29,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/questionnaire_render.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/questionnaire_render.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/document_downloads_action.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/sign/assets/signer_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/sign/lib/save-signature.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../sphere/process_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../sphere/process_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../sphere/process_revert_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../sphere/process_revert_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../sphere/token.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAjax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAlerts.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Storage/ReadAndCloseNativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Storage/ReadAndCloseNativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Utils/PaginationUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_COOKIE is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Config/ServerConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDeviceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirLocationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMediaRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationDispenseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirOrganizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/DemographicsRelatedPersonsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DemographicsRelatedPersonsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirResourcesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientPortalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientPortalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_REQUEST is forbidden\\. Use Symfony\'s Request object or filter_input\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientPortalService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
