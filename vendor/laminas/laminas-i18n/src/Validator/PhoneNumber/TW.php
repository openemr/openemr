<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '886',
    'patterns' => [
        'national' => [
            'general' => '/^[2-9]\\d{7,8}$/',
            'fixed' => '/^[2-8]\\d{7,8}$/',
            'mobile' => '/^9\\d{8}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^900\\d{6}$/',
            'emergency' => '/^11[029]$/',
        ],
        'possible' => [
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
