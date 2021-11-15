<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '246',
    'patterns' => [
        'national' => [
            'general' => '/^3\\d{6}$/',
            'fixed' => '/^37\\d{5}$/',
            'mobile' => '/^38\\d{5}$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
        ],
    ],
];
