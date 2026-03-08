<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/encounters_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
