<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '501',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[2-8]\\d{6}|0\\d{10})$/',
            'fixed' => '/^[234578][02]\\d{5}$/',
            'mobile' => '/^6[0-367]\\d{5}$/',
            'tollfree' => '/^0800\\d{7}$/',
            'emergency' => '/^9(?:0|11)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}(?:\\d{4})?$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{11}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
