<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/stats.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between used and 0 results in an error\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SmartLaunchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/BillingViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Patient/Cards/DemographicsViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/PortalCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between Effective and 0 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
