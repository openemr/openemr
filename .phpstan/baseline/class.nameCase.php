<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class User referenced with incorrect case\\: user\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
