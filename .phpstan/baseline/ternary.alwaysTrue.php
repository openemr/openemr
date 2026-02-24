<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogImportBuild.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/telecom_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/OnsiteDocumentListView.tpl.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../templates/super/rules/controllers/edit/intervals.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
