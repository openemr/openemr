<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '960',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[3467]\\d{6}|9(?:00\\d{7}|\\d{6}))$/',
            'fixed' => '/^(?:3(?:0[01]|3[0-59])|6(?:[567][02468]|8[024689]|90))\\d{4}$/',
            'mobile' => '/^(?:46[46]|7[3-9]\\d|9[6-9]\\d)\\d{4}$/',
            'pager' => '/^781\\d{4}$/',
            'premium' => '/^900\\d{7}$/',
            'shortcode' => '/^1(?:[19]0|23)$/',
            'emergency' => '/^1(?:02|19)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,10}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'pager' => '/^\\d{7}$/',
            'premium' => '/^\\d{10}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
