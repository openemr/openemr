<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
