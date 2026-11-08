<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<\\=" between \'1\'\\|\'2\'\\|\'3\'\\|\'4\'\\|\'5\'\\|\'6\'\\|\'7\' and 7 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/amc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
