<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '56',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[2-9]|600|123)\\d{7,8}$/',
            'fixed' => '/^(?:(?:[23]2|41|58)\\d{7}|(?:3[3-5]|4[235]|5[1-357]|6[13-57]|7[1-35])\\d{6,7})$/',
            'mobile' => '/^9[5-9]\\d{7}$/',
            'tollfree' => '/^(?:800\\d{6}|1230\\d{7})$/',
            'shared' => '/^600\\d{7,8}$/',
            'voip' => '/^44\\d{7}$/',
            'emergency' => '/^13[123]$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,11}$/',
            'fixed' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{9,11}$/',
            'shared' => '/^\\d{10,11}$/',
            'voip' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
