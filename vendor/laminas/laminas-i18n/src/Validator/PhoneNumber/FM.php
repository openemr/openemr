<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '691',
    'patterns' => [
        'national' => [
            'general' => '/^[39]\\d{6}$/',
            'fixed' => '/^(?:3[2357]0[1-9]\\d{3}|9[2-6]\\d{5})$/',
            'mobile' => '/^(?:3[2357]0[1-9]\\d{3}|9[2-7]\\d{5})$/',
            'emergency' => '/^(?:911|320221)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}(?:\\d{3})?$/',
        ],
    ],
];
