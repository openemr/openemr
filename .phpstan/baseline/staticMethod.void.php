<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:commitTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/BackgroundTaskManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:rollbackTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/BackgroundTaskManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:startTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/BackgroundTaskManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:commitTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:rollbackTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:startTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:commitTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:rollbackTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:startTransaction\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
