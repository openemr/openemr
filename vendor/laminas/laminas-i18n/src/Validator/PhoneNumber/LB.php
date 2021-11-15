<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '961',
    'patterns' => [
        'national' => [
            'general' => '/^[13-9]\\d{6,7}$/',
            'fixed' => '/^(?:[14-6]\\d{2}|7(?:[2-579]\\d|62|8[0-7])|[89][2-9]\\d)\\d{4}$/',
            'mobile' => '/^(?:3\\d|7(?:[01]\\d|6[013-9]|8[89]|91))\\d{5}$/',
            'premium' => '/^9[01]\\d{6}$/',
            'shared' => '/^8[01]\\d{6}$/',
            'emergency' => '/^(?:1(?:12|40|75)|999)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,8}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7,8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
