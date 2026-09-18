<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Variable \\$value in PHPDoc tag @var does not match any variable in the foreach loop\\: \\$values, \\$comparableValue$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldStatementResolver.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
