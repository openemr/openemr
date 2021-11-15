<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '350',
    'patterns' => [
        'national' => [
            'general' => '/^[2568]\\d{7}$/',
            'fixed' => '/^2(?:00\\d|16[0-7]|22[2457])\\d{4}$/',
            'mobile' => '/^(?:5[4-8]|60)\\d{6}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^8[1-689]\\d{6}$/',
            'shared' => '/^87\\d{6}$/',
            'shortcode' => '/^1(?:00|1(?:6(?:00[06]|11[17])|8\\d{2})|23|4(?:1|7[014])|5[015]|9[34])|8(?:00|4[0-2]|8\\d)$/',
            'emergency' => '/^1(?:12|9[09])$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
