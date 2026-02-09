<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class User referenced with incorrect case\\: user\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
