<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use getBoolean\\(\'translate_appt_categories\'\\) instead of get\\(\'translate_appt_categories\'\\) for boolean globals\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PostCalendar/PostCalendarRenderer.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
