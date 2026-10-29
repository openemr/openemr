<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'drive_encryption\'\\) instead of get\\(\'drive_encryption\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Common/OpenEmrCryptoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use getInt\\(\'phone_country_code\'\\) instead of get\\(\'phone_country_code\'\\) for integer globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/public/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'set_service_facility_encounter\'\\) instead of get\\(\'set_service_facility_encounter\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Use getInt\\(\'phone_country_code\'\\) instead of get\\(\'phone_country_code\'\\) for integer globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/public/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'set_service_facility_encounter\'\\) instead of get\\(\'set_service_facility_encounter\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/src/Bootstrap.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
