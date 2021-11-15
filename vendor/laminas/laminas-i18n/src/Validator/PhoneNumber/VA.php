<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '379',
    'patterns' => [
        'national' => [
            'general' => '/^06\\d{8}$/',
            'fixed' => '/^06698\\d{5}$/',
            'mobile' => '/^N/A$/',
            'emergency' => '/^11[2358]$/',
        ],
        'possible' => [
            'general' => '/^\\d{10}$/',
            'mobile' => '/^N/A$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
