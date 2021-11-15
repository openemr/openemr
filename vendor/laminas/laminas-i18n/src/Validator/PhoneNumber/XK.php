<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '383',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[23][89]|4[3-79])\\d{6}$/',
            'fixed' => '/^[23][89]\\d{6}$/',
            'mobile' => '/^4[3-79]\\d{6}$/',
            'tollfree' => '/^800\\d{3,9}$/',
            'premium' => '/^(?:90[0169]|78\\d)\\d{3,7}$/',
            'uan' => '/^7[06]\\d{4,10}$/',
            'shortcode' => '/^(?:1(?:1(?:[013-9]|\\d(2,4))|[89]\\d{1,4}))$/',
            'emergency' => '/^(?:112|19[234])$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{6,12}$/',
            'premium' => '/^\\d{6,12}$/',
            'uan' => '/^\\d{6,12}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
