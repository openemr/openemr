<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '974',
    'patterns' => [
        'national' => [
            'general' => '/^[2-8]\\d{6,7}$/',
            'fixed' => '/^4[04]\\d{6}$/',
            'mobile' => '/^[3567]\\d{7}$/',
            'pager' => '/^2(?:[12]\\d|61)\\d{4}$/',
            'tollfree' => '/^800\\d{4}$/',
            'shortcode' => '/^(?:1|20|9[27]\\d)\\d{2}$/',
            'emergency' => '/^999$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,8}$/',
            'pager' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
