<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertGreaterThan\\(\\) with \'20251229\' and \'20260310\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Release/BinaryForgePinTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
