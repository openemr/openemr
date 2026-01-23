<?php declare(strict_types = 1);

// total 5 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method fetchAssociative\\(\\) on class Doctrine\\\\DBAL\\\\Result\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/BC/DatabaseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method executeQuery\\(\\) on class Doctrine\\\\DBAL\\\\Connection\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/BC/DatabaseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method executeStatement\\(\\) on class Doctrine\\\\DBAL\\\\Connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/BC/DatabaseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method lastInsertId\\(\\) on class Doctrine\\\\DBAL\\\\Connection\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/BC/DatabaseTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
