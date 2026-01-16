<?php declare(strict_types = 1);

// total 7 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
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
