<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '64',
    'patterns' => [
        'national' => [
            'general' => '/^(?:6[235-9]\\d{6}|[2-57-9]\\d{7,10})$/',
            'fixed' => '/^(?:(?:3[2-79]|[49][2-689]|6[235-9]|7[2-589])\\d{6}|24099\\d{3})$/',
            'mobile' => '/^2(?:[028]\\d{7,8}|1(?:0\\d{5,7}|[12]\\d{5,6}|[3-9]\\d{5})|[79]\\d{7})$/',
            'pager' => '/^[28]6\\d{6,7}$/',
            'tollfree' => '/^(?:508\\d{6,7}|80\\d{6,8})$/',
            'premium' => '/^90\\d{7,9}$/',
            'emergency' => '/^111$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,11}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{8,10}$/',
            'pager' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{8,10}$/',
            'premium' => '/^\\d{9,11}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
