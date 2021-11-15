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
            'fixed' => '/^590(?:1[12]|2[0-68]|3[28]|4[126-8]|5[067]|6[018]|[89]\\d)\\d{4}$/',
            'mobile' => '/^690(?:00|1[1-9]|2[013-5]|[3-5]\\d|6[0-57-9]|7[1-6]|8[0-6]|9[09])\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
