<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '672',
    'patterns' => [
        'national' => [
            'general' => '/^[13]\\d{5}$/',
            'fixed' => '/^(?:1(?:06|17|28|39)|3[012]\\d)\\d{3}$/',
            'mobile' => '/^38\\d{4}$/',
            'emergency' => '/^9(?:11|55|77)$/',
        ],
        'possible' => [
            'general' => '/^\\d{5,6}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
