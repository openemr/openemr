<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '94',
    'patterns' => [
        'national' => [
            'general' => '/^[1-9]\\d{8}$/',
            'fixed' => '/^(?:[189]1|2[13-7]|3[1-8]|4[157]|5[12457]|6[35-7])[2-57]\\d{6}$/',
            'mobile' => '/^7[125-8]\\d{7}$/',
            'emergency' => '/^11[0189]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
