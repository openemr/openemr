<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '57',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[13]\\d{0,3}|[24-8])\\d{7}$/',
            'fixed' => '/^[124-8][2-9]\\d{6}$/',
            'mobile' => '/^3(?:0[0-24]|1\\d|2[01])\\d{7}$/',
            'tollfree' => '/^1800\\d{7}$/',
            'premium' => '/^19(?:0[01]|4[78])\\d{7}$/',
            'emergency' => '/^1(?:1[29]|23|32|56)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,11}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{11}$/',
            'premium' => '/^\\d{11}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
