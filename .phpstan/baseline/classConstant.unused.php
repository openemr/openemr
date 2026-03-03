<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:AUTH_RATE_LIMIT is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:SUPPORTED_SEARCH_FIELDS is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\RestControllers\\\\TransactionRestController\\:\\:SUPPORTED_SEARCH_FIELDS is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\CareTeamService\\:\\:PATIENT_HISTORY_TABLE is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\PatientService\\:\\:PATIENT_HISTORY_TABLE is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:COMPARATOR_INDEX_FULL is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:COMPARATOR_INDEX_TIMEZONE_HOUR is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:COMPARATOR_INDEX_TIMEZONE_MINUTE is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Constant OpenEMR\\\\Services\\\\Search\\\\DateSearchField\\:\\:COMPARATOR_INDEX_TIMEZONE_SIGN is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
