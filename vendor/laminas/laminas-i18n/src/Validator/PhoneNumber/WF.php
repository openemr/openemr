<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '681',
    'patterns' => [
        'national' => [
            'general' => '/^[5-7]\\d{5}$/',
            'fixed' => '/^(?:50|68|72)\\d{4}$/',
            'mobile' => '/^(?:50|68|72)\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
