<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^@covers value \\:\\:put with a valid resource uuid and payload references an invalid method\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/tests/Tests/Integration/VitalsApiIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
