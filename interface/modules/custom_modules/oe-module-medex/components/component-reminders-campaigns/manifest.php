<?php

declare(strict_types=1);

return [
    'key' => 'component-reminders-campaigns',
    'aliases' => ['appointment_reminders', 'gogreen', 'surveys', 'recalls', 'announcements', 'group_messaging'],
    'title' => 'Messaging Campaigns',
    'description' => 'Messaging campaign tools, SMS bot pages, and recall workflows for subscribed practices.',
    'menus' => [
        [
            'menu_id' => 'medex_sms_bot',
            'label' => 'SMS Bot',
            'path' => '/interface/modules/custom_modules/oe-module-medex/public/sms_bot.php',
            'locations' => ['top'],
            'acl' => ['admin', 'super'],
        ],
        [
            'menu_id' => 'medex_recall_board',
            'label' => 'Recall Board',
            'path' => '/interface/modules/custom_modules/oe-module-medex/public/recall_board.php',
            'locations' => ['top'],
            'acl' => ['admin', 'super'],
        ],
    ],
    'help' => [
        'title' => 'Messaging Campaigns',
        'summary' => 'Reminder, recall, survey, and GoGreen campaign tools are delivered as a single optional module component.',
        'points' => [
            'The stripped module includes these pages only when the matching messaging subscription is enabled.',
            'SMS Bot and Recall Board menu entries are injected only for subscribed practices.',
        ],
    ],
];
