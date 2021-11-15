<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '966',
    'patterns' => [
        'national' => [
            'general' => '/^(?:(?:[1-467]|92)\\d{7}|5\\d{8}|8\\d{9})$/',
            'fixed' => '/^(?:[12][24-8]|3[35-8]|4[3-68]|6[2-5]|7[235-7])\\d{6}$/',
            'mobile' => '/^(?:5[013-689]\\d|8111)\\d{6}$/',
            'tollfree' => '/^800\\d{7}$/',
            'uan' => '/^9200\\d{5}$/',
            'shortcode' => '/^9(0[24-79]|33|40|66|8[59]|9[02-6])$/',
            'emergency' => '/^99[7-9]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,10}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{9,10}$/',
            'tollfree' => '/^\\d{10}$/',
            'uan' => '/^\\d{9}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
