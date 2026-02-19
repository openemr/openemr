<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ignoreAuth\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRHeader.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$commitchanges\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$qrda_file_path\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_external_tables\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types, \\$code_external_tables\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ct_external_options\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$EXPORT_PATH\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$out, \\$indent\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encCheckUniqId, \\$from_date, \\$to_date, \\$EncounterCptCodes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encCheckUniqId, \\$from_date, \\$to_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$from_date, \\$to_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$mainQrdaPayerCodeSendArr, \\$from_date, \\$to_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$mainQrdaRaceCodeArr, \\$mainEthiCodeArr, \\$from_date, \\$to_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$preDefinedUniqIDRules\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$qrda_file_path\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$gacl_api\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$where, \\$eracount, \\$eraname\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$InsertionId\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$StringToEcho, \\$debug\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encount, \\$debug\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$invoice_total, \\$last_code, \\$paydate\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$invoice_total\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_ptname, \\$last_invnumber, \\$last_code\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$STMT_TEMP_FILE_PDF\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$where, \\$eracount, \\$eraname\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bcodes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$isAuthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir, \\$isAuthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$isAuthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ub04_codes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$tmpl_line_no\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$drug_id, \\$is_user_restricted\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$drug_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$auth_admin, \\$auth_lots\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$msg, \\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$msg, \\$page\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$msg, \\$warning_msg, \\$dem_check\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$msg\\)\\. Use dependency injection instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$faxcache\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$limit\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$patient_name, \\$patient_address, \\$patient_city, \\$patient_state, \\$patient_zip, \\$patient_phone, \\$patient_dob\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$physician_name, \\$practice_address, \\$practice_city, \\$practice_state, \\$practice_zip, \\$practice_phone, \\$practice_fax, \\$practice_dea\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR, \\$historical_ids, \\$USING_BOOTSTRAP, \\$BS_COL_CLASS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$historical_ids, \\$USING_BOOTSTRAP\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$counter_header\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$OE_SITE_DIR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$PMSFH\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$PMSFH_titles\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$codes_found\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$dated\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$display\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$documents\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$earlier\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter_data\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_folder\\)\\. Use dependency injection instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$id\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ins_coA\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ins_coB\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pat_data\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pcp_data\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$form_id, \\$encounter, \\$display_W_width\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$priors\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$prov_data\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$provider_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$reason\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ref_data\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$visit_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$providerNAME\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$send\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root, \\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$PDF_OUTPUT\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$choice\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_folder\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_name\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$tmp_files_remove\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$visit_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root, \\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$PDF_OUTPUT\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$send\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$task\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$visit_date\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$earlier\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$priors\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types, \\$justinit, \\$usbillstyle, \\$liprovstyle, \\$justifystyle, \\$fs, \\$price_levels_are_used, \\$institutional\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types, \\$usbillstyle, \\$liprovstyle, \\$justifystyle, \\$fs, \\$price_levels_are_used\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$i, \\$last_category, \\$FEE_SHEET_COLUMNS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/code_check.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$str_test, \\$str_nervous, \\$gad7_total, \\$pdf_as_string, \\$str_values, \\$str_difficulty_values, \\$data, \\$exp, \\$file_name, \\$str_generate_pdf\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$userauthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$therapy_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pelines\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/lines.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pelines\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$gbl_lab, \\$gbl_lab_title, \\$gbl_client_acct\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$gbl_lab_title, \\$gbl_lab, \\$gbl_client_acct, \\$gbl_use_codes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cssWidth, \\$cssHeight\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$isMetric\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$webserver_root, \\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$globalsBag\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$event_date, \\$info_msg\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$new_multiple_value, \\$provider, \\$event_date, \\$duration, \\$recurrspec, \\$starttime, \\$endtime, \\$locationspec\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$host, \\$port, \\$login, \\$pass, \\$dbase\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/config.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$slots, \\$slotsecs, \\$slotstime, \\$slotbase, \\$slotcount, \\$input_catid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pnconfig\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pntable\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$index\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pnmodvar\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pntable\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor1, \\$bgcolor2, \\$bgcolor3, \\$bgcolor4, \\$bgcolor5\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$textcolor1, \\$textcolor2\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor1, \\$bgcolor2, \\$bgcolor3, \\$bgcolor4, \\$bgcolor5, \\$bgcolor6, \\$textcolor1, \\$textcolor2\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pcSmarty.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor1, \\$bgcolor2\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor1, \\$bgcolor2, \\$bgcolor3, \\$bgcolor4, \\$bgcolor5, \\$bgcolor6, \\$textcolor1, \\$textcolor2\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$beg_year, \\$beg_month\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$out, \\$indent\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$appId\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$userauthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$N\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$lab_npi\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$orphanLog\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rhl7_return\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$SMS_NOTIFICATION_HOUR, \\$TYPE\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bTestRun\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/pharmacy_list_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$assignedEntity\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$representedOrganization\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$assignedEntity\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceRequestModelGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$representedOrganization\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceRequestModelGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Form/ImmunizationForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ACL_UPGRADE\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Model/SyndromicsurveillanceTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR, \\$BS_COL_CLASS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_group, \\$SHORT_FORM\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/pending_followup.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$areport, \\$arr_titles, \\$arr_show\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$areport, \\$arr_titles, \\$form_by\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$atotals, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cellcount, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rootdir, \\$qoe_init_javascript\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/qoe.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$lab_npi\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$orphanLog\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rhl7_return\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir, \\$orphanLog, \\$lab_npi\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aNotes\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$groupLevel, \\$groupCount, \\$itemSeparator, \\$ext\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$groupLevel, \\$groupCount, \\$itemSeparator, \\$pid, \\$encounter, \\$ext\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$keyLocation, \\$keyLength, \\$nextLocation\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$keyLocation, \\$keyLength\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ptrow, \\$hisrow, \\$enrow, \\$nextLocation, \\$keyLocation, \\$keyLength\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_encounter_layout\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES, \\$auth_med\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$first_time\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/manage_dup_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$PRODUCTION\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$TAXES_AFTER_ADJUSTMENT\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aAdjusts\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aInvTaxes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aTaxNames, \\$aInvTaxes, \\$checkout_times, \\$current_checksum\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aTaxNames, \\$aInvTaxes, \\$taxes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$aTaxNames, \\$num_optional_columns\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$details, \\$TAXES_AFTER_ADJUSTMENT\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$details, \\$rapid_data_entry, \\$aAdjusts\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService, \\$alertmsg\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_headers_written, \\$patdata, \\$patient_id, \\$encounter_id, \\$aAdjusts\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_num_type_columns, \\$form_num_method_columns, \\$form_num_ref_columns, \\$form_num_amount_columns\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$lino, \\$taxes, \\$num_optional_columns\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$lino, \\$totalchg, \\$aAdjusts, \\$taxes, \\$encounter_date, \\$TAXES_AFTER_ADJUSTMENT\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$num_optional_columns, \\$rcpt_num_method_columns, \\$rcpt_num_ref_columns, \\$rcpt_num_amount_columns\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rcpt_num_method_columns, \\$rcpt_num_ref_columns, \\$rcpt_num_amount_columns\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$taxes, \\$encounter_date, \\$num_optional_columns, \\$TAXES_AFTER_ADJUSTMENT\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root, \\$webserver_root, \\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$lino\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$prevsvcdate, \\$details\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$sl_err, \\$sl_cash_acc, \\$details, \\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$SBCODES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$html\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root, \\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_texts\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$errmsg\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$grand_total_charges, \\$grand_total_copays, \\$grand_total_encounters\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounters\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$export_patient_count, \\$export_dollars, \\$bgcolor\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$grand_total_agedbal, \\$is_due_ins, \\$form_age_cols\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$grand_total_charges, \\$grand_total_adjustments, \\$grand_total_paid\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$initial_colspan, \\$final_colspan, \\$form_cb_idays, \\$form_cb_err\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$initial_colspan, \\$form_cb_idays, \\$form_cb_err\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ins_co_name\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_action\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_from_date, \\$form_to_date, \\$form_product\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_warehouse_id, \\$last_product_id, \\$product_first\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$warehouse, \\$product, \\$secqtys, \\$priqtys, \\$grandqtys\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$whleft, \\$prodleft\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_days\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_details, \\$wrl_last_drug_id, \\$warnings, \\$encount, \\$fwcond, \\$fwbind, \\$form_days\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$gbl_expired_lot_warning_days, \\$form_facility, \\$form_warehouse, \\$form_action\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$warnings, \\$form_action\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$grandtotal, \\$grandqty, \\$encount, \\$form_action\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$product, \\$productcyp, \\$producttotal, \\$productqty, \\$grandtotal, \\$grandqty\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_cyp_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$atotals, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cellcount, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$areport, \\$arr_titles, \\$form_by, \\$form_content\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$areport, \\$arr_titles, \\$form_content, \\$from_date, \\$to_date, \\$arr_show\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$atotals, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cellcount, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_by, \\$arr_content, \\$form_content\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_by\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor, \\$orow, \\$enc_units, \\$enc_chg\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$enc_pmt, \\$total_pmt, \\$enc_adj, \\$total_adj, \\$enc_bal, \\$total_bal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$enc_units, \\$enc_chg, \\$enc_pmt, \\$enc_adj, \\$enc_bal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_report_by, \\$insarray, \\$grandpaytotal, \\$grandadjtotal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$paymethod, \\$paymethodleft, \\$methodpaytotal, \\$methodadjtotal, \\$grandpaytotal, \\$grandadjtotal, \\$showing_ppd\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$product, \\$category, \\$producttotal, \\$productqty, \\$cattotal, \\$catqty, \\$grandtotal, \\$grandqty\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$productleft, \\$catleft\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$datatypes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$fld_line_no, \\$sources, \\$lbfonly, \\$extra_html, \\$validations, \\$UOR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$layout_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$layouts, \\$form_inactive\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$layouts\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$sorted_datatypes\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$opt_line_no, \\$ISSUE_TYPE_CATEGORIES, \\$ISSUE_TYPE_STYLES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$opt_line_no, \\$ct_external_options\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$opt_line_no, \\$list_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$opt_line_no\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$thecodes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$error_msg\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$insert_count, \\$debug, \\$verbose\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$skipAuditLog\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ADODB_mysqli_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$MedEx\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$data\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$info\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$logged_in\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rcb_facility\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rcb_provider\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rcb_selectors\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$result_pat\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$setting_bootstrap_submenu\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$service_name\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$is_lbf, \\$pid, \\$table\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$COMPARE_FUNCTION_HASH\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ORDERHASH\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$REPEAT_FREQ, \\$REPEAT_FREQ_TYPE, \\$REPEAT_ON_NUM, \\$REPEAT_ON_DAY\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$appointment_sort_order\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$resNotNull\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$incoming_site_id\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$OE_SITES_BASE\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$HTML_CHARSET\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/contraception_billing_scan.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$contraception_billing_code, \\$contraception_billing_cyp, \\$contraception_billing_prov\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/contraception_billing_scan.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$hasAlerts\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$query_part, \\$query_part2, \\$query_part_day, \\$query_part_day1, \\$billstring, \\$auth\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$phimail_direct_message_check_allowed_mimetype\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$today, \\$userauthorized\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$today\\)\\. Use dependency injection instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor, \\$orow\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cellcount, \\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$form_output\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$out, \\$indent\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$row\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$taxes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pprow, \\$item_count, \\$cell_count, \\$last_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES, \\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR, \\$BS_COL_CLASS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$cell_count, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count, \\$last_group, \\$CPR, \\$condition_str, \\$BS_COL_CLASS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count, \\$last_group, \\$CPR\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count, \\$cell_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$item_count\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$last_group\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rootdir, \\$date_init, \\$ISSUE_TYPES, \\$code_types, \\$membership_group_number\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rootdir, \\$date_init, \\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$sk_layout_items\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$facilityService\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$policy_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$white_list\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sanitize.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$set, \\$start, \\$end\\)\\. Use dependency injection instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$port\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$EMAIL_NOTIFICATION_HOUR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$SMS_GATEWAY_USENAME, \\$SMS_GATEWAY_PASSWORD, \\$SMS_GATEWAY_APIKEY\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$SMS_NOTIFICATION_HOUR, \\$EMAIL_NOTIFICATION_HOUR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$SMS_NOTIFICATION_HOUR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$data_info\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$log_folder_path\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$patient_info\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$smsgateway_info\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ignoreAuth_onsite_portal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$slots, \\$slotsecs, \\$slotstime, \\$slotbase, \\$slotcount, \\$input_catid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$globalsBag\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$authUploadTemplates\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$templateService, \\$globalsBag\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$bgcolor, \\$orow, \\$enc_units, \\$enc_chg\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$enc_pmt, \\$total_pmt, \\$enc_adj, \\$total_adj, \\$enc_bal, \\$total_bal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$enc_units, \\$enc_chg, \\$enc_pmt, \\$enc_adj, \\$enc_bal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ignoreAuth_onsite_portal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ignoreAuth_onsite_portal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ignoreAuth_onsite_portal\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$OE_SITE_DIR\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathv.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFfms.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$encounter, \\$formname, \\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$formid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFvbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$sqlconf\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/sqlconf.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$TPSCriteriaDisplay, \\$TPSCriteriaKey, \\$TPSCriteriaIndex, \\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$query_part, \\$billstring, \\$auth\\)\\. Use dependency injection instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$query_part, \\$query_part2, \\$billstring, \\$auth\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$query_part_day, \\$query_part_day1\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$sl_err\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$X12info\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$ISSUE_TYPES\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclMain.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/BaseForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$rootdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$srcdir\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$webroot\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/EncounterSessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/EncounterSessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/EncounterSessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid, \\$encounter\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/PatientSessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$config\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/InstallationCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$v_js_includes\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/PortalCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$pid\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/PortalCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$enableMoves\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$attendant_type\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS_METADATA, \\$USER_SPECIFIC_GLOBALS, \\$USER_SPECIFIC_TABS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/GlobalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$webserver_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$web_root\\)\\. Use dependency injection instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Tabs/TabsWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$code_types\\)\\. Use dependency injection instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/CodeTypesServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of the "global" keyword is forbidden \\(\\$GLOBALS\\)\\. Use dependency injection instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Forms/FormLocatorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
