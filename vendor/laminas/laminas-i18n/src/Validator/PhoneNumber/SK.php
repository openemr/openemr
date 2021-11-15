<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '421',
    'patterns' => [
        'national' => [
            'general' => '/^[2-689]\\d{8}$/',
            'fixed' => '/^[2-5]\\d{8}$/',
            'mobile' => '/^9(?:0[1-8]|1[0-24-9]|4[0489])\\d{6}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^9(?:[78]\\d{7}|00\\d{6})$/',
            'shared' => '/^8[5-9]\\d{7}$/',
            'voip' => '/^6(?:5[0-4]|9[0-6])\\d{6}$/',
            'uan' => '/^96\\d{7}$/',
            'emergency' => '/^1(?:12|5[058])$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'uan' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
