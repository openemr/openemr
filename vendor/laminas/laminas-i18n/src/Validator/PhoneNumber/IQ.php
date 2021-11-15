<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '964',
    'patterns' => [
        'national' => [
            'general' => '/^[1-7]\\d{7,9}$/',
            'fixed' => '/^(?:1\\d{7}|(?:2[13-5]|3[02367]|4[023]|5[03]|6[026])\\d{6,7})$/',
            'mobile' => '/^7[3-9]\\d{8}$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{10}$/',
        ],
    ],
];
