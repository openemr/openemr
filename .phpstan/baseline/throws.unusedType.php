<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\Runner\\\\CommandRunner\\:\\:findCommands\\(\\) has ReflectionException in PHPDoc @throws tag but it\'s not thrown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Runner/CommandRunner.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
