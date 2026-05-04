<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Use of constant DATE_ISO8601 is deprecated\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 37,
    'path' => __DIR__ . '/../../library/edihistory/edih_io.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAlerts.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Csrf/CsrfUtils.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
