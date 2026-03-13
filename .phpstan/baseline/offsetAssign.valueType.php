<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Symfony\\\\Component\\\\Panther\\\\DomCrawler\\\\Form does not accept int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Symfony\\\\Component\\\\Panther\\\\DomCrawler\\\\Form does not accept int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Symfony\\\\Component\\\\Panther\\\\DomCrawler\\\\Form does not accept int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
