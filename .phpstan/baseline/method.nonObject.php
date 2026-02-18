<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendChild\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createElement\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXML\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXml\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCR.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRActor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRAlerts.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRImmunization.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRMedication.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRProblem.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/createCCRProcedure.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/createCCRResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_categories_array\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addItem\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cryptCheckStandard\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method decryptStandard\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method encryptStandard\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPatientPictureDocumentId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBusinessEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_foreign_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_node_info\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_node_name\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addItem\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_node\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete_node\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edit_node\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_node_info\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_node_name\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method populate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_name\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method populate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_InsuranceNumbers.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_provider\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pharmacies_factory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method collectCodeTypes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezColumnsStart\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezColumnsStop\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezImage\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezNewPage\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezOutput\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezSetY\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezStream\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezText\\(\\) on mixed\\.$#',
    'count' => 31,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filledRectangle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getList\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_dosage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_drug\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_form\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_interval\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_name_display\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_note\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_patient_id\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_per_refill\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_pubpid\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_quantity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_refills\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_route\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_size\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_substitute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_unit_display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method line\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lookup_code_description\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method output\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setColor\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setLineStyle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_x12_sftp_pass\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method populate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_x12_sftp_pass\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_authReprestOrginisation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_entryEffectTimeQRDAMed\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_entryEffectTimeQRDA\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_entryEffectTime\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_entryTime\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_facilAddress\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_facilName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_item\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_patName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_patientAddress\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_providerName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_represtCustodianOrginisation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_title\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_trElementsTitles\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_trElementsValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_act\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_assgnCustodian\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_assignAuthor\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_assignedEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_author\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_clinicaldocument\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_custodian\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_customTag\\(\\) on mixed\\.$#',
    'count' => 49,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_entry\\(\\) on mixed\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_legalAuthenticator\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_list\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_loopComponent\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_mainComponent\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_recordTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_section\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_structuredBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close_text\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method element\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getXml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_act\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_assgnCustodian\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_assignAuthor\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_assignedEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_author\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_clinicaldocument\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_custodian\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_customTag\\(\\) on mixed\\.$#',
    'count' => 49,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_entry\\(\\) on mixed\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_legalAuthenticator\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_list\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_loopComponent\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_mainComponent\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_recordTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_section\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_structuredBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method open_text\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_authorTime\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_codeCustom\\(\\) on mixed\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_confidentcode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_customId\\(\\) on mixed\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_customTag\\(\\) on mixed\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_efftime\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_lang\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_legalSignCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_realmcode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_setid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_templateid\\(\\) on mixed\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method self_typeid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method textDispContent\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_url\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/zutil.cli.doc_import.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ServerInfo\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/about.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method acl_query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 23,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug_text\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../gacl/admin/acl_test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRows\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method acl_query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_paging_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pageexecute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRows\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method acl_query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_paging_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pageexecute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRow\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_group\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug_text\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_group\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edit_group\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format_groups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method return_page\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sort_groups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRows\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_object_section\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug_text\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_object_section\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edit_object_section\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_paging_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pageexecute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method return_page\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRows\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_object\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug_text\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_object\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edit_object\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_paging_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pageexecute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method qstr\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method return_page\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format_groups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sort_groups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/group_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SelectLimit\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug_text\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_schema_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_version\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method qstr\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method startTimer\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method stopTimer\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/profiler.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method bill_info\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method onLogComplete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method RecordCount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendChild\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDebugSetting\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOpenEMRSiteDirectory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXML\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateAllergyUploadedByPatientIdAllergyId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateErxUploadedByListId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updatePatientImportStatusByPatientId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updatePrescriptionsUploadActiveByPatientIdPrescriptionId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fix_html_entities\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxSOAP.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendChild\\(\\) on mixed\\.$#',
    'count' => 92,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createElement\\(\\) on mixed\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createTextNode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAttribute\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendChild\\(\\) on mixed\\.$#',
    'count' => 197,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createElement\\(\\) on mixed\\.$#',
    'count' => 111,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createTextNode\\(\\) on mixed\\.$#',
    'count' => 86,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBusinessEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAttribute\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method checkInventory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genCodeSelectorValue\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genProviderOptionList\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genProviderSelect\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genWarehouseSelect\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method generatePriceLevelSelector\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method jsLineItemValidation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method loadProductItems\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method loadServiceItems\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updatePriceLevel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Output\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method writeHTML\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBillingLocation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBillingLocation\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBillingLocation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_date\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_note\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genPriceLevelSelect\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genProviderSelect\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method genWarehouseSelect\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pricesAuthorized\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method CompleteTrans\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method StartTrans\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getID\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method CompleteTrans\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method StartTrans\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_justify.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addArrayParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addProcParameters\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode_type\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getKey\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/C_FormPriorAuth.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ros/C_FormROS.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/C_FormSOAP.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clear_interpretation\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clear_reason\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_interpretation_codes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_interpretation_list_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_interpretation_option_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_interpretation_title\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_reason_code\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_reason_description\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_reason_status\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_vitals_column\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTwigTemplate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTwigVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/about_page.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/about_page.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTwigTemplate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/about_page.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTwigVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/about_page.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign_by_ref\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_sort_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/modifier.pc_date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCustomWhereFilter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPageId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setPrimaryMenuItem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundFilter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundValues\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFilterClause\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundFilter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFilterClause\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method create_events\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/Holidays_Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_holidays\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/Holidays_Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method import_holidays\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/holidays/Holidays_Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MedExBank\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SMS_bot\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display_add_recall\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display_recalls\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method icon_template\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method navigation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method preferences\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method autoReg\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete_recall\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAge\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save_recall\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTraceAsString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_data\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_mimetype\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_name\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method subscribeToEvents\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTemplateName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDbRecordId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthCalendarController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isCheckOutStatus\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthCalendarController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthToken\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIsActive\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUser\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUsername\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthToken\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounterById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInternalErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPatientEncounterListWithCategories\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUsername\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasInternalErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/FormattedPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/ParticipantListService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUsername\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/ParticipantListService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthToken\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TelehealthConfigurationVerifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUsername\\(\\) on Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Models\\\\TeleHealthUser\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TelehealthConfigurationVerifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRegistrationCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TelehealthRegistrationCodeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Validators/TelehealthPatientValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Validators/TelehealthPatientValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line2\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_phone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createExportPatientJob\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createExportPatientPopulationJob\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getExportSizeSettings\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getExportTaskForStatusUpdate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getJSON\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method runExportTask\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_hash\\(\\) on Document\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/EhiExportJobTask.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_hash_algo_title\\(\\) on Document\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/EhiExportJobTask.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_name\\(\\) on Document\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/EhiExportJobTask.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addPrimaryKey\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attributes\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method children\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method count\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setColumnNames\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Models/ExportState.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addKeyValueList\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addKeyValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRecords\\(\\) on OpenEMR\\\\Modules\\\\EhiExporter\\\\TableDefinitions\\\\ExportTableDefinition\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasNewData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchPersistedSetupSettings\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getVendorGlobals\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persistSetupSettings\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveModuleListenerGlobals\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method authenticate\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequest\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCredentials\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEmailSetup\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method chartFaxDocument\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCredentials\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCredentials\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_rc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_rc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCredentials\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_voice.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verifyAcl\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_voice.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method auth\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createMultipartBuilder\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method data\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeader\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReasonPhrase\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method json\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method loggedIn\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method login\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method platform\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method post\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method raw\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method request\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method response\\(\\) on mixed\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sendRequest\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method text\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method create\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method read\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method auth\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method data\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method loggedIn\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method login\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCredentials\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sendSMS\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method registerNamespaceIfNotExists\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method begin_transaction\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method bind_param\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method commit\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method execute\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method prepare\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method rollback\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method decryptStandard\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRealPath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isDir\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/config/module.config.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fromQuery\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addSubscriber\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method z_xlt\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fromQuery\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method z_xlt\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on Laminas\\\\View\\\\Renderer\\\\RendererInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method partial\\(\\) on Laminas\\\\View\\\\Renderer\\\\RendererInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method execute\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrevious\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method quoteValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method zAclCheck\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/CommonPlugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addSubscriber\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchXmlDocuments\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateDocumentCategoryUsingCatname\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateDocumentCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateDocumentCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method date_format\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method zQuery\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Form/ModuleconfigForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method z_xlt\\(\\) on mixed\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Form/ModuleconfigForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertAllergies\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertCarePlan\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertClinicalNote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertEncounter\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertFunctionalCognitiveStatus\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertImmunization\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertImportedFiles\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertLabResults\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertMedicalProblem\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertObservationPerformed\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertPayers\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertPrescriptions\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertProcedures\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertReconcilation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertReferrals\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method InsertVitals\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertImportedUser\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method parseCDAEntryComponents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method parseQRDAPatientDataSection\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method parseUnstructuredComponents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveValidationLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateDocument\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method zQuery\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXML\\(\\) on SimpleXMLElement\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method zQuery\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/ModuleconfigTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateDocumentCategoryUsingCatname\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateDocumentCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addSubscriber\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/CodeTypes/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addHeaderLine\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clearHeaders\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fromRoute\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategoryIDs\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDocument\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPost\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isPost\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addSubscriber\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/FHIR/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method codeslist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method date_format\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getImmunizationObservationResultsData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getListtitle\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getNotes\\(\\) on mixed\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method immunizedPatientDetails\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method count\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method current\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method zQuery\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method z_xlt\\(\\) on mixed\\.$#',
    'count' => 36,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariable\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method checknamespace\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method deleteMultidbById\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchAll\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMultipledbById\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method randomSafeKey\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method storeMultipledb\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method count\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method current\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method select\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addListener\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFilter/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setBoundValues\\(\\) on string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFilter/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setFilterClause\\(\\) on string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFilter/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addSubscriber\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFlowBoard/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariable\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPatients\\(\\) on array\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fromQuery\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/src/PrescriptionTemplates/Controller/HtmlTemplatesController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fromQuery\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/src/PrescriptionTemplates/Controller/PdfTemplatesController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PrescriptionTemplates/src/PrescriptionTemplates/Controller/PdfTemplatesController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method attach\\(\\) on Laminas\\\\EventManager\\\\SharedEventManagerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEvent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRouteMatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTarget\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getViewModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method layout\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setVariables\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method date_format\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/SyndromicsurveillanceController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method run\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_phone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/cash_receipt.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method buttonHtml\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isButtonViewable\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isLocked\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isLogViewable\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method renderLog\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method decryptStandard\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParsedData\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSource\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTransactionReference\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSuccessful\\(\\) on bool\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSuccessful\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method AddPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/label.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Add_Label\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/label.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Output\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/label.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBusinessEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_date\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_note\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isLogViewable\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSigned\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method renderLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ErrorMsg\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method pageHeading\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/dashboard_header.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method authorized\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method canAdd\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method canCollapse\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method canEdit\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAcl\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBackgroundColorClass\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTemplateFile\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTemplateVariables\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTextColorClass\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isInitiallyCollapsed\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method authorized\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method login\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method navigation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method possibleModalities\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_phone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_ins_type_code\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line2\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_phone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_phone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/quest/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getActionData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hydrateItemizedDataFromRecord\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAuthUserId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_accountStatusDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAuthUserId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_allergy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAuthUserId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/soap_functions/soap_patientfullmedication.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGroupEvents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGroup\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParticipants\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isAlreadyRegistered\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removeParticipant\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveParticipant\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateParticipant\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method changeGroupStatus\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method existGroup\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllCounselors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllGroups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllUsers\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCounselors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGroupEncounters\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGroupEvents\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGroup\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUserNameById\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method remove\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveNewGroup\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateGroup\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on Psr\\\\Http\\\\Message\\\\ResponseInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on Psr\\\\Http\\\\Message\\\\ResponseInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Abstract/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Abstract/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hash\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ESign/DbRow/Signable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verify\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/DbRow/Signable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Button.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Button.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Encounter/Signable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Button.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Button.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParam\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/ESign/Form/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHtml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Form/Log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verify\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Signature.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAmendment\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getClass\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDatetime\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFirstName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLastName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSuffix\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValedictory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/views/default/esign_signature_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method __increment\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method calculateEvents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method checkModality\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method generate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAge\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLastError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPreferences\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getResponse\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUrl\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method log_this\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method makeRequest\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method receive\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setData\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setUrl\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method show_progress_recall\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sync\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOrCreateForEntity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method info\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundFilter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFilterClause\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CategoryTree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CategoryTree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTimestamp\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line2\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_line2\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method toString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method x12_partner_factory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/InsuranceCompany.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Note.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Note.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method persist\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_line2\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method toString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_dob\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_name_display\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_provider_number_default\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_group_number\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Provider.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_provider_number\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Provider.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_rendering_provider_number\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Provider.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ErrorMsg\\(\\) on mixed\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GenID\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addItem\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getChildren\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getNode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTag\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasChildren\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MoveNext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/class.Parser_HL7v2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetDrawColor\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetFillColor\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _out\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method test\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AbstractAmcReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addDenominatorActionData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Denominator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addNumeratorActionData\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addDenominatorActionData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_7/Denominator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addNumeratorActionData\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_7/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Cqm/library/AbstractCqmReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method test\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Cqm/library/AbstractCqmReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetCredentials\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/login_sasl_client.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Start\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/sasl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Step\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/sasl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method create_thumbnail\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_string_file\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method image_to_file\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method retrieve_doc\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update_doc\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchReminderCount\\(\\) on OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\ClickatellSMSClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\|OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/dated_reminder_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_delimiters\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_filename\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_x12_transaction\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_271_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_delimiters\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_filename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_x12_transaction\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_delimiters\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_filename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_message\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_x12_transaction\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_271_code\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_835_code\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_delimiters\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_segments\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_delimiters\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_envelopes\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_filename\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_get_segment\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_segments\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_type\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edih_x12_transaction\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGlobalsService\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/globals.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllBillingLocations\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllFacility\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllFacility\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getById\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFacilityForUser\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrimaryBillingLocation\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.amcCollect.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.create_dir_structure.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _compile_resource\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.display_debug_console.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_compile_path\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.display_debug_console.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _include\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.display_debug_console.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _parse_resource_name\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _syntax_error\\(\\) on class\\-string\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _syntax_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.get_php_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _trigger_fatal_error\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_resource_plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _trigger_fatal_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_resource_plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _dequote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _eval\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _include\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _include\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_compiled_include.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _fetch_resource_info\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_auto_filename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_auto_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _read_file\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_auto_filename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _unlink\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rm_auto.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _unlink\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.rmdir.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _dequote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _eval\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _include\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _eval\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _include\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.smarty_include_php.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_auto_filename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_auto_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_compiled_resource.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _parse_attrs\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/compiler.assign.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _syntax_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/compiler.assign.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.assign_debug_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _dequote\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _fetch_resource_info\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_compile_path\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _parse_resource_name\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.counter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.cycle.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.cycle.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _compile_source\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _eval\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.eval.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _trigger_fatal_error\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.fetch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.mailto.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.math.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method trigger_error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup_init.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _get_plugin_filepath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/modifier.date_format.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ExecuteNoLog\\(\\) on ADOConnection\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ExecuteNoLog\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method PConnect\\(\\) on ADOConnection\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetFetchMode\\(\\) on ADOConnection\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetFetchMode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method connect\\(\\) on ADOConnection\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAssoc\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setConnectionParameter\\(\\) on ADOConnection\\|false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAppointment\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDataElement\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchDefaultCategories\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchDefaultProfiles\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchTemplateEvent\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTemplateListAllCategories\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTemplateListByProfile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPrevious\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Output\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParsedData\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSource\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTransactionReference\\(\\) on bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSuccessful\\(\\) on bool\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSuccessful\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetOrder\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetWhere\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method AttachObserver\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Close\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method CommitTransaction\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Fetch\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetLastInsertId\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetLastInsertId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetTableName\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetTableNames\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Observe\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Open\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Optimize\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Release\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method RollbackTransaction\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method StartTransaction\\(\\) on IDataDriver\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObject\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Fetch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetValueCache\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IncludeModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Observe\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Refresh\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Release\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Select\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetValueCache\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRoute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IsNumeric\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFont\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStyle\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setBold\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setCellValue\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setCreator\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setName\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setSize\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Observe\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Observable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ObserveToSmarty.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method DeleteCache\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetEnumValues\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetFieldMaps\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetKeyMaps\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IncludeModel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Observe\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Refresh\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method SetCache\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method AttachObserver\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method CacheLevel\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetOrs\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IsLoaded\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Refresh\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Set\\(\\) on array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToString\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method AttachObserver\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetDataPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetFieldMaps\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IsAnonymous\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method IsAuthorized\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Observe\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Refresh\\(\\) on class\\-string\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Set\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObject\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetch\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetFieldMaps\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/QueryBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetKeyMaps\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/QueryBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetWhere\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/QueryBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTraceAsString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/AppBasePortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetDataPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObjectArray\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetDataPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetValidationErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObjectArray\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetDataPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetValidationErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObjectArray\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetArray\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetValidationErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObjectArray\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetArray\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetValidationErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Get\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ToObjectArray\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetOneToMany\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Model/DAO/UserDAO.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultError404.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultError404.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultErrorFatal.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultErrorFatal.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/templates/DefaultErrorFatal.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteActivityViewListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteActivityViewListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsitePortalActivityListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsitePortalActivityListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/PatientListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/PatientListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method assign\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/_FormsHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/_Header.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method eprint\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/templates/_modalFormHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Execute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isLogViewable\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method renderLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPathname\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isDir\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPartner\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaimBatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method encryptStandard\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAction\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLogger\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addClaim\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method close\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorExternal.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setLogCompleteCallback\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezNewPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezOutput\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezSetY\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatFilename\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setLogCompleteCallback\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addPngFromFile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF_IMG.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezNewPage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF_IMG.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezSetY\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF_IMG.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ezText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF_IMG.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04Form_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatFilename\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04Form_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04Form_PDF.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04NoForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatFilename\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04NoForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04NoForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addClaim\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method append_claim\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method append_claim_close\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatContent\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatFilename\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setLogCompleteCallback\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method write_batch_file\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setLogCompleteCallback\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addClaim\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method append_claim\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method append_claim_close\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatContent\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBatFilename\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getClaims\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPartner\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPayorType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setIsLast\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method write_batch_file\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/TaskMarkAsClear.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/TaskMarkAsClear.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method appendToLog\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/TaskReopen.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method printToScreen\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/TaskReopen.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOrderingProviderID\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReferringProviderID\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_address\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_ins_claim_type\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_ins_type_code\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method additionalNotes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method autoAccidentState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingContactPhone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityAssignment\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityCity\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityETIN\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityNPI\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityStreet\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingFacilityZip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingProviderFirstName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingProviderLastName\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingProviderMiddleName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method billingProviderNPI\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method box14Qualifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method box15Qualifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method claimTypeRaw\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method claimType\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptCharges\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptKey\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptModifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptNDCID\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptNDCQuantity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptNDCUOM\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptNotecodes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cptUnits\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dateInitialTreatment\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method diagIndexArray\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method epsdtFlag\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityCity\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityNPI\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityPOS\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityStreet\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method facilityZip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method federalIdType\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getColumn\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInfo\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRow\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getWidth\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method groupName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method groupNumber\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hospitalizedFrom\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hospitalizedTo\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredCity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredDOB\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredFirstName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredLastName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredMiddleName\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredPhone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredRelationship\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredSex\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredStreet\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insuredZip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isHospitalized\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isOutsideLab\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isRelatedAuto\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isRelatedEmployment\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isRelatedOther\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isUnableToWork\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method medicaidReferralCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method miscOnsetDate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method offWorkFrom\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method offWorkTo\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method onsetDate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method outsideLabAmount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientCity\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientDOB\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientFirstName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientLastName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientMiddleName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientPaidAmount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientPhone\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientSex\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientStreet\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientZip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerAttn\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerCity\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerCount\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerSequence\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerStreet\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerTotals\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method payerZip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method planName\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method policyNumber\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method procCount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerFirstName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerGroupNumber\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerLastName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerMiddleName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerNPI\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerNumberType\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerNumber\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerSuffixName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method providerTaxonomy\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method referrerFirstName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method referrerLastName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method referrerMiddleName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method referrerNPI\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method referrerTaxonomy\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method serviceDate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supervisorNumberType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supervisorNumber\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Hcfa1500.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessage\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isRedirect\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSuccessful\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method purchase\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method send\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateFilterCriteria\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateTargetCriteria\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method route\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ControllerRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method display\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/ReminderIntervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method resolveRuleCriteriaType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method buildNewInstance\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setGroups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_acl\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_group\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_group_object\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_object\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_object_section\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method append_acl\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_acl\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_group\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method del_group_object\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method edit_object\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_acl\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_group_children\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_group_data\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_group_id\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_object_data\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_object_groups\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_object_id\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_object_section_section_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_objects\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_root_group_id\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_section_data\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search_acl\\(\\) on mixed\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method shift_acl\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AclExtended.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method acl_query\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclMain.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clear_cache\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Acl/AclMain.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method passwordHash\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method passwordNeedsRehash\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeyParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method verify\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method withHash\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/Validation/UniqueID.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/RefreshTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method containsScope\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/SMARTSessionTokenContextBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/SMARTSessionTokenContextBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaImport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getServiceManager\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaImport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaNewpatient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getServiceManager\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaNewpatient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaNewpatientImport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getServiceManager\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CcdaNewpatientImport.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeader\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAccessToken\\(\\) on League\\\\OAuth2\\\\Server\\\\Entities\\\\RefreshTokenEntityInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setExpiryDateTime\\(\\) on League\\\\OAuth2\\\\Server\\\\Entities\\\\RefreshTokenEntityInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setIdentifier\\(\\) on League\\\\OAuth2\\\\Server\\\\Entities\\\\RefreshTokenEntityInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Register.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getServiceManager\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Register.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method register\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Register.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/ZfcModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getServiceManager\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/ZfcModule.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method ErrorMsg\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Insert_ID\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_data_for_save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitals.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_reason_code\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitals.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method run_node_background_process\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestParsedRoute.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on Http\\\\Message\\\\Encoding\\\\GzipDecodeStream\\|resource\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getResource\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpSessionFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpSessionFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method contentType\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method request\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeader\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeaders\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method deleteToken\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeOAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasToken\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeOAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method critical\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method emergency\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method info\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method log\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method notice\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method MetaColumns\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method diff\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method invalidate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method remove\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method set\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method deleteObservationById\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on bool\\|DateTime\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getNewObservationTemplate\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getObservationById\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUuid\\(\\) on OpenEMR\\\\Services\\\\PatientService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveObservation\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method searchAndPopulateChildObservations\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateObservationData\\(\\) on OpenEMR\\\\Services\\\\ObservationService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method bootstrap\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method loadModules\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method xpath\\(\\) on SimpleXMLElement\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method generateCategoryIIIXml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method generateCategoryIXml\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method generateConsolidatedCategoryIIIXml\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getConsolidatedFilename\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method resolveMeasuresPath\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAcl\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Events/UserInterface/PageHeadingRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getClient\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFields\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getService\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setFieldValue\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateEvidenceDSIAttributes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updatePredictiveDSIAttributes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addListener\\(\\) on Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SmartLaunchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method serialize\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SmartLaunchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAppointmentUuid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SmartLaunchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clean\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method save\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method absolutepage\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclAdminApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method atfirstpage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclAdminApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method atlastpage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclAdminApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lastpageno\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclAdminApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method GetRows\\(\\) on ADORecordSet\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clean\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPayload\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Health/Check/CacheCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMenu\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMenu\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method render\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_city\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_country\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line1\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_line2\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_state\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_zip\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method Output\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pdf/PatientPortalPDFDocumentCreator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method deleteAppointmentRecord\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAppointment\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAppointmentsForPatient\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttributes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUserAccount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUserRole\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method containsScope\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 40,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getClient\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodeChallengeMethod\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodeChallenge\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getGrantTypeId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRedirectUri\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getScopes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getState\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isConfidential\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAllAtPath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertAtPath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setSession\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DrugRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DrugRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DrugRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/DrugRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EmployerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSoapNote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSoapNotes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getVital\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getVitals\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertEncounter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertSoapNote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertVital\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateEncounter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateSoapNote\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method updateVital\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateSoapNote\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateVital\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDeviceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method can_access\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method can_patient_access\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_mimetype\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method has_expired\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method is_deleted\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method process_deleted\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGenericRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirLocationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMediaRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationDispenseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirOrganizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createBundle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method write\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method write\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on OpenEMR\\\\Services\\\\Search\\\\SearchFieldComparableValue\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addProfile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/ProfileMappers/FhirConditionProfileMapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/ProfileMappers/FhirConditionProfileMapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterData\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FacilityRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInsuranceTypes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOneById\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceCompanyRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method swapInsurance\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOptionsByListName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/ListRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method delete\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/MessageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/MessageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/MessageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/MessageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterData\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PrescriptionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PrescriptionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PrescriptionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/PrescriptionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRegistrationStatus\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ProductRegistrationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRedirectUri\\(\\) on OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/ApiResponseLoggerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method authorizeRequest\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method shouldProcessRequest\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createResponse\\(\\) on OpenEMR\\\\Common\\\\Http\\\\Psr17Factory\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/VersionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addErrorMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/AllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUuid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insertEncounter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setValues\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on DOMNameSpaceNode\\|DOMNode\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lookupNamespaceURI\\(\\) on DOMElement\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method query\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodeWithType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInternalErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dischargeOptionIdFromCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodeWithType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method resolveCode\\(\\) on mixed\\.$#',
    'count' => 35,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getElementsByTagName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lookupNamespaceURI\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTextParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lookupNamespaceURI\\(\\) on DOMElement\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addErrorMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOptionsByListName\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addProcessingError\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setDisplay\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Document/BaseDocumentDownloader.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Document/BaseDocumentDownloader.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCodeWithType\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lookup_code_description\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method parseCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setSystemLogger\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setSession\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEndDate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getField\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStartDate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setSession\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on Symfony\\\\Component\\\\HttpFoundation\\\\Session\\\\SessionInterface\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setModifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addProcessingResult\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setInternalErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAll\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSupportedSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCategory\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method supportsCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPagination\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on class\\-string\\|object\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method searchPatientProviderGroups\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Group/FhirPatientProviderGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addExtension\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValues\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setDisplay\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method search\\(\\) on OpenEMR\\\\Services\\\\QuestionnaireService\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Utils/SearchRequestNormalizer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTime\\|true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getExtension\\(\\) on class\\-string\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUrl\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addErrorMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFirstDataResult\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method all\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method has\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addErrorMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPagination\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setTotalCount\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addErrorMessage\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validateId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PrescriptionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method calculate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/CqmCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_data_element\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBoundValues\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFilterClause\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFilter\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystemForCodeType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method add_stratification\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/AggregateCount.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_data_elements\\(\\) on mixed\\.$#',
    'count' => 50,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat1Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method build\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method calculateMeasure\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInnerResult\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method key_for_population_set\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method population_keys\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method population_sets_and_stratifications_for_measure\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHealth\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method start\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createQuestionnaireFormDictionary\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAnswer\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getItem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLinkId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getQuestionnaireIdAndVersion\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRepeats\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on class\\-string\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_fhirElementName\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXML\\(\\) on DOMDocument\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method xmlSerialize\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAnswerOption\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDisplay\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getItem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLinkId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRepeats\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCoding\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on class\\-string\\|object\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_fhirElementName\\(\\) on array\\|object\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_fhirElementName\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveXML\\(\\) on DOMDocument\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method xmlSerialize\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCurrentOffsetId\\(\\) on OpenEMR\\\\Common\\\\Database\\\\QueryPagination\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchConfigClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getField\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchConfigClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLimit\\(\\) on OpenEMR\\\\Common\\\\Database\\\\QueryPagination\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchConfigClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isAscending\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchConfigClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method asString\\(\\) on OpenEMR\\\\Services\\\\VersionServiceInterface\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clearTelemetryData\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method debug\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method error\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchActiveModuleCounts\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchEnabledEncounterForms\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchSitePopulationData\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method fetchUsageRecords\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method saveTelemetryEvent\\(\\) on OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method warning\\(\\) on Psr\\\\Log\\\\LoggerInterface\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/AllergyIntoleranceValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/AllergyIntoleranceValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/ConditionValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/ConditionValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method callback\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/CoverageValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Validators/CoverageValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/CoverageValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/EncounterValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/EncounterValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/FacilityValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/FacilityValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/InsuranceCompanyValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/InsuranceCompanyValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/PatientValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/PatientValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Validators/PractitionerValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/PractitionerValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method access_control\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method active_alert_flag\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_rule\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method passive_alert_flag\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patient_reminder_flag\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/alerts/list_actmgr.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method displayDetails\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCharacteristics\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInterval\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequirements\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRuleTypeLabels\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTypes\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isEditable\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategoryLabel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/action.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getItemLabel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/action.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequirements\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/age.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/age.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategoryLabel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/bucket.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getItemLabel\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/bucket.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTableNameOptions\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/custom.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequirements\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDetailFor\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/intervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOptions\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/lifestyle.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/lifestyle.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOptions\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/sex.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequirements\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/simple_text_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getTitle\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/simple_text_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasRuleType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/summary.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRuleTypeLabels\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/review/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AllergyIntoleranceFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AllergyIntoleranceFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AllergyIntoleranceFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getConfig\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patch\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method post\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method put\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/BulkAPITestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/CapabilityFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Api/CapabilityFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method post\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method put\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/FacilityApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/GroupExportFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/GroupExportFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeaders\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/GroupExportFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/GroupExportFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cleanupClient\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method cleanupRevokeAuth\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getOne\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method post\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method put\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/PatientFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Api/PractitionerApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Api/PractitionerApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PractitionerApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/PractitionerApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ProvenanceFhirApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/SmartConfigurationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationHeartRateTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationHeartRateTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationHeartRateTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationScreeningAndAssessmentsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationScreeningAndAssessmentsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/Observation/ObservationScreeningAndAssessmentsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method C14N\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on DOMNameSpaceNode\\|DOMNode\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContents\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAttribute\\(\\) on DOMNameSpaceNode\\|DOMNode\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method toString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Uuid/UuidRegistryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method uuid4\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Uuid/UuidRegistryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method findElement\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method accept\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method alert\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method clear\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method click\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method executeScript\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filterXPath\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method filter\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getElement\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isSelected\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method sendKeys\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method switchTo\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method calculate\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHealth\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method start\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/MeasureResultsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/CarePlanFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removeFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/CarePlanFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installFacilityFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/EncounterFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/EncounterFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removeFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/EncounterFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/EncounterFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFhirPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSinglePatientFixture\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManagerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on ReflectionType\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/GeoTelemetryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on ReflectionType\\|null\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method method\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method willReturn\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method with\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on ReflectionType\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Tools/OAuth2/ClientCredentialsAssertionGeneratorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/AllergyIntoleranceValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/AllergyIntoleranceValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lengthBetween\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/ConditionValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/ConditionValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method allowEmpty\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method callback\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method context\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method copyContext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method datetime\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method inArray\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method lengthBetween\\(\\) on mixed\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method numeric\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method optional\\(\\) on mixed\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method required\\(\\) on mixed\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method uuid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/CoverageValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/EncounterValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/EncounterValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/FacilityValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/FacilityValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/InsuranceCompanyValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/InsuranceCompanyValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/PractitionerValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/PractitionerValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirPatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirPractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/HandleProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/HandleProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/HandleProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setValidationMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/HandleProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getBody\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/PractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHeader\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method patientSelectConfirm\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setClientRepository\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setPatientContextSearchController\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method qual_id_to_description\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Services/Billing/MiscBillingOptionsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method collectCodeTypes\\(\\) on mixed\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../tests/Tests/Services/CodeTypesServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/EncounterServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAbatementDateTime\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionEncounterDiagnosisServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDisplay\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getClinicalStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRecordedDate\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getVerificationStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthenticator\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthor\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategory\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContent\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContext\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDate\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSubject\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getManagingOrganization\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getParticipant\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSubject\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method createDrugSaleDispense\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthorizingPrescription\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getContext\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMedicationCodeableConcept\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getProfileURIs\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getProfile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getQuantity\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSearchParams\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSubject\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getWhenHandedOver\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method parseOpenEMRRecord\\(\\) on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removeFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationDispenseUSCore8ComplianceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAuthoredOn\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategory\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDispenseRequest\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDosageInstruction\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIntent\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMedicationCodeableConcept\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMedicationReference\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReportedBoolean\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReportedReference\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getRequester\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSubject\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtension\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInternalErrors\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method insert\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method update\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFamily\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getType\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUnregisteredUuid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUse\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getExtension\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUrl\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getFamily\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getId\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getName\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCategory\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDisplay\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getExtension\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIssued\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPerformer\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSubject\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getProfile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getProfile\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDataAbsentReason\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEffectiveDateTime\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getField\\(\\) on OpenEMR\\\\Services\\\\Search\\\\ServiceField\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHasMember\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getNote\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPerformer\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueQuantity\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueString\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationObservationFormServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method errorLogCaller\\(\\) on OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getProfile\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getCoding\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getDataAbsentReason\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEffectiveDateTime\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEncounter\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getField\\(\\) on OpenEMR\\\\Services\\\\Search\\\\ServiceField\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getHasMember\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getMeta\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getNote\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getPerformer\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getReference\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatus\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSystem\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getText\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCodeableConcept\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueQuantity\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueString\\(\\) on OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValue\\(\\) on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationQuestionnaireItemServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAnswer\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getItem\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getLinkId\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueCoding\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueDecimal\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValueString\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method C14N\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method format\\(\\) on DateTimeImmutable\\|false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on DOMNameSpaceNode\\|DOMNode\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method item\\(\\) on DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setAttribute\\(\\) on DOMNameSpaceNode\\|DOMNode\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/PatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/PatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installPractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/PractitionerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePractitionerFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/PractitionerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _action_filter\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _action_intervals\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _action_submit_intervals\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _action_submit_summary\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method _action_summary\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrantTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setJwksUri\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrantTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setJwks\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrantTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getIdentifier\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Repositories/ScopeRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method toPaddedString\\(\\) on mixed\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method toString\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_id\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Forms/FormVitalsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_interpretation_codes\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Forms/FormVitalsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_interpretation_title\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Forms/FormVitalsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get_vitals_column\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Forms/FormVitalsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method dispatch\\(\\) on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setExternalCDRController\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getStatusCode\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ExternalClinicalDecisionSupport/RouteControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getSinglePatientFixture\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getUnregisteredUuid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method installSinglePatientFixture\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isExistingUuid\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method removePatientFixtures\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method validate\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Validators/PatientValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addData\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addInternalError\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getData\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getInternalErrors\\(\\) on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getValidationMessages\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasErrors\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method hasInternalErrors\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method isValid\\(\\) on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method setValidationMessages\\(\\) on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on Psr\\\\Http\\\\Message\\\\ResponseInterface\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method get\\(\\) on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getAttribute\\(\\) on Psr\\\\Http\\\\Message\\\\ResponseInterface\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method getEventDispatcher\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addListener\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/RestApiEventHookExample/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addToFHIRRouteMap\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/RestApiEventHookExample/Module.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot call method addToRouteMap\\(\\) on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/RestApiEventHookExample/Module.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
