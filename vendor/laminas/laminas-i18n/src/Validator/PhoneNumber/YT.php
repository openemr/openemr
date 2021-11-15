<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '262',
    'patterns' => [
        'national' => [
            'general' => '/^[268]\\d{8}$/',
            'fixed' => '/^2696[0-4]\\d{4}$/',
            'mobile' => '/^639\\d{6}$/',
            'tollfree' => '/^80\\d{7}$/',
            'emergency' => '/^1(?:12|5)$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
