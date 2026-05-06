<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Variable \\$web_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../Documentation/help_files/sl_eob_help.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$config might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$database_acl might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$database_patch might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dbase might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$host might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$login might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pass might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$port might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_acl might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_database might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_realpatch might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$authorID might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../ccr/createCCRActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$oemrID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sourceID might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../ccr/createCCRAlerts.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$authorID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRHeader.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sourceID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRImmunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sourceID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRMedication.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sourceID might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../ccr/createCCRProblem.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sourceID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$authorID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$policies_by_payer_id might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/chart_review_pids.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dupecount might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dupelist might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sqlconf might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupscore.cli.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$d might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$error might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$filetext might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$from_pathname might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$messages might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$temp_couchdb_url might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$p might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$prescription might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$files might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$qrda_file_path might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/ajax_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/chart_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$counter might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$query might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sql_bind_array might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$cqmCodes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/download_qrda.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/download_qrda.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$facility_id might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$facility_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_denom might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_exclude might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_ipp might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_numer1 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_numer2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_1_numer3 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_denom might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_exclude might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_ipp might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_numer1 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_numer2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stratum_2_numer3 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$userRow might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$alertmsg might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patients might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$facilResRow might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$gender might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patFname might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patLname might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patientRow might not be defined\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tdTitle might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$config might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$Table might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/code_systems/standard_tables_manage.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tres might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$row might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webroot might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$encounter$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$jasonzip$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$prec$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$jasonzip might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$num might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$txt might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$zip4 might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$include_root might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/easipro/pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$arr_files_php might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/expand_contract_js.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$catid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$erow might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$newid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tmp_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$userauthorized might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$date_init might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$enrow might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fs might not be defined\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ignoreAuth_onsite_portal might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$svccount might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$code_types might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$encounter might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pdf might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$web_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$cleave_opt might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$code_types might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$encounter might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ndc_applies might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$userauthorized might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$database might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$issues might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$req_encounter might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$req_pid might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$task might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$billing_id might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$database might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$json_diags might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$req_encounter might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$req_pid might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$task might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_default might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_extremely might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_form_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_form_title might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_more might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nearly might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nosave_confirm might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nosave_exit might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_not might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_q8 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_q8_2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_several might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_somewhat might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_very might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_afraid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_annoyed might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_control_worry might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_default might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_extremely might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_form_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_form_title might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_more might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nearly might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nervous might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nosave_confirm might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_nosave_exit might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_not might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_q8 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_q8_2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_relax might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_restless might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_several might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_somewhat might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_very might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$str_worry might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$disabled might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$posCode might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result might not be defined\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$therapyGroupCategories might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$therapy_group might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewmode might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$i might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/edit_diagnoses.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$isDorn might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$mode might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$disabled might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$inDir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$portal_temp_css_theme_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$temp_css_theme_name might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_js_includes might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webroot might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/help_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$create might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$go might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_definition.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/language.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$openemr_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/login_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$getevent might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_GLOBALS might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$smarty might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/modifier.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$BS_COL_CLASS might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$date_init might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$alertmsg might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_search_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$MedEx might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$count_facs might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$count_provs might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$current_events might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$from_date might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$icon2_here might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$icon_4_CALL might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$icon_here might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$local might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ptkr_future_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$select_facs might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$select_provs might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$statuses_list might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_js_includes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$encounter might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$encounter might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$logocode might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/smart/register-app.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tinylogocode1 might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/register-app.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tinylogocode2 might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/register-app.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$statuses might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/addGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$users might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/addGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$events might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/appointmentComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$groupId might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$users might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$group_id might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$readonly might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$statuses might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$counselors might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$deletion_response might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$deletion_try might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$group_types might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$therapyGroups might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dbase might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ESign/views/encounter/esign_button.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/ESign/views/encounter/esign_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ESign/views/form/esign_button.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../library/ESign/views/form/esign_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$autojustify might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$form_patient_id$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$form_patient_name$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$prog$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$setting_selectors$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$userid$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$deletes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$expired might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$facility might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$icon might not be defined\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$last_col_width might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$no_fu might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$no_interval might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid_list might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$provider might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ptkr_future_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tell_MedEx might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$today might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$urow might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result4 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$default$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fileroot might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/dated_reminders_counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/easipro_util.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/facility_ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$name_alt might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$titleGraphLine1 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$titleGraphLine2 might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$bgcolor$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$StringToAppend might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$StringToAppend2 might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patient_code might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patient_code_complete might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/set_pt.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rtn might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$provider$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$pid$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$inline_arg might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$string$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/OFX.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$cmapkey might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/fpdf/fpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ls might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/fpdf/fpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ret might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sql might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AbstractAmcReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$codes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$header_data might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$new_file might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$types_support might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$excluded might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pass_filter might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patientNumber might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$percentage might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$temp_track_pass might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$end might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$st might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/add_custombutton.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ISSUE_TYPES might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sel might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/share_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$iter might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dn might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/deletedrug.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$web_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$help_icon_title might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/display_help_icon_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$current_state might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/expand_contract_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$where might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$CPR$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$datetimepicker_formatInput might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/js/xl/jquery-datetimepicker-2-5-4.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$datetimepicker_showseconds might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/js/xl/jquery-datetimepicker-2-5-4.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$datetimepicker_timepicker might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/js/xl/jquery-datetimepicker-2-5-4.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$disabled$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$lbfonchange$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$list_id_esc$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$under$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$count might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$group_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$lrow might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$metadata might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$insarr might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$iter might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$returnval might not be defined\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$AccountCode might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$AdjustString might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$all might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tempCollectReminders might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$update_rem_log might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$where might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$arcount might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ret might not be defined\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$retar might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pass_sql might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_output might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$arg_list might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$attr_name might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$compiled_ref might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$expr might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$i might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$include_file might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$last_token might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$plugin_func might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$return might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$dummy$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_debug_start_time might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_included_tpls_idx might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_smarty_results might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$debug_start_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$included_tpls_idx might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_readable might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_template_source might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_message might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_plugin_func might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$debug_start_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_debug_start_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_config_vars might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_debug_start_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$_var_compiled$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_id might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$day_values might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$minutes might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$seconds might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$loop$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ord might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.mailto.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$smarty might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/specialty_forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$bcodes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$cellstatic might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$encounter might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$saveid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$spreadsheet_form_name might not be defined\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$userauthorized might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$handle1 might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$new_str might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/translation.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$use_validate_js might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/validation/validation_script.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$token_database might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$token_encrypt might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$portalRegistrationAuthorization might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/register.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$hiddenLanguageField might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/verify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result2 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/account/verify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result3 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/account/verify.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$event_date might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$insert might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patientname might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$providers_current might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$starttime might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$providerid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_allergies.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$class might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_lab_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_medications.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_prescriptions.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_pro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_problems.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$insurance_data_array might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/get_profile.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/get_profile.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$landingpage might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$defaultLangID might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$defaultLangName might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$one_time might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result2 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result3 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ccaudit might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$invoice might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$response might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$landingpage might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/logout.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sn might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$x might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$attachment$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/FileUpload.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$output might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultError404.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultErrorFatal.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteActivityViewListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsitePortalActivityListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../portal/patient/templates/PatientListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/_FormsHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/templates/_Header.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$this might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/patient/templates/_modalFormHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$form_pid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$payrow might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$adj_amt might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$last_year might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$pid might not be defined\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_js_includes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ISSUE_TYPES might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$OE_SITE_DIR might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$insurance_data_array might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$prevIssueType might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_js_includes might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ISSUE_TYPES might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$rootdir might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$srcdir might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$OE_SITES_BASE might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$memory_limit_mb might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$sqlconf might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$pid$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$pid$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFvbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$age_index might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$desc might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dun_message might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$patientIdCc might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$GLOBALS_METADATA might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_database might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_major might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_minor might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_patch might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_realpatch might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_tag might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$GLOBALS_METADATA might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$result2 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_database might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_major might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_minor might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_patch might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$v_tag might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$webserver_root might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$iter might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$query_part_day might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$query_part_day1 might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$msp might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$tmp might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$elog might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$returnval might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$segment_ar$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$st_pos$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$bht_pos might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dr might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ds might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$dt might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$env_ar might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$gs_ct might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$gs_fid might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$gs_start might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$gsn might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$icn might not be defined\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$seg_ar might not be defined\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$segidx might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$stn might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/EdiHistory/X12File.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$MDY might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$allowed might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$columns might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$arr_group_titles might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$response might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$issues might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$fhirScopes might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$values might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$row$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$returnval might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$retarr might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Undefined variable\\: \\$row_count$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$groups_map_table might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$object_group_table might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$object_map_table might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$object_sections_table might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$retarr might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$table might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$catEntry might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$action might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$action_href might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$action_title might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$frontSpecificRetail might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$trxcustidRetail might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$trxcustidRetailLicensekey might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$zip might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pharmacy/Services/ImportPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$exportWriter might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$outputResult might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$resource might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$all might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Rx/RxList.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$ending might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Rx/RxList.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$list might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Rx/RxList.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$my_data might not be defined\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Rx/RxList.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$type might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Rx/RxList.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/basic.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$_redirect might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/redirect.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/action.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/add_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/age.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/bucket.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/custom.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/intervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/lifestyle.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/sex.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$criteria might not be defined\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/simple_text_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/summary.php',
];
$ignoreErrors[] = [
    'message' => '#^Variable \\$viewBean might not be defined\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/review/view.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
