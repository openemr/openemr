<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between id and 0 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\=\\=" between used and 0 results in an error\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
