<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/plugins/function.pc_filter.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
