<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '236',
    'patterns' => [
        'national' => [
            'general' => '/^[278]\\d{7}$/',
            'fixed' => '/^2[12]\\d{6}$/',
            'mobile' => '/^7[0257]\\d{6}$/',
            'premium' => '/^8776\\d{4}$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
        ],
    ],
];
