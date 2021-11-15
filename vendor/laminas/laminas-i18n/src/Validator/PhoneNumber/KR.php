<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '82',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[1-7]\\d{3,9}|8\\d{8})$/',
            'fixed' => '/^(?:2|[34][1-3]|5[1-5]|6[1-4])(?:1\\d{2,3}|[2-9]\\d{6,7})$/',
            'mobile' => '/^1[0-25-9]\\d{7,8}$/',
            'tollfree' => '/^80\\d{7}$/',
            'premium' => '/^60[2-9]\\d{6}$/',
            'personal' => '/^50\\d{8}$/',
            'voip' => '/^70\\d{8}$/',
            'uan' => '/^1(?:5(?:44|66|77|88|99)|6(?:00|44|6[16]|70|88))\\d{4}$/',
            'emergency' => '/^11[29]$/',
        ],
        'possible' => [
            'general' => '/^\\d{4,10}$/',
            'fixed' => '/^\\d{4,10}$/',
            'mobile' => '/^\\d{9,10}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'personal' => '/^\\d{10}$/',
            'voip' => '/^\\d{10}$/',
            'uan' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
