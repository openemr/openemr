<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '297',
    'patterns' => [
        'national' => [
            'general' => '/^[25-9]\\d{6}$/',
            'fixed' => '/^5(?:2\\d|8[1-9])\\d{4}$/',
            'mobile' => '/^(?:5(?:6\\d|9[2-478])|6(?:[039]0|22|4[01]|6[0-2])|7[34]\\d|9(?:6[45]|9[4-8]))\\d{4}$/',
            'tollfree' => '/^800\\d{4}$/',
            'premium' => '/^900\\d{4}$/',
            'voip' => '/^(?:28\\d{5}|501\\d{4})$/',
            'emergency' => '/^(?:100|911)$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
