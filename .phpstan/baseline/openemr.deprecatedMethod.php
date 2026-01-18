<?php declare(strict_types = 1);

// total 7 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^GenID\\(\\) is deprecated\\. Use QueryUtils\\:\\:generateId\\(\\) or QueryUtils\\:\\:ediGenerateId\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^GenID\\(\\) is deprecated\\. Use QueryUtils\\:\\:generateId\\(\\) or QueryUtils\\:\\:ediGenerateId\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^GenID\\(\\) is deprecated\\. Use QueryUtils\\:\\:generateId\\(\\) or QueryUtils\\:\\:ediGenerateId\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
