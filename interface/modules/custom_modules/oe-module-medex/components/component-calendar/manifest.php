<?php

declare(strict_types=1);

return [
    'key' => 'component-calendar',
    'aliases' => ['calendar_ai', 'calendar_service', 'calendar_services', 'calendar_export', 'calendar_full'],
    'title' => 'Calendar Services',
    'description' => 'Calendar views, calendar feeds, and scheduling extensions for subscribed practices.',
    'bootstrap' => ['bootstrap.php'],
    'bootstrap_always' => true,
    'user_settings' => [
        [
            'key' => 'medex_use_full_calendar',
            'label' => 'Use MedEx Full Calendar',
            'section' => 'MedEx Calendar',
            'default' => '1',
            'description' => 'When enabled, subscribed users open the MedEx Full Calendar instead of the native OpenEMR calendar view.',
        ],
    ],
    'help' => [
        'title' => 'Calendar Services',
        'summary' => 'Calendar Services adds the MedEx scheduler, feed tools, and Full Calendar experience when that subscription is enabled.',
        'points' => [
            'Calendar Services and Full Calendar are loaded only when the matching subscription is active.',
            'Users can still opt out of Full Calendar with their personal MedEx calendar preference.',
        ],
    ],
];
