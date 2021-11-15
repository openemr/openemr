<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '508',
    'patterns' => [
        'national' => [
            'general' => '/^[45]\\d{5}$/',
            'fixed' => '/^41\\d{4}$/',
            'mobile' => '/^55\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
