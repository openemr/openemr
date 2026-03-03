<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/prior_auth/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
