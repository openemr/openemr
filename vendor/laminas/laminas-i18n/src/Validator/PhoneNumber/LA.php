<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '856',
    'patterns' => [
        'national' => [
            'general' => '/^[2-8]\\d{7,9}$/',
            'fixed' => '/^(?:2[13]|[35-7][14]|41|8[1468])\\d{6}$/',
            'mobile' => '/^20(?:2[2389]|5[4-689]|7[6-8]|9[57-9])\\d{6}$/',
            'emergency' => '/^19[015]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
