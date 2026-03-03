<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_options.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Measure.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
