<?php

declare(strict_types=1);

return [
    'key' => 'component-openemr-ui-hooks',
    'aliases' => ['appointment_reminders', 'gogreen', 'surveys', 'recalls', 'announcements', 'group_messaging'],
    'title' => 'OpenEMR UI Hooks',
    'description' => 'Shared OpenEMR page hooks used by messaging-family MedEx services.',
    'help' => [
        'title' => 'OpenEMR UI Hooks',
        'summary' => 'This shared component carries the page hooks MedEx uses inside OpenEMR for messaging and flow-board integrations.',
        'points' => [
            'This component is bundled only with services that need OpenEMR page hooks.',
            'Removing the related subscription removes these hooks from the stripped module build.',
        ],
    ],
];
