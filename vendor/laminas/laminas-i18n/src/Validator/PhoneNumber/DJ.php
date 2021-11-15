<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '253',
    'patterns' => [
        'national' => [
            'general' => '/^[27]\\d{7}$/',
            'fixed' => '/^2(?:1[2-5]|7[45])\\d{5}$/',
            'mobile' => '/^77[6-8]\\d{5}$/',
            'emergency' => '/^1[78]$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
