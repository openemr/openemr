<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '598',
    'patterns' => [
        'national' => [
            'general' => '/^[2489]\\d{6,7}$/',
            'fixed' => '/^(?:2\\d{7}|4[2-7]\\d{6})$/',
            'mobile' => '/^9[13-9]\\d{6}$/',
            'tollfree' => '/^80[05]\\d{4}$/',
            'premium' => '/^90[0-8]\\d{4}$/',
            'shortcode' => '/^1(?:0[4-9]|1[2368]|2[0-3568])$/',
            'emergency' => '/^(?:128|911)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{7}$/',
            'premium' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
