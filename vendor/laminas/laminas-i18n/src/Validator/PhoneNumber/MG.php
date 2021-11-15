<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '261',
    'patterns' => [
        'national' => [
            'general' => '/^[23]\\d{8}$/',
            'fixed' => '/^2(?:0(?:(?:2\\d|4[47]|5[3467]|6[279]|8[268]|9[245])\\d|7(?:2[29]|[35]\\d))|210\\d)\\d{4}$/',
            'mobile' => '/^3[02-4]\\d{7}$/',
            'emergency' => '/^11?[78]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
