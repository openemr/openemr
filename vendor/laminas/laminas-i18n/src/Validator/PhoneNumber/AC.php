<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '247',
    'patterns' => [
        'national' => [
            'general' => '/^[2-467]\d{3}$/',
            'fixed' => '/^(?:[267]\d|3[0-5]|4[4-69])\d{2}$/',
            'emergency' => '/^911$/',
        ],
        'possible' => [
            'general' => '/^\d{4}$/',
            'fixed' => '/^\d{4}$/',
            'emergency' => '/^\d{3}$/',
        ],
    ],
];
