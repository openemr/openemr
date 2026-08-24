<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type object is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type OpenEMR\\\\Menu\\\\InvalidArgumentException is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
