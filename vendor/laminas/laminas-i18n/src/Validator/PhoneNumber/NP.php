<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '977',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[1-8]\\d{7}|9(?:[1-69]\\d{6}|7[2-6]\\d{5,7}|8\\d{8}))$/',
            'fixed' => '/^(?:1[0124-6]|2[13-79]|3[135-8]|4[146-9]|5[135-7]|6[13-9]|7[15-9]|8[1-46-9]|9[1-79])\\d{6}$/',
            'mobile' => '/^9(?:7[45]|8[0145])\\d{7}$/',
            'emergency' => '/^1(?:0[0-3]|12)$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
