<?php

declare(strict_types=1);

return [
    'key' => 'component-secure-chat',
    'aliases' => ['secure_chat'],
    'title' => 'Secure Chat',
    'description' => 'Patient and staff secure chat pages.',
    'menus' => [
        [
            'menu_id' => 'medex_secure_chat',
            'label' => 'Secure Chat',
            'path' => '/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php',
            'locations' => ['top'],
            'acl' => ['admin', 'super'],
        ],
    ],
];
