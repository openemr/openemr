<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '55',
    'patterns' => [
        'national' => [
            'general' => '/^(?:[1-46-9]\\d{7,10}|5\\d{8,9})$/',
            'fixed' => '/^(?:[14689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])[2-5]\\d{7}$/',
            'mobile' => '/^(?:[14689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])9[6-9]\\d{7}$/',
            'tollfree' => '/^800\\d{6,7}$/',
            'premium' => '/^[359]00\\d{6,7}$/',
            'shared' => '/^[34]00\\d{5}$/',
            'emergency' => '/^(?:1(?:12|28|9[023])|911)$/',
        ],
        'possible' => [
            'general' => '/^\\d{8,11}$/',
            'mobile' => '/^\\d{11}$/',
            'shared' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ],
    ],
];
