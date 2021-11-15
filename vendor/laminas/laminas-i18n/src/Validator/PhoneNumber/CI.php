<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '225',
    'patterns' => [
        'national' => [
            'general' => '/^[02-6]\\d{7}$/',
            'fixed' => '/^(?:2(?:0[023]|1[02357]|[23][045]|4[03-5])|3(?:0[06]|1[069]|[2-4][07]|5[09]|6[08]))\\d{5}$/',
            'mobile' => '/^(?:0[1-9]|4[0-24-9]|5[057-9]|6[05679])\\d{6}$/',
            'emergency' => '/^1(?:1[01]|[78]0)$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
