<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property eRxPage\\:\\:\\$prescriptionIds is never written, only read\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Bootstrap\\:\\:\\$twig is never written, only read\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property MedExApi\\\\MedEx\\:\\:\\$cookie is never written, only read\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Property GlobalConfig\\:\\:\\$context is never written, only read\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
