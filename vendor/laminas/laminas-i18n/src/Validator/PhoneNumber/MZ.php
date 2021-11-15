<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '258',
    'patterns' => [
        'national' => [
            'general' => '/^[28]\\d{7,8}$/',
            'fixed' => '/^2(?:[1346]\\d|5[0-2]|[78][12]|93)\\d{5}$/',
            'mobile' => '/^8[246]\\d{7}$/',
            'tollfree' => '/^800\\d{6}$/',
            'shortcode' => '/^1[0234]\\d$/',
            'emergency' => '/^1(?:1[79]|9[78])$/',
        ],
        'possible' => [
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
