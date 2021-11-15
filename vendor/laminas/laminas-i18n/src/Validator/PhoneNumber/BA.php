<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '387',
    'patterns' => [
        'national' => [
            'general' => '/^[3-9]\\d{7,8}$/',
            'fixed' => '/^(?:[35]\\d|49)\\d{6}$/',
            'mobile' => '/^6(?:03|44|71|[1-356])\\d{6}$/',
            'tollfree' => '/^8[08]\\d{6}$/',
            'premium' => '/^9[0246]\\d{6}$/',
            'shared' => '/^8[12]\\d{6}$/',
            'uan' => '/^70[23]\\d{5}$/',
            'emergency' => '/^12[234]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,9}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'uan' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
