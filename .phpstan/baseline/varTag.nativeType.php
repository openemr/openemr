<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type EventDispatcherInterface is not subtype of native type Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/search_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type int\\|null is not subtype of native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/templates/procedure_specimen_row.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type string\\|false is not subtype of native type non\\-empty\\-array\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type EventDispatcherInterface is not subtype of native type Symfony\\\\Component\\\\EventDispatcher\\\\EventDispatcherInterface\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type OpenEMR\\\\Services\\\\Qdm\\\\PopulationSet is not subtype of native type array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/ResultsCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type OpenEMR\\\\Telemetry\\\\TelemetryRepository\\|OpenEMR\\\\Tests\\\\Isolated\\\\Telemetry\\\\MockObject is not subtype of native type PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
