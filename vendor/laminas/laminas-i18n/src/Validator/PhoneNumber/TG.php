<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '228',
    'patterns' => [
        'national' => [
            'general' => '/^[29]\\d{7}$/',
            'fixed' => '/^2(?:2[2-7]|3[23]|44|55|66|77)\\d{5}$/',
            'mobile' => '/^9[0-289]\\d{6}$/',
            'emergency' => '/^1(?:01|1[78]|7[17])$/',
        ],
        'possible' => [
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
