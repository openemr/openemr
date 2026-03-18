<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot clone non\\-object variable \\$start_tmp of type DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/EncounterService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
