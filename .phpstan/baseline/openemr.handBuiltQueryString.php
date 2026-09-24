<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "rxcuis" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "qrda_fname" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../index.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "key" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/new_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "payment_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "payment_id" is concatenated by hand in a template \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_patient_note.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "drug" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "drug" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "lot" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "drug" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/destroy_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "lot" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/destroy_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounter" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formOrigin" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "isPortal" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patientid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "visitid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/aftercare_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/ankleinjury/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/bronchitis/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/dictation/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/SpectacleRx.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "codetype" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounter" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "providerID" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounter" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "blahblah" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounter" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "providerID" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "uniqueID" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/code_choice/initialize_code_choice.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/contraception_products/initialize_contraception_products.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "addmore" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patientid" is concatenated by hand in a template \\(urlencode\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ptid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/initialize_review.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/views/review.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "mode" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/phq9/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "lineid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/edit_diagnoses.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/physical_exam/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formOrigin" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "mode" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "text" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/reviewofs/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formOrigin" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_encounter" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/track_anything/history.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/transfer_summary/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/treatment_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "name" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms_admin/forms_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/csv/load_csv_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_constant.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_definition.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_language.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_manage.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/language/language.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/erx_logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "filename" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/erx_logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "eid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "catid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "providerid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_appt_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand \\(dynamic key\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\]" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/display_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/display_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "itemized_test_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "numerator_label" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pass_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "report_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "report_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/patient_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pc_username\\[\\]" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pc_username\\[\\]" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/main_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "viewtype" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/main_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "date" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_encounterid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/lab_results_messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "begin" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "begin" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "noteid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "show_all" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "showall" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "showall" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortby" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortby" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortorder" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortorder" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/messages/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "active" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "active" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset" is concatenated by hand \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/onotes/office_comments_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "redirect" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index-portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/public/index-portal.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/primary_config_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "npi" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/primary_config_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/route_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "labGuid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/public/route_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "client_secret" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ConnectorApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "scope" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ConnectorApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand \\(dynamic key\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/setup_email.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "audit_render_id" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "auto_render_id" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "auto_render_name" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "data" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "data" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "data" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/rxlogmanager.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/weno_users.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "docId" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Listener/CCDAEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFilter/acl/acl_setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "browsenum" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_patient_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "addfav" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formseq" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/find_order_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "orderid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/order_manifest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "batch" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "review" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/orders_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ppid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/procedure_provider_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "orderid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formseq" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "order" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "popup" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "typeid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/types_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "billing" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounterid" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "issue" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "payment" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "transaction" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/deleter.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "mainSearchCriteria\\.v\\.c" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/coding.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/coding.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "type" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/coding.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/copay.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/diagnosis_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/encounter_top.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "target_element" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "codetype" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "target_element" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "encounter" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patientid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "visitid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/load_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/other.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "type" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/search_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fee" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "modifier" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "text" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "type" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "units" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/superbill_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/view_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "payid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "payment_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "feid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/edit_billnote.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "billing" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "doc_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "issue" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "issue" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pagesize" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pagestart" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\]" is concatenated by hand \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ptid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ptid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_normal.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fill" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "begin" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "mode" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "mode" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortby" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortby" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sortorder" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/reminder/patient_reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "category" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "entryID" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "item" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/rules/patient_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "default" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "visitid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/advancedirectives.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/advancedirectives.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "browsenum" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "browsenum" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_number\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "plan_name\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "policy_number\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "provider\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 20,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_pid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_DOB\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_city\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_country\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer_city\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer_country\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer_postal_code\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer_state\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_employer_street\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_fname\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_lname\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_mname\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_phone\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_postal_code\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_sex\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_ss\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_state\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "subscriber_street\\.value" is concatenated by hand in a template \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/browse.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "user_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "active" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "editlid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "leftrecords" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/disclosure_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_encounter" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "set_encounter" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formname" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/lbf_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/list_amendments.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "orderid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "docid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_active" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_doc_only" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_inactive" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "noteid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "noteid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset_sent" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "orderid" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "orderid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_active" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_doc_only" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_inactive" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "noteid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "offset" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "formid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/track_anything_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "transid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/add_transaction.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "title" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "transid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/transaction/transactions.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "tracker_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "codetype" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/clinical_reports.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "product" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/inventory_activity.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "t" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "t" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "sale_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/prescriptions_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "codetype" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "report_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/reports/report_results.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "enc" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ptid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/sales_by_item.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "form_inactive" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_layout_props.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\]" is concatenated by hand \\(js_escape\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "userid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_edit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fac_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "user_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/facility_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "method" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ESign/Abstract/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "module" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ESign/Abstract/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "r" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "token" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand \\(dynamic key\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "action" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "rev" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "category" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "item" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "list_id" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/custom_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/delete_category.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "list_id" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/custom_template/personalize.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "type" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ftype" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "bht03" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 60,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "err" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "errseg" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "fname" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 49,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ftype" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 47,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ftype" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "icn" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "rsptype" is concatenated by hand \\(attr_url\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "rsptype" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trace" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "tracecheck" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_data.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "contextName" is concatenated by hand \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "thisenc" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/spreadsheet.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/validation/validation_script.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "eid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "catid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "providerid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/find_appt_popup_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "redirect" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/lib/patient_groups.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/messaging/messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteActivityViewListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "referer_flag" is concatenated by hand \\(attr_url\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsitePortalActivityListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/PatientListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../portal/patient/templates/ProviderHome.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/_FormsHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/templates/_FormsHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/_Header.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/templates/_Header.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../portal/patient/templates/_modalFormHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/templates/_modalFormHeader.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(rawurlencode\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "site" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "cancel" is concatenated by hand in a template \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token" is concatenated by hand in a template \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "front" is concatenated by hand in a template \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id_cc" is concatenated by hand in a template \\(htmlspecialchars\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/initial_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/AbstractGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "delete" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/AbstractGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "location" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/AbstractGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "partner" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token_form" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "message" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "pid" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "rule_id" is concatenated by hand \\(urlencode\\(\\), untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "_count" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Database/QueryPagination.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "_offset" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Database/QueryPagination.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidMapping.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "redirect" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Portal/PatientPortalLoginController.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand \\(dynamic key\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ActionUrlBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "\\(dynamic\\)" is concatenated by hand \\(dynamic key\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "name" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "value" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "aggregators" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxcustid" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxcustid_licensekey" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxcustomfield\\[1\\]" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxcustomfield\\[2\\]" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxcustomfield\\[3\\]" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "trxoperator" is concatenated by hand \\(urlencode\\(\\), typed\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "Date" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PostCalendar/ViewModel/CalendarRenderDataBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Subscriber/UuidMappingEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "t" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ip" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/basic.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/base/template/criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand \\(unencoded, untyped\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/browse/plans_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "guid" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/detail/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/action.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "criteriaType" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/add_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "group_id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/add_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/add_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "type" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/add_criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "v" is concatenated by hand in a template \\(unencoded\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/diagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/intervals.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand in a template \\(attr_url\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/summary.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "action" is concatenated by hand \\(rawurlencode\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Support/OAuth2/AuthCodeFlow.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "csrf_token" is concatenated by hand \\(rawurlencode\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Support/OAuth2/AuthCodeFlow.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/AppointmentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/AppointmentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/AppointmentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/BbCreateStaffAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/BbCreateStaffAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/BbCreateStaffAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DdOpenPatientAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DdOpenPatientAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DdOpenPatientAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DocumentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DocumentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/DocumentPersistenceAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/FfOpenEncounterAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/FfOpenEncounterAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/FfOpenEncounterAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "document_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/KkEncounterFormNavbarUrlAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "parent_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/KkEncounterFormNavbarUrlAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient_id" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Acceptance/Ui/KkEncounterFormNavbarUrlAcceptanceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "state" is concatenated by hand \\(rawurlencode\\(\\)\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationLogoutFullFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "patient" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "test_suite_id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/US_Core_311/InfernoSinglePatientAPITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "console" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "category" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/FHIR/SMART/ResourceConstraintFiltererTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "id" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "code" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Services/FHIR/Subscriber/UuidMappingEventsSubscriberTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "ref" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tools/release/src/BinaryForgeResolver.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "milestone" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tools/release/src/GitHubApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "page" is concatenated by hand \\(unencoded int\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tools/release/src/GitHubApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "state" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tools/release/src/GitHubApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Query parameter "q" is concatenated by hand \\(unencoded string\\)\\. Build the query with QueryString\\:\\:build\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tools/release/src/PreflightChecker.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
