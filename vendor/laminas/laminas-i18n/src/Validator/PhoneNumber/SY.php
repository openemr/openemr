<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '963',
    'patterns' => [
        'national' => [
            'general' => '/^[1-59]\\d{7,8}$/',
            'fixed' => '/^(?:1(?:1\\d?|4\\d|[2356])|2[1-35]|3(?:[13]\\d|4)|4[13]|5[1-3])\\d{6}$/',
            'mobile' => '/^9(?:22|[35][0-8]|4\\d|6[024-9]|88|9[0-489])\\d{6}$/',
            'emergency' => '/^11[023]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
