<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '248',
    'patterns' => [
        'national' => [
            'general' => '/^[24689]\\d{5,6}$/',
            'fixed' => '/^4[2-46]\\d{5}$/',
            'mobile' => '/^2[5-8]\\d{5}$/',
            'tollfree' => '/^8000\\d{2}$/',
            'premium' => '/^98\\d{4}$/',
            'voip' => '/^64\\d{5}$/',
            'shortcode' => '/^(?:1(?:0\\d|1[027]|2[0-8]|3[13]|4[0-2]|[59][15]|6[1-9]|7[124-6]|8[158])|96\\d{2})$/',
            'emergency' => '/^999$/',
        ],
        'possible' => [
            'general' => '/^\\d{6,7}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{6}$/',
            'premium' => '/^\\d{6}$/',
            'voip' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
