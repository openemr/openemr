<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined static method OpenEMR\\\\Common\\\\Database\\\\QueryUtils\\:\\:fetchSingleRow\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuser.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
