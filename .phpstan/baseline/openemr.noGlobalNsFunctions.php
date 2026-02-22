<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function adminSqlQuery may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isServiceEnabled may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sendZipDownload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Function createCCR may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function createHybridXML may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function displayError may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gnrtCCR may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sourceType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function viewCCD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transmitCCD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transmitMessage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUuid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/uuid.php',
];
$ignoreErrors[] = [
    'message' => '#^Function outputMessage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parseArgs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^Function showHelp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^Function UpdateTable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/dupecheck/mergerecords.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_code_set_filters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_is_code_type_justify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function code_set_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_codetypes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convert_type_id_to_key may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function define_external_table may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fees_are_used may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isSnomedSpanish may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function justifiers_are_used may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function limit_query_string may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lookup_code_descriptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function main_code_set_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function modifiers_are_used may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function multiple_code_set_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function recursive_related_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function related_codes_are_used may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function return_code_information may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sequential_code_set_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function InsType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LWDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Function custom_labworks_Add may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mydie may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_labworks.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabelNumber may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_registry_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMeasureNumber may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_registry_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addInsurance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function custom_xml_Add may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setInsurance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCombinePatients may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadQRDACat1 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllActiveMedications may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllImmunization may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllInterventionProcedures may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllLabTests may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllMedicalProbs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllOrderMedications may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllPatientEncounters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllPhysicalExams may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllProcedures may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllRiskCatAssessment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getComponentQRDA1 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getHeaderQRDA1 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMeasureSection may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getQRDACat1PatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getReportingParam may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mainQrdaCatOneGenerate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function patCharactersticQRDA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function payerQRDA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allActiveMedsPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allEncPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allImmuPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allListsPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allOrderMedsPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allProcPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allVitalsPat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFacilDataChk may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getQRDAPatientNeedInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getQRDAPayerInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getQRDAStratumInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUsrDataCheck may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function patientQRDAHistory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function payerPatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_system_info may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Function array_walk_trim may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function profiler_start may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function profiler_stop may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function batchcom_WriteLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_InsertNotificationLogEntryBatchcom may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_age may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_date_format may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_csv may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function register_email may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function where_or_and may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batchcom.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function unique_by_key may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edi_270.php',
];
$ignoreErrors[] = [
    'message' => '#^Function era_payments_callback may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_list_payment_category may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/payment_master.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function eob_process_era_callback may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function eob_process_era_callback_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDetailLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMessageLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getOldDetail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parseDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Function SavePatientAudit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function emailLogin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function eob_search_era_callback may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fixup_invoice may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_auth_portal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function notify_portal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function upload_file_to_client may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function upload_file_to_client_pdf may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_clinic may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function buildTemplate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_ub04_claim may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_payer_defaults may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_ub04_array may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function savePayerTemplate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveTemplate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ub04Dispose may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ub04_dispose may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_codes_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lookup_codes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeTemplateLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Function areVendorsUsed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkWarehouseUsed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genWarehouseList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_email may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generateEmptyTd may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Function inventory_mapToTable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Function inventory_mergeData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Function inventory_processData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isFacilityAllowed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isProductSelectable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isUserRestricted may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isWarehouseAllowed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sellDrug may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_drug_email may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function array_key_exists_default may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LicensedPrescriber may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function MidlevelPrescriber may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function OutsidePrescription may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PatientFreeformAllergy may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PatientFreeformHealthplans may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PatientMedication may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PrescriptionRenewalResponse may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Staff may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function SupervisingDoctor may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function account may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkError may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function credentials may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function destination may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function erx_error_log may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getErxCredentials may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getErxPath may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getErxSoapPath may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function location may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function stringToNumeric may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function stripPhoneSlashes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function stripSpecialCharacter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function stripSpecialCharacterFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function stripStrings may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function trimData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function user_role may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function validation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getKittens may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mergeTiffs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addAppt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addBilling2 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addVitals may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function content_parser may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function patient_age may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_commands may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function remove_comments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function replace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMyPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function myauth may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function searchName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formatVitals may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/notegen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/notegen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function CAMOS_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bottomHeaderRx may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Function topHeaderRx may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_new_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_new_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_printable_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_printable_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function aftercare_plan_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ankleinjury_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bronchitis_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function care_plan_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/care_plan/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function clinic_note_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function clinical_instructions_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function clinical_notes_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dictation_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/dictation/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function startsWith may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/js/eye_base.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Menu_myGetRegistered may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_CODING_items may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_IMPPLAN_items may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function build_PMSFH may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function canvas_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cmp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function coding_carburetor may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function coding_engine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function copy_forward may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_GlaucomaFlowSheet may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_PMSFH may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_PRIOR_section may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_QP may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_VisualAcuities may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_draw_section may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_refractive_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function document_engine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function findProvider may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_lens_treatments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_specRx may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getIOPTARGETS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function in_array_r may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function menu_overhaul_bottom may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function menu_overhaul_left may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function menu_overhaul_top may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function priors_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_header may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_json_values may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function show_PMSFH_panel may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function show_PMSFH_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_your_engines may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deliver_document may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function make_document may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function make_task may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_tasks may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function show_task may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_taskman may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_draw_image may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function eye_mag_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function left_overs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function narrative may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_ACT may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function debug may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Function eye_mag_row_delete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Function find_contraceptive_methods may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/ajax/find_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_method_description may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/ajax/find_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Function echoProductLines may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function echoServiceLines may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endFSCategory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genDiagJS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fee_sheet_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function diag_code_types may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/code_check.php',
];
$ignoreErrors[] = [
    'message' => '#^Function load_fee_sheet_options may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_options_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function common_diagnoses may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_diags may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_procs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fee_sheet_items may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_diagnoses may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function select_encounters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_issues may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_justify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function diagnosis_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_search_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Function functional_cognitive_status_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gad7_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAttendanceStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroupAttendance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_appt_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_form_id_of_existing_attendance_form may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_group_encounter_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_groups_cat_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function if_to_create_for_patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insert_into_tgpa_table may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insert_patient_appt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insert_patient_encounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function largest_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function largest_id_plus_one may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function participant_insertions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function group_attendance_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function misc_billing_options_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sensitivity_compare may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newGroupEncounter_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newGroupEncounter/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newpatient_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function note_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/note/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function observation_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function painmap_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/painmap/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phq9_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function showExamLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function showTreatmentLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Function physical_exam_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prior_auth_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListOptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_lab_name may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isDornLab may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function normalizeDirectoryName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveEreq may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleteProcedure may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/handle_deletions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleteSpecimen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/handle_deletions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleteRemovedOrderCodes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleteRemovedSpecimens may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getOrCreateProcedureType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insertProcedureOrderCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insertProcedureSpecimen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveProcedureAnswers may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveProcedureOrderCodes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveProcedureSpecimens may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function softDeleteRemovedSpecimens may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateProcedureOrderCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateProcedureSpecimen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function procedure_order_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function questionnaire_assessments_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function reviewofs_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ros_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sdoh_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function soap_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function track_anything_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transfer_summary_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function treatment_plan_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertHeightToUs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertWeightToUs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertpoint may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cssFooter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cssHeader may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cssPage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function unitsDist may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function unitsWt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Function US_weight may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function vitals_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetCallingScriptName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function UrlIfImageExists may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function strterm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function find_or_create_constant may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Function utf8_fopen_read may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Function verify_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Function verify_translation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Function verify_translations may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_pattern may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/language.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insert_language_log may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/language.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function activate_lang_tab may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/language.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDefaultLanguage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLanguagesList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Function brCustomPlaceholder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_tar_archive may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gz_compress_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gzopen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function obliterate_dir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function DOBandEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function InsertEventFull may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isDaysEveryWeek may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isRegularRepeat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setEventDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doOneDay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnConfigGetVar may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnConfigInit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnDBGetConn may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnDBGetTables may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnGetBaseURI may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnGetBaseURL may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnInit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarCleanFromInput may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepForDisplay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepForOS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepForStore may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnVarPrepHTMLDisplay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAPIFunc may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAPILoad may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModAvailable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModDBInfoLoad may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModFunc may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModGetIDFromName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModGetInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModGetVar may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModLoad may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModSetVar may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnModURL may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dtSec may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dtSecDur may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function findFirstAvailable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function findFirstInDay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pcGetTopicName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pcVarPrepForDisplay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pcVarPrepHTMLDisplay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pc_clean may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_footer may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_getDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_makeValidURL may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_removeScriptTags may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_today may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_buildDaySelect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_buildMonthSelect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_buildYearSelect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_getCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_getTopics may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_getmonthname may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_jsPopup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_loadPopups may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_pageSetup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byCategoryA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byCategoryD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byTimeA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byTimeD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byTitleA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sort_byTitleD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_date_format may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_date_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_filter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_form_nav_close may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_form_nav_close.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_form_nav_open may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_form_nav_open.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_popup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_sort_events may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_url may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_url.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_pc_view_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_view_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_pc_date_format may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/modifier.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_categories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_categoriesConfirm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_categoriesUpdate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_clearCache may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_modifyconfig may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_admin_testSystem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_adminmenu may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_adminapi_addCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadminapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_adminapi_deleteCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadminapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_adminapi_updateCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadminapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_pntables may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pntables.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_user_display may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_user_search may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_user_view may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuser.php',
];
$ignoreErrors[] = [
    'message' => '#^Function calculateEvents may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fillBlocks may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getBlockTime may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_buildView may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_pcGetEvents may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_pcQueryEvents may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postcalendar_userapi_pcQueryEventsFA may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dateSearch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Add may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function AddIfPresent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endClient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exportEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTextListValue may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mappedFieldOption may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mappedOption may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function xmlTime may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/ippf_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_html_end may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_html_middle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_html_start may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_html_top may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_html_u2f may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function input_focus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function posted_to_hidden may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lab_results_messages may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Function renderPaginationControls may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_X12_Claimrev_get_reports may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_X12_Claimrev_send_files may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_send_eligibility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_X12_SFTP may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/SFTP_Mock_Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doOnetimeDocumentRequest may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doOnetimeInvoiceRequest may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadAndStoreRecording may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCallAnswered may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCallEnded may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCallInProgress may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCallOffHold may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCallOnHold may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleIncomingCall may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleMessageStore may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleNewFax may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleNewVoicemail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handlePresenceEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleRecording may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleTelephonySession may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function local_log may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function processRingCentralEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function storeCallEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_GetAlertPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_GetNotificationData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_InsertNotificationLogEntryFaxsms may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_SetMessage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function displayHelp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formatErrorMessage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isValidPhone may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rc_sms_notification_cron_update_entry may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doEmailNotificationTask may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doSmsNotificationTask may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadAndStoreFaxMedia may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTwigNamespaces may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_add_menu_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_document_render_action_anchors may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_document_render_javascript_fax_dialog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_patient_report_render_action_buttons may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_patient_report_render_javascript_post_load may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_sms_render_action_buttons may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_faxsms_sms_render_javascript_post_load may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_priorauth_add_menu_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_priorauth_patient_menu_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isValid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Function download_zipfile may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/file_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadWenoPharmacy may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadWenoPrescriptionLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getModuleState may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleDownloadError may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Function requireGlobals may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadWenoLogCsv may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Function downloadWenoLogCsvAndZip may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProviderByWenoId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Function my_decrypt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/config/autoload/global.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleGetMeasuresForPeriod may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleUpdateReportingPeriod may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function comprehensive_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function comprehensive_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function comprehensive_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getSearchClass may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function default_gen_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function default_loadPayerInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function default_send_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabID may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/load_compendium.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_order_summary may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oresRawData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pendingFollowupLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/pending_followup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pendingOrdersLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/pending_orders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function onvalue may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Function proc_provider_invalue may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Function loadColumnData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function proc_stats_genAnyCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function proc_stats_genHeadCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function proc_stats_genNumCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function proc_stats_getListTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_result_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_qoe_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/qoe.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_encounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_skeleton_patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPerformingOrganizationDetails may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hl7Crypt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function labNotice may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lookupTestCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function match_lab may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function match_patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function match_provider may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parseZPS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function poll_hl7_results may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receive_hl7_results may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7Abnormal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7CWE may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7Date may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7DateTime may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7DateTimeZone may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7DecodeData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7FlushMDM may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7FlushMain may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7InsertRow may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7LogMsg may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7MimeType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7ReportStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7Text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rhl7UpdateReportWithSpecimen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_order_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_result_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function storeNote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function recursiveDelete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Function types_invalue may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Function delete_document may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function delete_drug_sales may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleter_row_delete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleter_row_modify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function form_delete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dataFixup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doSubs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getIssues may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function keyReplace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function keySearch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceCompanies may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/copay.php',
];
$ignoreErrors[] = [
    'message' => '#^Function feSearchSort may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genFieldIdString may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_history_codes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_history.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ffescape may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_custom_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generatePageElement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDocListByEncID may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function showDocument may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fn_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Function render_attrs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Function render_list_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Function v may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hs_badge_class may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_widget.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hs_clip may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_widget.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hs_lo_title may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_widget.php',
];
$ignoreErrors[] = [
    'message' => '#^Function calculateScores may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/manage_dup_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function displayRow may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/manage_dup_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deleteRows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function logMergeEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mergeRows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolveDuplicateEncounters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolveTargets may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateRows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkout_getListTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_layout_display_field may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function invoiceChecksum may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippfReceiptDetailLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_generate_receipt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function load_adjustments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function load_taxes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pull_adjustment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pull_tax may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptPaymentLineIppf may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_form_headers may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_form_line_ippf may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_old_payment_line may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Function normal_generate_receipt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printFacilityHeader may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printProviderHeader may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptDetailLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptPaymentLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_form_line may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genColumn may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getContent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ActiveIssueCodeRecycleFn may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCodeText may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Function displayLogin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/create_portallogin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function areCredentialsCreated may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deceasedDays may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function filterActiveIssues may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getHiddenDashboardCards may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_document_by_catg may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function image_widget may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isApiAllowed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isContactEmail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isEnforceSigninEmailPortal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isPortalAllowed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isPortalEnabled may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isPortalSiteAddressValid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pic_array may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function print_as_money may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function demographics_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Function demographics_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Function demographics_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDisclosureByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disc_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationObservationLists may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationObservationResults may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveImmunizationObservationResults may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/immunizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printAmendment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/print_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertToDataArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/shot_record.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printHTML may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/shot_record.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printPDF may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/shot_record.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPrescriptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transaction_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transaction_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Function transaction_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Function myLocalJS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addwhere may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function universal_ereqForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Function universal_gen_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function universal_loadPayerInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function universal_send_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function labcorp_ereqForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hl7Race may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hl7Workman may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function labcorp_gen_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function labcorp_loadPayerInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function labcorp_send_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function loadGuarantorInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function orderDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/libs/labs_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function quest_gen_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function quest_loadPayerInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function quest_send_hl7_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectItemizedPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formatPatientReportData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRuleObjectForId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function appointments_fetch_reminders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetch_rule_txt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endDoctor may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postError may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appt_encounter_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endInsurance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function endPatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insuranceSelect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function destroyed_mapToTable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/destroyed_drugs_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function destroyed_mergeData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/destroyed_drugs_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function destroyed_processData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/destroyed_drugs_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function show_doc_total may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function format_cvx_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/immunization_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function format_ethnicity may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/immunization_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEndInventory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Function inventoryActivityLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addWarning may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkReorder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genUserWarehouses may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_report_line may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function zeroDays may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function inventoryTransactionsLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cypReportLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_cyp_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formatcyp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_cyp_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_daily_genAnyCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_daily_genHeadCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_daily_genNumCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_query may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_title may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAbortionMethod may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getContraceptiveMethod may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGcacClientStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRelatedAbortionMethod may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRelatedContraceptiveCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hadRecentAbService may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippfLoadColumnData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_stats_genAnyCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_stats_genHeadCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_stats_genNumCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_stats_getListTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_ippf_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_ma_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_referral may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function process_visit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function uses_description may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLoggedInUserFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/non_reported.php',
];
$ignoreErrors[] = [
    'message' => '#^Function mapCodeType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/non_reported.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetAllCredits may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetAllUnapplied may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function List_Look may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PrintCreditDetail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function PrintEncFooter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function payerCmp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptsByMethodLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function showLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function salesByItemLineItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkBackgroundServices may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkCreateCDB may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateBackgroundService may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addOrDeleteColumn may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectLayoutNames may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function encodeModifier may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fuzzyRename may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genFieldOptionList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genGroupId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genGroupSelector may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genLayoutOptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isColumnReserved may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function nextGroupOrder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function renameColumn may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setLayoutTimestamp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function swapGroups may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function tableNameFromLayout may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeFieldLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ctGenCbox may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ctGenCell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ctSelector may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCodeDescriptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listChecksum may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeCTLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeFSLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeITLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeOptionLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Function applyCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addrbook_invalue may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Function writeRow may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_registrations.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_and_download_certificates may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_client_cert may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function delete_certificates may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function download_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function do_visit_form may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ippf_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_MedEx may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/MedEx_background.php',
];
$ignoreErrors[] = [
    'message' => '#^Function error_xml may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function user_group_listings_xml may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function username_listings_xml may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/adminacl_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function write_code_info may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/code_attributes_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patients_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/document_helpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Function background_shutdown may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Function execute_background_service_calls may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertFtoC may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getIdealYSteps may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getIntoCmMultiplier may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLbstoKgMultiplier may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function graphsGetValues may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/graphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function AjaxDropDownCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deduplicateResults may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCheckPersonMatch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleCreatePerson may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleLinkPersonToPatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleSearchPersons may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleUnlinkPersonFromPatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function searchPatientDataTable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function searchPersonTable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function nameHistoryDelete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/specialty_form_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function nameHistorySave may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/specialty_form_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ajax_handleRequest may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/track_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dicom_history_action may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMultiple may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/upload.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcAdd may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcAddForce may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcCollect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcComplete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcCompleteSafe may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcInComplete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcInCompleteSafe may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcNoSoCProvided may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcRemove may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcSoCProvided may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function amcTrackingRequest may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function businessDaysDifference may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function processAmcCall may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formDisappear may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formFetch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formFooter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formHeader may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formJump may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formReappear may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formSubmit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formUpdate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/api.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateAppointmentStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointment_status.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByComment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByCompletedDrugScreen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByDoctorName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByPatientId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByPatientName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByTime may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByTrackerStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareAppointmentsByType may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compareBasic may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function createAvailableSlot may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ends_in_a_week may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchAllEvents may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchAppointmentCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchAppointments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchEvents may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchNextXAppts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchRecurrences may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchXPastAppts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAvailableSlots may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCompareFunction may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getComparisonOrder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getSlotSize may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function interpretRecurrence may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function recurrence_is_current may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sortAppointments may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function authCloseSession may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function authLoginScreen may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function start_X12_SFTP may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/billing_sftp_service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserFacWH may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserFacilities may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_holiday may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_weekend_day may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/calendar.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function craGetTimestamps may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/checkout_receipt_array.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generateReceiptArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/checkout_receipt_array.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAdjustTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/checkout_receipt_array.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptArrayDetailLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/checkout_receipt_array.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function receiptArrayPaymentLine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/checkout_receipt_array.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function array_merge_2 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function array_merge_n may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function active_alert_summary may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function allergy_conflict may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function appointment_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function buildPatientArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function buildPatientArrayEncounterBillingFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function buildPatientArrayPrimaryProviderBillingFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function calculate_percentage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function calculate_reminder_dates may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function clinical_summary_widget may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_database_label may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_plan may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_rule may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function compare_log_alerts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertCompSql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertDobtoAgeMonthDecimal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convertDobtoAgeYearDecimal may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function database_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dueStatusCompare may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_custom_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_database_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_lifestyle_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_lists_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function exist_procedure_item may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function itemsNumberCompare may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listingCDRReminderLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lists_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function procedure_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function reminder_results_integrate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_action_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_filter_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_plans_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_reminder_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_rules_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resolve_target_sql may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function returnTargetGroups may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rules_clinic_get_providers may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function set_plan_activity_patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function set_rule_activity_patient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sql_interval_string may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_filter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic_batch_method may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic_collate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic_cqm_amc_rule may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_rules_clinic_group_calculation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function test_targets may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _contraception_billing_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/contraception_billing_scan.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function contraception_billing_scan may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/contraception_billing_scan.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_crt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_csr may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_user_certificate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/create_ssl_certificate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_like_join may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_quote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Function maybe_csv_quote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Function need_csv_quote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Function split_csv_line may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/csv_like_join.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listitemCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Delete_Rows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/personalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Insert_Rows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/personalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetAllReminderCount may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetDueReminderCount may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetPortalAlertCounts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function GetServiceOtherCounts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function RemindersArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getReminderById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRemindersHTML may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function logRemindersArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sendReminder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setReminderAsProcessed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function array_natsort may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getBillsBetweendayReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/daysheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_allow_document_mimetype may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_close may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_connect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_extension may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_logit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_notify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_read_blob may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_service_userID may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_store may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_write may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function phimail_write_expect_OK may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addNewDocument may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Function document_category_to_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_extension may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDupScoreSQL may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dupscore.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_code_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_997_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_ta1_code may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/codes/edih_997_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_271_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_271_transaction_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_277_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_277_transaction_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_278_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_278_transaction_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_clp_summary may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_payment_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_transaction_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_round_cb may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_err_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_errdata may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_error may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_sbmtfile may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_cleanup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_create_zip may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_array may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_combine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_csv_split may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_date may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_filenames may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_main may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_move_old may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_restore may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_rewrite_csv may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_archive_undo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_claim_history may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_process_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_to_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_list_denied_claims may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_archive_select_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_array_bounds may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_array_flatten may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_assoc_array may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_check_filepath may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_check_x12_obj may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_clear_tmpdir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_convert_bytes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_denied_by_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_dirfile_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_edih_basedir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_edih_tmpdir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_edihist_log may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_controlnum may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_enctr may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_by_trace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_file_type may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_log_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_log_manage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_newfile_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_notes_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_parameters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_pid_enctr_parse may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_processed_files_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_search_record may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_setup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_singlerecord_test may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_table_header may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_table_select_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function csv_thead_html may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_order may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_csv_write may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_errseg_parse may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_format_date may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_format_money may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_format_percent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_format_telephone may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_271_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_277_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_278_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_837_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_csv_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_parse_date may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_parse_select may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_rsp_st_match may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_archive may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_archive_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_archive_restore may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_clmhist may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_csvtable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_denied_claims may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_era_processed may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_file_process may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_file_upload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_log may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_logfiles may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_x12file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_x12trans may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_php_inivals may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_user_notes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_271_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_277_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_278_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_837_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_997_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_change_loop may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_display_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_segments_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_sort_upload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_err_message may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_files may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_match_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_reindex may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_ziptoarray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_835_accounting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_835_accounting.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_disp_sftp_upload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edih_upload_sftp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_openemr_globals may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sftp_status may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Function emailServiceRun may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/email_service_run.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchCategoryIdByEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchDateService may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setencounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function InsertEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function __increment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function calendar_arrived may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function check_event_exist may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDayName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEarliestDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTheNextAppointment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function todaysEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function todaysEncounterCheck may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function todaysEncounterIf may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function todaysTherapyGroupEncounterCheck may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function todaysTherapyGroupEncounterIf may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_event may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function authorizeForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDocumentsByEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEncounterDateByEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEncounters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormByEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormIdByFormdirAndFormid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormNameByFormdir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormNameByFormdirAndFormid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProviderIdOfEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function hasFormPermission may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/forms.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function gblTimeZones may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getDefaultRenderListOptions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isGpRelation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/gprelations.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setGpRelation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/gprelations.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCounselors may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroupAttendanceStatuses may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroupCounselorsNames may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroupData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getGroupStatuses may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getParticipants may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProvidersOfEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTypeName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserNameById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function unsetGroup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/group.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/immunization_helper.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function ippf_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_con_form may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_con_newtype may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_con_save may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_gcac_form may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_gcac_newtype may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function issue_ippf_gcac_save may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ippf_issues.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetchProcedureId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getBarId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFacilityInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabProviders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLabconfig may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getNPI may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProcedureProvider may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProceduresInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getSelfPay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveBarCode may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_issue_type_category may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function disappearList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListTouch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function reappearList may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setListTouch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lists.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _create_option_element may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function accumActionConditions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function billing_facility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bs_disp_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bs_disp_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bs_disp_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function disp_end_cell may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function disp_end_group may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function disp_end_row may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_layout_rows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_layout_tabs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_layout_tabs_data may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function display_layout_tabs_data_editable may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dropdown_facility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function expand_collapse_widget may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genLabResults may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genLabResultsTextItem may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_display_field may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_form_field may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_layout_validation may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_list_map may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_plaintext_field may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_print_field may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_select_list may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getCodeDescription may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLayoutProperties may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getLayoutTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListItemTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientDescription may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getSmokeCodes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_layout_form_value may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_pharmacies may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isOption may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isSkipped may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_canvas_head may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbf_current_value may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function optionalAge may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parse_static_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function signer_head may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _set_patient_inc_count may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function dateToDB may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fixDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genFacilityTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function genPatientHeaderFooter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAllinsurances may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getByPatientDemographics may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getByPatientDemographicsFilter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEffectiveInsurances may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEmployerData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFacilities may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFacility may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getHistoryData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceDataByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceDataNew may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceNameByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceProvider may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceProviders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceProvidersExtra may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientAge may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientAgeDisplay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientAgeInDays may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientAgeYMD may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientDOB may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientFullNameAsString may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientLnames may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientNameFirstLast may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientNameSplit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientPID may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientPhone may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientSSN may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProviderId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProviderInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProviderName may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patient_balance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_patient_balance_excluding may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_unallocated_patient_balance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_unallocated_payment_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_patient_deceased may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newEmployerData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newHistoryData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newInsuranceData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function parseAgeInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pdValueOrNull may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateDupScore may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateEmployerData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateHistoryData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateInsuranceData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updatePatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectApptStatusSettings may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_Tracker_Elements may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_checkin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collect_checkout may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fetch_Patient_Tracker_Events may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getApptStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_Tracker_Time_Interval may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_checkin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_checkout may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function is_tracker_encounter_exist may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function manage_tracker_status may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient_tracker.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkIfPatientValidationHookIsActive may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patientvalidation.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function DistributionInsert may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function frontPayment may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function payment_row_delete may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function payment_row_modify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/payment.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setpid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pid.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addMailboxPnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addPnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function authorizePnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkPnotesNoteId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkPortalAuthUser may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function deletePnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function disappearPnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAssignedToById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMessageStatusById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientNotes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientNotifications may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientSentNotes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPnoteById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPnotesByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPnotesByUser may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getSentPnotesByDate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function pnoteConvertLinks may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function reappearPnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updatePnote may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updatePnoteMessageStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updatePnotePatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFormsByCategory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRegistered may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRegistryEntry may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRegistryEntryByDirectory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTherapyGroupCategories may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function installSQL may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function isRegistered may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function registerForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateRegistered may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/registry.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function patient_fetch_reminders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function patient_reminder_widget may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_reminders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_reminders may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_reminders_batch_method may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getEmployerReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getHistoryReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getInsuranceReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getListsReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientBillingEncounter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPatientReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRecEmployerData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRecHistoryData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRecInsuranceData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getRecPatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbt_current_value may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function lbt_report may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printDataOne may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printListData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printPatientBilling may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printPatientForms may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printPatientNotes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printPatientTransactions may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printRecData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function printRecDataOne may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function beginReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function bookmarkReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectItemizedPatientsCdrReport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectItemizedRuleDisplayTitle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function finishReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function formatReportData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getStatusReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function insertItemReportTracker may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function listingReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setTotalItemsReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updateReportDatabase may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_amcCollect may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.amcCollect.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_assetVersionNumber may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.assetVersionNumber.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_assetsTemplate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.assetsTemplate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_datetimepickerSupport may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.datetimepickerSupport.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_dispatchPatientDocumentEvent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.dispatchPatientDocumentEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_headerTemplate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.headerTemplate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_xla may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.xla.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_xlj may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.xlj.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_xlt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.xlt.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_xla may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/modifier.xla.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_xlt may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/modifier.xlt.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _smarty_sort_length may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_assemble_plugin_filepath may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assemble_plugin_filepath.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_assign_smarty_interface may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assign_smarty_interface.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_create_dir_structure may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.create_dir_structure.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_display_debug_console may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.display_debug_console.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_get_include_path may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_include_path.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_get_microtime may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_microtime.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_get_php_resource may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_is_secure may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_secure.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_is_trusted may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_trusted.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_load_plugins may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_load_resource_plugin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_resource_plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_process_cached_inserts may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_process_compiled_include may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_read_cache_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_rm_auto may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_rmdir may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rmdir.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_run_insert_handler may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_smarty_include_php may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_cache_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_compiled_include may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_compiled_resource may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_core_write_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_block_textformat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_compiler_assign may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/compiler.assign.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_assign_debug_info may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.assign_debug_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_config_load may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_counter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_cycle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.cycle.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_debug may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_eval may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_fetch may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_checkboxes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_checkboxes_output may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_image may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_options may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_options_optgroup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_options_optoutput may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_radios may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_radios_output may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_select_date may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_select_time may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_table may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_html_table_cycle may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_mailto may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.mailto.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_math may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.math.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_popup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_popup_init may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup_init.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_capitalize may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.capitalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_capitalize_ucfirst may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.capitalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_cat may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.cat.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_characters may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_characters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_paragraphs may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_paragraphs.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_sentences may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_sentences.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_count_words may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.count_words.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_date_format may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_debug_print_var may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.debug_print_var.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_default may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.default.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_escape may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.escape.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_indent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.indent.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_lower may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.lower.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_nl2br may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.nl2br.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _smarty_regex_replace_check may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.regex_replace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_regex_replace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.regex_replace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_replace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.replace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_spacify may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.spacify.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_string_format may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.string_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_strip may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.strip.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_strip_tags may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.strip_tags.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_truncate may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.truncate.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_upper may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.upper.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_modifier_wordwrap may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.wordwrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_outputfilter_trimwhitespace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/outputfilter.trimwhitespace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_outputfilter_trimwhitespace_replace may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/outputfilter.trimwhitespace.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_function_escape_special_chars may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/shared.escape_special_chars.php',
];
$ignoreErrors[] = [
    'message' => '#^Function smarty_make_timestamp may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/shared.make_timestamp.php',
];
$ignoreErrors[] = [
    'message' => '#^Function form2db may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function form2real may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function real2form may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getActorData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAlertData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getHeaderData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getImmunizationData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMedicationData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProblemData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getProcedureData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getReportFilename may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getResultData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql-ccr.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function HelpfulDie may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function edi_generate_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generate_id may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPrivDB may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function get_db may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function privQuery may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function privStatement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlBeginTrans may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlClose may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlCommitTrans may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlFetchArray may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlGetAssoc may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlGetLastInsertId may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlInsert may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlInsertClean_audit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlListFields may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlNumRows may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlQ may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlQuery may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlQueryCdrEngine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlQueryNoLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlRollbackTrans may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlStatement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlStatementCdrEngine may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlStatementNoLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sqlStatementThrowException may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function upgradeFromSqlFile may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql_upgrade_fx.php',
];
$ignoreErrors[] = [
    'message' => '#^Function chg_ct_external_torf1 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function chg_ct_external_torf2 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function drop_old_sct may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function drop_old_sct2 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getFileData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handle_zip_file may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function icd_import may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rmdir_recursive may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function rxnorm_import may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function snomedRF2_import may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function snomed_import may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function temp_copy may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function temp_dir_cleanup may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function temp_unarchive may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function update_tracker_table may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function valueset_import may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function reportTelemetryTask may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/telemetry_reporting_service.php',
];
$ignoreErrors[] = [
    'message' => '#^Function extractAddressDataFromForm may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleAddAddress may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleInactivateAddress may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function handleUpdateAddress may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function populateAddressFromData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function populateContactAddressFromData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveAddressesForPatient may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Function authorizeTransaction may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTransById may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getTransByPid may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function newTransaction may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/transactions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function checkUserSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectAndOrganizeExpandSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function effectiveUser may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserIDInfo may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getUserSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prevSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function removeUserSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function setUserSetting may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/user.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function autoPopulateAllMissingUuids may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/uuid.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_InsertNotificationLogEntrySmsEmail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sms_reminder_WriteLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_InsertNotificationLogEntry may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_SendMail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_SendSMS may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_WriteLog may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_getAlertpatientData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_getNotificationData may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cron_setmessage may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function my_print_r may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Function cleanupRegistrationSession may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function doCredentials may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPidHolder may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function notifyAdmin may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function processRecaptcha may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function resetPassword may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function saveInsurance may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function verifyEmail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_doOneDay may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Function buildNav may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Function collectStyles may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Function renderEditorHtml may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function renderProfileHtml may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Function CloseAudit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function SaveAudit may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Function addPortalMailboxMail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getMails may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPortalPatientDeleted may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPortalPatientNotes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPortalPatientNotifications may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getPortalPatientSentNotes may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function sendMail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function updatePortalMailMessageStatus may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/portal_mail.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_handleRequest may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/track_portal_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Function getAuthPortalUsers may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Function convert_html_to_text may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Function fix_newlines may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Function iterate_over_node may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Function next_child_name may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Function prev_child_name may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Zip may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/document_downloads_action.php',
];
$ignoreErrors[] = [
    'message' => '#^Function recursive_remove_directory may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/document_downloads_action.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_GetAllCredits may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_GetAllUnapplied may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_List_Look may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_PrintCreditDetail may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function portal_PrintEncFooter may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Function postToGet may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Function recursive_writable_directory_test may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFathbf_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFathbf_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFathv_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathv.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFathv_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFathv.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFfms_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFfms.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFfms_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFfms.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_default_ab_location may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_default_client_status may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_default_in_ab_proc may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_default_main_compl may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFgcac_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _LBFgcac_query_current_services may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _LBFgcac_query_recent may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _LBFgcac_query_recent_services may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function _LBFgcac_recent_default may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFgcac.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFvbf_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFvbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBFvbf_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBFvbf.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBTref_javascript may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBTref.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function LBTref_javascript_onload may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/LBF/LBTref.plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_HTML_statement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function create_statement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function make_statement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function osp_create_HTML_statement may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function report_header_2 may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_custom_patient_menu may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/oe-modify-patient-menu-example/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_custom_patient_created_action may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/oe-patient-create-update-hooks-example/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function oe_module_custom_patient_update_action may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/oe-patient-create-update-hooks-example/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_patient_data_to_remote_system may not be defined in the global namespace\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/oe-patient-create-update-hooks-example/openemr.bootstrap.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
