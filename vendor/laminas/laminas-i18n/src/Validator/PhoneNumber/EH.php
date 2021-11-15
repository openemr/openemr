<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '212',
    'patterns' => [
        'national' => [
            'general' => '/^[5689]\\d{8}$/',
            'fixed' => '/^528[89]\\d{5}$/',
            'mobile' => '/^6(?:0[0-6]|[14-7]\\d|2[2-46-9]|3[03-8]|8[01]|99)\\d{6}$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^89\\d{7}$/',
            'emergency' => '/^1(?:[59]|77)$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
