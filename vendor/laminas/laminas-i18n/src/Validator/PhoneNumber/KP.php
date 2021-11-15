<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '850',
    'patterns' => [
        'national' => [
            'general' => '/^(?:1\\d{9}|[28]\\d{7})$/',
            'fixed' => '/^(?:2\\d{7}|85\\d{6})$/',
            'mobile' => '/^19[123]\\d{7}$/',
        ],
        'possible' => [
            'general' => '/^(?:\\d{6,8}|\\d{10})$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{10}$/',
        ],
    ],
];
