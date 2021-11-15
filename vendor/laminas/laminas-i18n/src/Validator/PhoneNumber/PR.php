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
            'general' => '/^[5789]\\d{9}$/',
            'fixed' => '/^(?:787|939)[2-9]\\d{6}$/',
            'mobile' => '/^(?:787|939)[2-9]\\d{6}$/',
            'tollfree' => '/^8(?:00|55|66|77|88)[2-9]\\d{6}$/',
            'premium' => '/^900[2-9]\\d{6}$/',
            'personal' => '/^5(?:00|33|44)[2-9]\\d{6}$/',
            'emergency' => '/^911$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}(?:\\d{3})?$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'personal' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
