<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '1',
    'patterns' => [
        'national' => [
            'general' => '/^[5689]\\d{9}$/',
            'fixed' => '/^649(?:712|9(?:4\\d|50))\\d{4}$/',
            'mobile' => '/^649(?:2(?:3[129]|4[1-7])|3(?:3[1-39]|4[1-7])|4[34][12])\\d{4}$/',
            'tollfree' => '/^8(?:00|55|66|77|88)[2-9]\\d{6}$/',
            'premium' => '/^900[2-9]\\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\\d{6}$/',
            'voip' => '/^64971[01]\\d{4}$/',
            'emergency' => '/^9(?:11|99)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}(?:\\d{3})?$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'voip' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
