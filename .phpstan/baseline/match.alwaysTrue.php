<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Match arm comparison between \'day\' and \'day\' is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
