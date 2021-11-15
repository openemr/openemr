<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '58',
    'patterns' => [
        'national' => [
            'general' => '/^[24589]\\d{9}$/',
            'fixed' => '/^(?:2(?:12|3[457-9]|[58][1-9]|[467]\\d|9[1-6])|50[01])\\d{7}$/',
            'mobile' => '/^4(?:1[24-8]|2[46])\\d{7}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^900\\d{7}$/',
            'emergency' => '/^171$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,10}$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
