<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '227',
    'patterns' => [
        'national' => [
            'general' => '/^[029]\\d{7}$/',
            'fixed' => '/^2(?:0(?:20|3[1-7]|4[134]|5[14]|6[14578]|7[1-578])|1(?:4[145]|5[14]|6[14-68]|7[169]|88))\\d{4}$/',
            'mobile' => '/^9[0-46-9]\\d{6}$/',
            'tollfree' => '/^08\\d{6}$/',
            'premium' => '/^09\\d{6}$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
        ],
    ],
];
