<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/templates/linked_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/orders/procedure_stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/ippf_statistics.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/receipts_by_method_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/layout_service_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Empty array passed to foreach\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
