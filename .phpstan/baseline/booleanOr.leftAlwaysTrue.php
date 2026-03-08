<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/front_receipts_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/addrbook_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
