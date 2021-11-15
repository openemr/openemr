<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '686',
    'patterns' => [
        'national' => [
            'general' => '/^[2-689]\\d{4}$/',
            'fixed' => '/^(?:[234]\\d|50|8[1-5])\\d{3}$/',
            'mobile' => '/^(?:6\\d{4}|9(?:[0-8]\\d|9[015-8])\\d{2})$/',
            'shortcode' => '/^10(?:[0-8]|5[01259])$/',
            'emergency' => '/^99[2349]$/',
        ],
        'possible' => [
            'general' => '/^\\d{5}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
