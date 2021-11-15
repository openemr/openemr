<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '36',
    'patterns' => [
        'national' => [
            'general' => '/^[1-9]\\d{7,8}$/',
            'fixed' => '/^(?:1\\d|2(?:1\\d|[2-9])|3[2-7]|4[24-9]|5[2-79]|6[23689]|7(?:1\\d|[2-9])|8[2-57-9]|9[2-69])\\d{6}$/',
            'mobile' => '/^(?:[257]0|3[01])\\d{7}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^9[01]\\d{6}$/',
            'shared' => '/^40\\d{6}$/',
            'emergency' => '/^1(?:0[457]|12)$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
