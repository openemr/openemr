<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^You should use assertFalse\\(\\) instead of assertSame\\(\\) when expecting "false"$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Unit/Controllers/LoadCompendiumControllerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
