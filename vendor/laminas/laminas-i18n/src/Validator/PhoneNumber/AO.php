<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '244',
    'patterns' => [
        'national' => [
            'general' => '/^[29]\d{8}$/',
            'fixed' => '/^2\d(?:[26-9]\d|\d[26-9])\d{5}$/',
            'mobile' => '/^9[1-4]\d{7}$/',
            'emergency' => '/^11[235]$/',
        ],
        'possible' => [
            'general' => '/^\d{9}$/',
            'emergency' => '/^\d{3}$/',
        ],
    ],
];
