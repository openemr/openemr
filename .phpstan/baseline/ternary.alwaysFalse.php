<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/DomainModels/OpenEMRFhirQuestionnaireResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Ternary operator condition is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
