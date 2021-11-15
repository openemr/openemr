<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '255',
    'patterns' => [
        'national' => [
            'general' => '/^\\d{9}$/',
            'fixed' => '/^2[2-8]\\d{7}$/',
            'mobile' => '/^(?:6[158]|7[1-9])\\d{7}$/',
            'tollfree' => '/^80[08]\\d{6}$/',
            'premium' => '/^90\\d{7}$/',
            'shared' => '/^8(?:40|6[01])\\d{6}$/',
            'voip' => '/^41\\d{7}$/',
            'emergency' => '/^(?:11[12]|999)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'shared' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
