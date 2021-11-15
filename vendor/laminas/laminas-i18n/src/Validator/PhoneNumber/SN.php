<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '221',
    'patterns' => [
        'national' => [
            'general' => '/^[37]\\d{8}$/',
            'fixed' => '/^3(?:0(?:1[01]|80)|3(?:8[1-9]|9[2-9]))\\d{5}$/',
            'mobile' => '/^7(?:0(?:[01279]0|3[03]|4[05]|5[06]|6[03-5]|8[029])|6(?:1[23]|2[89]|3[3489]|4[6-9]|5\\d|6[3-9]|7[45]|8[3-8])|7\\d{2}|8(?:01|1[01]))\\d{5}$/',
            'voip' => '/^33301\\d{4}$/',
        ],
        'possible' => [
            'general' => '/^\\d{9}$/',
        ],
    ],
];
