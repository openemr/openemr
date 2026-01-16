<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:escapeCollateName\\(\\) throws exception Throwable but the PHPDoc contains @throws void\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\:\\:escapeDatabaseName\\(\\) throws exception Throwable but the PHPDoc contains @throws void\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
