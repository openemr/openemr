<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsArray\\(\\) with array\\<int, array\\<string, int\\|string\\|null\\>\\> will always evaluate to true\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsArray\\(\\) with array\\<int, string\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsArray\\(\\) with array\\<string, int\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsArray\\(\\) with array\\<string, int\\|string\\|null\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsInt\\(\\) with int will always evaluate to true\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsString\\(\\) with string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Email/EmailQueueServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
