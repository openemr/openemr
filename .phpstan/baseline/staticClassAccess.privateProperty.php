<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private property OpenEMR\\\\Common\\\\Logging\\\\EventAuditLogger\\:\\:\\$instances through static\\:\\:\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private property OpenEMR\\\\Common\\\\Session\\\\SessionWrapperFactory\\:\\:\\$instances through static\\:\\:\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Session/SessionWrapperFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Unsafe access to private property OpenEMR\\\\Tests\\\\Isolated\\\\Core\\\\Traits\\\\SingletonA\\:\\:\\$instances through static\\:\\:\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/Traits/SingletonTraitTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
