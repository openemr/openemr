<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/zutil.cli.doc_import.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaUserPreferencesTransformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/patient_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/unique_seen_patients_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/FeeSheet.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formatting.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/global_functions.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
