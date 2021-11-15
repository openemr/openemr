<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '256',
    'patterns' => [
        'national' => [
            'general' => '/^\\d{9}$/',
            'fixed' => '/^(?:20(?:[014]\\d{2}|2(?:40|[5-9]\\d)|3[23]\\d|5[0-4]\\d)\\d{4}|[34]\\d{8})$/',
            'mobile' => '/^7(?:0[0-7]|[15789]\\d|20|[46][0-4])\\d{6}$/',
            'tollfree' => '/^800[123]\\d{5}$/',
            'premium' => '/^90[123]\\d{6}$/',
            'emergency' => '/^999$/',
        ],
        'possible' => [
            'general' => '/^\\d{5,9}$/',
            'fixed' => '/^\\d{5,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
