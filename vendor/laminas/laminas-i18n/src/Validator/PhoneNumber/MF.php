<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '590',
    'patterns' => [
        'national' => [
            'general' => '/^[56]\\d{8}$/',
            'fixed' => '/^590(?:10|2[79]|5[128]|[78]7)\\d{4}$/',
            'mobile' => '/^690(?:10|2[27]|66|77|8[78])\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
