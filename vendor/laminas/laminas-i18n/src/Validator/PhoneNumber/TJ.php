<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '992',
    'patterns' => [
        'national' => [
            'general' => '/^[3-59]\\d{8}$/',
            'fixed' => '/^(?:3(?:1[3-5]|2[245]|3[12]|4[24-7]|5[25]|72)|4(?:46|74|87))\\d{6}$/',
            'mobile' => '/^(?:505|9[0-35-9]\\d)\\d{6}$/',
            'emergency' => '/^1(?:0[1-3]|12)$/',
        ],
        'possible' => [
            'general' => '/^\\d{3,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
