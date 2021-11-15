<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '224',
    'patterns' => [
        'national' => [
            'general' => '/^[23567]\\d{7,8}$/',
            'fixed' => '/^30(?:24|3[12]|4[1-35-7]|5[13]|6[189]|[78]1|9[1478])\\d{4}$/',
            'mobile' => '/^(?:(?:24|55)\\d{6}|6(?:0(?:2[0-35-9]|3[3467]|5[2457-9])|1[0-5]\\d|2\\d{2,3}|[4-9]\\d{2}|3(?:[14]0|35))\\d{4})$/',
            'voip' => '/^78\\d{6}$/',
        ],
        'possible' => [
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
        ],
    ],
];
