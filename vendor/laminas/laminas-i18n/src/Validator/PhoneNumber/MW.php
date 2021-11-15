<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '265',
    'patterns' => [
        'national' => [
            'general' => '/^(?:1(?:\\d{2})?|[2789]\\d{2})\\d{6}$/',
            'fixed' => '/^(?:1[2-9]|21\\d{2})\\d{5}$/',
            'mobile' => '/^(?:111|77\\d|88\\d|99\\d)\\d{6}$/',
            'emergency' => '/^(?:199|99[789])$/',
        ],
        'possible' => [
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
