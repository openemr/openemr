<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot clone non\\-object variable \\$start_tmp of type DateTime\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/EncounterService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
