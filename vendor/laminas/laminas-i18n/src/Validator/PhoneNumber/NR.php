<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '674',
    'patterns' => [
        'national' => [
            'general' => '/^[458]\\d{6}$/',
            'fixed' => '/^(?:444|888)\\d{4}$/',
            'mobile' => '/^55[5-9]\\d{4}$/',
            'shortcode' => '/^1(?:23|92)$/',
            'emergency' => '/^11[0-2]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
