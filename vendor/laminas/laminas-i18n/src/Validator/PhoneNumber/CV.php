<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '238',
    'patterns' => [
        'national' => [
            'general' => '/^[259]\\d{6}$/',
            'fixed' => '/^2(?:2[1-7]|3[0-8]|4[12]|5[1256]|6\\d|7[1-3]|8[1-5])\\d{4}$/',
            'mobile' => '/^(?:9\\d|59)\\d{5}$/',
            'emergency' => '/^13[012]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
