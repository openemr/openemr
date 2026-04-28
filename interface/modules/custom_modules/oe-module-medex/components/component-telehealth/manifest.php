<?php

declare(strict_types=1);

return [
    'key' => 'component-telehealth',
    'aliases' => ['telemedex'],
    'title' => 'Telehealth',
    'description' => 'Telehealth visit tools and modality support.',
    'menus' => [
        [
            'menu_id' => 'medex_telehealth',
            'label' => 'Telehealth',
            'path' => '/interface/modules/custom_modules/oe-module-medex/public/telehealth.php',
            'locations' => ['top'],
            'acl' => ['admin', 'super'],
        ],
    ],
];
