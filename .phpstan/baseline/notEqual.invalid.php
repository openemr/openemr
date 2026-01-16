<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\!\\=" between int and \\*NEVER\\* results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\!\\=" between prepared and 1 results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
