<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'OpenEMR\\\\\\\\Modules\\\\\\\\FaxSMS\\\\\\\\RestClient\\\\\\\\SignalWire\\\\\\\\Rest\\\\\\\\FaxInstance\' and OpenEMR\\\\Modules\\\\FaxSMS\\\\RestClient\\\\SignalWire\\\\Rest\\\\FaxInstance will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Modules/FaxSMS/Controller/SignalWireRestClientTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
