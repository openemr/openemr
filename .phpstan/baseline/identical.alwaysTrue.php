<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between \'\' and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between 0 and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between \'true\' and \'true\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between \'\' and \'\' will always evaluate to true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between true and true will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between \'object\' and \'object\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\=\\=\\= between \'us_core_v311\' and \'us_core_v311\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/US_Core_311/InfernoSinglePatientAPITest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
