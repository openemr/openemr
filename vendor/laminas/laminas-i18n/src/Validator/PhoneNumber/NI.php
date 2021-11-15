<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '505',
    'patterns' => [
        'national' => [
            'general' => '/^[128]\\d{7}$/',
            'fixed' => '/^2\\d{7}$/',
            'mobile' => '/^[578]\\d{7}$/',
            'tollfree' => '/^1800\\d{4}$/',
            'emergency' => '/^118$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
