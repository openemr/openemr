<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '502',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[2-7]\\d{7}|1[89]\\d{9})$/',
            'fixed' => '/^[267][2-9]\\d{6}$/',
            'mobile' => '/^[345]\\d{7}$/',
            'tollfree' => '/^18[01]\\d{8}$/',
            'premium' => '/^19\\d{9}$/',
            'shortcode' => '/^1(?:2[124-9]|[57]\\d{2})$/',
            'emergency' => '/^1(?:10|2[03])$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}(?:\\d{3})?$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{11}$/',
            'premium' => '/^\\d{11}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
