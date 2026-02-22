<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Do\\-while loop condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Cqm/reports/NQF_0421/Numerator1.php',
];
$ignoreErrors[] = [
    'message' => '#^Do\\-while loop condition is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Cqm/reports/NQF_0421/Numerator2.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
