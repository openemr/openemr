<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '235',
    'patterns' => [
        'national' => [
            'general' => '/^[2679]\\d{7}$/',
            'fixed' => '/^22(?:[3789]0|5[0-5]|6[89])\\d{4}$/',
            'mobile' => '/^(?:6[36]\\d|77\\d|9(?:5[0-4]|9\\d))\\d{5}$/',
            'emergency' => '/^1[78]$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2}$/',
        ],
    ],
];
