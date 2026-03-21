<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertSame\\(\\) with array\\{\'a\', \'b\', \'c\'\\} and array\\<string, mixed\\>\\|null will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/BC/DatabaseTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
