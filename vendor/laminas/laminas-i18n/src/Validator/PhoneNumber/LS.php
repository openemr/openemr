<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '266',
    'patterns' => [
        'national' => [
            'general' => '/^[2568]\\d{7}$/',
            'fixed' => '/^2\\d{7}$/',
            'mobile' => '/^[56]\\d{7}$/',
            'tollfree' => '/^800[256]\\d{4}$/',
            'emergency' => '/^11[257]$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
