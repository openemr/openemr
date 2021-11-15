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
            'general' => '/^[2589]\\d{9}$/',
            'fixed' => '/^242(?:3(?:02|[236][1-9]|4[0-24-9]|5[0-68]|7[3467]|8[0-4]|9[2-467])|461|502|6(?:12|7[67]|8[78]|9[89])|702)\\d{4}$/',
            'mobile' => '/^242(?:3(?:5[79]|[79]5)|4(?:[2-4][1-9]|5[1-8]|6[2-8]|7\\d|81)|5(?:2[45]|3[35]|44|5[1-9]|65|77)|6[34]6|727)\\d{4}$/',
            'tollfree' => '/^(?:242300\\d{4}|8(?:00|55|66|77|88)[2-9]\\d{6})$/',
            'premium' => '/^900[2-9]\\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\\d{6}$/',
            'emergency' => '/^91[19]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}(?:\\d{3})?$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
