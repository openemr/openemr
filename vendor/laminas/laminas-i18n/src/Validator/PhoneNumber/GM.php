<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

return [
    'code' => '220',
    'patterns' => [
        'national' => [
            'general' => '/^[2-9]\\d{6}$/',
            'fixed' => '/^(?:4(?:[23]\\d{2}|4(?:1[024679]|[6-9]\\d))|5(?:54[0-7]|6(?:[67]\\d)|7(?:1[04]|2[035]|3[58]|48))|8\\d{3})\\d{3}$/',
            'mobile' => '/^(?:2[0-2]|[3679]\\d)\\d{5}$/',
            'emergency' => '/^1?1[678]$/',
        ],
        'possible' => [
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{2,3}$/',
        ],
    ],
];
