<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
