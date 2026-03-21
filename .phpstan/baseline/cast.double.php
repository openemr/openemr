<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/procedure_order_save_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/insurance_allocation_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot cast mixed to float\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
