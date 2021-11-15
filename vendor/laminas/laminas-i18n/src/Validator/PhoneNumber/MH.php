<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '692',
    'patterns' => [
        'national' => [
            'general' => '/^[2-6]\\d{6}$/',
            'fixed' => '/^(?:247|528|625)\\d{4}$/',
            'mobile' => '/^(?:235|329|45[56]|545)\\d{4}$/',
            'voip' => '/^635\\d{4}$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
        ],
    ],
];
