<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Constructor of class OpenEMR\\\\Modules\\\\DiscoverAndChange\\\\Assessments\\\\Services\\\\TaskFHIRResourceService has an unused parameter \\$portalQuestionnaireResourceService\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/src/Services/TaskFHIRResourceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Constructor of class OpenEMR\\\\Modules\\\\IHI\\\\Bootstrap has an unused parameter \\$kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/src/Bootstrap.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
