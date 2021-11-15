<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '690',
    'patterns' => [
        'national' => [
            'general' => '/^[2-5]\\d{3}$/',
            'fixed' => '/^[2-4]\\d{3}$/',
            'mobile' => '/^5\\d{3}$/',
        ],
        'possible' => [
            'general' => '/^\\d{4}$/',
        ],
    ],
];
