<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '596',
    'patterns' => [
        'national' => [
            'general' => '/^[56]\\d{8}$/',
            'fixed' => '/^596(?:0[2-5]|[12]0|3[05-9]|4[024-8]|[5-7]\\d|89|9[4-8])\\d{4}$/',
            'mobile' => '/^696(?:[0-479]\\d|5[01]|8[0-689])\\d{4}$/',
            'emergency' => '/^1(?:12|[578])$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
